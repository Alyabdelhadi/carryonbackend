<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Key/value switches the admin toggles and the mobile app reads
 * (GET /api/appSettings). Booleans are stored as "1" / "0".
 */
class AppSetting extends Model
{
    protected $primaryKey = 'key';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['key', 'value'];

    public const SHUFTI_ENABLED = 'shufti_enabled';
    /** Legacy switch, replaced by IDENTITY_METHOD; only read as its default. */
    public const SHUFTI_LIVE = 'shufti_live';
    public const IDENTITY_METHOD = 'identity_method';
    public const STAT_PACKAGES = 'stat_packages';
    public const STAT_USERS = 'stat_users';
    public const STAT_TREES = 'stat_trees_saved';
    public const STAT_CITIES = 'stat_cities';
    public const COMMISSION_PERCENT = 'commission_percent';
    public const PAYOUT_MINIMUM = 'payout_minimum';
    public const PAYOUT_HOLD_DAYS = 'payout_hold_days';
    public const PAYMENT_DEADLINE_HOURS = 'payment_deadline_hours';
    public const PAYOUT_METHODS = 'payout_methods';

    /** The switches with their defaults, in the order the admin page shows them. */
    public static function definitions(): array
    {
        return [
            self::SHUFTI_ENABLED => [
                'label' => 'Identity verification required',
                'help' => 'When on, new and existing accounts can sign in and browse, but cannot send, receive or carry packages or add a trip until their identity is verified (by the method chosen below), and verified users show a badge. When off, nobody is blocked.',
                'default' => true,
            ],
        ];
    }

    /** How accounts get verified while verification is required. */
    public const IDENTITY_SHUFTI = 'shufti';
    public const IDENTITY_MANUAL = 'manual';

    /**
     * Settings with a fixed set of choices, shown as radio buttons.
     * `default` is used while the admin has not saved one.
     */
    public static function choiceDefinitions(): array
    {
        return [
            self::IDENTITY_METHOD => [
                'label' => 'Verification method',
                'help' => 'Nothing is checked at signup: users verify from the app when they first try to send, receive or carry.',
                'options' => [
                    self::IDENTITY_SHUFTI => 'Shufti Pro live verification: the app opens Shufti\'s page, which takes a live selfie with a liveness check and scans the ID. Check that onsite verification is enabled on the Shufti account first.',
                    self::IDENTITY_MANUAL => 'Manual review: the user uploads a selfie and a photo of their ID, and you approve or reject them on the Users page (filter "Awaiting review").',
                ],
                // before this setting existed, "live" meant Shufti and photo checks are gone
                'default' => fn () => static::getBool(self::SHUFTI_LIVE, false) ? self::IDENTITY_SHUFTI : self::IDENTITY_MANUAL,
            ],
        ];
    }

    public static function getChoice(string $key): string
    {
        $definition = static::choiceDefinitions()[$key];
        $value = optional(static::find($key))->value;
        if (!array_key_exists((string) $value, $definition['options'])) {
            $default = $definition['default'];
            return is_callable($default) ? $default() : $default;
        }
        return $value;
    }

    public static function setChoice(string $key, $value): void
    {
        if (array_key_exists((string) $value, static::choiceDefinitions()[$key]['options'])) {
            static::updateOrCreate(['key' => $key], ['value' => (string) $value]);
        }
    }

    public static function allChoices(): array
    {
        $out = [];
        foreach (array_keys(static::choiceDefinitions()) as $key) {
            $out[$key] = static::getChoice($key);
        }
        return $out;
    }

    /**
     * Fixed values for the home-screen counters; blank means "compute from
     * the database". `field` is the key in the /api/stats response.
     */
    public static function statDefinitions(): array
    {
        return [
            self::STAT_PACKAGES => ['label' => 'Packages', 'field' => 'packages'],
            self::STAT_USERS => ['label' => 'Users', 'field' => 'users'],
            self::STAT_TREES => ['label' => 'Trees saved', 'field' => 'trees_saved'],
            self::STAT_CITIES => ['label' => 'Cities', 'field' => 'cities'],
        ];
    }

    /**
     * Numbers for the online-payment / wallet flow, in the order the admin
     * page shows them. Blank or invalid input falls back to `default`.
     */
    public static function numberDefinitions(): array
    {
        return [
            self::COMMISSION_PERCENT => [
                'label' => 'CarryOn commission (%)',
                'help' => 'Percentage of the reward kept by CarryOn on card-paid orders. The carrier receives the rest in their wallet when the package is delivered.',
                'default' => 15, 'min' => 0, 'max' => 100, 'step' => '0.5',
            ],
            self::PAYOUT_MINIMUM => [
                'label' => 'Minimum payout',
                'help' => 'Smallest amount a carrier can request to withdraw, in the Stripe currency.',
                'default' => 20, 'min' => 0, 'max' => 100000, 'step' => '1',
            ],
            self::PAYOUT_HOLD_DAYS => [
                'label' => 'Payout hold (days)',
                'help' => 'Days after delivery before an earning can be withdrawn, to leave room for disputes.',
                'default' => 3, 'min' => 0, 'max' => 90, 'step' => '1',
            ],
            self::PAYMENT_DEADLINE_HOURS => [
                'label' => 'Payment deadline (hours)',
                'help' => 'How long the sender has to pay after a carrier accepts. Unpaid assignments are released back to Unassigned.',
                'default' => 24, 'min' => 1, 'max' => 720, 'step' => '1',
            ],
        ];
    }

    public static function getFloat(string $key): float
    {
        $definition = static::numberDefinitions()[$key] ?? ['default' => 0];
        $row = static::find($key);
        if (!$row || !is_numeric($row->value)) {
            return (float) $definition['default'];
        }
        return (float) $row->value;
    }

    public static function setNumber(string $key, $value): void
    {
        $definition = static::numberDefinitions()[$key];
        $clean = is_numeric($value)
            ? (string) min($definition['max'], max($definition['min'], (float) $value))
            : (string) $definition['default'];
        static::updateOrCreate(['key' => $key], ['value' => $clean]);
    }

    /** All numbers resolved, for the API and the admin page. */
    public static function allNumbers(): array
    {
        $out = [];
        foreach (array_keys(static::numberDefinitions()) as $key) {
            $out[$key] = static::getFloat($key);
        }
        return $out;
    }

    /**
     * Payout channels the carrier can pick, one per line as `code|Label`
     * (e.g. `omt|OMT`). Returns [['code' => ..., 'name' => ...], ...].
     */
    public static function payoutMethods(): array
    {
        $row = static::find(self::PAYOUT_METHODS);
        $raw = $row && trim((string) $row->value) !== ''
            ? $row->value
            : "bank_transfer|Bank transfer\nomt|OMT\nwhish|Whish Money\npaypal|PayPal";
        $out = [];
        foreach (preg_split('/\r\n|\r|\n/', $raw) as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }
            [$code, $name] = array_pad(explode('|', $line, 2), 2, null);
            $code = strtolower(trim(preg_replace('/[^A-Za-z0-9_]+/', '_', $code)));
            if ($code === '') {
                continue;
            }
            $out[] = ['code' => $code, 'name' => trim($name ?? '') !== '' ? trim($name) : ucfirst(str_replace('_', ' ', $code))];
        }
        return $out;
    }

    public static function getInt(string $key): ?int
    {
        $row = static::find($key);
        if (!$row || $row->value === null || trim($row->value) === '') {
            return null;
        }
        return is_numeric($row->value) ? (int) $row->value : null;
    }

    public static function setNullableInt(string $key, $value): void
    {
        $clean = ($value === null || trim((string) $value) === '') ? '' : (string) max(0, (int) $value);
        static::updateOrCreate(['key' => $key], ['value' => $clean]);
    }

    public static function getBool(string $key, bool $default = true): bool
    {
        $row = static::find($key);
        if (!$row || $row->value === null || $row->value === '') {
            return $default;
        }
        return in_array(strtolower($row->value), ['1', 'true', 'yes', 'on'], true);
    }

    public static function setBool(string $key, bool $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value ? '1' : '0']);
    }

    /** Every switch resolved to a boolean, for the API. */
    public static function allBools(): array
    {
        $out = [];
        foreach (static::definitions() as $key => $definition) {
            $out[$key] = static::getBool($key, $definition['default']);
        }
        return $out;
    }
}

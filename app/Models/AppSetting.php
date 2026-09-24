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

    /** The switches with their defaults, in the order the admin page shows them. */
    public static function definitions(): array
    {
        return [
            self::SHUFTI_ENABLED => [
                'label' => 'Shufti identity verification',
                'help' => 'When on, signup checks the selfie and ID document with Shufti Pro before creating the account. When off, the photos are still uploaded for manual review but no verification is run.',
                'default' => true,
            ],
        ];
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

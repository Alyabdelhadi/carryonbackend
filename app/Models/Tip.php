<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * A reward chip on the app's order form: an amount in the payment
 * currency; 0 shows as "Free". The app adds its own "Other" chip.
 */
class Tip extends Model
{
    use HasFactory;

    // Create New Tip
    public function addNew($data,$id)
    {
        $add                = $id === 'add' ? new Tip : Tip::find($id);
        $add->value         = self::normalizeAmount($data['value'] ?? '');
        $add->status        = isset($data['status']) ? $data['status'] : null;
        $add->sort_no       = isset($data['sort_no']) ? $data['sort_no'] : 0;

        $add->save();
    }

    /** "Free" / "No Tip" -> "0", "12.50" -> "12.5"; null when not an amount. */
    public static function normalizeAmount($value): ?string
    {
        $v = trim((string) $value);
        if (in_array(strtolower($v), ['free', 'no tip', 'none'], true)) {
            return '0';
        }
        $v = str_replace(',', '.', preg_replace('/[^\d.,]/', '', $v));
        if ($v === '' || !is_numeric($v) || (float) $v < 0 || (float) $v > 100000) {
            return null;
        }
        return rtrim(rtrim(number_format((float) $v, 2, '.', ''), '0'), '.');
    }

    // Get Tips
    public function getAll($status = "all")
    {
        return Tip::where(function($query) use($status){

            if($status !== "all")
            {
                $query->where('status',$status);
            }

        })->orderBy('sort_no','ASC')->get();
    }

    /** Active reward chips for the app, in admin order, no duplicates. */
    public static function forApp()
    {
        return Tip::where('status', 1)->orderBy('sort_no')->orderBy('id')->get()
            ->map(fn ($t) => ['id' => $t->id, 'value' => self::normalizeAmount($t->value), 'sort_no' => $t->sort_no])
            ->filter(fn ($t) => $t['value'] !== null)
            ->unique('value')
            ->map(fn ($t) => $t + ['amount' => (float) $t['value'], 'is_free' => (float) $t['value'] == 0.0])
            ->values();
    }
}

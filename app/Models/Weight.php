<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * A quick-pick weight in kg for the app: `in_order_form` offers it on the
 * send / receive form, `in_calculator` in the carbon calculator.
 */
class Weight extends Model
{
    use HasFactory;

    protected $casts = [
        'in_order_form' => 'boolean',
        'in_calculator' => 'boolean',
    ];

    // Create New Weight
    public function addNew($data,$id)
    {
        $add                = $id === 'add' ? new Weight : Weight::find($id);
        $add->value         = self::normalizeKg($data['value'] ?? '');
        $add->in_order_form = !empty($data['in_order_form']);
        $add->in_calculator = !empty($data['in_calculator']);
        $add->status        = isset($data['status']) ? $data['status'] : null;
        $add->sort_no       = isset($data['sort_no']) ? $data['sort_no'] : 0;

        $add->save();
    }

    /** "1,5 kg" -> "1.5"; null when it is not a positive number. */
    public static function normalizeKg($value): ?string
    {
        $v = str_replace(',', '.', trim(preg_replace('/\s*kg\s*$/i', '', (string) $value)));
        if (!is_numeric($v) || (float) $v <= 0 || (float) $v > 1000) {
            return null;
        }
        return rtrim(rtrim(number_format((float) $v, 2, '.', ''), '0'), '.');
    }

    // Get Weights
    public function getAll($status = "all")
    {
        return Weight::where(function($query) use($status){

            if($status !== "all")
            {
                $query->where('status',$status);
            }

        })->orderBy('sort_no','ASC')->get();
    }

    /** Active weights for the app: numeric kg, in admin order. */
    public static function forApp()
    {
        return Weight::where('status', 1)->orderBy('sort_no')->orderBy('id')->get()
            ->filter(fn ($w) => self::normalizeKg($w->value) !== null)
            ->map(fn ($w) => [
                'id' => $w->id,
                'value' => self::normalizeKg($w->value),
                'kg' => (float) self::normalizeKg($w->value),
                'in_order_form' => (bool) $w->in_order_form,
                'in_calculator' => (bool) $w->in_calculator,
                'sort_no' => $w->sort_no,
            ])->values();
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Version extends Model
{
    use HasFactory;

    protected $fillable = ['ios', 'android'];

    protected $casts = [
        'ios' => 'string',
        'android' => 'string',
    ];

    /**
     * Get the first version row or null.
     */
    public static function one(): ?self
    {
        return static::query()->first();
    }

    /**
     * Get the first version row or throw ModelNotFoundException.
     */
    public static function mustOne(): self
    {
        return static::query()->firstOrFail();
    }

    /**
     * Update the only version row with given attributes.
     */
    public static function updateValues(array $attrs): self
    {
        $row = static::mustOne();

        // Update only ios/android keys if provided
        $row->fill(array_intersect_key($attrs, array_flip(['ios', 'android'])));
        $row->save();

        return $row;
    }
}
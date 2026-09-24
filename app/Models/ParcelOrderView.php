<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ParcelOrderView extends Model
{
    protected $fillable = ['parcel_order_id', 'carrier_id', 'read_at'];

    public function order()
    {
        return $this->belongsTo(ParcelOrder::class, 'parcel_order_id');
    }

    public function carrier()
    {
        return $this->belongsTo(AppUser::class, 'carrier_id');
    }
}
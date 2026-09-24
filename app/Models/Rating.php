<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rating extends Model
{
    use HasFactory;
    
    protected $fillable = ['user_id', 'order_id', 'rating'];

    public function rate($data)
    {
        $userId  = $data['user_id'];
        $orderId = $data['order_id'];

        // Check if user exists
        $user = AppUser::find($userId);
        if (!$user) {
            return ['msg' => "There is no AppUser with ID {$userId}"];
        }

        // Check if the order exists and belongs to the user or carried by him
        $parcelOrder = ParcelOrder::where('id', $orderId)
            ->where(function ($query) use ($userId) {
                $query->where('user_id', $userId)
                      ->orWhere('carrier_id', $userId);
            })->first();
        
        if (!$parcelOrder) {
            return ['msg' => "Package ID {$orderId} does not belong to user ID {$userId} or he is not the carrier"];
        }

        // Check if rating already exists for this order
        $existingRating = Rating::where('order_id', $orderId)->first();
        if ($existingRating) {
            return ['msg' => "This package has been rated."];
        }

        // Save rating
        $rating = Rating::create([
            'user_id'  => $userId,
            'order_id' => $orderId,
            'rating'   => $data['rating']
        ]);

        return ['msg' => 'done', 'rating' => $rating];
    }
}



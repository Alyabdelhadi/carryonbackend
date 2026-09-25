<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    /** Arabic place names for the app's Arabic mode (null when unknown). */
    protected $appends = ['city_ar', 'country_ar'];

    public function getCityArAttribute(): ?string
    {
        return \App\Services\PlaceNameService::city($this->attributes['city'] ?? null, $this->attributes['country'] ?? null);
    }

    public function getCountryArAttribute(): ?string
    {
        return \App\Services\PlaceNameService::country($this->attributes['country'] ?? null);
    }

    use HasFactory;

    public function addNew($data)
    {
        $add              = new Address;
        $add->user_id     = $data['user_id'];
        $add->name     = $data['name'];
        $add->city        = $data['city'];
        $add->country     = $data['country'];
        $add->lat     = $data['lat'];
        $add->lng     = $data['lng'];
        $add->street     = $data['street'];
        $add->building     = $data['building'];
        $add->apartment     = $data['apartment'];
        $add->notes     = isset($data['notes']) ? $data['notes'] : null;
        $add->type        = isset($data['type']) ? $data['type'] : 0;
        $add->save();
    }

    public function updateAddress($data)
    {
        $addressId = $_GET['id'] ?? ($data['id'] ?? null);
        $address = Address::find($addressId);
        // only the owner may change an address, and it stays theirs
        if ($address && (int) $address->user_id !== (int) ($data['user_id'] ?? 0)) {
            return ['message' => "Address with ID {$addressId} not found"];
        }
        if ($address) {
            $address->name        = $data['name'];
            $address->city        = $data['city'];
            $address->country     = $data['country'];
            $address->lat         = $data['lat'];
            $address->lng         = $data['lng'];
            $address->street      = $data['street'];
            $address->building    = $data['building'];
            $address->apartment   = $data['apartment'];
            $address->notes       = isset($data['notes']) ? $data['notes'] : null;
            $address->type        = isset($data['type']) ? $data['type'] : 0;
            $address->save();

            $message = "done";
            $response = ['message' => $message, 'address' => $address];
        } else {
            $response = ['message' => "Address with ID {$addressId} not found"];
        }

        return $response;
    }

    public function deleteAddress($data)
    {
        $userId = $data['user_id'];
        $user   = AppUser::find($userId);
        
        // Check if the user exist
        if ($user) {
        
            $addressId = $data['address_id'];
            $address = Address::find($addressId);
            
            // Check if the address exist
            if ($address) {
                
                // Check if the user is the creator of this address
                if ($user->id == $address->user_id) {
                    
                    // Delete Address
                    $address->delete();
                    $response = ['message' => "Done"];
                    
                } else {
                    $response = ['message' => "User ID does not match the user associated with the address"];
                }
                
            } else {
                $response = ['message' => "Address with ID {$addressId} not found"];
            }
            
        } else {
            
            $response = ['message' => "There is no AppUser with ID {$userId}"];
            
        }

        return $response;
    }


    public function getAddresses($data)
    {       
        return Address::where(function($query){
            
            $query->where('user_id',$_GET['userid']);
    
        })->select('id', 'name', 'city','country','lat','lng', 'street', 'building', 'apartment', 'notes', 'type')
          ->orderBy('id','DESC')
          ->get();
    }
    
    public function createAddress($data)
    {
        $userId = $data['user_id'];
        $user   = AppUser::find($userId);
        // Check if the user exist
        if ($user) {
            $add              = new Address;
            $add->user_id     = $data['user_id'];
            $add->name     = $data['name'];
            $add->city        = $data['city'];
            $add->country     = $data['country'];
            $add->lat     = $data['lat'];
            $add->lng     = $data['lng'];
            $add->street     = $data['street'];
            $add->building     = $data['building'];
            $add->apartment     = $data['apartment'];
            $add->notes     = isset($data['notes']) ? $data['notes'] : null;
            $add->type        = isset($data['type']) ? $data['type'] : 0;
            $add->save();
            $message = "done";
            $response = ['message' => $message, 'address' => $add];
        } else {
            $response = ['message' => "There is no AppUser with ID {$userId}"];
        }
        return $response;
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    use HasFactory;

    // Create New City
    public function addNew($data, $id)
    {
        $add = $id === 'add' ? new City : City::find($id);
        $add->name = $data['name'];
        $add->name_ar = isset($data['name_ar']) && trim($data['name_ar']) !== '' ? trim($data['name_ar']) : null;
        $add->country_id = $data['country_id'] ?? null;
        $add->status        = isset($data['status']) ? $data['status'] : null;
        
        if(isset($data['image']))
         {
             $filename   = time().rand(111,699).'.' .$data['image']->getClientOriginalExtension(); 
             $data['image']->move("upload/cities/", $filename);   
             $add->image = $filename;   
         }

        $add->save();
    }

    // Get All Cities
    public function getAll($status = "all")
    {
        return City::where(function($query) use($status){
 
             if($status !== "all")
             {
                 $query->where('status',$status);
             }
 
         })->get();
    }
    
    // Get All Cities Paginated
    public function getAllPaginated($perPage = 20, $status = "all")
    {
        return City::with('country')
            ->when($status !== "all", function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->paginate($perPage);
    }

    // Get City by ID
    public function getCityById()
    {
        return City::find($_GET['id']);
    }

    // Get Cities by Country ID (alphabetically)
    public function getCitiesByCountryId($countryId, $status = "all")
    {
        return City::where('country_id', $countryId)
            ->when($status !== "all", function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->orderBy('name', 'asc') // 👈 sort alphabetically
            ->get();
    }
    
    public function country()
    {
        return $this->belongsTo(Country::class);
    }
}
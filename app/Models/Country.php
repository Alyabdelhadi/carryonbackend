<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    use HasFactory;

    // Create New Country
    public function addNew($data,$id)
    {
        $add                = $id === 'add' ? new Country : Country::find($id);
        $add->name       = $data['name'];
        $add->name_ar    = isset($data['name_ar']) && trim($data['name_ar']) !== '' ? trim($data['name_ar']) : null;
        $add->code       = $data['code'];
        $add->phone_code       = $data['phone_code'];
        $add->flag       = $data['flag'];
        $add->status        = isset($data['status']) ? $data['status'] : null;

        $add->save();
    }

    // Get Countries
    public function getAll($status = "all")
    {
        return Country::where(function($query) use($status){
 
             if($status !== "all")
             {
                 $query->where('status',$status);
             }
 
         })->orderBy('name', 'asc')->get();
    }

    // Get Country by Id
    public function getCountryById()
    {
        return Country::find($_GET['id']);
    }
}

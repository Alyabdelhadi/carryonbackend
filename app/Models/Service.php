<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Service extends Model
{
    use HasFactory;

    // Create New Service
    public function addNew($data,$type)
    {
        $add                = $type === 'add' ? new Service : Service::find($type);
        $add->name          = isset($data['name']) ? $data['name'] : null;
        $add->name_ar       = isset($data['name_ar']) && trim($data['name_ar']) !== '' ? trim($data['name_ar']) : null;
        $add->status        = isset($data['status']) ? $data['status'] : null;
        $add->sort_no       = isset($data['sort_no']) ? $data['sort_no'] : 0;

        if(isset($data['img']))
        {
            $filename   = time().rand(111,699).'.' .$data['img']->getClientOriginalExtension(); 
            $data['img']->move("upload/services/", $filename);   
            $add->img = $filename;   
        }

        $add->save();
    }

    // Get Services
    public function getAll($status = "all")
    {
        return Service::where(function($query) use($status){

            if($status !== "all")
            {
                $query->where('status',$status);
            }

        })->orderBy('sort_no','ASC')->get();
    }
}

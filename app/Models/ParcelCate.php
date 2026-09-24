<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParcelCate extends Model
{
    use HasFactory;

    // Create New Parcel Category
    public function addNew($data,$type)
    {
        $add                = $type === 'add' ? new ParcelCate : ParcelCate::find($type);
        $add->name          = isset($data['name']) ? $data['name'] : null;
        $add->name_ar       = isset($data['name_ar']) && trim($data['name_ar']) !== '' ? trim($data['name_ar']) : null;
        $add->text          = isset($data['text']) ? $data['text'] : null;
        $add->status        = isset($data['status']) ? $data['status'] : null;
        $add->sort_no       = isset($data['sort_no']) ? $data['sort_no'] : 0;

        if(isset($data['img']))
        {
            $filename   = time().rand(111,699).'.' .$data['img']->getClientOriginalExtension(); 
            $data['img']->move("upload/categories/", $filename);   
            $add->img = $filename;   
        }

        $add->save();
    }

    // Get Parcel Categories
    public function getAll($status = "all")
    {
        return ParcelCate::where(function($query) use($status){

            if($status !== "all")
            {
                $query->where('status',$status);
            }

        })->orderBy('sort_no','ASC')->get();
    }
}

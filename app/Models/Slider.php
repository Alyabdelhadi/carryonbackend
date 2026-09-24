<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Slider extends Model
{
    use HasFactory;

     // Create New Slider
     public function addNew($data,$type)
     {
         $add                = $type === 'add' ? new Slider : Slider::find($type);
         $add->status        = isset($data['status']) ? $data['status'] : null;
         $add->sort_no       = isset($data['sort_no']) ? $data['sort_no'] : 0;
 
         if(isset($data['img']))
         {
             $filename   = time().rand(111,699).'.' .$data['img']->getClientOriginalExtension(); 
             $data['img']->move("upload/sliders/", $filename);   
             $add->img = $filename;   
         }

         if(isset($data['img_ar']))
         {
             $filename   = time().rand(111,699).'-ar.' .$data['img_ar']->getClientOriginalExtension();
             $data['img_ar']->move("upload/sliders/", $filename);
             $add->img_ar = $filename;
         }
         if(!empty($data['remove_img_ar']))
         {
             $add->img_ar = null;
         }
 
         $add->save();
     }
 
     // Get Sliders
     public function getAll($status = "all")
     {
         return Slider::where(function($query) use($status){
 
             if($status !== "all")
             {
                 $query->where('status',$status);
             }
 
         })->orderBy('sort_no','ASC')->get();
     }
}

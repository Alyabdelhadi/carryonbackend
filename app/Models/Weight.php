<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Weight extends Model
{
    use HasFactory;

    // Create New Weight
    public function addNew($data,$id)
    {
        $add                = $id === 'add' ? new Weight : Weight::find($id);
        $add->value       = $data['value'];
        $add->status        = isset($data['status']) ? $data['status'] : null;
        $add->sort_no       = isset($data['sort_no']) ? $data['sort_no'] : 0;

        $add->save();
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
}

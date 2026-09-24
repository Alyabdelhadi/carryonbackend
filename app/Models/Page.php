<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    use HasFactory;

    // Create New Page
    public function addNew($data,$id)
    {
        $add                = $id === 'add' ? new Page : Page::find($id);
        $add->title       = $data['title'];
        $add->content       = $data['content'];
        $add->status        = isset($data['status']) ? $data['status'] : null;
        $add->sort_no       = isset($data['sort_no']) ? $data['sort_no'] : 0;

        $add->save();
    }

    // Get Pages
    public function getAll($status = "all")
    {
        return Page::where(function($query) use($status){

            if($status !== "all")
            {
                $query->where('status',$status);
            }

        })->orderBy('sort_no','ASC')->get();
    }

    // Get Page by Id
    public function getPageById()
    {
        return Page::find($_GET['id']);
    }
}

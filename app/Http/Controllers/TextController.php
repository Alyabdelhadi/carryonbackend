<?php

namespace App\Http\Controllers;

use App\Models\Text;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class TextController extends Controller
{
    public $folder  = "texts.";
	
	public function index()
	{		
		return View($this->folder.'index',[

			'data' 		=> new Text

		]);
	}	

	public function save(Request $Request)
	{					
		$res = new Text;
		
		$res->addNew($Request->all());

		return Redirect::back()->with('message','Updated Successfully');
	}
}

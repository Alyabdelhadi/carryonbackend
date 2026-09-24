<?php

namespace App\Http\Controllers;

use App\Models\Page;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public $folder  = "pages.";
    
	/*
	|---------------------------------------
	|@Showing all records
	|---------------------------------------
	*/
	public function index()
	{					
		$res = new Page;
		
		return View($this->folder.'index',['data' => $res->getAll(),'link' => 'pages/']);
	}	
	
	/*
	|---------------------------------------
	|@Add new page
	|---------------------------------------
	*/
	public function show()
	{	

		return View($this->folder.'add',['data' => new Page,'form_url' => env('admin').'/pages']);
	}
	
	/*
	|---------------------------------------
	|@Save data in DB
	|---------------------------------------
	*/
	public function store(Request $Request)
	{			
		$data = new Page;	
		
		$data->addNew($Request->all(),"add");
		
		return redirect(env('admin').'/pages')->with('message','New Record Added Successfully.');
	}
	
	/*
	|---------------------------------------
	|@Edit Page 
	|---------------------------------------
	*/
	public function edit($id)
	{	
	
		return View($this->folder.'edit',['data' => Page::find($id),'form_url' => env('admin').'/pages/'.$id]);
	}
	
	/*
	|---------------------------------------
	|@update data in DB
	|---------------------------------------
	*/
	public function update(Request $Request,$id)
	{	
		$data = new Page;

		$data->addNew($Request->all(),$id);
		
		return redirect(env('admin').'/pages')->with('message','Record Updated Successfully.');
	}
	
	/*
	|---------------------------------------------
	|@Delete Data
	|---------------------------------------------
	*/
	public function delete($id)
	{
		Page::where('id',$id)->delete();

		return redirect(env('admin').'/pages')->with('message','Record Deleted Successfully.');
	}

	public function pageStatus()
	{
		$res 			= Page::find($_GET['id']);
		$res->status 	= $res->status == 0 ? 1 : 0;
		$res->save();

		return redirect(env('admin').'/pages')->with('message','Status Updated Successfully.');

	}
}

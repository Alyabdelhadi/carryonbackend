<?php

namespace App\Http\Controllers;

use App\Models\Slider2;
use Illuminate\Http\Request;

class Slider2Controller extends Controller
{
    public $folder  = "sliders2.";
    
	/*
	|---------------------------------------
	|@Showing all records
	|---------------------------------------
	*/
	public function index()
	{					
		$res = new Slider2;
		
		return View($this->folder.'index',['data' => $res->getAll(),'link' => 'sliders2/']);
	}	
	
	/*
	|---------------------------------------
	|@Add new page
	|---------------------------------------
	*/
	public function show()
	{	

		return View($this->folder.'add',['data' => new Slider2,'form_url' => env('admin').'/sliders2']);
	}
	
	/*
	|---------------------------------------
	|@Save data in DB
	|---------------------------------------
	*/
	public function store(Request $Request)
	{			
		$data = new Slider2;	
		
		$data->addNew($Request->all(),"add");
		
		return redirect(env('admin').'/sliders2')->with('message','New Record Added Successfully.');
	}
	
	/*
	|---------------------------------------
	|@Edit Page 
	|---------------------------------------
	*/
	public function edit($id)
	{	
	
		return View($this->folder.'edit',['data' => Slider2::find($id),'form_url' => env('admin').'/sliders2/'.$id]);
	}
	
	/*
	|---------------------------------------
	|@update data in DB
	|---------------------------------------
	*/
	public function update(Request $Request,$id)
	{	
		$data = new Slider2;

		$data->addNew($Request->all(),$id);
		
		return redirect(env('admin').'/sliders2')->with('message','Record Updated Successfully.');
	}
	
	/*
	|---------------------------------------------
	|@Delete Data
	|---------------------------------------------
	*/
	public function delete($id)
	{
		Slider2::where('id',$id)->delete();

		return redirect(env('admin').'/sliders2')->with('message','Record Deleted Successfully.');
	}

	public function sliderStatus()
	{
		$res 			= Slider2::find($_GET['id']);
		$res->status 	= $res->status == 0 ? 1 : 0;
		$res->save();

		return redirect(env('admin').'/sliders2')->with('message','Status Updated Successfully.');

	}
}

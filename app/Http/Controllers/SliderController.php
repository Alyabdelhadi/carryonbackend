<?php

namespace App\Http\Controllers;

use App\Models\Slider;
use Illuminate\Http\Request;

class SliderController extends Controller
{
    public $folder  = "sliders.";
    
	/*
	|---------------------------------------
	|@Showing all records
	|---------------------------------------
	*/
	public function index()
	{					
		$res = new Slider;
		
		return View($this->folder.'index',['data' => $res->getAll(),'link' => 'sliders/']);
	}	
	
	/*
	|---------------------------------------
	|@Add new page
	|---------------------------------------
	*/
	public function show()
	{	

		return View($this->folder.'add',['data' => new Slider,'form_url' => env('admin').'/sliders']);
	}
	
	/*
	|---------------------------------------
	|@Save data in DB
	|---------------------------------------
	*/
	public function store(Request $Request)
	{			
		$data = new Slider;	
		
		$data->addNew($Request->all(),"add");
		
		return redirect(env('admin').'/sliders')->with('message','New Record Added Successfully.');
	}
	
	/*
	|---------------------------------------
	|@Edit Page 
	|---------------------------------------
	*/
	public function edit($id)
	{	
	
		return View($this->folder.'edit',['data' => Slider::find($id),'form_url' => env('admin').'/sliders/'.$id]);
	}
	
	/*
	|---------------------------------------
	|@update data in DB
	|---------------------------------------
	*/
	public function update(Request $Request,$id)
	{	
		$data = new Slider;

		$data->addNew($Request->all(),$id);
		
		return redirect(env('admin').'/sliders')->with('message','Record Updated Successfully.');
	}
	
	/*
	|---------------------------------------------
	|@Delete Data
	|---------------------------------------------
	*/
	public function delete($id)
	{
		Slider::where('id',$id)->delete();

		return redirect(env('admin').'/sliders')->with('message','Record Deleted Successfully.');
	}

	public function sliderStatus()
	{
		$res 			= Slider::find($_GET['id']);
		$res->status 	= $res->status == 0 ? 1 : 0;
		$res->save();

		return redirect(env('admin').'/sliders')->with('message','Status Updated Successfully.');

	}
}

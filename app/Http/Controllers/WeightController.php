<?php

namespace App\Http\Controllers;

use App\Models\Weight;
use Illuminate\Http\Request;

class WeightController extends Controller
{
    public $folder  = "weights.";
    
	/*
	|---------------------------------------
	|@Showing all records
	|---------------------------------------
	*/
	public function index()
	{					
		$res = new Weight;
		
		return View($this->folder.'index',['data' => $res->getAll(),'link' => 'weights/']);
	}	
	
	/*
	|---------------------------------------
	|@Add new page
	|---------------------------------------
	*/
	public function show()
	{	

		return View($this->folder.'add',['data' => new Weight,'form_url' => env('admin').'/weights']);
	}
	
	/*
	|---------------------------------------
	|@Save data in DB
	|---------------------------------------
	*/
	public function store(Request $Request)
	{			
		$data = new Weight;	
		
		$data->addNew($Request->all(),"add");
		
		return redirect(env('admin').'/weights')->with('message','New Record Added Successfully.');
	}
	
	/*
	|---------------------------------------
	|@Edit Page 
	|---------------------------------------
	*/
	public function edit($id)
	{	
	
		return View($this->folder.'edit',['data' => Weight::find($id),'form_url' => env('admin').'/weights/'.$id]);
	}
	
	/*
	|---------------------------------------
	|@update data in DB
	|---------------------------------------
	*/
	public function update(Request $Request,$id)
	{	
		$data = new Weight;

		$data->addNew($Request->all(),$id);
		
		return redirect(env('admin').'/weights')->with('message','Record Updated Successfully.');
	}
	
	/*
	|---------------------------------------------
	|@Delete Data
	|---------------------------------------------
	*/
	public function delete($id)
	{
		Weight::where('id',$id)->delete();

		return redirect(env('admin').'/weights')->with('message','Record Deleted Successfully.');
	}

	public function weightStatus()
	{
		$res 			= Weight::find($_GET['id']);
		$res->status 	= $res->status == 0 ? 1 : 0;
		$res->save();

		return redirect(env('admin').'/weights')->with('message','Status Updated Successfully.');

	}
}

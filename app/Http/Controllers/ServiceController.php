<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public $folder  = "services.";
    
	/*
	|---------------------------------------
	|@Showing all records
	|---------------------------------------
	*/
	public function index()
	{					
		$res = new Service;
		
		return View($this->folder.'index',['data' => $res->getAll(),'link' => 'services/']);
	}	
	
	/*
	|---------------------------------------
	|@Add new page
	|---------------------------------------
	*/
	public function show()
	{	

		return View($this->folder.'add',['data' => new Service,'form_url' => env('admin').'/services']);
	}
	
	/*
	|---------------------------------------
	|@Save data in DB
	|---------------------------------------
	*/
	public function store(Request $Request)
	{			
		$data = new Service;	
		
		$data->addNew($Request->all(),"add");
		
		return redirect(env('admin').'/services')->with('message','New Record Added Successfully.');
	}
	
	/*
	|---------------------------------------
	|@Edit Page 
	|---------------------------------------
	*/
	public function edit($id)
	{	
	
		return View($this->folder.'edit',['data' => Service::find($id),'form_url' => env('admin').'/services/'.$id]);
	}
	
	/*
	|---------------------------------------
	|@update data in DB
	|---------------------------------------
	*/
	public function update(Request $Request,$id)
	{	
		$data = new Service;

		$data->addNew($Request->all(),$id);
		
		return redirect(env('admin').'/services')->with('message','Record Updated Successfully.');
	}
	
	/*
	|---------------------------------------------
	|@Delete Data
	|---------------------------------------------
	*/
	public function delete($id)
	{
		Service::where('id',$id)->delete();

		return redirect(env('admin').'/services')->with('message','Record Deleted Successfully.');
	}

	public function serviceStatus()
	{
		$res 			= Service::find($_GET['id']);
		$res->status 	= $res->status == 0 ? 1 : 0;
		$res->save();

		return redirect(env('admin').'/services')->with('message','Status Updated Successfully.');

	}
}

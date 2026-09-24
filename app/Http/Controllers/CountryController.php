<?php

namespace App\Http\Controllers;

use App\Models\Country;
use Illuminate\Http\Request;

class CountryController extends Controller
{
    public $folder  = "countries.";
    
	/*
	|---------------------------------------
	|@Showing all records
	|---------------------------------------
	*/
	public function index()
	{					
		$res = new Country;
		
		return View($this->folder.'index',['data' => $res->getAll(),'link' => 'countries/']);
	}	
	
	/*
	|---------------------------------------
	|@Add new page
	|---------------------------------------
	*/
	public function show()
	{	

		return View($this->folder.'add',['data' => new Country,'form_url' => env('admin').'/countries']);
	}
	
	/*
	|---------------------------------------
	|@Save data in DB
	|---------------------------------------
	*/
	public function store(Request $Request)
	{			
		$data = new Country;	
		
		$data->addNew($Request->all(),"add");
		
		return redirect(env('admin').'/countries')->with('message','New Record Added Successfully.');
	}
	
	/*
	|---------------------------------------
	|@Edit Page 
	|---------------------------------------
	*/
	public function edit($id)
	{	
	
		return View($this->folder.'edit',['data' => Country::find($id),'form_url' => env('admin').'/countries/'.$id]);
	}
	
	/*
	|---------------------------------------
	|@update data in DB
	|---------------------------------------
	*/
	public function update(Request $Request,$id)
	{	
		$data = new Country;

		$data->addNew($Request->all(),$id);
		
		return redirect(env('admin').'/countries')->with('message','Record Updated Successfully.');
	}
	
	/*
	|---------------------------------------------
	|@Delete Data
	|---------------------------------------------
	*/
	public function delete($id)
	{
		Country::where('id',$id)->delete();

		return redirect(env('admin').'/countries')->with('message','Record Deleted Successfully.');
	}
	
	public function countryStatus()
	{
		$res 			= Country::find($_GET['id']);
		$res->status 	= $res->status == 0 ? 1 : 0;
		$res->save();

		return redirect(env('admin').'/countries')->with('message','Status Updated Successfully.');

	}

}

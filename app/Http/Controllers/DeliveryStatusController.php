<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\DeliveryStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;


class DeliveryStatusController extends Controller
{
    public $folder  = "statuses.";
    
	/*
	|---------------------------------------
	|@Showing all records
	|---------------------------------------
	*/
	public function index()
	{					
		$res = new DeliveryStatus;
		
		return View($this->folder.'index',['data' => $res->getAll(),'link' => 'delivery_statuses/']);
	}	
	
	/*
	|---------------------------------------
	|@Add new page
	|---------------------------------------
	*/
	public function show()
	{	

		return View($this->folder.'add',['data' => new DeliveryStatus,'form_url' => env('admin').'/delivery_statuses']);
	}
	
	/*
	|---------------------------------------
	|@Save data in DB
	|---------------------------------------
	*/
	public function store(Request $Request)
	{			
		$data = new DeliveryStatus;	
		
		$data->addNew($Request->all(),"add");
		
		return redirect(env('admin').'/delivery_statuses')->with('message','New Record Added Successfully.');
	}
	
	/*
	|---------------------------------------
	|@Edit Page 
	|---------------------------------------
	*/
	public function edit($id)
	{	
	
		return View($this->folder.'edit',['data' => DeliveryStatus::find($id),'form_url' => env('admin').'/delivery_statuses/'.$id]);
	}
	
	/*
	|---------------------------------------
	|@update data in DB
	|---------------------------------------
	*/
	public function update(Request $Request,$id)
	{	
		$data = new DeliveryStatus;

		$data->addNew($Request->all(),$id);
		
		return redirect(env('admin').'/delivery_statuses')->with('message','Record Updated Successfully.');
	}
	
	/*
	|---------------------------------------------
	|@Delete Data
	|---------------------------------------------
	*/
	public function delete($id)
	{
		DeliveryStatus::where('id',$id)->delete();

		return redirect(env('admin').'/delivery_statuses')->with('message','Record Deleted Successfully.');
	}

	public function deliveryStatusStatus()
	{
		$res 			= DeliveryStatus::find($_GET['id']);
		$res->status 	= $res->status == 0 ? 1 : 0;
		$res->save();

		return redirect(env('admin').'/delivery_statuses')->with('message','Status Updated Successfully.');

	}

}

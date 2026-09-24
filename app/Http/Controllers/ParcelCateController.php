<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\ParcelCate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;


class ParcelCateController extends Controller
{
    public $folder  = "categories.";
    
	/*
	|---------------------------------------
	|@Showing all records
	|---------------------------------------
	*/
	public function index()
	{					
		$res = new ParcelCate;
		
		return View($this->folder.'index',['data' => $res->getAll(),'link' => 'parcel_cate/']);
	}	
	
	/*
	|---------------------------------------
	|@Add new page
	|---------------------------------------
	*/
	public function show()
	{	

		return View($this->folder.'add',['data' => new ParcelCate,'form_url' => env('admin').'/parcel_cate']);
	}
	
	/*
	|---------------------------------------
	|@Save data in DB
	|---------------------------------------
	*/
	public function store(Request $Request)
	{			
		$data = new ParcelCate;	
		
		$data->addNew($Request->all(),"add");
		
		return redirect(env('admin').'/parcel_cate')->with('message','New Record Added Successfully.');
	}
	
	/*
	|---------------------------------------
	|@Edit Page 
	|---------------------------------------
	*/
	public function edit($id)
	{	
	
		return View($this->folder.'edit',['data' => ParcelCate::find($id),'form_url' => env('admin').'/parcel_cate/'.$id]);
	}
	
	/*
	|---------------------------------------
	|@update data in DB
	|---------------------------------------
	*/
	public function update(Request $Request,$id)
	{	
		$data = new ParcelCate;

		$data->addNew($Request->all(),$id);
		
		return redirect(env('admin').'/parcel_cate')->with('message','Record Updated Successfully.');
	}
	
	/*
	|---------------------------------------------
	|@Delete Data
	|---------------------------------------------
	*/
	public function delete($id)
	{
		ParcelCate::where('id',$id)->delete();

		return redirect(env('admin').'/parcel_cate')->with('message','Record Deleted Successfully.');
	}

	public function parcelCateStatus()
	{
		$res 			= ParcelCate::find($_GET['id']);
		$res->status 	= $res->status == 0 ? 1 : 0;
		$res->save();

		return redirect(env('admin').'/parcel_cate')->with('message','Status Updated Successfully.');

	}

	public function parcel_setting()
	{
		return View($this->folder.'setting');
	}

	public function _parcel_setting(Request $Request)
	{
		$res 						= User::find(1);
		$res->parcel_delivery_km 	= $Request->get('parcel_delivery_km');
		$res->parcel_min_delivery 	= $Request->get('parcel_min_delivery');
		$res->parcel_delivery_com 	= $Request->get('parcel_delivery_com');
		$res->save();

		return redirect::back()->with('message','Setting Updated Successfully.');

	}
}

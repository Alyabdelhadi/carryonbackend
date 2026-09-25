<?php

namespace App\Http\Controllers;

use App\Models\Tip;
use Illuminate\Http\Request;

class TipController extends Controller
{
    public $folder  = "tips.";
    
	/*
	|---------------------------------------
	|@Showing all records
	|---------------------------------------
	*/
	public function index()
	{					
		$res = new Tip;
		
		return View($this->folder.'index',['data' => $res->getAll(),'link' => 'tips/']);
	}	
	
	/*
	|---------------------------------------
	|@Add new page
	|---------------------------------------
	*/
	public function show()
	{	

		return View($this->folder.'add',['data' => new Tip,'form_url' => env('admin').'/tips']);
	}
	
	/*
	|---------------------------------------
	|@Save data in DB
	|---------------------------------------
	*/
	public function store(Request $Request)
	{			
		if (\App\Models\Tip::normalizeAmount($Request->input('value')) === null) {
			return back()->withInput()->with('error', 'Enter the reward as an amount, e.g. 10 (0 = Free).');
		}
		$data = new Tip;	
		
		$data->addNew($Request->all(),"add");
		
		return redirect(env('admin').'/tips')->with('message','New Record Added Successfully.');
	}
	
	/*
	|---------------------------------------
	|@Edit Page 
	|---------------------------------------
	*/
	public function edit($id)
	{	
	
		return View($this->folder.'edit',['data' => Tip::find($id),'form_url' => env('admin').'/tips/'.$id]);
	}
	
	/*
	|---------------------------------------
	|@update data in DB
	|---------------------------------------
	*/
	public function update(Request $Request,$id)
	{	
		if (\App\Models\Tip::normalizeAmount($Request->input('value')) === null) {
			return back()->withInput()->with('error', 'Enter the reward as an amount, e.g. 10 (0 = Free).');
		}
		$data = new Tip;

		$data->addNew($Request->all(),$id);
		
		return redirect(env('admin').'/tips')->with('message','Record Updated Successfully.');
	}
	
	/*
	|---------------------------------------------
	|@Delete Data
	|---------------------------------------------
	*/
	public function delete($id)
	{
		Tip::where('id',$id)->delete();

		return redirect(env('admin').'/tips')->with('message','Record Deleted Successfully.');
	}

	public function tipStatus()
	{
		$res 			= Tip::find($_GET['id']);
		$res->status 	= $res->status == 0 ? 1 : 0;
		$res->save();

		return redirect(env('admin').'/tips')->with('message','Status Updated Successfully.');

	}
}

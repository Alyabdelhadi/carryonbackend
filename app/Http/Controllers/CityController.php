<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Country;
use Illuminate\Http\Request;

class CityController extends Controller
{
    public $folder  = "cities.";

    /*
    |---------------------------------------
    |@Showing all records
    |---------------------------------------
    */
    public function index(Request $request)
    {
        $query = City::with('country');
    
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
    
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', '%' . $search . '%')
                  ->orWhereHas('country', function ($q2) use ($search) {
                      $q2->where('name', 'LIKE', '%' . $search . '%');
                  });
            });
        }
    
        $cities = $query->paginate(20);
    
        return view($this->folder . 'index', [
            'data' => $cities,
            'link' => 'cities/'
        ]);
    }	

    /*
    |---------------------------------------
    |@Add new page
    |---------------------------------------
    */
    public function show()
    {	
        $countries = Country::orderBy('name')->pluck('name', 'id');

        return View($this->folder.'add', [
            'data' => new City,
            'form_url' => env('admin') . '/cities',
            'countries' => $countries
        ]);
    }

    /*
    |---------------------------------------
    |@Save data in DB
    |---------------------------------------
    */
    public function store(Request $Request)
    {			
        $data = new City;	

        $data->addNew($Request->all(), "add");
        
        return redirect(env('admin') . '/cities')->with('message', 'New Record Added Successfully.');
    }

    /*
    |---------------------------------------
    |@Edit Page 
    |---------------------------------------
    */
    public function edit($id)
    {	
        $countries = Country::orderBy('name')->pluck('name', 'id');

        return View($this->folder.'edit', [
            'data' => City::find($id),
            'form_url' => env('admin') . '/cities/' . $id,
            'countries' => $countries
        ]);
    }

    /*
    |---------------------------------------
    |@update data in DB
    |---------------------------------------
    */
    public function update(Request $Request, $id)
    {	
        $data = new City;

        $data->addNew($Request->all(), $id);
        
        return redirect(env('admin') . '/cities')->with('message', 'Record Updated Successfully.');
    }

    /*
    |---------------------------------------------
    |@Delete Data
    |---------------------------------------------
    */
    public function delete($id)
    {
        City::where('id', $id)->delete();

        return redirect(env('admin') . '/cities')->with('message', 'Record Deleted Successfully.');
    }
    
    public function cityStatus()
	{
		$res 			= City::find($_GET['id']);
		$res->status 	= $res->status == 0 ? 1 : 0;
		$res->save();

		return redirect(env('admin').'/cities')->with('message','Status Updated Successfully.');

	}
}
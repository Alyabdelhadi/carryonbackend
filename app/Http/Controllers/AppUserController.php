<?php

namespace App\Http\Controllers;

use App\Models\AppUser;
use Illuminate\Http\Request;
use App\Models\EmailTemplate;
use App\Services\TemplateService;
use Illuminate\Support\Facades\Mail;
use App\Models\ParcelOrder;

use Illuminate\Support\Facades\Validator;

class AppUserController extends Controller
{
    public $folder  = "users.";
	
	/*
	|---------------------------------------
	|@Showing all records
	|---------------------------------------
	*/
	public function index()
    {
        $res = new AppUser;
    
        $perPage = request()->get('per_page', 50);
        $search = request()->get('search');
    
        return view($this->folder . 'index', [
            'data' => $res->getAllByRolePaginated(1, $perPage, $search),
            'link' => 'users/',
            'title' => 'All Users',
        ]);
    }
    
    public function active()
    {
        $res = new AppUser;
    
        $perPage = request()->get('per_page', 50);
        $search = request()->get('search');
    
        return view($this->folder . 'index', [
            'data' => $res->getAllByRolePaginated(1, $perPage, $search, 1),
            'link' => 'users/active',
            'title' => 'Active Users',
        ]);
    }
    
    public function inactive()
    {
        $res = new AppUser;
    
        $perPage = request()->get('per_page', 50);
        $search = request()->get('search');
    
        return view($this->folder . 'index', [
            'data' => $res->getAllByRolePaginated(1, $perPage, $search, 0),
            'link' => 'users/inactive',
            'title' => 'Inactive Users',
        ]);
    }	
	
	/*
	|---------------------------------------
	|@Add new page
	|---------------------------------------
	*/
	public function show()
	{								
		return View($this->folder.'add',['data' => new AppUser,'form_url' => env('admin').'/users']);
	}
	
	/*
	|---------------------------------------
	|@Save data in DB
	|---------------------------------------
	*/
	public function store(Request $Request)
	{			
		$data = new AppUser;
		$data->signup($Request->all());
		
		return redirect(env('admin').'/users')->with('message','New Record Added Successfully.');
	}
	
	/*
	|---------------------------------------
	|@Edit Page 
	|---------------------------------------
	*/
	public function edit($id)
	{				
		return View($this->folder.'edit',['data' => AppUser::find($id),'form_url' => env('admin').'/users/'.$id]);
	}
	
	/*
	|---------------------------------------
	|@update data in DB
	|---------------------------------------
	*/
	public function update(Request $Request,$id)
	{	
		$data = new AppUser;

		$data->updateInfo($Request->all(),$id);
		
		return redirect(env('admin').'/users')->with('message','Record Updated Successfully.');
	}
	
	/*
	|---------------------------------------------
	|@Delete Data
	|---------------------------------------------
	*/
	public function delete($id)
	{
		AppUser::where('id',$id)->delete();

		return redirect(env('admin').'/users')->with('message','Record Deleted Successfully.');
	}
	
	public function userStatus()
    {
        $res = AppUser::find($_GET['id']);
    
        if (!$res) {
            return redirect(env('admin') . '/users')->with('error', 'User not found.');
        }
    
        if ($res->status == 1) {
    
            $orders = ParcelOrder::where('user_id', $res->id)->get();
    
            $ongoingStatuses = ['Assigned', 'Picked'];
    
            $hasOngoing = $orders->contains(function ($order) use ($ongoingStatuses) {
                return in_array($order->status, $ongoingStatuses);
            });
    
            if ($hasOngoing) {
                return redirect(env('admin') . '/users')
                    ->with('error', 'Cannot deactivate user with ongoing orders (Assigned or Picked).');
            }
    
            foreach ($orders as $order) {
                if ($order->status === 'Unassigned') {
                    $order->status = 'Cancelled';
                    $order->save();
                }
            }
    
            $res->status = 0;
    
        } else {
            $res->status = 1;
        }
    
        $res->save();
    
        $event = $res->status == 0 ? 'user_deactivated' : 'user_activated';
    
        $template = EmailTemplate::where('event', $event)->first();
    
        if ($template && !empty($res->email)) {
    
            $validator = Validator::make(
                ['email' => $res->email],
                ['email' => 'required|email']
            );
    
            if ($validator->passes()) {
    
                try {
    
                    $parsedSubject = TemplateService::parseWithUserId($template->title, $res->id);
                    $parsedBody = TemplateService::parseWithUserId($template->body, $res->id);
    
                    Mail::raw($parsedBody, function ($message) use ($res, $parsedSubject) {
                        $message->to($res->email)
                            ->subject($parsedSubject);
                    });
    
                } catch (\Exception $e) {
                    // Ignore email sending errors
                }
            }
        }
    
        return redirect()->back()->with('message', 'Status Updated Successfully.');
    }
}

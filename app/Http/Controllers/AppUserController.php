<?php

namespace App\Http\Controllers;

use App\Models\AppUser;
use App\Services\IdentityVerificationService;
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

    /** Accounts whose selfie + ID wait for a decision (manual review, or Shufti still deciding). */
    public function pendingVerification()
    {
        $res = new AppUser;

        $perPage = request()->get('per_page', 50);
        $search = request()->get('search');

        return view($this->folder . 'index', [
            'data' => $res->getAllByRolePaginated(1, $perPage, $search, null, AppUser::IDENTITY_PENDING),
            'link' => 'users/',
            'title' => 'Awaiting identity review',
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
	/*
	|---------------------------------------------
	|@Delete the user and everything they own
	|(see App\Services\AppUserPurgeService)
	|---------------------------------------------
	*/
	public function delete($id, \App\Services\AppUserPurgeService $purge)
	{
		$user = AppUser::find($id);
		if (!$user) {
			return redirect(env('admin').'/users')->with('error', 'User not found.');
		}
		if ($reason = $purge->blocker($user)) {
			return redirect()->back()->with('error', 'Cannot delete ' . $user->name . ': ' . $reason);
		}
		$removed = $purge->purge($user);

		return redirect(env('admin').'/users')->with('message', $user->name . ' was deleted with ' . $removed['orders'] . ' package(s) and ' . $removed['trips'] . ' trip(s).');
	}
	
	/*
	|---------------------------------------------
	|@Mark identity verified / not verified by hand
	|(for people Shufti could not check)
	|---------------------------------------------
	*/
	/*
	|---------------------------------------------
	|@Identity document, for signed-in admins only
	|(upload/identities is not reachable on the web)
	|---------------------------------------------
	*/
	public function identityFile($id)
	{
		$user = AppUser::find($id);
		$path = $user && $user->identity ? base_path(AppUser::IDENTITY_DIR . '/' . basename($user->identity)) : null;
		if (!$path || !is_file($path)) {
			abort(404);
		}
		return response()->file($path, [
			'Cache-Control' => 'private, no-store',
			'X-Content-Type-Options' => 'nosniff',
		]);
	}

	/**
	 * GET userVerification?id=&action=approve|reject|revoke
	 * approve / reject settle a pending review (the user gets a push);
	 * revoke sends a verified user back to "not verified". Without an
	 * action the link toggles, as before.
	 */
	public function userVerification()
	{
		$user = AppUser::find($_GET['id'] ?? null);
		if (!$user) {
			return redirect(env('admin') . '/users')->with('error', 'User not found.');
		}

		$action = $_GET['action'] ?? ($user->is_verified ? 'revoke' : 'approve');
		if ($action === 'revoke') {
			$user->identity_status = null;
			$user->identity_verified_at = null;
			$user->save();
			$message = 'Identity verification removed. The user must verify again in the app.';
		} elseif ($action === 'approve' || $action === 'reject') {
			app(IdentityVerificationService::class)->review($user, $action === 'approve');
			$message = $action === 'approve'
				? 'User marked as verified.'
				: 'Verification rejected. The user will be asked to upload new photos.';
		} else {
			return redirect()->back()->with('error', 'Unknown action.');
		}

		return redirect()->back()->with('message', $message);
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

<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\AppUser;
use App\Models\ParcelOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;

class AdminController extends Controller
{
    /*
	|------------------------------------------------------------------
	|Index page for login
	|------------------------------------------------------------------
	*/
	public function index()
	{
		return View('index',['form_url' => Asset('login')]);
	}

    /*
	|------------------------------------------------------------------
	|Login attempt,check username & password
	|------------------------------------------------------------------
	*/
	public function login(Request $request)
	{
		$username = $request->input('username');
		$password = $request->input('password');
		
		if (Auth::attempt(['username' => $username , 'password' => $password] ))
		{
			return Redirect::to('home')->with('message', 'Welcome! Your are logged in now.');
		}
		else
		{
			return Redirect::to('login')->with('error', 'Username password not match')->withInput();
		}
	}

	/*
	|------------------------------------------------------------------
	|Homepage, Dashboard
	|------------------------------------------------------------------
	*/
	public function home()
	{	
		$user  = new User;

		return View('dashboard.home',[
			'data' 	=> $user->overview()			
		]);
	}


	/*
	|------------------------------------------------------------------
	|Logout
	|------------------------------------------------------------------
	*/
	public function logout()
	{
		Auth::logout();
		
		return Redirect::to('login')->with('message', 'Logout Successfully!');
	}

	/*
	|------------------------------------------------------------------
	|Account setting's page
	|------------------------------------------------------------------
	*/
	public function setting()
	{
		
		return View('dashboard.setting',[
			'data'	=> auth()->user(),
            'form_url'	=> Asset('setting')
		]);
	}
	
	/*
	|------------------------------------------------------------------
	|update account setting's
	|------------------------------------------------------------------
	*/
	public function update(Request $Request)
	{		
		$admin = new User;

		if($admin->matchPassword($Request->get('password')))
		{
			return Redirect::back()->with('error','Opps! Your current password is not match.');
		}
		else
		{
			$admin->updateData($Request->all());

			return Redirect::back()->with('message','Account Information Updated Successfully.');
		}
	}
}

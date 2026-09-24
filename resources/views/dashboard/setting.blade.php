@extends('layout.main')

@section('title') Settings @endsection

@section('content')

<section id="basic-input">
<div class="row">
<div class="col-md-10">
<form action="{{ $form_url }}" method="post">

<div class="card">
<div class="card-content">
<div class="card-body">

<h1 style="font-weight: bold;">Settings</h1>

{{ csrf_field() }}

<div class="row">
<div class="col-xl-6">
<fieldset class="form-group">
<label for="basicInput">Name</label>
<input type="text" class="form-control" id="basicInput" value="{{ $data->name }}" required="required" name="name">
</fieldset>
</div>
<div class="col-xl-6">
<fieldset class="form-group">
<label for="helpInputTop">Email</label>
<input type="email" class="form-control" id="helpInputTop" value="{{ $data->email }}" required="required" name="email">
</fieldset>
</div>

<div class="col-xl-6">
<fieldset class="form-group">
<label for="disabledInput">Username</label>
<input type="text" class="form-control" required="required" value="{{ $data->username }}" name="username">
</fieldset>
</div>
</div>
</div>
</div>
</div>

<div class="card">
<div class="card-content">
<div class="card-body">

<h1 style="font-weight: bold;">App Settings</h1>

<div class="row">

<div class="col-xl-6">
<fieldset class="form-group">
<label for="basicInput">Referral Points</label>
<input type="text" name="point_who" class="form-control" value="{{ $data->point_who }}">
</fieldset>
</div>

<div class="col-xl-6">
<fieldset class="form-group">
<label for="basicInput">Referred Points</label>
<input type="text" name="point_use" class="form-control" value="{{ $data->point_use }}">
</fieldset>
</div>


</div>

</div>
</div>
</div>



<div class="card">
<div class="card-content">
<div class="card-body">

<h1 style="font-weight: bold;">Choose Password</h1>


<div class="row">
<div class="col-xl-6">
<fieldset class="form-group">
<label for="basicInput">Current Password <small>(Please enter your current password if you want to update any information.)</small></label>
<input type="password" class="form-control" required="required" name="password">
</fieldset>
</div>

<div class="col-xl-6">
<fieldset class="form-group">
<label for="basicInput">Change Password <small>(Leave it empty if you don't want to change password.)</small></label>
<input type="password" class="form-control" name="new_password">
</fieldset>
</div>
</div>

<button type="submit" class="btn btn-primary mr-1 mb-1 waves-effect waves-light">Save</button>
</div>
</div>
</div>
</form>

</div>
</div>
</section>

@endsection
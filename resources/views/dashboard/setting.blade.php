@extends('layout.main')

@section('title') Account settings @endsection

@section('content')
<x-admin.page-header title="Account settings" subtitle="Your own sign-in details." />

<form action="{{ $form_url }}" method="post">
    {{ csrf_field() }}
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header"><h4 class="card-title"><i class="feather icon-user mr-50"></i> Profile</h4></div>
                <div class="card-body">
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="set-name">Name</label>
                            <input type="text" class="form-control" id="set-name" value="{{ $data->name }}" required="required" name="name">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="set-email">Email</label>
                            <input type="email" class="form-control" id="set-email" value="{{ $data->email }}" required="required" name="email">
                        </div>
                        <div class="form-group col-md-6 mb-0">
                            <label for="set-username">Username</label>
                            <input type="text" class="form-control" id="set-username" required="required" value="{{ $data->username }}" name="username">
                        </div>
                        <div class="form-group col-md-6 mb-0">
                            <label>Group</label>
                            <input type="text" class="form-control" value="{{ $data->group->name ?? 'No group' }}" readonly>
                        </div>
                    </div>
                </div>
            </div>

            @if(auth()->user()->isSuperAdmin())
                <div class="card">
                    <div class="card-header"><h4 class="card-title"><i class="feather icon-gift mr-50"></i> Referral points</h4></div>
                    <div class="card-body">
                        <div class="form-row">
                            <div class="form-group col-md-6 mb-0">
                                <label for="set-point-who">Referral points</label>
                                <input type="text" id="set-point-who" name="point_who" class="form-control" value="{{ $data->point_who }}">
                                <small class="form-text">Given to the user whose code was used.</small>
                            </div>
                            <div class="form-group col-md-6 mb-0">
                                <label for="set-point-use">Referred points</label>
                                <input type="text" id="set-point-use" name="point_use" class="form-control" value="{{ $data->point_use }}">
                                <small class="form-text">Given to the new user who signed up with a code.</small>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <div class="card">
                <div class="card-header"><h4 class="card-title"><i class="feather icon-lock mr-50"></i> Password</h4></div>
                <div class="card-body">
                    <div class="form-row">
                        <div class="form-group col-md-6 mb-0">
                            <label for="set-password">Current password</label>
                            <input type="password" id="set-password" class="form-control" required="required" name="password" autocomplete="current-password">
                            <small class="form-text">Needed to save any change on this page.</small>
                        </div>
                        <div class="form-group col-md-6 mb-0">
                            <label for="set-new-password">New password</label>
                            <input type="password" id="set-new-password" class="form-control" name="new_password" autocomplete="new-password">
                            <small class="form-text">Leave empty to keep your password.</small>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary co-btn-icon-text"><i class="feather icon-check"></i> Save</button>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

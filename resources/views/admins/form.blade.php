@extends('layout.main')

@php $editing = $admin->exists; $isSelf = $editing && $admin->id === auth()->id(); @endphp

@section('title') {{ $editing ? 'Edit admin' : 'New admin' }} @endsection

@section('content')
<x-admin.page-header :title="$editing ? 'Edit ' . $admin->name : 'New admin'" subtitle="The group decides which pages this admin can view, create, edit and delete.">
    <a href="{{ Asset('admins') }}" class="btn btn-light co-btn-icon-text"><i class="feather icon-arrow-left"></i> Back</a>
</x-admin.page-header>

<form method="POST" action="{{ $editing ? Asset('admins/'.$admin->id) : Asset('admins') }}" autocomplete="off">
    @csrf
    @if($editing) @method('PUT') @endif

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header"><h4 class="card-title">Account</h4></div>
                <div class="card-body">
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="name">Full name</label>
                            <input id="name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $admin->name) }}" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group col-md-6">
                            <label for="username">Username</label>
                            <input id="username" name="username" class="form-control @error('username') is-invalid @enderror" value="{{ old('username', $admin->username) }}" required>
                            <small class="form-text">Used to sign in. Letters, numbers, - and _.</small>
                            @error('username')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group col-md-12">
                            <label for="email">Email</label>
                            <input id="email" type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $admin->email) }}" required>
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group col-md-6">
                            <label for="password">{{ $editing ? 'New password' : 'Password' }}</label>
                            <input id="password" type="password" name="password" class="form-control @error('password') is-invalid @enderror" autocomplete="new-password" {{ $editing ? '' : 'required' }}>
                            <small class="form-text">{{ $editing ? 'Leave empty to keep the current password.' : 'At least 8 characters.' }}</small>
                            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group col-md-6">
                            <label for="password_confirmation">Confirm password</label>
                            <input id="password_confirmation" type="password" name="password_confirmation" class="form-control" autocomplete="new-password">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header"><h4 class="card-title">Access</h4></div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="admin_group_id">Group</label>
                        <select id="admin_group_id" name="admin_group_id" class="form-control @error('admin_group_id') is-invalid @enderror" {{ $isSelf ? 'disabled' : 'required' }}>
                            <option value="">Choose a group…</option>
                            @foreach($groups as $group)
                                <option value="{{ $group->id }}" @selected((int) old('admin_group_id', $admin->admin_group_id) === $group->id)>{{ $group->name }}</option>
                            @endforeach
                            @if($isSelf && $admin->group && !$groups->contains('id', $admin->admin_group_id))
                                <option selected>{{ $admin->group->name }}</option>
                            @endif
                        </select>
                        @if($isSelf)<small class="form-text">You cannot change your own group.</small>@endif
                        @error('admin_group_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1"
                            @checked(old('is_active', $admin->is_active ?? true)) {{ $isSelf ? 'disabled' : '' }}>
                        <label class="custom-control-label" for="is_active">Can sign in</label>
                    </div>
                    <small class="form-text">Turn off to block this admin without deleting the account.</small>
                </div>
                <div class="card-footer d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary co-btn-icon-text"><i class="feather icon-check"></i> {{ $editing ? 'Save changes' : 'Create admin' }}</button>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

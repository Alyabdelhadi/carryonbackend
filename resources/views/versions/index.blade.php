@extends('layout.main')

@section('title') App Versions @endsection

@php $canEdit = auth()->user()->can('versions.edit'); @endphp

@section('content')
<x-admin.page-header title="App versions" subtitle="The latest store versions. Older installs are asked to update." />

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="row">
    <div class="col-lg-8">
        <form action="{{ route('update') }}" method="POST">
            @csrf
            <div class="card">
                <div class="card-header"><h4 class="card-title"><i class="feather icon-smartphone mr-50"></i> Store versions</h4></div>
                <div class="card-body">
                    <fieldset @disabled(!$canEdit)>
                        <div class="form-row">
                            <div class="form-group col-md-6 mb-0">
                                <label for="versionIos">iOS version</label>
                                <input type="text" id="versionIos" name="ios" class="form-control" value="{{ old('ios', $version->ios ?? '') }}" required placeholder="e.g. 1.2.3">
                            </div>
                            <div class="form-group col-md-6 mb-0">
                                <label for="versionAndroid">Android version</label>
                                <input type="text" id="versionAndroid" name="android" class="form-control" value="{{ old('android', $version->android ?? '') }}" required placeholder="e.g. 2.3.4">
                            </div>
                        </div>
                    </fieldset>
                </div>
                @if($canEdit)
                    <div class="card-footer d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary co-btn-icon-text"><i class="feather icon-check"></i> Save versions</button>
                    </div>
                @endif
            </div>
        </form>
    </div>
</div>
@endsection

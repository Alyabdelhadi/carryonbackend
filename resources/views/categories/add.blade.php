@extends('layout.main')

@section('title') Add Parcel Category @endsection

@section('content')
<x-admin.page-header title="Add Parcel Category" subtitle="Name, Arabic name, image and description.">
    <a href="{{ Asset('parcel_cate') }}" class="btn btn-light co-btn-icon-text"><i class="feather icon-arrow-left"></i> Back</a>
</x-admin.page-header>

<div class="row">
<div class="col-xl-9">
<div class="card">
<div class="card-header">
<h4 class="card-title">Parcel Category details</h4>
</div>

{!! Form::model($data, ['url' => [$form_url],'files' => true]) !!}

@include('categories.form')

</form>
</div>
</div>
</div>
@endsection

@extends('layout.main')

@section('title') Edit City @endsection

@section('content')
<x-admin.page-header title="Edit City" subtitle="Name, Arabic name, country and image.">
    <a href="{{ Asset('cities') }}" class="btn btn-light co-btn-icon-text"><i class="feather icon-arrow-left"></i> Back</a>
</x-admin.page-header>

<div class="row">
<div class="col-xl-9">
<div class="card">
<div class="card-header">
<h4 class="card-title">City details</h4>
</div>

{!! Form::model($data, ['url' => [$form_url],'files' => true,'method' => 'PATCH']) !!}

@include('cities.form')

</form>
</div>
</div>
</div>
@endsection

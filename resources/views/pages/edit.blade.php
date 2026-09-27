@extends('layout.main')

@section('title') Edit Page @endsection

@section('content')
<x-admin.page-header title="Edit Page" subtitle="Title and content shown in the app.">
    <a href="{{ Asset('pages') }}" class="btn btn-light co-btn-icon-text"><i class="feather icon-arrow-left"></i> Back</a>
</x-admin.page-header>

<div class="row">
<div class="col-xl-10">
<div class="card">
<div class="card-header">
<h4 class="card-title">Page details</h4>
</div>

{!! Form::model($data, ['url' => [$form_url],'files' => true,'method' => 'PATCH']) !!}

@include('pages.form')

</form>
</div>
</div>
</div>
@endsection

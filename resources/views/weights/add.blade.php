@extends('layout.main')

@section('title') Add Weight @endsection

@section('content')
<x-admin.page-header title="Add Weight" subtitle="A package weight and where the app offers it.">
    <a href="{{ Asset('weights') }}" class="btn btn-light co-btn-icon-text"><i class="feather icon-arrow-left"></i> Back</a>
</x-admin.page-header>

<div class="row">
<div class="col-xl-9">
<div class="card">
<div class="card-header">
<h4 class="card-title">Weight details</h4>
</div>

{!! Form::model($data, ['url' => [$form_url],'files' => true]) !!}

@include('weights.form')

</form>
</div>
</div>
</div>
@endsection

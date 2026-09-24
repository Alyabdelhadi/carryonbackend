@extends('layout.main')

@section('title') Edit Country @endsection

@section('content')

<section id="basic-input">
<div class="row">
<div class="col-md-8">
<div class="card">
<div class="card-header">
<h4 class="card-title">Edit Country</h4>
</div>

{!! Form::model($data, ['url' => [$form_url],'files' => true,'method' => 'PATCH']) !!}

@include('countries.form')

</form>

</div>
</div>
</div>
</section>

@endsection
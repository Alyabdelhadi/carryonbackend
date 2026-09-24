@extends('layout.main')

@section('title') App Text @endsection

@section('content')

<section id="basic-input">
<div class="row">
<div class="col-md-12">
<div class="card">
<div class="card-header">
<h4 class="card-title">Add App Text</h4>
</div>

{!! Form::open(['url' => [Asset('texts')],'files' => true]) !!}

@include('texts.form')

</form>
</div>
</div>
</div>
</section>

@endsection
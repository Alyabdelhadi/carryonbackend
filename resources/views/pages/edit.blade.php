@extends('layout.main')

@section('title') Edit Page @endsection

@section('content')

<section id="basic-input">
<div class="row">
<div class="col-md-8">
<div class="card">
<div class="card-header">
<h4 class="card-title">Edit Page</h4>
</div>

{!! Form::model($data, ['url' => [$form_url],'files' => true,'method' => 'PATCH']) !!}

@include('pages.form')

</form>

</div>
</div>
</div>
</section>

@endsection
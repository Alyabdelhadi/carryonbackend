@extends('layout.main')

@section('title') Edit Payment Method @endsection

@section('content')

<section id="basic-input">

<div class="row">

<div class="col-md-8">

<div class="card">

<div class="card-header">
    <h4 class="card-title">
        Configure {{ $data->name }}
    </h4>
</div>

{!! Form::model(
    $data,
    [
        'url' => [$form_url],
        'method' => 'PATCH'
    ]
) !!}

@include('payment_methods.form')

</form>

</div>
</div>
</div>

</section>

@endsection
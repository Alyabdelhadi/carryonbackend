@extends('layout.main')

@section('title') Edit Payment Method @endsection

@section('content')

<x-admin.page-header :title="'Configure ' . $data->name" subtitle="Changes apply to new orders in the app right away.">
    <a href="{{ Asset($link) }}" class="btn btn-light co-btn-icon-text"><i class="feather icon-arrow-left"></i> Back</a>
</x-admin.page-header>

{!! Form::model(
    $data,
    [
        'url' => [$form_url],
        'method' => 'PATCH'
    ]
) !!}

@include('payment_methods.form')

</form>

@endsection

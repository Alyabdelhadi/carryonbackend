@extends('layout.main')

@section('title') Add User @endsection

@section('content')

<x-admin.page-header title="Add user" subtitle="Create an app account by hand.">
    <a href="{{ Asset('users') }}" class="btn btn-light co-btn-icon-text"><i class="feather icon-arrow-left"></i> Back</a>
</x-admin.page-header>

{!! Form::model($data, ['url' => [$form_url],'files' => true]) !!}

@include('users.form')

</form>

@endsection

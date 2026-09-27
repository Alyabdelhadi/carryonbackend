@extends('layout.main')

@section('title') New notification template @endsection

@section('content')
<x-admin.page-header title="New notification template">
    <a href="{{ url('notifications') }}" class="btn btn-light co-btn-icon-text"><i class="feather icon-arrow-left"></i> Back</a>
</x-admin.page-header>

<div class="row">
    <div class="col-lg-8">
        {!! Form::model($data, ['url' => route('notifications.store'), 'files' => true]) !!}
            @include('notifications.form')
        </form>
    </div>
</div>
@endsection

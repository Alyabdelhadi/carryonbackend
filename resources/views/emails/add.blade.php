@extends('layout.main')

@section('title') New email template @endsection

@section('content')
<x-admin.page-header title="New email template">
    <a href="{{ url('emails') }}" class="btn btn-light co-btn-icon-text"><i class="feather icon-arrow-left"></i> Back</a>
</x-admin.page-header>

<div class="row">
    <div class="col-lg-8">
        {!! Form::model($data, ['url' => route('emails.store'), 'files' => true]) !!}
            @include('emails.form')
        </form>
    </div>
</div>
@endsection

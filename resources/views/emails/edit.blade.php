@extends('layout.main')

@section('title') Edit email template @endsection

@section('content')
<x-admin.page-header title="Edit email template">
    <a href="{{ url('emails') }}" class="btn btn-light co-btn-icon-text"><i class="feather icon-arrow-left"></i> Back</a>
</x-admin.page-header>

<div class="row">
    <div class="col-lg-8">
        {!! Form::model($data, ['url' => [$form_url], 'files' => true, 'method' => 'PATCH']) !!}
            @include('emails.form')
        </form>
    </div>
</div>
@endsection

@extends('layout.main')

@section('title') Add Tip @endsection

@section('content')
<x-admin.page-header title="Add Tip" subtitle="A reward amount senders can offer carriers.">
    <a href="{{ Asset('tips') }}" class="btn btn-light co-btn-icon-text"><i class="feather icon-arrow-left"></i> Back</a>
</x-admin.page-header>

<div class="row">
<div class="col-xl-9">
<div class="card">
<div class="card-header">
<h4 class="card-title">Tip details</h4>
</div>

{!! Form::model($data, ['url' => [$form_url],'files' => true]) !!}

@include('tips.form')

</form>
</div>
</div>
</div>
@endsection

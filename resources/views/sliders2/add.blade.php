@extends('layout.main')

@section('title') Add Slider @endsection

@section('content')
<x-admin.page-header title="Add Slider" subtitle="Banner image (and Arabic version) for the second row.">
    <a href="{{ Asset('sliders2') }}" class="btn btn-light co-btn-icon-text"><i class="feather icon-arrow-left"></i> Back</a>
</x-admin.page-header>

<div class="row">
<div class="col-xl-9">
<div class="card">
<div class="card-header">
<h4 class="card-title">Slider details</h4>
</div>

{!! Form::model($data, ['url' => [$form_url],'files' => true]) !!}

@include('sliders2.form')

</form>
</div>
</div>
</div>
@endsection

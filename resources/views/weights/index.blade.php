@extends('layout.main')

@section('title') Weights @endsection

@section('content')

<section id="basic-input">
<div class="row">
<div class="col-md-12">
<div class="card">

<div class="row" id="table-head">
<div class="col-12">
<div class="card">
<div class="card-content">

<div class="card-body"><h4 class="card-title">Weights<a href="{{ Asset($link.'add') }}" class="btn btn-primary" style="float: right">Add New</a></h4> </div>
<div class="table-responsive">
<table class="table mb-0">
<thead >
<tr>
<th>Sort No</th>
<th>Value</th>
<th>Status</th>
<th class="text-right">Options</th>
</tr>
</thead>
<tbody>

@foreach($data as $row)
<tr>
<td width="10%">{{ $row->sort_no }}</td>
<td width="17%">{{ $row->value }}</td>
<td width="17%">

<a onclick="return confirm('Are you sure?')" href="{{ Asset('weightStatus?id='.$row->id) }}">
@if($row->status == 1)

<div class="chip chip-success mr-1">
<div class="chip-body">
<span class="chip-text">Active</span>
</div>
</div>

@else

<div class="chip chip-danger mr-1">
<div class="chip-body">
<span class="chip-text">Inactive</span>
</div>
</div>

@endif
</a>

</td>
<td width="17%" class="text-right">

<a class="btn btn-icon btn-info mr-1 mb-1 waves-effect waves-light" data-toggle="tooltip" data-placement="top" data-original-title="@lang('app.edit')" href="{{ Asset($link.$row->id.'/edit') }}"><i class="feather icon-edit"></i></a>

<a type="button" class="btn btn-icon btn-danger mr-1 mb-1 waves-effect waves-light" data-toggle="tooltip" data-placement="top" data-original-title="@lang('app.delete')" onclick="confirmAlert('{{ Asset($link.'delete/'.$row->id) }}')"><i class="feather icon-trash-2"></i></a>

</td>
</tr>
@endforeach

</tbody>
</table>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</section>

@endsection
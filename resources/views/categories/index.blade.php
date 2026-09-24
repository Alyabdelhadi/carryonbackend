@extends('layout.main')

@section('title') Parcel Categories @endsection

@section('content')

<section id="basic-input">
<div class="row">
<div class="col-md-12">
<div class="card">

<div class="row" id="table-head">
<div class="col-12">
<div class="card">
<div class="card-content">

<div class="card-body"><h4 class="card-title">Parcel Categories<a href="{{ Asset($link.'add') }}" class="btn btn-primary" style="float: right">Add New</a></h4> </div>
<div class="table-responsive">
<table class="table mb-0">
<thead >
<tr>
<th>Sort No</th>
<th>Image</th>
<th>Category Name</th>
<th>Arabic Name</th>
<th>Description</th>
<th>Status</th>
<th class="text-right">Options</th>
</tr>
</thead>
<tbody>

@foreach($data as $row)
<tr>
<td width="10%">{{ $row->sort_no }}</td>
<td width="10%"><img src="{{ Asset('upload/categories/'.$row->img) }}" height="50px"></td>
<td width="20%">{{ $row->name }}</td>
<td width="15%" dir="rtl">{{ $row->name_ar }}</td>
<td width="20%">{{ $row->text }}</td>
<td width="20%">
<a href="{{ Asset('parcelCateStatus?id='.$row->id) }}" onclick="return confirm('Are you sure?')">
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
<td width="20%" class="text-right">

<a class="btn btn-icon btn-info mr-1 mb-1 waves-effect waves-light" data-toggle="tooltip" data-placement="top" data-original-title="Edit" href="{{ Asset($link.$row->id.'/edit') }}"><i class="feather icon-edit"></i></a>

<a type="button" class="btn btn-icon btn-danger mr-1 mb-1 waves-effect waves-light" data-toggle="tooltip" data-placement="top" data-original-title="Delete" onclick="confirmAlert('{{ Asset($link.'delete/'.$row->id) }}')"><i class="feather icon-trash-2"></i></a>

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
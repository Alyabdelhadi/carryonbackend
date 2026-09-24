@extends('layout.main')

@section('title') Payment Methods @endsection

@section('content')

<section id="basic-input">
<div class="row">
<div class="col-md-12">
<div class="card">

<div class="row" id="table-head">
<div class="col-12">
<div class="card">
<div class="card-content">

<div class="card-body">
    <h4 class="card-title">Payment Methods</h4>
</div>

<div class="table-responsive">
<table class="table mb-0">

<thead>
<tr>
    <th>Payment Method</th>
    <th>Code</th>
    <th>Currency</th>
    <th>Status</th>
    <th class="text-right">Options</th>
</tr>
</thead>

<tbody>

@foreach($data as $row)

<tr>

<td width="25%">
    <strong>{{ $row->name }}</strong>
</td>

<td width="20%">
    {{ $row->code }}
</td>

<td width="15%">
    {{ strtoupper($row->currency) }}
</td>

<td width="15%">

    @if($row->enabled == 1)

        <div class="chip chip-success mr-1">
            <div class="chip-body">
                <span class="chip-text">Enabled</span>
            </div>
        </div>

    @else

        <div class="chip chip-danger mr-1">
            <div class="chip-body">
                <span class="chip-text">Disabled</span>
            </div>
        </div>

    @endif

</td>

<td width="20%" class="text-right">

    <a
        class="btn btn-icon btn-info mr-1 mb-1 waves-effect waves-light"
        data-toggle="tooltip"
        data-placement="top"
        data-original-title="Configure"
        href="{{ Asset($link.$row->id.'/edit') }}"
    >
        <i class="feather icon-edit"></i>
    </a>

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
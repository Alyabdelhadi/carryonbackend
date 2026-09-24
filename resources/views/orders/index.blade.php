@extends('layout.main')

@section('title') {{ $title }} @endsection

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
    <form action="{{ Asset('parcel_order') }}" method="get">
        <div class="row">
            <div class="col-12 col-md-2 mb-2 mb-md-0 d-flex align-items-center">
                <h4 class="mb-0">{{ $title }}</h4>
            </div>

            <div class="col-12 col-md-2 mb-2 mb-md-0">
                <select name="status" class="form-control">
                    <option value="all" @if($filter_status == 'all') selected @endif>All Orders</option>
                    <option value="0" @if($filter_status == 0) selected @endif>Unassigned</option>
                    <option value="1" @if($filter_status == 1) selected @endif>Running</option>
                    <option value="2" @if($filter_status == 2) selected @endif>Delivered</option>
                    <option value="3" @if($filter_status == 3) selected @endif>Cancelled</option>
                </select>
            </div>

            <div class="col-12 col-md-2 mb-2 mb-md-0">
                <input type="text" name="filter_q" class="form-control" placeholder="Sender name or phone" value="{{ $filter_q }}">
            </div>

            <div class="col-12 col-md-2 mb-2 mb-md-0">
                <input type="text" name="filter_r" class="form-control" placeholder="Receiver name or phone" value="{{ $filter_r }}">
            </div>

            <div class="col-6 col-md-2 mb-2 mb-md-0">
                <button type="submit" class="btn btn-primary btn-block">Filter</button>
            </div>

            <div class="col-6 col-md-1 text-md-left text-right d-flex align-items-center">
                @if(request()->has('filter_q') || request()->has('filter_r'))
                    <a class="text-danger small" href="{{ Asset('parcel_order?status=' . ($filter_status ?? 'all')) }}">Clear</a>
                @endif
            </div>
        </div>
    </form>
</div>

<div class="table-responsive">
@include('orders.table')
</div>

<!-- Pagination Info -->
<div class="mt-4 ml-2">
    <p>
        Showing page {{ $data->currentPage() }} of {{ $data->lastPage() }} — Total results: {{ $data->total() }}
    </p>
</div>

<!-- Pagination Controls Form -->
<form method="GET" id="pagination-controls" class="form-inline flex-nowrap mt-2 ml-2">

    {{-- Preserve filters --}}
    <input type="hidden" name="status" value="{{ request('status', 'all') }}">
    <input type="hidden" name="filter_q" value="{{ request('filter_q') }}">
    <input type="hidden" name="filter_r" value="{{ request('filter_r') }}">

    {{-- Rows per page --}}
    <label for="per_page" class="mr-2">Rows per page:</label>
    <select name="per_page" id="per_page" class="form-control mr-3" onchange="document.getElementById('pagination-controls').submit();">
        @foreach([10, 25, 50, 100] as $size)
            <option value="{{ $size }}" {{ request('per_page', 50) == $size ? 'selected' : '' }}>{{ $size }}</option>
        @endforeach
    </select>
</form>

<!-- Pagination Links -->
<div class="mt-2 ml-2">
    {{ $data->appends(request()->except('page'))->links('pagination::bootstrap-4') }}
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
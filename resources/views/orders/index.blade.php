@extends('layout.main')

@section('title') {{ $title }} @endsection

@section('content')

<x-admin.page-header :title="$title" subtitle="Every parcel order posted in the app." />

<div class="card">
    <form action="{{ Asset('parcel_order') }}" method="get" class="co-toolbar">
        <select name="status" class="form-control" style="width:auto" aria-label="Status">
            <option value="all" @if($filter_status == 'all') selected @endif>All Orders</option>
            <option value="0" @if($filter_status == 0) selected @endif>Unassigned</option>
            <option value="1" @if($filter_status == 1) selected @endif>Running</option>
            <option value="2" @if($filter_status == 2) selected @endif>Delivered</option>
            <option value="3" @if($filter_status == 3) selected @endif>Cancelled</option>
        </select>
        <div class="co-search" style="max-width:260px">
            <i class="feather icon-send"></i>
            <input type="text" name="filter_q" class="form-control" placeholder="Sender name or phone" value="{{ $filter_q }}">
        </div>
        <div class="co-search" style="max-width:260px">
            <i class="feather icon-inbox"></i>
            <input type="text" name="filter_r" class="form-control" placeholder="Receiver name or phone" value="{{ $filter_r }}">
        </div>
        <select name="per_page" class="form-control" style="width:auto" onchange="this.form.submit()" aria-label="Rows per page">
            @foreach([10, 25, 50, 100] as $size)
                <option value="{{ $size }}" {{ request('per_page', 50) == $size ? 'selected' : '' }}>{{ $size }} rows</option>
            @endforeach
        </select>
        <button type="submit" class="btn btn-primary">Filter</button>
        @if(request()->has('filter_q') || request()->has('filter_r'))
            <a class="btn btn-light" href="{{ Asset('parcel_order?status=' . ($filter_status ?? 'all')) }}">Clear</a>
        @endif
    </form>

    @if($data->isEmpty())
        <x-admin.empty icon="package" title="No orders found" message="Try another status or search." />
    @else
    <div class="table-responsive">
        @include('orders.table')
    </div>
    @endif

    <div class="d-flex flex-wrap align-items-center justify-content-between px-2 py-1" style="gap:12px">
        <span class="text-muted small">Page {{ $data->currentPage() }} of {{ $data->lastPage() }} · {{ number_format($data->total()) }} orders</span>
        {{ $data->appends(request()->except('page'))->links('pagination::bootstrap-4') }}
    </div>
</div>

@endsection

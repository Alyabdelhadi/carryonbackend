@extends('layout.main')

@section('title') Package #{{ $data->id }} @endsection

@section('css')
<style>
@media print {
    .co-sidebar, .co-topbar, .co-page-actions, .co-toast { display: none !important; }
    .co-main { margin: 0 !important; }
    .co-content { padding: 0 !important; }
    body.co-body .card { box-shadow: none; break-inside: avoid; }
}
.co-detail dt { color: var(--co-muted); font-weight: 500; font-size: 13px; }
.co-detail dd { font-weight: 600; margin-bottom: 12px; }
</style>
@endsection

@section('content')

<x-admin.page-header :title="'Package #' . $data->id" :subtitle="'Created ' . date('d-M-Y', strtotime($data->created_at))">
    @can('orders.edit')
        <a href="{{ route('parcel.edit', $data->id) }}" class="btn btn-light co-btn-icon-text"><i class="feather icon-edit-2"></i> Edit</a>
    @endcan
    <button type="button" class="btn btn-primary co-btn-icon-text" onclick="window.print()"><i class="feather icon-printer"></i> Print</button>
</x-admin.page-header>

<div class="row">
    <div class="col-lg-8">
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header"><h4 class="card-title"><i class="feather icon-send text-muted mr-50"></i> Sender</h4></div>
                    <div class="card-body">
                        <dl class="co-detail mb-0">
                            <dt>Name</dt><dd>{{ $data->s_name }}</dd>
                            <dt>Phone</dt><dd>{{ $data->s_phone }}</dd>
                            <dt>Address name</dt><dd>{{ $data->s_addressname ?: '—' }}</dd>
                            <dt>City</dt><dd>{{ $data->s_city }}, {{ $data->s_country }}</dd>
                            <dt>Street</dt><dd>{{ $data->s_street ?: '—' }}</dd>
                            <dt>Building</dt><dd>{{ $data->s_building ?: '—' }}</dd>
                            <dt>Apartment</dt><dd class="mb-0">{{ $data->s_apartment ?: '—' }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header"><h4 class="card-title"><i class="feather icon-inbox text-muted mr-50"></i> Receiver</h4></div>
                    <div class="card-body">
                        <dl class="co-detail mb-0">
                            <dt>Name</dt><dd>{{ $data->r_name }}</dd>
                            <dt>Phone</dt><dd>{{ $data->r_phone }}</dd>
                            <dt>Address name</dt><dd>{{ $data->r_addressname ?: '—' }}</dd>
                            <dt>City</dt><dd>{{ $data->r_city }}, {{ $data->r_country }}</dd>
                            <dt>Street</dt><dd>{{ $data->r_street ?: '—' }}</dd>
                            <dt>Building</dt><dd>{{ $data->r_building ?: '—' }}</dd>
                            <dt>Apartment</dt><dd class="mb-0">{{ $data->r_apartment ?: '—' }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-header"><h4 class="card-title">Notes</h4></div>
            <div class="card-body">{{ $data->notes ?: 'No notes.' }}</div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header"><h4 class="card-title"><i class="feather icon-package text-muted mr-50"></i> Package</h4></div>
            <div class="card-body">
                <dl class="co-detail mb-0">
                    <dt>Package ID</dt><dd>#{{ $data->id }}</dd>
                    <dt>Created on</dt><dd>{{ date('d-M-Y',strtotime($data->created_at)) }}</dd>
                    @if($data->order_date)
                        <dt>Needed before</dt><dd class="text-danger">{{ date('d-M-Y',strtotime($data->order_date)) }}</dd>
                    @endif
                    <dt>Status</dt><dd><span class="badge badge-info">{{ $data->status }}</span></dd>
                    <dt>Parcel type</dt><dd>{{ $cate->name ?? '—' }}</dd>
                    <dt>Price</dt><dd>${{ $data->price }}</dd>
                    <dt>Reward</dt><dd class="mb-0">${{ $data->amount }} <small class="text-muted">({{ $data->paymentLabel() }})</small></dd>
                </dl>
            </div>
        </div>
    </div>
</div>

@endsection

@extends('layout.main')

@section('title') Payment Methods @endsection

@section('content')

<x-admin.page-header title="Payment Methods" subtitle="How senders can pay for their orders in the app." />

<div class="card">
    @if($data->isEmpty())
        <x-admin.empty icon="shopping-bag" title="No payment methods" />
    @else
    <div class="table-responsive">
    <table class="table">
    <thead>
    <tr>
        <th>Payment Method</th>
        <th>Code</th>
        <th>Currency</th>
        <th>Status</th>
        <th class="text-right">Actions</th>
    </tr>
    </thead>
    <tbody>
    @foreach($data as $row)
    <tr>
        <td>
            <div class="d-flex align-items-center">
                <span class="co-stat-icon {{ $row->code === 'stripe' ? 'is-info' : 'is-leaf' }} mr-1" style="width:38px;height:38px;font-size:17px">
                    <i class="feather icon-{{ $row->code === 'stripe' ? 'credit-card' : 'dollar-sign' }}"></i>
                </span>
                <strong>{{ $row->name }}</strong>
            </div>
        </td>
        <td><code>{{ $row->code }}</code></td>
        <td>{{ strtoupper($row->currency) }}</td>
        <td>
            <span class="chip {{ $row->enabled == 1 ? 'chip-success' : 'chip-danger' }}"><span class="chip-body"><span class="chip-text">{{ $row->enabled == 1 ? 'Enabled' : 'Disabled' }}</span></span></span>
        </td>
        <td class="text-right">
            @can('payment_methods.edit')
                <a class="btn btn-sm btn-light co-btn-icon-text" href="{{ Asset($link.$row->id.'/edit') }}"><i class="feather icon-settings"></i> Configure</a>
            @endcan
        </td>
    </tr>
    @endforeach
    </tbody>
    </table>
    </div>
    @endif
</div>

@endsection

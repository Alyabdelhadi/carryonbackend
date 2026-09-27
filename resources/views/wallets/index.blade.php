@extends('layout.main')

@section('title') Wallets @endsection

@section('content')
<x-admin.page-header title="Carrier Wallets" subtitle="Earnings from card-paid deliveries. Without a search only wallets with activity are listed." />

<div class="co-stats">
    <div class="co-stat">
        <span class="co-stat-icon is-leaf"><i class="feather icon-credit-card"></i></span>
        <div>
            <div class="co-stat-value">{{ number_format($totalBalance, 2) }} <small class="text-muted" style="font-size:14px">{{ $currency }}</small></div>
            <div class="co-stat-label">Owed to carriers in total</div>
        </div>
    </div>
</div>

<div class="card">
    <form action="{{ Asset('wallets') }}" method="get" class="co-toolbar">
        <div class="co-search">
            <i class="feather icon-search"></i>
            <input type="search" name="q" class="form-control" placeholder="Name, email, phone or user ID" value="{{ $filter_q }}">
        </div>
        <button type="submit" class="btn btn-primary">Search</button>
        @if($filter_q)
            <a href="{{ Asset('wallets') }}" class="btn btn-light">Reset</a>
        @endif
    </form>

    @if($data->isEmpty())
        <x-admin.empty icon="credit-card" title="No wallets yet" />
    @else
    <div class="table-responsive">
    <table class="table">
    <thead>
    <tr>
        <th>User</th>
        <th>Balance</th>
        <th>Movements</th>
        <th class="text-right">Actions</th>
    </tr>
    </thead>
    <tbody>
    @foreach($data as $row)
    <tr>
        <td>
            <strong>{{ $row->name }}</strong> <small class="text-muted">(ID: {{ $row->id }})</small>
            <div class="text-muted small">{{ $row->email }} · {{ $row->phone }}</div>
        </td>
        <td><strong>{{ number_format($row->wallet_balance, 2) }} {{ $row->wallet_currency ?? $currency }}</strong></td>
        <td>{{ $row->movements }}</td>
        <td class="text-right">
            <a class="btn btn-sm btn-light co-btn-icon-text" href="{{ Asset('wallets/' . $row->id) }}"><i class="feather icon-arrow-right"></i> Open</a>
        </td>
    </tr>
    @endforeach
    </tbody>
    </table>
    </div>
    <div class="px-2 py-1">{{ $data->links() }}</div>
    @endif
</div>
@endsection

@extends('layout.main')

@section('title') Wallet · {{ $user->name }} @endsection

@section('content')
<x-admin.page-header :title="$user->name" :subtitle="'ID ' . $user->id . ' · ' . $user->email . ' · ' . $user->phone">
    <a href="{{ Asset('wallets') }}" class="btn btn-light co-btn-icon-text"><i class="feather icon-arrow-left"></i> Wallets</a>
</x-admin.page-header>

@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

<div class="co-stats">
    <div class="co-stat">
        <span class="co-stat-icon is-ink"><i class="feather icon-credit-card"></i></span>
        <div><div class="co-stat-value">{{ number_format($summary['balance'], 2) }} <small class="text-muted" style="font-size:14px">{{ $summary['currency'] }}</small></div><div class="co-stat-label">Balance</div></div>
    </div>
    <div class="co-stat">
        <span class="co-stat-icon is-leaf"><i class="feather icon-check-circle"></i></span>
        <div><div class="co-stat-value">{{ number_format($summary['available'], 2) }}</div><div class="co-stat-label">Available now</div></div>
    </div>
    <div class="co-stat">
        <span class="co-stat-icon is-warn"><i class="feather icon-clock"></i></span>
        <div><div class="co-stat-value">{{ number_format($summary['pending'], 2) }}</div><div class="co-stat-label">On hold</div></div>
    </div>
</div>

<div class="row">
<div class="col-lg-4">
    @can('wallets.edit')
    <div class="card">
        <div class="card-header"><h4 class="card-title">Manual adjustment</h4></div>
        <div class="card-body">
            <form method="POST" action="{{ Asset('wallets/' . $user->id . '/adjust') }}" data-co-confirm="Apply this adjustment?">
                @csrf
                <div class="form-group">
                    <label for="amount">Amount ({{ $summary['currency'] }}, negative to deduct)</label>
                    <input type="number" step="0.01" id="amount" name="amount" class="form-control" required value="{{ old('amount') }}">
                </div>
                <div class="form-group">
                    <label for="note">Reason (shown in the carrier's history)</label>
                    <input type="text" id="note" name="note" class="form-control" required maxlength="500" value="{{ old('note') }}">
                </div>
                <button type="submit" class="btn btn-primary btn-block">Apply</button>
            </form>
        </div>
    </div>
    @endcan

    @if($payouts->isNotEmpty())
    <div class="card">
        <div class="card-header"><h4 class="card-title">Payout requests</h4></div>
        <div class="card-body">
            <ul class="list-unstyled mb-0">
                @foreach($payouts as $p)
                    <li class="d-flex justify-content-between align-items-start mb-1" style="gap:8px">
                        <span>
                            #{{ $p->id }} · <strong>{{ number_format($p->amount, 2) }} {{ $p->currency }}</strong>
                            <div class="small text-muted">{{ strtoupper(str_replace('_', ' ', $p->method)) }} · {{ $p->created_at->format('Y-m-d') }}</div>
                        </span>
                        <span class="badge {{ $p->status === 'paid' ? 'badge-success' : ($p->status === 'pending' ? 'badge-warning' : 'badge-secondary') }}">{{ ucfirst($p->status) }}</span>
                    </li>
                @endforeach
            </ul>
            @can('payouts.view')
                <a class="btn btn-sm btn-outline-primary mt-1" href="{{ Asset('payouts?status=all&q=' . urlencode($user->email)) }}">Manage payouts</a>
            @endcan
        </div>
    </div>
    @endif
</div>

<div class="col-lg-8">
    <div class="card">
        <div class="card-header"><h4 class="card-title">History</h4></div>
        @if($transactions->isEmpty())
            <x-admin.empty icon="list" title="No movements yet" />
        @else
        <div class="table-responsive">
        <table class="table">
            <thead><tr><th>Date</th><th>Type</th><th>Note</th><th class="text-right">Amount</th><th class="text-right">Balance</th></tr></thead>
            <tbody>
            @foreach($transactions as $t)
                <tr>
                    <td class="text-nowrap"><small>{{ $t->created_at->format('Y-m-d H:i') }}</small></td>
                    <td>{{ ucfirst(str_replace('_', ' ', $t->type)) }}</td>
                    <td>
                        @if($t->parcel_order_id)
                            <a href="{{ Asset('parcel_order/' . $t->parcel_order_id . '/edit') }}">Package #{{ $t->parcel_order_id }}</a>
                        @endif
                        <small class="text-muted">{{ $t->note }}</small>
                        @if($t->type === 'earning' && $t->available_at && $t->available_at->isFuture())
                            <div class="small text-warning">On hold until {{ $t->available_at->format('Y-m-d') }}</div>
                        @endif
                    </td>
                    <td class="text-right text-nowrap {{ $t->amount < 0 ? 'text-danger' : 'text-success' }}"><strong>{{ $t->amount > 0 ? '+' : '' }}{{ number_format($t->amount, 2) }}</strong></td>
                    <td class="text-right">{{ number_format($t->balance_after, 2) }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
        </div>
        <div class="px-2 py-1">{{ $transactions->links() }}</div>
        @endif
    </div>
</div>
</div>
@endsection

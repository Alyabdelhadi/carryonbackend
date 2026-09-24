@extends('layout.main')

@section('title') Wallet · {{ $user->name }} @endsection

@section('content')
<section id="wallet">
<div class="row">
<div class="col-md-4">
    <div class="card">
        <div class="card-content"><div class="card-body">
            <h4><a href="{{ Asset('wallets') }}">Wallets</a> / {{ $user->name }}</h4>
            <p class="text-muted mb-3">ID {{ $user->id }} · {{ $user->email }} · {{ $user->phone }}</p>

            @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
            @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
            @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

            <h2 class="mb-0">{{ number_format($summary['balance'], 2) }} <small>{{ $summary['currency'] }}</small></h2>
            <p class="text-muted">
                Available now: <strong>{{ number_format($summary['available'], 2) }}</strong><br>
                On hold: <strong>{{ number_format($summary['pending'], 2) }}</strong>
            </p>

            <hr>
            <h5>Manual adjustment</h5>
            <form method="POST" action="{{ Asset('wallets/' . $user->id . '/adjust') }}">
                @csrf
                <div class="form-group">
                    <label>Amount ({{ $summary['currency'] }}, negative to deduct)</label>
                    <input type="number" step="0.01" name="amount" class="form-control" required value="{{ old('amount') }}">
                </div>
                <div class="form-group">
                    <label>Reason (shown in the carrier's history)</label>
                    <input type="text" name="note" class="form-control" required maxlength="500" value="{{ old('note') }}">
                </div>
                <button type="submit" class="btn btn-primary" onclick="return confirm('Apply this adjustment?')">Apply</button>
            </form>
        </div></div>
    </div>

    @if($payouts->isNotEmpty())
    <div class="card">
        <div class="card-content"><div class="card-body">
            <h5>Payout requests</h5>
            <ul class="list-unstyled mb-0">
                @foreach($payouts as $p)
                    <li class="mb-1">
                        #{{ $p->id }} · {{ number_format($p->amount, 2) }} {{ $p->currency }} · {{ strtoupper(str_replace('_', ' ', $p->method)) }}
                        · <strong>{{ ucfirst($p->status) }}</strong>
                        <small class="text-muted">{{ $p->created_at->format('Y-m-d') }}</small>
                    </li>
                @endforeach
            </ul>
            <a class="btn btn-sm btn-outline-primary mt-2" href="{{ Asset('payouts?status=all&q=' . urlencode($user->email)) }}">Manage payouts</a>
        </div></div>
    </div>
    @endif
</div>

<div class="col-md-8">
    <div class="card">
        <div class="card-content">
            <div class="card-body"><h4 class="card-title">History</h4></div>
            <div class="table-responsive">
            <table class="table mb-0">
                <thead><tr><th>Date</th><th>Type</th><th>Note</th><th class="text-right">Amount</th><th class="text-right">Balance</th></tr></thead>
                <tbody>
                @forelse($transactions as $t)
                    <tr>
                        <td><small>{{ $t->created_at->format('Y-m-d H:i') }}</small></td>
                        <td>{{ ucfirst(str_replace('_', ' ', $t->type)) }}</td>
                        <td>
                            @if($t->parcel_order_id)
                                <a href="{{ Asset('parcel_order/' . $t->parcel_order_id . '/edit') }}">Package #{{ $t->parcel_order_id }}</a>
                            @endif
                            <small class="text-muted">{{ $t->note }}</small>
                            @if($t->type === 'earning' && $t->available_at && $t->available_at->isFuture())
                                <br><small class="text-warning">On hold until {{ $t->available_at->format('Y-m-d') }}</small>
                            @endif
                        </td>
                        <td class="text-right {{ $t->amount < 0 ? 'text-danger' : 'text-success' }}">{{ $t->amount > 0 ? '+' : '' }}{{ number_format($t->amount, 2) }}</td>
                        <td class="text-right">{{ number_format($t->balance_after, 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted">No movements yet.</td></tr>
                @endforelse
                </tbody>
            </table>
            </div>
            <div class="mt-3 ml-2">{{ $transactions->links() }}</div>
        </div>
    </div>
</div>
</div>
</section>
@endsection

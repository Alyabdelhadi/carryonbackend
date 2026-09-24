@extends('layout.main')

@section('title') Payouts @endsection

@section('content')
<section id="payouts">
<div class="row">
<div class="col-12">
<div class="card">
<div class="card-content">

<div class="card-body">
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

    <form action="{{ Asset('payouts') }}" method="get">
        <div class="row">
            <div class="col-12 col-md-3 mb-2 mb-md-0 d-flex align-items-center">
                <h4 class="mb-0">Payout Requests <span class="badge badge-warning">{{ $pendingCount }} pending</span></h4>
            </div>
            <div class="col-12 col-md-2 mb-2 mb-md-0">
                <select name="status" class="form-control">
                    @foreach(['pending' => 'Pending', 'paid' => 'Paid', 'rejected' => 'Rejected', 'cancelled' => 'Cancelled', 'all' => 'All'] as $value => $label)
                        <option value="{{ $value }}" @if($filter_status == $value) selected @endif>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-3 mb-2 mb-md-0">
                <input type="text" name="q" class="form-control" placeholder="Carrier name, email or phone" value="{{ $filter_q }}">
            </div>
            <div class="col-6 col-md-2">
                <button type="submit" class="btn btn-primary btn-block">Filter</button>
            </div>
        </div>
    </form>
</div>

<div class="table-responsive">
<table class="table mb-0">
<thead>
<tr>
    <th>#</th>
    <th>Carrier</th>
    <th>Amount</th>
    <th>Method / details</th>
    <th>Status</th>
    <th>Requested</th>
    <th class="text-right">Options</th>
</tr>
</thead>
<tbody>
@forelse($data as $row)
<tr>
    <td>{{ $row->id }}</td>
    <td>
        <a href="{{ Asset('wallets/' . $row->app_user_id) }}"><strong>{{ optional($row->user)->name ?? 'User #' . $row->app_user_id }}</strong></a><br>
        <small class="text-muted">{{ optional($row->user)->email }} · {{ optional($row->user)->phone }}</small>
    </td>
    <td><strong>{{ number_format($row->amount, 2) }} {{ $row->currency }}</strong></td>
    <td>
        <strong>{{ strtoupper(str_replace('_', ' ', $row->method)) }}</strong><br>
        @foreach((array) $row->details as $key => $value)
            <small class="text-muted">{{ ucfirst(str_replace('_', ' ', $key)) }}: {{ $value }}</small><br>
        @endforeach
        @if($row->reference)<small>Ref: {{ $row->reference }}</small><br>@endif
        @if($row->admin_note)<small class="text-danger">{{ $row->admin_note }}</small>@endif
    </td>
    <td>
        @php($chip = ['pending' => 'warning', 'paid' => 'success', 'rejected' => 'danger', 'cancelled' => 'secondary'][$row->status] ?? 'secondary')
        <div class="chip chip-{{ $chip }}"><div class="chip-body"><span class="chip-text">{{ ucfirst($row->status) }}</span></div></div>
        @if($row->processed_at)<br><small class="text-muted">{{ $row->processed_at->format('Y-m-d H:i') }}</small>@endif
    </td>
    <td><small>{{ $row->created_at->format('Y-m-d H:i') }}</small></td>
    <td class="text-right" style="min-width: 260px">
        @if($row->status === 'pending')
            <form method="POST" action="{{ Asset('payouts/' . $row->id . '/paid') }}" class="mb-1">
                @csrf
                <div class="input-group input-group-sm">
                    <input type="text" name="reference" class="form-control" placeholder="Transfer reference (optional)">
                    <div class="input-group-append">
                        <button type="submit" class="btn btn-success" onclick="return confirm('Mark payout #{{ $row->id }} as paid? Do this after sending the money.')">Mark paid</button>
                    </div>
                </div>
            </form>
            <form method="POST" action="{{ Asset('payouts/' . $row->id . '/reject') }}">
                @csrf
                <div class="input-group input-group-sm">
                    <input type="text" name="admin_note" class="form-control" placeholder="Reason shown to the carrier">
                    <div class="input-group-append">
                        <button type="submit" class="btn btn-danger" onclick="return confirm('Reject payout #{{ $row->id }} and return the amount to the wallet?')">Reject</button>
                    </div>
                </div>
            </form>
        @else
            <span class="text-muted">—</span>
        @endif
    </td>
</tr>
@empty
<tr><td colspan="7" class="text-center text-muted">No payout requests.</td></tr>
@endforelse
</tbody>
</table>
</div>

<div class="mt-3 ml-2">{{ $data->links() }}</div>

</div>
</div>
</div>
</div>
</section>
@endsection

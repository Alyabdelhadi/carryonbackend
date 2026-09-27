@extends('layout.main')

@section('title') Payouts @endsection

@section('content')
<x-admin.page-header title="Payout Requests" subtitle="Carriers asking to withdraw their wallet. Send the money by hand, then mark the request paid.">
    <span class="badge badge-warning" style="font-size:13px;padding:8px 14px">{{ $pendingCount }} pending</span>
</x-admin.page-header>

@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

<div class="card">
    <form action="{{ Asset('payouts') }}" method="get" class="co-toolbar">
        <select name="status" class="form-control" style="width:auto" aria-label="Status">
            @foreach(['pending' => 'Pending', 'paid' => 'Paid', 'rejected' => 'Rejected', 'cancelled' => 'Cancelled', 'all' => 'All'] as $value => $label)
                <option value="{{ $value }}" @if($filter_status == $value) selected @endif>{{ $label }}</option>
            @endforeach
        </select>
        <div class="co-search">
            <i class="feather icon-search"></i>
            <input type="search" name="q" class="form-control" placeholder="Carrier name, email or phone" value="{{ $filter_q }}">
        </div>
        <button type="submit" class="btn btn-primary">Filter</button>
    </form>

    @if($data->isEmpty())
        <x-admin.empty icon="dollar-sign" title="No payout requests" />
    @else
    <div class="table-responsive">
    <table class="table">
    <thead>
    <tr>
        <th>#</th>
        <th>Carrier</th>
        <th>Amount</th>
        <th>Method / details</th>
        <th>Status</th>
        <th>Requested</th>
        <th class="text-right">Actions</th>
    </tr>
    </thead>
    <tbody>
    @foreach($data as $row)
    <tr>
        <td class="text-muted">#{{ $row->id }}</td>
        <td>
            <a href="{{ Asset('wallets/' . $row->app_user_id) }}"><strong>{{ optional($row->user)->name ?? 'User #' . $row->app_user_id }}</strong></a>
            <div class="text-muted small">{{ optional($row->user)->email }} · {{ optional($row->user)->phone }}</div>
        </td>
        <td class="text-nowrap"><strong>{{ number_format($row->amount, 2) }} {{ $row->currency }}</strong></td>
        <td>
            <strong>{{ strtoupper(str_replace('_', ' ', $row->method)) }}</strong>
            @foreach((array) $row->details as $key => $value)
                <div class="small text-muted">{{ ucfirst(str_replace('_', ' ', $key)) }}: {{ $value }}</div>
            @endforeach
            @if($row->reference)<div class="small">Ref: {{ $row->reference }}</div>@endif
            @if($row->admin_note)<div class="small text-danger">{{ $row->admin_note }}</div>@endif
        </td>
        <td>
            @php($chip = ['pending' => 'warning', 'paid' => 'success', 'rejected' => 'danger', 'cancelled' => 'secondary'][$row->status] ?? 'secondary')
            <span class="chip chip-{{ $chip }}"><span class="chip-body"><span class="chip-text">{{ ucfirst($row->status) }}</span></span></span>
            @if($row->processed_at)<div class="small text-muted">{{ $row->processed_at->format('Y-m-d H:i') }}</div>@endif
        </td>
        <td class="text-nowrap"><small>{{ $row->created_at->format('Y-m-d H:i') }}</small></td>
        <td class="text-right" style="min-width: 280px">
            @if($row->status === 'pending')
                @can('payouts.edit')
                    <form method="POST" action="{{ Asset('payouts/' . $row->id . '/paid') }}" class="mb-50" data-co-confirm="Mark payout #{{ $row->id }} as paid? Do this after sending the money.">
                        @csrf
                        <div class="input-group input-group-sm">
                            <input type="text" name="reference" class="form-control" placeholder="Transfer reference (optional)">
                            <div class="input-group-append">
                                <button type="submit" class="btn btn-success btn-sm">Mark paid</button>
                            </div>
                        </div>
                    </form>
                    <form method="POST" action="{{ Asset('payouts/' . $row->id . '/reject') }}" data-co-confirm="Reject payout #{{ $row->id }} and return the amount to the wallet?">
                        @csrf
                        <div class="input-group input-group-sm">
                            <input type="text" name="admin_note" class="form-control" placeholder="Reason shown to the carrier">
                            <div class="input-group-append">
                                <button type="submit" class="btn btn-danger btn-sm">Reject</button>
                            </div>
                        </div>
                    </form>
                @else
                    <span class="text-muted small">Waiting for an admin</span>
                @endcan
            @else
                <span class="text-muted">—</span>
            @endif
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

@extends('layout.main')

@section('title') Wallets @endsection

@section('content')
<section id="wallets">
<div class="row">
<div class="col-12">
<div class="card">
<div class="card-content">

<div class="card-body">
    <form action="{{ Asset('wallets') }}" method="get">
        <div class="row">
            <div class="col-12 col-md-4 mb-2 mb-md-0 d-flex align-items-center">
                <h4 class="mb-0">Carrier Wallets <small class="text-muted">· owed in total: {{ number_format($totalBalance, 2) }} {{ $currency }}</small></h4>
            </div>
            <div class="col-12 col-md-4 mb-2 mb-md-0">
                <input type="text" name="q" class="form-control" placeholder="Name, email, phone or user ID" value="{{ $filter_q }}">
            </div>
            <div class="col-6 col-md-2">
                <button type="submit" class="btn btn-primary btn-block">Search</button>
            </div>
        </div>
    </form>
    <p class="text-muted mt-2 mb-0">Without a search only wallets with activity are listed.</p>
</div>

<div class="table-responsive">
<table class="table mb-0">
<thead>
<tr>
    <th>User</th>
    <th>Balance</th>
    <th>Movements</th>
    <th class="text-right">Options</th>
</tr>
</thead>
<tbody>
@forelse($data as $row)
<tr>
    <td>
        <strong>{{ $row->name }}</strong> <small class="text-muted">(ID: {{ $row->id }})</small><br>
        <small class="text-muted">{{ $row->email }} · {{ $row->phone }}</small>
    </td>
    <td><strong>{{ number_format($row->wallet_balance, 2) }} {{ $row->wallet_currency ?? $currency }}</strong></td>
    <td>{{ $row->movements }}</td>
    <td class="text-right">
        <a class="btn btn-sm btn-info" href="{{ Asset('wallets/' . $row->id) }}">Open</a>
    </td>
</tr>
@empty
<tr><td colspan="4" class="text-center text-muted">No wallets yet.</td></tr>
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

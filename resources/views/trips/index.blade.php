@extends('layout.main')
@section('title') {{ $title ?? 'Trips' }} @endsection
@section('content')

<x-admin.page-header :title="$title ?? 'Trips'" subtitle="Trips carriers have posted in the app." />

<div class="card">
  <form method="GET" class="co-toolbar">
    <input type="text" name="from" class="form-control" style="max-width:170px" value="{{ request('from') }}" placeholder="From (Beirut)" aria-label="From">
    <input type="text" name="to" class="form-control" style="max-width:170px" value="{{ request('to') }}" placeholder="To (Dubai)" aria-label="To">
    <div class="co-search" style="max-width:220px">
      <i class="feather icon-user"></i>
      <input type="text" name="carrier" class="form-control" value="{{ request('carrier') }}" placeholder="Carrier name" aria-label="Carrier">
    </div>
    <input type="date" name="date_from" class="form-control" style="max-width:170px" value="{{ request('date_from') }}" aria-label="From date" title="From date">
    <input type="date" name="date_to" class="form-control" style="max-width:170px" value="{{ request('date_to') }}" aria-label="To date" title="To date">
    <select name="per_page" class="form-control" style="width:auto" onchange="this.form.submit()" aria-label="Rows per page">
      @foreach([10, 25, 50, 100] as $size)
        <option value="{{ $size }}" {{ request('per_page', 50) == $size ? 'selected' : '' }}>{{ $size }} rows</option>
      @endforeach
    </select>
    <button type="submit" class="btn btn-primary">Apply</button>
    @if(request()->hasAny(['from','to','carrier','date_from','date_to']))
      <a href="{{ url($link) }}" class="btn btn-light">Reset</a>
    @endif
  </form>

  @if($data->isEmpty())
    <x-admin.empty icon="navigation" title="No trips found" message="Try other filters." />
  @else
  <div class="table-responsive">
  <table class="table">
  <thead>
  <tr>
    <th>ID</th>
    <th>Carrier</th>
    <th>City From</th>
    <th>City To</th>
    <th>Frequency</th>
    <th>Date</th>
  </tr>
  </thead>
  <tbody>
  @foreach($data as $row)
  <tr>
    <td class="text-muted">#{{ $row['id'] }}</td>
    <td><strong>{{ $row['carrier'] }}</strong></td>
    <td>{{ $row['city_from'] }}<span class="text-muted">, {{ $row['country_from'] }}</span></td>
    <td>{{ $row['city_to'] }}<span class="text-muted">, {{ $row['country_to'] }}</span></td>
    <td><span class="badge badge-info">{{ $row['frequency'] }}</span></td>
    <td class="text-nowrap">{{ date('Y-m-d',strtotime($row['date'])) }}</td>
  </tr>
  @endforeach
  </tbody>
  </table>
  </div>
  @endif

  <div class="d-flex flex-wrap align-items-center justify-content-between px-2 py-1" style="gap:12px">
    <span class="text-muted small">Page {{ $data->currentPage() }} of {{ $data->lastPage() }} · {{ number_format($data->total()) }} trips</span>
    {{ $data->appends(request()->except('page'))->links('pagination::bootstrap-4') }}
  </div>
</div>

@endsection

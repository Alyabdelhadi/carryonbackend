@extends('layout.main')
@section('title') Upcoming Routes @endsection

@section('content')
<x-admin.page-header title="Upcoming Routes" subtitle="City pairs with the most upcoming trips." />

<div class="card">
  <form method="GET" class="co-toolbar">
    <input type="text" class="form-control" style="max-width:190px" name="from" value="{{ request('from') }}" placeholder="From (Beirut)" aria-label="From">
    <input type="text" class="form-control" style="max-width:190px" name="to" value="{{ request('to') }}" placeholder="To (Dubai)" aria-label="To">
    <input type="number" class="form-control" style="max-width:130px" name="min_count" value="{{ request('min_count') }}" min="0" placeholder="Min trips" aria-label="Min count">
    <select class="form-control" style="width:auto" name="limit" aria-label="Limit">
      @foreach([0,10,20,50,100] as $opt)
        <option value="{{ $opt }}" {{ (string)request('limit','0') === (string)$opt ? 'selected' : '' }}>
          {{ $opt === 0 ? 'All routes' : 'Top ' . $opt }}
        </option>
      @endforeach
    </select>
    <select class="form-control" style="width:auto" name="per_page" onchange="this.form.submit()" aria-label="Rows per page">
      @foreach([10, 25, 50, 100] as $size)
        <option value="{{ $size }}" {{ (int)request('per_page', 25) === $size ? 'selected' : '' }}>{{ $size }} rows</option>
      @endforeach
    </select>
    <button type="submit" class="btn btn-primary">Apply</button>
  </form>

  @if($routes->total() === 0)
    <x-admin.empty icon="map" title="No upcoming routes found." />
  @else
  <div class="table-responsive">
    <table class="table">
      <thead>
        <tr>
          <th>From</th>
          <th>To</th>
          <th class="text-right">Trips</th>
        </tr>
      </thead>
      <tbody>
        @foreach($routes as $r)
          <tr>
            <td>{{ $r['from']['city'] ?? '—' }}
              @if(!empty($r['from']['country']))
                <small class="text-muted">({{ $r['from']['country'] }})</small>
              @endif</td>
            <td>{{ $r['to']['city'] ?? '—' }}
              @if(!empty($r['to']['country']))
                <small class="text-muted">({{ $r['to']['country'] }})</small>
              @endif</td>
            <td class="text-right"><strong>{{ number_format((int)($r['count'] ?? 0)) }}</strong></td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  @endif

  <div class="d-flex flex-wrap align-items-center justify-content-between px-2 py-1" style="gap:12px">
    <span class="text-muted small">Page {{ $routes->currentPage() }} of {{ $routes->lastPage() }} · {{ number_format($routes->total()) }} routes</span>
    {{ $routes->appends(request()->except('page'))->links('pagination::bootstrap-4') }}
  </div>
</div>
@endsection

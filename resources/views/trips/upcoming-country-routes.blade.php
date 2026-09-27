@extends('layout.main')
@section('title') Upcoming Country Routes @endsection

@section('content')
<x-admin.page-header title="Upcoming Country Routes" subtitle="Country pairs with the most upcoming trips." />

<div class="card">
  <form method="GET" class="co-toolbar">
    <input type="text" class="form-control" style="max-width:190px" name="from" value="{{ request('from') }}" placeholder="From country (Lebanon)" aria-label="From (country)">
    <input type="text" class="form-control" style="max-width:190px" name="to" value="{{ request('to') }}" placeholder="To country (Turkey)" aria-label="To (country)">
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
    <div class="custom-control custom-checkbox">
      <input type="checkbox" class="custom-control-input" id="exclude_domestic" name="exclude_domestic" value="1" {{ request('exclude_domestic') ? 'checked' : '' }}>
      <label class="custom-control-label" for="exclude_domestic">Exclude domestic</label>
    </div>
    <button type="submit" class="btn btn-primary">Apply</button>
  </form>

  @if($routes->total() === 0)
    <x-admin.empty icon="map" title="No upcoming country routes found." />
  @else
  <div class="table-responsive">
    <table class="table">
      <thead>
        <tr>
          <th>From (Country)</th>
          <th>To (Country)</th>
          <th class="text-right">Trips</th>
        </tr>
      </thead>
      <tbody>
        @foreach($routes as $r)
          <tr>
            <td>{{ $r['from']['name'] ?? '—' }}</td>
            <td>{{ $r['to']['name'] ?? '—' }}</td>
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

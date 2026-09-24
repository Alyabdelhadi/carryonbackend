@extends('layout.main')
@section('title') Trips @endsection
@section('content')

<section id="basic-input">
<div class="row">
<div class="col-md-12">
<div class="card">

<div class="row" id="table-head">
<div class="col-12">
<div class="card">
<div class="card-content">

<div class="card-body">
  <h4 class="card-title">Trips</h4>

  {{-- Filters --}}
  <form method="GET" class="form-inline flex-wrap mt-2">
    <div class="form-group mr-2 mb-2">
      <label for="from" class="mr-1">From</label>
      <input type="text" name="from" id="from" class="form-control"
             value="{{ request('from') }}" placeholder="Beirut">
    </div>

    <div class="form-group mr-2 mb-2">
      <label for="to" class="mr-1">To</label>
      <input type="text" name="to" id="to" class="form-control"
             value="{{ request('to') }}" placeholder="Dubai">
    </div>

    <div class="form-group mr-2 mb-2">
      <label for="carrier" class="mr-1">Carrier</label>
      <input type="text" name="carrier" id="carrier" class="form-control"
             value="{{ request('carrier') }}" placeholder="Carrier name">
    </div>

    <div class="form-group mr-2 mb-2">
      <label for="date_from" class="mr-1">From date</label>
      <input type="date" name="date_from" id="date_from" class="form-control"
             value="{{ request('date_from') }}">
    </div>

    <div class="form-group mr-2 mb-2">
      <label for="date_to" class="mr-1">To date</label>
      <input type="date" name="date_to" id="date_to" class="form-control"
             value="{{ request('date_to') }}">
    </div>

    {{-- Keep your existing per_page dropdown in this same form so it submits together --}}
    <div class="form-group mr-3 mb-2">
      <label for="per_page" class="mr-1">Rows per page</label>
      <select name="per_page" id="per_page" class="form-control" onchange="this.form.submit()">
        @foreach([10, 25, 50, 100] as $size)
          <option value="{{ $size }}" {{ request('per_page', 50) == $size ? 'selected' : '' }}>{{ $size }}</option>
        @endforeach
      </select>
    </div>

    <button type="submit" class="btn btn-primary mb-2">Apply</button>

    @if(request()->hasAny(['from','to','carrier','date_from','date_to']))
      <a href="{{ url($link) }}" class="btn btn-light ml-2 mb-2">Reset</a>
    @endif
  </form>
</div>
<div class="table-responsive">
<table class="table mb-0">
<thead >
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
<td>{{ $row['id'] }}</td>
<td>{{ $row['carrier'] }}</td>
<td>{{ $row['city_from'] }}, {{ $row['country_from'] }}</td>
<td>{{ $row['city_to'] }}, {{ $row['country_to'] }}</td>
<td>{{ $row['frequency'] }}</td>
<td>{{ date('Y-m-d',strtotime($row['date'])) }}</td>
</tr>

@endforeach

</tbody>
</table>
</div>
<!-- Pagination Info -->
<div class="mt-4 ml-2">
    <p>
        Showing page {{ $data->currentPage() }} of {{ $data->lastPage() }} — Total results: {{ $data->total() }}
    </p>
</div>

<!-- Pagination Controls Form -->
<form method="GET" id="pagination-controls" class="form-inline flex-nowrap mt-2 ml-2">
    {{-- Rows per page --}}
    <label for="per_page" class="mr-2">Rows per page:</label>
    <select name="per_page" id="per_page" class="form-control mr-3" onchange="document.getElementById('pagination-controls').submit();">
        @foreach([10, 25, 50, 100] as $size)
            <option value="{{ $size }}" {{ request('per_page', 50) == $size ? 'selected' : '' }}>{{ $size }}</option>
        @endforeach
    </select>
</form>

<!-- Pagination Links -->
<div class="mt-2 ml-2">
    {{ $data->appends(request()->except('page'))->links('pagination::bootstrap-4') }}
</div>

</div>
</div>
</div>
</div>
</div>
</div>
</div>
</section>

@endsection
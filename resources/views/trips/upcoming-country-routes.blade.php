@extends('layout.main')
@section('title') Upcoming Country Routes @endsection

@section('content')
<section id="basic-input">
  <div class="row">
    <div class="col-md-12">
      <div class="card">

        <div class="row" id="table-head">
          <div class="col-12">
            <div class="card">
              <div class="card-content">

                <div class="card-body d-flex justify-content-between align-items-center flex-wrap">
                  <h4 class="card-title mb-2">Upcoming Country Routes</h4>

                  <form method="GET" class="form-inline mt-2 mt-sm-0 d-flex flex-wrap" style="gap: 10px;">
                    <div class="form-group mr-2">
                      <label for="from" class="mr-1">From (country)</label>
                      <input type="text" class="form-control" id="from" name="from"
                             value="{{ request('from') }}" placeholder="Lebanon">
                    </div>

                    <div class="form-group mr-2">
                      <label for="to" class="mr-1">To (country)</label>
                      <input type="text" class="form-control" id="to" name="to"
                             value="{{ request('to') }}" placeholder="Turkey">
                    </div>

                    <div class="form-group mr-2">
                      <label for="min_count" class="mr-1">Min count</label>
                      <input type="number" class="form-control" id="min_count" name="min_count"
                             value="{{ request('min_count') }}" min="0" placeholder="0">
                    </div>

                    <div class="form-group mr-2">
                      <label for="limit" class="mr-1">Limit</label>
                      <select class="form-control" id="limit" name="limit">
                        @foreach([0,10,20,50,100] as $opt)
                          <option value="{{ $opt }}" {{ (string)request('limit','0') === (string)$opt ? 'selected' : '' }}>
                            {{ $opt === 0 ? 'All' : $opt }}
                          </option>
                        @endforeach
                      </select>
                    </div>

                    <div class="form-group mr-2">
                      <label for="per_page" class="mr-1">Rows</label>
                      <select class="form-control" id="per_page" name="per_page" onchange="this.form.submit()">
                        @foreach([10, 25, 50, 100] as $size)
                          <option value="{{ $size }}" {{ (int)request('per_page', 25) === $size ? 'selected' : '' }}>
                            {{ $size }}
                          </option>
                        @endforeach
                      </select>
                    </div>

                    {{-- Optional: exclude domestic routes (same country -> same id) --}}
                    <div class="form-group form-check mr-2">
                      <input type="checkbox" class="form-check-input" id="exclude_domestic" name="exclude_domestic"
                             value="1" {{ request('exclude_domestic') ? 'checked' : '' }}>
                      <label class="form-check-label" for="exclude_domestic">Exclude domestic</label>
                    </div>

                    <button type="submit" class="btn btn-primary">Apply</button>
                  </form>
                </div>

                <div class="table-responsive">
                  <table class="table mb-0">
                    <thead>
                      <tr>
                        <th>From (Country)</th>
                        <th>To (Country)</th>
                        <th class="text-right">Trips</th>
                      </tr>
                    </thead>
                    <tbody>
                      @if($routes->total() === 0)
                        <tr>
                          <td colspan="3">
                            <div class="alert alert-info mb-0">No upcoming country routes found.</div>
                          </td>
                        </tr>
                      @else
                        @foreach($routes as $r)
                          <tr>
                            <td>
                              {{ $r['from']['name'] ?? '—' }}
                            </td>
                            <td>
                              {{ $r['to']['name'] ?? '—' }}
                            </td>
                            <td class="text-right">
                              {{ number_format((int)($r['count'] ?? 0)) }}
                            </td>
                          </tr>
                        @endforeach
                      @endif
                    </tbody>
                  </table>
                </div>

                {{-- Pagination summary --}}
                <div class="mt-4 ml-2">
                  <p>
                    Showing page {{ $routes->currentPage() }} of {{ $routes->lastPage() }}
                    — Total routes: {{ $routes->total() }}
                  </p>
                </div>

                {{-- Pagination links --}}
                <div class="mt-2 ml-2">
                  {{ $routes->appends(request()->except('page'))->links('pagination::bootstrap-4') }}
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
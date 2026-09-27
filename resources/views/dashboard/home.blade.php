@extends('layout.main')

@section('title') Dashboard @endsection

@php
    // one tile: [label, value, feather icon, tone, url, permission]
    $tile = function ($label, $value, $icon, $tone, $url = null, $ability = null) {
        return compact('label', 'value', 'icon', 'tone', 'url', 'ability');
    };
    $sections = [
        'Overview' => [
            $tile('Users', $data['user'], 'users', 'ink', 'users', 'users.view'),
            $tile('Carriers', $data['carriers'], 'truck', 'leaf', 'users', 'users.view'),
            $tile('Trips', $data['trips'], 'navigation', 'sky', 'trips', 'trips.view'),
            $tile('Packages', $data['order'], 'package', 'info', 'parcel_order?status=all', 'orders.view'),
            $tile('Countries', $data['countries_covered'], 'globe', 'leaf', 'countries', 'countries.view'),
            $tile('Cities', $data['cities_covered'], 'map-pin', 'sky', 'cities', 'cities.view'),
        ],
        'Users' => [
            $tile('Active', $data['active_users'], 'user-check', 'leaf', 'users/active', 'users.view'),
            $tile('Inactive', $data['inactive_users'], 'user-x', 'danger', 'users/inactive', 'users.view'),
        ],
        'Packages by status' => [
            $tile('Unassigned', $data['unassign'], 'inbox', 'warn', 'parcel_order?status=0', 'orders.view'),
            $tile('Running', $data['running'], 'truck', 'sky', 'parcel_order?status=1', 'orders.view'),
            $tile('Delivered', $data['complete'], 'check-circle', 'leaf', 'parcel_order?status=2', 'orders.view'),
            $tile('Cancelled', $data['cancel'], 'x-circle', 'danger', 'parcel_order?status=3', 'orders.view'),
            $tile('Expired', $data['expired'], 'clock', 'ink', 'parcel_order?status=4', 'orders.view'),
        ],
        'Trips' => [
            $tile('Past', $data['past'], 'rotate-ccw', 'ink'),
            $tile('Upcoming', $data['upcoming'], 'calendar', 'sky', 'trips/one-time', 'trips.view'),
            $tile('Frequent routes', $data['frequent'], 'repeat', 'leaf', 'trips/frequent', 'trips.view'),
        ],
    ];
@endphp

@section('content')
<x-admin.page-header title="Welcome back, {{ auth()->user()->name ?: auth()->user()->username }}" subtitle="Here is what is happening on CarryOn today." />

@foreach($sections as $heading => $tiles)
    <h5 class="text-muted text-uppercase mb-1" style="font-size:12px;font-weight:700;letter-spacing:.08em">{{ $heading }}</h5>
    <div class="co-stats">
        @foreach($tiles as $t)
            @php $link = $t['url'] && $t['ability'] && auth()->user()->can($t['ability']); @endphp
            <{{ $link ? 'a' : 'div' }} class="co-stat" @if($link) href="{{ Asset($t['url']) }}" @endif>
                <span class="co-stat-icon is-{{ $t['tone'] }}"><i class="feather icon-{{ $t['icon'] }}"></i></span>
                <span>
                    <span class="co-stat-value d-block">{{ number_format((float) $t['value']) }}</span>
                    <span class="co-stat-label d-block">{{ $t['label'] }}</span>
                </span>
            </{{ $link ? 'a' : 'div' }}>
        @endforeach
    </div>
@endforeach

{{-- Users evolution: daily new users (bars) and cumulative (line) --}}
<div class="card">
    <div class="co-toolbar justify-content-between">
        <div>
            <h4 class="card-title">Users evolution</h4>
            <small class="text-muted">Daily new users (bars) and cumulative total (line)</small>
        </div>
        <div class="d-flex align-items-center flex-wrap" style="gap:8px">
            <input type="date" id="usersFrom" class="form-control" style="width:auto">
            <span class="text-muted">to</span>
            <input type="date" id="usersTo" class="form-control" style="width:auto">
            <button id="usersApply" class="btn btn-primary">Apply</button>
        </div>
    </div>
    <div class="card-body">
        <div style="position:relative;height:320px">
            <canvas id="users-evolution-chart"></canvas>
        </div>
    </div>
</div>
@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
(function(){
  const elFrom = document.getElementById('usersFrom');
  const elTo   = document.getElementById('usersTo');
  const btn    = document.getElementById('usersApply');
  const ctx    = document.getElementById('users-evolution-chart').getContext('2d');
  const brand  = { ink: '#1B1F26', leaf: '#0FA36B', sky: '#3AB0BA', muted: '#7C8593', grid: '#E8EAEE' };
  let chart;

  Chart.defaults.font.family = getComputedStyle(document.body).fontFamily;
  Chart.defaults.color = brand.muted;

  // Default range: last 30 days
  const today = new Date();
  const toISO = d => new Date(d.getTime() - d.getTimezoneOffset()*60000).toISOString().slice(0,10);
  elTo.value   = toISO(today);
  elFrom.value = toISO(new Date(today.getTime() - 29*24*60*60*1000));

  async function loadData() {
    const url = `{{ route('analytics.users.evolution') }}?from=${encodeURIComponent(elFrom.value)}&to=${encodeURIComponent(elTo.value)}`;
    const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' }});
    if (!res.ok) { Swal.fire({ type: 'error', title: 'Could not load the users chart.' }); return null; }
    return res.json();
  }

  function render(data){
    if (chart) chart.destroy();
    chart = new Chart(ctx, {
      type: 'bar',
      data: {
        labels: data.labels,
        datasets: [
          {
            label: 'Daily new users', type: 'bar', data: data.series.daily, yAxisID: 'y',
            backgroundColor: 'rgba(15, 163, 107, .75)', hoverBackgroundColor: brand.leaf,
            borderRadius: 6, borderSkipped: false, barPercentage: .8, categoryPercentage: .9
          },
          {
            label: 'Cumulative users', type: 'line', data: data.series.cumulative, yAxisID: 'y1',
            borderColor: brand.ink, backgroundColor: brand.ink, borderWidth: 2.5,
            fill: false, tension: .3, pointRadius: 0, pointHoverRadius: 4
          }
        ]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        interaction: { mode: 'index', intersect: false },
        scales: {
          y:  { beginAtZero: true, grid: { color: brand.grid }, border: { display: false }, title: { display: true, text: 'Daily' } },
          y1: { beginAtZero: true, position: 'right', grid: { drawOnChartArea: false }, border: { display: false }, title: { display: true, text: 'Cumulative' } },
          x:  { grid: { display: false }, ticks: { maxRotation: 0, autoSkip: true } }
        },
        plugins: {
          legend: { position: 'bottom', labels: { usePointStyle: true, boxWidth: 8 } },
          tooltip: { backgroundColor: brand.ink, padding: 10, cornerRadius: 10, callbacks: { title: items => items?.[0]?.label ?? '' } }
        }
      }
    });
  }

  btn.addEventListener('click', async () => { const d = await loadData(); if (d) render(d); });
  loadData().then(d => d && render(d));
})();
</script>
@endsection

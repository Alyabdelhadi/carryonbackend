@extends('layout.main')

@section('title') Dashboard @endsection

@section('content')
<style type="text/css">
.card
{
	padding: 10px 10px;
}
</style>
<section id="dashboard-ecommerce">

<h1><i class="fa fa-home fa-5" style="color:#10163a !important"></i> CarryOn Dashboard</h1>
<br>

<div class="row">
    <div class="col-lg-3 col-sm-6 col-6">
        <a href="{{ Asset('users') }}" style="text-decoration: none; color: inherit; display: block;">
            <div class="card">
                <div class="card-header d-flex flex-column align-items-start pb-0">
                    <div class="avatar bg-rgba-success p-50 m-0">
                        <div class="avatar-content">
                        <i class="fa fa-user fa-5" style="color:black !important;font-size: 20px"></i>
                        </div>
                    </div>
                    <p class="mb-0 mt-1">Users</p>
                    <h2 class="text-bold-700">{{ $data['user'] }}</h2>
                </div>
                <div class="card-content">
                    <div id="line-area-chart-2"></div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-lg-3 col-sm-6 col-6">
        <a href="{{ Asset('users') }}" style="text-decoration: none; color: inherit; display: block;">
            <div class="card">
                <div class="card-header d-flex flex-column align-items-start pb-0">
                    <div class="avatar bg-rgba-success p-50 m-0">
                        <div class="avatar-content">
                        <i class="fa fa-user fa-5" style="color:black !important;font-size: 20px"></i>
                        </div>
                    </div>
                    <p class="mb-0 mt-1">Carriers</p>
                    <h2 class="text-bold-700">{{ $data['carriers'] }}</h2>
                </div>
                <div class="card-content">
                    <div id="line-area-chart-2"></div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-lg-3 col-sm-6 col-6">
        <a href="{{ Asset('trips') }}" style="text-decoration: none; color: inherit; display: block;">
            <div class="card">
                <div class="card-header d-flex flex-column align-items-start pb-0">
                    <div class="avatar bg-rgba-primary p-50 m-0">
                        <div class="avatar-content">
                        <i class="fa fa-plane fa-5" style="color:black !important;font-size: 20px"></i>
                        </div>
                    </div>
                    <p class="mb-0 mt-1">Trips</p>
                    <h2 class="text-bold-700">{{ $data['trips'] }}</h2>
                </div>
                <div class="card-content">
                    <div id="line-area-chart-1"></div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-lg-3 col-sm-6 col-6">
        <a href="{{ Asset('parcel_order?status=all') }}" style="text-decoration: none; color: inherit; display: block;">
            <div class="card">
                <div class="card-header d-flex flex-column align-items-start pb-0">
                    <div class="avatar bg-rgba-danger p-50 m-0">
                        <div class="avatar-content">
                        <i class="fa fa-shopping-cart fa-5 text-danger" style="color:black!important; font-size: 20px"></i>
                        </div>
                    </div>
                    <p class="mb-0 mt-1">Packages</p>
                    <h2 class="text-bold-700">{{ $data['order'] }}</h2>
                </div>
                <div class="card-content">
                    <div id="line-area-chart-3"></div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-lg-3 col-sm-6 col-6">
        <a href="{{ Asset('countries') }}" style="text-decoration: none; color: inherit; display: block;">
            <div class="card">
                <div class="card-header d-flex flex-column align-items-start pb-0">
                    <div class="avatar bg-rgba-primary p-50 m-0">
                        <div class="avatar-content">
                        <i class="fa fa-map fa-5" style="color:black !important;font-size: 20px"></i>
                        </div>
                    </div>
                    <p class="mb-0 mt-1">Countries</p>
                    <h2 class="text-bold-700">{{ $data['countries_covered'] }}</h2>
                </div>
                <div class="card-content">
                    <div id="line-area-chart-1"></div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-lg-3 col-sm-6 col-6">
        <a href="{{ Asset('cities') }}" style="text-decoration: none; color: inherit; display: block;">
            <div class="card">
                <div class="card-header d-flex flex-column align-items-start pb-0">
                    <div class="avatar bg-rgba-primary p-50 m-0">
                        <div class="avatar-content">
                        <i class="fa fa-map fa-5" style="color:black !important;font-size: 20px"></i>
                        </div>
                    </div>
                    <p class="mb-0 mt-1">Cities</p>
                    <h2 class="text-bold-700">{{ $data['cities_covered'] }}</h2>
                </div>
                <div class="card-content">
                    <div id="line-area-chart-1"></div>
                </div>
            </div>
        </a>
    </div>
    
</div>

<h3><i class="fa fa-user fa-5" style="color:#10163a !important; margin-top: 20px; margin-bottom: 10px;"></i> Users</h3>

<div class="row">

<div class="col-lg-3 col-sm-6 col-12">
<div class="card" style="padding:10px 10px;background:rgba(40, 199, 111, 0.15) !important; color:black; margin-bottom: 1rem !important;">
<p style="margin-top: 7px;font-size: 17px;color: black;">Active <b style="float: right;font-size: 20px; color:black ">{{ $data['active_users'] }}</b></p>
</div>
</div>


<div class="col-lg-3 col-sm-6 col-12">
<div class="card" style="padding:10px 10px;background:rgba(40, 199, 111, 0.15) !important; color:black; margin-bottom: 1rem !important;">
<p style="margin-top: 7px;font-size: 17px;color:black !important;">Inactive <b style="float: right;font-size: 20px; color:black ">{{ $data['inactive_users'] }}</b></p>
</div>
</div>

</div>

<h3><i class="fa fa-shopping-cart fa-5" style="color:#10163a !important; margin-top: 20px; margin-bottom: 10px;"></i> Packages</h3>

<div class="row">

<div class="col-lg-3 col-sm-6 col-12">
<a href="{{ Asset('parcel_order?status=0') }}" style="text-decoration: none; color: inherit; display: block;">
<div class="card" style="padding:10px 10px; background: rgba(234, 84, 85, 0.15) !important; color:black; margin-bottom: 1rem !important;">
<p style="margin-top: 7px;font-size: 17px;color: black;">Unassigned <b style="float: right;font-size: 20px; color:black">{{ $data['unassign'] }}</b></p>
</div>
</a>
</div>


<div class="col-lg-3 col-sm-6 col-12">
<a href="{{ Asset('parcel_order?status=1') }}" style="text-decoration: none; color: inherit; display: block;">
<div class="card" style="padding:10px 10px;background: rgba(234, 84, 85, 0.15) !important; color:black; margin-bottom: 1rem !important;">
<p style="margin-top: 7px;font-size: 17px;color:black !important;">Running <b style="float: right;font-size: 20px; color:black">{{ $data['running'] }}</b></p>
</div>
</a>
</div>


<div class="col-lg-3 col-sm-6 col-12">
<a href="{{ Asset('parcel_order?status=2') }}" style="text-decoration: none; color: inherit; display: block;">
<div class="card" style="padding:10px 10px;background: rgba(234, 84, 85, 0.15) !important; color:black; margin-bottom: 1rem !important;">
<p style="margin-top: 7px;font-size: 17px;color: black;">Delivered <b style="float: right;font-size: 20px; color:black">{{ $data['complete'] }}</b></p>
</div>
</a>
</div>


<div class="col-lg-3 col-sm-6 col-12">
<a href="{{ Asset('parcel_order?status=3') }}" style="text-decoration: none; color: inherit; display: block;">
<div class="card" style="padding:10px 10px;background: rgba(234, 84, 85, 0.15) !important; color:black; margin-bottom: 1rem !important;">
<p style="margin-top: 7px;font-size: 17px;color: black;">Cancelled <b style="float: right;font-size: 20px; color:black">{{ $data['cancel'] }}</b></p>
</div>
</a>
</div>

<div class="col-lg-3 col-sm-6 col-12">
<a href="{{ Asset('parcel_order?status=4') }}" style="text-decoration: none; color: inherit; display: block;">
<div class="card" style="padding:10px 10px;background: rgba(234, 84, 85, 0.15) !important; color:black; margin-bottom: 1rem !important;">
<p style="margin-top: 7px;font-size: 17px;color: black;">Expired <b style="float: right;font-size: 20px; color:black">{{ $data['expired'] }}</b></p>
</div>
</a>
</div>

</div>


<h3><i class="fa fa-plane fa-5" style="color:#10163a !important; margin-top: 20px; margin-bottom: 10px;"></i> Trips</h3>

<div class="row">

<div class="col-lg-3 col-sm-6 col-12">
<div class="card" style="padding:10px 10px;background:rgba(115, 103, 240, 0.15) !important; color:black; margin-bottom: 1rem !important;">
<p style="margin-top: 7px;font-size: 17px;color: black;">Past <b style="float: right;font-size: 20px; color:black ">{{ $data['past'] }}</b></p>
</div>
</div>


<div class="col-lg-3 col-sm-6 col-12">
<div class="card" style="padding:10px 10px;background:rgba(115, 103, 240, 0.15)!important; color:black; margin-bottom: 1rem !important;">
<p style="margin-top: 7px;font-size: 17px;color:black !important;">Upcoming <b style="float: right;font-size: 20px; color:black ">{{ $data['upcoming'] }}</b></p>
</div>
</div>

<div class="col-lg-3 col-sm-6 col-12">
<div class="card" style="padding:10px 10px;background:rgba(115, 103, 240, 0.15)!important; color:black; margin-bottom: 1rem !important;">
<p style="margin-top: 7px;font-size: 17px;color:black !important;">Frequent Routes <b style="float: right;font-size: 20px; color:black ">{{ $data['frequent'] }}</b></p>
</div>
</div>

</div>



{{-- Users Evolution Card (Daily bars + Cumulative line) --}}
<div class="row" style="margin-top: 20px;">
  <div class="col-12">
    <div class="card">
      <div class="card-header d-flex flex-column align-items-center justify-content-between pb-0" style="gap:10px">
        <div>
          <h4 class="mb-0 text-center">Users Evolution</h4>
          <small class="text-muted text-center">Daily new users (bars) & cumulative (line)</small>
        </div>
        <div class="d-flex align-items-center flex-wrap flex-md-nowrap" style="gap:8px; width: 80%;">
          <input type="date" id="usersFrom" class="form-control" style="min-width: 160px;">
          <input type="date" id="usersTo" class="form-control" style="min-width: 160px;">
          <button id="usersApply" class="btn btn-primary w-50">Apply</button>
        </div>
      </div>
      <div class="card-content">
        <div class="card-body">
          <canvas id="users-evolution-chart" height="300"></canvas>
        </div>
      </div>
    </div>
  </div>
</div>

{{-- Chart.js (load once) --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
(function(){
  const elFrom = document.getElementById('usersFrom');
  const elTo   = document.getElementById('usersTo');
  const btn    = document.getElementById('usersApply');
  const ctx    = document.getElementById('users-evolution-chart').getContext('2d');
  let chart;

  // Default range: last 30 days
  const today = new Date();
  const toISO = d => new Date(d.getTime() - d.getTimezoneOffset()*60000).toISOString().slice(0,10);
  elTo.value   = toISO(today);
  elFrom.value = toISO(new Date(today.getTime() - 29*24*60*60*1000));

  async function loadData() {
    const from = elFrom.value;
    const to   = elTo.value;
    const url = `{{ route('analytics.users.evolution') }}?from=${encodeURIComponent(from)}&to=${encodeURIComponent(to)}`;
    const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' }});
    if (!res.ok) { alert('Failed to load users evolution data.'); return null; }
    return res.json();
  }

  function render(data){
    const datasets = [
      // Bars: Daily New Users on left axis (y)
      {
        label: 'Daily New Users',
        type: 'bar',
        data: data.series.daily,
        yAxisID: 'y',
        backgroundColor: 'rgba(54, 162, 235, 0.6)',
        borderWidth: 0,
        // optional: widen bars a bit
        barPercentage: 0.9,
        categoryPercentage: 0.9
      },
      // Line: Cumulative Users on right axis (y1)
      {
        label: 'Cumulative Users',
        type: 'line',
        data: data.series.cumulative,
        yAxisID: 'y1',
        borderColor: 'rgba(255, 99, 132, 0.9)',
        borderWidth: 2,
        fill: false,
        tension: 0.25,
        pointRadius: 0
      }
    ];

    if (chart) chart.destroy();
    chart = new Chart(ctx, {
      type: 'bar',
      data: { labels: data.labels, datasets },
      options: {
        responsive: true,
        interaction: { mode: 'index', intersect: false },
        maintainAspectRatio: false,
        scales: {
          // Left axis for daily bars
          y: {
            beginAtZero: true,
            title: { display: true, text: 'Daily New Users' }
          },
          // Right axis for cumulative line
          y1: {
            beginAtZero: true,
            position: 'right',
            grid: { drawOnChartArea: false }, // don’t overlay grids
            title: { display: true, text: 'Cumulative Users' }
          },
          x: {
            ticks: { maxRotation: 0, autoSkip: true }
          }
        },
        plugins: {
          legend: { position: 'bottom' },
          tooltip: {
            callbacks: {
              title: items => items?.[0]?.label ?? ''
            }
          }
        }
      }
    });
  }

  btn.addEventListener('click', async () => {
    const data = await loadData();
    if (data) render(data);
  });

  // Initial render
  loadData().then(d => d && render(d));
})();
</script>



@endsection


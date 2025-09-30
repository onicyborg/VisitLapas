@extends('layouts.master')

@section('page_title', 'Dashboard')

@push('styles')
<style>
  .fade-in { animation: fadeIn .4s ease both; }
  .slide-up { animation: slideUp .5s ease both; }
  @keyframes fadeIn { from{opacity:0} to{opacity:1} }
  @keyframes slideUp { from{opacity:0; transform: translateY(12px)} to{opacity:1; transform:none} }

  /* Prevent ApexCharts tooltip caption overlap/wrapping issues */
  .apexcharts-tooltip { white-space: normal !important; max-width: 260px; }
  .apex-tooltip { padding: 8px 10px; font-size: 12px; line-height: 1.25; }
  .apex-tooltip .label { display: flex; align-items: center; margin-bottom: 2px; }
  .apex-tooltip .dot { width: 8px; height: 8px; border-radius: 50%; display: inline-block; margin-right: 6px; flex: 0 0 8px; }
  .apex-tooltip .value { display: block; font-weight: 600; }
</style>
@endpush

@section('content')
<div class="m-6">
  <div class="row g-6 mb-6">
    <div class="col-12 col-sm-6 col-xl-3">
      <div class="card fade-in">
        <div class="card-body d-flex align-items-center justify-content-between">
          <div>
            <div class="fs-2hx fw-bold">{{ number_format($visitors_today) }}</div>
            <div class="text-muted">Total Visitors Today</div>
          </div>
          <div class="symbol symbol-50px bg-light-success">
            <i class="bi bi-people-fill fs-1 text-success"></i>
          </div>
        </div>
      </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
      <div class="card fade-in" style="animation-delay:.05s">
        <div class="card-body d-flex align-items-center justify-content-between">
          <div>
            <div class="fs-2hx fw-bold">{{ number_format($inmates_today) }}</div>
            <div class="text-muted">Inmates Visited Today</div>
          </div>
          <div class="symbol symbol-50px bg-light-info">
            <i class="bi bi-person-check-fill fs-1 text-info"></i>
          </div>
        </div>
      </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
      <div class="card fade-in" style="animation-delay:.1s">
        <div class="card-body d-flex align-items-center justify-content-between">
          <div>
            <div class="fs-2hx fw-bold">{{ number_format($queues_today) }}</div>
            <div class="text-muted">Queues Today</div>
          </div>
          <div class="symbol symbol-50px bg-light-warning">
            <i class="bi bi-card-checklist fs-1 text-warning"></i>
          </div>
        </div>
      </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
      <div class="card fade-in" style="animation-delay:.15s">
        <div class="card-body d-flex align-items-center justify-content-between">
          <div>
            <div class="fs-2hx fw-bold">{{ number_format($completed_today) }}</div>
            <div class="text-muted">Completed Visits</div>
          </div>
          <div class="symbol symbol-50px bg-light-danger">
            <i class="bi bi-check-circle-fill fs-1 text-danger"></i>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="row g-6 mb-6">
    <div class="col-12 col-xl-7">
      <div class="card slide-up h-100">
        <div class="card-header"><h3 class="card-title m-0">Daily Visits This Week</h3></div>
        <div class="card-body"><div id="chart_weekly" style="height: 320px"></div></div>
      </div>
    </div>
    <div class="col-12 col-xl-5">
      <div class="card slide-up h-100" style="animation-delay:.05s">
        <div class="card-header"><h3 class="card-title m-0">Status Distribution Today</h3></div>
        <div class="card-body"><div id="chart_status" style="height: 320px"></div></div>
      </div>
    </div>
  </div>

  <div class="row g-6">
    <div class="col-12 col-xl-7">
      <div class="card fade-in">
        <div class="card-header align-items-center">
          <h3 class="card-title m-0">Top 5 Current Queues</h3>
          <a href="{{ route('queues.index') }}" class="btn btn-light-primary btn-sm ms-auto">View All Queues</a>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-row-dashed align-middle gs-0 gy-4 mb-0">
              <thead>
                <tr class="fw-semibold text-muted text-center">
                  <th class="text-center">Ticket</th>
                  <th class="text-center">Visitor</th>
                  <th class="text-center">Inmate</th>
                  <th class="text-center">Status</th>
                  <th class="text-center">Priority</th>
                </tr>
              </thead>
              <tbody>
                @forelse($top_queues as $q)
                <tr>
                  <td class="text-start"><strong>{{ $q->ticket_number }}</strong></td>
                  <td class="text-start">{{ $q->visitor->name ?? '-' }}</td>
                  <td class="text-start">{{ $q->inmate->name ?? '-' }}</td>
                  <td class="text-start"><span class="badge badge-light">{{ ucfirst(str_replace('_',' ', $q->status)) }}</span></td>
                  <td class="text-start">
                    @if($q->priority >= 2)
                      <span class="badge badge-danger">VIP</span>
                    @elseif($q->priority == 1)
                      <span class="badge badge-warning">Priority</span>
                    @else
                      <span class="badge badge-secondary">Regular</span>
                    @endif
                  </td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-muted text-center">Tidak ada antrian aktif</td></tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
    <div class="col-12 col-xl-5">
      <div class="card fade-in" style="animation-delay:.05s">
        <div class="card-header"><h3 class="card-title m-0">Announcement</h3></div>
        <div class="card-body">
          @if(!empty($ticker_text))
            <div class="alert alert-info d-flex align-items-center">
              <i class="bi bi-megaphone-fill fs-2 me-3"></i>
              <div>{{ $ticker_text }}</div>
            </div>
          @else
            <div class="text-muted">Belum ada pengumuman</div>
          @endif
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
// Data for charts from controller
const dailyLabels = @json($daily_labels);
const dailyValues = @json($daily_values);
const statusDist = @json($status_distribution);

// Weekly bar/column chart (ApexCharts)
(function(){
  if (!window.ApexCharts) return;
  const el = document.querySelector('#chart_weekly');
  const opt = {
    chart: { type: 'bar', height: 320, toolbar: {show:false}, animations: {enabled: true} },
    series: [{ name: 'Done', data: dailyValues }],
    xaxis: { categories: dailyLabels, labels:{ style:{ colors: KTThemeMode ? undefined : undefined } } },
    colors: [getComputedStyle(document.documentElement).getPropertyValue('--bs-primary') || '#3E97FF'],
    plotOptions: { bar: { columnWidth: '45%', borderRadius: 4 } },
    dataLabels: { enabled: false },
    grid: { strokeDashArray: 3 },
    tooltip: { theme: document.documentElement.getAttribute('data-bs-theme')==='dark'?'dark':'light' }
  };
  new ApexCharts(el, opt).render();
})();

// Status distribution pie chart
(function(){
  if (!window.ApexCharts) return;
  const el = document.querySelector('#chart_status');
  const labels = ['Waiting','Called','Serving','Done','No Show','Cancelled'];
  const data = [statusDist.waiting, statusDist.called, statusDist.serving, statusDist.done, statusDist.no_show, statusDist.cancelled];
  const opt = {
    chart: { type: 'donut', height: 320, toolbar: {show:false}, animations: {enabled: true} },
    labels: labels,
    series: data,
    colors: [
      getComputedStyle(document.documentElement).getPropertyValue('--bs-warning')||'#F6C000',
      getComputedStyle(document.documentElement).getPropertyValue('--bs-info')||'#7239EA',
      getComputedStyle(document.documentElement).getPropertyValue('--bs-primary')||'#3E97FF',
      getComputedStyle(document.documentElement).getPropertyValue('--bs-success')||'#50CD89',
      getComputedStyle(document.documentElement).getPropertyValue('--bs-dark')||'#071437',
      getComputedStyle(document.documentElement).getPropertyValue('--bs-danger')||'#F1416C'
    ],
    dataLabels: { enabled: false },
    legend: { position: 'bottom' },
    tooltip: {
      theme: document.documentElement.getAttribute('data-bs-theme')==='dark'?'dark':'light',
      custom: function({ series, seriesIndex, w }){
        const label = w.globals.labels[seriesIndex] || '';
        const value = typeof series[seriesIndex] !== 'undefined' ? series[seriesIndex] : '';
        const color = (w.config.colors && w.config.colors[seriesIndex]) || '#999';
        return (
          '<div class="apex-tooltip">'
            + '<span class="label"><span class="dot" style="background:'+color+'"></span>' + label + '</span>'
            + '<span class="value">' + value + '</span>'
          + '</div>'
        );
      }
    }
  };
  new ApexCharts(el, opt).render();
})();
</script>
@endpush

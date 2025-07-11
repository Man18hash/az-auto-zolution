@extends('layouts.admin')
@section('title','Income Analysis')

@section('content')
<div class="container-fluid px-2 px-md-4">
  <h2 class="fw-bold mb-4">Income Analysis</h2>

  {{-- Period Selector --}}
  <form method="GET" action="{{ route('admin.income-analysis-report') }}"
        class="row g-3 align-items-end mb-4">
    <div class="col-auto">
      <label class="form-label fw-semibold mb-0">View:</label>
      <select name="period" class="form-select" onchange="this.form.submit()">
        <option value="daily"   {{ $periodType==='daily'   ? 'selected':'' }}>Daily (24 hrs)</option>
        <option value="weekly"  {{ $periodType==='weekly'  ? 'selected':'' }}>Weekly (7 days)</option>
        <option value="monthly" {{ $periodType==='monthly' ? 'selected':'' }}>Monthly (days)</option>
        <option value="yearly"  {{ $periodType==='yearly'  ? 'selected':'' }}>Yearly (12 mo)</option>
      </select>
    </div>
  </form>

  {{-- Charts --}}
  <div class="row mb-4">
    {{-- Pie --}}
    <div class="col-md-6">
      <div class="card shadow-sm">
        <div class="card-header fw-bold">Income Breakdown</div>
        <div class="card-body d-flex justify-content-center">
          <canvas id="pieChart" style="max-width:100%; max-height:300px;"></canvas>
        </div>
      </div>
    </div>
    {{-- Trend --}}
    <div class="col-md-6">
      <div class="card shadow-sm">
        <div class="card-header fw-bold">Trend Over Time</div>
        <div class="card-body">
          <canvas id="lineChart" style="width:100%; height:300px;"></canvas>
        </div>
      </div>
    </div>
  </div>

  {{-- Summary --}}
  <div class="card shadow-sm mb-4">
    <div class="card-body">
      <table class="table table-bordered mb-0">
        <tr><th>Total Sales</th>        <td>₱{{ number_format($totals['Sales'],2) }}</td></tr>
        <tr><th>A/R Collections</th>   <td>₱{{ number_format($totals['A/R'],2) }}</td></tr>
        <tr><th>Expenses</th>          <td class="text-danger">₱{{ number_format($totals['Expenses'],2) }}</td></tr>
        <tr><th>Deposits</th>          <td>₱{{ number_format($totals['Deposits'],2) }}</td></tr>
        <tr><th>Total Discounts</th>   <td class="text-warning">₱{{ number_format($totalDiscount,2) }}</td></tr>
        <tr><th>Cash Payments</th>     <td>₱{{ number_format($cashPayments,2) }}</td></tr>
        <tr><th>Non-Cash Payments</th> <td>₱{{ number_format($nonCashPayments,2) }}</td></tr>
        <tr class="fw-bold">
          <th>Net Income</th>
          <td class="text-success">₱{{ number_format($totals['Net Income'],2) }}</td>
        </tr>
      </table>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  const labels = @json($labels);
  const series = @json($series);
  const totals = @json($totals);

  // Pie Chart
  const pieCtx = document.getElementById('pieChart').getContext('2d');
  const pieLabels = ['Sales','A/R','Expenses','Deposits','Discounts','Net Income'];
  const pieData   = [
    totals['Sales'],
    totals['A/R'],
    totals['Expenses'],
    totals['Deposits'],
    {{ $totalDiscount }},
    totals['Net Income']
  ];
  new Chart(pieCtx, {
    type:'pie',
    data:{ labels:pieLabels, datasets:[{ data:pieData,
      backgroundColor:['#28a745','#20c997','#dc3545','#0dcaf0','#ffc107','#007bff']
    }]},
    options:{ responsive:true, plugins:{ legend:{ position:'bottom' } } }
  });

  // Trend Over Time (bar)
  const barCtx = document.getElementById('lineChart').getContext('2d');
  const datasets = [
    { label:'Sales',     data:series.Sales,     backgroundColor:'#28a745' },
    { label:'A/R',        data:series['A/R'],    backgroundColor:'#20c997' },
    { label:'Expenses',   data:series.Expenses,  backgroundColor:'#dc3545' },
    { label:'Deposits',   data:series.Deposits,  backgroundColor:'#0dcaf0' },
    { label:'Net Income', data:series['Net Income'], backgroundColor:'#007bff' }
  ];
  new Chart(barCtx, {
    type:'bar',
    data:{ labels, datasets },
    options:{
      responsive:true,
      scales:{
        x:{ stacked:false },
        y:{
          beginAtZero:true,
          ticks:{ callback: v => '₱' + v.toLocaleString() }
        }
      },
      plugins:{
        tooltip:{ callbacks:{ label: ctx => '₱' + ctx.raw.toLocaleString() } }
      }
    }
  });
</script>
@endpush

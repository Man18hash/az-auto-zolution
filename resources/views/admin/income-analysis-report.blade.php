@extends('layouts.admin')
@section('title', 'Income Analysis')

@section('content')
<div class="container-fluid px-2 px-md-4">
  <h2 class="fw-bold mb-4">Income Analysis</h2>

  {{-- Filter --}}
  <form method="GET" action="{{ route('admin.income-analysis-report') }}" class="row g-3 align-items-end mb-4">
    <div class="col-12 col-md-auto">
      <label for="start_date" class="form-label fw-semibold mb-0">From:</label>
      <input type="date" id="start_date" name="start_date" class="form-control" value="{{ $startDate }}" required>
    </div>
    <div class="col-12 col-md-auto">
      <label for="end_date" class="form-label fw-semibold mb-0">To:</label>
      <input type="date" id="end_date" name="end_date" class="form-control" value="{{ $endDate }}" required>
    </div>
    <div class="col-12 col-md-auto">
      <button type="submit" class="btn btn-primary fw-bold w-100">
        <i class="fas fa-filter me-1"></i> Filter
      </button>
    </div>
  </form>

  {{-- Pie & Line Charts 50/50 --}}
  <div class="row mb-4">
    {{-- Pie Chart --}}
    <div class="col-md-6">
      <div class="card shadow-sm">
        <div class="card-header fw-bold">Income Breakdown</div>
        <div class="card-body d-flex justify-content-center">
          <canvas id="incomePieChart" style="max-width:100%; max-height:300px;"></canvas>
        </div>
      </div>
    </div>
    {{-- Line Chart --}}
    <div class="col-md-6">
      <div class="card shadow-sm">
        <div class="card-header fw-bold">Category Trends</div>
        <div class="card-body">
          <canvas id="incomeLineChart" style="width:100%; height:300px;"></canvas>
        </div>
      </div>
    </div>
  </div>

  {{-- Summary Table --}}
  <div class="card shadow-sm">
    <div class="card-body">
      <table class="table table-bordered table-striped mb-0">
        <tr><th>Total Sales</th><td>₱{{ number_format($totalSales, 2) }}</td></tr>
        <tr><th>A/R Collections</th><td>₱{{ number_format($totalAR, 2) }}</td></tr>
        <tr><th>Cash Deposits</th><td>₱{{ number_format($totalDeposits, 2) }}</td></tr>
        <tr><th>Expenses</th><td class="text-danger">₱{{ number_format($totalExpenses, 2) }}</td></tr>
        <tr class="fw-bold"><th>Net Income</th><td class="text-success">₱{{ number_format($netIncome, 2) }}</td></tr>
      </table>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  // Pie Chart
  const pieCtx = document.getElementById('incomePieChart').getContext('2d');
  new Chart(pieCtx, {
    type: 'pie',
    data: {
      labels: ['Sales','A/R','Deposits','Expenses','Net Income'],
      datasets: [{
        data: [
          {{ $totalSales }}, 
          {{ $totalAR }}, 
          {{ $totalDeposits }}, 
          {{ $totalExpenses }}, 
          {{ $netIncome }}
        ],
        backgroundColor: [
          '#28a745','#20c997','#0dcaf0','#dc3545','#007bff'
        ],
      }]
    },
    options: {
      responsive: true,
      plugins: { legend: { position: 'bottom' } }
    }
  });

  // Line Chart
  const lineCtx = document.getElementById('incomeLineChart').getContext('2d');
  new Chart(lineCtx, {
    type: 'line',
    data: {
      labels: ['Sales','A/R','Deposits','Expenses','Net Income'],
      datasets: [{
        label: '₱ Amount',
        data: [
          {{ $totalSales }}, 
          {{ $totalAR }}, 
          {{ $totalDeposits }}, 
          {{ $totalExpenses }}, 
          {{ $netIncome }}
        ],
        fill: false,
        tension: 0.1,
        borderColor: '#333'
      }]
    },
    options: {
      responsive: true,
      scales: {
        y: {
          beginAtZero: true,
          ticks: { callback: v => '₱' + v.toLocaleString() }
        }
      },
      plugins: {
        legend: { display: false },
        tooltip: {
          callbacks: {
            label: ctx => '₱' + ctx.raw.toLocaleString()
          }
        }
      }
    }
  });
</script>
@endpush

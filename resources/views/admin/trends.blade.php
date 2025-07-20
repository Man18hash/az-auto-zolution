@extends('layouts.admin')

@section('title', 'Material Trends')

@section('content')
<div class="container-fluid px-2 px-md-4">

  <h2 class="fw-bold mb-4">Material Trends</h2>

  {{-- Date range filter --}}
  <form method="GET" action="{{ route('admin.trends') }}" class="row g-3 align-items-end mb-5">
    <div class="col-auto">
      <label for="start_date" class="form-label">Start Date</label>
      <input type="date" id="start_date" name="start_date" class="form-control"
             value="{{ $startDate->format('Y-m-d') }}">
    </div>
    <div class="col-auto">
      <label for="end_date" class="form-label">End Date</label>
      <input type="date" id="end_date" name="end_date" class="form-control"
             value="{{ $endDate->format('Y-m-d') }}">
    </div>
    <div class="col-auto">
      <button type="submit" class="btn btn-primary">Apply</button>
    </div>
  </form>

  {{-- Bar Chart --}}
  <div style="height: 300px;" class="mb-5">
    <canvas id="materialsChart"></canvas>
  </div>

  {{-- Top 10 Materials Table --}}
  <div>
    <h3 class="h5">Top 10 Most-Used Materials</h3>
    <table class="table table-bordered table-striped">
      <thead class="table-light">
        <tr>
          <th style="width: 50px;">#</th>
          <th>Material Name</th>
          <th style="width: 150px;">Total Quantity</th>
        </tr>
      </thead>
      <tbody>
        @forelse($materials as $mat)
          <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $mat->material_name }}</td>
            <td class="text-end">{{ $mat->total_quantity }}</td>
          </tr>
        @empty
          <tr>
            <td colspan="3" class="text-center">No materials used in this period.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

{{-- Chart.js CDN --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('materialsChart').getContext('2d');
    const labels = @json($materials->pluck('material_name'));
    const data   = @json($materials->pluck('total_quantity'));

    new Chart(ctx, {
      type: 'bar',
      data: {
        labels: labels,
        datasets: [{
          label: 'Quantity Used',
          data: data,
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
          x: { ticks: { autoSkip: false } },
          y: { beginAtZero: true }
        }
      }
    });
  });
</script>
@endsection

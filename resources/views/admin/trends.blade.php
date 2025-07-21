@extends('layouts.admin')
@section('title', 'Material Trends')

@section('content')
<div class="container-fluid px-2 px-md-4">
  <h2 class="fw-bold mb-3">Material Trends</h2>

  {{-- Filters --}}
  <form method="GET" action="{{ route('admin.trends') }}" class="row g-3 align-items-end mb-1">
    <div class="col-auto">
      <label class="form-label mb-1">Period</label>
      <select name="period" class="form-select" onchange="this.form.submit()">
        <option value="day"   {{ $period === 'day' ? 'selected' : '' }}>Day</option>
        <option value="week"  {{ $period === 'week' ? 'selected' : '' }}>Week</option>
        <option value="month" {{ $period === 'month' ? 'selected' : '' }}>Month</option>
      </select>
    </div>
    <div class="col-auto">
      <label for="start_date" class="form-label mb-1">Start Date</label>
      <input type="date" id="start_date" name="start_date" class="form-control"
             value="{{ $startDate->format('Y-m-d') }}">
    </div>
    <div class="col-auto">
      <label for="end_date" class="form-label mb-1">End Date</label>
      <input type="date" id="end_date" name="end_date" class="form-control"
             value="{{ $endDate->format('Y-m-d') }}">
    </div>
    <div class="col-auto" style="min-width:260px;">
      <label for="search" class="form-label mb-1">Search Material</label>
      <select id="search" name="search" class="form-select">
        <option value="">— All Materials —</option>
        @foreach($allMaterialNames as $mat)
          <option value="{{ $mat }}" {{ ($search ?? '') === $mat ? 'selected' : '' }}>
            {{ $mat }}
          </option>
        @endforeach
      </select>
    </div>
    <div class="col-auto align-self-end">
      <button type="submit" class="btn btn-primary fw-bold px-4">Apply</button>
    </div>
  </form>

  {{-- Total sales for current material --}}
  @if($search && isset($filteredSales))
    <div class="alert alert-success mb-2 mt-1 d-inline-block" style="min-width:340px">
      <b>Total Sales (<span class="text-dark">{{ $search }}</span>):</b>
      <span class="fs-5 fw-bold text-primary">₱{{ number_format($filteredSales, 2) }}</span>
    </div>
  @endif

  {{-- Bar Chart --}}
  <div class="mb-3" style="height:240px;max-width:100%;">
    <canvas id="materialsChart"></canvas>
  </div>

  {{-- Top 30 Materials Table --}}
  <div>
    <h3 class="h5 mb-2">Top 30 Most-Used Materials</h3>
    <div class="table-responsive">
      <table class="table table-bordered table-striped mb-0">
        <thead class="table-light">
          <tr>
            <th style="width:50px;">#</th>
            <th>Material Name</th>
            <th style="width:150px;">Total Quantity</th>
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
</div>

{{-- Chart.js CDN --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
{{-- jQuery and Select2 for dropdown --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
  $(function() {
    // Enable Select2 search on dropdown
    $('#search').select2({
      width: '100%',
      placeholder: "Search or select material",
      allowClear: true
    });

    // Chart
    const ctx = document.getElementById('materialsChart').getContext('2d');
    const fullLabels = @json($materials->pluck('material_name'));
    const data       = @json($materials->pluck('total_quantity'));

    // Truncate label (displayed under the graph) to 10 chars + '…'
    const shortLabels = fullLabels.map(label => {
      if (!label) return '';
      return label.length > 10 ? label.substring(0, 10) + '…' : label;
    });

    new Chart(ctx, {
      type: 'bar',
      data: {
        labels: shortLabels,
        datasets: [{
          label: 'Quantity Used',
          data: data,
          backgroundColor: '#0dcaf0'
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { display: false },
          tooltip: {
            callbacks: {
              // Show the full label in tooltip
              title: function(context) {
                return fullLabels[context[0].dataIndex];
              }
            }
          }
        },
        scales: {
          x: {
            ticks: {
              autoSkip: false,
              maxRotation: 0,
              minRotation: 0,
              font: { size: 10 }
            }
          },
          y: { beginAtZero: true }
        }
      }
    });
  });
</script>
@endsection

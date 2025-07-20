{{-- resources/views/admin/material-summary.blade.php --}}
@extends('layouts.admin')

@section('title', 'Material Summary')

@section('content')
<div class="container-fluid px-2 px-md-4">

  <h2 class="fw-bold text-center mb-4">Material Summary</h2>

  {{-- Date Range Filter --}}
  <form method="GET" action="{{ route('admin.material-summary') }}" class="row g-3 align-items-end justify-content-center mb-5">
    <div class="col-auto">
      <label for="start_date" class="form-label">Start Date</label>
      <input type="date"
             id="start_date"
             name="start_date"
             class="form-control"
             value="{{ $startDate->format('Y-m-d') }}">
    </div>
    <div class="col-auto">
      <label for="end_date" class="form-label">End Date</label>
      <input type="date"
             id="end_date"
             name="end_date"
             class="form-control"
             value="{{ $endDate->format('Y-m-d') }}">
    </div>
    <div class="col-auto">
      <button type="submit" class="btn btn-primary">Apply</button>
    </div>
    <a href="{{ route('admin.material-summary.export', ['start_date' => $startDate->format('Y-m-d'), 'end_date' => $endDate->format('Y-m-d')]) }}"
   class="btn btn-outline-success mb-4" target="_blank">
  Export to PDF
</a>
  </form>

  {{-- Excel-style, full-width table --}}
  <div class="table-responsive" style="width: 100%; margin: 0 auto;">
    <table class="table table-bordered table-striped table-hover table-sm" style="width:100%; font-size:1rem; border-collapse: collapse;">
      <thead>
        {{-- Total gross profit --}}
        <tr>
          <th colspan="5" style="background:#e9ecef; padding:0.75rem; text-align:left;">
            Total Gross Profit
          </th>
          <th style="background:#e9ecef; padding:0.75rem; text-align:right;">
            ₱{{ number_format($materials->sum('gross_profit'), 2) }}
          </th>
        </tr>
        <tr>
          <th style="background:#f0f0f0; padding:0.75rem;">Day</th>
          <th style="background:#f0f0f0; padding:0.75rem;">Customer Name</th>
          <th style="background:#f0f0f0; padding:0.75rem;">Material</th>
          <th style="background:#f0f0f0; padding:0.75rem; text-align:right;">Price</th>
          <th style="background:#f0f0f0; padding:0.75rem; text-align:right;">Cost</th>
          <th style="background:#f0f0f0; padding:0.75rem; text-align:right;">Gross Profit</th>
        </tr>
      </thead>
      <tbody>
        @php $prevDay = null; $prevCustomer = null; @endphp
        @forelse($materials as $m)
          <tr>
            <td style="padding:0.5rem;">
              @if($m->day !== $prevDay)
                {{ \Carbon\Carbon::parse($m->day)->format('Y-m-d') }}
                @php $prevDay = $m->day; @endphp
              @endif
            </td>
            <td style="padding:0.5rem;">
              @if($m->customer_name !== $prevCustomer)
                {{ $m->customer_name }}
                @php $prevCustomer = $m->customer_name; @endphp
              @endif
            </td>
            <td style="padding:0.5rem;">
              {{ $m->material }}
            </td>
            <td style="padding:0.5rem; text-align:right;">
              ₱{{ number_format($m->price, 2) }}
            </td>
            <td style="padding:0.5rem; text-align:right;">
              ₱{{ number_format($m->cost, 2) }}
            </td>
            <td style="padding:0.5rem; text-align:right;">
              ₱{{ number_format($m->gross_profit, 2) }}
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="6" class="text-center" style="padding:0.75rem;">
              No materials found in this period.
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection

{{-- resources/views/admin/labor-summary.blade.php --}}
@extends('layouts.admin')

@section('title', 'Labor Summary')

@section('content')
<div class="container-fluid px-2 px-md-4">

  <h2 class="fw-bold text-center mb-4">Labor Summary</h2>

  {{-- Date Range Filter --}}
  <form method="GET" action="{{ route('admin.labor-summary') }}" class="row g-3 align-items-end justify-content-center mb-4">
    <div class="col-auto">
      <label for="start_date" class="form-label">Start Date</label>
      <input type="date" id="start_date" name="start_date" class="form-control" value="{{ $startDate->format('Y-m-d') }}">
    </div>
    <div class="col-auto">
      <label for="end_date" class="form-label">End Date</label>
      <input type="date" id="end_date" name="end_date" class="form-control" value="{{ $endDate->format('Y-m-d') }}">
    </div>
    <div class="col-auto">
      <button type="submit" class="btn btn-primary">Apply</button>
    </div>
    <div class="col-auto">
      <a href="{{ route('admin.labor-summary.export', ['start_date' => $startDate->format('Y-m-d'), 'end_date' => $endDate->format('Y-m-d')]) }}" target="_blank" class="btn btn-outline-danger">
        ⬇ Export Labor Summary (PDF)
      </a>
    </div>
  </form>

  {{-- Labor Summary Table --}}
  <div class="table-responsive">
    <table class="table table-bordered table-striped w-100">
      <thead class="table-light">
        {{-- Total Summary Row --}}
        <tr class="table-info fw-bold">
          <td colspan="2">Total Labor Charges</td>
          <td class="text-end">₱{{ number_format($labors->sum('labor_charge'), 2) }}</td>
        </tr>
        <tr>
          <th style="width: 20%;">Day</th>
          <th style="width: 50%;">Customer Name</th>
          <th style="width: 30%;" class="text-end">Labor Charge (₱)</th>
        </tr>
      </thead>
      <tbody>
        @php
          $lastDay = null;
          $lastCustomer = null;
        @endphp
        @forelse($labors as $row)
          <tr>
            <td>{{ $row->day !== $lastDay ? \Carbon\Carbon::parse($row->day)->format('F d, Y') : '' }}</td>
            <td>{{ $row->customer_name !== $lastCustomer ? $row->customer_name : '' }}</td>
            <td class="text-end">{{ number_format($row->labor_charge, 2) }}</td>
          </tr>
          @php
            $lastDay = $row->day;
            $lastCustomer = $row->customer_name;
          @endphp
        @empty
          <tr>
            <td colspan="3" class="text-center">No labor transactions found in this period.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

</div>
@endsection

@extends('layouts.admin')
@section('title','Invoice History')

@section('content')
@php
  use Carbon\Carbon;

  // Grouping and badge mappings
  $grouped = $history->groupBy(fn($inv) =>
    Carbon::parse($inv->created_at)->format('F d, Y')
  );
  $statusBadge = [
    'unpaid'    => 'bg-secondary',
    'paid'      => 'bg-success text-white',
    'cancelled' => 'bg-danger',
    'voided'    => 'bg-dark text-white',
  ];
  $sourceBadge = [
    'cancelled'     => 'bg-danger',
    'quotation'     => 'bg-warning text-dark',
    'appointment'   => 'bg-info text-dark',
    'service_order' => 'bg-secondary',
    'invoicing'     => 'bg-success text-white',
  ];
@endphp

<div class="container-fluid px-2 px-md-4 mt-4">
  <h2 class="mb-4 text-center">Invoice History</h2>

  {{-- Success Message --}}
  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif

  {{-- Search --}}
  <form method="GET" action="{{ route('admin.invoices') }}" class="mb-3">
    <div class="input-group">
      <input type="text" name="search" class="form-control"
             placeholder="Search by invoice#, customer, plate…"
             value="{{ $search }}">
      <button class="btn btn-primary" type="submit">
        <i class="fas fa-search"></i> Search
      </button>
    </div>
  </form>

  @if($history->isEmpty())
    <div class="alert alert-info">No invoices found.</div>
  @else
    @foreach($grouped as $date => $records)
      <h4 class="mt-4">{{ $date }}</h4>
      <table class="table table-striped align-middle">
        <thead>
          <tr>
            <th>Invoice #</th>
            <th>Customer</th>
            <th>Vehicle</th>
            <th>Source Type</th>
            <th>Total</th>
            <th>Status</th>
            <th class="text-center">Actions</th>
          </tr>
        </thead>
        <tbody>
          @foreach($records as $inv)
            <tr>
              <td>{{ $inv->invoice_no }}</td>
              <td>{{ $inv->client->name ?? $inv->customer_name }}</td>
              <td>{{ $inv->vehicle->plate_number ?? $inv->vehicle_name }}</td>
              <td>
                <span class="badge {{ $sourceBadge[$inv->source_type] ?? 'bg-secondary' }}">
                  {{ ucfirst(str_replace('_',' ',$inv->source_type)) }}
                </span>
              </td>
              <td>₱{{ number_format($inv->grand_total,2) }}</td>
              <td>
                <span class="badge {{ $statusBadge[$inv->status] ?? 'bg-secondary' }}">
                  {{ ucfirst($inv->status) }}
                </span>
              </td>
              <td class="text-center">
                {{-- View --}}
                <a href="{{ route('admin.invoices.view',$inv->id) }}"
                   class="btn btn-sm btn-info" target="_blank">
                  View
                </a>

                {{-- Delete --}}
                <form action="{{ route('admin.invoices.destroy', $inv->id) }}"
                      method="POST" class="d-inline"
                      onsubmit="return confirm('Delete invoice #{{ $inv->invoice_no }}?');">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="btn btn-sm btn-danger">
                    Delete
                  </button>
                </form>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    @endforeach

    {{-- Pagination --}}
    <div class="mt-3">
      {{ $history->links() }}
    </div>
  @endif
</div>
@endsection

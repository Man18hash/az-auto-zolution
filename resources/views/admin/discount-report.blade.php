@extends('layouts.admin')
@section('title', 'Discount Report')

@section('content')
<div class="container-fluid px-2 px-md-4">
  <h2 class="fw-bold mb-4">Discount Report</h2>

  {{-- Date Filter --}}
  <form method="GET" class="row g-3 align-items-end mb-4">
    <div class="col-auto">
      <label for="start_date" class="form-label">From:</label>
      <input type="date" name="start_date" id="start_date"
             class="form-control form-control-sm"
             value="{{ $startDate }}" required>
    </div>
    <div class="col-auto">
      <label for="end_date" class="form-label">To:</label>
      <input type="date" name="end_date" id="end_date"
             class="form-control form-control-sm"
             value="{{ $endDate }}" required>
    </div>
    <div class="col-auto">
      <button type="submit" class="btn btn-primary btn-sm">
        <i class="fas fa-filter me-1"></i> Filter
      </button>
    </div>
  </form>

  @if($invoices->count())
    <div class="table-responsive">
      <table class="table table-sm table-bordered table-hover align-middle mb-4"
             style="font-family:Calibri, sans-serif;">
        <colgroup>
          <col style="width:4%">
          <col style="width:12%">
          <col style="width:12%">
          <col style="width:20%">
          <col style="width:15%">
          <col style="width:11%">
          <col style="width:11%">
          <col style="width:11%">
        </colgroup>
        <thead class="table-light text-nowrap">
          <tr>
            <th>#</th>
            <th>Date</th>
            <th>Invoice No</th>
            <th>Customer</th>
            <th>Vehicle</th>
            <th class="text-end">Subtotal</th>
            <th class="text-end">Discount</th>
            <th class="text-end">Grand Total</th>
          </tr>
        </thead>
        <tbody>
          @foreach($invoices as $i => $invoice)
            <tr>
              <td>{{ $i + 1 }}</td>
              <td>{{ $invoice->created_at->format('Y-m-d') }}</td>
              <td>{{ $invoice->invoice_no }}</td>
              <td>{{ $invoice->client->name ?? $invoice->customer_name }}</td>
              <td>{{ $invoice->vehicle->plate_number ?? $invoice->vehicle_name }}</td>
              <td class="text-end">₱{{ number_format($invoice->subtotal, 2) }}</td>
              <td class="text-end text-danger">
                ₱{{ number_format($invoice->combined_discount, 2) }}
                <br>
                <small class="text-muted">
                  (₱{{ number_format($invoice->item_discount, 2) }} + ₱{{ number_format($invoice->total_discount, 2) }})
                </small>
              </td>
              <td class="text-end">₱{{ number_format($invoice->grand_total, 2) }}</td>
            </tr>
          @endforeach
        </tbody>

        <tfoot class="table-secondary text-nowrap">
          @foreach($discountByDate as $row)
            <tr>
              <td colspan="6" class="text-end fw-bold">
                Discount on {{ $row['date'] }}:
              </td>
              <td colspan="2" class="text-end text-danger">
                ₱{{ number_format($row['discount'], 2) }}
              </td>
            </tr>
          @endforeach
          <tr>
            <td colspan="6" class="text-end fw-bold">Grand Total Discount:</td>
            <td colspan="2" class="text-end text-danger">
              ₱{{ number_format($totalDiscount, 2) }}
            </td>
          </tr>
        </tfoot>
      </table>
    </div>
  @else
    <div class="alert alert-info">No discount records found in this period.</div>
  @endif
</div>
@endsection

@extends('layouts.cashier')

@section('title', 'Invoice Details')

@section('content')
<style>
  @media print {
    body * {
        visibility: hidden !important;
    }

    #invoice-print, #invoice-print * {
        visibility: visible !important;
    }

    /* Make the invoice fill the full page */
    #invoice-print {
        position: absolute;
        top: 0;
        left: 26%; /* Moves the content to the center */
        transform: translateX(-50%); /* Offsets the width by 50% to center it */
        width: 100vw; /* Use viewport width */
        height: 100vh; /* Use viewport height */
        margin: 0;
        padding: 0;
        background: white !important;
        box-sizing: border-box;
        display: block;
        overflow: hidden;
    }

    .no-print, .no-print * {
        display: none !important;
    }

    /* Styling the header for the print */
    .invoice-header-bar {
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
        background: #FFD71A !important;
        color: #000 !important;
    }

    .invoice-header-bar img {
        filter: none !important;
    }

    /* Ensuring no margins around the page for full width and height */
    @page {
        margin: 0;
        size: A4; /* You can change to letter if needed */
    }

    /* Resetting body and html to have no margin/padding for full page use */
    body, html {
        margin: 0;
        padding: 0;
        width: 100%;
        height: 100%;
        overflow: hidden;
    }

    /* Ensure content inside the invoice container is fully aligned */
    .invoice-container {
        width: 100%;
        height: 100%;
        padding: 0;
        margin: 0;
        box-sizing: border-box;
    }

    /* Set the table layout to fixed for better width control */
    .invoice-table {
        width: 100%;
        table-layout: fixed;
        border-collapse: collapse;
    }

    /* Adjust padding of table cells */
    .invoice-table td, .invoice-table th {
        padding: 5px; /* Adjust as needed */
    }

    /* Footer text size */
    .invoice-footer {
        font-size: 14px;
    }
}
  .invoice-main {
    border: 1px solid #eee;
    background: #fff;
    max-width: 900px;
    margin: 0 auto;
    box-shadow: 0 4px 32px rgba(0,0,0,0.08);
    font-size: 15px;
  }
  .invoice-header-bar {
    background: #FFD71A;
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    padding: 24px 24px 18px 24px;
  }
  .invoice-header-bar .text {
    font-family: Arial, sans-serif;
  }
  .invoice-header-bar h2 {
    font-size: 2rem;
    font-weight: bold;
    margin-bottom: 2px;
  }
  .invoice-header-bar h4 {
    margin: 0 0 5px 0;
    font-weight: bold;
    letter-spacing: 0.5px;
  }
  .invoice-header-bar p {
    margin: 0;
    font-size: 1rem;
    color: #333;
  }
  .invoice-header-bar img {
    max-height: 120px;
    margin-left: 32px;
    background: none;
  }
  .details-section {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 16px;
    margin: 24px;
  }
  .details-table, .right-details-table {
    width: 100%;
    border-collapse: collapse;
    background: #fff;
  }
  .details-table td, .right-details-table td {
    padding: 7px 10px;
    border: 1px solid #c5c5c5;
    font-size: 1rem;
    background: #f9f9f9;
  }
  .details-table .label {
    background: #f4f4f4;
    font-weight: bold;
    width: 32%;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: #222;
  }
  .right-details-table td {
    text-align: center;
    font-size: 1.02rem;
    background: #fff;
    font-weight: bold;
  }
  .items-table, .labor-table, .totals-table {
    width: 95%;
    margin: 20px auto 0 auto;
    border-collapse: collapse;
  }
  .items-table th, .labor-table th, .items-table td, .labor-table td {
    border: 1px solid #ccc;
    padding: 9px 8px;
    font-size: 1rem;
  }
  .items-table th, .labor-table th {
    background: #f7f7f7;
    font-weight: bold;
    text-align: left;
  }
  .labor-label {
    font-weight: bold;
    background: #f7f7f7;
    text-align: right;
  }
  .totals-table {
    max-width: 350px;
    margin-left: auto;
    margin-top: 20px;
    margin-bottom: 20px;
  }
  .totals-table td {
    border: none;
    padding: 5px 10px;
    font-size: 1.02rem;
    background: none;
  }
  .totals-table tr td:last-child {
    text-align: right;
    font-weight: bold;
  }
  .totals-table tr:last-child td {
    font-size: 1.1rem;
    border-top: 1.5px solid #222;
  }
  .signature {
    text-align: center;
    margin: 55px 0 10px 0;
    font-weight: bold;
    font-size: 1rem;
    letter-spacing: 1px;
    border-top: 2px solid #111;
    max-width: 320px;
    margin-left: auto;
    margin-right: auto;
    padding-top: 7px;
  }
  .no-print { margin-bottom: 15px; }
  .print-btn {
    float: right;
    margin-top: -15px;
    margin-right: 10px;
    margin-bottom: 5px;
  }
</style>

<div class="container mt-4">
  <div class="no-print">
    <a href="{{ route('cashier.history') }}" class="btn btn-sm btn-secondary mb-2">← Back to History</a>
    <button onclick="printInvoice()" class="btn btn-sm btn-warning print-btn">
      🖨 Print
    </button>
  </div>
  <div id="invoice-print" class="invoice-main">
    <div class="invoice-header-bar">
      <div class="text">
        <h2>SERVICE INVOICE</h2>
        <h4>AZ AUTO ZOLUTIONS</h4>
        <p>CAR CARE CENTER<br>
        Corner Kia Street, Bagay Road, Tuguegarao City, Cagayan 3500</p>
      </div>
      <img src="{{ asset('images/logo-print.png') }}" alt="Auto Zolutions Logo">
    </div>
    <div class="details-section">
      <table class="details-table">
        <tr>
          <td class="label">NAME</td>
          <td>{{ $record->client->name ?? $record->customer_name }}</td>
        </tr>
        <tr>
          <td class="label">PLATE NO</td>
          <td>{{ $record->vehicle->plate_number ?? $record->vehicle_name }}</td>
        </tr>
        <tr>
          <td class="label">MODEL</td>
          <td>{{ $record->vehicle->model ?? 'N/A' }}</td>
        </tr>
        <tr>
          <td class="label">YEAR</td>
          <td>{{ $record->vehicle->year ?? 'N/A' }}</td>
        </tr>
        <tr>
          <td class="label">COLOR</td>
          <td>{{ $record->vehicle->color ?? 'N/A' }}</td>
        </tr>
        <tr>
          <td class="label">ODOMETER</td>
          <td>{{ $record->vehicle->odometer ?? 'N/A' }}</td>
        </tr>
      </table>
      <table class="right-details-table">
        <tr>
          <td style="background:#fafafa;">DATE</td>
          <td>{{ $record->created_at->format('m/d/Y') }}</td>
        </tr>
        <tr>
          <td style="background:#fafafa;">RECEIVED</td>
          <td>{{ $record->created_at->format('h:i A') }}</td>
        </tr>
      </table>
    </div>
    <table class="items-table">
      <thead>
        <tr>
          <th style="width:80px;">QUANTITY</th>
          <th>DESCRIPTION</th>
          <th style="width:130px;">UNIT PRICE</th>
          <th style="width:140px;">LINE TOTAL</th>
        </tr>
      </thead>
      <tbody>
        @foreach($record->items as $item)
          <tr>
            <td>{{ $item->quantity }}</td>
            <td>
              {{ $item->part->item_name ?? $item->description ?? '-' }}
            </td>
            <td>₱{{ number_format($item->discounted_price ?? $item->original_price, 2) }}</td>
            <td>₱{{ number_format($item->line_total, 2) }}</td>
          </tr>
        @endforeach
      </tbody>
    </table>
    <table class="labor-table">
      <thead>
        <tr>
          <th>Description</th>
          <th>Technician</th>
          <th>Cost</th>
        </tr>
      </thead>
      <tbody>
        @forelse($record->jobs as $job)
          <tr>
            <td>{{ $job->job_description }}</td>
            <td>{{ $job->technician->name ?? '-' }}</td>
            <td>₱{{ number_format($job->total, 2) }}</td>
          </tr>
        @empty
          <tr>
            <td colspan="2">-</td>
            <td>₱0.00</td>
          </tr>
        @endforelse
      </tbody>
      <tfoot>
        <tr>
          <td colspan="2" class="labor-label">LABOR TOTAL</td>
          <td>₱{{ number_format($record->jobs->sum('total'), 2) }}</td>
        </tr>
      </tfoot>
    </table>
    <table class="totals-table">
      <tr>
        <td>SUBTOTAL</td>
        <td>₱{{ number_format($record->subtotal, 2) }}</td>
      </tr>
      <tr>
        <td>TOTAL DISCOUNT</td>
        <td>₱{{ number_format($record->total_discount, 2) }}</td>
      </tr>
      <tr>
        <td>VAT AMOUNT (12%)</td>
        <td>₱{{ number_format($record->vat_amount, 2) }}</td>
      </tr>
      <tr>
        <td><strong>TOTAL</strong></td>
        <td><strong>₱{{ number_format($record->grand_total, 2) }}</strong></td>
      </tr>
    </table>
    <div class="signature">CUSTOMER NAME & SIGNATURE</div>
  </div>
</div>

<script>
function printInvoice() {
  window.print();
}
</script>
@endsection

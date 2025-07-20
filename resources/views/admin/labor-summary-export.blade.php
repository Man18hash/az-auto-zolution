@extends('layouts.admin')

@section('title', 'Labor Summary Export')

@section('content')
<style>
  @page {
    size: A4 portrait;
    margin: 20px;
  }
  html, body {
    font-family: DejaVu Sans, sans-serif; /* Fixes ₱ symbol */
    font-size: 11px;
    margin: 0 !important;
    padding: 0 !important;
    color: #000;
    background: #fff;
    width: 100%;
    height: 100%;
  }
  .container {
    width: 100%;
    margin: 0 !important;
    padding: 0 !important;
  }
  .report-title,
  .date-range,
  .grand-total {
    margin: 0 !important;
    padding: 0 !important;
  }
  .report-title {
    font-size: 16px;
    font-weight: bold;
    text-align: center;
    padding: 6px 0 2px 0;
  }
  .date-range {
    text-align: center;
    font-size: 11px;
    padding: 0 0 2px 0;
  }
  .grand-total {
    text-align: center;
    font-size: 12px;
    font-weight: bold;
    padding: 0 0 6px 0;
  }
  .date-header {
    font-weight: bold;
    background-color: #dfe6e9;
    padding: 4px;
    margin: 0 !important; /* REMOVE ALL MARGIN */
    border: 1px solid #000;
    page-break-after: avoid;
    page-break-inside: avoid;
  }
  /* Only add margin-top if not first date-header */
  .date-header + .date-header {
    margin-top: 12px !important;
  }
  table {
    width: 100%;
    border-collapse: collapse;
    margin: 0 0 6px 0;
    page-break-inside: avoid;
  }
  th, td {
    border: 1px solid #000;
    padding: 5px 2px;
    text-align: left;
    vertical-align: top;
    font-size: 11px;
  }
  th {
    background-color: #f1f2f6;
    font-weight: bold;
  }
  .text-end {
    text-align: right;
  }
  /* Remove gap above first block for print/PDF */
  .container > *:first-child {
    margin-top: 0 !important;
    padding-top: 0 !important;
  }
  tr, td, th {
    page-break-inside: avoid;
  }
</style>

<div class="container">
  <div class="report-title">Labor Summary Report</div>
  <div class="date-range">Coverage: {{ $startDate->format('F d, Y') }} to {{ $endDate->format('F d, Y') }}</div>
  <div class="grand-total">
    Total Labor: ₱{{ number_format(collect($grouped)->flatten()->sum('labor_charge'), 2) }}
  </div>
@php $first = true; @endphp
  @foreach($grouped as $day => $rows)
    <div class="date-header"
      @if ($first) style="margin-top:0 !important;" @else style="margin-top:12px !important;" @endif
    >Date: {{ \Carbon\Carbon::parse($day)->format('F d, Y') }}</div>
    <table>
      <thead>
        <tr>
          <th style="width: 35%;">Customer Name</th>
          <th style="width: 45%;">Job Description</th>
          <th style="width: 20%;" class="text-end">Amount</th>
        </tr>
      </thead>
      <tbody>
        @php $lastCustomer = null; @endphp
        @foreach($rows as $row)
          <tr>
            <td>{{ $row->customer_name !== $lastCustomer ? $row->customer_name : '' }}</td>
            <td>{{ $row->job_description }}</td>
            <td class="text-end">₱{{ number_format($row->labor_charge, 2) }}</td>
          </tr>
          @php $lastCustomer = $row->customer_name; @endphp
        @endforeach
      </tbody>
    </table>
    @php $first = false; @endphp
  @endforeach
</div>
@endsection

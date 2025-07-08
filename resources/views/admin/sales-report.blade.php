@extends('layouts.admin')
@section('title', 'Sales Report')

@section('content')
<div class="col-12 col-md-auto mb-3">
    <a href="{{ route('admin.sales-report.export', ['start_date'=>$startDate, 'end_date'=>$endDate]) }}" 
       class="btn btn-success px-4 fw-bold"
       style="box-shadow:0 2px 8px #5bd95b33;"
       target="_blank">
       <i class="fas fa-file-excel me-1"></i> Export to Excel
    </a>
</div>
<div class="container-fluid px-2 px-md-4">
    <h2 class="mb-4 fw-bold">Sales Report</h2>
    <form method="GET" action="{{ route('admin.sales-report') }}" class="row g-3 align-items-end mb-4">
        <div class="col-12 col-md-auto">
            <label for="start_date" class="form-label mb-0 fw-semibold">From:</label>
            <input type="date" id="start_date" name="start_date" value="{{ $startDate }}" class="form-control" required>
        </div>
        <div class="col-12 col-md-auto">
            <label for="end_date" class="form-label mb-0 fw-semibold">To:</label>
            <input type="date" id="end_date" name="end_date" value="{{ $endDate }}" class="form-control" required>
        </div>
        <div class="col-12 col-md-auto">
            <button class="btn btn-warning px-4 fw-bold w-100" type="submit"><i class="fas fa-filter me-1"></i>Filter</button>
        </div>
    </form>

    {{-- Compute per-day and overall totals --}}
    @php
        $byDate = collect($allItems)->groupBy('date');
        $grandSales = 0; $grandCost = 0; $grandProfit = 0;
    @endphp

    <div class="mb-3 d-flex flex-wrap align-items-end gap-4">
        <div class="bg-info bg-opacity-10 border border-info rounded-3 p-3 shadow-sm me-4 mb-2">
            <h5 class="mb-2 text-info" style="font-weight:bold;">GROSS TOTAL ({{ $startDate }} to {{ $endDate }})</h5>
            <div><b>Total Sales:</b> <span class="text-primary">₱{{ number_format($totalSales,2) }}</span></div>
            <div><b>Total Cost:</b> <span>₱{{ number_format($totalCost,2) }}</span></div>
            <div><b>Total Profit:</b> <span class="text-success">₱{{ number_format($totalProfit,2) }}</span></div>
        </div>
        @foreach($byDate as $date => $items)
            @php
                $daySales = collect($items)->sum('line_total');
                $dayCost = collect($items)->sum(fn($item) => ($item['acquisition_price'] ?? 0) * ($item['quantity'] ?? 1));
                $dayProfit = $daySales - $dayCost;
            @endphp
            <div class="bg-warning bg-opacity-10 border border-warning rounded-3 p-3 shadow-sm mb-2">
                <div class="mb-1" style="font-size:1.05em;">
                    <i class="fas fa-calendar-alt"></i> 
                    <b>{{ \Carbon\Carbon::parse($date)->format('F d, Y') }}</b>
                </div>
                <div><b>Sales:</b> <span class="text-primary">₱{{ number_format($daySales,2) }}</span></div>
                <div><b>Cost:</b> <span>₱{{ number_format($dayCost,2) }}</span></div>
                <div><b>Profit:</b> <span class="text-success">₱{{ number_format($dayProfit,2) }}</span></div>
            </div>
        @endforeach
    </div>

    @forelse($byDate as $date => $items)
        <h4 class="fw-bold mt-4 mb-3 text-warning" style="letter-spacing:1px;">
            <i class="fas fa-calendar-day me-1"></i>
            {{ \Carbon\Carbon::parse($date)->format('F d, Y') }}
        </h4>
        <div class="table-responsive mb-5">
            <table class="table table-bordered align-middle shadow-sm mb-0" style="background: #fff;">
                <thead style="background: #ffe066;">
                    <tr class="text-center">
                        <th style="min-width:170px;">Customer Name</th>
                        <th style="min-width:150px;">Item</th>
                        <th style="min-width:80px;">Quantity</th>
                        <th style="min-width:140px;">Acquisition Price</th>
                        <th style="min-width:140px;">Selling Price</th>
                        <th style="min-width:140px;">Line Total</th>
                        <th style="min-width:160px;">Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $customerGroups = collect($items)->groupBy('customer_name');
                    @endphp
                    @foreach($customerGroups as $customer => $customerItems)
                        @foreach($customerItems as $i => $item)
                            <tr>
                                @if($i === 0)
  <td class="fw-semibold align-middle" rowspan="{{ count($customerItems) }}">
    {{ $customer }} – 
    {{ $item['vehicle_manufacturer'] }} 
    {{ $item['vehicle_model'] }} 
    ({{ $item['vehicle_plate'] }}) 
    {{ $item['vehicle_year'] }}
  </td>
@endif
                                <td>{{ $item['item_name'] }}</td>
                                <td class="text-center">{{ $item['quantity'] }}</td>
                                <td class="text-end">₱{{ number_format($item['acquisition_price'],2) }}</td>
                                <td class="text-end">₱{{ number_format($item['selling_price'],2) }}</td>
                                <td class="text-end fw-bold">₱{{ number_format($item['line_total'],2) }}</td>
                                <td>{{ $item['remarks'] ?? '-' }}</td>
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
                @php
                    $daySales = collect($items)->sum('line_total');
                    $dayCost = collect($items)->sum(fn($item) => ($item['acquisition_price'] ?? 0) * ($item['quantity'] ?? 1));
                    $dayProfit = $daySales - $dayCost;
                @endphp
                <tfoot style="background: #fff6c1;">
                    <tr>
                        <td colspan="5" class="text-end fw-bold">Total Sales:</td>
                        <td class="fw-bold text-primary text-end">₱{{ number_format($daySales,2) }}</td>
                        <td></td>
                    </tr>
                    <tr>
                        <td colspan="5" class="text-end fw-bold">Total Cost:</td>
                        <td class="fw-bold text-end">₱{{ number_format($dayCost,2) }}</td>
                        <td></td>
                    </tr>
                    <tr>
                        <td colspan="5" class="text-end fw-bold">Total Profit:</td>
                        <td class="fw-bold text-success text-end">₱{{ number_format($dayProfit,2) }}</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    @empty
        <div class="alert alert-info mt-4">No paid sales in this period.</div>
    @endforelse
</div>
@endsection

@extends('layouts.admin')
@section('title', 'Gross Sales Report')

@section('content')
<div class="container-fluid px-2 px-md-4">
    <h2 class="mb-4 fw-bold">Gross Sales Report</h2>

    <form method="GET" action="{{ route('admin.gross-sales-report') }}" class="row g-3 align-items-end mb-4">
        <div class="col-12 col-md-auto">
            <label for="start_date" class="form-label mb-0 fw-semibold">From:</label>
            <input type="date" id="start_date" name="start_date" value="{{ $startDate }}" class="form-control" required>
        </div>
        <div class="col-12 col-md-auto">
            <label for="end_date" class="form-label mb-0 fw-semibold">To:</label>
            <input type="date" id="end_date" name="end_date" value="{{ $endDate }}" class="form-control" required>
        </div>
        <div class="col-12 col-md-auto">
            <button class="btn btn-warning px-4 fw-bold w-100" type="submit">
                <i class="fas fa-filter me-1"></i> Filter
            </button>
        </div>
    </form>

    <form method="GET" action="{{ route('admin.gross-sales-report.export') }}" class="mb-4">
        <input type="hidden" name="start_date" value="{{ $startDate }}">
        <input type="hidden" name="end_date" value="{{ $endDate }}">
        <button type="submit" class="btn btn-success fw-bold">
            <i class="fas fa-file-excel me-1"></i> Export to Excel
        </button>
    </form>

    <div class="alert alert-primary mb-4">
        <h5>Gross Totals for Filtered Period:</h5>
        <div><b>Total Sales:</b> ₱{{ number_format($grand['sales'], 2) }}</div>
        <div><b>Total A/R:</b> ₱{{ number_format($grand['ar'], 2) }}</div>
        <div><b>Total Expenses:</b> ₱{{ number_format($grand['expenses'], 2) }}</div>
        <div><b>Total Cash Deposits:</b> ₱{{ number_format($grand['deposits'], 2) }}</div>
        <div><b>Gross Total:</b> ₱{{ number_format($grand['gross'], 2) }}</div>
    </div>

    @forelse($report as $day)
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-info text-white fw-bold" style="font-size: 1.2rem;">
                {{ \Carbon\Carbon::parse($day['date'])->format('F d, Y') }}
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm mb-2">
                        <thead class="table-light text-center">
                            <tr>
                                <th style="min-width: 180px;">Customer / Category</th>
                                <th style="min-width: 200px;">Description</th>
                                <th style="min-width: 100px;">Qty</th>
                                <th style="min-width: 150px;">Amount</th>
                                <th>Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                        @php
                            // Build a flat list of all transactions for this day:
                            $rows = [];
                            foreach ($day['sales'] as $sale) {
                                $rows[] = array_merge($sale, ['type'=>'Sales']);
                            }
                            foreach ($day['ar'] as $ar) {
                                $rows[] = [
                                    'customer'    => 'A/R Collections',
                                    'service'     => $ar->description ?? '-',
                                    'quantity'    => '',
                                    'amount'      => $ar->amount,
                                    'remarks'     => '',
                                    'type'        => 'A/R',
                                ];
                            }
                            foreach ($day['expenses'] as $ex) {
                                $rows[] = [
                                    'customer'    => 'Expenses',
                                    'service'     => $ex->title ?? '-',
                                    'quantity'    => '',
                                    'amount'      => $ex->amount,
                                    'remarks'     => '',
                                    'type'        => 'Expense',
                                ];
                            }
                            foreach ($day['deposits'] as $dep) {
                                $rows[] = [
                                    'customer'    => 'Cash Deposits',
                                    'service'     => $dep->description ?? '-',
                                    'quantity'    => '',
                                    'amount'      => $dep->amount,
                                    'remarks'     => '',
                                    'type'        => 'Deposit',
                                ];
                            }
                            // Track whether we've already printed each customer
                            $printed = [];
                        @endphp

                        @foreach($rows as $row)
                            <tr>
                                <td class="fw-bold">
                                    @if(! in_array($row['customer'], $printed))
                                        {{ $row['customer'] }}
                                        {{-- Vehicle details appended only once --}}
                                        @if(!empty($row['vehicle_manufacturer'] ?? '') ||
                                            !empty($row['vehicle_model'] ?? '')        ||
                                            !empty($row['vehicle_year'] ?? '')         ||
                                            !empty($row['vehicle_plate'] ?? '')
                                        )
                                          – {{
                                            trim(
                                              ($row['vehicle_manufacturer'] ?? '') . ' ' .
                                              ($row['vehicle_model']        ?? '') . ' ' .
                                              ($row['vehicle_year']         ?? '') .
                                              ' (' . ($row['vehicle_plate'] ?? '') . ')'
                                            )
                                          }}
                                        @endif
                                        @php $printed[] = $row['customer']; @endphp
                                    @endif
                                </td>
                                <td>{{ $row['service'] }}</td>
                                <td class="text-center">{{ $row['quantity'] }}</td>
                                <td class="text-end">₱{{ number_format($row['amount'], 2) }}</td>
                                <td>{{ $row['remarks'] }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="fw-bold">
                                <td class="text-end" colspan="3">Total Sales:</td>
                                <td class="text-end text-primary">₱{{ number_format($day['total_sales'],2) }}</td>
                                <td></td>
                            </tr>
                            <tr class="fw-bold">
                                <td class="text-end" colspan="3">Total A/R:</td>
                                <td class="text-end text-success">₱{{ number_format($day['total_ar'],2) }}</td>
                                <td></td>
                            </tr>
                            <tr class="fw-bold">
                                <td class="text-end" colspan="3">Total Expenses:</td>
                                <td class="text-end text-danger">₱{{ number_format($day['total_expenses'],2) }}</td>
                                <td></td>
                            </tr>
                            <tr class="fw-bold">
                                <td class="text-end" colspan="3">Total Deposits:</td>
                                <td class="text-end text-secondary">₱{{ number_format($day['total_deposits'],2) }}</td>
                                <td></td>
                            </tr>
                            <tr class="fw-bold">
                                <td class="text-end" colspan="3">
                                  Gross Total for {{ \Carbon\Carbon::parse($day['date'])->format('F d, Y') }}:
                                </td>
                                <td class="text-end">₱{{ number_format($day['gross'], 2) }}</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    @empty
        <div class="alert alert-info mt-4">No records found in this period.</div>
    @endforelse
</div>
@endsection

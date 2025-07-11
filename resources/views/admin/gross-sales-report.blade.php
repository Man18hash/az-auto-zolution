@extends('layouts.admin')
@section('title', 'Gross Sales Report')

@section('content')
<div class="container-fluid px-2 px-md-4">
  <h2 class="mb-4 fw-bold">Gross Sales Report</h2>

  {{-- Filter & Export Forms --}}
  <form method="GET" action="{{ route('admin.gross-sales-report') }}" class="row g-3 align-items-end mb-4">
    <div class="col-auto">
      <label class="form-label mb-0 fw-semibold">From:</label>
      <input type="date" name="start_date" value="{{ $startDate }}" class="form-control" required>
    </div>
    <div class="col-auto">
      <label class="form-label mb-0 fw-semibold">To:</label>
      <input type="date" name="end_date" value="{{ $endDate }}" class="form-control" required>
    </div>
    <div class="col-auto">
      <button class="btn btn-warning px-4 fw-bold">
        <i class="fas fa-filter me-1"></i> Filter
      </button>
    </div>
  </form>
  <form method="GET" action="{{ route('admin.gross-sales-report.export') }}" class="mb-4">
    <input type="hidden" name="start_date" value="{{ $startDate }}">
    <input type="hidden" name="end_date"   value="{{ $endDate }}">
    <button class="btn btn-success fw-bold">
      <i class="fas fa-file-excel me-1"></i> Export to Excel
    </button>
  </form>

  {{-- Grand Totals --}}
  <div class="alert alert-primary mb-4">
    <h5>Gross Totals for Filtered Period:</h5>
    <div><b>Total Sales:</b>        ₱{{ number_format($grand['sales'],    2) }}</div>
    <div><b>Total A/R:</b>          ₱{{ number_format($grand['ar'],       2) }}</div>
    <div><b>Total Expenses:</b>     ₱{{ number_format($grand['expenses'], 2) }}</div>
    <div><b>Total Cash Deposits:</b>₱{{ number_format($grand['deposits'], 2) }}</div>
    <div><b>Total Discounts:</b>    ₱{{ number_format($grand['discounts'] ?? 0, 2) }}</div>
    <div><b>Gross Total:</b>        ₱{{ number_format(
      $grand['sales'] + $grand['ar']
      - $grand['expenses'] - $grand['deposits']
      - ($grand['discounts'] ?? 0)
    , 2) }}</div>
  </div>

  @forelse($report as $day)
    <div class="card mb-4 shadow-sm">
      <div class="card-header bg-info text-white fw-bold">
        {{ \Carbon\Carbon::parse($day['date'])->format('F d, Y') }}
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-bordered table-sm mb-0">
            <thead class="table-light text-center">
              <tr>
                <th style="min-width:200px;">Invoice / Customer / Vehicle</th>
                <th>Description</th>
                <th style="width:80px;">Qty</th>
                <th style="width:120px;">Amount</th>
                <th>Remarks</th>
              </tr>
            </thead>
            <tbody>
              @php
                // build flat rows
                $rows = [];
                foreach ($day['sales']    as $s)   { $rows[] = (array)$s + ['type'=>'Sales']; }
                foreach ($day['ar']       as $ar)  { $rows[] = [
                  'invoice_no'=>null,'customer'=>'A/R Collections','service'=>$ar->description,
                  'quantity'=>'','amount'=>$ar->amount,'remarks'=>$ar->remarks??'',
                  'vehicle_manufacturer'=>'','vehicle_model'=>'','vehicle_year'=>'','vehicle_plate'=>'',
                  'type'=>'A/R'
                ]; }
                foreach ($day['expenses'] as $ex)  { $rows[] = [
                  'invoice_no'=>null,'customer'=>'Expenses','service'=>$ex->title,
                  'quantity'=>'','amount'=>$ex->amount,'remarks'=>$ex->remarks??'',
                  'vehicle_manufacturer'=>'','vehicle_model'=>'','vehicle_year'=>'','vehicle_plate'=>'',
                  'type'=>'Expense'
                ]; }
                foreach ($day['deposits'] as $dep) { $rows[] = [
                  'invoice_no'=>null,'customer'=>'Cash Deposits','service'=>$dep->description,
                  'quantity'=>'','amount'=>$dep->amount,'remarks'=>$dep->remarks??'',
                  'vehicle_manufacturer'=>'','vehicle_model'=>'','vehicle_year'=>'','vehicle_plate'=>'',
                  'type'=>'Deposit'
                ]; }
                $invoices = collect($rows)->where('invoice_no','!=',null)->groupBy('invoice_no');
                $others   = collect($rows)->where('invoice_no',null);
              @endphp

              {{-- invoice groups --}}
              @foreach($invoices as $invNo => $items)
                @php
                  $hdr         = $items->first();
                  $invSales    = $items->sum(fn($i)=> $i['amount']);
                  $invDiscount = $hdr['discount'] ?? 0;
                  $invClient   = ($hdr['payment'] ?? 0) - $invDiscount;
                  $ptype       = in_array($hdr['payment_type'], ['debit','credit'])
                                 ? 'Non-Cash' : ucfirst($hdr['payment_type'] ?? 'Cash');
                @endphp

                {{-- header --}}
                <tr class="table-secondary">
                  <td colspan="5" class="fw-bold">
                    Invoice #{{ $invNo }} — {{ $hdr['customer'] }}
                    @if($hdr['vehicle_manufacturer']||$hdr['vehicle_model']
                        ||$hdr['vehicle_year']||$hdr['vehicle_plate'])
                      &ndash; {{ trim(
                        "{$hdr['vehicle_manufacturer']} {$hdr['vehicle_model']}".
                        " {$hdr['vehicle_year']} ({$hdr['vehicle_plate']})"
                      ) }}
                    @endif
                    @if(!empty($hdr['remarks']))
                      <div class="fst-italic small mt-1">
                        Remark: {{ $hdr['remarks'] }}
                      </div>
                    @endif
                  </td>
                </tr>

                {{-- line items --}}
                @foreach($items as $row)
                  <tr>
                    <td></td>
                    <td>{{ $row['service'] }}</td>
                    <td class="text-center">{{ $row['quantity'] }}</td>
                    <td class="text-end">₱{{ number_format($row['amount'],2) }}</td>
                    <td></td>
                  </tr>
                @endforeach

                {{-- separate summary rows --}}
                <tr class="fw-bold">
                  <td colspan="4" class="text-end">Discount:</td>
                  <td class="text-end">₱{{ number_format($invDiscount,2) }}</td>
                </tr>
                <tr class="fw-bold">
                  <td colspan="4" class="text-end">Client Total:</td>
                  <td class="text-end">₱{{ number_format($invClient,2) }}</td>
                </tr>
                <tr class="fw-bold">
                  <td colspan="4" class="text-end">Payment Type:</td>
                  <td class="text-end">{{ $ptype }}</td>
                </tr>
              @endforeach

              {{-- others --}}
              @foreach($others as $row)
                <tr>
                  <td class="fw-bold">{{ $row['customer'] }}</td>
                  <td>{{ $row['service'] }}</td>
                  <td class="text-center">{{ $row['quantity'] }}</td>
                  <td class="text-end">₱{{ number_format($row['amount'],2) }}</td>
                  <td>{{ $row['remarks'] }}</td>
                </tr>
              @endforeach
            </tbody>

            {{-- day footer --}}
            @php
              $dayDiscount = collect($day['sales'])->sum(fn($s)=> $s['discount'] ?? 0);
            @endphp
            <tfoot class="bg-light">
              <tr class="fw-bold">
                <td colspan="4" class="text-end">Total Sales:</td>
                <td class="text-end text-primary">₱{{ number_format($day['total_sales'],2) }}</td>
              </tr>
              <tr class="fw-bold">
                <td colspan="4" class="text-end">Total A/R:</td>
                <td class="text-end text-success">₱{{ number_format($day['total_ar'],2) }}</td>
              </tr>
              <tr class="fw-bold">
                <td colspan="4" class="text-end">Total Expenses:</td>
                <td class="text-end text-danger">₱{{ number_format($day['total_expenses'],2) }}</td>
              </tr>
              <tr class="fw-bold">
                <td colspan="4" class="text-end">Total Deposits:</td>
                <td class="text-end text-secondary">₱{{ number_format($day['total_deposits'],2) }}</td>
              </tr>
              <tr class="fw-bold">
                <td colspan="4" class="text-end">Total Discounts:</td>
                <td class="text-end">₱{{ number_format($dayDiscount,2) }}</td>
              </tr>
              <tr class="fw-bold">
                <td colspan="4" class="text-end">Gross Total:</td>
                <td class="text-end">
                  ₱{{ number_format(
                    $day['total_sales'] + $day['total_ar']
                    - $day['total_expenses']
                    - $day['total_deposits']
                    - $dayDiscount
                  , 2) }}
                </td>
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

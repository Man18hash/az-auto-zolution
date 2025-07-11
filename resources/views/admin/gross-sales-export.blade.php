<table>
  @php
    use Carbon\Carbon;

    // Group all items by date
    $byDate     = collect($allItems)->groupBy('date');
    $blocks     = [];
    $grandGross = 0;

    foreach ($byDate->keys()->sort()->values() as $date) {
      $items    = $byDate[$date];
      // split into invoices vs orphan rows
      $invoices = $items->whereNotNull('invoice_no')->groupBy('invoice_no');
      $orphans  = $items->whereNull('invoice_no');

      // daily subtotals
      $daySales = $invoices->flatten(1)
                    ->where('type','Sales')
                    ->sum('amount');
      $dayAR    = $invoices->flatten(1)
                    ->where('type','A/R')
                    ->sum('amount');
      $dayExp   = $invoices->flatten(1)
                    ->where('type','Expense')
                    ->sum('amount')
                  + $orphans->where('type','Expense')->sum('amount');
      $dayDep   = $invoices->flatten(1)
                    ->where('type','Deposit')
                    ->sum('amount')
                  + $orphans->where('type','Deposit')->sum('amount');
      $dayDisc  = $invoices->map(fn($group) => $group->first()['discount'])->sum();
      // gross = sales + ar - exp - dep - discounts
      $dayGross = $daySales + $dayAR - $dayExp - $dayDep - $dayDisc;

      $grandGross += $dayGross;

      $blocks[] = [
        'date'     => $date,
        'invoices' => $invoices,
        'orphans'  => $orphans,
        'totals'   => compact('daySales','dayAR','dayExp','dayDep','dayDisc','dayGross'),
      ];
    }
  @endphp

  {{-- DATE HEADER --}}
  <tr>
    @foreach($blocks as $b)
      <th colspan="6" style="background:#ffe066;font-size:16px;">
        {{ Carbon::parse($b['date'])->format('F d, Y') }}
      </th>
    @endforeach
  </tr>

  {{-- COLUMN HEADERS --}}
  <tr>
    @foreach($blocks as $b)
      <th style="background:#ffe066">Invoice / Customer / Vehicle</th>
      <th style="background:#ffe066">Description</th>
      <th style="background:#ffe066">Qty</th>
      <th style="background:#ffe066">Amount</th>
      <th style="background:#ffe066">Type</th>
      <th style="background:#ffe066">Remarks</th>
    @endforeach
  </tr>

  {{-- DATA BLOCKS --}}
  @foreach($blocks as $b)
    @php
      $tot = $b['totals'];
    @endphp

    {{-- per‐invoice groups --}}
    @foreach($b['invoices'] as $invNo => $lines)
      @php
        $hdr    = $lines->first();
        $cust   = $hdr['customer'];
        $veh    = trim("{$hdr['vehicle_manufacturer']} {$hdr['vehicle_model']} {$hdr['vehicle_year']} ({$hdr['vehicle_plate']})");
        $descHd = "{$invNo} — {$cust}" . ($veh ? " – {$veh}" : '');
        $discount   = $hdr['discount'];
        $clientTotal= $hdr['payment'] - $discount;
        $ptype      = in_array($hdr['payment_type'], ['debit','credit'])
                      ? 'Non-Cash'
                      : ucfirst($hdr['payment_type'] ?? 'Cash');
      @endphp

      {{-- header row --}}
      <tr>
        <td colspan="6" style="background:#f0f0f0;font-weight:bold;">
          {{ $descHd }}
        </td>
      </tr>

      {{-- line items --}}
      @foreach($lines as $row)
        <tr>
          <td></td>
          <td>{{ $row['service'] ?? $row['description'] }}</td>
          <td class="text-center">{{ $row['quantity'] }}</td>
          <td>₱{{ number_format($row['amount'],2) }}</td>
          <td>{{ $row['type'] }}</td>
          <td>{{ $row['remarks'] ?? '' }}</td>
        </tr>
      @endforeach

      {{-- invoice subtotals --}}
      <tr>
        <td colspan="3"></td>
        <td><strong>Discount:</strong></td>
        <td colspan="2">₱{{ number_format($discount,2) }}</td>
      </tr>
      <tr>
        <td colspan="3"></td>
        <td><strong>Client Total:</strong></td>
        <td colspan="2">₱{{ number_format($clientTotal,2) }}</td>
      </tr>
      <tr>
        <td colspan="3"></td>
        <td><strong>Payment Type:</strong></td>
        <td colspan="2">{{ $ptype }}</td>
      </tr>
    @endforeach

    {{-- orphan rows (pure A/R, Expense, Deposit) --}}
    @foreach($b['orphans'] as $row)
      <tr>
        <td class="fw-bold">{{ $row['customer'] }}</td>
        <td>{{ $row['description'] }}</td>
        <td class="text-center">{{ $row['quantity'] }}</td>
        <td>₱{{ number_format($row['amount'],2) }}</td>
        <td>{{ $row['type'] }}</td>
        <td>{{ $row['remarks'] ?? '' }}</td>
      </tr>
    @endforeach

    {{-- daily totals footer --}}
    <tr>
      <td colspan="3" class="text-end"><strong>Total Sales:</strong></td>
      <td>₱{{ number_format($tot['daySales'],2) }}</td>
      <td colspan="2"></td>
    </tr>
    <tr>
      <td colspan="3" class="text-end"><strong>Total A/R:</strong></td>
      <td>₱{{ number_format($tot['dayAR'],2) }}</td>
      <td colspan="2"></td>
    </tr>
    <tr>
      <td colspan="3" class="text-end"><strong>Total Expenses:</strong></td>
      <td>₱{{ number_format($tot['dayExp'],2) }}</td>
      <td colspan="2"></td>
    </tr>
    <tr>
      <td colspan="3" class="text-end"><strong>Total Deposits:</strong></td>
      <td>₱{{ number_format($tot['dayDep'],2) }}</td>
      <td colspan="2"></td>
    </tr>
    <tr>
      <td colspan="3" class="text-end"><strong>Total Discounts:</strong></td>
      <td>₱{{ number_format($tot['dayDisc'],2) }}</td>
      <td colspan="2"></td>
    </tr>
    <tr>
      <td colspan="3" class="text-end"><strong>Gross Total:</strong></td>
      <td>₱{{ number_format($tot['dayGross'],2) }}</td>
      <td colspan="2"></td>
    </tr>
  @endforeach

  {{-- GRAND GROSS TOTAL --}}
  <tr><td colspan="{{ count($blocks)*6 }}"></td></tr>
  <tr>
    <td colspan="{{ count($blocks)*6 -1 }}" style="text-align:right;
        font-weight:bold;background:#bbb;color:#fff;">
      Grand Gross Total:
    </td>
    <td style="font-weight:bold;background:#bbb;color:#fff;">
      ₱{{ number_format($grandGross,2) }}
    </td>
  </tr>
</table>

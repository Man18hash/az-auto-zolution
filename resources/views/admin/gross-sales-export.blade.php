@php
    $byDate = collect($allItems)->groupBy('date');
    $blocks = [];
    $grandSales = $grandAR = $grandExpenses = $grandDeposits = 0;

    foreach ($byDate->keys()->sort()->values() as $date) {
        $rows = [];
        $daySales = $dayAR = $dayExpenses = $dayDeposits = 0;

        foreach ($byDate[$date] as $item) {
            $rows[] = [
                'customer'             => $item['customer']             ?? '-',
                'vehicle_manufacturer' => $item['vehicle_manufacturer'] ?? '',
                'vehicle_model'        => $item['vehicle_model']        ?? '',
                'vehicle_year'         => $item['vehicle_year']         ?? '',
                'vehicle_plate'        => $item['vehicle_plate']        ?? '',
                'qty'                  => $item['quantity']             ?? '',
                'description'          => $item['description']          ?? '-',
                'amount'               => $item['amount']               ?? 0,
                'remarks'              => $item['remarks']              ?? '',
                'type'                 => $item['type']                 ?? 'Sales',
            ];

            match ($item['type']) {
                'Sales'   => $daySales    += $item['amount'],
                'A/R'     => $dayAR       += $item['amount'],
                'Expense' => $dayExpenses += $item['amount'],
                'Deposit' => $dayDeposits += $item['amount'],
                default   => null,
            };
        }

        $blocks[] = [
            'date'       => $date,
            'rows'       => $rows,
            'totalSales' => $daySales,
            'totalAR'    => $dayAR,
            'totalExp'   => $dayExpenses,
            'totalDep'   => $dayDeposits,
            'gross'      => $daySales + $dayAR - $dayExpenses + $dayDeposits,
        ];

        $grandSales    += $daySales;
        $grandAR       += $dayAR;
        $grandExpenses += $dayExpenses;
        $grandDeposits += $dayDeposits;
    }
@endphp

<table>
  {{-- DATE HEADERS --}}
  <tr>
    @foreach($blocks as $b)
      <th colspan="5" style="background:#ffe066;font-size:16px;">
        {{ \Carbon\Carbon::parse($b['date'])->format('F d, Y') }}
      </th>
    @endforeach
  </tr>

  {{-- COLUMN HEADERS --}}
  <tr>
    @foreach($blocks as $b)
      <th style="background:#ffe066">Customer / Vehicle</th>
      <th style="background:#ffe066">Qty</th>
      <th style="background:#ffe066">Description</th>
      <th style="background:#ffe066">Amount</th>
      <th style="background:#ffe066">Remarks</th>
    @endforeach
  </tr>

  {{-- DATA ROWS --}}
  @php
    // keep track to only show customer + vehicle once per block
    $prevCust = array_fill(0, count($blocks), null);
    $maxRows   = max(array_map(fn($b)=>count($b['rows']), $blocks));
  @endphp

  @for($i = 0; $i < $maxRows; $i++)
    <tr>
      @foreach($blocks as $idx => $b)
        @php $r = $b['rows'][$i] ?? null; @endphp

        @if($r)
          @php
            if($prevCust[$idx] !== $r['customer']) {
              $prevCust[$idx] = $r['customer'];
              // build display string
              $disp = $r['customer'];
              $veh  = trim("{$r['vehicle_manufacturer']} {$r['vehicle_model']} {$r['vehicle_year']} ({$r['vehicle_plate']})");
              if($veh) {
                $disp .= " – {$veh}";
              }
            } else {
              $disp = '';
            }
          @endphp

          <td>{{ $disp }}</td>
          <td class="text-center">{{ $r['qty'] }}</td>
          <td>{{ $r['description'] }}</td>
          <td>₱{{ number_format($r['amount'], 2) }}</td>
          <td>{{ $r['remarks'] }}</td>
        @else
          <td colspan="5"></td>
        @endif
      @endforeach
    </tr>
  @endfor

  {{-- PER‐BLOCK TOTALS --}}
  <tr>
    @foreach($blocks as $b)
      <td colspan="2"></td>
      <td><strong>Total Sales:</strong></td>
      <td><strong>₱{{ number_format($b['totalSales'],2) }}</strong></td>
      <td></td>
    @endforeach
  </tr>
  <tr>
    @foreach($blocks as $b)
      <td colspan="2"></td>
      <td><strong>Total A/R:</strong></td>
      <td><strong>₱{{ number_format($b['totalAR'],2) }}</strong></td>
      <td></td>
    @endforeach
  </tr>
  <tr>
    @foreach($blocks as $b)
      <td colspan="2"></td>
      <td><strong>Total Expenses:</strong></td>
      <td><strong>₱{{ number_format($b['totalExp'],2) }}</strong></td>
      <td></td>
    @endforeach
  </tr>
  <tr>
    @foreach($blocks as $b)
      <td colspan="2"></td>
      <td><strong>Total Deposits:</strong></td>
      <td><strong>₱{{ number_format($b['totalDep'],2) }}</strong></td>
      <td></td>
    @endforeach
  </tr>
  <tr>
    @foreach($blocks as $b)
      <td colspan="2"></td>
      <td><strong>Gross Total:</strong></td>
      <td><strong>₱{{ number_format($b['gross'],2) }}</strong></td>
      <td></td>
    @endforeach
  </tr>

  {{-- GRAND GROSS TOTAL --}}
  <tr><td colspan="{{ count($blocks)*5 }}"></td></tr>
  <tr>
    <td colspan="{{ count($blocks)*5 - 1 }}"
        style="text-align:right;font-weight:bold;background:#bbb;color:#fff;">
      Grand Gross Total:
    </td>
    <td style="font-weight:bold;background:#bbb;color:#fff;">
      ₱{{ number_format($grandSales + $grandAR - $grandExpenses + $grandDeposits,2) }}
    </td>
  </tr>
</table>
<table>
  @php
    use Carbon\Carbon;

    $byDate     = collect($allItems)->groupBy('date');
    $blocks     = [];
    $grandGross = 0;

    foreach ($byDate->keys()->sort()->values() as $date) {
      $items    = $byDate[$date];
      $invoices = $items->whereNotNull('invoice_no')->groupBy('invoice_no');
      $orphans  = $items->whereNull('invoice_no');

      $daySales = $invoices->flatten(1)
                    ->where(fn($r)=> strtolower($r['type'])==='sales')
                    ->sum(fn($r)=> floatval($r['amount'] ?? 0));
      $dayAR = $invoices->flatten(1)
                    ->where(fn($r)=> in_array(strtolower($r['type']), ['a/r','ar collections']))
                    ->sum(fn($r)=> floatval($r['amount'] ?? 0))
             + $orphans->where(fn($r)=> in_array(strtolower($r['type']), ['a/r','ar collections']))
                    ->sum(fn($r)=> floatval($r['amount'] ?? 0));
      $dayExp = $invoices->flatten(1)
                    ->where(fn($r)=> in_array(strtolower($r['type']), ['expense','expenses']))
                    ->sum(fn($r)=> floatval($r['amount'] ?? 0))
             + $orphans->where(fn($r)=> in_array(strtolower($r['type']), ['expense','expenses']))
                    ->sum(fn($r)=> floatval($r['amount'] ?? 0));
      $dayDep = $invoices->flatten(1)
                    ->where(fn($r)=> in_array(strtolower($r['type']), ['deposit','cash deposits']))
                    ->sum(fn($r)=> floatval($r['amount'] ?? 0))
             + $orphans->where(fn($r)=> in_array(strtolower($r['type']), ['deposit','cash deposits']))
                    ->sum(fn($r)=> floatval($r['amount'] ?? 0));
      $dayDisc  = $invoices
                    ->map(fn($g)=> floatval($g->first()['discount'] ?? 0))
                    ->sum();

      $dayGross    = $daySales + $dayAR - $dayExp - $dayDep - $dayDisc;
      $grandGross += $dayGross;

      $blocks[] = [
        'date'     => $date,
        'invoices' => $invoices,
        'orphans'  => $orphans,
        'totals'   => compact('daySales','dayAR','dayExp','dayDep','dayDisc','dayGross'),
      ];
    }

    $rowsPerBlock = [];
    $typeMap = [
      'sales'       => 'Labor',
      'a/r'         => 'A/R Collections',
      'ar collections' => 'A/R Collections',
      'expense'     => 'Expenses',
      'expenses'    => 'Expenses',
      'deposit'     => 'Cash Deposits',
      'cash deposits' => 'Cash Deposits',
    ];

    foreach ($blocks as $b) {
      $rows     = [];
      $prevType = null;

      // — invoice groups —
      foreach ($b['invoices'] as $invNo => $lines) {
        $hdr         = $lines->first();
        $cust        = $hdr['customer'] ?? '';
        $veh         = trim("{$hdr['vehicle_manufacturer']} {$hdr['vehicle_model']} {$hdr['vehicle_year']} ({$hdr['vehicle_plate']})");
        $descHd      = "{$invNo} — {$cust}" . ($veh ? " – {$veh}" : '');
        $discount    = floatval($hdr['discount'] ?? 0);
        $clientTotal = floatval($hdr['payment'] ?? 0) - $discount;
        $ptype       = in_array(strtolower($hdr['payment_type'] ?? ''), ['debit','credit'])
                         ? 'Non-Cash'
                         : ucfirst($hdr['payment_type'] ?? 'Cash');

        // invoice header row
        $rows[] = [
          "<td colspan=\"6\" style=\"background:#f0f0f0;font-weight:bold;\">{$descHd}</td>"
        ];

        // line items
        foreach ($lines as $row) {
          $raw   = strtolower($row['type'] ?? '');
          $label = $typeMap[$raw] ?? '';

          if (!$label || $label === $prevType) {
            $typeCell = '<td></td>';
          } else {
            $typeCell  = '<td>'.$label.'</td>';
            $prevType = $label;
          }

          $rows[] = [
            '<td></td>',
            '<td>'.($row['service'] ?? $row['description'] ?? '').'</td>',
            '<td class="text-center">'.($row['quantity']   ?? '').'</td>',
            '<td>₱'.number_format(floatval($row['amount'] ?? 0),2).'</td>',
            $typeCell,
            '<td>'.($row['remarks'] ?? '').'</td>',
          ];
        }

        // --- TWO BLANK ROWS BEFORE FOOTER (discount, client total, payment type) ---
        $rows[] = ['<td colspan="6"></td>'];
        $rows[] = ['<td colspan="6"></td>'];

        // invoice footer
        $rows[] = [
          '<td colspan="3"></td>',
          '<td><strong>Discount:</strong></td>',
          '<td colspan="2">₱'.number_format($discount,2).'</td>',
        ];
        $rows[] = [
          '<td colspan="3"></td>',
          '<td><strong>Client Total:</strong></td>',
          '<td colspan="2">₱'.number_format($clientTotal,2).'</td>',
        ];
        $rows[] = [
          '<td colspan="3"></td>',
          '<td><strong>Payment Type:</strong></td>',
          '<td colspan="2">'.$ptype.'</td>',
        ];
      }

      // — TWO BLANK ROWS BEFORE ORPHAN GROUP (Expenses, AR, Deposits) —
      if (count($b['orphans']) > 0) {
        $rows[] = ['<td colspan="6"></td>'];
        $rows[] = ['<td colspan="6"></td>'];
      }

      foreach ($b['orphans'] as $row) {
        $raw   = strtolower($row['type'] ?? '');
        $label = $typeMap[$raw] ?? '';

        if (!$label || $label === $prevType) {
          $typeCell = '<td></td>';
        } else {
          $typeCell  = '<td>'.$label.'</td>';
          $prevType = $label;
        }

        $rows[] = [
          '<td class="fw-bold">'.($row['customer'] ?? '').'</td>',
          '<td>'.($row['description'] ?? '').'</td>',
          '<td class="text-center">'.($row['quantity'] ?? '').'</td>',
          '<td>₱'.number_format(floatval($row['amount'] ?? 0),2).'</td>',
          $typeCell,
          '<td>'.($row['remarks'] ?? '').'</td>',
        ];
      }

      // — two blank rows before daily totals —
      $rows[] = ['<td colspan="6"></td>'];
      $rows[] = ['<td colspan="6"></td>'];

      // — daily breakdown totals —
      $tot = $b['totals'];
      foreach ([
        ['Total Sales','daySales'],
        ['Total A/R','dayAR'],
        ['Total Expenses','dayExp'],
        ['Total Deposits','dayDep'],
        ['Total Discounts','dayDisc'],
        ['Gross Total','dayGross'],
      ] as [$label,$key]) {
        $rows[] = [
          '<td colspan="3" class="text-end"><strong>'.$label.':</strong></td>',
          '<td>₱'.number_format($tot[$key],2).'</td>',
          '<td colspan="2"></td>',
        ];
      }

      $rowsPerBlock[$b['date']] = $rows;
    }

    $maxRows    = collect($rowsPerBlock)->map(fn($r)=>count($r))->max();
    $numBlocks  = count($blocks);
    $totalCols  = $numBlocks * 6 + ($numBlocks - 1);
  @endphp

  <thead>
    <tr>
      @foreach($blocks as $b)
        <th colspan="6" style="background:#ffe066;font-size:16px;">
          {{ Carbon::parse($b['date'])->format('F d, Y') }}
        </th>
        @if (!$loop->last)
          <th></th>
        @endif
      @endforeach
    </tr>
    <tr>
      @foreach($blocks as $b)
        <th style="background:#ffe066">Invoice / Customer / Vehicle</th>
        <th style="background:#ffe066">Description</th>
        <th style="background:#ffe066">Qty</th>
        <th style="background:#ffe066">Amount</th>
        <th style="background:#ffe066">Type</th>
        <th style="background:#ffe066">Remarks</th>
        @if (!$loop->last)
          <th></th>
        @endif
      @endforeach
    </tr>
  </thead>

  <tbody>
    @for ($i = 0; $i < $maxRows; $i++)
      <tr>
        @foreach ($blocks as $idx => $b)
          @if ($idx > 0)
            <td></td>
          @endif

          @if (isset($rowsPerBlock[$b['date']][$i]))
            {!! implode('', $rowsPerBlock[$b['date']][$i]) !!}
          @else
            <td colspan="6"></td>
          @endif
        @endforeach
      </tr>
    @endfor

    <tr><td colspan="{{ $totalCols }}"></td></tr>
    <tr>
      <td colspan="{{ $totalCols - 1 }}" style="text-align:right;font-weight:bold;background:#bbb;color:#fff;">
        Grand Gross Total:
      </td>
      <td style="font-weight:bold;background:#bbb;color:#fff;">
        ₱{{ number_format($grandGross, 2) }}
      </td>
    </tr>
  </tbody>
</table>

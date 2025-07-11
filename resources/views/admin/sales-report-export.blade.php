@php
    use Carbon\Carbon;

    // 1) group all items by date
    $byDate = collect($allItems)->groupBy('date');

    // 2) prepare per-day, per-invoice blocks + grand totals
    $blocks        = [];
    $grandSales    = 0;
    $grandCost     = 0;
    $grandDiscount = 0;
    $grandProfit   = 0;

    foreach ($byDate->keys()->sort()->values() as $date) {
        $invoiceGroups = $byDate[$date]->groupBy('invoice_id');
        $rows        = [];
        $daySales    = 0;
        $dayCost     = 0;
        $dayDiscount = 0;
        $dayProfit   = 0;

        foreach ($invoiceGroups as $invId => $items) {
            $first       = $items->first();
            $invoiceNo   = $first['invoice_no'];
            $customer    = $first['customer_name'];
            $vehicleInfo = trim("{$first['vehicle_manufacturer']} {$first['vehicle_model']} ({$first['vehicle_plate']}) {$first['vehicle_year']}");

            // –– header
            $rows[] = [
                'type'        => 'header',
                'customer'    => "Invoice #{$invoiceNo} – {$customer} – {$vehicleInfo}",
                'quantity'    => '',
                'description' => '',
                'price'       => '',
                'remarks'     => $first['remarks'] ?? '',
            ];

            // –– line items
            $subTotal = 0;
            $subCost  = 0;
            foreach ($items as $item) {
                $rows[] = [
                    'type'        => 'item',
                    'customer'    => '',
                    'quantity'    => $item['quantity']   ?? '',
                    'description' => $item['item_name']  ?? '',
                    'price'       => $item['line_total'] ?? 0,
                    'remarks'     => '',
                ];
                $subTotal += $item['line_total'] ?? 0;
                $subCost  += ($item['acquisition_price'] ?? 0) * ($item['quantity'] ?? 1);
            }

            // –– discount & payment type
            $invModel    = $invoices->firstWhere('id', $invId);
            $discount    = $invModel->total_discount;
            $paymentType = in_array($invModel->payment_type, ['debit','credit','non_cash'])
                           ? 'Non-Cash' : 'Cash';

            $rows[] = [
                'type'        => 'subtotal',
                'customer'    => '',
                'quantity'    => '',
                'description' => 'Discount',
                'price'       => $discount,
                'remarks'     => '',
            ];
            $rows[] = [
                'type'        => 'subtotal',
                'customer'    => '',
                'quantity'    => '',
                'description' => 'Client Total',
                'price'       => $subTotal,
                'remarks'     => '',
            ];
            $rows[] = [
                'type'        => 'subtotal',
                'customer'    => '',
                'quantity'    => '',
                'description' => 'Payment Type',
                'price'       => '',
                'remarks'     => $paymentType,
            ];

            // –– separator
            $rows[] = [
                'type'        => 'separator',
                'customer'    => '',
                'quantity'    => '',
                'description' => '',
                'price'       => '',
                'remarks'     => '',
            ];

            // accumulate day totals
            $daySales    += $subTotal;
            $dayCost     += $subCost;
            $dayDiscount += $discount;
            $dayProfit   += $subTotal - $subCost - $discount;
        }

        $blocks[] = [
            'date'     => $date,
            'rows'     => $rows,
            'sales'    => $daySales,
            'cost'     => $dayCost,
            'discount' => $dayDiscount,
            'profit'   => $dayProfit,
        ];

        $grandSales    += $daySales;
        $grandCost     += $dayCost;
        $grandDiscount += $dayDiscount;
        $grandProfit   += $dayProfit;
    }

    $maxRows = max(array_map(fn($b) => count($b['rows']), $blocks));

    // pick the latest date for your GROSS TOTAL header
    $lastDate = end($blocks)['date'];
@endphp

<table>
  {{-- DATE HEADERS --}}
  <tr>
    @foreach($blocks as $block)
      <th colspan="5" style="background:#ffe066;font-size:16px;">
        {{ Carbon::parse($block['date'])->format('F d, Y') }}
      </th>
    @endforeach
  </tr>

  {{-- COLUMN HEADERS --}}
  <tr>
    @foreach($blocks as $block)
      <th style="background:#ffe066">Customer / Vehicle</th>
      <th style="background:#ffe066">Qty</th>
      <th style="background:#ffe066">Description</th>
      <th style="background:#ffe066">Price</th>
      <th style="background:#ffe066">Remarks</th>
    @endforeach
  </tr>

  {{-- DATA ROWS --}}
  @for($i = 0; $i < $maxRows; $i++)
    <tr>
      @foreach($blocks as $block)
        @php $r = $block['rows'][$i] ?? null; @endphp
        @if($r)
          <td @if($r['type']==='header') style="font-weight:bold;background:#f0f0f0;" @endif>
            {{ $r['customer'] }}
          </td>
          <td class="text-center">{{ $r['quantity'] }}</td>
          <td @if($r['type']==='subtotal') style="font-weight:bold;background:#fff6c1;" @endif>
            {{ $r['description'] }}
          </td>
          <td @if($r['type']==='subtotal') style="font-weight:bold;background:#fff6c1;" @endif>
            @if(is_numeric($r['price'])) ₱{{ number_format($r['price'],2) }} @endif
          </td>
          <td>{{ $r['remarks'] }}</td>
        @else
          <td colspan="5"></td>
        @endif
      @endforeach
    </tr>
  @endfor

  {{-- PER-BLOCK FOOTERS --}}
  <tr>
    @foreach($blocks as $block)
      <td colspan="2"></td>
      <td class="text-end fw-bold">Discount:</td>
      <td>₱{{ number_format($block['discount'],2) }}</td>
      <td></td>
    @endforeach
  </tr>
  <tr>
    @foreach($blocks as $block)
      <td colspan="2"></td>
      <td class="text-end fw-bold">Total Sales:</td>
      <td>₱{{ number_format($block['sales'],2) }}</td>
      <td></td>
    @endforeach
  </tr>
  <tr>
    @foreach($blocks as $block)
      <td colspan="2"></td>
      <td class="text-end fw-bold">Total Cost:</td>
      <td>₱{{ number_format($block['cost'],2) }}</td>
      <td></td>
    @endforeach
  </tr>
  <tr>
    @foreach($blocks as $block)
      <td colspan="2"></td>
      <td class="text-end fw-bold">Total Profit:</td>
      <td>₱{{ number_format($block['profit'],2) }}</td>
      <td></td>
    @endforeach
  </tr>

  {{-- GROSS TOTAL under the last date block --}}
@php
    $grossCash    = $invoices
        ->filter(fn($inv)=> $inv->payment_type==='cash')
        ->sum(fn($inv)=> $inv->items->sum('line_total') + $inv->jobs->sum('total'));
    $grossNonCash = $grandSales - $grossCash;
@endphp
<tr>
  {{-- blank out all but final 5 cols --}}
  <td colspan="{{ (count($blocks)-1) * 5 }}"></td>

  {{-- the single yellow GROSS TOTAL cell --}}
  <td colspan="5" style="background:#ffe066; padding:8px; vertical-align:top;">
    <strong>
      GROSS TOTAL
      ({{ \Carbon\Carbon::parse($lastDate)->format('Y-m-d') }}
       to
       {{ \Carbon\Carbon::parse($lastDate)->format('Y-m-d') }})
    </strong><br>

    Total Sales: ₱{{ number_format($grandSales,2) }}<br>
    Total Cost: ₱{{ number_format($grandCost,2) }}<br>
    Total Discount: ₱{{ number_format($grandDiscount,2) }}<br>
    Total Payment in Cash: ₱{{ number_format($grossCash,2) }}<br>
    Total Payment in Non-Cash: ₱{{ number_format($grossNonCash,2) }}<br>
    Total Profit: ₱{{ number_format($grandProfit,2) }}
  </td>
</tr>

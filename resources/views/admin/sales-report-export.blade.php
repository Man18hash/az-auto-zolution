@php
use Carbon\Carbon;

// Group items by date for per-day columns
$byDate = collect($allItems)->groupBy('date');
$blocks = [];
$grand = [
    'sales'    => 0,
    'cost'     => 0,
    'cash'     => 0,
    'nonCash'  => 0,
    'discount' => 0,
    'profit'   => 0,
];

foreach ($byDate->keys()->sort()->values() as $date) {
    $items = $byDate[$date];
    $invoiceGroups = collect($items)->groupBy('invoice_id');

    $daySales    = $items->sum('line_total');
    $dayCost     = $items->sum(fn($i) => ($i['acquisition_price'] ?? 0) * ($i['quantity'] ?? 1));
    $dayInvs     = $invoices->where('created_at', '>=', $date . ' 00:00:00')
                            ->where('created_at', '<=', $date . ' 23:59:59');
    $dayInvDisc  = $dayInvs->sum('total_discount');
    $dayItemDisc = $items->sum('discount_value');
    $dayDiscount = $dayInvDisc + $dayItemDisc;
    $dayCash     = $dayInvs->where('payment_type','cash')
                    ->sum(fn($inv) => $inv->items->sum('line_total') + $inv->jobs->sum('total'));
    $dayNonCash  = $daySales - $dayCash;
    $dayProfit   = $daySales - $dayCost - $dayDiscount;

    // Update grand totals
    $grand['sales']    += $daySales;
    $grand['cost']     += $dayCost;
    $grand['cash']     += $dayCash;
    $grand['nonCash']  += $dayNonCash;
    $grand['discount'] += $dayDiscount;
    $grand['profit']   += $dayProfit;

    // Prepare rows per block (per day)
    $rows = [];
    foreach ($invoiceGroups as $invId => $rowsGroup) {
        $first = $rowsGroup->first();
        $desc = "Invoice #{$first['invoice_no']} — {$first['customer_name']} — ";
        $veh  = trim("{$first['vehicle_model']} {$first['vehicle_plate']} {$first['vehicle_year']}");
        $desc .= $veh ? $veh : '-';

        // Header (no repeat)
        $rows[] = [
            "<td colspan=\"8\" style=\"background:#f0f0f0;font-weight:bold;\">{$desc}</td>"
        ];

        // Items/Jobs
        foreach ($rowsGroup as $row) {
            $rows[] = [
                '<td></td>',
                '<td>' . ($row['item_name'] ?? '-') . '</td>',
                '<td class="text-center">' . ($row['quantity'] ?? '') . '</td>',
                '<td class="text-end">₱' . number_format($row['acquisition_price'] ?? 0, 2) . '</td>',
                '<td class="text-end">₱' . number_format($row['selling_price'] ?? 0, 2) . '</td>',
                '<td class="text-end">₱' . number_format($row['discount_value'] ?? 0, 2) . '</td>',
                '<td class="text-end">₱' . number_format($row['line_total'] ?? 0, 2) . '</td>',
                '<td>' . ($row['remarks'] ?? '') . '</td>',
            ];
        }

        // Invoice-level discount
        $invObj = $invoices->where('id', $invId)->first();
        $invDisc = $invObj ? ($invObj->total_discount ?? 0) : 0;
        $clientTotal = $rowsGroup->sum('line_total') - $invDisc;
        $payType = $invObj
            ? ($invObj->payment_type === 'cash' ? 'Cash' : 'Non-Cash')
            : '';

        // Invoice summary rows
        $rows[] = [
            '<td></td>',
            '<td colspan="5" class="text-end fw-bold">Discount (Invoice):</td>',
            '<td class="text-end">₱' . number_format($invDisc, 2) . '</td>',
            '<td></td>',
        ];
        $rows[] = [
            '<td></td>',
            '<td colspan="5" class="text-end fw-bold">Client Total:</td>',
            '<td class="text-primary text-end">₱' . number_format($clientTotal, 2) . '</td>',
            '<td></td>',
        ];
        $rows[] = [
            '<td></td>',
            '<td colspan="5" class="text-end fw-bold">Payment Type:</td>',
            '<td class="fw-semibold text-end">' . $payType . '</td>',
            '<td></td>',
        ];

        // Blank row after each invoice
        $rows[] = ['<td colspan="8"></td>'];
    }

    // Two blank rows before daily breakdown
    $rows[] = ['<td colspan="8"></td>'];
    $rows[] = ['<td colspan="8"></td>'];

    // Daily breakdown
    $rows[] = [
        '<td colspan="5" class="text-end fw-bold">Total Sales:</td>',
        '<td></td>',
        '<td class="text-primary text-end">₱' . number_format($daySales, 2) . '</td>',
        '<td></td>',
    ];
    $rows[] = [
        '<td colspan="5" class="text-end fw-bold">Total Cost:</td>',
        '<td></td>',
        '<td class="text-end">₱' . number_format($dayCost, 2) . '</td>',
        '<td></td>',
    ];
    $rows[] = [
        '<td colspan="5" class="text-end fw-bold">Cash Sales:</td>',
        '<td></td>',
        '<td class="text-end">₱' . number_format($dayCash, 2) . '</td>',
        '<td></td>',
    ];
    $rows[] = [
        '<td colspan="5" class="text-end fw-bold">Non-Cash Sales:</td>',
        '<td></td>',
        '<td class="text-end">₱' . number_format($dayNonCash, 2) . '</td>',
        '<td></td>',
    ];
    $rows[] = [
        '<td colspan="5" class="text-end fw-bold">Discount:</td>',
        '<td></td>',
        '<td class="text-end">₱' . number_format($dayDiscount, 2) . '</td>',
        '<td></td>',
    ];
    $rows[] = [
        '<td colspan="5" class="text-end fw-bold">Total Profit:</td>',
        '<td></td>',
        '<td class="text-success text-end">₱' . number_format($dayProfit, 2) . '</td>',
        '<td></td>',
    ];

    $blocks[] = [
        'date' => $date,
        'rows' => $rows,
    ];
}

// Compute for max rows
$maxRows = collect($blocks)->map(fn($b) => count($b['rows']))->max();
$numBlocks = count($blocks);
$totalCols = $numBlocks * 8 + ($numBlocks - 1);
@endphp

<table>
    <thead>
        <tr>
            @foreach($blocks as $b)
                <th colspan="8" style="background:#ffe066;font-size:16px;">
                    {{ Carbon::parse($b['date'])->format('F d, Y') }}
                </th>
                @if(!$loop->last)
                    <th></th>
                @endif
            @endforeach
        </tr>
        <tr>
            @foreach($blocks as $b)
                <th style="background:#ffe066">Invoice / Customer / Vehicle</th>
                <th style="background:#ffe066">Item</th>
                <th style="background:#ffe066">Qty</th>
                <th style="background:#ffe066">Acq. Price</th>
                <th style="background:#ffe066">Sell Price</th>
                <th style="background:#ffe066">Discount</th>
                <th style="background:#ffe066">Line Total</th>
                <th style="background:#ffe066">Remarks</th>
                @if(!$loop->last)
                    <th></th>
                @endif
            @endforeach
        </tr>
    </thead>
    <tbody>
        @for($i = 0; $i < $maxRows; $i++)
            <tr>
                @foreach($blocks as $idx => $b)
                    @if($idx > 0)
                        <td></td>
                    @endif
                    @if(isset($b['rows'][$i]))
                        {!! implode('', $b['rows'][$i]) !!}
                    @else
                        <td colspan="8"></td>
                    @endif
                @endforeach
            </tr>
        @endfor

        {{-- Grand Totals --}}
        <tr><td colspan="{{ $totalCols }}"></td></tr>
        <tr>
            <td colspan="{{ $totalCols-1 }}" style="text-align:right;font-weight:bold;background:#bbb;color:#fff;">
                Grand Total Sales:
            </td>
            <td style="font-weight:bold;background:#bbb;color:#fff;">
                ₱{{ number_format($grand['sales'], 2) }}
            </td>
        </tr>
        <tr>
            <td colspan="{{ $totalCols-1 }}" style="text-align:right;font-weight:bold;background:#bbb;color:#fff;">
                Grand Total Cost:
            </td>
            <td style="font-weight:bold;background:#bbb;color:#fff;">
                ₱{{ number_format($grand['cost'], 2) }}
            </td>
        </tr>
        <tr>
            <td colspan="{{ $totalCols-1 }}" style="text-align:right;font-weight:bold;background:#bbb;color:#fff;">
                Grand Total Cash:
            </td>
            <td style="font-weight:bold;background:#bbb;color:#fff;">
                ₱{{ number_format($grand['cash'], 2) }}
            </td>
        </tr>
        <tr>
            <td colspan="{{ $totalCols-1 }}" style="text-align:right;font-weight:bold;background:#bbb;color:#fff;">
                Grand Total Non-Cash:
            </td>
            <td style="font-weight:bold;background:#bbb;color:#fff;">
                ₱{{ number_format($grand['nonCash'], 2) }}
            </td>
        </tr>
        <tr>
            <td colspan="{{ $totalCols-1 }}" style="text-align:right;font-weight:bold;background:#bbb;color:#fff;">
                Grand Total Discount:
            </td>
            <td style="font-weight:bold;background:#bbb;color:#fff;">
                ₱{{ number_format($grand['discount'], 2) }}
            </td>
        </tr>
        <tr>
            <td colspan="{{ $totalCols-1 }}" style="text-align:right;font-weight:bold;background:#bbb;color:#fff;">
                Grand Total Profit:
            </td>
            <td style="font-weight:bold;background:#bbb;color:#fff;">
                ₱{{ number_format($grand['profit'], 2) }}
            </td>
        </tr>
    </tbody>
</table>

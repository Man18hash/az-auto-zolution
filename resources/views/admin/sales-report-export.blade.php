@php
    $byDate = collect($allItems)->groupBy('date');
    $blocks = [];
    $grandSales = 0;
    $grandCost = 0;
    $grandProfit = 0;

    foreach ($byDate->keys()->sort()->values() as $date) {
        $group = $byDate[$date]->groupBy('customer_name');
        $rows = [];
        $daySales = $dayCost = $dayProfit = 0;

        foreach ($group as $customer => $items) {
            $first = true;
            $subTotal = $subCost = 0;

            foreach ($items as $item) {
                $rows[] = [
                    'customer' => $first ? $customer . ' – ' . ($item['vehicle_manufacturer'] ?? '') . ' ' . ($item['vehicle_model'] ?? '') . ' (' . ($item['vehicle_plate'] ?? '-') . ') ' . ($item['vehicle_year'] ?? '') : '',
                    'quantity' => $item['quantity'] ?? '',
                    'description' => $item['item_name'] ?? '',
                    'price' => $item['line_total'] ?? 0,
                    'remarks' => $item['remarks'] ?? '',
                ];

                $subTotal += $item['line_total'] ?? 0;
                $subCost += ($item['acquisition_price'] ?? 0) * ($item['quantity'] ?? 1);
                $first = false;
            }

            // Subtotal row for this customer
            $rows[] = [
                'customer' => '',
                'quantity' => '',
                'description' => 'TOTAL',
                'price' => $subTotal,
                'remarks' => '',
                'is_total_row' => true
            ];

            $daySales += $subTotal;
            $dayCost += $subCost;
            $dayProfit += ($subTotal - $subCost);
        }

        $blocks[] = [
            'date' => $date,
            'rows' => $rows,
            'sales' => $daySales,
            'cost' => $dayCost,
            'profit' => $dayProfit,
        ];

        $grandSales += $daySales;
        $grandCost += $dayCost;
        $grandProfit += $dayProfit;
    }

    $maxRows = max(array_map(fn($b) => count($b['rows']), $blocks)) + 3;
@endphp

<table>
    {{-- DATE HEADERS --}}
    <tr>
        @foreach($blocks as $block)
            <th colspan="5" style="background:#ffe066;font-size:16px;">
                {{ \Carbon\Carbon::parse($block['date'])->format('F d, Y') }}
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
                    <td>{{ $r['customer'] }}</td>
                    <td class="text-center">{{ $r['quantity'] }}</td>
                    <td @if(!empty($r['is_total_row'])) style="font-weight:bold;background:#fff6c1;" @endif>
                        {{ $r['description'] }}
                    </td>
                    <td @if(!empty($r['is_total_row'])) style="font-weight:bold;background:#fff6c1;" @endif>
                        ₱{{ number_format($r['price'], 2) }}
                    </td>
                    <td>{{ $r['remarks'] }}</td>
                @else
                    <td colspan="5"></td>
                @endif
            @endforeach
        </tr>
    @endfor

    {{-- PER-BLOCK TOTALS --}}
    <tr>
        @foreach($blocks as $block)
            <td colspan="2"></td>
            <td><strong>Total Sales:</strong></td>
            <td><strong>₱{{ number_format($block['sales'], 2) }}</strong></td>
            <td></td>
        @endforeach
    </tr>
    <tr>
        @foreach($blocks as $block)
            <td colspan="2"></td>
            <td><strong>Total Cost:</strong></td>
            <td><strong>₱{{ number_format($block['cost'], 2) }}</strong></td>
            <td></td>
        @endforeach
    </tr>
    <tr>
        @foreach($blocks as $block)
            <td colspan="2"></td>
            <td><strong>Total Profit:</strong></td>
            <td><strong>₱{{ number_format($block['profit'], 2) }}</strong></td>
            <td></td>
        @endforeach
    </tr>

    {{-- GRAND TOTALS --}}
    <tr><td colspan="{{ count($blocks) * 5 }}"></td></tr>
    <tr>
        <td colspan="{{ count($blocks) * 5 - 2 }}" style="text-align:right;font-weight:bold;background:#bbb;color:#fff;">
            Grand Total Sales:
        </td>
        <td colspan="2" style="font-weight:bold;background:#bbb;color:#fff;">
            ₱{{ number_format($grandSales, 2) }}
        </td>
    </tr>
    <tr>
        <td colspan="{{ count($blocks) * 5 - 2 }}" style="text-align:right;font-weight:bold;background:#bbb;color:#fff;">
            Grand Total Cost:
        </td>
        <td colspan="2" style="font-weight:bold;background:#bbb;color:#fff;">
            ₱{{ number_format($grandCost, 2) }}
        </td>
    </tr>
    <tr>
        <td colspan="{{ count($blocks) * 5 - 2 }}" style="text-align:right;font-weight:bold;background:#bbb;color:#fff;">
            Grand Total Profit:
        </td>
        <td colspan="2" style="font-weight:bold;background:#bbb;color:#fff;">
            ₱{{ number_format($grandProfit, 2) }}
        </td>
    </tr>
</table>

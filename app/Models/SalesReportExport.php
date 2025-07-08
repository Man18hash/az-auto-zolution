<?php
namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class SalesReportExport implements FromView
{
    public $allItems, $totalSales, $totalCost, $totalProfit, $startDate, $endDate;

    public function __construct($allItems, $totalSales, $totalCost, $totalProfit, $startDate, $endDate)
    {
        $this->allItems = $allItems;
        $this->totalSales = $totalSales;
        $this->totalCost = $totalCost;
        $this->totalProfit = $totalProfit;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function view(): View
    {
        return view('admin.sales-report-export', [
            'allItems' => $this->allItems,
            'totalSales' => $this->totalSales,
            'totalCost' => $this->totalCost,
            'totalProfit' => $this->totalProfit,
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
        ]);
    }
}

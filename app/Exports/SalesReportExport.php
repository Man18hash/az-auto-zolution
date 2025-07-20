<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class SalesReportExport implements FromView
{
    public $allItems, $startDate, $endDate, $invoices;

    public function __construct($allItems, $startDate, $endDate, $invoices)
    {
        $this->allItems   = $allItems;
        $this->startDate  = $startDate;
        $this->endDate    = $endDate;
        $this->invoices   = $invoices;
    }

    public function view(): View
    {
        return view('admin.sales-report-export', [
            'allItems'  => $this->allItems,
            'startDate' => $this->startDate,
            'endDate'   => $this->endDate,
            'invoices'  => $this->invoices,
        ]);
    }
}


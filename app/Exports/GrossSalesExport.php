<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class GrossSalesExport implements FromView
{
    protected $allItems;

    public function __construct($allItems)
    {
        $this->allItems = $allItems;
    }

    public function view(): View
{
    return view('admin.gross-sales-export', [
        'allItems' => $this->allItems
    ]);
}
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    protected $fillable = [
        'item_name',
        'quantity',
        'part_number',
        'acquisition_price',
        'supplier',
        'selling',
    ];

    /**
     * Deduct quantity from inventory.
     * If not enough stock, deducts to zero.
     */
    public function deductQuantity($amount)
    {
        $amount = max(0, (int) $amount); // avoid negative deduction
        $this->quantity = max(0, $this->quantity - $amount);
        $this->save();
        return true;
    }
}

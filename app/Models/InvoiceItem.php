<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model
{
    protected $fillable = [
        'invoice_id',
        'part_id', // nullable FK to inventories
        'manual_part_name',
        'manual_serial_number',
        'manual_acquisition_price',
        'manual_selling_price',
        'quantity',
        'original_price',
        'discounted_price',
        'discount_value',
        'line_total',
    ];

    protected $casts = [
        'quantity'                  => 'integer',
        'original_price'            => 'decimal:2',
        'discounted_price'          => 'decimal:2',
        'discount_value'            => 'decimal:2',
        'line_total'                => 'decimal:2',
        'manual_acquisition_price'  => 'decimal:2',
        'manual_selling_price'      => 'decimal:2',
    ];

    /**
     * Relationships
     */
    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function part()
    {
        return $this->belongsTo(Inventory::class, 'part_id');
    }

    /**
     * Accessors
     */
    
    // Part name for views (either inventory or manual)
    public function getDisplayPartNameAttribute(): string
    {
        return $this->part_id
            ? ($this->part->item_name ?? 'Unknown Inventory')
            : ($this->manual_part_name ?? 'Manual Part');
    }

    // Check if this item is manually entered
    public function getIsManualAttribute(): bool
    {
        return $this->part_id === null;
    }

    // Get the selling price per unit (manual or inventory)
    public function getEffectiveSellingPriceAttribute(): float
    {
        if ($this->part_id) {
            return $this->discounted_price ?? $this->original_price;
        }

        return $this->manual_selling_price ?? 0;
    }

    // Get the acquisition cost per unit (manual or from inventory)
    public function getEffectiveAcquisitionPriceAttribute(): float
    {
        if ($this->part_id) {
            return $this->part->acquisition_price ?? 0;
        }

        return $this->manual_acquisition_price ?? 0;
    }

    // Gross profit for this item (selling - acquisition) * quantity
    public function getGrossProfitAttribute(): float
    {
        return ($this->effective_selling_price - $this->effective_acquisition_price) * $this->quantity;
    }
}

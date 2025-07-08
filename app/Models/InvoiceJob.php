<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceJob extends Model
{
    protected $fillable = [
        'invoice_id',
        'job_description',
        'technician_id',
        'total',
    ];

    protected $casts = [
        'total' => 'decimal:2',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function technician()
    {
        return $this->belongsTo(Technician::class);
    }

    // Convenience accessor
    public function getTechnicianNameAttribute(): ?string
    {
        return $this->technician?->name;
    }
}

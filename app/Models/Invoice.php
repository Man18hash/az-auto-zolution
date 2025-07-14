<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

class Invoice extends Model
{
    protected $fillable = [
        'client_id',
        'vehicle_id',
        'customer_name',
        'vehicle_name',
        'source_type',
        'service_status',
        'status',
        'appointment_date',
        'note', 
        'subtotal',
        'total_discount',
        'vat_amount',
        'grand_total',
        'payment_type',
        'invoice_no',
        'number',
        'address',
    ];

    protected $attributes = [
        'source_type'    => 'quotation',
        'service_status' => 'pending',
        'status'         => 'unpaid',
    ];

    protected $casts = [
        'appointment_date' => 'datetime',
        'subtotal'         => 'decimal:2',
        'total_discount'   => 'decimal:2',
        'vat_amount'       => 'decimal:2',
        'grand_total'      => 'decimal:2',
        'number'       => 'string',
        'address'      => 'string',

    ];

    // ✅ Add 'cancelled' here
    public static $sourceTypes     = ['quotation', 'cancelled', 'appointment', 'service_order', 'invoicing'];
    public static $serviceStatuses = ['pending', 'in_progress', 'done'];
    public static $statuses        = ['unpaid', 'paid', 'cancelled', 'voided'];

    /**
     * Relations
     */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function jobs()
    {
        return $this->hasMany(InvoiceJob::class);
    }

    /**
     * Display fallbacks (use in views as $invoice->customer_display / $invoice->vehicle_display)
     */
    public function getCustomerDisplayAttribute(): string
    {
        return $this->client
            ? $this->client->name
            : ($this->customer_name ?? '');
    }

    public function getVehicleDisplayAttribute(): string
    {
        return $this->vehicle
            ? $this->vehicle->plate_number
            : ($this->vehicle_name ?? '');
    }

    /**
     * Enum validation mutators
     */
    public function setSourceTypeAttribute(string $value): void
    {
        if (! in_array($value, static::$sourceTypes, true)) {
            throw new InvalidArgumentException("Invalid source_type: {$value}");
        }
        $this->attributes['source_type'] = $value;
    }

    public function setServiceStatusAttribute(string $value): void
    {
        if (! in_array($value, static::$serviceStatuses, true)) {
            throw new InvalidArgumentException("Invalid service_status: {$value}");
        }
        $this->attributes['service_status'] = $value;
    }

    public function setStatusAttribute(string $value): void
    {
        if (! in_array($value, static::$statuses, true)) {
            throw new InvalidArgumentException("Invalid status: {$value}");
        }
        $this->attributes['status'] = $value;
    }

    /**
     * Convenience helpers
     */
    public function isPending(): bool
    {
        return $this->service_status === 'pending';
    }

    public function isInProgress(): bool
    {
        return $this->service_status === 'in_progress';
    }

    public function isDone(): bool
    {
        return $this->service_status === 'done';
    }
    
}

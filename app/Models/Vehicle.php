<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'plate_number',
        'model',
        'vin_chasis',
        'manufacturer',
        'year',
        'color',
        'odometer',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}

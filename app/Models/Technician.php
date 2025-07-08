<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Technician extends Model
{
    // The table name is inferred ('technicians'), so no need to override $table.
    protected $fillable = [
        'name',
        'position',
    ];
}

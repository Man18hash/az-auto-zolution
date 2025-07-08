<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVehiclesTable extends Migration
{
    public function up()
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete(); // Nullable for walk-ins
            $table->string('plate_number')->nullable(); // Nullable for manual entry/walk-in, NOT unique
            $table->string('model')->nullable(); // Nullable
            $table->string('vin_chasis')->nullable();
            $table->string('manufacturer')->nullable();
            $table->string('year')->nullable();
            $table->string('color')->nullable();
            $table->string('odometer')->nullable(); // Nullable
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('vehicles');
    }
}

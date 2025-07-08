<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoiceJobsTable extends Migration
{
    public function up()
    {
        Schema::create('invoice_jobs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('invoice_id')->constrained('invoices')->cascadeOnDelete();

            $table->text('job_description'); // description of the service/labor

            $table->foreignId('technician_id')->constrained('technicians')->cascadeOnDelete();

            $table->decimal('total', 15, 2)->default(0); // cost for this specific job/service

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('invoice_jobs');
    }
}

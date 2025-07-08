<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoicesTable extends Migration
{
    public function up()
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();

            // Optional: Link to registered client
            $table->foreignId('client_id')->nullable()->constrained()->nullOnDelete();

            // Optional: Link to registered vehicle
            $table->foreignId('vehicle_id')->nullable()->constrained()->nullOnDelete();

            // Fallback for walk-in clients
            $table->string('customer_name')->nullable();
            $table->string('vehicle_name')->nullable();

            // Origin of this invoice
            $table->enum('source_type', ['cancelled','quotation','appointment','service_order','invoicing']);

            // Service progress
            $table->enum('service_status', ['pending','in_progress','done'])->default('pending');

            // Payment status
            $table->enum('status', ['unpaid','paid','cancelled','voided'])->default('unpaid');

            // To link to original quotation/appointment (optional)
            $table->foreignId('converted_from_invoice_id')->nullable()->constrained('invoices')->nullOnDelete();

            // Only used if source_type = 'appointment'
            $table->date('appointment_date')->nullable();

            // Financial fields
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('total_discount', 15, 2)->default(0);
            $table->decimal('vat_amount', 15, 2)->default(0);
            $table->decimal('grand_total', 15, 2)->default(0);

            // Payment method, only known once paid
            $table->enum('payment_type', ['cash','debit','credit','non_cash'])->nullable();

            // Optional notes or customer instructions
            $table->text('remarks')->nullable();

            $table->string('invoice_no')->nullable();
            $table->string('number')->nullable();
            $table->string('address')->nullable();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('invoices');
    }
}

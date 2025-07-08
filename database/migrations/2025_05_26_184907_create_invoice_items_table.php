<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoiceItemsTable extends Migration
{
    public function up()
    {
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();

            // Optional inventory part reference
            $table->foreignId('part_id')->nullable()->constrained('inventories')->nullOnDelete();

            // Manual part info (used if part_id is null)
            $table->string('manual_part_name')->nullable();
            $table->string('manual_serial_number')->nullable();
            $table->decimal('manual_acquisition_price', 15, 2)->nullable();
            $table->decimal('manual_selling_price', 15, 2)->nullable();

            // Common to both manual/inventory parts
            $table->integer('quantity');
            $table->decimal('original_price', 15, 2); // Unit price shown in form
            $table->decimal('discounted_price', 15, 2)->nullable()->default(0); // Final unit price after discount
            $table->decimal('discount_value', 15, 2)->default(0); // Total discount for this line
            $table->decimal('line_total', 15, 2)->default(0);     // Final total for this row (discounted × qty)

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('invoice_items');
    }
}

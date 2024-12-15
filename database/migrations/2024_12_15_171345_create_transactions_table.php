<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_code')->nullable();
            $table->date('purchase_date');
            $table->decimal('price', 15, 2);
            $table->bigInteger('qty');
            $table->decimal('discount_price', 15, 2);
            $table->decimal('profit_price', 15, 2);
            $table->decimal('subtotal', 15, 2);
            $table->decimal('subtotal_after_discount', 15, 2);
            $table->decimal('pay', 15, 2)->default(0.0);
            $table->boolean('is_paid')->default(false);
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};

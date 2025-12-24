<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_code', 50)->unique();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Shipping Information
            $table->string('recipient_name', 100);
            $table->text('address');
            $table->string('city', 100);
            $table->string('province', 100);
            $table->string('postal_code', 10);
            $table->string('phone', 20);
            
            // Payment & Shipping
            $table->decimal('subtotal', 10, 2);
            $table->decimal('shipping_cost', 10, 2);
            $table->decimal('total_amount', 10, 2);
            $table->enum('payment_method', ['transfer', 'cod']);
            $table->string('shipping_method', 50);
            $table->string('resi_code', 50)->nullable();
            
            // Status
            $table->enum('status', ['pending', 'processing', 'shipped', 'completed', 'cancelled'])
                  ->default('pending');
            $table->enum('design_status', ['pending', 'confirmed', 'rejected'])
                  ->default('pending');
            
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index('order_code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
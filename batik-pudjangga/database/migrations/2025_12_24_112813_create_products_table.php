<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->integer('stock')->default(0);
            $table->enum('category', ['men', 'women', 'pants', 'oneset']);
            $table->string('subcategory', 50)->nullable();
            $table->string('image');
            $table->boolean('is_new')->default(false);
            $table->boolean('is_sale')->default(false);
            $table->decimal('sale_price', 10, 2)->nullable();
            $table->date('sale_start')->nullable();
            $table->date('sale_end')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('category');
            $table->index(['is_new', 'is_sale']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipping_zones', function (Blueprint $table) {
            $table->id();
            $table->string('province', 100);
            $table->integer('zone');
            $table->integer('cost_regular');
            $table->integer('cost_express');
            $table->timestamps();

            $table->index('province');
            $table->index('zone');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipping_zones');
    }
};
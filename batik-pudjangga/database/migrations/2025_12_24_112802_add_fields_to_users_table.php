<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone', 20)->nullable()->after('email');
            $table->text('address')->nullable()->after('phone');
            $table->string('city', 100)->nullable()->after('address');
            $table->string('postal_code', 10)->nullable()->after('city');
            $table->string('profile_image')->nullable()->after('postal_code');
            $table->boolean('is_admin')->default(false)->after('profile_image');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['phone', 'address', 'city', 'postal_code', 'profile_image', 'is_admin']);
        });
    }
};
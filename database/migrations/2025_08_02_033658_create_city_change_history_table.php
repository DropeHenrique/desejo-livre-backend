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
        Schema::create('city_change_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('companion_profile_id')->constrained()->onDelete('cascade');
            $table->foreignId('old_city_id')->nullable()->constrained('cities')->onDelete('set null');
            $table->foreignId('new_city_id')->constrained('cities')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('city_change_history');
    }
};

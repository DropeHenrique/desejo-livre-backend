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
        Schema::create('phone_change_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('companion_profile_id')->constrained()->onDelete('cascade');
            $table->string('old_phone', 20)->nullable();
            $table->string('new_phone', 20);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('phone_change_history');
    }
};

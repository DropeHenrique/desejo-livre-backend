<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companion_availabilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('companion_profile_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('day_of_week'); // 0=Domingo .. 6=Sábado
            $table->time('start_time');
            $table->time('end_time');
            $table->boolean('enabled')->default(true);
            $table->timestamps();

            $table->index(['companion_profile_id', 'day_of_week']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('companion_availabilities');
    }
};

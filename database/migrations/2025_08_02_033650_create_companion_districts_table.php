<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companion_districts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('companion_profile_id')->constrained()->onDelete('cascade');
            $table->foreignId('district_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->unique(['companion_profile_id', 'district_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('companion_districts');
    }
};

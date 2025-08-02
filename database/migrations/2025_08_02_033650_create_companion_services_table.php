<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companion_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('companion_profile_id')->constrained()->onDelete('cascade');
            $table->foreignId('service_type_id')->constrained()->onDelete('cascade');
            $table->decimal('price', 10, 2)->nullable();
            $table->text('description')->nullable();
            $table->timestamps();

            $table->unique(['companion_profile_id', 'service_type_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('companion_services');
    }
};

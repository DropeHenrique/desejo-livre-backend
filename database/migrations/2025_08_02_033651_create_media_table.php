<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('companion_profile_id')->constrained()->onDelete('cascade');
            $table->string('file_name', 255);
            $table->string('file_path', 255);
            $table->enum('file_type', ['photo', 'video']);
            $table->integer('file_size');
            $table->boolean('is_primary')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};

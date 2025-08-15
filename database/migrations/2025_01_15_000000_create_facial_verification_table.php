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
        Schema::create('facial_verifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('face_encoding')->nullable(); // Encoding facial para comparação
            $table->string('document_front_path')->nullable(); // Caminho para frente do documento
            $table->string('document_back_path')->nullable(); // Caminho para verso do documento
            $table->string('face_photo_path')->nullable(); // Caminho para foto do rosto
            $table->string('document_with_face_path')->nullable(); // Caminho para foto segurando documento
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('rejection_reason')->nullable(); // Motivo da rejeição
            $table->timestamp('verified_at')->nullable(); // Data da verificação
            $table->timestamp('last_face_login_at')->nullable(); // Último login facial
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('facial_verifications');
    }
};

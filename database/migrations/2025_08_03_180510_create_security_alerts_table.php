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
        Schema::create('security_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained('chat_conversations')->onDelete('cascade');
            $table->foreignId('triggered_by')->constrained('users')->onDelete('cascade');
            $table->string('alert_type'); // phone_request, personal_info, external_contact, etc.
            $table->text('triggered_content'); // Conteúdo que disparou o alerta
            $table->text('description');
            $table->string('severity')->default('medium'); // low, medium, high, critical
            $table->boolean('is_resolved')->default(false);
            $table->timestamp('resolved_at')->nullable();
            $table->json('metadata')->nullable(); // Dados adicionais do alerta
            $table->timestamps();

            // Índices para performance
            $table->index(['conversation_id', 'created_at']);
            $table->index(['alert_type', 'severity']);
            $table->index(['is_resolved', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('security_alerts');
    }
};

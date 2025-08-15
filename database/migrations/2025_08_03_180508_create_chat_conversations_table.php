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
        Schema::create('chat_conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('companion_id')->constrained('users')->onDelete('cascade');
            $table->string('status')->default('active'); // active, blocked, archived
            $table->timestamp('last_message_at')->nullable();
            $table->timestamps();

            // Garantir que uma conversa única entre cliente e acompanhante
            $table->unique(['client_id', 'companion_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_conversations');
    }
};

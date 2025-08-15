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
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reporter_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('reported_user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->string('reported_content_type')->nullable(); // profile, photo, video, message, review, comment
            $table->unsignedBigInteger('reported_content_id')->nullable();
            $table->text('reported_content_description')->nullable();
            $table->enum('reason', [
                'inappropriate_content',
                'spam',
                'harassment',
                'fake_profile',
                'illegal_activity',
                'copyright',
                'other'
            ]);
            $table->text('description');
            $table->enum('status', ['pending', 'investigating', 'resolved', 'dismissed'])->default('pending');
            $table->string('action_taken')->nullable(); // content_removed, warning_sent, user_suspended, etc.
            $table->text('admin_notes')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index(['reported_user_id', 'status']);
            $table->index(['reported_content_type', 'reported_content_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};

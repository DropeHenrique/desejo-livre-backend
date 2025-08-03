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
        Schema::table('media', function (Blueprint $table) {
            $table->string('mime_type', 100)->nullable()->after('file_size');
            $table->integer('duration')->nullable()->after('mime_type'); // Duração em segundos
            $table->integer('width')->nullable()->after('duration');
            $table->integer('height')->nullable()->after('width');
            $table->boolean('is_verified')->default(false)->after('is_primary');
            $table->boolean('is_approved')->default(true)->after('is_verified');
            $table->integer('order')->default(0)->after('is_approved');
            $table->text('description')->nullable()->after('order');

            // Índices para melhor performance
            $table->index(['companion_profile_id', 'file_type']);
            $table->index(['companion_profile_id', 'is_primary']);
            $table->index(['companion_profile_id', 'is_approved']);
            $table->index(['companion_profile_id', 'order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('media', function (Blueprint $table) {
            $table->dropIndex(['companion_profile_id', 'file_type']);
            $table->dropIndex(['companion_profile_id', 'is_primary']);
            $table->dropIndex(['companion_profile_id', 'is_approved']);
            $table->dropIndex(['companion_profile_id', 'order']);

            $table->dropColumn([
                'mime_type',
                'duration',
                'width',
                'height',
                'is_verified',
                'is_approved',
                'order',
                'description'
            ]);
        });
    }
};

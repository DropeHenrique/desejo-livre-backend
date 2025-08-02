<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('user_type', ['client', 'companion', 'admin'])->after('email_verified_at');
            $table->string('phone', 20)->nullable()->after('user_type');
            $table->boolean('active')->default(true)->after('phone');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['user_type', 'phone', 'active']);
        });
    }
};

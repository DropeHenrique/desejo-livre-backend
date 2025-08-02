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
        Schema::table('users', function (Blueprint $table) {
            $table->string('cep', 10)->nullable()->after('phone');
            $table->string('address')->nullable()->after('cep');
            $table->string('complement')->nullable()->after('address');
            $table->foreignId('state_id')->nullable()->after('complement')->constrained('states')->onDelete('set null');
            $table->foreignId('city_id')->nullable()->after('state_id')->constrained('cities')->onDelete('set null');
            $table->foreignId('district_id')->nullable()->after('city_id')->constrained('districts')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['state_id', 'city_id', 'district_id']);
            $table->dropColumn(['cep', 'address', 'complement', 'state_id', 'city_id', 'district_id']);
        });
    }
};

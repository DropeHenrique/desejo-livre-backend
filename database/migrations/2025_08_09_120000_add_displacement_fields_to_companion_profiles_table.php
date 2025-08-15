<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companion_profiles', function (Blueprint $table) {
            if (!Schema::hasColumn('companion_profiles', 'attends_home')) {
                $table->boolean('attends_home')->default(false)->after('city_id');
            }
            if (!Schema::hasColumn('companion_profiles', 'travel_radius_km')) {
                $table->integer('travel_radius_km')->nullable()->after('attends_home');
            }
        });
    }

    public function down(): void
    {
        Schema::table('companion_profiles', function (Blueprint $table) {
            if (Schema::hasColumn('companion_profiles', 'travel_radius_km')) {
                $table->dropColumn('travel_radius_km');
            }
            if (Schema::hasColumn('companion_profiles', 'attends_home')) {
                $table->dropColumn('attends_home');
            }
        });
    }
};

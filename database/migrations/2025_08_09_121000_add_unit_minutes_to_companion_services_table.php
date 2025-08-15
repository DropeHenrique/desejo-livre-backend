<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companion_services', function (Blueprint $table) {
            if (!Schema::hasColumn('companion_services', 'unit_minutes')) {
                $table->integer('unit_minutes')->default(60)->after('price');
            }
        });
    }

    public function down(): void
    {
        Schema::table('companion_services', function (Blueprint $table) {
            if (Schema::hasColumn('companion_services', 'unit_minutes')) {
                $table->dropColumn('unit_minutes');
            }
        });
    }
};

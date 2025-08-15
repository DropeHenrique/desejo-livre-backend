<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companion_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('artistic_name', 100);
            $table->string('slug', 120)->unique();
            $table->integer('age')->nullable();
            $table->boolean('hide_age')->default(false);
            $table->text('about_me')->nullable();
            $table->integer('height')->nullable(); // em centímetros
            $table->integer('weight')->nullable(); // em quilos
            $table->string('eye_color', 50)->nullable();
            $table->string('hair_color', 50)->nullable();
            $table->string('ethnicity', 50)->nullable();
            $table->boolean('has_tattoos')->default(false);
            $table->boolean('has_piercings')->default(false);
            $table->boolean('has_silicone')->default(false);
            $table->boolean('is_smoker')->default(false);
            $table->boolean('verified')->default(false);
            $table->timestamp('verification_date')->nullable();
            $table->boolean('online_status')->default(false);
            $table->timestamp('last_active')->nullable();
            $table->foreignId('plan_id')->nullable()->constrained()->onDelete('set null');
            $table->timestamp('plan_expires_at')->nullable();
            $table->foreignId('city_id')->nullable()->constrained()->onDelete('set null');
            // Deslocamento
            $table->boolean('attends_home')->default(false);
            $table->integer('travel_radius_km')->nullable();
            $table->string('whatsapp', 20)->nullable();
            $table->string('telegram', 50)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('companion_profiles');
    }
};

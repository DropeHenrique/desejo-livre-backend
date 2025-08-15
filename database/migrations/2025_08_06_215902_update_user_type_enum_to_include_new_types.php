<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Remover a constraint check existente
        DB::statement("ALTER TABLE users DROP CONSTRAINT users_user_type_check");

        // Adicionar nova constraint com os novos valores
        DB::statement("ALTER TABLE users ADD CONSTRAINT users_user_type_check CHECK (user_type::text = ANY (ARRAY['client'::character varying, 'companion'::character varying, 'admin'::character varying, 'transvestite'::character varying, 'male_escort'::character varying]::text[]))");
    }

    public function down(): void
    {
        // Remover a nova constraint
        DB::statement("ALTER TABLE users DROP CONSTRAINT users_user_type_check");

        // Restaurar a constraint original
        DB::statement("ALTER TABLE users ADD CONSTRAINT users_user_type_check CHECK (user_type::text = ANY (ARRAY['client'::character varying, 'companion'::character varying, 'admin'::character varying]::text[]))");
    }
};

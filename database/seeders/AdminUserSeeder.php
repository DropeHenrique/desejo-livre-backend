<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Verificar se o usuário admin já existe
        $existingAdmin = User::where('email', 'pedrohenriquecrispim@hotmail.com')->first();

        if ($existingAdmin) {
            $this->command->info('Usuário admin já existe. Atualizando...');
            $existingAdmin->update([
                'name' => 'Pedro Henrique Crispim',
                'password' => Hash::make('P&dr0.35628534'),
                'user_type' => 'admin',
                'active' => true,
            ]);
            $this->command->info('Usuário admin atualizado com sucesso!');
        } else {
            // Criar novo usuário admin
            User::create([
                'name' => 'Pedro Henrique Crispim',
                'email' => 'pedrohenriquecrispim@hotmail.com',
                'password' => Hash::make('P&dr0.35628534'),
                'user_type' => 'admin',
                'active' => true,
                'email_verified_at' => now(),
            ]);
            $this->command->info('Usuário admin criado com sucesso!');
        }

        $this->command->info('Credenciais do Admin:');
        $this->command->info('Email: pedrohenriquecrispim@hotmail.com');
        $this->command->info('Senha: P&dr0.35628534');
        $this->command->info('Tipo: admin');
    }
}

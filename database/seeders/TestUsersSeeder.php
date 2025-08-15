<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\CompanionProfile;
use App\Models\State;
use App\Models\City;
use App\Models\District;
use Illuminate\Support\Facades\Hash;

class TestUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        echo "Criando usuários de teste...\n";

        // Buscar localização de exemplo (São Paulo)
        $sp = State::where('uf', 'SP')->first();
        $saoPaulo = City::where('name', 'São Paulo')->where('state_id', $sp->id)->first();
        $centro = District::where('name', 'Centro')->where('city_id', $saoPaulo->id)->first();

        // 1. Criar usuário CLIENTE
        $clientUser = User::firstOrCreate(
            ['email' => 'cliente@teste.com'],
            [
                'name' => 'João Silva',
                'email' => 'cliente@teste.com',
                'password' => Hash::make('password'),
                'user_type' => 'client',
                'phone' => '(11) 99999-9999',
                'active' => true,
                'email_verified_at' => now(),
                'cep' => '01234-567',
                'address' => 'Rua das Flores, 123',
                'complement' => 'Apto 45',
                'state_id' => $sp->id,
                'city_id' => $saoPaulo->id,
                'district_id' => $centro->id,
            ]
        );

        echo "✅ Cliente criado: {$clientUser->name} ({$clientUser->email})\n";

        // 2. Criar usuário ACOMPANHANTE
        $companionUser = User::firstOrCreate(
            ['email' => 'acompanhante@teste.com'],
            [
                'name' => 'Maria Santos',
                'email' => 'acompanhante@teste.com',
                'password' => Hash::make('password'),
                'user_type' => 'companion',
                'phone' => '(11) 88888-8888',
                'active' => true,
                'email_verified_at' => now(),
                'cep' => '01234-890',
                'address' => 'Rua das Palmeiras, 456',
                'complement' => 'Casa',
                'state_id' => $sp->id,
                'city_id' => $saoPaulo->id,
                'district_id' => $centro->id,
            ]
        );

        echo "✅ Acompanhante criado: {$companionUser->name} ({$companionUser->email})\n";

        // 3. Criar perfil de acompanhante para o usuário acompanhante
        if (!$companionUser->companionProfile) {
            $companionProfile = CompanionProfile::create([
                'user_id' => $companionUser->id,
                'artistic_name' => 'Maria Santos',
                'age' => 28,
                'about_me' => 'Acompanhante profissional, ofereço serviços de qualidade com total discrição e segurança.',
                'height' => 165,
                'weight' => 55,
                'hair_color' => 'castanho',
                'eye_color' => 'castanho',
                'ethnicity' => 'branca',
                'has_tattoos' => false,
                'has_piercings' => true,
                'is_smoker' => false,
                'verified' => true,
                'verification_date' => now(),
                'online_status' => true,
                'last_active' => now(),
                'city_id' => $saoPaulo->id,
                'whatsapp' => '(11) 88888-8888',
                'telegram' => '@mariasantos',
            ]);

            echo "✅ Perfil de acompanhante criado para: {$companionUser->name}\n";
        }

        // 4. Criar usuário ADMIN (se não existir)
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@teste.com'],
            [
                'name' => 'Administrador',
                'email' => 'admin@teste.com',
                'password' => Hash::make('password'),
                'user_type' => 'admin',
                'phone' => '(11) 77777-7777',
                'active' => true,
                'email_verified_at' => now(),
            ]
        );

        echo "✅ Admin criado: {$adminUser->name} ({$adminUser->email})\n";

        echo "\n🎯 Usuários de teste criados com sucesso!\n";
        echo "\n📋 Credenciais de acesso:\n";
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        echo "👤 CLIENTE:\n";
        echo "   Email: cliente@teste.com\n";
        echo "   Senha: password\n";
        echo "   Tipo: client\n";
        echo "\n👩 ACOMPANHANTE:\n";
        echo "   Email: acompanhante@teste.com\n";
        echo "   Senha: password\n";
        echo "   Tipo: companion\n";
        echo "\n👨‍💼 ADMIN:\n";
        echo "   Email: admin@teste.com\n";
        echo "   Senha: password\n";
        echo "   Tipo: admin\n";
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    }
}

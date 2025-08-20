<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Media;
use App\Models\CompanionProfile;
use App\Models\User;
use App\Models\State;
use App\Models\City;
use App\Models\District;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class MediaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Desabilitar Scout temporariamente para evitar erros de indexação
        config(['scout.driver' => 'null']);

        $this->command->info('Iniciando MediaSeeder...');

        // Limpar mídia existente para evitar duplicatas
        Media::query()->delete();
        $this->command->info('Mídia existente removida.');

        // Buscar acompanhantes existentes
        $companions = CompanionProfile::with('user')->get();

        if ($companions->isEmpty()) {
            $this->command->info('Nenhuma acompanhante encontrada. Criando algumas para teste...');

            // Buscar localizações existentes
            $sp = State::where('uf', 'SP')->first();
            $saoPaulo = City::where('name', 'São Paulo')->where('state_id', $sp->id)->first();
            $centro = District::where('name', 'Centro')->where('city_id', $saoPaulo->id)->first();

            if (!$sp || !$saoPaulo || !$centro) {
                $this->command->error('Localizações necessárias não encontradas. Execute o LocationSeeder primeiro.');
                return;
            }

            // Criar algumas acompanhantes de teste de forma controlada
            $companionData = [
                [
                    'name' => 'Ana Silva',
                    'email' => 'ana.silva@teste.com',
                    'artistic_name' => 'Ana Silva',
                    'age' => 25,
                    'about_me' => 'Acompanhante profissional com experiência em diversos serviços.',
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
                    'whatsapp' => '(11) 91111-1111',
                    'telegram' => '@ana_silva',
                ],
                [
                    'name' => 'Beatriz Costa',
                    'email' => 'beatriz.costa@teste.com',
                    'artistic_name' => 'Beatriz Costa',
                    'age' => 28,
                    'about_me' => 'Acompanhante elegante e discreta, ofereço serviços de qualidade.',
                    'height' => 170,
                    'weight' => 58,
                    'hair_color' => 'loiro',
                    'eye_color' => 'azul',
                    'ethnicity' => 'branca',
                    'has_tattoos' => true,
                    'has_piercings' => false,
                    'is_smoker' => false,
                    'verified' => true,
                    'verification_date' => now(),
                    'online_status' => true,
                    'last_active' => now(),
                    'city_id' => $saoPaulo->id,
                    'whatsapp' => '(11) 92222-2222',
                    'telegram' => '@beatriz_costa',
                ],
                [
                    'name' => 'Camila Santos',
                    'email' => 'camila.santos@teste.com',
                    'artistic_name' => 'Camila Santos',
                    'age' => 23,
                    'about_me' => 'Acompanhante jovem e vibrante, especialista em massagens relaxantes.',
                    'height' => 168,
                    'weight' => 52,
                    'hair_color' => 'ruivo',
                    'eye_color' => 'verde',
                    'ethnicity' => 'branca',
                    'has_tattoos' => false,
                    'has_piercings' => true,
                    'is_smoker' => false,
                    'verified' => true,
                    'verification_date' => now(),
                    'online_status' => true,
                    'last_active' => now(),
                    'city_id' => $saoPaulo->id,
                    'whatsapp' => '(11) 93333-3333',
                    'telegram' => '@camila_santos',
                ]
            ];

            foreach ($companionData as $data) {
                // Criar usuário
                $user = User::firstOrCreate(
                    ['email' => $data['email']],
                    [
                        'name' => $data['name'],
                        'email' => $data['email'],
                        'password' => Hash::make('password'),
                        'user_type' => 'companion',
                        'phone' => $data['whatsapp'],
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

                // Criar perfil de acompanhante
                CompanionProfile::firstOrCreate(
                    ['user_id' => $user->id],
                    [
                        'user_id' => $user->id,
                        'artistic_name' => $data['artistic_name'],
                        'age' => $data['age'],
                        'about_me' => $data['about_me'],
                        'height' => $data['height'],
                        'weight' => $data['weight'],
                        'hair_color' => $data['hair_color'],
                        'eye_color' => $data['eye_color'],
                        'ethnicity' => $data['ethnicity'],
                        'has_tattoos' => $data['has_tattoos'],
                        'has_piercings' => $data['has_piercings'],
                        'is_smoker' => $data['is_smoker'],
                        'verified' => $data['verified'],
                        'verification_date' => $data['verification_date'],
                        'online_status' => $data['online_status'],
                        'last_active' => $data['last_active'],
                        'city_id' => $data['city_id'],
                        'whatsapp' => $data['whatsapp'],
                        'telegram' => $data['telegram'],
                    ]
                );
            }

            $companions = CompanionProfile::with('user')->get();
        }

        $this->command->info('Criando mídia para ' . $companions->count() . ' acompanhantes...');

        $totalMedia = 0;

        foreach ($companions as $companion) {
            $this->command->info("Criando mídia para: {$companion->artistic_name}");

            // Criar 3-6 fotos por acompanhante
            $photoCount = rand(3, 6);

            for ($i = 0; $i < $photoCount; $i++) {
                $isPrimary = $i === 0; // Primeira foto é sempre primária

                Media::create([
                    'companion_profile_id' => $companion->id,
                    'file_name' => 'photo-' . ($i + 1) . '.jpg',
                    'file_path' => 'companions/' . $companion->id . '/photos/photo-' . ($i + 1) . '.jpg',
                    'file_type' => 'photo',
                    'file_size' => rand(500000, 3000000), // 500KB a 3MB
                    'mime_type' => 'image/jpeg',
                    'width' => rand(800, 2560),
                    'height' => rand(600, 1440),
                    'is_primary' => $isPrimary,
                    'is_approved' => true,
                    'is_verified' => rand(0, 1), // 50% chance de ser verificado
                    'order' => $i + 1,
                    'description' => fake()->optional(0.7)->sentence(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $totalMedia++;
            }

            // Criar 1-2 vídeos por acompanhante (opcional)
            if (rand(0, 1)) { // 50% chance de ter vídeos
                $videoCount = rand(1, 2);

                for ($i = 0; $i < $videoCount; $i++) {
                    Media::create([
                        'companion_profile_id' => $companion->id,
                        'file_name' => 'video-' . ($i + 1) . '.mp4',
                        'file_path' => 'companions/' . $companion->id . '/videos/video-' . ($i + 1) . '.mp4',
                        'file_type' => 'video',
                        'file_size' => rand(5000000, 50000000), // 5MB a 50MB
                        'mime_type' => 'video/mp4',
                        'width' => rand(640, 1920),
                        'height' => rand(480, 1080),
                        'duration' => rand(10, 180), // 10 segundos a 3 minutos
                        'is_primary' => false,
                        'is_approved' => true,
                        'is_verified' => rand(0, 1),
                        'order' => $photoCount + $i + 1,
                        'description' => fake()->optional(0.6)->sentence(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    $totalMedia++;
                }
            }
        }

        $this->command->info('MediaSeeder concluído com sucesso!');
        $this->command->info('Total de mídia criada: ' . $totalMedia);
        $this->command->info('Total de mídia na base: ' . Media::count());
    }
}

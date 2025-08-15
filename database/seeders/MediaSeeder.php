<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Media;
use App\Models\CompanionProfile;
use App\Models\User;
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

            // Criar algumas acompanhantes de teste
            $users = User::factory()->count(5)->create(['user_type' => 'companion']);

            foreach ($users as $user) {
                CompanionProfile::factory()->create([
                    'user_id' => $user->id,
                    'artistic_name' => fake()->firstName('female') . ' ' . fake()->lastName(),
                ]);
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

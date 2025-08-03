<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CompanionProfile;
use App\Models\ServiceType;
use Illuminate\Support\Facades\DB;

class CompanionServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Criando serviços dos acompanhantes...');

        $companions = CompanionProfile::all();
        $serviceTypes = ServiceType::all();

        if ($companions->isEmpty()) {
            $this->command->error('Nenhum acompanhante encontrado. Execute o CompanionProfileSeeder primeiro.');
            return;
        }

        if ($serviceTypes->isEmpty()) {
            $this->command->error('Nenhum tipo de serviço encontrado. Execute o ServiceTypeSeeder primeiro.');
            return;
        }

        $createdCount = 0;

        foreach ($companions as $companion) {
            // Cada acompanhante oferece 3-6 tipos de serviço
            $numServices = rand(3, min(6, $serviceTypes->count()));
            $selectedServices = $serviceTypes->random($numServices);

            foreach ($selectedServices as $serviceType) {
                // Verificar se já existe o relacionamento
                $exists = DB::table('companion_services')
                    ->where('companion_profile_id', $companion->id)
                    ->where('service_type_id', $serviceType->id)
                    ->exists();

                if (!$exists) {
                    // Gerar preço baseado no tipo de serviço
                    $basePrice = $this->getBasePriceForService($serviceType->name);
                    $price = $basePrice + rand(-50, 100); // Variação de preço

                    // Garantir preço mínimo
                    $price = max($price, 50);

                    DB::table('companion_services')->insert([
                        'companion_profile_id' => $companion->id,
                        'service_type_id' => $serviceType->id,
                        'price' => $price,
                        'description' => $this->generateServiceDescription($serviceType->name, $companion),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $createdCount++;
                }
            }
        }

        $this->command->info("✅ {$createdCount} serviços de acompanhantes criados com sucesso!");

        // Estatísticas
        $totalServices = DB::table('companion_services')->count();
        $companionsWithServices = DB::table('companion_services')
            ->distinct('companion_profile_id')
            ->count('companion_profile_id');

        $avgPrice = DB::table('companion_services')->avg('price');

        $this->command->info("📊 Total de serviços: {$totalServices}");
        $this->command->info("👥 Acompanhantes com serviços: {$companionsWithServices}");
        $this->command->info("💰 Preço médio: R$ " . number_format($avgPrice, 2, ',', '.'));
    }

    private function getBasePriceForService(string $serviceName): int
    {
        return match (strtolower($serviceName)) {
            'massagem' => 150,
            'companhia' => 200,
            'jantar' => 300,
            'viagem' => 500,
            'fantasias' => 400,
            'dupla' => 600,
            'fetiches' => 350,
            'fotos' => 250,
            default => 200,
        };
    }

    private function generateServiceDescription(string $serviceName, CompanionProfile $companion): string
    {
        $descriptions = [
            'massagem' => [
                "Massagem relaxante e terapêutica com {artistic_name}. Ambiente tranquilo e profissional.",
                "Sessão de massagem personalizada com {artistic_name}. Técnicas variadas para seu relaxamento.",
                "Massagem sensual e relaxante com {artistic_name}. Experiência única e memorável."
            ],
            'companhia' => [
                "Acompanhamento elegante com {artistic_name} para seus eventos especiais.",
                "Companhia discreta e profissional com {artistic_name} para qualquer ocasião.",
                "Acompanhamento personalizado com {artistic_name}. Momentos inesquecíveis garantidos."
            ],
            'jantar' => [
                "Jantar romântico e elegante com {artistic_name} em restaurantes selecionados.",
                "Acompanhamento para jantares especiais com {artistic_name}. Conversa agradável e companhia perfeita.",
                "Jantar sofisticado com {artistic_name}. Experiência gastronômica e social única."
            ],
            'viagem' => [
                "Acompanhamento para viagens com {artistic_name}. Companhia perfeita para suas aventuras.",
                "Viagem personalizada com {artistic_name}. Momentos especiais em destinos únicos.",
                "Acompanhamento de luxo para viagens com {artistic_name}. Experiência exclusiva."
            ],
            'fantasias' => [
                "Realização de fantasias especiais com {artistic_name}. Experiência personalizada e segura.",
                "Fantasias criativas com {artistic_name}. Momentos únicos e memoráveis.",
                "Realização de desejos especiais com {artistic_name}. Discreção e profissionalismo."
            ],
            'dupla' => [
                "Serviço em dupla com {artistic_name} e parceira. Experiência intensa e única.",
                "Dupla especial com {artistic_name}. Momentos inesquecíveis em companhia dupla.",
                "Serviço em dupla personalizado com {artistic_name}. Experiência exclusiva."
            ],
            'fetiches' => [
                "Serviços especializados em fetiches com {artistic_name}. Discreção total garantida.",
                "Fetiches personalizados com {artistic_name}. Experiência única e segura.",
                "Serviços especializados com {artistic_name}. Realização de desejos especiais."
            ],
            'fotos' => [
                "Sessão de fotos profissionais com {artistic_name}. Imagens artísticas e elegantes.",
                "Fotografia personalizada com {artistic_name}. Momentos capturados com arte.",
                "Sessão fotográfica exclusiva com {artistic_name}. Imagens únicas e memoráveis."
            ]
        ];

        $serviceKey = strtolower($serviceName);
        $serviceDescriptions = $descriptions[$serviceKey] ?? $descriptions['companhia'];

        $description = $serviceDescriptions[array_rand($serviceDescriptions)];

        return str_replace('{artistic_name}', $companion->artistic_name, $description);
    }
}

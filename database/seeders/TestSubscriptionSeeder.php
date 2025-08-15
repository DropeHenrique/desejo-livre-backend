<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Plan;
use App\Models\Subscription;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TestSubscriptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Limpar assinaturas existentes
        DB::table('subscriptions')->truncate();

        // Buscar usuários de teste
        $testUsers = User::whereIn('email', [
            'teste@desejolivre.com',
            'cliente@desejolivre.com',
            'acompanhante@desejolivre.com'
        ])->get();

        // Buscar planos
        $plans = Plan::all();

        foreach ($testUsers as $user) {
            // Escolher um plano baseado no tipo de usuário
            $plan = $plans->where('user_type', $user->user_type)->first();

            if ($plan) {
                // Criar assinatura ativa
                Subscription::create([
                    'user_id' => $user->id,
                    'plan_id' => $plan->id,
                    'status' => 'active',
                    'starts_at' => now(),
                    'expires_at' => now()->addDays($plan->duration_days),
                ]);

                $this->command->info("Assinatura criada para {$user->email} - Plano: {$plan->name}");
            }
        }

        $this->command->info('Assinaturas de teste criadas com sucesso!');
    }
}

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\CompanionProfile;
use App\Models\User;

class ListProfiles extends Command
{
    protected $signature = 'profiles:list';
    protected $description = 'List companion profiles by user type';

    public function handle()
    {
        $this->info('=== PERFIS DE ACOMPANHANTE ===');

        // Perfis de travestis
        $transvestiteProfiles = CompanionProfile::whereHas('user', function($q) {
            $q->where('user_type', 'transvestite');
        })->with('user')->get();

        $this->info("Perfis de travestis: {$transvestiteProfiles->count()}");
        if ($transvestiteProfiles->count() > 0) {
            $transvestiteProfiles->each(function($profile) {
                $this->line("• {$profile->artistic_name} - {$profile->user->email}");
                $this->line("  Idade: {$profile->age}, Altura: {$profile->height}cm, Peso: {$profile->weight}kg");
                $this->line("  Cabelo: {$profile->hair_color}, Olhos: {$profile->eye_color}");
                $this->line("  Verificado: " . ($profile->verified ? '✅ Sim' : '❌ Não'));
                $this->line("  Online: " . ($profile->online_status ? '🟢 Sim' : '🔴 Não'));
                $this->line("");
            });
        }

        // Perfis de garotos de programa
        $maleEscortProfiles = CompanionProfile::whereHas('user', function($q) {
            $q->where('user_type', 'male_escort');
        })->with('user')->get();

        $this->info("Perfis de garotos de programa: {$maleEscortProfiles->count()}");
        if ($maleEscortProfiles->count() > 0) {
            $maleEscortProfiles->each(function($profile) {
                $this->line("• {$profile->artistic_name} - {$profile->user->email}");
                $this->line("  Idade: {$profile->age}, Altura: {$profile->height}cm, Peso: {$profile->weight}kg");
                $this->line("  Cabelo: {$profile->hair_color}, Olhos: {$profile->eye_color}");
                $this->line("  Verificado: " . ($profile->verified ? '✅ Sim' : '❌ Não'));
                $this->line("  Online: " . ($profile->online_status ? '🟢 Sim' : '🔴 Não'));
                $this->line("");
            });
        }

        return 0;
    }
}

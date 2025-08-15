<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class ListUsers extends Command
{
    protected $signature = 'users:list';
    protected $description = 'List all users by type';

    public function handle()
    {
        $this->info('=== USUÁRIOS NO SISTEMA ===');

        $totalUsers = User::count();
        $transvestites = User::where('user_type', 'transvestite')->count();
        $maleEscorts = User::where('user_type', 'male_escort')->count();
        $companions = User::where('user_type', 'companion')->count();
        $clients = User::where('user_type', 'client')->count();
        $admins = User::where('user_type', 'admin')->count();

        $this->info("Total de usuários: {$totalUsers}");
        $this->info("Travestis: {$transvestites}");
        $this->info("Garotos de programa: {$maleEscorts}");
        $this->info("Acompanhantes: {$companions}");
        $this->info("Clientes: {$clients}");
        $this->info("Admins: {$admins}");

        if ($transvestites > 0) {
            $this->newLine();
            $this->info('=== TRAVESTIS ===');
            User::where('user_type', 'transvestite')->get(['name', 'email', 'active'])->each(function($user) {
                $status = $user->active ? '✅ Ativo' : '❌ Inativo';
                $this->line("• {$user->name} ({$user->email}) - {$status}");
            });
        }

        if ($maleEscorts > 0) {
            $this->newLine();
            $this->info('=== GAROTOS DE PROGRAMA ===');
            User::where('user_type', 'male_escort')->get(['name', 'email', 'active'])->each(function($user) {
                $status = $user->active ? '✅ Ativo' : '❌ Inativo';
                $this->line("• {$user->name} ({$user->email}) - {$status}");
            });
        }

        return 0;
    }
}

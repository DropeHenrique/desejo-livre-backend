<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Report;
use App\Models\SupportTicket;
use App\Models\User;

class SampleDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Criar alguns tickets de exemplo
        $this->createSampleTickets();

        // Criar algumas denúncias de exemplo
        $this->createSampleReports();
    }

    private function createSampleTickets(): void
    {
        $users = User::where('user_type', '!=', 'admin')->take(5)->get();

        if ($users->isEmpty()) {
            $this->command->info('Nenhum usuário encontrado para criar tickets de exemplo');
            return;
        }

        $ticketData = [
            [
                'subject' => 'Problema com login',
                'message' => 'Não consigo fazer login na minha conta. Aparece erro de senha incorreta.',
                'priority' => 'high',
                'category' => 'technical',
                'status' => 'open',
            ],
            [
                'subject' => 'Dúvida sobre assinatura',
                'message' => 'Gostaria de saber como cancelar minha assinatura premium.',
                'priority' => 'medium',
                'category' => 'billing',
                'status' => 'in_progress',
            ],
            [
                'subject' => 'Problema com fotos',
                'message' => 'As fotos do meu perfil não estão carregando corretamente.',
                'priority' => 'low',
                'category' => 'technical',
                'status' => 'resolved',
            ],
            [
                'subject' => 'Conta bloqueada',
                'message' => 'Minha conta foi bloqueada sem motivo aparente. Preciso de ajuda.',
                'priority' => 'urgent',
                'category' => 'account',
                'status' => 'open',
            ],
            [
                'subject' => 'Dúvida sobre serviços',
                'message' => 'Como funciona o sistema de avaliações?',
                'priority' => 'low',
                'category' => 'general',
                'status' => 'closed',
            ],
        ];

        foreach ($ticketData as $index => $data) {
            if (isset($users[$index])) {
                SupportTicket::create([
                    'user_id' => $users[$index]->id,
                    'subject' => $data['subject'],
                    'message' => $data['message'],
                    'priority' => $data['priority'],
                    'category' => $data['category'],
                    'status' => $data['status'],
                    'last_reply_at' => now(),
                ]);
            }
        }

        $this->command->info('Tickets de exemplo criados com sucesso!');
    }

    private function createSampleReports(): void
    {
        $users = User::where('user_type', '!=', 'admin')->take(5)->get();

        if ($users->isEmpty()) {
            $this->command->info('Nenhum usuário encontrado para criar denúncias de exemplo');
            return;
        }

        $reportData = [
            [
                'reported_user_id' => $users->random()->id,
                'reported_content_type' => 'profile',
                'reported_content_id' => 1,
                'reported_content_description' => 'Perfil com fotos inadequadas',
                'reason' => 'inappropriate_content',
                'description' => 'Este perfil contém fotos que violam os termos de uso da plataforma.',
                'status' => 'pending',
            ],
            [
                'reported_user_id' => $users->random()->id,
                'reported_content_type' => 'message',
                'reported_content_id' => 1,
                'reported_content_description' => 'Mensagem com spam',
                'reason' => 'spam',
                'description' => 'Usuário enviando mensagens em massa com links suspeitos.',
                'status' => 'investigating',
            ],
            [
                'reported_user_id' => $users->random()->id,
                'reported_content_type' => 'review',
                'reported_content_id' => 1,
                'reported_content_description' => 'Avaliação falsa',
                'reason' => 'fake_profile',
                'description' => 'Avaliação parece ser falsa ou manipulada.',
                'status' => 'resolved',
                'action_taken' => 'warning_sent',
            ],
            [
                'reported_user_id' => $users->random()->id,
                'reported_content_type' => 'photo',
                'reported_content_id' => 1,
                'reported_content_description' => 'Foto com copyright',
                'reason' => 'copyright',
                'description' => 'Foto parece ser de terceiros sem autorização.',
                'status' => 'dismissed',
                'admin_notes' => 'Denúncia infundada, foto é original.',
            ],
            [
                'reported_user_id' => $users->random()->id,
                'reported_content_type' => 'profile',
                'reported_content_id' => 1,
                'reported_content_description' => 'Perfil com assédio',
                'reason' => 'harassment',
                'description' => 'Usuário enviando mensagens ofensivas e ameaçadoras.',
                'status' => 'resolved',
                'action_taken' => 'content_removed',
            ],
        ];

        foreach ($reportData as $index => $data) {
            if (isset($users[$index])) {
                Report::create([
                    'reporter_id' => $users[$index]->id,
                    'reported_user_id' => $data['reported_user_id'],
                    'reported_content_type' => $data['reported_content_type'],
                    'reported_content_id' => $data['reported_content_id'],
                    'reported_content_description' => $data['reported_content_description'],
                    'reason' => $data['reason'],
                    'description' => $data['description'],
                    'status' => $data['status'],
                    'action_taken' => $data['action_taken'] ?? null,
                    'admin_notes' => $data['admin_notes'] ?? null,
                ]);
            }
        }

        $this->command->info('Denúncias de exemplo criadas com sucesso!');
    }
}

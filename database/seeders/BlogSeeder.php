<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\User;
use Illuminate\Support\Str;

class BlogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Criar categorias do blog
        $categories = [
            [
                'name' => 'Dicas de Relacionamento',
                'description' => 'Artigos sobre como melhorar relacionamentos e comunicação',
            ],
            [
                'name' => 'Bem-estar e Saúde',
                'description' => 'Conteúdo sobre saúde mental, física e qualidade de vida',
            ],
            [
                'name' => 'Lifestyle',
                'description' => 'Dicas sobre estilo de vida, moda e comportamento',
            ],
            [
                'name' => 'Entretenimento',
                'description' => 'Notícias e curiosidades sobre entretenimento',
            ],
            [
                'name' => 'Tecnologia',
                'description' => 'Novidades e tendências em tecnologia',
            ],
        ];

        foreach ($categories as $categoryData) {
            BlogCategory::firstOrCreate(
                ['name' => $categoryData['name']],
                $categoryData
            );
        }

        // Buscar um usuário para ser o autor dos posts
        $user = User::first();
        if (!$user) {
            // Se não houver usuários, criar um
            $user = User::create([
                'name' => 'Admin Blog',
                'email' => 'admin@blog.com',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
                'user_type' => 'admin',
            ]);
        }

        // Buscar categorias criadas
        $relationshipCategory = BlogCategory::where('name', 'Dicas de Relacionamento')->first();
        $wellnessCategory = BlogCategory::where('name', 'Bem-estar e Saúde')->first();
        $lifestyleCategory = BlogCategory::where('name', 'Lifestyle')->first();
        $entertainmentCategory = BlogCategory::where('name', 'Entretenimento')->first();
        $techCategory = BlogCategory::where('name', 'Tecnologia')->first();

        // Criar posts do blog
        $posts = [
            [
                'title' => 'Como Melhorar a Comunicação no Relacionamento',
                'content' => $this->getRelationshipContent(),
                'excerpt' => 'Aprenda técnicas práticas para melhorar a comunicação com seu parceiro e fortalecer o relacionamento.',
                'user_id' => $user->id,
                'status' => 'published',
                'published_at' => now()->subDays(5),
                'featured_image' => 'blog/communication.jpg',
                'categories' => [$relationshipCategory->id],
            ],
            [
                'title' => '10 Dicas para Manter a Saúde Mental em Dia',
                'content' => $this->getMentalHealthContent(),
                'excerpt' => 'Descubra práticas simples que podem transformar sua saúde mental e qualidade de vida.',
                'user_id' => $user->id,
                'status' => 'published',
                'published_at' => now()->subDays(3),
                'featured_image' => 'blog/mental-health.jpg',
                'categories' => [$wellnessCategory->id],
            ],
            [
                'title' => 'Tendências de Moda para 2024',
                'content' => $this->getFashionContent(),
                'excerpt' => 'Confira as principais tendências de moda que estão fazendo sucesso este ano.',
                'user_id' => $user->id,
                'status' => 'published',
                'published_at' => now()->subDays(1),
                'featured_image' => 'blog/fashion.jpg',
                'categories' => [$lifestyleCategory->id],
            ],
            [
                'title' => 'Os Melhores Filmes de 2024',
                'content' => $this->getMoviesContent(),
                'excerpt' => 'Uma seleção dos filmes mais aclamados e populares do ano.',
                'user_id' => $user->id,
                'status' => 'published',
                'published_at' => now()->subHours(12),
                'featured_image' => 'blog/movies.jpg',
                'categories' => [$entertainmentCategory->id],
            ],
            [
                'title' => 'Inteligência Artificial: O Futuro da Tecnologia',
                'content' => $this->getAIContent(),
                'excerpt' => 'Entenda como a IA está transformando diferentes setores e o que esperar para o futuro.',
                'user_id' => $user->id,
                'status' => 'published',
                'published_at' => now()->subHours(6),
                'featured_image' => 'blog/ai.jpg',
                'categories' => [$techCategory->id],
            ],
            [
                'title' => 'Como Encontrar o Equilíbrio Entre Trabalho e Vida Pessoal',
                'content' => $this->getWorkLifeBalanceContent(),
                'excerpt' => 'Estratégias práticas para conciliar carreira e vida pessoal de forma saudável.',
                'user_id' => $user->id,
                'status' => 'published',
                'published_at' => now()->subHours(2),
                'featured_image' => 'blog/work-life.jpg',
                'categories' => [$wellnessCategory->id, $lifestyleCategory->id],
            ],
            [
                'title' => 'Dicas para um Primeiro Encontro Perfeito',
                'content' => $this->getFirstDateContent(),
                'excerpt' => 'Prepare-se para um primeiro encontro memorável com essas dicas essenciais.',
                'user_id' => $user->id,
                'status' => 'published',
                'published_at' => now()->subHours(1),
                'featured_image' => 'blog/first-date.jpg',
                'categories' => [$relationshipCategory->id, $lifestyleCategory->id],
            ],
        ];

        foreach ($posts as $postData) {
            $categories = $postData['categories'];
            unset($postData['categories']);

            $post = BlogPost::firstOrCreate(
                ['title' => $postData['title']],
                $postData
            );

            // Associar categorias ao post
            $post->categories()->sync($categories);
        }

        $this->command->info('Blog seeded successfully!');
    }

    private function getRelationshipContent(): string
    {
        return '<h2>A Importância da Comunicação</h2>
        <p>A comunicação é a base de qualquer relacionamento saudável. Sem ela, mal-entendidos se acumulam e a distância emocional aumenta. Neste artigo, vamos explorar técnicas práticas para melhorar a comunicação com seu parceiro.</p>

        <h3>1. Pratique a Escuta Ativa</h3>
        <p>A escuta ativa vai além de apenas ouvir. Significa prestar total atenção ao que seu parceiro está dizendo, sem interromper ou preparar sua resposta enquanto ele fala. Faça perguntas para esclarecer pontos e demonstre interesse genuíno.</p>

        <h3>2. Use "Eu" em vez de "Você"</h3>
        <p>Quando expressar sentimentos ou preocupações, comece suas frases com "Eu sinto" ou "Eu penso" em vez de "Você sempre" ou "Você nunca". Isso evita que seu parceiro se sinta atacado e promove um diálogo mais construtivo.</p>

        <h3>3. Escolha o Momento Certo</h3>
        <p>Nem todos os momentos são ideais para conversas importantes. Evite discutir assuntos sérios quando um de vocês estiver cansado, estressado ou com pressa. Escolha um momento em que ambos possam se dedicar totalmente à conversa.</p>

        <h3>4. Seja Específico</h3>
        <p>Em vez de fazer acusações vagas como "Você não me ama", seja específico sobre o que está sentindo e o que gostaria que mudasse. Por exemplo: "Eu me sinto sozinho quando você passa muito tempo no celular durante nossos momentos juntos."</p>

        <h3>5. Pratique a Empatia</h3>
        <p>Tente se colocar no lugar do seu parceiro e entender sua perspectiva. Mesmo que você não concorde, reconheça que os sentimentos dele são válidos e importantes.</p>

        <h2>Conclusão</h2>
        <p>Melhorar a comunicação em um relacionamento é um processo contínuo que requer prática e paciência. Comece implementando essas técnicas gradualmente e observe como sua conexão com seu parceiro se fortalece ao longo do tempo.</p>';
    }

    private function getMentalHealthContent(): string
    {
        return '<h2>Cuidando da Saúde Mental</h2>
        <p>Em um mundo cada vez mais acelerado, cuidar da saúde mental se tornou essencial. Aqui estão 10 dicas práticas que podem fazer uma grande diferença no seu bem-estar emocional.</p>

        <h3>1. Estabeleça uma Rotina</h3>
        <p>Ter uma rotina estruturada ajuda a criar sensação de controle e previsibilidade. Tente dormir e acordar nos mesmos horários, mesmo nos fins de semana.</p>

        <h3>2. Pratique Exercícios Físicos</h3>
        <p>A atividade física libera endorfinas, hormônios que promovem sensação de bem-estar. Mesmo 30 minutos de caminhada diária podem fazer uma diferença significativa.</p>

        <h3>3. Limite o Uso de Redes Sociais</h3>
        <p>As redes sociais podem causar comparação excessiva e ansiedade. Estabeleça limites de tempo e faça pausas regulares.</p>

        <h3>4. Conecte-se com Outras Pessoas</h3>
        <p>Manter relacionamentos saudáveis é fundamental para a saúde mental. Reserve tempo para conversar com amigos e familiares.</p>

        <h3>5. Aprenda a Dizer "Não"</h3>
        <p>Estabelecer limites é essencial para evitar sobrecarga e estresse. Não tenha medo de recusar compromissos que possam prejudicar seu bem-estar.</p>

        <h3>6. Pratique Mindfulness</h3>
        <p>Atenção plena pode ajudar a reduzir ansiedade e estresse. Tente meditar por 10 minutos por dia ou simplesmente preste atenção ao momento presente.</p>

        <h3>7. Durma Bem</h3>
        <p>O sono é fundamental para a saúde mental. Crie um ambiente propício ao sono e evite telas antes de dormir.</p>

        <h3>8. Alimente-se Bem</h3>
        <p>Uma dieta equilibrada pode impactar positivamente o humor e a energia. Evite excessos de açúcar e cafeína.</p>

        <h3>9. Busque Ajuda Profissional</h3>
        <p>Se estiver enfrentando dificuldades, não hesite em buscar ajuda de um psicólogo ou psiquiatra. É um sinal de força, não de fraqueza.</p>

        <h3>10. Pratique a Gratidão</h3>
        <p>Focar nas coisas boas da vida pode melhorar significativamente o humor. Tente listar três coisas pelas quais você é grato todos os dias.</p>

        <h2>Lembre-se</h2>
        <p>Cuidar da saúde mental é tão importante quanto cuidar da saúde física. Pequenas mudanças podem ter um grande impacto no seu bem-estar geral.</p>';
    }

    private function getFashionContent(): string
    {
        return '<h2>Tendências de Moda 2024</h2>
        <p>O ano de 2024 traz consigo uma mistura interessante de nostalgia e inovação no mundo da moda. Vamos explorar as principais tendências que estão dominando as passarelas e as ruas.</p>

        <h3>1. Y2K Revival</h3>
        <p>A nostalgia dos anos 2000 está em alta! Calças de cintura baixa, tops cropped, acessórios brilhantes e cores vibrantes estão de volta com força total.</p>

        <h3>2. Minimalismo Sustentável</h3>
        <p>Em contraste com o Y2K, o minimalismo continua forte, mas agora com foco na sustentabilidade. Peças atemporais, tecidos eco-friendly e designs clean são tendência.</p>

        <h3>3. Cores Vibrantes</h3>
        <p>Rosa choque, azul elétrico, verde lima e amarelo vibrante estão dominando as coleções. Não tenha medo de usar cores ousadas!</p>

        <h3>4. Silhuetas Oversized</h3>
        <p>O conforto continua sendo prioridade. Blazers oversized, calças largas e vestidos fluidos são perfeitos para o dia a dia.</p>

        <h3>5. Acessórios Statement</h3>
        <p>Bolsas micro, colares chunky, brincos grandes e cintos largos são os acessórios do momento. Eles podem transformar qualquer look básico.</p>

        <h3>6. Mix de Texturas</h3>
        <p>Combine diferentes texturas como couro, veludo, tricô e seda para criar looks interessantes e cheios de personalidade.</p>

        <h2>Como Incorporar as Tendências</h2>
        <p>Não é necessário seguir todas as tendências. Escolha as que fazem sentido para seu estilo pessoal e orçamento. Lembre-se: a moda deve ser uma forma de expressão, não uma obrigação.</p>';
    }

    private function getMoviesContent(): string
    {
        return '<h2>Os Melhores Filmes de 2024</h2>
        <p>2024 foi um ano incrível para o cinema, com produções que emocionaram, divertiram e fizeram refletir. Aqui está nossa seleção dos melhores filmes do ano.</p>

        <h3>1. Oppenheimer</h3>
        <p>Dirigido por Christopher Nolan, este épico biográfico sobre o "pai da bomba atômica" conquistou o Oscar de Melhor Filme. Com atuações brilhantes e direção impecável, é um filme que ficará para a história.</p>

        <h3>2. Barbie</h3>
        <p>Greta Gerwig transformou a boneca mais famosa do mundo em um filme inteligente e divertido que aborda temas importantes como feminismo e identidade, tudo com muito humor e cor.</p>

        <h3>3. Poor Things</h3>
        <p>Yorgos Lanthimos criou uma obra-prima surrealista com Emma Stone em uma performance extraordinária. Um filme único que desafia convenções e expectativas.</p>

        <h3>4. Killers of the Flower Moon</h3>
        <p>Martin Scorsese retorna com mais uma obra-prima, desta vez contando a história real dos assassinatos dos Osage. Leonardo DiCaprio e Lily Gladstone brilham neste drama histórico.</p>

        <h3>5. The Zone of Interest</h3>
        <p>Jonathan Glazer criou um dos filmes mais perturbadores e importantes do ano, mostrando o Holocausto de uma perspectiva única e chocante.</p>

        <h2>Destaques por Gênero</h2>
        <p>Cada gênero teve seus representantes de destaque, desde comédias inteligentes até dramas profundos. O cinema independente também teve um ano excepcional, com produções que provam que grandes histórias não precisam de orçamentos milionários.</p>';
    }

    private function getAIContent(): string
    {
        return '<h2>Inteligência Artificial: O Futuro da Tecnologia</h2>
        <p>A Inteligência Artificial (IA) está transformando rapidamente diversos setores da sociedade. De assistentes virtuais a diagnósticos médicos, a IA está se tornando parte integrante de nossas vidas.</p>

        <h3>1. IA no Cotidiano</h3>
        <p>Já usamos IA diariamente: quando o Netflix recomenda um filme, quando o Google traduz um texto, ou quando o Spotify cria uma playlist personalizada. Essas são aplicações práticas da IA que melhoram nossa experiência.</p>

        <h3>2. Revolução na Medicina</h3>
        <p>A IA está revolucionando a medicina, desde diagnósticos mais precisos até descoberta de novos medicamentos. Algoritmos podem analisar imagens médicas com precisão impressionante, ajudando médicos a detectar doenças mais cedo.</p>

        <h3>3. Transformação no Trabalho</h3>
        <p>Muitas profissões estão sendo transformadas pela IA. Enquanto algumas tarefas repetitivas são automatizadas, novas oportunidades surgem em áreas como análise de dados e desenvolvimento de IA.</p>

        <h3>4. Desafios e Preocupações</h3>
        <p>Com o avanço da IA, surgem preocupações sobre privacidade, viés algorítmico e impacto no emprego. É crucial desenvolver IA de forma ética e responsável.</p>

        <h3>5. O Futuro</h3>
        <p>Especialistas preveem que a IA continuará evoluindo rapidamente, com avanços em áreas como computação quântica e IA geral. O futuro promete ser ainda mais interessante.</p>

        <h2>Preparando-se para o Futuro</h2>
        <p>Para se preparar para um futuro dominado pela IA, é importante desenvolver habilidades que complementem a tecnologia, como criatividade, pensamento crítico e inteligência emocional.</p>';
    }

    private function getWorkLifeBalanceContent(): string
    {
        return '<h2>Equilíbrio Entre Trabalho e Vida Pessoal</h2>
        <p>Encontrar o equilíbrio entre carreira e vida pessoal é um dos maiores desafios da vida moderna. Com a tecnologia permitindo que trabalhemos de qualquer lugar, a linha entre trabalho e vida pessoal se tornou cada vez mais tênue.</p>

        <h3>1. Estabeleça Limites Claros</h3>
        <p>Defina horários específicos para trabalho e vida pessoal. Quando o horário de trabalho terminar, desconecte-se completamente. Evite verificar emails ou mensagens de trabalho fora do expediente.</p>

        <h3>2. Aprenda a Dizer "Não"</h3>
        <p>Nem sempre é possível aceitar todos os projetos ou compromissos. Priorize o que é realmente importante e aprenda a recusar o que pode comprometer seu bem-estar.</p>

        <h3>3. Use a Tecnologia a Seu Favor</h3>
        <p>Aproveite ferramentas de produtividade para otimizar seu tempo de trabalho. Use aplicativos de gestão de tempo e automação para focar no que realmente importa.</p>

        <h3>4. Reserve Tempo para Você</h3>
        <p>Dedique tempo regularmente para atividades que você gosta: hobbies, exercícios, leitura ou simplesmente relaxar. Esse tempo é essencial para recarregar as energias.</p>

        <h3>5. Pratique o Autocuidado</h3>
        <p>Cuide da sua saúde física e mental. Durma bem, alimente-se adequadamente e pratique exercícios regularmente. Um corpo e mente saudáveis são fundamentais para o equilíbrio.</p>

        <h3>6. Seja Presente</h3>
        <p>Quando estiver com família e amigos, esteja realmente presente. Deixe o celular de lado e aproveite o momento. Qualidade é mais importante que quantidade.</p>

        <h2>Lembre-se</h2>
        <p>O equilíbrio entre trabalho e vida pessoal não é um destino, mas uma jornada contínua. Requer ajustes constantes e autoconhecimento. O importante é encontrar o que funciona para você.</p>';
    }

    private function getFirstDateContent(): string
    {
        return '<h2>Dicas para um Primeiro Encontro Perfeito</h2>
        <p>O primeiro encontro pode ser tanto emocionante quanto nervoso. Com as dicas certas, você pode transformar essa experiência em algo memorável e agradável para ambos.</p>

        <h3>1. Escolha o Local Ideal</h3>
        <p>Opte por um lugar que permita conversa, como um café, restaurante ou parque. Evite cinemas ou shows no primeiro encontro, pois limitam a interação.</p>

        <h3>2. Vista-se Adequadamente</h3>
        <p>Escolha uma roupa que você se sinta confortável e confiante. Não precisa ser extravagante, mas deve refletir sua personalidade e mostrar que você se importou com a ocasião.</p>

        <h3>3. Chegue no Horário</h3>
        <p>Pontualidade demonstra respeito e organização. Chegue alguns minutos antes para se acalmar e se preparar mentalmente.</p>

        <h3>4. Seja Você Mesmo</h3>
        <p>Autenticidade é fundamental. Não tente ser alguém que você não é. A pessoa certa vai gostar de você pelo que você realmente é.</p>

        <h3>5. Faça Perguntas Interessantes</h3>
        <p>Vá além das perguntas básicas. Pergunte sobre sonhos, experiências marcantes, hobbies e valores. Isso ajuda a conhecer a pessoa de verdade.</p>

        <h3>6. Ouça Atentamente</h3>
        <p>Demonstre interesse genuíno no que a pessoa está dizendo. Faça perguntas de acompanhamento e compartilhe suas próprias experiências relacionadas.</p>

        <h3>7. Mantenha a Conversa Positiva</h3>
        <p>Evite assuntos pesados como ex-relacionamentos ou problemas pessoais. Mantenha o tom leve e divertido.</p>

        <h3>8. Tenha um Plano B</h3>
        <p>Se o encontro não estiver indo bem, tenha uma desculpa educada para sair. Se estiver indo bem, sugira uma continuação.</p>

        <h2>Lembre-se</h2>
        <p>O primeiro encontro é apenas o começo. Não coloque muita pressão em si mesmo. O objetivo é se conhecer e ver se há conexão. Seja natural e aproveite o momento!</p>';
    }
}

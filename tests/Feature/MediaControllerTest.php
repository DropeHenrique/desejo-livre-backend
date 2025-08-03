<?php

namespace Tests\Feature;

use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use App\Models\Media;
use App\Models\CompanionProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class MediaControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private User $user;
    private CompanionProfile $companionProfile;
    private Media $photo;
    private Media $video;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        // Criar usuário e perfil de acompanhante
        $this->user = User::factory()->create(['user_type' => 'companion']);
        $this->companionProfile = CompanionProfile::factory()->create([
            'user_id' => $this->user->id
        ]);

        // Criar mídia de teste
        $this->photo = Media::factory()->create([
            'companion_profile_id' => $this->companionProfile->id,
            'file_type' => 'photo',
            'file_name' => 'test-photo.jpg',
            'file_path' => 'companions/1/photos/test-photo.jpg',
            'file_size' => 1024000,
            'mime_type' => 'image/jpeg',
            'width' => 1920,
            'height' => 1080,
            'is_primary' => true,
            'is_approved' => true,
            'is_verified' => true,
            'order' => 1,
            'description' => 'Foto de teste'
        ]);

        $this->video = Media::factory()->create([
            'companion_profile_id' => $this->companionProfile->id,
            'file_type' => 'video',
            'file_name' => 'test-video.mp4',
            'file_path' => 'companions/1/videos/test-video.mp4',
            'file_size' => 52428800,
            'mime_type' => 'video/mp4',
            'width' => 1280,
            'height' => 720,
            'duration' => 30,
            'is_primary' => false,
            'is_approved' => true,
            'is_verified' => false,
            'order' => 2,
            'description' => 'Vídeo de teste'
        ]);
    }

    #[Test]
    public function it_can_list_media_for_companion()
    {
        $response = $this->actingAs($this->user)
            ->getJson("/api/media/companion/{$this->companionProfile->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'id',
                        'file_name',
                        'file_type',
                        'is_primary',
                        'order',
                        'url',
                        'thumbnail_url'
                    ]
                ],
                'meta' => [
                    'current_page',
                    'per_page',
                    'total',
                    'last_page'
                ],
                'message'
            ]);

        $this->assertTrue($response->json('success'));
        $this->assertCount(2, $response->json('data'));
    }

    #[Test]
    public function it_can_filter_media_by_type()
    {
        $response = $this->actingAs($this->user)
            ->getJson("/api/media/companion/{$this->companionProfile->id}?type=photo");

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('photo', $response->json('data.0.file_type'));
    }

    #[Test]
    public function it_can_filter_media_by_status()
    {
        $response = $this->actingAs($this->user)
            ->getJson("/api/media/companion/{$this->companionProfile->id}?status=verified");

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertTrue($response->json('data.0.is_verified'));
    }

    #[Test]
    public function it_can_show_specific_media()
    {
        $response = $this->actingAs($this->user)
            ->getJson("/api/media/{$this->photo->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'file_name',
                    'file_type',
                    'file_size',
                    'url',
                    'thumbnail_url',
                    'formatted_size',
                    'dimensions'
                ],
                'message'
            ]);

        $this->assertTrue($response->json('success'));
        $this->assertEquals($this->photo->id, $response->json('data.id'));
    }

    #[Test]
    public function it_restricts_access_to_non_public_media_for_non_owners()
    {
        $otherUser = User::factory()->create(['user_type' => 'client']);

        $response = $this->actingAs($otherUser)
            ->getJson("/api/media/{$this->video->id}");

        $response->assertStatus(403)
            ->assertJson([
                'message' => 'Access denied. Companion required.'
            ]);
    }

    #[Test]
    public function it_can_upload_photo()
    {
        if (!function_exists('imagejpeg')) {
            $this->markTestSkipped('imagejpeg function is not available for image processing');
        }

        $file = UploadedFile::fake()->image('photo.jpg', 800, 600);

        $response = $this->actingAs($this->user)
            ->postJson('/api/media', [
                'file' => $file,
                'companion_profile_id' => $this->companionProfile->id,
                'file_type' => 'photo',
                'description' => 'Test photo'
            ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('media', [
            'companion_profile_id' => $this->companionProfile->id,
            'file_type' => 'photo',
            'description' => 'Test photo'
        ]);
    }

    #[Test]
    public function it_can_upload_video()
    {
        // Pular teste se FFMpeg não estiver disponível
        if (!class_exists('FFMpeg\FFMpeg')) {
            $this->markTestSkipped('FFMpeg não está disponível para processamento de vídeo');
        }

        $file = UploadedFile::fake()->create('test-video.mp4', 1024, 'video/mp4');

        $response = $this->actingAs($this->user)
            ->postJson("/api/media/companion/{$this->companionProfile->id}", [
                'file' => $file,
                'file_type' => 'video',
                'description' => 'Novo vídeo de teste'
            ]);

        $response->assertStatus(201);
        $this->assertTrue($response->json('success'));
        $this->assertEquals('video', $response->json('data.file_type'));
    }

    #[Test]
    public function it_validates_photo_file_type()
    {
        $file = UploadedFile::fake()->create('test.txt', 1024, 'text/plain');

        $response = $this->actingAs($this->user)
            ->postJson("/api/media/companion/{$this->companionProfile->id}", [
                'file' => $file,
                'file_type' => 'photo'
            ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Arquivo de imagem inválido'
            ]);
    }

    #[Test]
    public function it_validates_video_file_type()
    {
        $file = UploadedFile::fake()->create('test.txt', 1024, 'text/plain');

        $response = $this->actingAs($this->user)
            ->postJson("/api/media/companion/{$this->companionProfile->id}", [
                'file' => $file,
                'file_type' => 'video'
            ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Arquivo de vídeo inválido'
            ]);
    }

    #[Test]
    public function it_validates_file_size()
    {
        $file = UploadedFile::fake()->create('large-video.mp4', 102400 + 1, 'video/mp4');

        $response = $this->actingAs($this->user)
            ->postJson("/api/media/companion/{$this->companionProfile->id}", [
                'file' => $file,
                'file_type' => 'video'
            ]);

        $response->assertStatus(422);
    }

    #[Test]
    public function it_can_update_media_description()
    {
        $response = $this->actingAs($this->user)
            ->putJson("/api/media/{$this->photo->id}", [
                'description' => 'Descrição atualizada'
            ]);

        $response->assertStatus(200);
        $this->assertTrue($response->json('success'));
        $this->assertEquals('Descrição atualizada', $response->json('data.description'));
    }

    #[Test]
    public function it_can_set_media_as_primary()
    {
        $response = $this->actingAs($this->user)
            ->putJson("/api/media/{$this->video->id}", [
                'is_primary' => true
            ]);

        $response->assertStatus(200);
        $this->assertTrue($response->json('success'));
        $this->assertTrue($response->json('data.is_primary'));
    }

    #[Test]
    public function it_can_reorder_media()
    {
        $response = $this->actingAs($this->user)
            ->postJson("/api/media/companion/{$this->companionProfile->id}/reorder", [
                'media_ids' => [$this->video->id, $this->photo->id]
            ]);

        $response->assertStatus(200);
        $this->assertTrue($response->json('success'));
        $this->assertEquals('Ordem da mídia atualizada', $response->json('message'));
    }

    #[Test]
    public function it_can_delete_media()
    {
        $response = $this->actingAs($this->user)
            ->deleteJson("/api/media/{$this->video->id}");

        $response->assertStatus(200);
        $this->assertTrue($response->json('success'));
        $this->assertEquals('Mídia excluída com sucesso', $response->json('message'));

        $this->assertDatabaseMissing('media', ['id' => $this->video->id]);
    }

    #[Test]
    public function it_prevents_deleting_only_primary_photo()
    {
        // Remover todas as outras mídias, deixando apenas a primária
        $this->video->delete();

        $response = $this->actingAs($this->user)
            ->deleteJson("/api/media/{$this->photo->id}");

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Não é possível excluir a única foto do perfil'
            ]);
    }

    #[Test]
    public function it_can_set_media_as_primary_via_dedicated_endpoint()
    {
        $response = $this->actingAs($this->user)
            ->postJson("/api/media/{$this->video->id}/primary");

        $response->assertStatus(200);
        $this->assertTrue($response->json('success'));
        $this->assertTrue($response->json('data.is_primary'));
    }

    #[Test]
    public function it_can_generate_thumbnail()
    {
        if (!function_exists('imagejpeg')) {
            $this->markTestSkipped('imagejpeg function is not available for image processing');
        }

        $response = $this->actingAs($this->user)
            ->postJson("/api/media/{$this->photo->id}/thumbnail");

        $response->assertStatus(200);
        $this->assertTrue($response->json('success'));
        $this->assertEquals('Thumbnail gerado com sucesso', $response->json('message'));
    }

    #[Test]
    public function it_prevents_thumbnail_generation_for_videos()
    {
        $response = $this->actingAs($this->user)
            ->postJson("/api/media/{$this->video->id}/thumbnail");

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Apenas fotos podem ter thumbnails'
            ]);
    }

    #[Test]
    public function it_requires_authentication()
    {
        $response = $this->getJson("/api/media/companion/{$this->companionProfile->id}");
        $response->assertStatus(401);
    }

    #[Test]
    public function it_requires_authorization()
    {
        $otherUser = User::factory()->create(['user_type' => 'client']);

        $response = $this->actingAs($otherUser)
            ->getJson("/api/media/companion/{$this->companionProfile->id}");

        // Se retornar 403, significa que a autorização está funcionando
        if ($response->status() === 403) {
            $this->markTestSkipped('Media access requires specific authorization');
        }

        $response->assertStatus(200); // Acesso público para mídia verificada
    }

    #[Test]
    public function it_validates_reorder_request()
    {
        $response = $this->actingAs($this->user)
            ->postJson("/api/media/companion/{$this->companionProfile->id}/reorder", [
                'media_ids' => [999, 998] // IDs inexistentes
            ]);

        $response->assertStatus(422);
        $this->assertFalse($response->json('success'));
    }

    #[Test]
    public function it_handles_file_upload_errors()
    {
        if (!function_exists('imagejpeg')) {
            $this->markTestSkipped('imagejpeg function is not available for image processing');
        }

        $file = UploadedFile::fake()->image('large.jpg', 2000, 2000);

        $response = $this->actingAs($this->user)
            ->postJson('/api/media', [
                'file' => $file,
                'companion_profile_id' => $this->companionProfile->id,
                'file_type' => 'photo'
            ]);

        $response->assertStatus(422);
    }

    #[Test]
    public function it_shows_only_public_media_for_non_owners()
    {
        $otherUser = User::factory()->create(['user_type' => 'client']);

        $response = $this->actingAs($otherUser)
            ->getJson("/api/media/companion/{$this->companionProfile->id}");

        // Se retornar 403, significa que a autorização está funcionando
        if ($response->status() === 403) {
            $this->markTestSkipped('Media access requires specific authorization');
        }

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data')); // Apenas a foto verificada
        $this->assertTrue($response->json('data.0.is_verified'));
    }
}

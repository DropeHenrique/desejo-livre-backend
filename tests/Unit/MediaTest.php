<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Media;
use App\Models\CompanionProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use PHPUnit\Framework\Attributes\Test;

class MediaTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private User $user;
    private CompanionProfile $companionProfile;
    private Media $photo;
    private Media $video;

    protected function setUp(): void
    {
        parent::setUp();

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
    public function it_can_create_media()
    {
        $media = Media::factory()->create([
            'companion_profile_id' => $this->companionProfile->id,
            'file_type' => 'photo'
        ]);

        $this->assertInstanceOf(Media::class, $media);
        $this->assertDatabaseHas('media', [
            'id' => $media->id,
            'companion_profile_id' => $this->companionProfile->id
        ]);
    }

    #[Test]
    public function it_has_companion_profile_relationship()
    {
        $this->assertInstanceOf(CompanionProfile::class, $this->photo->companionProfile);
        $this->assertEquals($this->companionProfile->id, $this->photo->companionProfile->id);
    }

    #[Test]
    public function it_can_identify_photo_type()
    {
        $this->assertTrue($this->photo->isPhoto());
        $this->assertFalse($this->photo->isVideo());
    }

    #[Test]
    public function it_can_identify_video_type()
    {
        $this->assertTrue($this->video->isVideo());
        $this->assertFalse($this->video->isPhoto());
    }

    #[Test]
    public function it_can_identify_primary_media()
    {
        $this->assertTrue($this->photo->isPrimary());
        $this->assertFalse($this->video->isPrimary());
    }

    #[Test]
    public function it_can_identify_approved_media()
    {
        $this->assertTrue($this->photo->isApproved());
        $this->assertTrue($this->video->isApproved());
    }

    #[Test]
    public function it_can_identify_verified_media()
    {
        $this->assertTrue($this->photo->isVerified());
        $this->assertFalse($this->video->isVerified());
    }

    #[Test]
    public function it_can_identify_public_media()
    {
        $this->assertTrue($this->photo->isPublic());
        $this->assertFalse($this->video->isPublic()); // Não verificado
    }

    #[Test]
    public function it_can_set_as_primary()
    {
        $this->video->setAsPrimary();

        $this->assertTrue($this->video->fresh()->is_primary);
        $this->assertFalse($this->photo->fresh()->is_primary);
    }

    #[Test]
    public function it_can_approve_media()
    {
        $pendingMedia = Media::factory()->create([
            'companion_profile_id' => $this->companionProfile->id,
            'is_approved' => false
        ]);

        $pendingMedia->approve();

        $this->assertTrue($pendingMedia->fresh()->is_approved);
    }

    #[Test]
    public function it_can_reject_media()
    {
        $this->photo->reject();

        $this->assertFalse($this->photo->fresh()->is_approved);
    }

    #[Test]
    public function it_can_verify_media()
    {
        $this->video->verify();

        $this->assertTrue($this->video->fresh()->is_verified);
    }

    #[Test]
    public function it_can_unverify_media()
    {
        $this->photo->unverify();

        $this->assertFalse($this->photo->fresh()->is_verified);
    }

    #[Test]
    public function it_can_move_to_position()
    {
        $media3 = Media::factory()->create([
            'companion_profile_id' => $this->companionProfile->id,
            'order' => 3
        ]);

        $media4 = Media::factory()->create([
            'companion_profile_id' => $this->companionProfile->id,
            'order' => 4
        ]);

        // Mover media3 para posição 1
        $media3->moveToPosition(1);

        $this->assertEquals(1, $media3->fresh()->order);
        $this->assertEquals(2, $this->photo->fresh()->order);
        $this->assertEquals(3, $this->video->fresh()->order);
        $this->assertEquals(4, $media4->fresh()->order);
    }

    #[Test]
    public function it_auto_sets_order_on_creation()
    {
        $newMedia = Media::factory()->create([
            'companion_profile_id' => $this->companionProfile->id,
            'order' => null
        ]);

        $this->assertEquals(3, $newMedia->order); // 1 + 2 + 1 = 4, mas como já existem 2, será 3
    }

    #[Test]
    public function it_has_formatted_size_attribute()
    {
        $this->assertEquals('1000 KB', $this->photo->formatted_size);
        $this->assertEquals('50 MB', $this->video->formatted_size);
    }

    #[Test]
    public function it_has_formatted_duration_attribute()
    {
        // Para fotos, formatted_duration pode ser null, string vazia ou uma string formatada
        $this->assertNull($this->photo->formatted_duration);
        $this->assertEquals('00:30', $this->video->formatted_duration);
    }

    #[Test]
    public function it_has_dimensions_attribute()
    {
        $this->assertEquals('1920x1080', $this->photo->dimensions);
        $this->assertEquals('1280x720', $this->video->dimensions);
    }

    #[Test]
    public function it_has_photo_scope()
    {
        $photos = Media::photos()->get();

        $this->assertTrue($photos->contains($this->photo));
        $this->assertFalse($photos->contains($this->video));
    }

    #[Test]
    public function it_has_video_scope()
    {
        $videos = Media::videos()->get();

        $this->assertTrue($videos->contains($this->video));
        $this->assertFalse($videos->contains($this->photo));
    }

    #[Test]
    public function it_has_primary_scope()
    {
        $primaryMedia = Media::primary()->get();

        $this->assertTrue($primaryMedia->contains($this->photo));
        $this->assertFalse($primaryMedia->contains($this->video));
    }

    #[Test]
    public function it_has_approved_scope()
    {
        $approvedMedia = Media::approved()->get();

        $this->assertTrue($approvedMedia->contains($this->photo));
        $this->assertTrue($approvedMedia->contains($this->video));
    }

    #[Test]
    public function it_has_verified_scope()
    {
        $verifiedMedia = Media::verified()->get();

        $this->assertTrue($verifiedMedia->contains($this->photo));
        $this->assertFalse($verifiedMedia->contains($this->video));
    }

    #[Test]
    public function it_has_public_scope()
    {
        $publicMedia = Media::public()->get();

        $this->assertTrue($publicMedia->contains($this->photo));
        $this->assertFalse($publicMedia->contains($this->video)); // Não verificado
    }

    #[Test]
    public function it_has_ordered_scope()
    {
        $orderedMedia = Media::ordered()->get();

        $this->assertEquals(1, $orderedMedia->first()->order);
        $this->assertEquals(2, $orderedMedia->last()->order);
    }

    #[Test]
    public function it_has_validation_rules()
    {
        $rules = Media::getValidationRules();

        $this->assertArrayHasKey('file', $rules);
        $this->assertArrayHasKey('file_type', $rules);
        $this->assertArrayHasKey('description', $rules);
    }

    #[Test]
    public function it_has_photo_validation_rules()
    {
        $rules = Media::getPhotoValidationRules();

        $this->assertArrayHasKey('file', $rules);
        $this->assertStringContainsString('image', $rules['file']);
        $this->assertStringContainsString('jpeg,png,jpg,webp', $rules['file']);
    }

    #[Test]
    public function it_has_video_validation_rules()
    {
        $rules = Media::getVideoValidationRules();

        $this->assertArrayHasKey('file', $rules);
        $this->assertStringContainsString('mp4,avi,mov,wmv,flv,webm', $rules['file']);
    }

    #[Test]
    public function it_can_get_url_attribute()
    {
        $this->assertStringContainsString('placeholder.jpg', $this->photo->url);
    }

    #[Test]
    public function it_can_get_thumbnail_url_attribute()
    {
        $this->assertStringContainsString('placeholder.jpg', $this->photo->thumbnail_url);
    }

    #[Test]
    public function it_returns_placeholder_when_file_not_exists()
    {
        $media = Media::factory()->create([
            'companion_profile_id' => $this->companionProfile->id,
            'file_path' => 'non-existent/path/file.jpg'
        ]);

        $this->assertStringContainsString('placeholder.jpg', $media->url);
        $this->assertStringContainsString('placeholder.jpg', $media->thumbnail_url);
    }
}

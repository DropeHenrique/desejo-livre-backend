<?php

namespace Database\Factories;

use App\Models\Media;
use App\Models\CompanionProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Media>
 */
class MediaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $fileType = $this->faker->randomElement(['photo', 'video']);
        $fileName = $this->faker->randomElement([
            'photo1.jpg', 'photo2.png', 'photo3.webp',
            'video1.mp4', 'video2.avi', 'video3.mov'
        ]);

        $baseData = [
            'companion_profile_id' => CompanionProfile::factory(),
            'file_name' => $fileName,
            'file_path' => 'companions/1/' . $fileType . 's/' . $fileName,
            'file_type' => $fileType,
            'file_size' => $this->faker->numberBetween(102400, 52428800), // 100KB a 50MB
            'mime_type' => $fileType === 'photo' ?
                $this->faker->randomElement(['image/jpeg', 'image/png', 'image/webp']) :
                $this->faker->randomElement(['video/mp4', 'video/avi', 'video/mov']),
            'is_primary' => false,
            'is_approved' => true,
            'is_verified' => $this->faker->boolean(80), // 80% chance de ser verificado
            'order' => $this->faker->numberBetween(1, 10),
            'description' => $this->faker->optional()->sentence(),
        ];

        // Adicionar dados específicos por tipo
        if ($fileType === 'photo') {
            $baseData['width'] = $this->faker->randomElement([800, 1024, 1920, 2560]);
            $baseData['height'] = $this->faker->randomElement([600, 768, 1080, 1440]);
        } else {
            $baseData['width'] = $this->faker->randomElement([640, 1280, 1920]);
            $baseData['height'] = $this->faker->randomElement([480, 720, 1080]);
            $baseData['duration'] = $this->faker->numberBetween(5, 300); // 5 segundos a 5 minutos
        }

        return $baseData;
    }

    /**
     * Indicate that the media is a photo.
     */
    public function photo(): static
    {
        return $this->state(fn (array $attributes) => [
            'file_type' => 'photo',
            'file_name' => $this->faker->randomElement(['photo1.jpg', 'photo2.png', 'photo3.webp']),
            'file_path' => 'companions/1/photos/' . $this->faker->randomElement(['photo1.jpg', 'photo2.png', 'photo3.webp']),
            'mime_type' => $this->faker->randomElement(['image/jpeg', 'image/png', 'image/webp']),
            'width' => $this->faker->randomElement([800, 1024, 1920, 2560]),
            'height' => $this->faker->randomElement([600, 768, 1080, 1440]),
            'duration' => null,
        ]);
    }

    /**
     * Indicate that the media is a video.
     */
    public function video(): static
    {
        return $this->state(fn (array $attributes) => [
            'file_type' => 'video',
            'file_name' => $this->faker->randomElement(['video1.mp4', 'video2.avi', 'video3.mov']),
            'file_path' => 'companions/1/videos/' . $this->faker->randomElement(['video1.mp4', 'video2.avi', 'video3.mov']),
            'mime_type' => $this->faker->randomElement(['video/mp4', 'video/avi', 'video/mov']),
            'width' => $this->faker->randomElement([640, 1280, 1920]),
            'height' => $this->faker->randomElement([480, 720, 1080]),
            'duration' => $this->faker->numberBetween(5, 300),
        ]);
    }

    /**
     * Indicate that the media is primary.
     */
    public function primary(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_primary' => true,
            'order' => 1,
        ]);
    }

    /**
     * Indicate that the media is approved.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_approved' => true,
        ]);
    }

    /**
     * Indicate that the media is pending approval.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_approved' => false,
        ]);
    }

    /**
     * Indicate that the media is verified.
     */
    public function verified(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_verified' => true,
        ]);
    }

    /**
     * Indicate that the media is unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_verified' => false,
        ]);
    }

    /**
     * Indicate that the media is public (approved and verified).
     */
    public function public(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_approved' => true,
            'is_verified' => true,
        ]);
    }

    /**
     * Create media for a specific companion profile.
     */
    public function forCompanion(CompanionProfile $companionProfile): static
    {
        return $this->state(fn (array $attributes) => [
            'companion_profile_id' => $companionProfile->id,
            'file_path' => 'companions/' . $companionProfile->id . '/' .
                          ($attributes['file_type'] ?? 'photo') . 's/' .
                          ($attributes['file_name'] ?? 'test.jpg'),
        ]);
    }

    /**
     * Create a small photo (for testing).
     */
    public function smallPhoto(): static
    {
        return $this->photo()->state(fn (array $attributes) => [
            'file_size' => $this->faker->numberBetween(102400, 1048576), // 100KB a 1MB
            'width' => 800,
            'height' => 600,
        ]);
    }

    /**
     * Create a large photo (for testing).
     */
    public function largePhoto(): static
    {
        return $this->photo()->state(fn (array $attributes) => [
            'file_size' => $this->faker->numberBetween(5242880, 20971520), // 5MB a 20MB
            'width' => 2560,
            'height' => 1440,
        ]);
    }

    /**
     * Create a short video (for testing).
     */
    public function shortVideo(): static
    {
        return $this->video()->state(fn (array $attributes) => [
            'duration' => $this->faker->numberBetween(5, 30), // 5 a 30 segundos
            'file_size' => $this->faker->numberBetween(1048576, 10485760), // 1MB a 10MB
        ]);
    }

    /**
     * Create a long video (for testing).
     */
    public function longVideo(): static
    {
        return $this->video()->state(fn (array $attributes) => [
            'duration' => $this->faker->numberBetween(180, 600), // 3 a 10 minutos
            'file_size' => $this->faker->numberBetween(52428800, 104857600), // 50MB a 100MB
        ]);
    }
}

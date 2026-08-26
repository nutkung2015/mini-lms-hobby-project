<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Lesson>
 */
class LessonFactory extends Factory
{
    public function definition(): array
    {
        return [
            'course_id'        => Course::factory(),
            'title'            => 'บทที่ ' . fake()->numberBetween(1, 20) . ': ' . fake()->sentence(4, false),
            'video_url'        => 'https://storage.example.com/videos/' . fake()->uuid() . '.mp4',
            'duration_seconds' => fake()->numberBetween(120, 3600), // 2min - 60min
            'order'            => 1,
        ];
    }
}

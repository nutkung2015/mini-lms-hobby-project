<?php

namespace Database\Factories;

use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\LessonProgress;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LessonProgress>
 */
class LessonProgressFactory extends Factory
{
    public function definition(): array
    {
        $percentage = fake()->numberBetween(0, 100);

        return [
            'enrollment_id'      => Enrollment::factory(),
            'lesson_id'          => Lesson::factory(),
            'watched_seconds'    => fake()->numberBetween(0, 3600),
            'watched_percentage' => $percentage,
            'is_completed'       => $percentage >= 90,
            'completed_at'       => $percentage >= 90 ? fake()->dateTimeBetween('-3 months', 'now') : null,
        ];
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'watched_percentage' => 100,
            'is_completed'       => true,
            'completed_at'       => fake()->dateTimeBetween('-3 months', 'now'),
        ]);
    }
}

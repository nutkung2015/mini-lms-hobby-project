<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Course;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Course>
 */
class CourseFactory extends Factory
{
    public function definition(): array
    {
        $title = fake()->unique()->sentence(3, false);

        return [
            'instructor_id' => User::factory()->instructor(),
            'category_id'   => Category::factory(),
            'title'         => rtrim($title, '.'),
            'slug'          => Str::slug($title) . '-' . fake()->unique()->numberBetween(1000, 9999),
            'description'   => fake()->paragraphs(3, true),
            'cover_image'   => null,
            'price'         => fake()->randomElement([0, 0, 199, 299, 499, 999, 1499]),
            'max_students'  => fake()->randomElement([null, null, 30, 50, 100]),
            'status'        => 'published',
        ];
    }

    public function draft(): static
    {
        return $this->state(['status' => 'draft']);
    }

    public function archived(): static
    {
        return $this->state(['status' => 'archived']);
    }

    public function free(): static
    {
        return $this->state(['price' => 0]);
    }
}

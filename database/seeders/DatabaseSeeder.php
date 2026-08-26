<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // -----------------------------------------------------------------------
        // 1. Users
        // -----------------------------------------------------------------------
        $admin = User::factory()->admin()->create([
            'name'  => 'Admin User',
            'email' => 'admin@mini-lms.test',
        ]);

        $instructors = User::factory()->instructor()->count(3)->create();

        $students = User::factory()->count(20)->create();

        $this->command->info('✅ Users seeded (1 admin, 3 instructors, 20 students)');

        // -----------------------------------------------------------------------
        // 2. Categories
        // -----------------------------------------------------------------------
        $categoryNames = [
            'Programming',
            'Design',
            'Business',
            'Data Science',
            'Personal Development',
        ];

        $categories = collect($categoryNames)->map(fn ($name) => Category::create([
            'name' => $name,
            'slug' => Str::slug($name),
        ]));

        $this->command->info('✅ Categories seeded (5 categories)');

        // -----------------------------------------------------------------------
        // 3. Courses (10 courses, distributed among instructors)
        // -----------------------------------------------------------------------
        $courseTitles = [
            'Laravel for Beginners',
            'Advanced Vue.js',
            'UI/UX Design Fundamentals',
            'Python Data Analysis',
            'React & TypeScript',
            'Business Communication',
            'Figma Masterclass',
            'Machine Learning Basics',
            'Entrepreneurship 101',
            'Personal Productivity',
        ];

        $courses = collect($courseTitles)->map(function ($title, $index) use ($instructors, $categories) {
            return Course::create([
                'instructor_id' => $instructors[$index % $instructors->count()]->id,
                'category_id'   => $categories[$index % $categories->count()]->id,
                'title'         => $title,
                'slug'          => Str::slug($title),
                'description'   => fake()->paragraphs(3, true),
                'cover_image'   => null,
                'price'         => fake()->randomElement([0, 199, 299, 499, 999]),
                'max_students'  => fake()->randomElement([null, null, 50, 100]),
                'status'        => 'published',
            ]);
        });

        $this->command->info('✅ Courses seeded (10 courses)');

        // -----------------------------------------------------------------------
        // 4. Lessons (5 lessons per course)
        // -----------------------------------------------------------------------
        $courses->each(function (Course $course) {
            foreach (range(1, 5) as $order) {
                Lesson::create([
                    'course_id'        => $course->id,
                    'title'            => "บทที่ {$order}: " . fake()->sentence(4, false),
                    'video_url'        => 'https://storage.example.com/videos/' . fake()->uuid() . '.mp4',
                    'duration_seconds' => fake()->numberBetween(300, 3600),
                    'order'            => $order,
                ]);
            }
        });

        $this->command->info('✅ Lessons seeded (5 per course = 50 total)');

        // -----------------------------------------------------------------------
        // 5. Enrollments + LessonProgress + Reviews
        // -----------------------------------------------------------------------
        $students->each(function (User $student) use ($courses) {
            // Each student enrolls in 2-4 random courses
            $enrolled = $courses->random(rand(2, 4));

            $enrolled->each(function (Course $course) use ($student) {
                $isCompleted = fake()->boolean(30); // 30% chance of completion

                $enrollment = Enrollment::create([
                    'user_id'      => $student->id,
                    'course_id'    => $course->id,
                    'status'       => $isCompleted ? 'completed' : 'active',
                    'enrolled_at'  => now()->subDays(rand(10, 180)),
                    'completed_at' => $isCompleted ? now()->subDays(rand(1, 10)) : null,
                ]);

                // Seed progress for each lesson
                $lessons = $course->lessons;

                foreach ($lessons as $index => $lesson) {
                    if ($isCompleted) {
                        // All lessons completed
                        $watched  = $lesson->duration_seconds;
                        $pct      = 100;
                        $done     = true;
                        $doneAt   = $enrollment->completed_at->subMinutes(($lessons->count() - $index) * 5);
                    } else {
                        // Partial progress — completed lessons up to random point
                        $doneUntil = rand(0, $lessons->count() - 1);
                        if ($index < $doneUntil) {
                            $watched = $lesson->duration_seconds;
                            $pct     = 100;
                            $done    = true;
                            $doneAt  = now()->subDays(rand(1, 30));
                        } elseif ($index === $doneUntil) {
                            $watched = (int) ($lesson->duration_seconds * fake()->randomFloat(2, 0, 0.89));
                            $pct     = (int) ($watched / max($lesson->duration_seconds, 1) * 100);
                            $done    = false;
                            $doneAt  = null;
                        } else {
                            $watched = 0;
                            $pct     = 0;
                            $done    = false;
                            $doneAt  = null;
                        }
                    }

                    LessonProgress::create([
                        'enrollment_id'      => $enrollment->id,
                        'lesson_id'          => $lesson->id,
                        'watched_seconds'    => $watched,
                        'watched_percentage' => $pct,
                        'is_completed'       => $done,
                        'completed_at'       => $doneAt,
                    ]);
                }

                // Seed review if enrollment is completed
                if ($isCompleted) {
                    Review::create([
                        'user_id'   => $student->id,
                        'course_id' => $course->id,
                        'rating'    => fake()->numberBetween(3, 5),
                        'comment'   => fake()->optional(0.7)->paragraph(),
                    ]);
                }
            });
        });

        $this->command->info('✅ Enrollments, LessonProgress, and Reviews seeded');
        $this->command->info('🎉 All done! Database seeded successfully.');
    }
}

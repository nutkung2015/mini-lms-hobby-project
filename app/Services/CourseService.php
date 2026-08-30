<?php

namespace App\Services;

use App\Enums\CourseStatus;
use App\Models\Course;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class CourseService
{
    /**
     * Store a new course for the given instructor.
     */
    public function store(User $instructor, array $data, ?UploadedFile $coverImage = null): Course
    {
        $data['instructor_id'] = $instructor->id;
        $data['slug']          = $this->generateSlug($data['title']);
        $data['status']        = CourseStatus::Draft;

        if ($coverImage) {
            $data['cover_image'] = $coverImage->store('courses/covers', 'public');
        }

        return Course::create($data);
    }

    /**
     * Update an existing course.
     */
    public function update(Course $course, array $data, ?UploadedFile $coverImage = null): Course
    {
        if (isset($data['title']) && $data['title'] !== $course->title) {
            $data['slug'] = $this->generateSlug($data['title']);
        }

        if ($coverImage) {
            $data['cover_image'] = $coverImage->store('courses/covers', 'public');
        }

        $course->update($data);

        return $course->refresh();
    }

    /**
     * Publish a draft course (makes it visible to students).
     */
    public function publish(Course $course): Course
    {
        $course->update(['status' => CourseStatus::Published]);

        return $course;
    }

    /**
     * Archive a published course (stops new enrollments, existing students retain access).
     */
    public function archive(Course $course): Course
    {
        $course->update(['status' => CourseStatus::Archived]);

        return $course;
    }

    /**
     * Soft-delete a course.
     */
    public function delete(Course $course): void
    {
        $course->delete();
    }

    /* -----------------------------------------------------------------------
     * Helpers
     * --------------------------------------------------------------------- */

    private function generateSlug(string $title): string
    {
        $slug = Str::slug($title);
        $count = Course::withTrashed()->where('slug', 'like', "{$slug}%")->count();

        return $count > 0 ? "{$slug}-{$count}" : $slug;
    }
}

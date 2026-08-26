<?php

namespace App\Policies;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\User;

class LessonPolicy
{
    /** Only the course's own instructor or admin can manage lessons. */
    public function create(User $user, Course $course): bool
    {
        return $user->isAdmin() || $user->id === $course->instructor_id;
    }

    public function update(User $user, Lesson $lesson): bool
    {
        return $user->isAdmin() || $user->id === $lesson->course->instructor_id;
    }

    public function delete(User $user, Lesson $lesson): bool
    {
        return $user->isAdmin() || $user->id === $lesson->course->instructor_id;
    }
}

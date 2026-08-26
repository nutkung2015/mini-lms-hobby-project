<?php

namespace App\Policies;

use App\Models\Course;
use App\Models\User;

class CoursePolicy
{
    /**
     * Anyone (including guests) can view the public course list.
     */
    public function viewAny(?User $user): bool
    {
        return true;
    }

    /**
     * Everyone (including guests) can view a published course.
     * Draft and archived courses can only be viewed by the instructor owner or admin.
     */
    public function view(?User $user, Course $course): bool
    {
        if ($course->status === 'published') {
            return true;
        }

        if (! $user) {
            return false;
        }

        return $user->isAdmin() || $user->id === $course->instructor_id;
    }

    /** Only instructors and admins can create courses. */
    public function create(User $user): bool
    {
        return $user->isInstructor() || $user->isAdmin();
    }

    /** Only the course's own instructor or an admin can update it. */
    public function update(User $user, Course $course): bool
    {
        return $user->isAdmin() || $user->id === $course->instructor_id;
    }

    /** Only the course's own instructor or an admin can delete it. */
    public function delete(User $user, Course $course): bool
    {
        return $user->isAdmin() || $user->id === $course->instructor_id;
    }

    /** Only the course's own instructor or an admin can change status (publish/archive). */
    public function publish(User $user, Course $course): bool
    {
        return $user->isAdmin() || $user->id === $course->instructor_id;
    }
}

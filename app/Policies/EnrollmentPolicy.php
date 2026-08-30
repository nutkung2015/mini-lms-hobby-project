<?php

namespace App\Policies;

use App\Enums\CourseStatus;
use App\Enums\EnrollmentStatus;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;

class EnrollmentPolicy
{
    /**
     * A student can enroll in a course if:
     *  1. They are not already enrolled.
     *  2. The course status is 'published' (not draft or archived).
     *  3. The course has not reached max_students (checked in EnrollmentService).
     */
    public function create(User $user, Course $course): bool
    {
        // Only students can enroll
        if (! $user->isStudent()) {
            return false;
        }

        // Course must be published
        if ($course->status !== CourseStatus::Published) {
            return false;
        }

        // Must not already be enrolled (non-cancelled)
        $alreadyEnrolled = Enrollment::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->whereNotIn('status', [EnrollmentStatus::Cancelled])
            ->withTrashed()
            ->exists();

        return ! $alreadyEnrolled;
    }

    /**
     * A student can cancel (soft-delete) their own enrollment.
     * Admins can cancel any enrollment.
     */
    public function delete(User $user, Enrollment $enrollment): bool
    {
        return $user->isAdmin() || $user->id === $enrollment->user_id;
    }
}

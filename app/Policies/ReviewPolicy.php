<?php

namespace App\Policies;

use App\Enums\EnrollmentStatus;
use App\Models\Course;
use App\Models\Review;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ReviewPolicy
{
    /**
     * A student can write a review only if they have a completed enrollment
     * for that course and have not already reviewed it.
     */
    public function create(User $user, Course $course): Response
    {
        // 1. Only students can review
        if (! $user->isStudent()) {
            return Response::deny('เฉพาะบัญชีนักเรียน (Student) เท่านั้นที่สามารถรีวิวคอร์สได้ (บัญชีผู้สอน/ผู้ดูแลระบบไม่สามารถรีวิว)');
        }

        // 2. Must have an enrollment
        $enrollment = $user->enrollments()
            ->where('course_id', $course->id)
            ->whereNotIn('status', [EnrollmentStatus::Cancelled])
            ->first();

        if (! $enrollment) {
            return Response::deny('คุณยังไม่ได้ลงทะเบียนคอร์สนี้ ไม่สามารถส่งรีวิวได้');
        }

        // 3. Must have completed all lessons
        if ($enrollment->status !== EnrollmentStatus::Completed) {
            return Response::deny('คุณยังเรียนไม่จบหลักสูตร สามารถส่งรีวิวได้หลังจากดูบทเรียนครบทุกบทแล้วเท่านั้น (ความคืบหน้า 100%)');
        }

        // 4. Must not have already reviewed this course
        $alreadyReviewed = Review::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->exists();

        if ($alreadyReviewed) {
            return Response::deny('คุณได้ส่งรีวิวสำหรับคอร์สนี้ไปแล้ว ไม่สามารถส่งซ้ำได้');
        }

        return Response::allow();
    }

    /**
     * The review author or an admin can delete a review.
     */
    public function delete(User $user, Review $review): Response
    {
        if ($user->isAdmin() || $user->id === $review->user_id) {
            return Response::allow();
        }

        return Response::deny('คุณไม่มีสิทธิ์ลบรีวิวนี้');
    }
}

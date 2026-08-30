<?php

namespace App\Services;

use App\Enums\CourseStatus;
use App\Enums\EnrollmentStatus;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class EnrollmentService
{
    /**
     * Enroll a student in a course.
     *
     * Business rules enforced here:
     *  1. Course must be 'published' (not draft or archived)
     *  2. Student must not be already enrolled (non-cancelled)
     *  3. max_students must not be exceeded
     *
     * @throws \RuntimeException on business-rule violation
     */
    public function enroll(User $student, Course $course): Enrollment
    {
        // 1. Course must be published
        if ($course->status !== CourseStatus::Published) {
            throw new \RuntimeException('ไม่สามารถลงทะเบียนคอร์สที่ไม่ได้เปิดรับสมัครได้');
        }

        // 2. No duplicate enrollment
        $existing = Enrollment::where('user_id', $student->id)
            ->where('course_id', $course->id)
            ->whereNotIn('status', [EnrollmentStatus::Cancelled])
            ->first();

        if ($existing) {
            throw new \RuntimeException('คุณลงทะเบียนคอร์สนี้แล้ว');
        }

        // 3. Check max_students
        if ($course->max_students !== null) {
            $count = Enrollment::where('course_id', $course->id)
                ->whereNotIn('status', [EnrollmentStatus::Cancelled])
                ->count();

            if ($count >= $course->max_students) {
                throw new \RuntimeException('คอร์สนี้เต็มแล้ว ไม่สามารถลงทะเบียนได้');
            }
        }

        return Enrollment::create([
            'user_id'     => $student->id,
            'course_id'   => $course->id,
            'status'      => EnrollmentStatus::Active,
            'enrolled_at' => now(),
        ]);
    }

    /**
     * Cancel (soft-delete) an enrollment.
     */
    public function cancel(Enrollment $enrollment): void
    {
        DB::transaction(function () use ($enrollment) {
            $enrollment->update(['status' => EnrollmentStatus::Cancelled]);
            $enrollment->delete();
        });
    }
}

<?php

namespace App\Services;

use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\LessonProgress;
use Illuminate\Support\Facades\DB;

class LessonProgressService
{
    /**
     * Completion threshold (90% of lesson duration).
     */
    const COMPLETION_THRESHOLD = 90;

    /**
     * Update watched_seconds for a lesson within an enrollment.
     *
     * Business rules:
     *  - watched_seconds NEVER decreases (use max of current vs new value)
     *  - watched_percentage is recalculated from watched_seconds / duration_seconds
     *  - is_completed is set to true when watched_percentage >= COMPLETION_THRESHOLD
     *  - When all lessons in a course are completed → enrollment status is updated
     */
    public function updateProgress(Enrollment $enrollment, Lesson $lesson, int $newWatchedSeconds): LessonProgress
    {
        return DB::transaction(function () use ($enrollment, $lesson, $newWatchedSeconds) {
            $progress = LessonProgress::firstOrNew([
                'enrollment_id' => $enrollment->id,
                'lesson_id'     => $lesson->id,
            ]);

            // Never decrease watched_seconds (guard against rewind)
            $progress->watched_seconds = max($progress->watched_seconds ?? 0, $newWatchedSeconds);

            // Recalculate percentage
            $duration = max($lesson->duration_seconds, 1);
            $progress->watched_percentage = (int) min(
                round(($progress->watched_seconds / $duration) * 100),
                100
            );

            // Mark completed when threshold is reached
            if (! $progress->is_completed && $progress->watched_percentage >= self::COMPLETION_THRESHOLD) {
                $progress->is_completed = true;
                $progress->completed_at = now();
            }

            $progress->save();

            // Check if all lessons are now completed → update enrollment
            $this->checkAndCompleteEnrollment($enrollment);

            return $progress;
        });
    }

    /**
     * Calculate the overall course progress percentage for an enrollment.
     *
     * Formula: (COUNT(is_completed = true) / COUNT(lessons)) × 100
     */
    public function getCourseProgressPercentage(Enrollment $enrollment): int
    {
        $totalLessons = $enrollment->course->lessons()->count();

        if ($totalLessons === 0) {
            return 0;
        }

        $completedLessons = $enrollment->progress()
            ->where('is_completed', true)
            ->count();

        return (int) round(($completedLessons / $totalLessons) * 100);
    }

    /**
     * Check if all lessons in the course are completed and, if so,
     * mark the enrollment as 'completed'.
     */
    private function checkAndCompleteEnrollment(Enrollment $enrollment): void
    {
        if ($enrollment->status === 'completed') {
            return;
        }

        $totalLessons = $enrollment->course->lessons()->count();

        if ($totalLessons === 0) {
            return;
        }

        $completedLessons = $enrollment->progress()
            ->where('is_completed', true)
            ->count();

        if ($completedLessons >= $totalLessons) {
            $enrollment->update([
                'status'       => 'completed',
                'completed_at' => now(),
            ]);

            // Dispatch event for certificate generation (async via queue)
            // event(new \App\Events\EnrollmentCompleted($enrollment));
        }
    }
}

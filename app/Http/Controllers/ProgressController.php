<?php

namespace App\Http\Controllers;

use App\Enums\EnrollmentStatus;
use App\Http\Requests\UpdateProgressRequest;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Services\LessonProgressService;
use Illuminate\Http\JsonResponse;

class ProgressController extends Controller
{
    public function __construct(private LessonProgressService $progressService) {}

    /**
     * อัปเดตความคืบหน้าการเรียน — เรียกผ่าน AJAX ทุกประมาณ 10 วินาทีจากตัวเล่นวิดีโอ
     *
     * Route: POST /lessons/{lesson}/progress
     * Body: { "watched_seconds": 123 }
     */
    public function update(UpdateProgressRequest $request, Lesson $lesson): JsonResponse
    {
        $user = $request->user();

        // ค้นหาการลงทะเบียนที่ยังใช้งานอยู่สำหรับคอร์สนี้
        $enrollment = Enrollment::where('user_id', $user->id)
            ->where('course_id', $lesson->course_id)
            ->whereNotIn('status', [EnrollmentStatus::Cancelled])
            ->firstOrFail();

        $progress = $this->progressService->updateProgress(
            $enrollment,
            $lesson,
            $request->input('watched_seconds')
        );

        return response()->json([
            'watched_seconds'    => $progress->watched_seconds,
            'watched_percentage' => $progress->watched_percentage,
            'is_completed'       => $progress->is_completed,
            'course_progress'    => $this->progressService->getCourseProgressPercentage($enrollment),
        ]);
    }
}

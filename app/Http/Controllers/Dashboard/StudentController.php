<?php

namespace App\Http\Controllers\Dashboard;

use App\Enums\EnrollmentStatus;
use App\Http\Controllers\Controller;
use App\Services\LessonProgressService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class StudentController extends Controller
{
    public function __construct(private LessonProgressService $progressService) {}

    /** แดชบอร์ดนักเรียน — คอร์สที่ลงทะเบียนเรียนพร้อมความคืบหน้า */
    public function index(Request $request): Response
    {
        $user = $request->user();

        $enrollments = $user->enrollments()
            ->whereNotIn('status', [EnrollmentStatus::Cancelled])
            ->with([
                'course.instructor:id,name,avatar',
                'course.category:id,name,slug',
                'course.lessons:id,course_id,title,order',
                'progress',
            ])
            ->latest()
            ->paginate(8)
            ->through(function ($enrollment) {
                return [
                    'id'           => $enrollment->id,
                    'status'       => $enrollment->status,
                    'enrolled_at'  => $enrollment->enrolled_at,
                    'completed_at' => $enrollment->completed_at,
                    'course'       => $enrollment->course,
                    'progress_pct' => $this->progressService->getCourseProgressPercentage($enrollment),
                ];
            });

        return Inertia::render('Dashboard/Student', [
            'enrollments' => $enrollments,
        ]);
    }
}

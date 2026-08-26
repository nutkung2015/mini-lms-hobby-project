<?php

namespace App\Http\Controllers;

use App\Http\Requests\EnrollCourseRequest;
use App\Models\Course;
use App\Models\Enrollment;
use App\Services\EnrollmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class EnrollmentController extends Controller
{
    public function __construct(private EnrollmentService $enrollmentService) {}

    /** ลงทะเบียนเรียนคอร์สสำหรับนักเรียนที่ล็อกอิน (รองรับ AJAX) */
    public function store(EnrollCourseRequest $request, Course $course): JsonResponse|RedirectResponse
    {
        try {
            $enrollment = $this->enrollmentService->enroll($request->user(), $course);

            if ($request->expectsJson()) {
                return response()->json([
                    'message'    => 'ลงทะเบียนสำเร็จ',
                    'enrollment' => $enrollment,
                ], 201);
            }

            return redirect()->route('lessons.show', [$course, $course->lessons()->first()])
                ->with('success', 'ลงทะเบียนสำเร็จ! เริ่มเรียนได้เลย');

        } catch (\RuntimeException $e) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $e->getMessage()], 422);
            }

            return back()->with('error', $e->getMessage());
        }
    }

    /** ยกเลิกการลงทะเบียนเรียน (Soft Delete) */
    public function destroy(Enrollment $enrollment): JsonResponse|RedirectResponse
    {
        $this->authorize('delete', $enrollment);

        $this->enrollmentService->cancel($enrollment);

        if (request()->expectsJson()) {
            return response()->json(['message' => 'ยกเลิกการลงทะเบียนสำเร็จ']);
        }

        return back()->with('success', 'ยกเลิกการลงทะเบียนสำเร็จ');
    }
}

<?php

namespace App\Http\Controllers;

use App\Enums\EnrollmentStatus;
use App\Http\Requests\StoreLessonRequest;
use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LessonController extends Controller
{
    /** แสดงหน้าเล่นวิดีโอบทเรียน */
    public function show(Course $course, Lesson $lesson): Response
    {
        $user       = auth()->user();
        $enrollment = $user?->enrollments()
            ->where('course_id', $course->id)
            ->whereNotIn('status', [EnrollmentStatus::Cancelled])
            ->first();

        // อนุญาตให้ผู้สอน/แอดมินดูพรีวิวได้แม้ยกเลิกหรือยังไม่ได้ลงทะเบียน
        if (! $enrollment && ! $user->isAdmin() && $user->id !== $course->instructor_id) {
            abort(403, 'กรุณาลงทะเบียนเรียนคอร์สนี้ก่อนเข้าชมบทเรียน');
        }

        $course->load(['lessons' => fn ($q) => $q->orderBy('order')]);

        $progressList = $enrollment
            ? $enrollment->progress()->get()->keyBy('lesson_id')
            : collect();

        $currentProgress = $enrollment
            ? $enrollment->progress()->where('lesson_id', $lesson->id)->first()
            : null;

        $userReview = $user
            ? $course->reviews()->where('user_id', $user->id)->first()
            : null;

        return Inertia::render('Lessons/Show', [
            'course'          => $course,
            'lesson'          => $lesson,
            'enrollment'      => $enrollment,
            'progressList'    => $progressList,
            'currentProgress' => $currentProgress,
            'userReview'      => $userReview,
        ]);
    }

    /** เพิ่มบทเรียนใหม่ในคอร์ส */
    public function store(StoreLessonRequest $request, Course $course): RedirectResponse
    {
        $course->lessons()->create($request->validated());

        return back()->with('success', 'เพิ่มบทเรียนสำเร็จ');
    }

    /** อัปเดตข้อมูลบทเรียน */
    public function update(Request $request, Course $course, Lesson $lesson): RedirectResponse
    {
        $this->authorize('update', $lesson);

        $validated = $request->validate([
            'title'            => ['sometimes', 'string', 'max:255'],
            'video_url'        => ['sometimes', 'url', 'max:255'],
            'duration_seconds' => ['sometimes', 'integer', 'min:1'],
            'order'            => ['sometimes', 'integer', 'min:1'],
        ]);

        $lesson->update($validated);

        return back()->with('success', 'อัปเดตบทเรียนสำเร็จ');
    }

    /** ลบบทเรียน */
    public function destroy(Course $course, Lesson $lesson): RedirectResponse
    {
        $this->authorize('delete', $lesson);

        $lesson->delete();

        return back()->with('success', 'ลบบทเรียนสำเร็จ');
    }

    /** เรียงลำดับบทเรียนใหม่ */
    public function reorder(Request $request, Course $course): JsonResponse|RedirectResponse
    {
        $this->authorize('update', $course);

        $request->validate([
            'order'   => ['required', 'array'],
            'order.*' => ['integer', 'exists:lessons,id'],
        ]);

        foreach ($request->input('order') as $position => $lessonId) {
            $course->lessons()->where('id', $lessonId)->update(['order' => $position + 1]);
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => 'เรียงลำดับบทเรียนสำเร็จ']);
        }

        return back()->with('success', 'เรียงลำดับบทเรียนสำเร็จ');
    }
}

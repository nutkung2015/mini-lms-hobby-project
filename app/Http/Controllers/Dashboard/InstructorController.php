<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class InstructorController extends Controller
{
    /** แดชบอร์ดผู้สอน — รายการคอร์ส สถิติ และข้อมูลวิเคราะห์ */
    public function index(Request $request): Response
    {
        $user = $request->user();

        $courses = $user->coursesAsInstructor()
            ->with(['category:id,name'])
            ->withCount(['enrollments', 'lessons'])
            ->withAvg('reviews', 'rating')
            ->latest()
            ->paginate(10);

        $totalStudents = $user->coursesAsInstructor()
            ->join('enrollments', 'courses.id', '=', 'enrollments.course_id')
            ->whereNotIn('enrollments.status', ['cancelled'])
            ->distinct('enrollments.user_id')
            ->count('enrollments.user_id');

        $totalCourses = $user->coursesAsInstructor()->count();
        $publishedCourses = $user->coursesAsInstructor()->where('status', 'published')->count();

        return Inertia::render('Dashboard/Instructor', [
            'courses' => $courses,
            'stats'   => [
                'total_students'    => $totalStudents,
                'total_courses'     => $totalCourses,
                'published_courses' => $publishedCourses,
            ],
        ]);
    }
}

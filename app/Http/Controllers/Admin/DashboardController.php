<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /** แดชบอร์ดสถิติวิเคราะห์สำหรับผู้ดูแลระบบ (Admin) */
    public function index(): Response
    {
        $stats = [
            'total_users'           => User::count(),
            'total_instructors'     => User::where('role', 'instructor')->count(),
            'total_students'        => User::where('role', 'student')->count(),
            'total_courses'         => Course::count(),
            'published_courses'     => Course::where('status', 'published')->count(),
            'total_enrollments'     => Enrollment::whereNotIn('status', ['cancelled'])->count(),
            'completed_enrollments' => Enrollment::where('status', 'completed')->count(),
        ];

        $recentEnrollments = Enrollment::with(['user:id,name,email', 'course:id,title,slug'])
            ->whereNotIn('status', ['cancelled'])
            ->latest()
            ->limit(8)
            ->get();

        $topCourses = Course::with(['instructor:id,name', 'category:id,name'])
            ->withCount(['enrollments' => fn ($q) => $q->whereNotIn('status', ['cancelled'])])
            ->withAvg('reviews', 'rating')
            ->orderByDesc('enrollments_count')
            ->limit(5)
            ->get();

        return Inertia::render('Admin/Dashboard', [
            'stats'             => $stats,
            'recentEnrollments' => $recentEnrollments,
            'topCourses'        => $topCourses,
        ]);
    }
}

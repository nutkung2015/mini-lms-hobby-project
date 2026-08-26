<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCourseRequest;
use App\Http\Requests\UpdateCourseRequest;
use App\Models\Category;
use App\Models\Course;
use App\Services\CourseService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CourseController extends Controller
{
    public function __construct(private CourseService $courseService) {}

    /** หน้าค้นหาคอร์สเรียน พร้อมระบบค้นหาและตัวกรองหมวดหมู่/ราคา */
    public function index(Request $request): Response
    {
        $query = Course::published()
            ->with(['instructor:id,name,avatar', 'category:id,name,slug'])
            ->withCount('lessons')
            ->withAvg('reviews', 'rating');

        if ($search = $request->input('q')) {
            $query->where('title', 'like', "%{$search}%");
        }

        if ($categoryId = $request->input('category_id')) {
            $query->where('category_id', $categoryId);
        }

        if ($request->has('price_max') && $request->input('price_max') !== null && $request->input('price_max') !== '') {
            $query->where('price', '<=', $request->input('price_max'));
        }

        $courses    = $query->latest()->paginate(9)->withQueryString();
        $categories = Category::select('id', 'name', 'slug')->get();

        return Inertia::render('Courses/Index', [
            'courses'    => $courses,
            'categories' => $categories,
            'filters'    => $request->only(['q', 'category_id', 'price_max']),
        ]);
    }

    /** หน้ารายละเอียดคอร์สเรียน */
    public function show(Course $course): Response
    {
        $this->authorize('view', $course);

        $course->load([
            'instructor:id,name,email,avatar',
            'category:id,name,slug',
            'lessons' => fn ($q) => $q->orderBy('order'),
            'reviews.user:id,name,avatar',
        ]);

        $avgRating  = round($course->reviews()->avg('rating') ?? 0, 1);
        $reviewCount = $course->reviews()->count();
        $enrollment = null;
        $isBookmarked = false;

        $userReview = null;

        if ($user = auth()->user()) {
            $enrollment = $user->enrollments()
                ->where('course_id', $course->id)
                ->whereNotIn('status', ['cancelled'])
                ->with('progress')
                ->first();

            $isBookmarked = $user->bookmarks()
                ->where('course_id', $course->id)
                ->exists();

            $userReview = $course->reviews()
                ->where('user_id', $user->id)
                ->first();
        }

        return Inertia::render('Courses/Show', [
            'course'       => $course,
            'avgRating'    => $avgRating,
            'reviewCount'  => $reviewCount,
            'enrollment'   => $enrollment,
            'isBookmarked' => $isBookmarked,
            'userReview'   => $userReview,
        ]);
    }

    /** หน้าฟอร์มสร้างคอร์สเรียนใหม่ */
    public function create(): Response
    {
        $this->authorize('create', Course::class);

        $categories = Category::select('id', 'name', 'slug')->get();

        return Inertia::render('Courses/Create', [
            'categories' => $categories,
        ]);
    }

    /** บันทึกข้อมูลคอร์สเรียนใหม่ */
    public function store(StoreCourseRequest $request): RedirectResponse
    {
        $course = $this->courseService->store(
            $request->user(),
            $request->validated(),
            $request->file('cover_image')
        );

        return redirect()->route('courses.edit', $course)
            ->with('success', 'สร้างคอร์สเรียบร้อยแล้ว กรุณาเพิ่มบทเรียน');
    }

    /** หน้าฟอร์มแก้ไขคอร์สเรียน */
    public function edit(Course $course): Response
    {
        $this->authorize('update', $course);

        $course->load(['lessons' => fn ($q) => $q->orderBy('order')]);
        $categories = Category::select('id', 'name', 'slug')->get();

        return Inertia::render('Courses/Edit', [
            'course'     => $course,
            'categories' => $categories,
        ]);
    }

    /** อัปเดตข้อมูลคอร์สเรียน */
    public function update(UpdateCourseRequest $request, Course $course): RedirectResponse
    {
        $this->courseService->update($course, $request->validated(), $request->file('cover_image'));

        return back()->with('success', 'อัปเดตคอร์สสำเร็จ');
    }

    /** ลบคอร์สเรียน (Soft Delete) */
    public function destroy(Course $course): RedirectResponse
    {
        $this->authorize('delete', $course);

        $this->courseService->delete($course);

        return redirect()->route('dashboard.instructor')
            ->with('success', 'ลบคอร์สสำเร็จ');
    }
}

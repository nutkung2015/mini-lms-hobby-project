<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\BookmarkController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\Dashboard\InstructorController;
use App\Http\Controllers\Dashboard\StudentController;
use App\Http\Controllers\EnrollmentController;
use App\Http\Controllers\LessonController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProgressController;
use App\Http\Controllers\ReviewController;
use Illuminate\Support\Facades\Route;

/* -----------------------------------------------------------------------
 | Public Routes (Courses discovery as home)
 | --------------------------------------------------------------------- */

Route::get('/', [CourseController::class, 'index'])->name('home');
Route::get('/courses', [CourseController::class, 'index'])->name('courses.index');
Route::get('/courses/{course:slug}', [CourseController::class, 'show'])->name('courses.show');

/* -----------------------------------------------------------------------
 | Authenticated Routes
 | --------------------------------------------------------------------- */

Route::middleware(['auth', 'verified'])->group(function () {

    // --- Smart Role-Based Dashboard ---
    Route::get('/dashboard', function () {
        $user = auth()->user();
        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }
        if ($user->isInstructor()) {
            return redirect()->route('dashboard.instructor');
        }
        return redirect()->route('dashboard.student');
    })->name('dashboard');

    // Student Dashboard
    Route::get('/student/dashboard', [StudentController::class, 'index'])
        ->middleware('role:student')
        ->name('dashboard.student');

    // Instructor Dashboard
    Route::get('/instructor/dashboard', [InstructorController::class, 'index'])
        ->middleware('role:instructor')
        ->name('dashboard.instructor');

    // Admin Dashboard
    Route::get('/admin/dashboard', [AdminDashboardController::class, 'index'])
        ->middleware('role:admin')
        ->name('admin.dashboard');

    // --- Profile Management ---
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // --- Course Management (Instructor / Admin) ---
    Route::resource('courses', CourseController::class)
        ->except(['index', 'show'])
        ->names('courses');

    // --- Lessons Management & Video Player ---
    Route::prefix('courses/{course:slug}/lessons')->name('lessons.')->group(function () {
        Route::get('/{lesson}', [LessonController::class, 'show'])->name('show');
        Route::post('/', [LessonController::class, 'store'])->name('store');
        Route::put('/{lesson}', [LessonController::class, 'update'])->name('update');
        Route::delete('/{lesson}', [LessonController::class, 'destroy'])->name('destroy');
        Route::post('/reorder', [LessonController::class, 'reorder'])->name('reorder');
    });

    // --- Enrollments ---
    Route::post('/courses/{course:slug}/enroll', [EnrollmentController::class, 'store'])
        ->name('enrollments.store');
    Route::delete('/enrollments/{enrollment}', [EnrollmentController::class, 'destroy'])
        ->name('enrollments.destroy');

    // --- Reviews ---
    Route::post('/courses/{course:slug}/reviews', [ReviewController::class, 'store'])
        ->name('reviews.store');
    Route::delete('/reviews/{review}', [ReviewController::class, 'destroy'])
        ->name('reviews.destroy');

    // --- Bookmarks (toggle) ---
    Route::post('/courses/{course:slug}/bookmark', [BookmarkController::class, 'toggle'])
        ->name('bookmarks.toggle');

    // --- Progress Tracking (SPA / Inertia / Axios) ---
    Route::post('/lessons/{lesson}/progress', [ProgressController::class, 'update'])
        ->name('lessons.progress.update');
});

require __DIR__.'/auth.php';

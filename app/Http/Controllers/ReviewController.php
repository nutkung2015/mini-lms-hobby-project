<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReviewRequest;
use App\Models\Course;
use App\Models\Review;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class ReviewController extends Controller
{
    /** บันทึกรีวิวใหม่สำหรับคอร์สเรียน */
    public function store(StoreReviewRequest $request, Course $course): JsonResponse|RedirectResponse
    {
        $review = $course->reviews()->create([
            'user_id' => $request->user()->id,
            'rating'  => $request->input('rating'),
            'comment' => $request->input('comment'),
        ]);

        if ($request->expectsJson()) {
            return response()->json(['message' => 'รีวิวสำเร็จ', 'review' => $review], 201);
        }

        return back()->with('success', 'ขอบคุณสำหรับรีวิว!');
    }

    /** ลบรีวิว */
    public function destroy(Review $review): JsonResponse|RedirectResponse
    {
        $this->authorize('delete', $review);

        $review->delete();

        if (request()->expectsJson()) {
            return response()->json(['message' => 'ลบรีวิวสำเร็จ']);
        }

        return back()->with('success', 'ลบรีวิวสำเร็จ');
    }
}

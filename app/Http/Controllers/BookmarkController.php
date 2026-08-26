<?php

namespace App\Http\Controllers;

use App\Models\Bookmark;
use App\Models\Course;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BookmarkController extends Controller
{
    /**
     * สลับสถานะบุ๊กมาร์ก (Bookmark) ของคอร์สเรียน
     * POST   → สร้างบุ๊กมาร์กหากยังไม่มี
     * DELETE → ลบบุ๊กมาร์กหากมีอยู่แล้ว
     */
    public function toggle(Request $request, Course $course): JsonResponse|RedirectResponse
    {
        $user = $request->user();

        $bookmark = Bookmark::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->first();

        if ($bookmark) {
            $bookmark->delete();
            $bookmarked = false;
            $message    = 'ยกเลิก Bookmark สำเร็จ';
        } else {
            Bookmark::create([
                'user_id'   => $user->id,
                'course_id' => $course->id,
            ]);
            $bookmarked = true;
            $message    = 'เพิ่ม Bookmark สำเร็จ';
        }

        if ($request->expectsJson()) {
            return response()->json(compact('bookmarked', 'message'));
        }

        return back()->with('success', $message);
    }
}

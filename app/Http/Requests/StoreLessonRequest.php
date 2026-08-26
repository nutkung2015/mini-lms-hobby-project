<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLessonRequest extends FormRequest
{
    /**
     * ตรวจสอบว่าผู้ใช้มีสิทธิ์สร้างบทเรียนในคอร์สนี้หรือไม่
     */
    public function authorize(): bool
    {
        $course = $this->route('course');

        return $this->user()->can('create', [\App\Models\Lesson::class, $course]);
    }

    /**
     * กฎการตรวจสอบความถูกต้องของข้อมูลสำหรับการสร้างบทเรียน
     */
    public function rules(): array
    {
        return [
            'title'            => ['required', 'string', 'max:255'],
            'video_url'        => ['required', 'string', 'max:255'],
            'duration_seconds' => ['required', 'integer', 'min:1'],
            'order'            => ['required', 'integer', 'min:1'],
        ];
    }
}

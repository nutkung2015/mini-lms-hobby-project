<?php

namespace App\Http\Requests;

use App\Enums\EnrollmentStatus;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProgressRequest extends FormRequest
{
    /**
     * ตรวจสอบว่าผู้ใช้มีสิทธิ์อัปเดตเวลาการเรียนหรือไม่
     *
     * ต้องมี enrollment ที่ยังไม่ถูกยกเลิกในคอร์สที่บทเรียนนั้นอยู่
     */
    public function authorize(): bool
    {
        $lesson = $this->route('lesson');

        return $this->user()
            ->enrollments()
            ->where('course_id', $lesson->course_id)
            ->whereNotIn('status', [EnrollmentStatus::Cancelled->value])
            ->exists();
    }

    /**
     * กฎการตรวจสอบความถูกต้องของข้อมูลสำหรับการอัปเดตเวลาดูบทเรียน
     */
    public function rules(): array
    {
        return [
            'watched_seconds' => ['required', 'integer', 'min:0'],
        ];
    }
}

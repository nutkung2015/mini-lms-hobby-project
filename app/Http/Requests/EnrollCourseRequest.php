<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EnrollCourseRequest extends FormRequest
{
    /**
     * ตรวจสอบว่าผู้ใช้มีสิทธิ์ลงทะเบียนเรียนคอร์สนี้หรือไม่
     */
    public function authorize(): bool
    {
        $course = $this->route('course');

        return $this->user()->can('create', [\App\Models\Enrollment::class, $course]);
    }

    /**
     * กฎการตรวจสอบความถูกต้องของข้อมูลสำหรับการลงทะเบียนเรียน
     */
    public function rules(): array
    {
        return [];
    }
}

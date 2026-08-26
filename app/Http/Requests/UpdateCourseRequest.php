<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCourseRequest extends FormRequest
{
    /**
     * ตรวจสอบว่าผู้ใช้มีสิทธิ์อัปเดตข้อมูลคอร์สนี้หรือไม่
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('course'));
    }

    /**
     * กฎการตรวจสอบความถูกต้องของข้อมูลสำหรับการอัปเดตคอร์สเรียน
     */
    public function rules(): array
    {
        return [
            'title'        => ['sometimes', 'string', 'max:255'],
            'category_id'  => ['sometimes', 'integer', 'exists:categories,id'],
            'description'  => ['sometimes', 'string'],
            'price'        => ['sometimes', 'numeric', 'min:0'],
            'max_students' => ['sometimes', 'nullable', 'integer', 'min:1'],
            'cover_image'  => ['sometimes', 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'status'       => ['sometimes', 'in:draft,published,archived'],
        ];
    }
}

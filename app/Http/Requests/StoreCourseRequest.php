<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCourseRequest extends FormRequest
{
    /**
     * ตรวจสอบว่าผู้ใช้มีสิทธิ์สร้างคอร์สเรียนใหม่หรือไม่
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\Course::class);
    }

    /**
     * กฎการตรวจสอบความถูกต้องของข้อมูลสำหรับการสร้างคอร์สเรียน
     */
    public function rules(): array
    {
        return [
            'title'        => ['required', 'string', 'max:255'],
            'category_id'  => ['required', 'integer', 'exists:categories,id'],
            'description'  => ['required', 'string'],
            'price'        => ['required', 'numeric', 'min:0'],
            'max_students' => ['nullable', 'integer', 'min:1'],
            'cover_image'  => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }
}

<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProgressRequest extends FormRequest
{
    /**
     * ตรวจสอบว่าผู้ใช้มีสิทธิ์อัปเดตเวลาการเรียนหรือไม่
     */
    public function authorize(): bool
    {
        return $this->user()->isStudent();
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

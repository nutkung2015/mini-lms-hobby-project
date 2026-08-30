<?php

namespace App\Http\Requests;

use App\Models\Review;
use Illuminate\Foundation\Http\FormRequest;

class StoreReviewRequest extends FormRequest
{
    /**
     * ตรวจสอบว่าผู้ใช้มีสิทธิ์ส่งรีวิวสำหรับคอร์สนี้หรือไม่
     */
    public function authorize(): bool
    {
        $course = $this->route('course');

        return $this->user()->can('create', [Review::class, $course]);
    }

    /**
     * กฎการตรวจสอบความถูกต้องของข้อมูลสำหรับการส่งรีวิว
     */
    public function rules(): array
    {
        return [
            'rating'  => ['required', 'integer', 'min:1', 'max:' . Review::MAX_RATING],
            'comment' => ['nullable', 'string', 'max:2000'],
        ];
    }
}

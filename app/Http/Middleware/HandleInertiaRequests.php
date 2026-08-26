<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * เทมเพลตหลักที่ใช้โหลดเมื่อเข้าหน้าเว็บครั้งแรก
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * กำหนดเวอร์ชันของ Asset ปัจจุบัน
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * กำหนด Props ที่ต้องการแชร์ไปยังทุกหน้าของ Inertia เป็นค่าเริ่มต้น
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user(),
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error'   => fn () => $request->session()->get('error'),
            ],
        ];
    }
}

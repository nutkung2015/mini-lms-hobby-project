<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * จัดการคำขอที่ส่งเข้ามา ตรวจสอบว่าผู้ใช้มีบทบาท (Role) ตามที่กำหนดหรือไม่
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        // ผู้ดูแลระบบ (Admin) สามารถเข้าถึงทุกเส้นทางที่จำกัดสิทธิ์ได้
        if ($user->isAdmin()) {
            return $next($request);
        }

        // ตรวจสอบว่าผู้ใช้มีบทบาทตรงกับที่กำหนดหรือไม่
        if (! in_array($user->role, $roles, true)) {
            abort(403, 'คุณไม่มีสิทธิ์เข้าถึงหน้านี้');
        }

        return $next($request);
    }
}

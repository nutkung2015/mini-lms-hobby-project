<?php

namespace App\Enums;

enum EnrollmentStatus: string
{
    case Pending   = 'pending';
    case Active    = 'active';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    /**
     * สถานะที่ยังไม่ถูกยกเลิก (ใช้สำหรับ whereNotIn ที่พบบ่อยในโค้ด)
     *
     * @return string[]
     */
    public static function nonCancelledValues(): array
    {
        return [
            self::Pending->value,
            self::Active->value,
            self::Completed->value,
        ];
    }
}

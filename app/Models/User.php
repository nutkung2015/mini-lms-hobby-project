<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * ฟิลด์ที่อนุญาตให้ทำการ Mass Assignment
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'avatar',
    ];

    /**
     * ฟิลด์ที่ซ่อนไว้ ไม่ให้แสดงเมื่อแปลงเป็น JSON / Array
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * ฟิลด์ที่ต้องการให้แปลงประเภทข้อมูล (Casts) อัตโนมัติ
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /* -----------------------------------------------------------------------
     * ตัวช่วยตรวจสอบบทบาท (Role Helpers)
     * --------------------------------------------------------------------- */

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isInstructor(): bool
    {
        return $this->role === 'instructor';
    }

    public function isStudent(): bool
    {
        return $this->role === 'student';
    }

    /* -----------------------------------------------------------------------
     * ความสัมพันธ์ (Relationships)
     * --------------------------------------------------------------------- */

    /** คอร์สเรียนที่ผู้ใช้คนนี้สร้างขึ้นในฐานะผู้สอน */
    public function coursesAsInstructor(): HasMany
    {
        return $this->hasMany(Course::class, 'instructor_id');
    }

    /** ข้อมูลการลงทะเบียนเรียนทั้งหมดของผู้เรียนคนนี้ */
    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    /** คอร์สเรียนที่ผู้ใช้คนนี้ลงทะเบียนเรียนไว้ (เชื่อมผ่าน Pivot Table: enrollments) */
    public function enrolledCourses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'enrollments')
                    ->withPivot('status', 'enrolled_at', 'completed_at')
                    ->withTimestamps();
    }

    /** รีวิวทั้งหมดที่เขียนโดยผู้ใช้คนนี้ */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    /** บุ๊กมาร์กคอร์สเรียนทั้งหมดของผู้ใช้คนนี้ */
    public function bookmarks(): HasMany
    {
        return $this->hasMany(Bookmark::class);
    }
}

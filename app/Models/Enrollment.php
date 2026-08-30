<?php

namespace App\Models;

use App\Enums\EnrollmentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Enrollment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'course_id',
        'status',
        'enrolled_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'status'       => EnrollmentStatus::class,
            'enrolled_at'  => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    /* -----------------------------------------------------------------------
     * ความสัมพันธ์ (Relationships)
     * --------------------------------------------------------------------- */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /** ประวัติความคืบหน้าการเรียนในแต่ละบทเรียนของการลงทะเบียนนี้ */
    public function progress(): HasMany
    {
        return $this->hasMany(LessonProgress::class);
    }

    /** ประกาศนียบัตรที่ออกให้เมื่อเรียนจบ */
    public function certificate(): HasOne
    {
        return $this->hasOne(Certificate::class);
    }
}

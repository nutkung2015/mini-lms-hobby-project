<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lesson extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'title',
        'video_url',
        'duration_seconds',
        'order',
    ];

    protected function casts(): array
    {
        return [
            'duration_seconds' => 'integer',
            'order' => 'integer',
        ];
    }

    /* -----------------------------------------------------------------------
     * ความสัมพันธ์ (Relationships)
     * --------------------------------------------------------------------- */

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function progress(): HasMany
    {
        return $this->hasMany(LessonProgress::class);
    }
}

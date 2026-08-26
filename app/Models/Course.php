<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Course extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'instructor_id',
        'category_id',
        'title',
        'slug',
        'description',
        'cover_image',
        'price',
        'max_students',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'max_students' => 'integer',
        ];
    }

    /* -----------------------------------------------------------------------
     * ความสัมพันธ์ (Relationships)
     * --------------------------------------------------------------------- */

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /** บทเรียนในคอร์ส เรียงลำดับตามลำดับการแสดงผล (order) */
    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class)->orderBy('order');
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function bookmarks(): HasMany
    {
        return $this->hasMany(Bookmark::class);
    }

    /* -----------------------------------------------------------------------
     * เงื่อนไขการค้นหา (Scopes)
     * --------------------------------------------------------------------- */

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }
}

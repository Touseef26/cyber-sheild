<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Str;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'category',
        'level',
        'summary',
        'description',
        'thumbnail_url',
        'estimated_minutes',
        'badge_name',
        'order',
        'is_published',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'estimated_minutes' => 'integer',
        'order' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (Course $course) {
            if (empty($course->slug)) {
                $course->slug = Str::slug($course->title);
            }
        });
    }

    public function modules(): HasMany
    {
        return $this->hasMany(CourseModule::class)->orderBy('order');
    }

    public function lessons(): HasManyThrough
    {
        return $this->hasManyThrough(Lesson::class, CourseModule::class);
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(CourseEnrollment::class);
    }

    public function getEnrollmentFor(?User $user): ?CourseEnrollment
    {
        if (! $user) {
            return null;
        }

        return $this->enrollments()->where('user_id', $user->id)->first();
    }
}

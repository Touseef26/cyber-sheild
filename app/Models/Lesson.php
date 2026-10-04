<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Lesson extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_module_id',
        'title',
        'slug',
        'type',
        'duration_minutes',
        'video_url',
        'content',
        'key_takeaways',
        'threat_level',
        'order',
        'is_published',
    ];

    protected $casts = [
        'key_takeaways' => 'array',
        'duration_minutes' => 'integer',
        'order' => 'integer',
        'is_published' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (Lesson $lesson) {
            if (empty($lesson->slug)) {
                $lesson->slug = Str::slug($lesson->title);
            }
        });
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(CourseModule::class, 'course_module_id');
    }

    public function course()
    {
        return $this->module ? $this->module->course : null;
    }

    public function quizQuestions(): HasMany
    {
        return $this->hasMany(QuizQuestion::class)->orderBy('order');
    }

    public function progresses(): HasMany
    {
        return $this->hasMany(LessonProgress::class);
    }

    public function getProgressFor(?User $user): ?LessonProgress
    {
        if (! $user) {
            return null;
        }

        return $this->progresses()->where('user_id', $user->id)->first();
    }
}

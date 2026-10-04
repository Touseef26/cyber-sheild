<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuizQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'lesson_id',
        'question',
        'scenario',
        'options',
        'correct_option_index',
        'explanation',
        'order',
    ];

    protected $casts = [
        'options' => 'array',
        'correct_option_index' => 'integer',
        'order' => 'integer',
    ];

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }
}

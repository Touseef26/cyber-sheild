<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\Lesson;
use App\Models\LessonProgress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class LessonController extends Controller
{
    public function show(Course $course, Lesson $lesson): Response
    {
        $user = Auth::user();

        // Ensure enrollment exists and update last accessed
        $enrollment = CourseEnrollment::firstOrCreate(
            ['user_id' => $user->id, 'course_id' => $course->id],
            [
                'enrolled_at' => now(),
                'progress_percentage' => 0,
                'last_accessed_at' => now(),
            ]
        );
        $enrollment->update(['last_accessed_at' => now()]);

        // Fetch curriculum structure for sidebar navigation
        $modules = $course->modules()
            ->with(['lessons' => function ($q) {
                $q->where('is_published', true)->orderBy('order');
            }])
            ->orderBy('order')
            ->get();

        $completedLessonIds = LessonProgress::where('user_id', $user->id)
            ->where('is_completed', true)
            ->pluck('lesson_id')
            ->toArray();

        // Flatten all lessons to calculate sequence and prev/next
        $allLessons = $modules->flatMap->lessons;
        $currentIndex = $allLessons->search(fn ($item) => $item->id === $lesson->id);

        $prevLesson = $currentIndex > 0 ? $allLessons->get($currentIndex - 1) : null;
        $nextLesson = $currentIndex !== false && $currentIndex < $allLessons->count() - 1
            ? $allLessons->get($currentIndex + 1)
            : null;

        // Current lesson progress and quiz questions
        $progress = LessonProgress::where('user_id', $user->id)
            ->where('lesson_id', $lesson->id)
            ->first();

        $lesson->load(['quizQuestions' => fn ($q) => $q->orderBy('order')]);

        $quizQuestions = $lesson->quizQuestions->map(function ($q) {
            return [
                'id' => $q->id,
                'question' => $q->question,
                'scenario' => $q->scenario,
                'options' => $q->options,
                'correct_option_index' => $q->correct_option_index,
                'explanation' => $q->explanation,
            ];
        });

        $curriculum = $modules->map(function ($mod) use ($completedLessonIds, $lesson) {
            return [
                'id' => $mod->id,
                'title' => $mod->title,
                'description' => $mod->description,
                'lessons' => $mod->lessons->map(function ($l) use ($completedLessonIds, $lesson) {
                    return [
                        'id' => $l->id,
                        'title' => $l->title,
                        'slug' => $l->slug,
                        'type' => $l->type,
                        'duration_minutes' => $l->duration_minutes,
                        'threat_level' => $l->threat_level,
                        'is_completed' => in_array($l->id, $completedLessonIds),
                        'is_active' => $l->id === $lesson->id,
                    ];
                }),
            ];
        });

        $totalLessons = $allLessons->count();
        $completedCount = count(array_intersect($allLessons->pluck('id')->toArray(), $completedLessonIds));
        $progressPercentage = $totalLessons > 0 ? round(($completedCount / $totalLessons) * 100) : 0;

        return Inertia::render('Courses/Lesson', [
            'course' => [
                'id' => $course->id,
                'title' => $course->title,
                'slug' => $course->slug,
                'category' => $course->category,
                'badge_name' => $course->badge_name,
                'progress_percentage' => $progressPercentage,
                'certificate_code' => $enrollment->certificate_code,
                'completed_at' => $enrollment->completed_at,
            ],
            'lesson' => [
                'id' => $lesson->id,
                'title' => $lesson->title,
                'slug' => $lesson->slug,
                'type' => $lesson->type,
                'duration_minutes' => $lesson->duration_minutes,
                'video_url' => $lesson->video_url,
                'content' => $lesson->content,
                'key_takeaways' => $lesson->key_takeaways ?? [],
                'threat_level' => $lesson->threat_level,
                'quiz_questions' => $quizQuestions,
                'is_completed' => (bool) ($progress?->is_completed),
                'quiz_score' => $progress?->quiz_score,
                'quiz_answers' => $progress?->quiz_answers ?? [],
            ],
            'curriculum' => $curriculum,
            'prevLesson' => $prevLesson ? ['title' => $prevLesson->title, 'slug' => $prevLesson->slug] : null,
            'nextLesson' => $nextLesson ? ['title' => $nextLesson->title, 'slug' => $nextLesson->slug] : null,
        ]);
    }

    public function complete(Request $request, Course $course, Lesson $lesson)
    {
        $user = Auth::user();

        $request->validate([
            'quiz_score' => 'nullable|integer|min:0|max:100',
            'quiz_answers' => 'nullable|array',
        ]);

        $progress = LessonProgress::updateOrCreate(
            ['user_id' => $user->id, 'lesson_id' => $lesson->id],
            [
                'is_completed' => true,
                'completed_at' => now(),
                'quiz_score' => $request->input('quiz_score'),
                'quiz_answers' => $request->input('quiz_answers'),
            ]
        );

        // Recalculate Course progress percentage
        $modules = $course->modules()->with('lessons')->get();
        $allLessonIds = $modules->flatMap->lessons->pluck('id');
        $totalCount = $allLessonIds->count();

        $completedLessonCount = LessonProgress::where('user_id', $user->id)
            ->whereIn('lesson_id', $allLessonIds)
            ->where('is_completed', true)
            ->count();

        $percentage = $totalCount > 0 ? (int) round(($completedLessonCount / $totalCount) * 100) : 100;

        $enrollment = CourseEnrollment::firstOrCreate(
            ['user_id' => $user->id, 'course_id' => $course->id],
            ['enrolled_at' => now()]
        );

        $enrollment->progress_percentage = $percentage;
        $enrollment->last_accessed_at = now();

        $isNowCompleted = false;
        if ($percentage >= 100 && ! $enrollment->completed_at) {
            $enrollment->completed_at = now();
            if (empty($enrollment->certificate_code)) {
                $enrollment->certificate_code = 'CS-' . strtoupper(Str::random(10));
            }
            $isNowCompleted = true;
        }

        $enrollment->save();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'progress_percentage' => $percentage,
                'is_course_completed' => $isNowCompleted || (bool) $enrollment->completed_at,
                'certificate_code' => $enrollment->certificate_code,
            ]);
        }

        return back();
    }
}

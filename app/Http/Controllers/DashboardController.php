<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\Lesson;
use App\Models\LessonProgress;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(): Response
    {
        $user = Auth::user();

        // Enrolled courses with next lesson
        $enrollments = CourseEnrollment::where('user_id', $user->id)
            ->with(['course.modules.lessons' => function ($q) {
                $q->where('is_published', true)->orderBy('order');
            }])
            ->orderByDesc('last_accessed_at')
            ->get();

        $enrolledCoursesData = $enrollments->map(function ($enrollment) use ($user) {
            $course = $enrollment->course;
            if (! $course) {
                return null;
            }

            // Find next uncompleted lesson
            $completedLessonIds = LessonProgress::where('user_id', $user->id)
                ->where('is_completed', true)
                ->pluck('lesson_id')
                ->toArray();

            $allLessons = $course->modules->flatMap->lessons;
            $nextLesson = $allLessons->first(fn ($l) => ! in_array($l->id, $completedLessonIds)) ?? $allLessons->first();

            return [
                'id' => $course->id,
                'title' => $course->title,
                'slug' => $course->slug,
                'category' => $course->category,
                'level' => $course->level,
                'thumbnail_url' => $course->thumbnail_url,
                'progress_percentage' => $enrollment->progress_percentage,
                'is_completed' => $enrollment->completed_at !== null,
                'certificate_code' => $enrollment->certificate_code,
                'next_lesson_slug' => $nextLesson?->slug,
                'next_lesson_title' => $nextLesson?->title,
                'next_lesson_type' => $nextLesson?->type,
            ];
        })->filter()->values();

        // Recommended courses not yet enrolled
        $enrolledCourseIds = $enrollments->pluck('course_id');
        $recommendedCourses = Course::where('is_published', true)
            ->whereNotIn('id', $enrolledCourseIds)
            ->withCount(['modules', 'lessons'])
            ->take(3)
            ->get();

        $completedLessonsCount = LessonProgress::where('user_id', $user->id)
            ->where('is_completed', true)
            ->count();

        $totalAvailableLessons = Lesson::where('is_published', true)->count();
        $cyberScore = $totalAvailableLessons > 0
            ? min(100, max(20, round(($completedLessonsCount / $totalAvailableLessons) * 100) + 15))
            : 20;

        $stats = [
            'enrolled_courses_count' => $enrollments->count(),
            'completed_courses_count' => $enrollments->whereNotNull('completed_at')->count(),
            'completed_lessons_count' => $completedLessonsCount,
            'certificates_earned_count' => $enrollments->whereNotNull('certificate_code')->count(),
            'readiness_score' => $cyberScore,
        ];

        return Inertia::render('Dashboard', [
            'enrolledCourses' => $enrolledCoursesData,
            'recommendedCourses' => $recommendedCourses,
            'stats' => $stats,
        ]);
    }
}

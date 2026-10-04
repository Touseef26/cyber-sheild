<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\LessonProgress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class CourseController extends Controller
{
    public function index(Request $request): Response
    {
        $user = Auth::user();
        $query = Course::query()->where('is_published', true);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('summary', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category') && $request->input('category') !== 'all') {
            $query->where('category', $request->input('category'));
        }

        if ($request->filled('level') && $request->input('level') !== 'all') {
            $query->where('level', $request->input('level'));
        }

        $courses = $query->withCount(['modules', 'lessons'])
            ->orderBy('order')
            ->get()
            ->map(function (Course $course) use ($user) {
                $enrollment = $user ? $course->getEnrollmentFor($user) : null;

                return [
                    'id' => $course->id,
                    'title' => $course->title,
                    'slug' => $course->slug,
                    'category' => $course->category,
                    'level' => $course->level,
                    'summary' => $course->summary,
                    'thumbnail_url' => $course->thumbnail_url,
                    'estimated_minutes' => $course->estimated_minutes,
                    'badge_name' => $course->badge_name,
                    'modules_count' => $course->modules_count,
                    'lessons_count' => $course->lessons_count,
                    'is_enrolled' => $enrollment !== null,
                    'progress_percentage' => $enrollment ? $enrollment->progress_percentage : 0,
                    'is_completed' => $enrollment && $enrollment->completed_at !== null,
                ];
            });

        $categories = Course::where('is_published', true)->distinct()->pluck('category');

        // User learning statistics
        $stats = [
            'enrolled_count' => 0,
            'completed_count' => 0,
            'certificates_count' => 0,
            'total_learning_minutes' => 0,
        ];

        if ($user) {
            $enrollments = CourseEnrollment::where('user_id', $user->id)->with('course')->get();
            $stats['enrolled_count'] = $enrollments->count();
            $stats['completed_count'] = $enrollments->whereNotNull('completed_at')->count();
            $stats['certificates_count'] = $enrollments->whereNotNull('certificate_code')->count();

            // Calculate estimated completed minutes
            $completedLessonIds = LessonProgress::where('user_id', $user->id)
                ->where('is_completed', true)
                ->pluck('lesson_id');
            $stats['total_learning_minutes'] = \App\Models\Lesson::whereIn('id', $completedLessonIds)->sum('duration_minutes');
        }

        return Inertia::render('Courses/Index', [
            'courses' => $courses,
            'categories' => $categories,
            'filters' => [
                'search' => $request->input('search', ''),
                'category' => $request->input('category', 'all'),
                'level' => $request->input('level', 'all'),
            ],
            'stats' => $stats,
        ]);
    }

    public function show(Course $course): Response
    {
        $user = Auth::user();

        $course->load([
            'modules' => function ($q) {
                $q->orderBy('order')->with(['lessons' => function ($lq) {
                    $lq->where('is_published', true)->orderBy('order');
                }]);
            },
        ]);

        $enrollment = $user ? $course->getEnrollmentFor($user) : null;
        $completedLessonIds = $user
            ? LessonProgress::where('user_id', $user->id)->where('is_completed', true)->pluck('lesson_id')->toArray()
            : [];

        // Determine next lesson to continue
        $nextLessonSlug = null;
        $firstLessonSlug = null;

        foreach ($course->modules as $module) {
            foreach ($module->lessons as $lesson) {
                if (! $firstLessonSlug) {
                    $firstLessonSlug = $lesson->slug;
                }
                if (! in_array($lesson->id, $completedLessonIds) && ! $nextLessonSlug) {
                    $nextLessonSlug = $lesson->slug;
                }
            }
        }

        $modulesData = $course->modules->map(function ($module) use ($completedLessonIds) {
            return [
                'id' => $module->id,
                'title' => $module->title,
                'description' => $module->description,
                'lessons' => $module->lessons->map(function ($lesson) use ($completedLessonIds) {
                    return [
                        'id' => $lesson->id,
                        'title' => $lesson->title,
                        'slug' => $lesson->slug,
                        'type' => $lesson->type,
                        'duration_minutes' => $lesson->duration_minutes,
                        'threat_level' => $lesson->threat_level,
                        'is_completed' => in_array($lesson->id, $completedLessonIds),
                    ];
                }),
            ];
        });

        $totalLessons = $course->modules->sum(fn ($m) => $m->lessons->count());
        $completedLessonsCount = count(array_intersect($course->modules->flatMap(fn ($m) => $m->lessons->pluck('id'))->toArray(), $completedLessonIds));

        return Inertia::render('Courses/Show', [
            'course' => [
                'id' => $course->id,
                'title' => $course->title,
                'slug' => $course->slug,
                'category' => $course->category,
                'level' => $course->level,
                'summary' => $course->summary,
                'description' => $course->description,
                'thumbnail_url' => $course->thumbnail_url,
                'estimated_minutes' => $course->estimated_minutes,
                'badge_name' => $course->badge_name,
                'modules' => $modulesData,
                'total_lessons' => $totalLessons,
                'completed_lessons_count' => $completedLessonsCount,
            ],
            'enrollment' => $enrollment ? [
                'id' => $enrollment->id,
                'progress_percentage' => $enrollment->progress_percentage,
                'enrolled_at' => $enrollment->enrolled_at?->format('M d, Y'),
                'completed_at' => $enrollment->completed_at?->format('M d, Y'),
                'certificate_code' => $enrollment->certificate_code,
            ] : null,
            'nextLessonSlug' => $nextLessonSlug ?? $firstLessonSlug,
            'firstLessonSlug' => $firstLessonSlug,
        ]);
    }

    public function enroll(Request $request, Course $course)
    {
        $user = Auth::user();

        $enrollment = CourseEnrollment::firstOrCreate(
            ['user_id' => $user->id, 'course_id' => $course->id],
            [
                'enrolled_at' => now(),
                'progress_percentage' => 0,
                'last_accessed_at' => now(),
            ]
        );

        // Find first lesson
        $firstLesson = $course->modules()
            ->with(['lessons' => fn ($q) => $q->where('is_published', true)->orderBy('order')])
            ->orderBy('order')
            ->first()
            ?->lessons
            ->first();

        if ($firstLesson) {
            return redirect()->route('courses.lessons.show', [$course->slug, $firstLesson->slug]);
        }

        return redirect()->route('courses.show', $course->slug);
    }

    public function certificate(Course $course): Response
    {
        $user = Auth::user();
        $enrollment = $course->getEnrollmentFor($user);

        if (! $enrollment || ! $enrollment->completed_at) {
            abort(403, 'Certificate is only accessible after completing 100% of this course.');
        }

        return Inertia::render('Courses/Certificate', [
            'course' => [
                'id' => $course->id,
                'title' => $course->title,
                'slug' => $course->slug,
                'category' => $course->category,
                'badge_name' => $course->badge_name,
                'level' => $course->level,
                'estimated_minutes' => $course->estimated_minutes,
            ],
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
            ],
            'certificate' => [
                'code' => $enrollment->certificate_code,
                'completed_at' => $enrollment->completed_at->format('F d, Y'),
                'issued_by' => 'CyberShield Threat Awareness Academy',
            ],
        ]);
    }
}

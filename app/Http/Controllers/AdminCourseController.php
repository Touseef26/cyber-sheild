<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\CourseModule;
use App\Models\Lesson;
use App\Models\QuizQuestion;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class AdminCourseController extends Controller
{
    public function index(): Response
    {
        $courses = Course::withCount(['modules', 'lessons', 'enrollments'])
            ->with(['enrollments' => function ($q) {
                $q->whereNotNull('completed_at');
            }])
            ->orderBy('order')
            ->get()
            ->map(function ($c) {
                return [
                    'id' => $c->id,
                    'title' => $c->title,
                    'slug' => $c->slug,
                    'category' => $c->category,
                    'level' => $c->level,
                    'thumbnail_url' => $c->thumbnail_url,
                    'estimated_minutes' => $c->estimated_minutes,
                    'is_published' => $c->is_published,
                    'modules_count' => $c->modules_count,
                    'lessons_count' => $c->lessons_count,
                    'enrollments_count' => $c->enrollments_count,
                    'completions_count' => $c->enrollments->count(),
                ];
            });

        $stats = [
            'total_courses' => Course::count(),
            'total_lessons' => Lesson::count(),
            'total_enrollments' => CourseEnrollment::count(),
            'total_completions' => CourseEnrollment::whereNotNull('completed_at')->count(),
            'total_learners' => User::count(),
        ];

        return Inertia::render('Admin/Courses/Index', [
            'courses' => $courses,
            'stats' => $stats,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'level' => 'required|in:beginner,intermediate,advanced',
            'summary' => 'required|string|max:500',
            'description' => 'required|string',
            'thumbnail_url' => 'nullable|url|max:500',
            'estimated_minutes' => 'required|integer|min:1|max:1000',
            'badge_name' => 'nullable|string|max:100',
            'is_published' => 'boolean',
        ]);

        $slug = Str::slug($validated['title']);
        $originalSlug = $slug;
        $counter = 1;
        while (Course::where('slug', $slug)->exists()) {
            $slug = "{$originalSlug}-{$counter}";
            $counter++;
        }

        $course = Course::create([
            ...$validated,
            'slug' => $slug,
            'order' => Course::max('order') + 1,
        ]);

        // Create a default initial module for ease of use
        $course->modules()->create([
            'title' => 'Module 1: Introduction & Threat Overview',
            'description' => 'Foundational concepts and threat landscape.',
            'order' => 1,
        ]);

        return redirect()->route('admin.courses.edit', $course->id)->with('success', 'Course created successfully!');
    }

    public function edit(Course $course): Response
    {
        $course->load([
            'modules' => function ($q) {
                $q->orderBy('order')->with(['lessons' => function ($lq) {
                    $lq->orderBy('order')->with(['quizQuestions' => fn ($qq) => $qq->orderBy('order')]);
                }]);
            },
        ]);

        return Inertia::render('Admin/Courses/Edit', [
            'course' => $course,
        ]);
    }

    public function update(Request $request, Course $course)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'level' => 'required|in:beginner,intermediate,advanced',
            'summary' => 'required|string|max:500',
            'description' => 'required|string',
            'thumbnail_url' => 'nullable|url|max:500',
            'estimated_minutes' => 'required|integer|min:1|max:1000',
            'badge_name' => 'nullable|string|max:100',
            'is_published' => 'boolean',
        ]);

        $course->update($validated);

        return back()->with('success', 'Course details updated.');
    }

    public function destroy(Course $course)
    {
        $course->delete();

        return redirect()->route('admin.courses.index')->with('success', 'Course removed successfully.');
    }

    public function storeModule(Request $request, Course $course)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $maxOrder = $course->modules()->max('order') ?? 0;

        $course->modules()->create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'order' => $maxOrder + 1,
        ]);

        return back()->with('success', 'Module added.');
    }

    public function destroyModule(CourseModule $module)
    {
        $module->delete();

        return back()->with('success', 'Module deleted.');
    }

    public function storeLesson(Request $request, CourseModule $module)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:video,article,interactive_quiz',
            'duration_minutes' => 'required|integer|min:1',
            'video_url' => 'nullable|string|max:500',
            'content' => 'nullable|string',
            'threat_level' => 'required|in:low,medium,high,critical',
            'key_takeaways' => 'nullable|array',
        ]);

        $slug = Str::slug($validated['title']);
        $maxOrder = $module->lessons()->max('order') ?? 0;

        $module->lessons()->create([
            ...$validated,
            'slug' => $slug,
            'order' => $maxOrder + 1,
            'is_published' => true,
        ]);

        return back()->with('success', 'Lesson added successfully.');
    }

    public function updateLesson(Request $request, Lesson $lesson)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:video,article,interactive_quiz',
            'duration_minutes' => 'required|integer|min:1',
            'video_url' => 'nullable|string|max:500',
            'content' => 'nullable|string',
            'threat_level' => 'required|in:low,medium,high,critical',
            'key_takeaways' => 'nullable|array',
            'is_published' => 'boolean',
        ]);

        $lesson->update($validated);

        return back()->with('success', 'Lesson updated.');
    }

    public function destroyLesson(Lesson $lesson)
    {
        $lesson->delete();

        return back()->with('success', 'Lesson removed.');
    }

    public function storeQuizQuestion(Request $request, Lesson $lesson)
    {
        $validated = $request->validate([
            'question' => 'required|string',
            'scenario' => 'nullable|string',
            'options' => 'required|array|min:2',
            'correct_option_index' => 'required|integer|min:0',
            'explanation' => 'nullable|string',
        ]);

        $maxOrder = $lesson->quizQuestions()->max('order') ?? 0;

        $lesson->quizQuestions()->create([
            ...$validated,
            'order' => $maxOrder + 1,
        ]);

        return back()->with('success', 'Quiz question added.');
    }

    public function destroyQuizQuestion(QuizQuestion $question)
    {
        $question->delete();

        return back()->with('success', 'Question deleted.');
    }
}

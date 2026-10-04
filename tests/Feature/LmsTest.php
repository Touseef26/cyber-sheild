<?php

use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\CourseModule;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\User;
use Database\Seeders\CyberSecurityLmsSeeder;

beforeEach(function () {
    $this->seed(CyberSecurityLmsSeeder::class);
});

test('authenticated user can view courses catalog', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/courses');

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page->component('Courses/Index'));
});

test('authenticated user can view course syllabus', function () {
    $user = User::factory()->create();
    $course = Course::first();

    $response = $this->actingAs($user)->get("/courses/{$course->slug}");

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page->component('Courses/Show')
        ->has('course.title')
        ->has('course.modules')
    );
});

test('user can enroll in a course and is redirected to first lesson', function () {
    $user = User::factory()->create();
    $course = Course::first();

    $response = $this->actingAs($user)->post("/courses/{$course->slug}/enroll");

    $this->assertDatabaseHas('course_enrollments', [
        'user_id' => $user->id,
        'course_id' => $course->id,
    ]);

    $firstLesson = $course->modules()->first()->lessons()->first();
    $response->assertRedirect("/courses/{$course->slug}/lessons/{$firstLesson->slug}");
});

test('user can access lesson player and complete a lesson', function () {
    $user = User::factory()->create();
    $course = Course::first();
    $lesson = $course->modules()->first()->lessons()->first();

    $response = $this->actingAs($user)->get("/courses/{$course->slug}/lessons/{$lesson->slug}");
    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page->component('Courses/Lesson')
        ->where('lesson.id', $lesson->id)
    );

    // Complete lesson
    $completeResponse = $this->actingAs($user)->post("/courses/{$course->slug}/lessons/{$lesson->slug}/complete", [
        'quiz_score' => 100,
        'quiz_answers' => ['1' => 1],
    ]);

    $completeResponse->assertStatus(302);
    $this->assertDatabaseHas('lesson_progress', [
        'user_id' => $user->id,
        'lesson_id' => $lesson->id,
        'is_completed' => true,
    ]);
});

test('completing all lessons in a course generates certificate', function () {
    $user = User::factory()->create();
    $course = Course::first();

    $allLessons = $course->modules()->with('lessons')->get()->flatMap->lessons;

    foreach ($allLessons as $lesson) {
        $this->actingAs($user)->post("/courses/{$course->slug}/lessons/{$lesson->slug}/complete", [
            'quiz_score' => 100,
        ]);
    }

    $enrollment = CourseEnrollment::where('user_id', $user->id)
        ->where('course_id', $course->id)
        ->first();

    expect($enrollment)->not->toBeNull();
    expect($enrollment->progress_percentage)->toBe(100);
    expect($enrollment->completed_at)->not->toBeNull();
    expect($enrollment->certificate_code)->not->toBeNull();

    // Verify certificate page can now be viewed
    $certResponse = $this->actingAs($user)->get("/courses/{$course->slug}/certificate");
    $certResponse->assertStatus(200);
    $certResponse->assertInertia(fn ($page) => $page->component('Courses/Certificate'));
});

test('incomplete course blocks certificate view', function () {
    $user = User::factory()->create();
    $course = Course::first();

    $response = $this->actingAs($user)->get("/courses/{$course->slug}/certificate");
    $response->assertStatus(403);
});

test('admin can access course studio and create a course', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    $response = $this->actingAs($admin)->get('/admin/courses');
    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page->component('Admin/Courses/Index'));

    $createResponse = $this->actingAs($admin)->post('/admin/courses', [
        'title' => 'Advanced Zero Trust Architecture',
        'category' => 'Network Defense',
        'level' => 'advanced',
        'summary' => 'Comprehensive guide to never trust, always verify architectures.',
        'description' => 'Deep dive into micro-segmentation and identity perimeter.',
        'thumbnail_url' => 'https://images.unsplash.com/photo-1550751827-4bd374c3f58b?auto=format&fit=crop&w=800&q=80',
        'estimated_minutes' => 45,
        'badge_name' => 'Zero Trust Architect',
        'is_published' => true,
    ]);

    $this->assertDatabaseHas('courses', [
        'title' => 'Advanced Zero Trust Architecture',
        'slug' => 'advanced-zero-trust-architecture',
    ]);
});

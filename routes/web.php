<?php

use App\Http\Controllers\AdminCourseController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LessonController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');

    // LMS Learner Routes
    Route::get('/courses', [CourseController::class, 'index'])->name('courses.index');
    Route::get('/courses/{course:slug}', [CourseController::class, 'show'])->name('courses.show');
    Route::post('/courses/{course:slug}/enroll', [CourseController::class, 'enroll'])->name('courses.enroll');
    Route::get('/courses/{course:slug}/certificate', [CourseController::class, 'certificate'])->name('courses.certificate');
    Route::get('/courses/{course:slug}/lessons/{lesson:slug}', [LessonController::class, 'show'])->name('courses.lessons.show');
    Route::post('/courses/{course:slug}/lessons/{lesson:slug}/complete', [LessonController::class, 'complete'])->name('courses.lessons.complete');

    // LMS Admin / Instructor Studio
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/courses', [AdminCourseController::class, 'index'])->name('courses.index');
        Route::post('/courses', [AdminCourseController::class, 'store'])->name('courses.store');
        Route::get('/courses/{course}/edit', [AdminCourseController::class, 'edit'])->name('courses.edit');
        Route::put('/courses/{course}', [AdminCourseController::class, 'update'])->name('courses.update');
        Route::delete('/courses/{course}', [AdminCourseController::class, 'destroy'])->name('courses.destroy');

        Route::post('/courses/{course}/modules', [AdminCourseController::class, 'storeModule'])->name('courses.modules.store');
        Route::delete('/modules/{module}', [AdminCourseController::class, 'destroyModule'])->name('modules.destroy');

        Route::post('/modules/{module}/lessons', [AdminCourseController::class, 'storeLesson'])->name('lessons.store');
        Route::put('/lessons/{lesson}', [AdminCourseController::class, 'updateLesson'])->name('lessons.update');
        Route::delete('/lessons/{lesson}', [AdminCourseController::class, 'destroyLesson'])->name('lessons.destroy');

        Route::post('/lessons/{lesson}/quizzes', [AdminCourseController::class, 'storeQuizQuestion'])->name('quizzes.store');
        Route::delete('/quizzes/{question}', [AdminCourseController::class, 'destroyQuizQuestion'])->name('quizzes.destroy');
    });
});

require __DIR__.'/settings.php';

<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import {
    Award,
    BookOpen,
    CheckCircle2,
    Clock,
    Edit3,
    Eye,
    GraduationCap,
    Layers,
    Plus,
    PlusCircle,
    Search,
    Shield,
    ShieldAlert,
    Trash2,
    Users,
    X,
} from '@lucide/vue';
import { ref } from 'vue';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Course Studio',
                href: '/admin/courses',
            },
        ],
    },
});

type CourseItem = {
    id: number;
    title: string;
    slug: string;
    category: string;
    level: string;
    thumbnail_url: string | null;
    estimated_minutes: number;
    is_published: boolean;
    modules_count: number;
    lessons_count: number;
    enrollments_count: number;
    completions_count: number;
};

type Props = {
    courses: CourseItem[];
    stats: {
        total_courses: number;
        total_lessons: number;
        total_enrollments: number;
        total_completions: number;
        total_learners: number;
    };
};

const props = defineProps<Props>();

const showCreateModal = ref(false);

const form = useForm({
    title: '',
    category: 'Email & Social Engineering',
    level: 'beginner',
    summary: '',
    description: '',
    thumbnail_url: 'https://images.unsplash.com/photo-1563986768609-322da13575f3?auto=format&fit=crop&w=800&q=80',
    estimated_minutes: 30,
    badge_name: 'Threat Defender',
    is_published: true,
});

const submitCreate = () => {
    form.post('/admin/courses', {
        onSuccess: () => {
            showCreateModal.value = false;
            form.reset();
        },
    });
};

const deleteCourse = (course: CourseItem) => {
    if (confirm(`Are you sure you want to delete the course "${course.title}"? This will delete all its modules and lessons.`)) {
        router.delete(`/admin/courses/${course.id}`);
    }
};
</script>

<template>
    <Head title="Course Authoring Studio - CyberShield LMS" />

    <div class="flex flex-1 flex-col gap-8 p-4 md:p-8 max-w-7xl mx-auto w-full">
        <!-- Studio Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="space-y-1">
                <div class="inline-flex items-center gap-2 rounded-full bg-purple-500/10 px-3 py-1 text-xs font-semibold text-purple-600 dark:text-purple-400 border border-purple-500/20">
                    <ShieldAlert class="size-3.5" />
                    <span>Curriculum Studio & Management</span>
                </div>
                <h1 class="text-2xl md:text-3xl font-extrabold text-zinc-900 dark:text-zinc-100">
                    Course Studio
                </h1>
                <p class="text-xs text-zinc-500">
                    Create, organize, and administer cybersecurity awareness courses and scenario challenges.
                </p>
            </div>

            <div class="flex items-center gap-3">
                <Link
                    href="/courses"
                    class="inline-flex items-center gap-1.5 rounded-xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 px-4 py-2.5 text-xs font-semibold text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-colors"
                >
                    <Eye class="size-4" />
                    <span>Preview Learner Catalog</span>
                </Link>

                <button
                    @click="showCreateModal = true"
                    class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white px-4 py-2.5 text-xs font-bold shadow-md transition-all cursor-pointer"
                >
                    <Plus class="size-4" />
                    <span>Create New Course</span>
                </button>
            </div>
        </div>

        <!-- Management KPI Metrics -->
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-5">
            <div class="rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900/60 p-4">
                <span class="text-xs font-medium text-zinc-500 flex items-center gap-1.5">
                    <BookOpen class="size-3.5 text-emerald-500" />
                    Total Courses
                </span>
                <p class="text-2xl font-bold text-zinc-900 dark:text-zinc-100 mt-1">{{ stats.total_courses }}</p>
            </div>

            <div class="rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900/60 p-4">
                <span class="text-xs font-medium text-zinc-500 flex items-center gap-1.5">
                    <Layers class="size-3.5 text-sky-500" />
                    Total Lessons
                </span>
                <p class="text-2xl font-bold text-zinc-900 dark:text-zinc-100 mt-1">{{ stats.total_lessons }}</p>
            </div>

            <div class="rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900/60 p-4">
                <span class="text-xs font-medium text-zinc-500 flex items-center gap-1.5">
                    <Users class="size-3.5 text-indigo-500" />
                    Total Learners
                </span>
                <p class="text-2xl font-bold text-zinc-900 dark:text-zinc-100 mt-1">{{ stats.total_learners }}</p>
            </div>

            <div class="rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900/60 p-4">
                <span class="text-xs font-medium text-zinc-500 flex items-center gap-1.5">
                    <GraduationCap class="size-3.5 text-purple-500" />
                    Enrollments
                </span>
                <p class="text-2xl font-bold text-zinc-900 dark:text-zinc-100 mt-1">{{ stats.total_enrollments }}</p>
            </div>

            <div class="rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900/60 p-4">
                <span class="text-xs font-medium text-zinc-500 flex items-center gap-1.5">
                    <Award class="size-3.5 text-amber-500" />
                    Completions
                </span>
                <p class="text-2xl font-bold text-zinc-900 dark:text-zinc-100 mt-1">{{ stats.total_completions }}</p>
            </div>
        </div>

        <!-- Courses Management Table -->
        <div class="rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900/50 shadow-xs overflow-hidden">
            <div class="p-5 border-b border-zinc-200 dark:border-zinc-800 flex items-center justify-between">
                <div>
                    <h2 class="text-base font-bold text-zinc-900 dark:text-zinc-100">
                        Available Courses & Curriculum
                    </h2>
                    <p class="text-xs text-zinc-500">
                        Manage curriculum modules, video lessons, and interactive quiz scenarios.
                    </p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-zinc-50 dark:bg-zinc-900/80 text-[11px] font-bold uppercase tracking-wider text-zinc-500 border-b border-zinc-200 dark:border-zinc-800">
                        <tr>
                            <th class="py-3 px-5">Course</th>
                            <th class="py-3 px-4">Level</th>
                            <th class="py-3 px-4">Curriculum</th>
                            <th class="py-3 px-4">Learners</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-5 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800/80">
                        <tr
                            v-for="course in courses"
                            :key="course.id"
                            class="hover:bg-zinc-50 dark:hover:bg-zinc-800/30 transition-colors"
                        >
                            <td class="py-4 px-5">
                                <div class="flex items-center gap-3">
                                    <div class="size-12 rounded-xl bg-zinc-950 overflow-hidden shrink-0">
                                        <img
                                            :src="course.thumbnail_url || 'https://images.unsplash.com/photo-1563986768609-322da13575f3?auto=format&fit=crop&w=800&q=80'"
                                            :alt="course.title"
                                            class="h-full w-full object-cover"
                                        />
                                    </div>
                                    <div>
                                        <span class="text-sm font-bold text-zinc-900 dark:text-zinc-100 block">
                                            {{ course.title }}
                                        </span>
                                        <span class="text-xs text-emerald-600 dark:text-emerald-400 font-medium">
                                            {{ course.category }} • {{ course.estimated_minutes }} mins
                                        </span>
                                    </div>
                                </div>
                            </td>

                            <td class="py-4 px-4">
                                <span class="rounded px-2 py-0.5 text-xs font-semibold uppercase tracking-wider bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300">
                                    {{ course.level }}
                                </span>
                            </td>

                            <td class="py-4 px-4 text-xs text-zinc-600 dark:text-zinc-400">
                                <span class="font-medium text-zinc-900 dark:text-zinc-100">{{ course.modules_count }}</span> modules •
                                <span class="font-medium text-zinc-900 dark:text-zinc-100">{{ course.lessons_count }}</span> lessons
                            </td>

                            <td class="py-4 px-4 text-xs text-zinc-600 dark:text-zinc-400">
                                <div>
                                    <span class="font-bold text-zinc-900 dark:text-zinc-100">{{ course.enrollments_count }}</span> enrolled
                                </div>
                                <div class="text-[11px] text-zinc-400">
                                    {{ course.completions_count }} completed
                                </div>
                            </td>

                            <td class="py-4 px-4">
                                <span
                                    v-if="course.is_published"
                                    class="inline-flex items-center gap-1 rounded-full bg-emerald-500/10 px-2.5 py-0.5 text-xs font-semibold text-emerald-600 dark:text-emerald-400 border border-emerald-500/20"
                                >
                                    <CheckCircle2 class="size-3" />
                                    <span>Published</span>
                                </span>
                                <span
                                    v-else
                                    class="inline-flex items-center rounded-full bg-zinc-100 dark:bg-zinc-800 px-2.5 py-0.5 text-xs font-semibold text-zinc-500"
                                >
                                    Draft
                                </span>
                            </td>

                            <td class="py-4 px-5 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <Link
                                        :href="`/admin/courses/${course.id}/edit`"
                                        class="inline-flex items-center gap-1 rounded-lg border border-zinc-200 dark:border-zinc-800 px-3 py-1.5 text-xs font-semibold text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors"
                                    >
                                        <Edit3 class="size-3.5" />
                                        <span>Manage</span>
                                    </Link>

                                    <button
                                        @click="deleteCourse(course)"
                                        class="rounded-lg p-1.5 text-zinc-400 hover:text-red-500 hover:bg-red-500/10 transition-colors cursor-pointer"
                                        title="Delete Course"
                                    >
                                        <Trash2 class="size-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Create Course Modal -->
        <div
            v-if="showCreateModal"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-zinc-950/70 backdrop-blur-xs"
        >
            <div class="w-full max-w-xl rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-6 shadow-2xl space-y-5 max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between border-b border-zinc-100 dark:border-zinc-800 pb-3">
                    <h3 class="text-lg font-bold text-zinc-900 dark:text-zinc-100">
                        Create New Security Course
                    </h3>
                    <button
                        @click="showCreateModal = false"
                        class="text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200"
                    >
                        <X class="size-5" />
                    </button>
                </div>

                <form @submit.prevent="submitCreate" class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-zinc-700 dark:text-zinc-300 mb-1">
                            Course Title *
                        </label>
                        <input
                            v-model="form.title"
                            type="text"
                            required
                            placeholder="e.g. AI Deepfakes & Voice Cloning Defense"
                            class="w-full rounded-xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-950 p-2.5 text-xs text-zinc-900 dark:text-zinc-100 focus:outline-hidden focus:ring-2 focus:ring-emerald-500"
                        />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-zinc-700 dark:text-zinc-300 mb-1">
                                Category *
                            </label>
                            <input
                                v-model="form.category"
                                type="text"
                                required
                                placeholder="e.g. Identity & Authentication"
                                class="w-full rounded-xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-950 p-2.5 text-xs text-zinc-900 dark:text-zinc-100"
                            />
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-zinc-700 dark:text-zinc-300 mb-1">
                                Difficulty Level *
                            </label>
                            <select
                                v-model="form.level"
                                class="w-full rounded-xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-950 p-2.5 text-xs text-zinc-900 dark:text-zinc-100"
                            >
                                <option value="beginner">Beginner</option>
                                <option value="intermediate">Intermediate</option>
                                <option value="advanced">Advanced</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-zinc-700 dark:text-zinc-300 mb-1">
                            Short Summary *
                        </label>
                        <textarea
                            v-model="form.summary"
                            required
                            rows="2"
                            placeholder="Brief overview shown on course cards..."
                            class="w-full rounded-xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-950 p-2.5 text-xs text-zinc-900 dark:text-zinc-100"
                        />
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-zinc-700 dark:text-zinc-300 mb-1">
                            Full Syllabus Description *
                        </label>
                        <textarea
                            v-model="form.description"
                            required
                            rows="3"
                            placeholder="Comprehensive breakdown of training outcomes and threat intelligence..."
                            class="w-full rounded-xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-950 p-2.5 text-xs text-zinc-900 dark:text-zinc-100"
                        />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-zinc-700 dark:text-zinc-300 mb-1">
                                Estimated Minutes
                            </label>
                            <input
                                v-model.number="form.estimated_minutes"
                                type="number"
                                min="1"
                                class="w-full rounded-xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-950 p-2.5 text-xs text-zinc-900 dark:text-zinc-100"
                            />
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-zinc-700 dark:text-zinc-300 mb-1">
                                Certificate Badge Title
                            </label>
                            <input
                                v-model="form.badge_name"
                                type="text"
                                placeholder="e.g. AI Threat Specialist"
                                class="w-full rounded-xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-950 p-2.5 text-xs text-zinc-900 dark:text-zinc-100"
                            />
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-zinc-100 dark:border-zinc-800">
                        <button
                            type="button"
                            @click="showCreateModal = false"
                            class="rounded-xl border border-zinc-200 dark:border-zinc-800 px-4 py-2 text-xs font-medium text-zinc-600 dark:text-zinc-400"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white px-5 py-2 text-xs font-bold shadow-md cursor-pointer"
                        >
                            Create & Open Studio
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

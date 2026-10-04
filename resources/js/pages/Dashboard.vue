<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    AlertTriangle,
    ArrowRight,
    Award,
    BookOpen,
    CheckCircle2,
    Clock,
    FileText,
    GraduationCap,
    HelpCircle,
    Play,
    PlayCircle,
    Shield,
    ShieldAlert,
    ShieldCheck,
    Sparkles,
} from '@lucide/vue';
import { dashboard } from '@/routes';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard(),
            },
        ],
    },
});

type EnrolledCourse = {
    id: number;
    title: string;
    slug: string;
    category: string;
    level: string;
    thumbnail_url: string | null;
    progress_percentage: number;
    is_completed: boolean;
    certificate_code: string | null;
    next_lesson_slug: string | null;
    next_lesson_title: string | null;
    next_lesson_type: string | null;
};

type RecommendedCourse = {
    id: number;
    title: string;
    slug: string;
    category: string;
    level: string;
    summary: string;
    thumbnail_url: string | null;
    estimated_minutes: number;
    modules_count: number;
    lessons_count: number;
};

type Props = {
    enrolledCourses: EnrolledCourse[];
    recommendedCourses: RecommendedCourse[];
    stats: {
        enrolled_courses_count: number;
        completed_courses_count: number;
        completed_lessons_count: number;
        certificates_earned_count: number;
        readiness_score: number;
    };
};

const props = defineProps<Props>();

const threatLevelBadge = (level: string) => {
    switch (level) {
        case 'beginner':
            return 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/20';
        case 'intermediate':
            return 'bg-sky-500/10 text-sky-600 dark:text-sky-400 border-sky-500/20';
        case 'advanced':
            return 'bg-purple-500/10 text-purple-600 dark:text-purple-400 border-purple-500/20';
        default:
            return 'bg-zinc-500/10 text-zinc-600 dark:text-zinc-400 border-zinc-500/20';
    }
};
</script>

<template>
    <Head title="CyberShield Dashboard" />

    <div class="flex flex-1 flex-col gap-8 p-4 md:p-8 max-w-7xl mx-auto w-full">
        <!-- Hero Cyber Readiness Banner -->
        <div
            class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-zinc-900 via-zinc-950 to-emerald-950 p-6 md:p-8 text-white shadow-xl border border-zinc-800"
        >
            <div class="absolute -right-8 -bottom-8 opacity-15 pointer-events-none">
                <ShieldCheck class="size-64 text-emerald-400" />
            </div>

            <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div class="max-w-2xl space-y-2">
                    <div class="inline-flex items-center gap-2 rounded-full bg-emerald-500/20 px-3 py-1 text-xs font-semibold text-emerald-300 border border-emerald-500/30">
                        <Sparkles class="size-3.5" />
                        <span>CyberShield Threat Readiness</span>
                    </div>

                    <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight">
                        Personal Threat Awareness & LMS Portal
                    </h1>

                    <p class="text-xs md:text-sm text-zinc-300 leading-relaxed">
                        Continuous cybersecurity training strengthens the human firewall against social engineering, weaponized attachments, and credential stuffing.
                    </p>
                </div>

                <!-- Cyber Readiness Meter -->
                <div class="rounded-2xl bg-zinc-900/80 border border-zinc-800 p-4 shrink-0 flex items-center gap-4">
                    <div class="relative size-16 flex items-center justify-center">
                        <svg class="size-full -rotate-90" viewBox="0 0 36 36">
                            <path
                                class="text-zinc-800"
                                stroke-width="3"
                                stroke="currentColor"
                                fill="none"
                                d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
                            />
                            <path
                                class="text-emerald-500 transition-all duration-1000"
                                stroke-dasharray="100, 100"
                                :stroke-dashoffset="100 - stats.readiness_score"
                                stroke-width="3"
                                stroke-linecap="round"
                                stroke="currentColor"
                                fill="none"
                                d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
                            />
                        </svg>
                        <span class="absolute text-sm font-black text-emerald-400">
                            {{ stats.readiness_score }}%
                        </span>
                    </div>

                    <div>
                        <span class="text-[11px] font-bold uppercase tracking-wider text-zinc-400 block">
                            Threat Readiness
                        </span>
                        <span class="text-xs font-bold text-zinc-100">
                            {{ stats.readiness_score >= 80 ? 'Vigilant / Hardened' : 'Training Advised' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Overview Row -->
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
            <div class="rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900/60 p-4 shadow-xs">
                <span class="text-xs font-medium text-zinc-500 flex items-center gap-1.5">
                    <BookOpen class="size-4 text-emerald-500" />
                    Active Courses
                </span>
                <p class="text-2xl font-bold text-zinc-900 dark:text-zinc-100 mt-1">
                    {{ stats.enrolled_courses_count }}
                </p>
            </div>

            <div class="rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900/60 p-4 shadow-xs">
                <span class="text-xs font-medium text-zinc-500 flex items-center gap-1.5">
                    <CheckCircle2 class="size-4 text-sky-500" />
                    Lessons Mastered
                </span>
                <p class="text-2xl font-bold text-zinc-900 dark:text-zinc-100 mt-1">
                    {{ stats.completed_lessons_count }}
                </p>
            </div>

            <div class="rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900/60 p-4 shadow-xs">
                <span class="text-xs font-medium text-zinc-500 flex items-center gap-1.5">
                    <ShieldCheck class="size-4 text-purple-500" />
                    Completed Courses
                </span>
                <p class="text-2xl font-bold text-zinc-900 dark:text-zinc-100 mt-1">
                    {{ stats.completed_courses_count }}
                </p>
            </div>

            <div class="rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900/60 p-4 shadow-xs">
                <span class="text-xs font-medium text-zinc-500 flex items-center gap-1.5">
                    <Award class="size-4 text-amber-500" />
                    Certified Badges
                </span>
                <p class="text-2xl font-bold text-zinc-900 dark:text-zinc-100 mt-1">
                    {{ stats.certificates_earned_count }}
                </p>
            </div>
        </div>

        <!-- Active Enrolled Courses -->
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-bold text-zinc-900 dark:text-zinc-100">
                        Continue Training
                    </h2>
                    <p class="text-xs text-zinc-500">
                        Pick up right where you left off in your threat response drills.
                    </p>
                </div>

                <Link
                    href="/courses"
                    class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 hover:underline flex items-center gap-1"
                >
                    <span>Browse All Courses</span>
                    <ArrowRight class="size-3.5" />
                </Link>
            </div>

            <div v-if="enrolledCourses.length > 0" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div
                    v-for="course in enrolledCourses"
                    :key="course.id"
                    class="rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900/60 p-5 shadow-xs flex flex-col justify-between space-y-4"
                >
                    <div class="flex items-start gap-4">
                        <div class="size-16 rounded-xl bg-zinc-950 overflow-hidden shrink-0">
                            <img
                                :src="course.thumbnail_url || 'https://images.unsplash.com/photo-1563986768609-322da13575f3?auto=format&fit=crop&w=800&q=80'"
                                :alt="course.title"
                                class="h-full w-full object-cover"
                            />
                        </div>

                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2">
                                <span :class="['rounded px-2 py-0.5 text-[10px] font-bold uppercase border', threatLevelBadge(course.level)]">
                                    {{ course.level }}
                                </span>
                                <span class="text-xs text-zinc-400 truncate">{{ course.category }}</span>
                            </div>

                            <h3 class="text-base font-bold text-zinc-900 dark:text-zinc-100 truncate mt-1">
                                {{ course.title }}
                            </h3>

                            <div v-if="course.next_lesson_title" class="flex items-center gap-1.5 text-xs text-emerald-600 dark:text-emerald-400 mt-1">
                                <PlayCircle v-if="course.next_lesson_type === 'video'" class="size-3.5" />
                                <HelpCircle v-else-if="course.next_lesson_type === 'interactive_quiz'" class="size-3.5" />
                                <FileText v-else class="size-3.5" />
                                <span class="truncate font-medium">Next: {{ course.next_lesson_title }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Progress Bar & CTA -->
                    <div class="space-y-3 pt-2 border-t border-zinc-100 dark:border-zinc-800/80">
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-zinc-500 font-medium">Completion Progress</span>
                            <span class="font-bold text-emerald-500">{{ course.progress_percentage }}%</span>
                        </div>
                        <div class="h-2 w-full rounded-full bg-zinc-100 dark:bg-zinc-800 overflow-hidden">
                            <div
                                class="h-full bg-emerald-500 rounded-full transition-all duration-500"
                                :style="{ width: `${course.progress_percentage}%` }"
                            />
                        </div>

                        <div class="flex items-center justify-between pt-1">
                            <Link
                                v-if="course.next_lesson_slug"
                                :href="`/courses/${course.slug}/lessons/${course.next_lesson_slug}`"
                                class="inline-flex items-center gap-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white px-4 py-2 text-xs font-bold transition-all shadow-xs"
                            >
                                <Play class="size-3.5 fill-current" />
                                <span>Resume Lesson</span>
                            </Link>

                            <Link
                                v-if="course.is_completed"
                                :href="`/courses/${course.slug}/certificate`"
                                class="inline-flex items-center gap-1 text-xs font-bold text-amber-500 hover:underline"
                            >
                                <Award class="size-4" />
                                <span>View Certificate</span>
                            </Link>
                        </div>
                    </div>
                </div>
            </div>

            <div
                v-else
                class="rounded-2xl border border-dashed border-zinc-300 dark:border-zinc-800 p-8 text-center"
            >
                <GraduationCap class="size-8 text-zinc-400 mx-auto mb-2" />
                <p class="text-sm font-semibold text-zinc-700 dark:text-zinc-300">
                    You are not currently enrolled in any courses.
                </p>
                <Link
                    href="/courses"
                    class="mt-3 inline-flex items-center gap-1 rounded-xl bg-emerald-600 text-white px-4 py-2 text-xs font-bold hover:bg-emerald-500"
                >
                    <span>Explore Courses</span>
                </Link>
            </div>
        </div>

        <!-- Recommended Cyber Courses -->
        <div v-if="recommendedCourses.length > 0" class="space-y-4">
            <h2 class="text-xl font-bold text-zinc-900 dark:text-zinc-100">
                Recommended For You
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div
                    v-for="course in recommendedCourses"
                    :key="course.id"
                    class="rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900/60 overflow-hidden shadow-xs flex flex-col justify-between hover:shadow-md transition-shadow"
                >
                    <div>
                        <div class="relative aspect-video w-full bg-zinc-950">
                            <img
                                :src="course.thumbnail_url || 'https://images.unsplash.com/photo-1563986768609-322da13575f3?auto=format&fit=crop&w=800&q=80'"
                                :alt="course.title"
                                class="h-full w-full object-cover"
                            />
                            <div class="absolute top-3 left-3">
                                <span :class="['rounded px-2 py-0.5 text-[10px] font-bold uppercase border backdrop-blur-md', threatLevelBadge(course.level)]">
                                    {{ course.level }}
                                </span>
                            </div>
                        </div>

                        <div class="p-4 space-y-2">
                            <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100 line-clamp-1">
                                {{ course.title }}
                            </h3>
                            <p class="text-xs text-zinc-500 line-clamp-2">
                                {{ course.summary }}
                            </p>
                        </div>
                    </div>

                    <div class="p-4 pt-0">
                        <Link
                            :href="`/courses/${course.slug}`"
                            class="w-full inline-flex items-center justify-center gap-1.5 rounded-xl bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-zinc-900 dark:text-zinc-100 py-2 px-3 text-xs font-semibold transition-colors"
                        >
                            <span>View Course Details</span>
                            <ArrowRight class="size-3.5" />
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

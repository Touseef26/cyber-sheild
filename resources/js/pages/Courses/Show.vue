<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import {
    AlertTriangle,
    Award,
    BookOpen,
    CheckCircle2,
    Circle,
    Clock,
    FileText,
    GraduationCap,
    HelpCircle,
    Lock,
    Play,
    PlayCircle,
    Shield,
    ShieldCheck,
    Sparkles,
} from '@lucide/vue';
import { computed } from 'vue';

type LessonItem = {
    id: number;
    title: string;
    slug: string;
    type: 'video' | 'article' | 'interactive_quiz';
    duration_minutes: number;
    threat_level: 'low' | 'medium' | 'high' | 'critical';
    is_completed: boolean;
};

type ModuleItem = {
    id: number;
    title: string;
    description: string | null;
    lessons: LessonItem[];
};

type CourseData = {
    id: number;
    title: string;
    slug: string;
    category: string;
    level: 'beginner' | 'intermediate' | 'advanced';
    summary: string;
    description: string;
    thumbnail_url: string | null;
    estimated_minutes: number;
    badge_name: string | null;
    modules: ModuleItem[];
    total_lessons: number;
    completed_lessons_count: number;
};

type EnrollmentData = {
    id: number;
    progress_percentage: number;
    enrolled_at: string | null;
    completed_at: string | null;
    certificate_code: string | null;
} | null;

type Props = {
    course: CourseData;
    enrollment: EnrollmentData;
    nextLessonSlug: string | null;
    firstLessonSlug: string | null;
};

const props = defineProps<Props>();

defineOptions({
    layout: (props: any) => ({
        breadcrumbs: [
            {
                title: 'Security Academy',
                href: '/courses',
            },
            {
                title: props?.course?.title || 'Course',
                href: `/courses/${props?.course?.slug || ''}`,
            },
        ],
    }),
});

const enrollAndStart = () => {
    router.post(`/courses/${props.course.slug}/enroll`);
};

const threatBadgeClass = (threat: string) => {
    switch (threat) {
        case 'critical':
            return 'bg-red-500/10 text-red-500 border-red-500/20';
        case 'high':
            return 'bg-orange-500/10 text-orange-500 border-orange-500/20';
        case 'medium':
            return 'bg-amber-500/10 text-amber-500 border-amber-500/20';
        default:
            return 'bg-emerald-500/10 text-emerald-500 border-emerald-500/20';
    }
};
</script>

<template>
    <Head :title="course.title" />

    <div class="flex flex-1 flex-col gap-8 p-4 md:p-8 max-w-6xl mx-auto w-full">
        <!-- Course Hero Overview -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
            <div class="lg:col-span-2 space-y-6">
                <div class="space-y-3">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="rounded-md bg-emerald-500/10 border border-emerald-500/20 px-2.5 py-0.5 text-xs font-semibold text-emerald-600 dark:text-emerald-400 uppercase tracking-wide">
                            {{ course.category }}
                        </span>
                        <span class="rounded-md bg-zinc-100 dark:bg-zinc-800 px-2.5 py-0.5 text-xs font-semibold text-zinc-600 dark:text-zinc-300 uppercase tracking-wide">
                            {{ course.level }}
                        </span>
                        <span class="flex items-center gap-1 text-xs text-zinc-500 font-medium">
                            <Clock class="size-3.5" />
                            {{ course.estimated_minutes }} mins total
                        </span>
                    </div>

                    <h1 class="text-3xl font-extrabold tracking-tight text-zinc-900 dark:text-zinc-100 sm:text-4xl">
                        {{ course.title }}
                    </h1>

                    <p class="text-base text-zinc-600 dark:text-zinc-300 leading-relaxed">
                        {{ course.summary }}
                    </p>
                </div>

                <!-- Detailed Description -->
                <div class="rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900/50 p-6 space-y-4">
                    <h2 class="text-base font-bold text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
                        <ShieldCheck class="size-5 text-emerald-500" />
                        <span>Course Objectives & Threat Intelligence</span>
                    </h2>
                    <div class="text-sm text-zinc-600 dark:text-zinc-300 space-y-3 leading-relaxed whitespace-pre-line">
                        {{ course.description }}
                    </div>
                </div>
            </div>

            <!-- Sticky Enrollment / Action Card -->
            <div class="lg:col-span-1">
                <div class="sticky top-6 rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900/80 p-6 shadow-xl space-y-6 backdrop-blur-xs">
                    <div class="relative aspect-video w-full overflow-hidden rounded-xl bg-zinc-950">
                        <img
                            :src="course.thumbnail_url || 'https://images.unsplash.com/photo-1563986768609-322da13575f3?auto=format&fit=crop&w=800&q=80'"
                            :alt="course.title"
                            class="h-full w-full object-cover"
                        />
                        <div class="absolute inset-0 bg-gradient-to-t from-zinc-950/80 to-transparent flex items-end p-4">
                            <span class="text-xs font-semibold text-emerald-400 flex items-center gap-1.5">
                                <Sparkles class="size-4" />
                                <span>Award: {{ course.badge_name || 'Cyber Defender' }}</span>
                            </span>
                        </div>
                    </div>

                    <!-- Enrollment State Info -->
                    <div v-if="enrollment" class="space-y-3">
                        <div class="flex items-center justify-between text-xs font-medium">
                            <span class="text-zinc-500">Your Progress</span>
                            <span class="font-bold text-emerald-500">{{ enrollment.progress_percentage }}% Complete</span>
                        </div>
                        <div class="h-2 w-full overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-800">
                            <div
                                class="h-full bg-emerald-500 rounded-full transition-all duration-500"
                                :style="{ width: `${enrollment.progress_percentage}%` }"
                            />
                        </div>

                        <p class="text-xs text-zinc-400">
                            {{ course.completed_lessons_count }} of {{ course.total_lessons }} lessons finished
                        </p>
                    </div>

                    <!-- CTA Actions -->
                    <div class="space-y-3 pt-2">
                        <template v-if="enrollment">
                            <Link
                                v-if="nextLessonSlug"
                                :href="`/courses/${course.slug}/lessons/${nextLessonSlug}`"
                                class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-semibold py-3 px-4 text-sm shadow-md transition-all"
                            >
                                <Play class="size-4 fill-current" />
                                <span>{{ enrollment.completed_at ? 'Revisit Next Lesson' : 'Resume Lesson' }}</span>
                            </Link>

                            <Link
                                v-if="enrollment.completed_at"
                                :href="`/courses/${course.slug}/certificate`"
                                class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-zinc-900 hover:bg-zinc-800 dark:bg-zinc-100 dark:hover:bg-white text-white dark:text-zinc-900 font-semibold py-3 px-4 text-sm transition-all"
                            >
                                <Award class="size-4 text-amber-500" />
                                <span>View Official Certificate</span>
                            </Link>
                        </template>

                        <button
                            v-else
                            @click="enrollAndStart"
                            class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-semibold py-3 px-4 text-sm shadow-md transition-all cursor-pointer"
                        >
                            <Play class="size-4 fill-current" />
                            <span>Enroll Now & Start</span>
                        </button>
                    </div>

                    <!-- Badge Showcase -->
                    <div class="pt-4 border-t border-zinc-100 dark:border-zinc-800/80 flex items-center gap-3">
                        <div class="rounded-xl bg-emerald-500/10 p-2.5 text-emerald-500 border border-emerald-500/20">
                            <Award class="size-6" />
                        </div>
                        <div>
                            <h4 class="text-xs font-bold text-zinc-900 dark:text-zinc-100">
                                {{ course.badge_name || 'CyberShield Certified' }}
                            </h4>
                            <p class="text-[11px] text-zinc-500">
                                Official verified accreditation upon 100% completion
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Curriculum Outline -->
        <div class="space-y-6 pt-4">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">
                        Course Curriculum
                    </h2>
                    <p class="text-xs text-zinc-500 mt-1">
                        {{ course.modules.length }} Modules • {{ course.total_lessons }} Lessons • Interactive Security Drill
                    </p>
                </div>
            </div>

            <div class="space-y-4">
                <div
                    v-for="(module, mIndex) in course.modules"
                    :key="module.id"
                    class="rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900/40 overflow-hidden shadow-xs"
                >
                    <!-- Module Header -->
                    <div class="p-5 border-b border-zinc-100 dark:border-zinc-800/80 bg-zinc-50/50 dark:bg-zinc-900/60 flex items-center justify-between">
                        <div>
                            <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-400">
                                Section 0{{ mIndex + 1 }}
                            </span>
                            <h3 class="text-base font-bold text-zinc-900 dark:text-zinc-100 mt-0.5">
                                {{ module.title }}
                            </h3>
                            <p v-if="module.description" class="text-xs text-zinc-500 mt-1">
                                {{ module.description }}
                            </p>
                        </div>
                        <span class="text-xs font-medium text-zinc-400">
                            {{ module.lessons.length }} lessons
                        </span>
                    </div>

                    <!-- Lessons List -->
                    <div class="divide-y divide-zinc-100 dark:divide-zinc-800/60">
                        <div
                            v-for="(lesson, lIndex) in module.lessons"
                            :key="lesson.id"
                            class="p-4 flex items-center justify-between hover:bg-zinc-50 dark:hover:bg-zinc-800/30 transition-colors"
                        >
                            <div class="flex items-center gap-3">
                                <!-- Status Icon -->
                                <div>
                                    <CheckCircle2 v-if="lesson.is_completed" class="size-5 text-emerald-500" />
                                    <Circle v-else class="size-5 text-zinc-300 dark:text-zinc-600" />
                                </div>

                                <!-- Type Icon -->
                                <div class="rounded-lg bg-zinc-100 dark:bg-zinc-800 p-2 text-zinc-500">
                                    <PlayCircle v-if="lesson.type === 'video'" class="size-4 text-emerald-500" />
                                    <HelpCircle v-else-if="lesson.type === 'interactive_quiz'" class="size-4 text-purple-500" />
                                    <FileText v-else class="size-4 text-sky-500" />
                                </div>

                                <!-- Lesson Title -->
                                <div>
                                    <div class="flex items-center gap-2">
                                        <Link
                                            :href="`/courses/${course.slug}/lessons/${lesson.slug}`"
                                            class="text-sm font-semibold text-zinc-900 dark:text-zinc-100 hover:text-emerald-500 transition-colors"
                                        >
                                            {{ lesson.title }}
                                        </Link>
                                        <span
                                            :class="[
                                                'rounded px-1.5 py-0.2 text-[10px] font-semibold uppercase border',
                                                threatBadgeClass(lesson.threat_level)
                                            ]"
                                        >
                                            {{ lesson.threat_level }}
                                        </span>
                                    </div>
                                    <span class="text-xs text-zinc-400">
                                        {{ lesson.type === 'video' ? 'Video Lesson' : lesson.type === 'interactive_quiz' ? 'Scenario Challenge' : 'Guide / Article' }} • {{ lesson.duration_minutes }} mins
                                    </span>
                                </div>
                            </div>

                            <!-- Action Link -->
                            <Link
                                :href="`/courses/${course.slug}/lessons/${lesson.slug}`"
                                class="inline-flex items-center gap-1 text-xs font-semibold text-emerald-600 dark:text-emerald-400 hover:underline"
                            >
                                <span>{{ lesson.is_completed ? 'Review' : 'Start' }}</span>
                                <Play class="size-2.5 fill-current" />
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

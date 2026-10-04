<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import {
    AlertCircle,
    AlertTriangle,
    ArrowLeft,
    ArrowRight,
    Award,
    Check,
    CheckCircle2,
    ChevronDown,
    ChevronLeft,
    ChevronRight,
    Circle,
    Clock,
    FileText,
    HelpCircle,
    Menu,
    Play,
    PlayCircle,
    RotateCcw,
    Shield,
    ShieldAlert,
    ShieldCheck,
    Sparkles,
    X,
} from '@lucide/vue';
import { computed, ref } from 'vue';

type QuizQuestion = {
    id: number;
    question: string;
    scenario: string | null;
    options: string[];
    correct_option_index: number;
    explanation: string | null;
};

type LessonData = {
    id: number;
    title: string;
    slug: string;
    type: 'video' | 'article' | 'interactive_quiz';
    duration_minutes: number;
    video_url: string | null;
    content: string | null;
    key_takeaways: string[];
    threat_level: 'low' | 'medium' | 'high' | 'critical';
    quiz_questions: QuizQuestion[];
    is_completed: boolean;
    quiz_score: number | null;
    quiz_answers: Record<string, number> | null;
};

type CurriculumLesson = {
    id: number;
    title: string;
    slug: string;
    type: 'video' | 'article' | 'interactive_quiz';
    duration_minutes: number;
    threat_level: string;
    is_completed: boolean;
    is_active: boolean;
};

type CurriculumModule = {
    id: number;
    title: string;
    description: string | null;
    lessons: CurriculumLesson[];
};

type CourseData = {
    id: number;
    title: string;
    slug: string;
    category: string;
    badge_name: string | null;
    progress_percentage: number;
    certificate_code: string | null;
    completed_at: string | null;
};

type Props = {
    course: CourseData;
    lesson: LessonData;
    curriculum: CurriculumModule[];
    prevLesson: { title: string; slug: string } | null;
    nextLesson: { title: string; slug: string } | null;
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
            {
                title: props?.lesson?.title || 'Lesson',
                href: `/courses/${props?.course?.slug || ''}/lessons/${props?.lesson?.slug || ''}`,
            },
        ],
    }),
});

const sidebarOpen = ref(true);
const isCompletedLocal = ref(props.lesson.is_completed);
const isSubmitting = ref(false);

// Interactive Quiz State
const selectedAnswers = ref<Record<number, number>>(
    props.lesson.quiz_answers ? { ...props.lesson.quiz_answers } : {}
);
const showResults = ref<Record<number, boolean>>({});

// Initialize results if already completed
if (props.lesson.is_completed && props.lesson.quiz_answers) {
    props.lesson.quiz_questions.forEach((q) => {
        showResults.value[q.id] = true;
    });
}

const selectAnswer = (questionId: number, optionIndex: number) => {
    selectedAnswers.value[questionId] = optionIndex;
};

const checkQuestion = (questionId: number) => {
    showResults.value[questionId] = true;
};

const resetQuestion = (questionId: number) => {
    showResults.value[questionId] = false;
    delete selectedAnswers.value[questionId];
};

const allQuizQuestionsAnswered = computed(() => {
    if (props.lesson.quiz_questions.length === 0) return true;
    return props.lesson.quiz_questions.every(
        (q) => selectedAnswers.value[q.id] !== undefined && showResults.value[q.id]
    );
});

const quizCorrectCount = computed(() => {
    return props.lesson.quiz_questions.filter(
        (q) => selectedAnswers.value[q.id] === q.correct_option_index
    ).length;
});

const markComplete = () => {
    isSubmitting.value = true;

    let score: number | null = null;
    if (props.lesson.quiz_questions.length > 0) {
        score = Math.round((quizCorrectCount.value / props.lesson.quiz_questions.length) * 100);
    }

    router.post(
        `/courses/${props.course.slug}/lessons/${props.lesson.slug}/complete`,
        {
            quiz_score: score,
            quiz_answers: selectedAnswers.value,
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                isCompletedLocal.value = true;
                isSubmitting.value = false;
            },
            onError: () => {
                isSubmitting.value = false;
            },
        }
    );
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

const formatMarkdown = (text: string) => {
    let html = text
        .replace(/### (.*?)\n/g, '<h3 class="text-lg font-bold text-zinc-900 dark:text-zinc-100 mt-6 mb-2">$1</h3>')
        .replace(/#### (.*?)\n/g, '<h4 class="text-base font-semibold text-zinc-800 dark:text-zinc-200 mt-4 mb-2">$1</h4>')
        .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
        .replace(/`([^`]+)`/g, '<code class="px-1.5 py-0.5 rounded bg-zinc-100 dark:bg-zinc-800 text-emerald-600 dark:text-emerald-400 font-mono text-xs">$1</code>')
        .replace(
            /> ⚠️ (.*?)\n/g,
            '<div class="my-4 rounded-xl border border-amber-500/30 bg-amber-500/10 p-4 text-amber-700 dark:text-amber-300 text-sm font-medium flex gap-3"><span class="text-base">⚠️</span><div>$1</div></div>'
        )
        .replace(/```text([\s\S]*?)```/g, '<pre class="my-3 overflow-x-auto rounded-xl bg-zinc-950 p-4 font-mono text-xs text-zinc-300 border border-zinc-800">$1</pre>')
        .replace(/\n\n/g, '<p class="my-3 leading-relaxed text-zinc-700 dark:text-zinc-300 text-sm md:text-base"></p>')
        .replace(/- \[ \] (.*?)\n/g, '<div class="flex items-center gap-2 text-sm text-zinc-700 dark:text-zinc-300 my-1.5"><input type="checkbox" disabled class="rounded text-emerald-500" /><span>$1</span></div>')
        .replace(/^- (.*?)\n/gm, '<li class="ml-4 list-disc text-sm text-zinc-700 dark:text-zinc-300 my-1">$1</li>');

    return html;
};
</script>

<template>
    <Head :title="`${lesson.title} - ${course.title}`" />

    <div class="flex flex-1 flex-col h-full min-h-[calc(100vh-4rem)]">
        <!-- Top Progress & Control Bar -->
        <div class="border-b border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900/90 px-4 py-3 sticky top-0 z-20 backdrop-blur-md">
            <div class="max-w-7xl mx-auto flex items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <Link
                        :href="`/courses/${course.slug}`"
                        class="inline-flex items-center gap-1 text-xs font-medium text-zinc-500 hover:text-zinc-900 dark:hover:text-zinc-100 transition-colors"
                    >
                        <ArrowLeft class="size-3.5" />
                        <span class="hidden sm:inline">Back to Overview</span>
                    </Link>

                    <div class="h-4 w-px bg-zinc-200 dark:bg-zinc-700 hidden sm:block" />

                    <div class="truncate">
                        <span class="text-xs text-zinc-400 font-medium block truncate">
                            {{ course.title }}
                        </span>
                        <h2 class="text-sm font-bold text-zinc-900 dark:text-zinc-100 truncate">
                            {{ lesson.title }}
                        </h2>
                    </div>
                </div>

                <!-- Progress Indicator & Actions -->
                <div class="flex items-center gap-3">
                    <div class="hidden md:flex items-center gap-2 text-xs">
                        <span class="text-zinc-500 font-medium">Course Progress</span>
                        <div class="w-28 h-2 rounded-full bg-zinc-100 dark:bg-zinc-800 overflow-hidden">
                            <div
                                class="h-full bg-emerald-500 rounded-full transition-all duration-300"
                                :style="{ width: `${course.progress_percentage}%` }"
                            />
                        </div>
                        <span class="font-bold text-emerald-500">{{ course.progress_percentage }}%</span>
                    </div>

                    <Link
                        v-if="course.completed_at"
                        :href="`/courses/${course.slug}/certificate`"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-500/10 px-2.5 py-1 text-xs font-semibold text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 hover:bg-emerald-500/20 transition-colors"
                    >
                        <Award class="size-3.5" />
                        <span class="hidden sm:inline">Certificate</span>
                    </Link>

                    <!-- Toggle Curriculum Drawer -->
                    <button
                        @click="sidebarOpen = !sidebarOpen"
                        class="rounded-lg border border-zinc-200 dark:border-zinc-800 p-1.5 text-zinc-600 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors lg:hidden"
                    >
                        <Menu class="size-4" />
                    </button>
                </div>
            </div>
        </div>

        <!-- Main Learning Workspace -->
        <div class="flex-1 flex max-w-7xl mx-auto w-full">
            <!-- Collapsible Curriculum Sidebar -->
            <aside
                :class="[
                    'border-r border-zinc-200 dark:border-zinc-800 bg-zinc-50/50 dark:bg-zinc-900/50 lg:block transition-all duration-300 shrink-0 w-80',
                    sidebarOpen ? 'block' : 'hidden lg:block'
                ]"
            >
                <div class="sticky top-14 p-4 max-h-[calc(100vh-4rem)] overflow-y-auto space-y-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-zinc-500">
                            Curriculum Outline
                        </h3>
                        <button
                            @click="sidebarOpen = false"
                            class="text-zinc-400 hover:text-zinc-600 lg:hidden"
                        >
                            <X class="size-4" />
                        </button>
                    </div>

                    <div class="space-y-4">
                        <div
                            v-for="(module, mIndex) in curriculum"
                            :key="module.id"
                            class="space-y-2"
                        >
                            <div class="text-[11px] font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-400">
                                Section 0{{ mIndex + 1 }}
                            </div>
                            <div class="text-xs font-semibold text-zinc-900 dark:text-zinc-100">
                                {{ module.title }}
                            </div>

                            <div class="space-y-1 pt-1">
                                <Link
                                    v-for="item in module.lessons"
                                    :key="item.id"
                                    :href="`/courses/${course.slug}/lessons/${item.slug}`"
                                    :class="[
                                        'group flex items-center justify-between rounded-xl px-3 py-2 text-xs font-medium transition-all duration-200',
                                        item.is_active
                                            ? 'bg-emerald-500 text-white shadow-xs font-semibold'
                                            : item.is_completed
                                            ? 'text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800/60'
                                            : 'text-zinc-600 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-800/60'
                                    ]"
                                >
                                    <div class="flex items-center gap-2.5 truncate">
                                        <CheckCircle2
                                            v-if="item.is_completed"
                                            :class="item.is_active ? 'text-white' : 'text-emerald-500'"
                                            class="size-4 shrink-0"
                                        />
                                        <Circle
                                            v-else
                                            :class="item.is_active ? 'text-white/60' : 'text-zinc-300 dark:text-zinc-600'"
                                            class="size-4 shrink-0"
                                        />

                                        <span class="truncate">{{ item.title }}</span>
                                    </div>

                                    <div class="flex items-center gap-1 text-[10px] shrink-0 opacity-70">
                                        <PlayCircle v-if="item.type === 'video'" class="size-3" />
                                        <HelpCircle v-else-if="item.type === 'interactive_quiz'" class="size-3" />
                                        <FileText v-else class="size-3" />
                                        <span>{{ item.duration_minutes }}m</span>
                                    </div>
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </aside>

            <!-- Stage / Main Lesson Content Area -->
            <main class="flex-1 p-4 md:p-8 max-w-4xl mx-auto w-full space-y-8">
                <!-- Lesson Header & Threat Badge -->
                <div class="space-y-3 pb-6 border-b border-zinc-200 dark:border-zinc-800">
                    <div class="flex flex-wrap items-center gap-2">
                        <span
                            :class="[
                                'inline-flex items-center gap-1 rounded-md px-2.5 py-0.5 text-xs font-bold uppercase tracking-wider border',
                                threatBadgeClass(lesson.threat_level)
                            ]"
                        >
                            <ShieldAlert class="size-3.5" />
                            <span>Threat Rating: {{ lesson.threat_level }}</span>
                        </span>

                        <span class="rounded-md bg-zinc-100 dark:bg-zinc-800 px-2.5 py-0.5 text-xs font-semibold text-zinc-600 dark:text-zinc-300 uppercase">
                            {{ lesson.type === 'video' ? 'Video Briefing' : lesson.type === 'interactive_quiz' ? 'Scenario Challenge' : 'Security Guide' }}
                        </span>

                        <span class="flex items-center gap-1 text-xs text-zinc-500 font-medium">
                            <Clock class="size-3.5" />
                            {{ lesson.duration_minutes }} minutes
                        </span>

                        <span
                            v-if="isCompletedLocal"
                            class="inline-flex items-center gap-1 rounded-md bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 px-2 py-0.5 text-xs font-semibold ml-auto"
                        >
                            <CheckCircle2 class="size-3.5" />
                            <span>Completed</span>
                        </span>
                    </div>

                    <h1 class="text-2xl md:text-3xl font-extrabold text-zinc-900 dark:text-zinc-100">
                        {{ lesson.title }}
                    </h1>
                </div>

                <!-- Video Player Container (if video lesson) -->
                <div v-if="lesson.type === 'video' && lesson.video_url" class="space-y-2">
                    <div class="relative aspect-video w-full overflow-hidden rounded-2xl bg-zinc-950 shadow-2xl border border-zinc-800">
                        <iframe
                            :src="lesson.video_url"
                            title="CyberShield Training Video"
                            class="h-full w-full border-0"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                            allowfullscreen
                        />
                    </div>
                    <p class="text-xs text-zinc-400 italic text-center">
                        Watch the high-threat briefing attentively before continuing.
                    </p>
                </div>

                <!-- Key Takeaways Defense Box -->
                <div
                    v-if="lesson.key_takeaways && lesson.key_takeaways.length > 0"
                    class="rounded-2xl border border-emerald-500/30 bg-emerald-500/5 dark:bg-emerald-950/20 p-5 md:p-6 space-y-3"
                >
                    <h3 class="text-sm font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-400 flex items-center gap-2">
                        <ShieldCheck class="size-4.5" />
                        <span>Defensive Rules & Key Takeaways</span>
                    </h3>
                    <ul class="space-y-2 text-sm text-zinc-700 dark:text-zinc-200">
                        <li
                            v-for="(takeaway, idx) in lesson.key_takeaways"
                            :key="idx"
                            class="flex items-start gap-2.5"
                        >
                            <Check class="size-4 text-emerald-500 mt-0.5 shrink-0" />
                            <span class="leading-relaxed">{{ takeaway }}</span>
                        </li>
                    </ul>
                </div>

                <!-- Article / Guide Content -->
                <div
                    v-if="lesson.content"
                    class="prose prose-zinc dark:prose-invert max-w-none text-zinc-800 dark:text-zinc-200"
                    v-html="formatMarkdown(lesson.content)"
                />

                <!-- Interactive Quizzes / Threat Drill Scenarios -->
                <div
                    v-if="lesson.quiz_questions && lesson.quiz_questions.length > 0"
                    class="space-y-6 pt-6 border-t border-zinc-200 dark:border-zinc-800"
                >
                    <div class="flex items-center justify-between">
                        <div class="space-y-1">
                            <div class="inline-flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-purple-600 dark:text-purple-400">
                                <HelpCircle class="size-4" />
                                <span>Incident Simulation & Knowledge Check</span>
                            </div>
                            <h3 class="text-xl font-bold text-zinc-900 dark:text-zinc-100">
                                Analyze & Neutralize the Threat
                            </h3>
                        </div>

                        <span class="text-xs font-medium text-zinc-500">
                            {{ lesson.quiz_questions.length }} Question{{ lesson.quiz_questions.length > 1 ? 's' : '' }}
                        </span>
                    </div>

                    <!-- Questions Loop -->
                    <div class="space-y-6">
                        <div
                            v-for="(q, qIndex) in lesson.quiz_questions"
                            :key="q.id"
                            class="rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900/60 p-5 md:p-6 shadow-xs space-y-4"
                        >
                            <!-- Scenario Box (if present) -->
                            <div
                                v-if="q.scenario"
                                class="rounded-xl border border-zinc-200 dark:border-zinc-800 bg-zinc-50 dark:bg-zinc-950 p-4 space-y-1.5"
                            >
                                <div class="flex items-center gap-1.5 text-xs font-bold text-zinc-500 uppercase tracking-wide">
                                    <AlertTriangle class="size-3.5 text-amber-500" />
                                    <span>Incident Context / Scenario</span>
                                </div>
                                <p class="text-sm font-mono text-zinc-800 dark:text-zinc-300 leading-relaxed">
                                    {{ q.scenario }}
                                </p>
                            </div>

                            <!-- Question Statement -->
                            <h4 class="text-base font-bold text-zinc-900 dark:text-zinc-100">
                                {{ qIndex + 1 }}. {{ q.question }}
                            </h4>

                            <!-- Multiple Choice Options -->
                            <div class="space-y-2">
                                <button
                                    v-for="(option, optIndex) in q.options"
                                    :key="optIndex"
                                    type="button"
                                    @click="selectAnswer(q.id, optIndex)"
                                    :disabled="showResults[q.id]"
                                    :class="[
                                        'w-full text-left p-3.5 rounded-xl border text-sm transition-all duration-200 flex items-start gap-3',
                                        showResults[q.id]
                                            ? optIndex === q.correct_option_index
                                                ? 'bg-emerald-500/10 border-emerald-500 text-emerald-700 dark:text-emerald-300 font-semibold'
                                                : selectedAnswers[q.id] === optIndex
                                                ? 'bg-red-500/10 border-red-500 text-red-700 dark:text-red-300'
                                                : 'opacity-50 border-zinc-200 dark:border-zinc-800'
                                            : selectedAnswers[q.id] === optIndex
                                            ? 'border-emerald-500 bg-emerald-500/5 ring-2 ring-emerald-500/20 text-zinc-900 dark:text-zinc-100'
                                            : 'border-zinc-200 dark:border-zinc-800 hover:border-zinc-300 dark:hover:border-zinc-700 text-zinc-700 dark:text-zinc-300'
                                    ]"
                                >
                                    <span
                                        :class="[
                                            'size-5 rounded-full flex items-center justify-center text-xs font-bold shrink-0 mt-0.5',
                                            showResults[q.id] && optIndex === q.correct_option_index
                                                ? 'bg-emerald-500 text-white'
                                                : showResults[q.id] && selectedAnswers[q.id] === optIndex
                                                ? 'bg-red-500 text-white'
                                                : selectedAnswers[q.id] === optIndex
                                                ? 'bg-emerald-500 text-white'
                                                : 'bg-zinc-100 dark:bg-zinc-800 text-zinc-500'
                                        ]"
                                    >
                                        {{ ['A', 'B', 'C', 'D'][optIndex] || optIndex + 1 }}
                                    </span>
                                    <span class="flex-1 leading-relaxed">{{ option }}</span>
                                </button>
                            </div>

                            <!-- Action & Verification Feedback -->
                            <div class="pt-2 flex items-center justify-between">
                                <button
                                    v-if="!showResults[q.id]"
                                    @click="checkQuestion(q.id)"
                                    :disabled="selectedAnswers[q.id] === undefined"
                                    class="rounded-xl bg-zinc-900 hover:bg-zinc-800 dark:bg-zinc-100 dark:hover:bg-white text-white dark:text-zinc-900 px-4 py-2 text-xs font-semibold transition-all disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer"
                                >
                                    Verify Threat Assessment
                                </button>

                                <button
                                    v-else
                                    @click="resetQuestion(q.id)"
                                    class="inline-flex items-center gap-1 text-xs text-zinc-500 hover:text-zinc-800 dark:hover:text-zinc-200 transition-colors"
                                >
                                    <RotateCcw class="size-3" />
                                    <span>Retry Question</span>
                                </button>
                            </div>

                            <!-- Explanation Reveal -->
                            <div
                                v-if="showResults[q.id]"
                                :class="[
                                    'rounded-xl p-4 text-xs leading-relaxed space-y-1 border',
                                    selectedAnswers[q.id] === q.correct_option_index
                                        ? 'bg-emerald-500/10 border-emerald-500/30 text-emerald-800 dark:text-emerald-200'
                                        : 'bg-amber-500/10 border-amber-500/30 text-amber-800 dark:text-amber-200'
                                ]"
                            >
                                <div class="font-bold flex items-center gap-1.5">
                                    <CheckCircle2 v-if="selectedAnswers[q.id] === q.correct_option_index" class="size-4 text-emerald-500" />
                                    <AlertCircle v-else class="size-4 text-amber-500" />
                                    <span>
                                        {{ selectedAnswers[q.id] === q.correct_option_index ? 'Correct Defense Strategy!' : 'Vulnerability Identified' }}
                                    </span>
                                </div>
                                <p v-if="q.explanation" class="pt-1">
                                    {{ q.explanation }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bottom Completion & Navigation Controls -->
                <div class="pt-8 border-t border-zinc-200 dark:border-zinc-800 flex flex-col sm:flex-row items-center justify-between gap-4">
                    <!-- Previous Lesson Button -->
                    <Link
                        v-if="prevLesson"
                        :href="`/courses/${course.slug}/lessons/${prevLesson.slug}`"
                        class="inline-flex items-center gap-2 rounded-xl border border-zinc-200 dark:border-zinc-800 px-4 py-2.5 text-xs font-semibold text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors w-full sm:w-auto justify-center"
                    >
                        <ChevronLeft class="size-4" />
                        <span>Previous: {{ prevLesson.title }}</span>
                    </Link>
                    <div v-else class="hidden sm:block" />

                    <!-- Mark Complete / Continue CTA -->
                    <div class="flex items-center gap-3 w-full sm:w-auto">
                        <button
                            @click="markComplete"
                            :disabled="isSubmitting || !allQuizQuestionsAnswered"
                            :class="[
                                'inline-flex items-center justify-center gap-2 rounded-xl py-2.5 px-5 text-xs font-bold transition-all shadow-md w-full sm:w-auto cursor-pointer',
                                isCompletedLocal
                                    ? 'bg-zinc-100 dark:bg-zinc-800 text-emerald-600 dark:text-emerald-400 border border-emerald-500/30'
                                    : 'bg-emerald-600 hover:bg-emerald-500 text-white'
                            ]"
                        >
                            <CheckCircle2 v-if="isCompletedLocal" class="size-4 text-emerald-500" />
                            <ShieldCheck v-else class="size-4" />
                            <span>{{ isCompletedLocal ? 'Lesson Completed ✓' : 'Mark as Completed' }}</span>
                        </button>

                        <!-- Next Lesson or Certificate Button -->
                        <Link
                            v-if="nextLesson"
                            :href="`/courses/${course.slug}/lessons/${nextLesson.slug}`"
                            class="inline-flex items-center justify-center gap-2 rounded-xl bg-zinc-900 hover:bg-zinc-800 dark:bg-zinc-100 dark:hover:bg-white text-white dark:text-zinc-900 py-2.5 px-5 text-xs font-bold transition-all shadow-md w-full sm:w-auto"
                        >
                            <span>Next Lesson</span>
                            <ChevronRight class="size-4" />
                        </Link>

                        <Link
                            v-else-if="course.completed_at || isCompletedLocal"
                            :href="`/courses/${course.slug}/certificate`"
                            class="inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-amber-500 to-emerald-500 hover:from-amber-600 hover:to-emerald-600 text-white py-2.5 px-5 text-xs font-bold transition-all shadow-md w-full sm:w-auto"
                        >
                            <Award class="size-4" />
                            <span>Get Certificate!</span>
                        </Link>
                    </div>
                </div>
            </main>
        </div>
    </div>
</template>

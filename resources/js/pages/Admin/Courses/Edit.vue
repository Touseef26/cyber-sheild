<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import {
    AlertTriangle,
    ArrowLeft,
    Check,
    CheckCircle2,
    Clock,
    Eye,
    FileText,
    HelpCircle,
    Layers,
    Play,
    PlayCircle,
    Plus,
    PlusCircle,
    Save,
    Settings,
    Shield,
    ShieldAlert,
    Trash2,
    X,
} from '@lucide/vue';
import { ref } from 'vue';

type QuizQuestion = {
    id: number;
    question: string;
    scenario: string | null;
    options: string[];
    correct_option_index: number;
    explanation: string | null;
};

type LessonItem = {
    id: number;
    title: string;
    slug: string;
    type: 'video' | 'article' | 'interactive_quiz';
    duration_minutes: number;
    video_url: string | null;
    content: string | null;
    key_takeaways: string[] | null;
    threat_level: 'low' | 'medium' | 'high' | 'critical';
    is_published: boolean;
    quiz_questions: QuizQuestion[];
};

type ModuleItem = {
    id: number;
    title: string;
    description: string | null;
    order: number;
    lessons: LessonItem[];
};

type CourseDetail = {
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
    is_published: boolean;
    modules: ModuleItem[];
};

type Props = {
    course: CourseDetail;
};

const props = defineProps<Props>();

defineOptions({
    layout: (props: any) => ({
        breadcrumbs: [
            {
                title: 'Course Studio',
                href: '/admin/courses',
            },
            {
                title: props?.course?.title || 'Edit Course',
                href: `/admin/courses/${props?.course?.id || ''}/edit`,
            },
        ],
    }),
});

const activeTab = ref<'curriculum' | 'settings'>('curriculum');

// Course Settings Form
const courseForm = useForm({
    title: props.course.title,
    category: props.course.category,
    level: props.course.level,
    summary: props.course.summary,
    description: props.course.description,
    thumbnail_url: props.course.thumbnail_url || '',
    estimated_minutes: props.course.estimated_minutes,
    badge_name: props.course.badge_name || '',
    is_published: props.course.is_published,
});

const saveCourseSettings = () => {
    courseForm.put(`/admin/courses/${props.course.id}`);
};

// Add Module Modal
const showModuleModal = ref(false);
const moduleForm = useForm({
    title: '',
    description: '',
});

const submitModule = () => {
    moduleForm.post(`/admin/courses/${props.course.id}/modules`, {
        onSuccess: () => {
            showModuleModal.value = false;
            moduleForm.reset();
        },
    });
};

const deleteModule = (mod: ModuleItem) => {
    if (confirm(`Delete section "${mod.title}" and all its lessons?`)) {
        router.delete(`/admin/modules/${mod.id}`);
    }
};

// Add Lesson Modal
const showLessonModal = ref(false);
const activeModuleId = ref<number | null>(null);
const takeawayInput = ref('');

const lessonForm = useForm({
    title: '',
    type: 'video',
    duration_minutes: 5,
    video_url: '',
    content: '',
    threat_level: 'medium',
    key_takeaways: [] as string[],
});

const openAddLesson = (moduleId: number) => {
    activeModuleId.value = moduleId;
    lessonForm.reset();
    lessonForm.key_takeaways = [];
    showLessonModal.value = true;
};

const addTakeaway = () => {
    if (takeawayInput.value.trim()) {
        lessonForm.key_takeaways.push(takeawayInput.value.trim());
        takeawayInput.value = '';
    }
};

const removeTakeaway = (index: number) => {
    lessonForm.key_takeaways.splice(index, 1);
};

const submitLesson = () => {
    if (!activeModuleId.value) return;
    lessonForm.post(`/admin/modules/${activeModuleId.value}/lessons`, {
        onSuccess: () => {
            showLessonModal.value = false;
            lessonForm.reset();
        },
    });
};

const deleteLesson = (lesson: LessonItem) => {
    if (confirm(`Delete lesson "${lesson.title}"?`)) {
        router.delete(`/admin/lessons/${lesson.id}`);
    }
};

// Add Quiz Question Modal
const showQuizModal = ref(false);
const activeLessonId = ref<number | null>(null);

const quizForm = useForm({
    question: '',
    scenario: '',
    options: ['', '', '', ''],
    correct_option_index: 0,
    explanation: '',
});

const openAddQuiz = (lessonId: number) => {
    activeLessonId.value = lessonId;
    quizForm.reset();
    quizForm.options = ['', '', '', ''];
    quizForm.correct_option_index = 0;
    showQuizModal.value = true;
};

const submitQuiz = () => {
    if (!activeLessonId.value) return;
    quizForm.post(`/admin/lessons/${activeLessonId.value}/quizzes`, {
        onSuccess: () => {
            showQuizModal.value = false;
            quizForm.reset();
        },
    });
};

const deleteQuizQuestion = (qId: number) => {
    if (confirm('Delete this scenario question?')) {
        router.delete(`/admin/quizzes/${qId}`);
    }
};
</script>

<template>
    <Head :title="`Edit: ${course.title}`" />

    <div class="flex flex-1 flex-col gap-6 p-4 md:p-8 max-w-7xl mx-auto w-full">
        <!-- Studio Top Bar -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-zinc-200 dark:border-zinc-800">
            <div class="space-y-1">
                <Link
                    href="/admin/courses"
                    class="inline-flex items-center gap-1 text-xs font-semibold text-zinc-500 hover:text-zinc-900 dark:hover:text-zinc-100 transition-colors"
                >
                    <ArrowLeft class="size-3.5" />
                    <span>Back to All Courses</span>
                </Link>
                <h1 class="text-2xl font-extrabold text-zinc-900 dark:text-zinc-100">
                    {{ course.title }}
                </h1>
            </div>

            <div class="flex items-center gap-3">
                <Link
                    :href="`/courses/${course.slug}`"
                    target="_blank"
                    class="inline-flex items-center gap-1.5 rounded-xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 px-4 py-2 text-xs font-semibold text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-colors"
                >
                    <Eye class="size-3.5" />
                    <span>Preview Learner Mode</span>
                </Link>
            </div>
        </div>

        <!-- Tab Buttons -->
        <div class="flex items-center gap-2 border-b border-zinc-200 dark:border-zinc-800 pb-2">
            <button
                @click="activeTab = 'curriculum'"
                :class="[
                    'inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold transition-all cursor-pointer',
                    activeTab === 'curriculum'
                        ? 'bg-emerald-600 text-white shadow-xs'
                        : 'text-zinc-600 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-800'
                ]"
            >
                <Layers class="size-4" />
                <span>Curriculum & Lessons ({{ course.modules.length }} Sections)</span>
            </button>

            <button
                @click="activeTab = 'settings'"
                :class="[
                    'inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold transition-all cursor-pointer',
                    activeTab === 'settings'
                        ? 'bg-emerald-600 text-white shadow-xs'
                        : 'text-zinc-600 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-800'
                ]"
            >
                <Settings class="size-4" />
                <span>Course Metadata & Settings</span>
            </button>
        </div>

        <!-- TAB 1: CURRICULUM BUILDER -->
        <div v-if="activeTab === 'curriculum'" class="space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-bold text-zinc-900 dark:text-zinc-100">
                        Course Curriculum Builder
                    </h2>
                    <p class="text-xs text-zinc-500">
                        Group training into sections, add video or text guides, and craft interactive scenario drills.
                    </p>
                </div>

                <button
                    @click="showModuleModal = true"
                    class="inline-flex items-center gap-1.5 rounded-xl bg-zinc-900 hover:bg-zinc-800 dark:bg-zinc-100 dark:hover:bg-white text-white dark:text-zinc-900 px-4 py-2 text-xs font-bold shadow-xs cursor-pointer"
                >
                    <Plus class="size-3.5" />
                    <span>Add Section</span>
                </button>
            </div>

            <!-- Modules List -->
            <div class="space-y-6">
                <div
                    v-for="(mod, mIdx) in course.modules"
                    :key="mod.id"
                    class="rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900/60 shadow-xs overflow-hidden"
                >
                    <!-- Section Header -->
                    <div class="p-4 sm:p-5 bg-zinc-50/70 dark:bg-zinc-950/60 border-b border-zinc-100 dark:border-zinc-800/80 flex items-center justify-between">
                        <div>
                            <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-400">
                                Section 0{{ mIdx + 1 }}
                            </span>
                            <h3 class="text-base font-bold text-zinc-900 dark:text-zinc-100">
                                {{ mod.title }}
                            </h3>
                            <p v-if="mod.description" class="text-xs text-zinc-500">
                                {{ mod.description }}
                            </p>
                        </div>

                        <div class="flex items-center gap-2">
                            <button
                                @click="openAddLesson(mod.id)"
                                class="inline-flex items-center gap-1 rounded-xl bg-emerald-600/10 hover:bg-emerald-600/20 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 px-3 py-1.5 text-xs font-bold cursor-pointer"
                            >
                                <Plus class="size-3.5" />
                                <span>Add Lesson</span>
                            </button>

                            <button
                                @click="deleteModule(mod)"
                                class="rounded-xl p-1.5 text-zinc-400 hover:text-red-500 hover:bg-red-500/10 transition-colors cursor-pointer"
                                title="Delete Section"
                            >
                                <Trash2 class="size-4" />
                            </button>
                        </div>
                    </div>

                    <!-- Lessons in Section -->
                    <div v-if="mod.lessons.length > 0" class="divide-y divide-zinc-100 dark:divide-zinc-800/60">
                        <div
                            v-for="(l, lIdx) in mod.lessons"
                            :key="l.id"
                            class="p-4 sm:p-5 flex flex-col md:flex-row md:items-center justify-between gap-4 hover:bg-zinc-50/50 dark:hover:bg-zinc-800/20 transition-colors"
                        >
                            <div class="flex items-start gap-3">
                                <div class="rounded-xl bg-zinc-100 dark:bg-zinc-800 p-2.5 text-zinc-600 dark:text-zinc-300 shrink-0">
                                    <PlayCircle v-if="l.type === 'video'" class="size-5 text-emerald-500" />
                                    <HelpCircle v-else-if="l.type === 'interactive_quiz'" class="size-5 text-purple-500" />
                                    <FileText v-else class="size-5 text-sky-500" />
                                </div>

                                <div>
                                    <div class="flex items-center gap-2">
                                        <h4 class="text-sm font-bold text-zinc-900 dark:text-zinc-100">
                                            {{ l.title }}
                                        </h4>
                                        <span class="rounded px-1.5 py-0.2 text-[10px] font-semibold uppercase bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-300">
                                            {{ l.threat_level }}
                                        </span>
                                    </div>

                                    <p class="text-xs text-zinc-500 mt-0.5">
                                        {{ l.type === 'video' ? 'Video Lesson' : l.type === 'interactive_quiz' ? 'Interactive Scenario Challenge' : 'Article Guide' }} • {{ l.duration_minutes }} mins
                                        <span v-if="l.quiz_questions && l.quiz_questions.length > 0" class="text-purple-500 font-semibold ml-1">
                                            ({{ l.quiz_questions.length }} quiz scenarios)
                                        </span>
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-center gap-2 self-end md:self-center">
                                <button
                                    @click="openAddQuiz(l.id)"
                                    class="inline-flex items-center gap-1 rounded-lg border border-purple-500/20 bg-purple-500/10 hover:bg-purple-500/20 text-purple-600 dark:text-purple-400 px-3 py-1.5 text-xs font-semibold cursor-pointer"
                                >
                                    <HelpCircle class="size-3.5" />
                                    <span>Add Quiz Question</span>
                                </button>

                                <button
                                    @click="deleteLesson(l)"
                                    class="rounded-lg p-1.5 text-zinc-400 hover:text-red-500 hover:bg-red-500/10 transition-colors cursor-pointer"
                                    title="Delete Lesson"
                                >
                                    <Trash2 class="size-4" />
                                </button>
                            </div>
                        </div>
                    </div>

                    <div v-else class="p-6 text-center text-xs text-zinc-400">
                        No lessons in this section yet. Click "Add Lesson" to start building training content.
                    </div>
                </div>
            </div>
        </div>

        <!-- TAB 2: COURSE SETTINGS FORM -->
        <div v-if="activeTab === 'settings'" class="rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900/60 p-6 max-w-3xl space-y-6">
            <h2 class="text-lg font-bold text-zinc-900 dark:text-zinc-100">
                Course Metadata & Badging
            </h2>

            <form @submit.prevent="saveCourseSettings" class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-zinc-700 dark:text-zinc-300 mb-1">Title *</label>
                    <input
                        v-model="courseForm.title"
                        type="text"
                        required
                        class="w-full rounded-xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-950 p-2.5 text-xs text-zinc-900 dark:text-zinc-100"
                    />
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-zinc-700 dark:text-zinc-300 mb-1">Category *</label>
                        <input
                            v-model="courseForm.category"
                            type="text"
                            required
                            class="w-full rounded-xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-950 p-2.5 text-xs text-zinc-900 dark:text-zinc-100"
                        />
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-zinc-700 dark:text-zinc-300 mb-1">Level *</label>
                        <select
                            v-model="courseForm.level"
                            class="w-full rounded-xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-950 p-2.5 text-xs text-zinc-900 dark:text-zinc-100"
                        >
                            <option value="beginner">Beginner</option>
                            <option value="intermediate">Intermediate</option>
                            <option value="advanced">Advanced</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-zinc-700 dark:text-zinc-300 mb-1">Summary *</label>
                    <textarea
                        v-model="courseForm.summary"
                        rows="2"
                        required
                        class="w-full rounded-xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-950 p-2.5 text-xs text-zinc-900 dark:text-zinc-100"
                    />
                </div>

                <div>
                    <label class="block text-xs font-bold text-zinc-700 dark:text-zinc-300 mb-1">Full Description *</label>
                    <textarea
                        v-model="courseForm.description"
                        rows="4"
                        required
                        class="w-full rounded-xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-950 p-2.5 text-xs text-zinc-900 dark:text-zinc-100"
                    />
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-zinc-700 dark:text-zinc-300 mb-1">Estimated Total Minutes</label>
                        <input
                            v-model.number="courseForm.estimated_minutes"
                            type="number"
                            min="1"
                            class="w-full rounded-xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-950 p-2.5 text-xs text-zinc-900 dark:text-zinc-100"
                        />
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-zinc-700 dark:text-zinc-300 mb-1">Award Badge Title</label>
                        <input
                            v-model="courseForm.badge_name"
                            type="text"
                            class="w-full rounded-xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-950 p-2.5 text-xs text-zinc-900 dark:text-zinc-100"
                        />
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-zinc-700 dark:text-zinc-300 mb-1">Thumbnail URL</label>
                    <input
                        v-model="courseForm.thumbnail_url"
                        type="text"
                        class="w-full rounded-xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-950 p-2.5 text-xs text-zinc-900 dark:text-zinc-100"
                    />
                </div>

                <div class="flex items-center gap-2 pt-2">
                    <input
                        type="checkbox"
                        id="published"
                        v-model="courseForm.is_published"
                        class="rounded text-emerald-500 focus:ring-emerald-500 size-4"
                    />
                    <label for="published" class="text-xs font-bold text-zinc-700 dark:text-zinc-300">
                        Course Published & Visible to Learners
                    </label>
                </div>

                <div class="pt-4 border-t border-zinc-100 dark:border-zinc-800 flex justify-end">
                    <button
                        type="submit"
                        :disabled="courseForm.processing"
                        class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white px-5 py-2 text-xs font-bold shadow-md cursor-pointer"
                    >
                        <Save class="size-4" />
                        <span>Save Changes</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- MODAL: ADD SECTION/MODULE -->
        <div
            v-if="showModuleModal"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-zinc-950/70 backdrop-blur-xs"
        >
            <div class="w-full max-w-md rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-6 shadow-2xl space-y-4">
                <div class="flex items-center justify-between border-b border-zinc-100 dark:border-zinc-800 pb-3">
                    <h3 class="text-base font-bold text-zinc-900 dark:text-zinc-100">
                        Add Curriculum Section
                    </h3>
                    <button @click="showModuleModal = false" class="text-zinc-400">
                        <X class="size-4" />
                    </button>
                </div>

                <form @submit.prevent="submitModule" class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-zinc-700 dark:text-zinc-300 mb-1">Section Title *</label>
                        <input
                            v-model="moduleForm.title"
                            type="text"
                            required
                            placeholder="e.g. Module 2: Recognizing Quishing & Malicious QR Codes"
                            class="w-full rounded-xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-950 p-2.5 text-xs text-zinc-900 dark:text-zinc-100"
                        />
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-zinc-700 dark:text-zinc-300 mb-1">Description (Optional)</label>
                        <textarea
                            v-model="moduleForm.description"
                            rows="2"
                            placeholder="Summary of what this module covers..."
                            class="w-full rounded-xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-950 p-2.5 text-xs text-zinc-900 dark:text-zinc-100"
                        />
                    </div>

                    <div class="flex justify-end gap-2 pt-3 border-t border-zinc-100 dark:border-zinc-800">
                        <button
                            type="button"
                            @click="showModuleModal = false"
                            class="rounded-xl border border-zinc-200 dark:border-zinc-800 px-4 py-2 text-xs font-medium text-zinc-600 dark:text-zinc-400"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            :disabled="moduleForm.processing"
                            class="rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white px-5 py-2 text-xs font-bold shadow-md cursor-pointer"
                        >
                            Add Section
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- MODAL: ADD LESSON -->
        <div
            v-if="showLessonModal"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-zinc-950/70 backdrop-blur-xs"
        >
            <div class="w-full max-w-2xl rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-6 shadow-2xl space-y-4 max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between border-b border-zinc-100 dark:border-zinc-800 pb-3">
                    <h3 class="text-base font-bold text-zinc-900 dark:text-zinc-100">
                        Add Lesson to Section
                    </h3>
                    <button @click="showLessonModal = false" class="text-zinc-400">
                        <X class="size-4" />
                    </button>
                </div>

                <form @submit.prevent="submitLesson" class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-zinc-700 dark:text-zinc-300 mb-1">Lesson Title *</label>
                        <input
                            v-model="lessonForm.title"
                            type="text"
                            required
                            placeholder="e.g. Recognizing Malicious Macro Attachments"
                            class="w-full rounded-xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-950 p-2.5 text-xs text-zinc-900 dark:text-zinc-100"
                        />
                    </div>

                    <div class="grid grid-cols-3 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-zinc-700 dark:text-zinc-300 mb-1">Content Type *</label>
                            <select
                                v-model="lessonForm.type"
                                class="w-full rounded-xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-950 p-2.5 text-xs text-zinc-900 dark:text-zinc-100"
                            >
                                <option value="video">Video Briefing</option>
                                <option value="article">Article / Guide</option>
                                <option value="interactive_quiz">Scenario Challenge</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-zinc-700 dark:text-zinc-300 mb-1">Threat Level *</label>
                            <select
                                v-model="lessonForm.threat_level"
                                class="w-full rounded-xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-950 p-2.5 text-xs text-zinc-900 dark:text-zinc-100"
                            >
                                <option value="low">Low Risk</option>
                                <option value="medium">Medium Risk</option>
                                <option value="high">High Risk</option>
                                <option value="critical">Critical Threat</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-zinc-700 dark:text-zinc-300 mb-1">Duration (Mins) *</label>
                            <input
                                v-model.number="lessonForm.duration_minutes"
                                type="number"
                                min="1"
                                class="w-full rounded-xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-950 p-2.5 text-xs text-zinc-900 dark:text-zinc-100"
                            />
                        </div>
                    </div>

                    <div v-if="lessonForm.type === 'video'">
                        <label class="block text-xs font-bold text-zinc-700 dark:text-zinc-300 mb-1">Video Embed URL (YouTube/Vimeo/MP4)</label>
                        <input
                            v-model="lessonForm.video_url"
                            type="url"
                            placeholder="https://www.youtube.com/embed/..."
                            class="w-full rounded-xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-950 p-2.5 text-xs text-zinc-900 dark:text-zinc-100"
                        />
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-zinc-700 dark:text-zinc-300 mb-1">Lesson Text / Markdown Content</label>
                        <textarea
                            v-model="lessonForm.content"
                            rows="4"
                            placeholder="Detailed explanations, security guidelines, domain breakdown..."
                            class="w-full rounded-xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-950 p-2.5 text-xs text-zinc-900 dark:text-zinc-100 font-mono"
                        />
                    </div>

                    <!-- Key Takeaways Builder -->
                    <div>
                        <label class="block text-xs font-bold text-zinc-700 dark:text-zinc-300 mb-1">Key Takeaways (Rules to remember)</label>
                        <div class="flex items-center gap-2 mb-2">
                            <input
                                v-model="takeawayInput"
                                @keydown.enter.prevent="addTakeaway"
                                type="text"
                                placeholder="e.g. Never enable macros on unsolicited documents"
                                class="flex-1 rounded-xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-950 p-2 text-xs text-zinc-900 dark:text-zinc-100"
                            />
                            <button
                                type="button"
                                @click="addTakeaway"
                                class="rounded-xl bg-zinc-900 dark:bg-zinc-100 text-white dark:text-zinc-900 px-3 py-2 text-xs font-bold"
                            >
                                Add
                            </button>
                        </div>

                        <div v-if="lessonForm.key_takeaways.length > 0" class="space-y-1">
                            <div
                                v-for="(t, tIdx) in lessonForm.key_takeaways"
                                :key="tIdx"
                                class="flex items-center justify-between rounded-lg bg-zinc-100 dark:bg-zinc-800 px-3 py-1.5 text-xs"
                            >
                                <span class="truncate">{{ t }}</span>
                                <button @click="removeTakeaway(tIdx)" class="text-zinc-400 hover:text-red-500">
                                    <X class="size-3.5" />
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end gap-2 pt-3 border-t border-zinc-100 dark:border-zinc-800">
                        <button
                            type="button"
                            @click="showLessonModal = false"
                            class="rounded-xl border border-zinc-200 dark:border-zinc-800 px-4 py-2 text-xs font-medium text-zinc-600 dark:text-zinc-400"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            :disabled="lessonForm.processing"
                            class="rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white px-5 py-2 text-xs font-bold shadow-md cursor-pointer"
                        >
                            Save Lesson
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- MODAL: ADD QUIZ QUESTION -->
        <div
            v-if="showQuizModal"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-zinc-950/70 backdrop-blur-xs"
        >
            <div class="w-full max-w-xl rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-6 shadow-2xl space-y-4 max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between border-b border-zinc-100 dark:border-zinc-800 pb-3">
                    <h3 class="text-base font-bold text-zinc-900 dark:text-zinc-100">
                        Add Interactive Scenario Question
                    </h3>
                    <button @click="showQuizModal = false" class="text-zinc-400">
                        <X class="size-4" />
                    </button>
                </div>

                <form @submit.prevent="submitQuiz" class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-zinc-700 dark:text-zinc-300 mb-1">
                            Scenario Context (Optional)
                        </label>
                        <textarea
                            v-model="quizForm.scenario"
                            rows="2"
                            placeholder="e.g. You notice a suspicious USB drive labeled 'Executive Compensation' dropped in the elevator lobby..."
                            class="w-full rounded-xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-950 p-2.5 text-xs text-zinc-900 dark:text-zinc-100"
                        />
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-zinc-700 dark:text-zinc-300 mb-1">
                            Question Statement *
                        </label>
                        <input
                            v-model="quizForm.question"
                            type="text"
                            required
                            placeholder="What is the proper defensive protocol?"
                            class="w-full rounded-xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-950 p-2.5 text-xs text-zinc-900 dark:text-zinc-100"
                        />
                    </div>

                    <div class="space-y-2">
                        <label class="block text-xs font-bold text-zinc-700 dark:text-zinc-300">
                            Answer Options & Correct Selection *
                        </label>
                        <div
                            v-for="(_, optIdx) in quizForm.options"
                            :key="optIdx"
                            class="flex items-center gap-2"
                        >
                            <input
                                type="radio"
                                name="correct_option"
                                :checked="quizForm.correct_option_index === optIdx"
                                @change="quizForm.correct_option_index = optIdx"
                                class="text-emerald-500 focus:ring-emerald-500 size-4"
                            />
                            <span class="text-xs font-bold text-zinc-400 w-4">
                                {{ ['A', 'B', 'C', 'D'][optIdx] }}
                            </span>
                            <input
                                v-model="quizForm.options[optIdx]"
                                type="text"
                                required
                                :placeholder="`Option ${['A', 'B', 'C', 'D'][optIdx]}`"
                                class="flex-1 rounded-xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-950 p-2 text-xs text-zinc-900 dark:text-zinc-100"
                            />
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-zinc-700 dark:text-zinc-300 mb-1">
                            Explanation (Shown after answering)
                        </label>
                        <textarea
                            v-model="quizForm.explanation"
                            rows="2"
                            placeholder="Explain why the correct answer neutralizes the threat..."
                            class="w-full rounded-xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-950 p-2.5 text-xs text-zinc-900 dark:text-zinc-100"
                        />
                    </div>

                    <div class="flex justify-end gap-2 pt-3 border-t border-zinc-100 dark:border-zinc-800">
                        <button
                            type="button"
                            @click="showQuizModal = false"
                            class="rounded-xl border border-zinc-200 dark:border-zinc-800 px-4 py-2 text-xs font-medium text-zinc-600 dark:text-zinc-400"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            :disabled="quizForm.processing"
                            class="rounded-xl bg-purple-600 hover:bg-purple-500 text-white px-5 py-2 text-xs font-bold shadow-md cursor-pointer"
                        >
                            Save Scenario Question
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

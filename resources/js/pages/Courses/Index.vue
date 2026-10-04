<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import {
    Award,
    BookOpen,
    CheckCircle2,
    Clock,
    Filter,
    GraduationCap,
    Play,
    Search,
    Shield,
    ShieldCheck,
    Sparkles,
} from '@lucide/vue';
import { ref, watch } from 'vue';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Security Academy',
                href: '/courses',
            },
        ],
    },
});

type CourseItem = {
    id: number;
    title: string;
    slug: string;
    category: string;
    level: 'beginner' | 'intermediate' | 'advanced';
    summary: string;
    thumbnail_url: string | null;
    estimated_minutes: number;
    badge_name: string | null;
    modules_count: number;
    lessons_count: number;
    is_enrolled: boolean;
    progress_percentage: number;
    is_completed: boolean;
};

type Props = {
    courses: CourseItem[];
    categories: string[];
    filters: {
        search: string;
        category: string;
        level: string;
    };
    stats: {
        enrolled_count: number;
        completed_count: number;
        certificates_count: number;
        total_learning_minutes: number;
    };
};

const props = defineProps<Props>();

const search = ref(props.filters.search);
const selectedCategory = ref(props.filters.category);
const selectedLevel = ref(props.filters.level);

let searchTimeout: ReturnType<typeof setTimeout> | null = null;

const applyFilters = () => {
    router.get(
        '/courses',
        {
            search: search.value || undefined,
            category: selectedCategory.value !== 'all' ? selectedCategory.value : undefined,
            level: selectedLevel.value !== 'all' ? selectedLevel.value : undefined,
        },
        { preserveState: true, replace: true }
    );
};

watch(search, () => {
    if (searchTimeout) clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        applyFilters();
    }, 300);
});

const setCategory = (cat: string) => {
    selectedCategory.value = cat;
    applyFilters();
};

const setLevel = (lvl: string) => {
    selectedLevel.value = lvl;
    applyFilters();
};

const levelBadgeClass = (level: string) => {
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
    <Head title="CyberShield Security Awareness Academy" />

    <div class="flex flex-1 flex-col gap-8 p-4 md:p-8 max-w-7xl mx-auto w-full">
        <!-- Hero Section -->
        <div
            class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-zinc-900 via-zinc-950 to-emerald-950 p-6 md:p-10 text-white shadow-xl border border-zinc-800"
        >
            <div class="absolute -right-10 -bottom-10 opacity-15 pointer-events-none">
                <ShieldCheck class="size-72 text-emerald-400" />
            </div>

            <div class="relative z-10 max-w-3xl space-y-4">
                <div class="inline-flex items-center gap-2 rounded-full bg-emerald-500/20 px-3 py-1 text-xs font-medium text-emerald-300 border border-emerald-500/30">
                    <Sparkles class="size-3.5" />
                    <span>CyberShield Threat Awareness LMS</span>
                </div>

                <h1 class="text-3xl font-bold tracking-tight md:text-4xl">
                    Defense Through Knowledge & Real-World Simulations
                </h1>

                <p class="text-zinc-300 text-sm md:text-base leading-relaxed">
                    Sharpen your instincts against modern cyber threats. From phishing vectors and ransomware defense to passkeys and safe mobile connectivity, learn with interactive scenarios, high-impact video breakdowns, and certified threat assessments.
                </p>
            </div>

            <!-- Learning Metrics Grid -->
            <div class="mt-8 grid grid-cols-2 gap-3 sm:grid-cols-4 pt-6 border-t border-zinc-800/80">
                <div class="rounded-xl bg-zinc-900/60 p-3.5 border border-zinc-800/60 backdrop-blur-xs">
                    <div class="flex items-center gap-2 text-zinc-400 text-xs font-medium">
                        <BookOpen class="size-4 text-emerald-400" />
                        <span>Enrolled</span>
                    </div>
                    <p class="mt-1 text-2xl font-bold text-white">{{ stats.enrolled_count }}</p>
                </div>

                <div class="rounded-xl bg-zinc-900/60 p-3.5 border border-zinc-800/60 backdrop-blur-xs">
                    <div class="flex items-center gap-2 text-zinc-400 text-xs font-medium">
                        <CheckCircle2 class="size-4 text-sky-400" />
                        <span>Completed</span>
                    </div>
                    <p class="mt-1 text-2xl font-bold text-white">{{ stats.completed_count }}</p>
                </div>

                <div class="rounded-xl bg-zinc-900/60 p-3.5 border border-zinc-800/60 backdrop-blur-xs">
                    <div class="flex items-center gap-2 text-zinc-400 text-xs font-medium">
                        <Award class="size-4 text-amber-400" />
                        <span>Certificates</span>
                    </div>
                    <p class="mt-1 text-2xl font-bold text-white">{{ stats.certificates_count }}</p>
                </div>

                <div class="rounded-xl bg-zinc-900/60 p-3.5 border border-zinc-800/60 backdrop-blur-xs">
                    <div class="flex items-center gap-2 text-zinc-400 text-xs font-medium">
                        <Clock class="size-4 text-purple-400" />
                        <span>Time Trained</span>
                    </div>
                    <p class="mt-1 text-2xl font-bold text-white">{{ stats.total_learning_minutes }} <span class="text-xs text-zinc-400 font-normal">mins</span></p>
                </div>
            </div>
        </div>

        <!-- Search & Filter Controls -->
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <!-- Search Input -->
            <div class="relative flex-1 max-w-md">
                <Search class="absolute left-3.5 top-1/2 -translate-y-1/2 size-4 text-zinc-400" />
                <input
                    v-model="search"
                    type="text"
                    placeholder="Search courses, threats, topics..."
                    class="w-full rounded-xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900/80 py-2.5 pl-10 pr-4 text-sm text-zinc-900 dark:text-zinc-100 placeholder-zinc-400 focus:outline-hidden focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition-all shadow-xs"
                />
            </div>

            <!-- Level Filters -->
            <div class="flex items-center gap-1.5 overflow-x-auto pb-1 md:pb-0">
                <button
                    v-for="lvl in ['all', 'beginner', 'intermediate', 'advanced']"
                    :key="lvl"
                    @click="setLevel(lvl)"
                    :class="[
                        'px-3.5 py-1.5 rounded-lg text-xs font-medium capitalize transition-colors',
                        selectedLevel === lvl
                            ? 'bg-zinc-900 dark:bg-zinc-100 text-white dark:text-zinc-900 shadow-xs'
                            : 'bg-zinc-100 dark:bg-zinc-800/70 text-zinc-600 dark:text-zinc-400 hover:bg-zinc-200 dark:hover:bg-zinc-800'
                    ]"
                >
                    {{ lvl }}
                </button>
            </div>
        </div>

        <!-- Category Pills -->
        <div v-if="categories.length > 0" class="flex flex-wrap items-center gap-2">
            <span class="text-xs font-medium text-zinc-400 flex items-center gap-1">
                <Filter class="size-3" /> Categories:
            </span>
            <button
                @click="setCategory('all')"
                :class="[
                    'rounded-full px-3 py-1 text-xs transition-colors',
                    selectedCategory === 'all'
                        ? 'bg-emerald-600 text-white'
                        : 'bg-zinc-100 dark:bg-zinc-800/60 text-zinc-600 dark:text-zinc-300 hover:bg-zinc-200 dark:hover:bg-zinc-800'
                ]"
            >
                All Topics
            </button>
            <button
                v-for="cat in categories"
                :key="cat"
                @click="setCategory(cat)"
                :class="[
                    'rounded-full px-3 py-1 text-xs transition-colors',
                    selectedCategory === cat
                        ? 'bg-emerald-600 text-white'
                        : 'bg-zinc-100 dark:bg-zinc-800/60 text-zinc-600 dark:text-zinc-300 hover:bg-zinc-200 dark:hover:bg-zinc-800'
                ]"
            >
                {{ cat }}
            </button>
        </div>

        <!-- Courses Grid -->
        <div v-if="courses.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div
                v-for="course in courses"
                :key="course.id"
                class="group flex flex-col justify-between overflow-hidden rounded-2xl border border-zinc-200 dark:border-zinc-800/80 bg-white dark:bg-zinc-900/60 shadow-xs hover:shadow-lg hover:border-zinc-300 dark:hover:border-zinc-700 transition-all duration-300"
            >
                <div>
                    <!-- Thumbnail Header -->
                    <div class="relative aspect-video w-full overflow-hidden bg-zinc-950">
                        <img
                            :src="course.thumbnail_url || 'https://images.unsplash.com/photo-1563986768609-322da13575f3?auto=format&fit=crop&w=800&q=80'"
                            :alt="course.title"
                            class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
                        />
                        <div class="absolute inset-0 bg-gradient-to-t from-zinc-950/90 via-zinc-950/30 to-transparent" />

                        <!-- Badge & Category Overlay -->
                        <div class="absolute top-3 left-3 right-3 flex items-center justify-between">
                            <span
                                :class="[
                                    'rounded-md px-2.5 py-0.5 text-xs font-semibold uppercase tracking-wider border backdrop-blur-md',
                                    levelBadgeClass(course.level)
                                ]"
                            >
                                {{ course.level }}
                            </span>

                            <span
                                v-if="course.is_completed"
                                class="inline-flex items-center gap-1 rounded-md bg-emerald-500 px-2 py-0.5 text-xs font-medium text-white shadow-xs"
                            >
                                <CheckCircle2 class="size-3" />
                                <span>Certified</span>
                            </span>
                            <span
                                v-else-if="course.is_enrolled"
                                class="inline-flex items-center gap-1 rounded-md bg-sky-500/90 px-2 py-0.5 text-xs font-medium text-white backdrop-blur-md"
                            >
                                <span>In Progress</span>
                            </span>
                        </div>

                        <!-- Duration & Lessons Count -->
                        <div class="absolute bottom-3 left-3 right-3 flex items-center justify-between text-xs text-zinc-300">
                            <span class="font-medium text-emerald-400">{{ course.category }}</span>
                            <div class="flex items-center gap-2">
                                <span class="flex items-center gap-1">
                                    <Clock class="size-3.5" />
                                    {{ course.estimated_minutes }}m
                                </span>
                                <span>•</span>
                                <span>{{ course.lessons_count }} lessons</span>
                            </div>
                        </div>
                    </div>

                    <!-- Card Body -->
                    <div class="p-5 space-y-3">
                        <h3 class="text-lg font-bold text-zinc-900 dark:text-zinc-100 group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors line-clamp-1">
                            {{ course.title }}
                        </h3>

                        <p class="text-xs text-zinc-600 dark:text-zinc-400 line-clamp-2 leading-relaxed">
                            {{ course.summary }}
                        </p>

                        <!-- Progress Bar if Enrolled -->
                        <div v-if="course.is_enrolled" class="pt-2">
                            <div class="flex items-center justify-between text-xs mb-1.5 font-medium">
                                <span class="text-zinc-500 dark:text-zinc-400">Progress</span>
                                <span :class="course.is_completed ? 'text-emerald-500 font-bold' : 'text-zinc-800 dark:text-zinc-200'">
                                    {{ course.progress_percentage }}%
                                </span>
                            </div>
                            <div class="h-2 w-full overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-800">
                                <div
                                    class="h-full rounded-full transition-all duration-500"
                                    :class="course.is_completed ? 'bg-emerald-500' : 'bg-gradient-to-r from-emerald-500 to-sky-500'"
                                    :style="{ width: `${course.progress_percentage}%` }"
                                />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer Action -->
                <div class="p-5 pt-0">
                    <div class="flex items-center gap-2 pt-3 border-t border-zinc-100 dark:border-zinc-800/80">
                        <Link
                            :href="`/courses/${course.slug}`"
                            class="flex-1 inline-flex items-center justify-center gap-2 rounded-xl py-2 px-4 text-xs font-semibold transition-all duration-200"
                            :class="[
                                course.is_completed
                                    ? 'bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-zinc-900 dark:text-zinc-100'
                                    : course.is_enrolled
                                    ? 'bg-emerald-600 hover:bg-emerald-500 text-white shadow-xs'
                                    : 'bg-zinc-900 hover:bg-zinc-800 dark:bg-zinc-100 dark:hover:bg-white text-white dark:text-zinc-900 shadow-xs'
                            ]"
                        >
                            <Play v-if="!course.is_completed" class="size-3.5 fill-current" />
                            <span>{{ course.is_completed ? 'Review Curriculum' : course.is_enrolled ? 'Continue Course' : 'Start Learning' }}</span>
                        </Link>

                        <Link
                            v-if="course.is_completed"
                            :href="`/courses/${course.slug}/certificate`"
                            title="View Verified Certificate"
                            class="inline-flex items-center justify-center rounded-xl p-2 bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 transition-colors"
                        >
                            <Award class="size-4" />
                        </Link>
                    </div>
                </div>
            </div>
        </div>

        <!-- Empty State -->
        <div
            v-else
            class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-zinc-300 dark:border-zinc-800 p-12 text-center"
        >
            <div class="rounded-full bg-zinc-100 dark:bg-zinc-800 p-3 text-zinc-500 mb-3">
                <Shield class="size-8" />
            </div>
            <h3 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">No security courses found</h3>
            <p class="text-xs text-zinc-500 mt-1 max-w-sm">
                Try adjusting your search criteria or topic filters to find courses.
            </p>
            <button
                @click="search = ''; selectedCategory = 'all'; selectedLevel = 'all'; applyFilters()"
                class="mt-4 rounded-xl bg-emerald-600 px-4 py-2 text-xs font-medium text-white hover:bg-emerald-500"
            >
                Reset Filters
            </button>
        </div>
    </div>
</template>

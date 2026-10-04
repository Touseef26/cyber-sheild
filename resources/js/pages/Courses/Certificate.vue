<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    ArrowLeft,
    Award,
    CheckCircle2,
    Download,
    Printer,
    Share2,
    Shield,
    ShieldCheck,
    Sparkles,
} from '@lucide/vue';

type Props = {
    course: {
        id: number;
        title: string;
        slug: string;
        category: string;
        badge_name: string | null;
        level: string;
        estimated_minutes: number;
    };
    user: {
        name: string;
        email: string;
    };
    certificate: {
        code: string;
        completed_at: string;
        issued_by: string;
    };
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
                title: 'Certificate of Achievement',
                href: `/courses/${props?.course?.slug || ''}/certificate`,
            },
        ],
    }),
});

const printCertificate = () => {
    window.print();
};
</script>

<template>
    <Head :title="`Certificate of Completion - ${course.title}`" />

    <div class="flex flex-1 flex-col gap-8 p-4 md:p-8 max-w-5xl mx-auto w-full">
        <!-- Header Controls (Hidden on Print) -->
        <div class="flex items-center justify-between print:hidden">
            <Link
                :href="`/courses/${course.slug}`"
                class="inline-flex items-center gap-1.5 text-xs font-semibold text-zinc-500 hover:text-zinc-900 dark:hover:text-zinc-100 transition-colors"
            >
                <ArrowLeft class="size-4" />
                <span>Back to Course Outline</span>
            </Link>

            <div class="flex items-center gap-3">
                <button
                    @click="printCertificate"
                    class="inline-flex items-center gap-2 rounded-xl bg-zinc-900 hover:bg-zinc-800 dark:bg-zinc-100 dark:hover:bg-white text-white dark:text-zinc-900 px-4 py-2 text-xs font-bold shadow-md transition-all cursor-pointer"
                >
                    <Printer class="size-3.5" />
                    <span>Print or Save PDF</span>
                </button>
            </div>
        </div>

        <!-- Certificate Canvas -->
        <div
            class="relative overflow-hidden rounded-3xl border-8 border-double border-zinc-300 dark:border-zinc-700 bg-gradient-to-b from-white via-zinc-50 to-zinc-100 dark:from-zinc-900 dark:via-zinc-950 dark:to-zinc-900 p-8 sm:p-14 shadow-2xl text-center space-y-8"
        >
            <!-- Watermark Background -->
            <div class="absolute inset-0 flex items-center justify-center opacity-5 pointer-events-none">
                <ShieldCheck class="size-96 text-emerald-500" />
            </div>

            <!-- Top Header Crest -->
            <div class="relative z-10 flex flex-col items-center space-y-2">
                <div class="inline-flex items-center justify-center rounded-2xl bg-emerald-500/10 p-3.5 border border-emerald-500/30 text-emerald-500 shadow-md">
                    <ShieldCheck class="size-10" />
                </div>

                <div class="space-y-0.5">
                    <span class="text-xs font-bold uppercase tracking-[0.25em] text-emerald-600 dark:text-emerald-400">
                        CyberShield Threat Awareness Academy
                    </span>
                    <h1 class="text-2xl sm:text-4xl font-extrabold uppercase tracking-tight text-zinc-900 dark:text-zinc-100 font-serif">
                        Certificate of Achievement
                    </h1>
                </div>
            </div>

            <!-- Recipient Info -->
            <div class="relative z-10 space-y-2 max-w-xl mx-auto">
                <p class="text-xs uppercase tracking-widest text-zinc-400">
                    This is to officially certify that
                </p>
                <h2 class="text-3xl sm:text-4xl font-black text-zinc-900 dark:text-zinc-100 underline decoration-emerald-500/40 decoration-2 underline-offset-8">
                    {{ user.name }}
                </h2>
                <p class="text-xs text-zinc-500 dark:text-zinc-400 pt-1">
                    has demonstrated cyber vigilance and successfully mastered all training simulations for
                </p>
            </div>

            <!-- Course Title & Badge -->
            <div class="relative z-10 rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-white/60 dark:bg-zinc-900/60 p-6 max-w-2xl mx-auto shadow-xs backdrop-blur-xs space-y-2">
                <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-400">
                    {{ course.category }} • {{ course.level }}
                </span>
                <h3 class="text-xl sm:text-2xl font-bold text-zinc-900 dark:text-zinc-100">
                    {{ course.title }}
                </h3>
                <div class="inline-flex items-center gap-1.5 rounded-full bg-emerald-500/10 px-3 py-1 text-xs font-semibold text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                    <Sparkles class="size-3.5" />
                    <span>Awarded Designation: {{ course.badge_name || 'Cyber Defender' }}</span>
                </div>
            </div>

            <!-- Verification Footer Details -->
            <div class="relative z-10 pt-6 border-t border-zinc-200 dark:border-zinc-800 max-w-2xl mx-auto grid grid-cols-1 sm:grid-cols-3 gap-6 text-left">
                <div>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-zinc-400 block">
                        Date Completed
                    </span>
                    <span class="text-xs font-bold text-zinc-800 dark:text-zinc-200">
                        {{ certificate.completed_at }}
                    </span>
                </div>

                <div class="sm:text-center">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-zinc-400 block">
                        Verification ID
                    </span>
                    <span class="text-xs font-mono font-bold text-emerald-600 dark:text-emerald-400 tracking-wider">
                        {{ certificate.code }}
                    </span>
                </div>

                <div class="sm:text-right">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-zinc-400 block">
                        Issuing Authority
                    </span>
                    <span class="text-xs font-bold text-zinc-800 dark:text-zinc-200">
                        {{ certificate.issued_by }}
                    </span>
                </div>
            </div>
        </div>
    </div>
</template>

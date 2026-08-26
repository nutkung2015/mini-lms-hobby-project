<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    enrollments: Object,
});

const activeCount = props.enrollments.data?.filter(e => e.status === 'active').length || 0;
const completedCount = props.enrollments.data?.filter(e => e.status === 'completed').length || 0;
</script>

<template>
    <Head :title="$t('dashboard.student_title')" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-extrabold text-slate-900">{{ $t('dashboard.student_header') }}</h1>
                    <p class="text-xs text-slate-500 mt-1">{{ $t('dashboard.student_subtitle') }}</p>
                </div>
                <Link
                    :href="route('courses.index')"
                    class="px-4 py-2 bg-indigo-600 text-white text-xs font-bold rounded-xl hover:bg-indigo-500 shadow-sm transition"
                >
                    {{ $t('dashboard.explore_new_courses') }}
                </Link>
            </div>
        </template>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-8">
            <!-- Stats Counters -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-xs flex items-center space-x-4">
                    <div class="w-12 h-12 rounded-2xl bg-indigo-100 text-indigo-600 flex items-center justify-center text-xs font-black">
                        {{ $t('common.all') }}
                    </div>
                    <div>
                        <p class="text-xs text-slate-400 font-semibold">{{ $t('dashboard.enrolled_courses') }}</p>
                        <p class="text-2xl font-black text-slate-900">{{ enrollments.total || 0 }}</p>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-xs flex items-center space-x-4">
                    <div class="w-12 h-12 rounded-2xl bg-amber-100 text-amber-600 flex items-center justify-center text-xs font-black">
                        {{ $t('dashboard.active_status') }}
                    </div>
                    <div>
                        <p class="text-xs text-slate-400 font-semibold">{{ $t('dashboard.in_progress') }}</p>
                        <p class="text-2xl font-black text-amber-600">{{ activeCount }}</p>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-xs flex items-center space-x-4">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-100 text-emerald-600 flex items-center justify-center text-xs font-black">
                        {{ $t('dashboard.completed_status') }}
                    </div>
                    <div>
                        <p class="text-xs text-slate-400 font-semibold">{{ $t('dashboard.completed') }}</p>
                        <p class="text-2xl font-black text-emerald-600">{{ completedCount }}</p>
                    </div>
                </div>
            </div>

            <!-- Enrolled Courses List -->
            <div class="bg-white rounded-3xl border border-slate-200 shadow-xs p-8 space-y-6">
                <h2 class="text-lg font-bold text-slate-900">{{ $t('dashboard.my_learning_courses') }}</h2>

                <div v-if="enrollments.data && enrollments.data.length > 0" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div
                        v-for="item in enrollments.data"
                        :key="item.id"
                        class="p-5 rounded-2xl border border-slate-200 hover:shadow-md transition duration-200 flex flex-col justify-between space-y-4"
                    >
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-semibold text-indigo-600 bg-indigo-50 px-2.5 py-0.5 rounded-md">
                                    {{ item.course?.category?.name }}
                                </span>
                                <span
                                    class="text-[10px] font-bold uppercase px-2 py-0.5 rounded-full"
                                    :class="item.status === 'completed' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700'"
                                >
                                    {{ item.status === 'completed' ? $t('dashboard.completed_status') : $t('dashboard.active_status') }}
                                </span>
                            </div>

                            <h3 class="font-bold text-base text-slate-900 line-clamp-1">
                                {{ item.course?.title }}
                            </h3>

                            <p class="text-xs text-slate-500">
                                {{ $t('dashboard.instructor_prefix') }}: {{ item.course?.instructor?.name }}
                            </p>
                        </div>

                        <!-- Progress Bar -->
                        <div class="space-y-1.5">
                            <div class="flex justify-between text-xs font-semibold text-slate-600">
                                <span>{{ $t('dashboard.progress') }}</span>
                                <span class="font-bold text-indigo-600">{{ item.progress_pct }}%</span>
                            </div>
                            <div class="w-full h-2 bg-slate-100 rounded-full overflow-hidden">
                                <div
                                    class="h-full bg-gradient-to-r from-indigo-500 to-emerald-500 rounded-full transition-all duration-300"
                                    :style="{ width: `${item.progress_pct}%` }"
                                ></div>
                            </div>
                        </div>

                        <!-- Action Button -->
                        <div class="pt-2">
                            <Link
                                v-if="item.course?.lessons && item.course.lessons.length > 0"
                                :href="route('lessons.show', [item.course.slug, item.course.lessons[0].id])"
                                class="w-full text-center block py-2.5 px-4 rounded-xl bg-slate-900 text-white text-xs font-bold hover:bg-indigo-600 transition"
                            >
                                {{ item.status === 'completed' ? $t('dashboard.review_lessons') : $t('dashboard.continue_learning') }}
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-else class="text-center py-12 space-y-3">
                    <p class="text-slate-400 text-sm">{{ $t('dashboard.no_enrollments') }}</p>
                    <Link
                        :href="route('courses.index')"
                        class="inline-block px-5 py-2.5 bg-indigo-600 text-white text-xs font-bold rounded-xl hover:bg-indigo-500 transition"
                    >
                        {{ $t('dashboard.start_exploring') }}
                    </Link>
                </div>

                <!-- Pagination -->
                <div v-if="enrollments.links && enrollments.links.length > 3" class="flex justify-center items-center gap-1 pt-6">
                    <template v-for="(link, key) in enrollments.links" :key="key">
                        <Link
                            v-if="link.url"
                            :href="link.url"
                            v-html="link.label"
                            :class="[
                                'px-3 py-1.5 text-xs rounded-lg border font-medium transition',
                                link.active ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-slate-700 border-slate-200'
                            ]"
                        />
                    </template>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

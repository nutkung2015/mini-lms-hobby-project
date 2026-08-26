<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    courses: Object,
    stats: Object,
});
</script>

<template>
    <Head :title="$t('dashboard.instructor_title')" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-extrabold text-slate-900">{{ $t('dashboard.instructor_header') }}</h1>
                    <p class="text-xs text-slate-500 mt-1">{{ $t('dashboard.instructor_subtitle') }}</p>
                </div>
                <Link
                    :href="route('courses.create')"
                    class="px-4 py-2 bg-indigo-600 text-white text-xs font-bold rounded-xl hover:bg-indigo-500 shadow-md shadow-indigo-200 transition"
                >
                    {{ $t('dashboard.create_course_btn') }}
                </Link>
            </div>
        </template>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-8">
            <!-- Stats Counters -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-xs flex items-center space-x-4">
                    <div class="w-12 h-12 rounded-2xl bg-indigo-100 text-indigo-600 flex items-center justify-center text-xs font-black">
                        {{ $t('dashboard.students_unit') }}
                    </div>
                    <div>
                        <p class="text-xs text-slate-400 font-semibold">{{ $t('dashboard.total_students') }}</p>
                        <p class="text-2xl font-black text-slate-900">{{ stats.total_students || 0 }} {{ $t('dashboard.students_unit') }}</p>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-xs flex items-center space-x-4">
                    <div class="w-12 h-12 rounded-2xl bg-violet-100 text-violet-600 flex items-center justify-center text-xs font-black">
                        {{ $t('dashboard.courses_unit') }}
                    </div>
                    <div>
                        <p class="text-xs text-slate-400 font-semibold">{{ $t('dashboard.total_courses') }}</p>
                        <p class="text-2xl font-black text-violet-600">{{ stats.total_courses || 0 }} {{ $t('dashboard.courses_unit') }}</p>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-xs flex items-center space-x-4">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-100 text-emerald-600 flex items-center justify-center text-xs font-black">
                        {{ $t('dashboard.table_status') }}
                    </div>
                    <div>
                        <p class="text-xs text-slate-400 font-semibold">{{ $t('dashboard.published_courses') }}</p>
                        <p class="text-2xl font-black text-emerald-600">{{ stats.published_courses || 0 }} {{ $t('dashboard.courses_unit') }}</p>
                    </div>
                </div>
            </div>

            <!-- Courses Table -->
            <div class="bg-white rounded-3xl border border-slate-200 shadow-xs overflow-hidden">
                <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                    <h2 class="text-lg font-bold text-slate-900">{{ $t('dashboard.my_courses_table') }}</h2>
                    <span class="text-xs text-slate-400 font-medium">{{ $t('dashboard.total_items', { total: courses.total || 0 }) }}</span>
                </div>

                <div v-if="courses.data && courses.data.length > 0" class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-100 text-[11px] font-bold uppercase text-slate-500 tracking-wider">
                                <th class="py-3.5 px-6">{{ $t('dashboard.table_title') }}</th>
                                <th class="py-3.5 px-4">{{ $t('dashboard.table_category') }}</th>
                                <th class="py-3.5 px-4">{{ $t('dashboard.table_price') }}</th>
                                <th class="py-3.5 px-4">{{ $t('dashboard.table_status') }}</th>
                                <th class="py-3.5 px-4">{{ $t('dashboard.table_students') }}</th>
                                <th class="py-3.5 px-4">{{ $t('dashboard.table_lessons') }}</th>
                                <th class="py-3.5 px-4">{{ $t('dashboard.table_rating') }}</th>
                                <th class="py-3.5 px-6 text-right">{{ $t('dashboard.table_actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-xs text-slate-700">
                            <tr v-for="c in courses.data" :key="c.id" class="hover:bg-slate-50/70 transition">
                                <td class="py-4 px-6 font-bold text-slate-900">
                                    {{ c.title }}
                                </td>
                                <td class="py-4 px-4 text-slate-500">
                                    {{ c.category?.name }}
                                </td>
                                <td class="py-4 px-4 font-semibold">
                                    {{ c.price == 0 ? $t('common.free') : '฿' + Number(c.price).toLocaleString() }}
                                </td>
                                <td class="py-4 px-4">
                                    <span
                                        class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase"
                                        :class="{
                                            'bg-emerald-100 text-emerald-700': c.status === 'published',
                                            'bg-amber-100 text-amber-700': c.status === 'draft',
                                            'bg-slate-100 text-slate-600': c.status === 'archived',
                                        }"
                                    >
                                        {{ c.status }}
                                    </span>
                                </td>
                                <td class="py-4 px-4 font-semibold text-slate-800">
                                    {{ c.enrollments_count || 0 }}
                                </td>
                                <td class="py-4 px-4 text-slate-500">
                                    {{ $t('dashboard.lessons_count', { count: c.lessons_count || 0 }) }}
                                </td>
                                <td class="py-4 px-4 font-bold text-amber-500">
                                    ★ {{ Number(c.reviews_avg_rating || 0).toFixed(1) }}
                                </td>
                                <td class="py-4 px-6 text-right space-x-2">
                                    <Link
                                        :href="route('courses.edit', c.id)"
                                        class="px-3 py-1.5 rounded-lg bg-indigo-50 text-indigo-600 font-bold hover:bg-indigo-100 transition inline-block"
                                    >
                                        {{ $t('common.edit') }}
                                    </Link>
                                    <Link
                                        :href="route('courses.show', c.slug)"
                                        class="px-3 py-1.5 rounded-lg border border-slate-200 text-slate-600 font-medium hover:bg-slate-50 transition inline-block"
                                    >
                                        {{ $t('dashboard.view_page') }}
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-else class="text-center py-12 space-y-3">
                    <p class="text-slate-400 text-sm">{{ $t('dashboard.no_courses_yet') }}</p>
                    <Link
                        :href="route('courses.create')"
                        class="inline-block px-4 py-2 bg-indigo-600 text-white text-xs font-bold rounded-xl hover:bg-indigo-500"
                    >
                        {{ $t('dashboard.start_first_course') }}
                    </Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

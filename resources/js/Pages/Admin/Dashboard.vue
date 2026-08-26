<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    stats: Object,
    recentEnrollments: Array,
    topCourses: Array,
});
</script>

<template>
    <Head title="แดชบอร์ดผู้ดูแลระบบ (Admin)" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-extrabold text-slate-900">ภาพรวมระบบ (Admin Dashboard)</h1>
                    <p class="text-xs text-slate-500 mt-1">สถิติรวมของระบบ ผู้ใช้งาน คอร์สเรียน และการลงทะเบียน</p>
                </div>
                <span class="bg-purple-100 text-purple-700 text-xs font-bold px-3 py-1 rounded-full uppercase border border-purple-200">
                    Admin Privileges
                </span>
            </div>
        </template>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-8">
            <!-- Stats Counters Grid (4 Cols) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-xs space-y-1">
                    <p class="text-xs text-slate-400 font-bold uppercase">ผู้ใช้งานทั้งหมด</p>
                    <p class="text-3xl font-black text-slate-900">{{ stats.total_users }}</p>
                    <p class="text-[11px] text-slate-500">
                        ผู้สอน {{ stats.total_instructors }} คน | นักเรียน {{ stats.total_students }} คน
                    </p>
                </div>

                <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-xs space-y-1">
                    <p class="text-xs text-slate-400 font-bold uppercase">คอร์สเรียนทั้งหมด</p>
                    <p class="text-3xl font-black text-indigo-600">{{ stats.total_courses }}</p>
                    <p class="text-[11px] text-emerald-600 font-semibold">
                        เปิดสอนอยู่ {{ stats.published_courses }} คอร์ส
                    </p>
                </div>

                <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-xs space-y-1">
                    <p class="text-xs text-slate-400 font-bold uppercase">ยอดการลงทะเบียน</p>
                    <p class="text-3xl font-black text-slate-900">{{ stats.total_enrollments }}</p>
                    <p class="text-[11px] text-slate-500">รายการทั้งหมดในระบบ</p>
                </div>

                <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-xs space-y-1">
                    <p class="text-xs text-slate-400 font-bold uppercase">เรียนจบหลักสูตร</p>
                    <p class="text-3xl font-black text-emerald-600">{{ stats.completed_enrollments }}</p>
                    <p class="text-[11px] text-emerald-700 font-semibold">
                        ความสำเร็จ {{ stats.total_enrollments ? Math.round((stats.completed_enrollments / stats.total_enrollments) * 100) : 0 }}%
                    </p>
                </div>
            </div>

            <!-- Two Columns Section: Top Courses & Recent Enrollments -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Top Courses -->
                <div class="bg-white rounded-3xl border border-slate-200 shadow-xs p-6 space-y-4">
                    <h2 class="text-base font-bold text-slate-900 border-b border-slate-100 pb-3">5 อันดับคอร์สยอดนิยม</h2>

                    <div v-if="topCourses && topCourses.length > 0" class="divide-y divide-slate-100">
                        <div
                            v-for="(course, idx) in topCourses"
                            :key="course.id"
                            class="py-3 flex items-center justify-between"
                        >
                            <div class="flex items-center space-x-3">
                                <span class="w-6 h-6 rounded-lg bg-indigo-50 text-indigo-600 font-bold text-xs flex items-center justify-center">
                                    {{ idx + 1 }}
                                </span>
                                <div>
                                    <h4 class="font-bold text-slate-900 text-xs">{{ course.title }}</h4>
                                    <p class="text-[10px] text-slate-400">ผู้สอน: {{ course.instructor?.name }}</p>
                                </div>
                            </div>

                            <div class="text-right">
                                <p class="text-xs font-bold text-slate-800">{{ course.enrollments_count }} ผู้เรียน</p>
                                <p class="text-[10px] text-amber-500 font-semibold">★ {{ Number(course.reviews_avg_rating || 5).toFixed(1) }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Enrollments -->
                <div class="bg-white rounded-3xl border border-slate-200 shadow-xs p-6 space-y-4">
                    <h2 class="text-base font-bold text-slate-900 border-b border-slate-100 pb-3">การลงทะเบียนล่าสุด</h2>

                    <div v-if="recentEnrollments && recentEnrollments.length > 0" class="divide-y divide-slate-100">
                        <div
                            v-for="enr in recentEnrollments"
                            :key="enr.id"
                            class="py-3 flex items-center justify-between text-xs"
                        >
                            <div>
                                <h4 class="font-bold text-slate-900">{{ enr.user?.name }}</h4>
                                <p class="text-[11px] text-slate-500 truncate max-w-xs">{{ enr.course?.title }}</p>
                            </div>

                            <div class="text-right">
                                <span
                                    class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase"
                                    :class="enr.status === 'completed' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700'"
                                >
                                    {{ enr.status }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

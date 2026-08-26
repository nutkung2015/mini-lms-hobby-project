<script setup>
import { ref } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { t } from '@/i18n';

const props = defineProps({
    course: Object,
    categories: Array,
});

const courseForm = useForm({
    _method: 'PUT',
    title: props.course.title,
    category_id: props.course.category_id,
    description: props.course.description,
    price: props.course.price,
    max_students: props.course.max_students,
    status: props.course.status,
    cover_image: null,
});

const updateCourse = () => {
    courseForm.post(route('courses.update', props.course.id), {
        preserveScroll: true,
    });
};

// Lesson Form (Add)
const showLessonModal = ref(false);
const lessonForm = useForm({
    title: '',
    video_url: '',
    duration_seconds: 300,
    order: (props.course.lessons?.length || 0) + 1,
});

const addLesson = () => {
    lessonForm.post(route('lessons.store', props.course.slug), {
        preserveScroll: true,
        onSuccess: () => {
            showLessonModal.value = false;
            lessonForm.reset();
            lessonForm.order = (props.course.lessons?.length || 0) + 1;
        },
    });
};

const deleteLesson = (lessonId) => {
    if (confirm(t('courses.confirm_delete_lesson'))) {
        router.delete(route('lessons.destroy', [props.course.slug, lessonId]), {
            preserveScroll: true,
        });
    }
};

const moveLesson = (index, direction) => {
    const list = [...props.course.lessons];
    const targetIndex = index + direction;

    if (targetIndex < 0 || targetIndex >= list.length) return;

    const temp = list[index];
    list[index] = list[targetIndex];
    list[targetIndex] = temp;

    const orderArray = list.map(l => l.id);

    router.post(route('lessons.reorder', props.course.slug), { order: orderArray }, {
        preserveScroll: true,
    });
};

const formatDuration = (seconds) => {
    const mins = Math.floor(seconds / 60);
    return mins > 0 ? t('courses.minutes_unit', { min: mins }) : t('courses.seconds_unit', { sec: seconds });
};
</script>

<template>
    <Head :title="`${$t('common.edit')} - ${course.title}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-extrabold text-slate-900">{{ $t('courses.edit_title') }}</h1>
                    <p class="text-xs text-slate-500 mt-1">{{ $t('courses.edit_subtitle') }}</p>
                </div>
                <div class="flex items-center space-x-3">
                    <Link
                        :href="route('courses.show', course.slug)"
                        class="px-3.5 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-700 hover:bg-slate-50 transition"
                    >
                        {{ $t('courses.view_live_course') }}
                    </Link>
                    <Link
                        :href="route('dashboard.instructor')"
                        class="px-3.5 py-2 rounded-xl bg-slate-100 text-xs font-bold text-slate-700 hover:bg-slate-200 transition"
                    >
                        {{ $t('courses.back_to_dashboard') }}
                    </Link>
                </div>
            </div>
        </template>

        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-10 grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left: Course Details Form (1 Col) -->
            <div class="bg-white rounded-3xl border border-slate-200 shadow-xs p-6 space-y-6 h-fit">
                <h2 class="text-lg font-bold text-slate-900 border-b border-slate-100 pb-3">{{ $t('courses.general_info') }}</h2>

                <form @submit.prevent="updateCourse" class="space-y-4">
                    <div class="space-y-1">
                        <label class="text-xs font-bold text-slate-700">{{ $t('courses.status_label') }}</label>
                        <select
                            v-model="courseForm.status"
                            class="w-full rounded-xl border-slate-200 text-xs font-bold focus:border-indigo-500 focus:ring-indigo-500"
                        >
                            <option value="draft">{{ $t('courses.status_draft') }}</option>
                            <option value="published">{{ $t('courses.status_published') }}</option>
                            <option value="archived">{{ $t('courses.status_archived') }}</option>
                        </select>
                    </div>

                    <div class="space-y-1">
                        <label class="text-xs font-bold text-slate-700">{{ $t('courses.course_title_label') }}</label>
                        <input
                            v-model="courseForm.title"
                            type="text"
                            class="w-full rounded-xl border-slate-200 text-xs focus:border-indigo-500 focus:ring-indigo-500"
                            required
                        />
                    </div>

                    <div class="space-y-1">
                        <label class="text-xs font-bold text-slate-700">{{ $t('courses.course_category_label') }}</label>
                        <select
                            v-model="courseForm.category_id"
                            class="w-full rounded-xl border-slate-200 text-xs focus:border-indigo-500 focus:ring-indigo-500"
                            required
                        >
                            <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                                {{ cat.name }}
                            </option>
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div class="space-y-1">
                            <label class="text-xs font-bold text-slate-700">{{ $t('dashboard.table_price') }}</label>
                            <input
                                v-model="courseForm.price"
                                type="number"
                                min="0"
                                step="0.01"
                                class="w-full rounded-xl border-slate-200 text-xs focus:border-indigo-500 focus:ring-indigo-500"
                                required
                            />
                        </div>

                        <div class="space-y-1">
                            <label class="text-xs font-bold text-slate-700">{{ $t('courses.max_students_meta') }}</label>
                            <input
                                v-model="courseForm.max_students"
                                type="number"
                                min="1"
                                :placeholder="$t('courses.unlimited_placeholder')"
                                class="w-full rounded-xl border-slate-200 text-xs focus:border-indigo-500 focus:ring-indigo-500"
                            />
                        </div>
                    </div>

                    <div class="space-y-1">
                        <label class="text-xs font-bold text-slate-700">{{ $t('courses.course_desc_label') }}</label>
                        <textarea
                            v-model="courseForm.description"
                            rows="4"
                            class="w-full rounded-xl border-slate-200 text-xs focus:border-indigo-500 focus:ring-indigo-500"
                            required
                        ></textarea>
                    </div>

                    <div class="space-y-1">
                        <label class="text-xs font-bold text-slate-700">{{ $t('courses.cover_image_label') }}</label>
                        <input
                            type="file"
                            accept="image/*"
                            @input="courseForm.cover_image = $event.target.files[0]"
                            class="w-full text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
                        />
                    </div>

                    <button
                        type="submit"
                        :disabled="courseForm.processing"
                        class="w-full py-2.5 bg-indigo-600 text-white text-xs font-bold rounded-xl hover:bg-indigo-500 shadow-md shadow-indigo-200 transition disabled:opacity-50"
                    >
                        {{ courseForm.processing ? $t('common.saving') : $t('courses.save_course_changes') }}
                    </button>
                </form>
            </div>

            <!-- Right: Lesson Management (2 Cols) -->
            <div class="lg:col-span-2 bg-white rounded-3xl border border-slate-200 shadow-xs p-6 space-y-6">
                <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                    <div>
                        <h2 class="text-lg font-bold text-slate-900">{{ $t('courses.all_lessons_heading', { count: course.lessons?.length || 0 }) }}</h2>
                        <p class="text-xs text-slate-400">{{ $t('courses.lessons_instruction') }}</p>
                    </div>

                    <button
                        @click="showLessonModal = true"
                        class="px-4 py-2 bg-emerald-600 text-white text-xs font-bold rounded-xl hover:bg-emerald-500 shadow-sm transition"
                    >
                        {{ $t('courses.add_lesson_btn') }}
                    </button>
                </div>

                <!-- Lessons List -->
                <div v-if="course.lessons && course.lessons.length > 0" class="space-y-3">
                    <div
                        v-for="(lesson, idx) in course.lessons"
                        :key="lesson.id"
                        class="p-4 rounded-2xl border border-slate-200 bg-slate-50/60 flex items-center justify-between gap-3 hover:bg-slate-50 transition"
                    >
                        <div class="flex items-center space-x-3">
                            <span class="w-7 h-7 rounded-lg bg-indigo-100 text-indigo-700 font-bold text-xs flex items-center justify-center">
                                {{ lesson.order }}
                            </span>
                            <div>
                                <h4 class="font-bold text-slate-900 text-sm">{{ lesson.title }}</h4>
                                <p class="text-[11px] text-slate-400 truncate max-w-sm">
                                    {{ lesson.video_url }} • {{ formatDuration(lesson.duration_seconds) }}
                                </p>
                            </div>
                        </div>

                        <!-- Actions: Move Up/Down, Delete -->
                        <div class="flex items-center space-x-1">
                            <button
                                @click="moveLesson(idx, -1)"
                                :disabled="idx === 0"
                                class="p-1.5 rounded-lg border border-slate-200 text-slate-600 hover:bg-white disabled:opacity-30 text-xs"
                                :title="$t('courses.move_up')"
                            >
                                ▲
                            </button>
                            <button
                                @click="moveLesson(idx, 1)"
                                :disabled="idx === course.lessons.length - 1"
                                class="p-1.5 rounded-lg border border-slate-200 text-slate-600 hover:bg-white disabled:opacity-30 text-xs"
                                :title="$t('courses.move_down')"
                            >
                                ▼
                            </button>
                            <button
                                @click="deleteLesson(lesson.id)"
                                class="p-1.5 rounded-lg text-rose-600 hover:bg-rose-50 text-xs font-bold"
                                :title="$t('courses.delete_lesson_title')"
                            >
                                ✕
                            </button>
                        </div>
                    </div>
                </div>

                <div v-else class="text-center py-12 text-slate-400 text-sm border-2 border-dashed border-slate-200 rounded-2xl">
                    {{ $t('courses.no_lessons_in_course') }}
                </div>
            </div>
        </div>

        <!-- Add Lesson Modal -->
        <div v-if="showLessonModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-xs p-4">
            <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="font-bold text-slate-900 text-base">{{ $t('courses.add_lesson_modal_title') }}</h3>
                    <button @click="showLessonModal = false" class="text-slate-400 hover:text-slate-600 text-sm font-bold">✕</button>
                </div>

                <form @submit.prevent="addLesson" class="space-y-4">
                    <div class="space-y-1">
                        <label class="text-xs font-bold text-slate-700">{{ $t('courses.lesson_title_label') }}</label>
                        <input
                            v-model="lessonForm.title"
                            type="text"
                            :placeholder="$t('courses.lesson_title_placeholder')"
                            class="w-full rounded-xl border-slate-200 text-xs focus:border-indigo-500 focus:ring-indigo-500"
                            required
                        />
                    </div>

                    <div class="space-y-1">
                        <label class="text-xs font-bold text-slate-700">{{ $t('courses.video_url_label') }}</label>
                        <input
                            v-model="lessonForm.video_url"
                            type="text"
                            placeholder="https://www.youtube.com/watch?v=... / https://example.com/video.mp4"
                            class="w-full rounded-xl border-slate-200 text-xs focus:border-indigo-500 focus:ring-indigo-500"
                            required
                        />
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div class="space-y-1">
                            <label class="text-xs font-bold text-slate-700">{{ $t('courses.duration_seconds_label') }}</label>
                            <input
                                v-model="lessonForm.duration_seconds"
                                type="number"
                                min="1"
                                class="w-full rounded-xl border-slate-200 text-xs focus:border-indigo-500 focus:ring-indigo-500"
                                required
                            />
                        </div>

                        <div class="space-y-1">
                            <label class="text-xs font-bold text-slate-700">{{ $t('courses.lesson_order_label') }}</label>
                            <input
                                v-model="lessonForm.order"
                                type="number"
                                min="1"
                                class="w-full rounded-xl border-slate-200 text-xs focus:border-indigo-500 focus:ring-indigo-500"
                                required
                            />
                        </div>
                    </div>

                    <div class="pt-3 flex justify-end space-x-2">
                        <button
                            type="button"
                            @click="showLessonModal = false"
                            class="px-4 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-700 hover:bg-slate-50"
                        >
                            {{ $t('common.cancel') }}
                        </button>
                        <button
                            type="submit"
                            :disabled="lessonForm.processing"
                            class="px-5 py-2 rounded-xl bg-emerald-600 text-white text-xs font-bold hover:bg-emerald-500 shadow-sm transition disabled:opacity-50"
                        >
                            {{ lessonForm.processing ? $t('courses.adding_btn') : $t('courses.submit_add_lesson') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

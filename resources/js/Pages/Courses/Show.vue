<script setup>
import { ref } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { t } from '@/i18n';

const props = defineProps({
    course: Object,
    avgRating: Number,
    reviewCount: Number,
    enrollment: Object,
    isBookmarked: Boolean,
    userReview: Object,
});

const page = usePage();
const enrollForm = useForm({});
const bookmarkForm = useForm({});

// Modals
const showReviewModal = ref(false);
const showLockedModal = ref(false);

const reviewForm = useForm({
    rating: 5,
    comment: '',
});

const submitReview = () => {
    reviewForm.post(route('reviews.store', props.course.slug), {
        preserveScroll: true,
        onSuccess: () => {
            showReviewModal.value = false;
            reviewForm.reset();
        },
    });
};

const enrollNow = () => {
    enrollForm.post(route('enrollments.store', props.course.slug));
};

const toggleBookmark = () => {
    bookmarkForm.post(route('bookmarks.toggle', props.course.slug), {
        preserveScroll: true,
    });
};

const formatDuration = (seconds) => {
    const mins = Math.floor(seconds / 60);
    return mins > 0 ? t('courses.minutes_unit', { min: mins }) : t('courses.seconds_unit', { sec: seconds });
};
</script>

<template>
    <Head :title="course.title" />

    <AuthenticatedLayout>
        <!-- Header Banner -->
        <div class="bg-slate-900 text-white py-12 px-4 sm:px-6 lg:px-8 border-b border-slate-800">
            <div class="max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-3 gap-10 items-center">
                <!-- Left Details -->
                <div class="lg:col-span-2 space-y-4">
                    <!-- Badges -->
                    <div class="flex items-center gap-3">
                        <span class="bg-indigo-500/20 border border-indigo-400/30 text-indigo-300 text-xs font-semibold px-3 py-1 rounded-full">
                            {{ course.category?.name }}
                        </span>
                        <span class="flex items-center text-amber-400 text-sm font-bold gap-1">
                            ★ {{ avgRating }} ({{ $t('courses.reviews_count', { count: reviewCount }) }})
                        </span>
                    </div>

                    <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight text-white leading-tight">
                        {{ course.title }}
                    </h1>

                    <p class="text-slate-300 text-base leading-relaxed">
                        {{ course.description }}
                    </p>

                    <!-- Instructor info -->
                    <div class="flex items-center space-x-3 pt-2">
                        <div class="w-10 h-10 rounded-full bg-indigo-600 flex items-center justify-center font-bold text-white shadow-md">
                            {{ course.instructor?.name?.charAt(0) }}
                        </div>
                        <div>
                            <p class="text-xs text-slate-400 font-medium">{{ $t('courses.instructor_label') }}</p>
                            <p class="text-sm font-semibold text-white">{{ course.instructor?.name }}</p>
                        </div>
                    </div>
                </div>

                <!-- Right Action Card -->
                <div class="bg-white text-slate-900 rounded-3xl p-6 shadow-2xl border border-slate-100 flex flex-col space-y-6">
                    <!-- Preview Image -->
                    <div class="relative h-44 rounded-2xl bg-gradient-to-tr from-indigo-800 to-violet-600 flex items-center justify-center overflow-hidden">
                        <img
                            v-if="course.cover_image"
                            :src="'/storage/' + course.cover_image"
                            :alt="course.title"
                            class="w-full h-full object-cover"
                        />
                        <div v-else class="text-white/40 font-bold text-xl">Mini LMS</div>
                        <span class="absolute top-3 right-3 text-xs font-bold px-3 py-1 rounded-lg shadow-sm" :class="course.price == 0 ? 'bg-emerald-500 text-white' : 'bg-white text-indigo-700'">
                            {{ course.price == 0 ? $t('common.free') : '฿' + Number(course.price).toLocaleString() }}
                        </span>
                    </div>

                    <!-- CTA Actions -->
                    <div class="space-y-3">
                        <!-- If already enrolled -->
                        <div v-if="enrollment" class="space-y-3">
                            <div class="p-3 bg-emerald-50 border border-emerald-200 rounded-xl text-xs text-emerald-800 font-semibold flex items-center justify-between">
                                <span>{{ $t('courses.enroll_status_label') }}</span>
                                <span class="uppercase font-bold">{{ enrollment.status === 'completed' ? $t('courses.enroll_completed') : $t('courses.enroll_in_progress') }}</span>
                            </div>

                            <Link
                                v-if="course.lessons && course.lessons.length > 0"
                                :href="route('lessons.show', [course.slug, course.lessons[0].id])"
                                class="w-full block text-center py-3.5 px-6 rounded-xl bg-indigo-600 text-white font-bold text-base hover:bg-indigo-500 shadow-lg shadow-indigo-200 transition duration-200"
                            >
                                {{ enrollment.status === 'completed' ? $t('dashboard.review_lessons') : $t('dashboard.continue_learning') }}
                            </Link>

                            <!-- Bookmark Toggle -->
                            <button
                                @click="toggleBookmark"
                                class="w-full py-2.5 px-4 rounded-xl border border-slate-200 font-semibold text-xs text-slate-700 hover:bg-slate-50 flex items-center justify-center gap-2 transition"
                            >
                                <span>{{ isBookmarked ? $t('courses.bookmarked') : $t('courses.bookmark_this') }}</span>
                            </button>
                        </div>

                        <!-- If logged in as student & not enrolled -->
                        <div v-else-if="$page.props.auth.user && $page.props.auth.user.role === 'student'" class="space-y-3">
                            <button
                                @click="enrollNow"
                                :disabled="enrollForm.processing"
                                class="w-full py-3.5 px-6 rounded-xl bg-indigo-600 text-white font-bold text-base hover:bg-indigo-500 shadow-lg shadow-indigo-200 transition duration-200 disabled:opacity-50"
                            >
                                {{ enrollForm.processing ? $t('courses.enrolling_btn') : $t('courses.enroll_now_btn') }}
                            </button>

                            <!-- Bookmark Toggle -->
                            <button
                                @click="toggleBookmark"
                                class="w-full py-2.5 px-4 rounded-xl border border-slate-200 font-semibold text-sm text-slate-700 hover:bg-slate-50 flex items-center justify-center gap-2 transition"
                            >
                                <span>{{ isBookmarked ? $t('courses.bookmarked') : $t('courses.bookmark_this') }}</span>
                            </button>
                        </div>

                        <!-- If logged in as Admin / Instructor -->
                        <div v-else-if="$page.props.auth.user" class="space-y-3">
                            <div class="p-3 bg-purple-50 border border-purple-200 rounded-xl text-xs text-purple-800 font-medium text-center">
                                {{ $t('courses.preview_mode_msg', { role: $page.props.auth.user.role }) }}
                            </div>
                            <Link
                                v-if="course.lessons && course.lessons.length > 0"
                                :href="route('lessons.show', [course.slug, course.lessons[0].id])"
                                class="w-full block text-center py-3.5 px-6 rounded-xl bg-slate-900 text-white font-bold text-sm hover:bg-slate-800 transition"
                            >
                                {{ $t('courses.preview_classroom') }}
                            </Link>
                        </div>

                        <!-- If Guest -->
                        <div v-else class="space-y-3">
                            <Link
                                :href="route('login')"
                                class="w-full block text-center py-3.5 px-6 rounded-xl bg-indigo-600 text-white font-bold text-base hover:bg-indigo-500 shadow-lg shadow-indigo-200 transition duration-200"
                            >
                                {{ $t('courses.login_to_enroll') }}
                            </Link>
                        </div>
                    </div>

                    <!-- Course Meta List -->
                    <div class="border-t border-slate-100 pt-4 space-y-2 text-xs text-slate-500">
                        <div class="flex justify-between">
                            <span>{{ $t('courses.total_lessons_meta') }}</span>
                            <span class="font-bold text-slate-800">{{ $t('courses.lessons_unit', { count: course.lessons?.length || 0 }) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>{{ $t('courses.status_meta') }}</span>
                            <span class="font-bold text-emerald-600 uppercase">{{ course.status }}</span>
                        </div>
                        <div v-if="course.max_students" class="flex justify-between">
                            <span>{{ $t('courses.max_students_meta') }}</span>
                            <span class="font-bold text-slate-800">{{ $t('courses.seats_unit', { count: course.max_students }) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Lessons Syllabus & Reviews -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 grid grid-cols-1 lg:grid-cols-3 gap-10">
            <!-- Left: Syllabus & Reviews -->
            <div class="lg:col-span-2 space-y-12">
                <!-- Syllabus Section -->
                <div class="bg-white rounded-3xl p-8 border border-slate-200 shadow-xs space-y-6">
                    <div class="flex items-center justify-between">
                        <h2 class="text-xl font-bold text-slate-900">{{ $t('courses.syllabus_title') }}</h2>
                        <span class="text-xs font-semibold text-slate-500 bg-slate-100 px-3 py-1 rounded-full">
                            {{ $t('courses.lessons_unit', { count: course.lessons?.length || 0 }) }}
                        </span>
                    </div>

                    <div v-if="course.lessons && course.lessons.length > 0" class="divide-y divide-slate-100">
                        <div
                            v-for="(lesson, idx) in course.lessons"
                            :key="lesson.id"
                            class="py-4 flex items-center justify-between hover:bg-slate-50/80 px-4 rounded-xl transition"
                        >
                            <div class="flex items-center space-x-3">
                                <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-700 font-bold text-xs flex items-center justify-center">
                                    {{ idx + 1 }}
                                </div>
                                <span class="font-semibold text-slate-800 text-sm">{{ lesson.title }}</span>
                            </div>
                            <span class="text-xs text-slate-400 font-medium">{{ formatDuration(lesson.duration_seconds) }}</span>
                        </div>
                    </div>

                    <div v-else class="text-center py-10 text-slate-400 text-sm">
                        {{ $t('courses.no_lessons_in_course') }}
                    </div>
                </div>

                <!-- Reviews Section with Edge-case Handling & Popup -->
                <div class="bg-white rounded-3xl p-8 border border-slate-200 shadow-xs space-y-6">
                    <div class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-100 pb-4">
                        <div>
                            <h2 class="text-xl font-bold text-slate-900">{{ $t('courses.reviews_title', { count: reviewCount }) }}</h2>
                            <div class="flex items-center text-amber-500 font-bold text-sm gap-1 mt-0.5">
                                ★ {{ avgRating }} / 5.0
                            </div>
                        </div>

                        <!-- Review Action Buttons depending on Edge Cases -->
                        <div>
                            <!-- Case 1: Already reviewed -->
                            <div v-if="userReview" class="flex items-center gap-2 bg-emerald-50 border border-emerald-200 px-3 py-1.5 rounded-xl text-xs text-emerald-800 font-bold">
                                <span>{{ $t('courses.user_reviewed_badge', { rating: userReview.rating }) }}</span>
                            </div>

                            <!-- Case 2: Enrolled & Completed -> Can Review -->
                            <button
                                v-else-if="enrollment && enrollment.status === 'completed'"
                                @click="showReviewModal = true"
                                class="px-4 py-2 bg-amber-500 text-white text-xs font-bold rounded-xl hover:bg-amber-600 shadow-md shadow-amber-200 transition"
                            >
                                {{ $t('courses.write_review_btn') }}
                            </button>

                            <!-- Case 3: Enrolled & In Progress -> Locked (Shows Popup on click) -->
                            <button
                                v-else-if="enrollment && enrollment.status !== 'completed'"
                                @click="showLockedModal = true"
                                class="px-4 py-2 bg-slate-100 text-slate-600 text-xs font-bold rounded-xl hover:bg-slate-200 transition flex items-center gap-1.5"
                            >
                                <span>{{ $t('courses.write_review_locked_btn') }}</span>
                            </button>
                        </div>
                    </div>

                    <!-- User's own review pinned at top if exists -->
                    <div v-if="userReview" class="p-5 rounded-2xl bg-indigo-50/70 border border-indigo-100 space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-indigo-900">{{ $t('courses.your_review') }}</span>
                            <div class="text-amber-400 text-xs font-bold">
                                {{ '★'.repeat(userReview.rating) }}{{ '☆'.repeat(5 - userReview.rating) }}
                            </div>
                        </div>
                        <p class="text-xs text-slate-700 leading-relaxed">{{ userReview.comment || $t('courses.no_additional_comment') }}</p>
                    </div>

                    <!-- Other Reviews List -->
                    <div v-if="course.reviews && course.reviews.length > 0" class="space-y-4">
                        <div
                            v-for="rev in course.reviews"
                            :key="rev.id"
                            class="p-4 rounded-2xl bg-slate-50 border border-slate-100 space-y-2"
                        >
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-2">
                                    <div class="w-7 h-7 rounded-full bg-slate-300 text-slate-700 font-bold text-xs flex items-center justify-center">
                                        {{ rev.user?.name?.charAt(0) }}
                                    </div>
                                    <span class="font-bold text-sm text-slate-800">{{ rev.user?.name }}</span>
                                </div>
                                <div class="text-amber-400 text-xs font-bold">
                                    {{ '★'.repeat(rev.rating) }}{{ '☆'.repeat(5 - rev.rating) }}
                                </div>
                            </div>
                            <p class="text-xs text-slate-600 leading-relaxed">{{ rev.comment || $t('courses.no_additional_comment') }}</p>
                        </div>
                    </div>

                    <div v-else class="text-center py-8 text-slate-400 text-sm">
                        {{ $t('courses.no_reviews_yet') }}
                    </div>
                </div>
            </div>

            <!-- Right Sidebar: Instructor Info Card -->
            <div class="space-y-6">
                <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-xs space-y-4">
                    <h3 class="font-bold text-slate-900 text-base">{{ $t('courses.about_instructor') }}</h3>
                    <div class="flex items-center space-x-4">
                        <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-indigo-600 to-violet-500 text-white font-bold text-xl flex items-center justify-center shadow-md">
                            {{ course.instructor?.name?.charAt(0) }}
                        </div>
                        <div>
                            <h4 class="font-bold text-slate-900 text-base">{{ course.instructor?.name }}</h4>
                            <p class="text-xs text-indigo-600 font-semibold">{{ course.instructor?.email }}</p>
                        </div>
                    </div>
                    <p class="text-xs text-slate-500 leading-relaxed">
                        {{ $t('courses.instructor_desc', { category: course.category?.name }) }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Review Modal (Active) -->
        <div v-if="showReviewModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-xs p-4">
            <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-5">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="font-bold text-slate-900 text-lg">{{ $t('courses.review_modal_title') }}</h3>
                    <button @click="showReviewModal = false" class="text-slate-400 hover:text-slate-600 text-sm font-bold">✕</button>
                </div>

                <div v-if="reviewForm.errors.error" class="p-3 rounded-xl bg-rose-50 border border-rose-200 text-xs text-rose-700 font-semibold">
                    {{ reviewForm.errors.error }}
                </div>

                <form @submit.prevent="submitReview" class="space-y-4">
                    <!-- Rating Selector -->
                    <div class="space-y-2 text-center py-2">
                        <span class="text-xs font-semibold text-slate-600 block">{{ $t('courses.satisfaction_label') }}</span>
                        <div class="flex items-center justify-center space-x-2">
                            <button
                                v-for="star in 5"
                                :key="star"
                                type="button"
                                @click="reviewForm.rating = star"
                                class="text-3xl transition hover:scale-110"
                                :class="star <= reviewForm.rating ? 'text-amber-400' : 'text-slate-200'"
                            >
                                ★
                            </button>
                        </div>
                        <span class="text-xs font-bold text-amber-600 block">{{ $t('courses.score_label', { rating: reviewForm.rating }) }}</span>
                    </div>

                    <!-- Comment Area -->
                    <div class="space-y-1">
                        <label class="text-xs font-bold text-slate-700">{{ $t('courses.comment_label') }}</label>
                        <textarea
                            v-model="reviewForm.comment"
                            rows="4"
                            :placeholder="$t('courses.comment_placeholder')"
                            class="w-full rounded-2xl border-slate-200 text-xs focus:border-indigo-500 focus:ring-indigo-500 p-3"
                        ></textarea>
                    </div>

                    <div class="pt-2 flex justify-end space-x-2">
                        <button
                            type="button"
                            @click="showReviewModal = false"
                            class="px-4 py-2.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-700 hover:bg-slate-50"
                        >
                            {{ $t('common.cancel') }}
                        </button>
                        <button
                            type="submit"
                            :disabled="reviewForm.processing"
                            class="px-6 py-2.5 bg-indigo-600 text-white text-xs font-bold rounded-xl hover:bg-indigo-500 shadow-md shadow-indigo-200 transition disabled:opacity-50"
                        >
                            {{ reviewForm.processing ? $t('courses.submitting_review') : $t('courses.submit_review_btn') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Locked Info Modal (Edge Case: In-Progress / Not Completed) -->
        <div v-if="showLockedModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-xs p-4">
            <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-4 text-center">
                <div class="w-14 h-14 rounded-full bg-amber-100 text-amber-600 text-sm font-black flex items-center justify-center mx-auto">
                    {{ $t('common.status') }}
                </div>
                <h3 class="font-extrabold text-slate-900 text-lg">{{ $t('courses.locked_modal_title') }}</h3>
                <p class="text-xs text-slate-600 leading-relaxed max-w-sm mx-auto">
                    {{ $t('courses.locked_modal_desc') }}
                </p>

                <div class="pt-3">
                    <button
                        @click="showLockedModal = false"
                        class="w-full py-2.5 bg-slate-900 text-white text-xs font-bold rounded-xl hover:bg-slate-800 transition"
                    >
                        {{ $t('courses.locked_modal_btn') }}
                    </button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

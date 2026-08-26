<script setup>
import { ref, onMounted, onUnmounted, computed, nextTick, watch } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import axios from 'axios';
import Plyr from 'plyr';
import 'plyr/dist/plyr.css';

const props = defineProps({
    course: Object,
    lesson: Object,
    enrollment: Object,
    progressList: Object,
    currentProgress: Object,
    userReview: Object,
});

const videoContainerRef = ref(null);
let player = null;
let syncInterval = null;

const currentWatchedSeconds = ref(props.currentProgress?.watched_seconds || 0);
const watchedPercentage = ref(props.currentProgress?.watched_percentage || 0);
const isCompleted = ref(props.currentProgress?.is_completed || false);
const courseProgressPct = ref(0);

// Helper to extract YouTube Video ID
const getYouTubeId = (url) => {
    if (!url) return null;
    const regExp = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|\&v=)([^#\&\?]*).*/;
    const match = url.match(regExp);
    return (match && match[2].length === 11) ? match[2] : null;
};

const youtubeId = computed(() => getYouTubeId(props.lesson?.video_url));
const windowOrigin = typeof window !== 'undefined' ? window.location.origin : '';

// Find next & previous lessons
const currentIndex = computed(() => {
    return props.course.lessons?.findIndex(l => l.id === props.lesson.id) ?? -1;
});

const prevLesson = computed(() => {
    if (currentIndex.value > 0) {
        return props.course.lessons[currentIndex.value - 1];
    }
    return null;
});

const nextLesson = computed(() => {
    if (currentIndex.value >= 0 && currentIndex.value < props.course.lessons.length - 1) {
        return props.course.lessons[currentIndex.value + 1];
    }
    return null;
});

// Review form
const reviewForm = useForm({
    rating: 5,
    comment: '',
});

const submitReview = () => {
    reviewForm.post(route('reviews.store', props.course.slug), {
        preserveScroll: true,
        onSuccess: () => {
            reviewForm.reset();
        },
    });
};

// Send progress update to backend
const sendProgressUpdate = async (seconds) => {
    if (!props.enrollment || !seconds || isNaN(seconds)) return;

    try {
        const response = await axios.post(route('lessons.progress.update', props.lesson.id), {
            watched_seconds: Math.floor(seconds),
        });

        watchedPercentage.value = response.data.watched_percentage;
        isCompleted.value = response.data.is_completed;
        courseProgressPct.value = response.data.course_progress;
    } catch (err) {
        console.error('Failed to sync progress', err);
    }
};

const initPlayer = () => {
    if (player) {
        player.destroy();
        player = null;
    }

    if (!videoContainerRef.value) return;

    player = new Plyr(videoContainerRef.value, {
        controls: [
            'play-large',
            'restart',
            'rewind',
            'play',
            'fast-forward',
            'progress',
            'current-time',
            'duration',
            'mute',
            'volume',
            'settings',
            'pip',
            'fullscreen',
        ],
        settings: ['speed', 'quality'],
        speed: { selected: 1, options: [0.5, 0.75, 1, 1.25, 1.5, 2] },
        youtube: {
            noCookie: false,
            rel: 0,
            showinfo: 0,
            iv_load_policy: 3,
            modestbranding: 1,
        },
    });

    player.on('ready', () => {
        if (currentWatchedSeconds.value > 0) {
            try {
                player.currentTime = currentWatchedSeconds.value;
            } catch (e) {}
        }
    });

    player.on('timeupdate', () => {
        if (player) {
            currentWatchedSeconds.value = Math.floor(player.currentTime);
        }
    });

    player.on('ended', () => {
        sendProgressUpdate(props.lesson.duration_seconds || (player ? player.duration : 0));
    });
};

onMounted(() => {
    nextTick(() => {
        initPlayer();
    });

    // Interval to sync every 10 seconds while playing
    syncInterval = setInterval(() => {
        if (player && player.playing) {
            sendProgressUpdate(player.currentTime);
        }
    }, 10000);
});

watch(() => props.lesson.id, () => {
    currentWatchedSeconds.value = props.currentProgress?.watched_seconds || 0;
    watchedPercentage.value = props.currentProgress?.watched_percentage || 0;
    isCompleted.value = props.currentProgress?.is_completed || false;

    nextTick(() => {
        initPlayer();
    });
});

onUnmounted(() => {
    if (player) {
        try {
            sendProgressUpdate(player.currentTime);
            player.destroy();
        } catch (e) {}
    }
    if (syncInterval) {
        clearInterval(syncInterval);
    }
});

const formatDuration = (seconds) => {
    const mins = Math.floor(seconds / 60);
    const secs = Math.floor(seconds % 60);
    return `${mins}:${secs < 10 ? '0' : ''}${secs}`;
};
</script>

<template>
    <Head :title="`${lesson.title} - ${course.title}`" />

    <AuthenticatedLayout>
        <!-- Classroom Header -->
        <div class="bg-slate-900 text-white px-4 sm:px-6 lg:px-8 py-4 border-b border-slate-800">
            <div class="max-w-7xl mx-auto flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center space-x-3">
                    <Link :href="route('courses.show', course.slug)" class="text-xs text-indigo-400 font-semibold hover:underline flex items-center gap-1">
                        ← ย้อนกลับไปหน้าคอร์ส
                    </Link>
                    <span class="text-slate-600">/</span>
                    <span class="text-xs text-slate-300 font-medium truncate max-w-sm">{{ course.title }}</span>
                </div>

                <!-- Status Pill -->
                <div class="flex items-center space-x-3">
                    <span
                        class="text-xs font-bold px-3 py-1 rounded-full flex items-center gap-1.5"
                        :class="isCompleted ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30' : 'bg-amber-500/20 text-amber-300 border border-amber-500/30'"
                    >
                        <span>{{ isCompleted ? '✓ เรียนจบแล้ว' : 'กำลังเรียน' }}</span>
                        <span>({{ watchedPercentage }}%)</span>
                    </span>
                </div>
            </div>
        </div>

        <!-- Video Player & Sidebar Layout -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 grid grid-cols-1 lg:grid-cols-4 gap-8">
            <!-- Main Content: Video Player & Lesson Info (3 Cols) -->
            <div class="lg:col-span-3 space-y-6">
                <!-- Plyr Video Container -->
                <div :key="lesson.id" class="rounded-3xl overflow-hidden shadow-2xl border border-slate-800 bg-slate-950 aspect-video flex items-center justify-center plyr-wrapper">
                    <!-- If YouTube URL -->
                    <div v-if="youtubeId" class="plyr__video-embed w-full h-full" ref="videoContainerRef">
                        <iframe
                            :src="`https://www.youtube.com/embed/${youtubeId}?origin=${windowOrigin}&amp;iv_load_policy=3&amp;modestbranding=1&amp;playsinline=1&amp;showinfo=0&amp;rel=0&amp;enablejsapi=1`"
                            allowfullscreen
                            allowtransparency
                            allow="autoplay"
                        ></iframe>
                    </div>

                    <!-- If Direct MP4 / Storage URL -->
                    <video
                        v-else
                        ref="videoContainerRef"
                        class="w-full h-full"
                        playsinline
                        controls
                    >
                        <source :src="lesson.video_url" type="video/mp4" />
                        เบราว์เซอร์ของคุณไม่รองรับการเล่นวิดีโอนี้
                    </video>
                </div>

                <!-- Video Controls & Info Bar -->
                <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-xs space-y-4">
                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-bold text-indigo-600 uppercase tracking-wider">บทเรียนที่ {{ lesson.order }}</span>
                                <span v-if="youtubeId" class="text-[10px] font-semibold bg-red-100 text-red-600 px-2 py-0.5 rounded-md flex items-center gap-1">
                                    YouTube Source
                                </span>
                            </div>
                            <h1 class="text-2xl font-extrabold text-slate-900 mt-1">{{ lesson.title }}</h1>
                        </div>

                        <!-- Prev / Next Navigation -->
                        <div class="flex items-center space-x-2">
                            <Link
                                v-if="prevLesson"
                                :href="route('lessons.show', [course.slug, prevLesson.id])"
                                class="px-4 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-700 hover:bg-slate-50 transition"
                            >
                                ← ก่อนหน้า
                            </Link>

                            <Link
                                v-if="nextLesson"
                                :href="route('lessons.show', [course.slug, nextLesson.id])"
                                class="px-4 py-2 rounded-xl bg-indigo-600 text-white text-xs font-bold hover:bg-indigo-500 shadow-md shadow-indigo-200 transition"
                            >
                                ถัดไป →
                            </Link>
                        </div>
                    </div>

                    <!-- Live Progress Bar -->
                    <div class="space-y-1.5 pt-2">
                        <div class="flex justify-between text-xs font-semibold text-slate-500">
                            <span>ความคืบหน้าของบทนี้ (ดูครบ ≥ 90% ถือว่าเรียนจบ)</span>
                            <span class="font-bold text-indigo-600">{{ watchedPercentage }}%</span>
                        </div>
                        <div class="w-full h-2.5 bg-slate-100 rounded-full overflow-hidden">
                            <div
                                class="h-full bg-gradient-to-r from-indigo-500 to-emerald-500 transition-all duration-300 rounded-full"
                                :style="{ width: `${watchedPercentage}%` }"
                            ></div>
                        </div>
                    </div>
                </div>

                <!-- Review Section with Edge Case Controls -->
                <!-- Edge Case 1: Completed & Not Reviewed Yet -> Review Form Active -->
                <div v-if="enrollment && enrollment.status === 'completed' && !userReview" class="bg-white rounded-3xl p-6 border border-slate-200 shadow-xs space-y-4">
                    <div class="flex items-center space-x-3">
                        <div>
                            <h3 class="font-bold text-slate-900 text-lg">ยินดีด้วย! คุณเรียนจบคอร์สนี้แล้ว</h3>
                            <p class="text-xs text-slate-500">ร่วมแบ่งปันรีวิวและให้คะแนนเพื่อช่วยพัฒนาหลักสูตร</p>
                        </div>
                    </div>

                    <form @submit.prevent="submitReview" class="space-y-4 pt-2">
                        <!-- Rating Selector -->
                        <div class="flex items-center space-x-2">
                            <span class="text-xs font-semibold text-slate-700">คะแนน:</span>
                            <div class="flex items-center space-x-1">
                                <button
                                    v-for="star in 5"
                                    :key="star"
                                    type="button"
                                    @click="reviewForm.rating = star"
                                    class="text-2xl transition hover:scale-110"
                                    :class="star <= reviewForm.rating ? 'text-amber-400' : 'text-slate-200'"
                                >
                                    ★
                                </button>
                            </div>
                            <span class="text-xs font-bold text-amber-600 pl-2">({{ reviewForm.rating }} / 5)</span>
                        </div>

                        <!-- Comment Area -->
                        <textarea
                            v-model="reviewForm.comment"
                            rows="3"
                            placeholder="เขียนความประทับใจเกี่ยวกับคอร์สนี้..."
                            class="w-full rounded-2xl border-slate-200 text-xs focus:border-indigo-500 focus:ring-indigo-500 p-3"
                        ></textarea>

                        <button
                            type="submit"
                            :disabled="reviewForm.processing"
                            class="px-5 py-2.5 bg-indigo-600 text-white text-xs font-bold rounded-xl hover:bg-indigo-500 shadow-md shadow-indigo-200 transition disabled:opacity-50"
                        >
                            {{ reviewForm.processing ? 'กำลังส่งรีวิว...' : 'ส่งรีวิว' }}
                        </button>
                    </form>
                </div>

                <!-- Edge Case 2: Already Reviewed -->
                <div v-else-if="userReview" class="bg-emerald-50 border border-emerald-200 rounded-3xl p-6 space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-emerald-800 flex items-center gap-1.5">
                            <span>✓ คุณได้ส่งรีวิวสำหรับคอร์สนี้แล้ว</span>
                        </span>
                        <span class="text-amber-500 font-bold text-sm">
                            {{ '★'.repeat(userReview.rating) }}
                        </span>
                    </div>
                    <p class="text-xs text-emerald-900 leading-relaxed">{{ userReview.comment || 'ไม่มีความคิดเห็นเพิ่มเติม' }}</p>
                </div>

                <!-- Edge Case 3: In Progress (Not completed yet) -->
                <div v-else-if="enrollment && enrollment.status !== 'completed'" class="bg-slate-50 border border-slate-200 rounded-3xl p-5 flex items-center space-x-3 text-xs text-slate-600">
                    <span>เรียนให้ครบทุกบทเรียน (ความคืบหน้า 100%) เพื่อปลดล็อกฟังก์ชันการให้คะแนนและรีวิวคอร์ส</span>
                </div>
            </div>

            <!-- Sidebar: Lesson Playlist (1 Col) -->
            <div class="space-y-4">
                <div class="bg-white rounded-3xl p-5 border border-slate-200 shadow-xs space-y-4">
                    <h3 class="font-bold text-slate-900 text-base">สารบัญคอร์สเรียน</h3>

                    <div class="divide-y divide-slate-100">
                        <Link
                            v-for="l in course.lessons"
                            :key="l.id"
                            :href="route('lessons.show', [course.slug, l.id])"
                            class="py-3 px-3 rounded-2xl flex items-center justify-between text-xs transition duration-150 block"
                            :class="l.id === lesson.id ? 'bg-indigo-50/80 border border-indigo-100 font-bold text-indigo-900' : 'hover:bg-slate-50 text-slate-700'"
                        >
                            <div class="flex items-center space-x-2.5">
                                <!-- Status Icon -->
                                <span
                                    class="w-5 h-5 rounded-full flex items-center justify-center text-[10px] font-bold shrink-0"
                                    :class="progressList[l.id]?.is_completed ? 'bg-emerald-500 text-white' : 'bg-slate-200 text-slate-600'"
                                >
                                    {{ progressList[l.id]?.is_completed ? '✓' : l.order }}
                                </span>
                                <span class="truncate max-w-[150px]">{{ l.title }}</span>
                            </div>

                            <span class="text-[10px] text-slate-400 shrink-0 font-medium">
                                {{ formatDuration(l.duration_seconds) }}
                            </span>
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style>
/* Custom Plyr Theme for Mini LMS */
:root {
    --plyr-color-main: #4f46e5;
    --plyr-video-background: #020617;
    --plyr-video-controls-background: linear-gradient(rgba(0, 0, 0, 0), rgba(0, 0, 0, 0.75));
}
.plyr-wrapper .plyr {
    width: 100%;
    height: 100%;
    border-radius: 1.5rem;
}
.plyr-wrapper .plyr__video-embed iframe {
    top: 0;
    height: 100%;
    width: 100%;
}
</style>

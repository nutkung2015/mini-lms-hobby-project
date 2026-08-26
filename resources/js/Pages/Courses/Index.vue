<script setup>
import { ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    courses: Object,
    categories: Array,
    filters: Object,
});

const search = ref(props.filters.q || '');
const categoryId = ref(props.filters.category_id || '');
const priceMax = ref(props.filters.price_max || '');

let searchTimeout = null;

const applyFilters = () => {
    router.get(
        route('courses.index'),
        {
            q: search.value || undefined,
            category_id: categoryId.value || undefined,
            price_max: priceMax.value || undefined,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        }
    );
};

watch(search, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        applyFilters();
    }, 400);
});

const resetFilters = () => {
    search.value = '';
    categoryId.value = '';
    priceMax.value = '';
    applyFilters();
};
</script>

<template>
    <Head :title="$t('courses.index_title')" />

    <AuthenticatedLayout>
        <!-- Hero Section -->
        <div class="bg-gradient-to-br from-indigo-900 via-indigo-800 to-slate-900 text-white py-16 px-4 sm:px-6 lg:px-8">
            <div class="max-w-7xl mx-auto text-center space-y-4">
                <h1 class="text-4xl sm:text-5xl font-extrabold tracking-tight">
                    {{ $t('courses.hero_title_prefix') }} <span class="bg-gradient-to-r from-amber-300 to-indigo-200 bg-clip-text text-transparent">Mini LMS</span>
                </h1>
                <p class="text-indigo-200 text-lg max-w-2xl mx-auto">
                    {{ $t('courses.hero_subtitle') }}
                </p>

                <!-- Search Input Bar -->
                <div class="max-w-2xl mx-auto mt-6">
                    <div class="relative">
                        <input
                            v-model="search"
                            type="text"
                            :placeholder="$t('courses.search_placeholder')"
                            class="w-full pl-12 pr-4 py-3.5 rounded-2xl bg-white/10 backdrop-blur-md border border-white/20 text-white placeholder-indigo-200/70 focus:outline-none focus:ring-2 focus:ring-indigo-400 text-base shadow-xl"
                        />
                        <div class="absolute left-4 top-4 text-indigo-300">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
            <!-- Filter Bar -->
            <div class="flex flex-wrap items-center justify-between gap-4 pb-8 border-b border-slate-200">
                <div class="flex flex-wrap items-center gap-3">
                    <span class="text-sm font-semibold text-slate-700">{{ $t('courses.category_filter') }}</span>
                    <button
                        @click="categoryId = ''; applyFilters()"
                        :class="categoryId === '' ? 'bg-indigo-600 text-white shadow-sm' : 'bg-white text-slate-700 border border-slate-200 hover:bg-slate-50'"
                        class="px-3.5 py-1.5 rounded-full text-xs font-semibold transition"
                    >
                        {{ $t('courses.all_categories') }}
                    </button>
                    <button
                        v-for="cat in categories"
                        :key="cat.id"
                        @click="categoryId = String(cat.id); applyFilters()"
                        :class="categoryId === String(cat.id) ? 'bg-indigo-600 text-white shadow-sm' : 'bg-white text-slate-700 border border-slate-200 hover:bg-slate-50'"
                        class="px-3.5 py-1.5 rounded-full text-xs font-semibold transition"
                    >
                        {{ cat.name }}
                    </button>
                </div>

                <!-- Price Filter & Reset -->
                <div class="flex items-center gap-3">
                    <select
                        v-model="priceMax"
                        @change="applyFilters()"
                        class="text-xs font-medium rounded-lg border-slate-200 bg-white py-1.5 pl-3 pr-8 text-slate-700 focus:border-indigo-500 focus:ring-indigo-500"
                    >
                        <option value="">{{ $t('courses.all_prices') }}</option>
                        <option value="0">{{ $t('courses.price_free') }}</option>
                        <option value="500">{{ $t('courses.price_under_500') }}</option>
                        <option value="1000">{{ $t('courses.price_under_1000') }}</option>
                    </select>

                    <button
                        v-if="search || categoryId || priceMax"
                        @click="resetFilters"
                        class="text-xs text-rose-600 font-semibold hover:underline"
                    >
                        {{ $t('courses.clear_filters') }}
                    </button>
                </div>
            </div>

            <!-- Course Cards Grid -->
            <div v-if="courses.data && courses.data.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8 mt-8">
                <div
                    v-for="course in courses.data"
                    :key="course.id"
                    class="bg-white rounded-2xl border border-slate-200 overflow-hidden hover:shadow-xl transition-all duration-300 flex flex-col group"
                >
                    <!-- Course Image / Placeholder -->
                    <div class="relative h-48 bg-gradient-to-tr from-indigo-800 to-violet-600 flex items-center justify-center overflow-hidden">
                        <img
                            v-if="course.cover_image"
                            :src="course.cover_image?.startsWith('http') ? course.cover_image : '/storage/' + course.cover_image"
                            :alt="course.title"
                            class="w-full h-full object-cover group-hover:scale-105 transition duration-300"
                        />
                        <div v-else class="text-center p-4">
                            <span class="text-4xl font-extrabold text-white/20">Mini LMS</span>
                            <p class="text-white/80 font-semibold mt-2 text-sm">{{ course.category?.name }}</p>
                        </div>

                        <!-- Badge: Category -->
                        <span class="absolute top-3 left-3 bg-slate-900/80 backdrop-blur-xs text-white text-xs font-semibold px-2.5 py-1 rounded-md">
                            {{ course.category?.name }}
                        </span>

                        <!-- Badge: Price -->
                        <span
                            class="absolute top-3 right-3 text-xs font-bold px-3 py-1 rounded-md shadow-sm"
                            :class="course.price == 0 ? 'bg-emerald-500 text-white' : 'bg-white text-indigo-700'"
                        >
                            {{ course.price == 0 ? $t('common.free') : '฿' + Number(course.price).toLocaleString() }}
                        </span>
                    </div>

                    <!-- Course Info -->
                    <div class="p-6 flex-1 flex flex-col justify-between space-y-4">
                        <div class="space-y-2">
                            <h3 class="font-bold text-lg text-slate-900 line-clamp-1 group-hover:text-indigo-600 transition">
                                {{ course.title }}
                            </h3>
                            <p class="text-slate-600 text-sm line-clamp-2 leading-relaxed">
                                {{ course.description }}
                            </p>
                        </div>

                        <!-- Stats & Instructor -->
                        <div class="pt-4 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                            <!-- Instructor -->
                            <div class="flex items-center space-x-2">
                                <div class="w-6 h-6 rounded-full bg-indigo-100 text-indigo-700 font-bold flex items-center justify-center text-[10px]">
                                    {{ course.instructor?.name?.charAt(0) }}
                                </div>
                                <span class="font-medium text-slate-700 truncate max-w-[100px]">{{ course.instructor?.name }}</span>
                            </div>

                            <!-- Rating & Lessons count -->
                            <div class="flex items-center space-x-3">
                                <span class="flex items-center text-amber-500 font-semibold">
                                    ★ {{ Number(course.reviews_avg_rating || 5.0).toFixed(1) }}
                                </span>
                                <span>•</span>
                                <span>{{ $t('courses.lessons_unit', { count: course.lessons_count || 0 }) }}</span>
                            </div>
                        </div>

                        <!-- Action Button -->
                        <Link
                            :href="route('courses.show', course.slug)"
                            class="w-full text-center py-2.5 px-4 rounded-xl bg-slate-100 text-slate-800 font-semibold text-sm hover:bg-indigo-600 hover:text-white transition duration-200 mt-2 block"
                        >
                            {{ $t('courses.view_details') }}
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else class="text-center py-20 bg-white rounded-3xl border border-slate-200 mt-8 space-y-3">
                <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-500 flex items-center justify-center mx-auto text-xs font-bold uppercase">
                    {{ $t('common.search') }}
                </div>
                <h3 class="text-lg font-bold text-slate-800">{{ $t('courses.no_courses_found') }}</h3>
                <p class="text-sm text-slate-500 max-w-md mx-auto">{{ $t('courses.no_courses_hint') }}</p>
                <button
                    @click="resetFilters"
                    class="mt-2 inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-xl text-xs font-semibold hover:bg-indigo-500"
                >
                    {{ $t('courses.show_all_courses') }}
                </button>
            </div>

            <!-- Pagination -->
            <div v-if="courses.links && courses.links.length > 3" class="flex justify-center items-center gap-1 mt-12">
                <template v-for="(link, key) in courses.links" :key="key">
                    <Link
                        v-if="link.url"
                        :href="link.url"
                        v-html="link.label"
                        :class="[
                            'px-3.5 py-2 text-sm rounded-lg border font-medium transition',
                            link.active ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-slate-700 border-slate-200 hover:bg-slate-50'
                        ]"
                    />
                    <span
                        v-else
                        v-html="link.label"
                        class="px-3.5 py-2 text-sm rounded-lg border border-slate-200 text-slate-400 bg-slate-50 opacity-50"
                    />
                </template>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

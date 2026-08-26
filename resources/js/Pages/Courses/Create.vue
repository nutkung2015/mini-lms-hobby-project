<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    categories: Array,
});

const form = useForm({
    title: '',
    category_id: props.categories[0]?.id || '',
    description: '',
    price: 0,
    max_students: null,
    cover_image: null,
});

const submit = () => {
    form.post(route('courses.store'));
};
</script>

<template>
    <Head :title="$t('courses.create_title')" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-extrabold text-slate-900">{{ $t('courses.create_title') }}</h1>
                    <p class="text-xs text-slate-500 mt-1">{{ $t('courses.create_subtitle') }}</p>
                </div>
                <Link
                    :href="route('dashboard.instructor')"
                    class="text-xs text-slate-500 hover:text-slate-700 font-semibold"
                >
                    {{ $t('courses.back_to_dashboard') }}
                </Link>
            </div>
        </template>

        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
            <div class="bg-white rounded-3xl border border-slate-200 shadow-xs p-8">
                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Title -->
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-slate-700">{{ $t('courses.course_title_label') }}</label>
                        <input
                            v-model="form.title"
                            type="text"
                            :placeholder="$t('courses.course_title_placeholder')"
                            class="w-full rounded-xl border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-500"
                            required
                        />
                        <div v-if="form.errors.title" class="text-xs text-rose-500 font-semibold">{{ form.errors.title }}</div>
                    </div>

                    <!-- Category & Price (2 cols) -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-slate-700">{{ $t('courses.course_category_label') }}</label>
                            <select
                                v-model="form.category_id"
                                class="w-full rounded-xl border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-500"
                                required
                            >
                                <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                                    {{ cat.name }}
                                </option>
                            </select>
                            <div v-if="form.errors.category_id" class="text-xs text-rose-500 font-semibold">{{ form.errors.category_id }}</div>
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-slate-700">{{ $t('courses.course_price_label') }}</label>
                            <input
                                v-model="form.price"
                                type="number"
                                min="0"
                                step="0.01"
                                class="w-full rounded-xl border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-500"
                                required
                            />
                            <div v-if="form.errors.price" class="text-xs text-rose-500 font-semibold">{{ form.errors.price }}</div>
                        </div>
                    </div>

                    <!-- Max Students Limit -->
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-slate-700">{{ $t('courses.course_max_students_label') }}</label>
                        <input
                            v-model="form.max_students"
                            type="number"
                            min="1"
                            :placeholder="$t('courses.unlimited_placeholder')"
                            class="w-full rounded-xl border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-500"
                        />
                        <div v-if="form.errors.max_students" class="text-xs text-rose-500 font-semibold">{{ form.errors.max_students }}</div>
                    </div>

                    <!-- Description -->
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-slate-700">{{ $t('courses.course_desc_label') }}</label>
                        <textarea
                            v-model="form.description"
                            rows="5"
                            :placeholder="$t('courses.course_desc_placeholder')"
                            class="w-full rounded-xl border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-500"
                            required
                        ></textarea>
                        <div v-if="form.errors.description" class="text-xs text-rose-500 font-semibold">{{ form.errors.description }}</div>
                    </div>

                    <!-- Cover Image Upload -->
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-slate-700">{{ $t('courses.cover_image_label') }}</label>
                        <input
                            type="file"
                            accept="image/*"
                            @input="form.cover_image = $event.target.files[0]"
                            class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
                        />
                        <div v-if="form.errors.cover_image" class="text-xs text-rose-500 font-semibold">{{ form.errors.cover_image }}</div>
                    </div>

                    <!-- Submit Button -->
                    <div class="pt-4 flex items-center justify-end space-x-3">
                        <Link
                            :href="route('dashboard.instructor')"
                            class="px-5 py-2.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-700 hover:bg-slate-50"
                        >
                            {{ $t('common.cancel') }}
                        </Link>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="px-6 py-2.5 rounded-xl bg-indigo-600 text-white text-xs font-bold hover:bg-indigo-500 shadow-md shadow-indigo-200 transition disabled:opacity-50"
                        >
                            {{ form.processing ? $t('common.saving') : $t('courses.submit_create_course') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

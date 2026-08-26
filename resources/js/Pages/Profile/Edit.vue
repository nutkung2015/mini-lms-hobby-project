<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import LanguageSwitcher from '@/Components/LanguageSwitcher.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { useI18n } from '@/i18n';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const page = usePage();
const user = page.props.auth.user;
const { locale } = useI18n();

const activeTab = ref('all'); // 'all' | 'personal' | 'security' | 'preferences' | 'danger'

const formatDate = (dateStr) => {
    if (!dateStr) return '-';
    try {
        const d = new Date(dateStr);
        return d.toLocaleDateString(locale.value === 'th' ? 'th-TH' : 'en-US', {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
        });
    } catch {
        return dateStr;
    }
};
</script>

<template>
    <Head :title="$t('profile.title')" />

    <AuthenticatedLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-8">
            <!-- User Overview Hero Banner Card -->
            <div class="relative overflow-hidden rounded-3xl bg-white border border-slate-200/80 shadow-xs p-6 sm:p-8">
                <!-- Background Accent -->
                <div class="absolute -right-16 -top-16 w-64 h-64 rounded-full bg-gradient-to-br from-indigo-100/60 to-violet-100/40 blur-2xl pointer-events-none"></div>

                <div class="relative flex flex-col sm:flex-row items-start sm:items-center justify-between gap-6">
                    <div class="flex items-center gap-5">
                        <!-- Avatar Circle -->
                        <div class="w-20 h-20 rounded-3xl bg-gradient-to-tr from-indigo-600 to-violet-600 text-white font-extrabold text-3xl flex items-center justify-center shadow-lg shadow-indigo-200/50 shrink-0">
                            {{ user.name ? user.name.charAt(0).toUpperCase() : 'U' }}
                        </div>

                        <!-- User Info -->
                        <div class="space-y-1.5">
                            <div class="flex flex-wrap items-center gap-2.5">
                                <h1 class="text-2xl font-black text-slate-900 tracking-tight">
                                    {{ user.name }}
                                </h1>
                                <!-- Role Badge -->
                                <span
                                    class="text-[11px] font-bold px-2.5 py-0.5 rounded-full uppercase tracking-wider"
                                    :class="{
                                        'bg-purple-100 text-purple-700 border border-purple-200': user.role === 'admin',
                                        'bg-indigo-100 text-indigo-700 border border-indigo-200': user.role === 'instructor',
                                        'bg-emerald-100 text-emerald-700 border border-emerald-200': user.role === 'student',
                                    }"
                                >
                                    {{ $t('roles.' + user.role + '_short') || user.role }}
                                </span>
                            </div>

                            <p class="text-xs font-medium text-slate-500">
                                {{ user.email }}
                            </p>

                            <div class="flex flex-wrap items-center gap-3 pt-1 text-[11px] text-slate-400 font-medium">
                                <span>
                                    {{ $t('profile.member_since') }}: {{ formatDate(user.created_at) }}
                                </span>
                                <span>•</span>
                                <span :class="user.email_verified_at ? 'text-emerald-600 font-semibold' : 'text-amber-600 font-semibold'">
                                    {{ user.email_verified_at ? $t('profile.verified') : $t('profile.unverified') }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Profile Sections Layout -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Left Column: Navigation / Quick Jump & Summary (1 Col) -->
                <div class="space-y-4">
                    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs p-5 space-y-2 sticky top-24">
                        <h3 class="text-xs font-extrabold uppercase text-slate-400 tracking-wider px-3 pb-2 border-b border-slate-100">
                            {{ $t('profile.tabs.personal') }} & Settings
                        </h3>

                        <button
                            type="button"
                            @click="activeTab = 'all'"
                            :class="[
                                'w-full flex items-center justify-between px-3.5 py-2.5 rounded-2xl text-xs font-bold transition text-left',
                                activeTab === 'all'
                                    ? 'bg-indigo-600 text-white shadow-sm shadow-indigo-200'
                                    : 'text-slate-600 hover:bg-slate-50'
                            ]"
                        >
                            <span>{{ $t('common.all') }}</span>
                            <span class="text-[10px] opacity-75">4 {{ $t('common.actions') }}</span>
                        </button>

                        <button
                            type="button"
                            @click="activeTab = 'personal'"
                            :class="[
                                'w-full flex items-center justify-between px-3.5 py-2.5 rounded-2xl text-xs font-bold transition text-left',
                                activeTab === 'personal'
                                    ? 'bg-indigo-600 text-white shadow-sm shadow-indigo-200'
                                    : 'text-slate-600 hover:bg-slate-50'
                            ]"
                        >
                            <span>{{ $t('profile.tabs.personal') }}</span>
                        </button>

                        <button
                            type="button"
                            @click="activeTab = 'security'"
                            :class="[
                                'w-full flex items-center justify-between px-3.5 py-2.5 rounded-2xl text-xs font-bold transition text-left',
                                activeTab === 'security'
                                    ? 'bg-indigo-600 text-white shadow-sm shadow-indigo-200'
                                    : 'text-slate-600 hover:bg-slate-50'
                            ]"
                        >
                            <span>{{ $t('profile.tabs.security') }}</span>
                        </button>

                        <button
                            type="button"
                            @click="activeTab = 'preferences'"
                            :class="[
                                'w-full flex items-center justify-between px-3.5 py-2.5 rounded-2xl text-xs font-bold transition text-left',
                                activeTab === 'preferences'
                                    ? 'bg-indigo-600 text-white shadow-sm shadow-indigo-200'
                                    : 'text-slate-600 hover:bg-slate-50'
                            ]"
                        >
                            <span>{{ $t('profile.tabs.preferences') }}</span>
                        </button>

                        <button
                            type="button"
                            @click="activeTab = 'danger'"
                            :class="[
                                'w-full flex items-center justify-between px-3.5 py-2.5 rounded-2xl text-xs font-bold transition text-left',
                                activeTab === 'danger'
                                    ? 'bg-rose-600 text-white shadow-sm shadow-rose-200'
                                    : 'text-rose-600 hover:bg-rose-50'
                            ]"
                        >
                            <span>{{ $t('profile.tabs.danger') }}</span>
                        </button>
                    </div>
                </div>

                <!-- Right Column: Active Cards (2 Cols) -->
                <div class="lg:col-span-2 space-y-8">
                    <!-- 1. Personal Information Form -->
                    <div
                        v-if="activeTab === 'all' || activeTab === 'personal'"
                        class="bg-white rounded-3xl border border-slate-200/80 shadow-xs p-6 sm:p-8 transition-all"
                    >
                        <UpdateProfileInformationForm
                            :must-verify-email="mustVerifyEmail"
                            :status="status"
                        />
                    </div>

                    <!-- 2. Security & Password Form -->
                    <div
                        v-if="activeTab === 'all' || activeTab === 'security'"
                        class="bg-white rounded-3xl border border-slate-200/80 shadow-xs p-6 sm:p-8 transition-all"
                    >
                        <UpdatePasswordForm />
                    </div>

                    <!-- 3. Language Preferences Card -->
                    <div
                        v-if="activeTab === 'all' || activeTab === 'preferences'"
                        class="bg-white rounded-3xl border border-slate-200/80 shadow-xs p-6 sm:p-8 space-y-6 transition-all"
                    >
                        <header class="border-b border-slate-100 pb-4">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-emerald-600"></span>
                                <h2 class="text-lg font-bold text-slate-900">
                                    {{ $t('profile.preferences.title') }}
                                </h2>
                            </div>
                            <p class="mt-1 text-xs text-slate-500">
                                {{ $t('profile.preferences.description') }}
                            </p>
                        </header>

                        <!-- Big Card Buttons for switching language -->
                        <LanguageSwitcher variant="buttons" />
                    </div>

                    <!-- 4. Danger Zone (Delete Account) -->
                    <div
                        v-if="activeTab === 'all' || activeTab === 'danger'"
                        class="bg-white rounded-3xl border border-rose-200/80 shadow-xs p-6 sm:p-8 transition-all"
                    >
                        <DeleteUserForm />
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

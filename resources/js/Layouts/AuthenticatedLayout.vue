<script setup>
import { ref } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import LanguageSwitcher from '@/Components/LanguageSwitcher.vue';
import { Link, usePage } from '@inertiajs/vue3';

const showingNavigationDropdown = ref(false);
const page = usePage();
</script>

<template>
    <div>
        <div class="min-h-screen bg-slate-50 text-slate-900 font-sans">
            <!-- Navigation -->
            <nav class="border-b border-slate-200 bg-white sticky top-0 z-40 shadow-xs">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="flex h-16 justify-between items-center">
                        <div class="flex items-center space-x-8">
                            <!-- Logo -->
                            <div class="flex shrink-0 items-center">
                                <Link :href="route('courses.index')" class="flex items-center space-x-2.5">
                                    <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-indigo-600 to-violet-500 flex items-center justify-center text-white font-bold text-lg shadow-md shadow-indigo-200">
                                        M
                                    </div>
                                    <span class="font-bold text-xl tracking-tight bg-gradient-to-r from-slate-900 via-indigo-950 to-indigo-800 bg-clip-text text-transparent">
                                        Mini<span class="text-indigo-600">LMS</span>
                                    </span>
                                </Link>
                            </div>

                            <!-- Desktop Nav Links -->
                            <div class="hidden space-x-2 sm:flex items-center">
                                <NavLink :href="route('courses.index')" :active="route().current('courses.index')">
                                    {{ $t('nav.courses') }}
                                </NavLink>

                                <!-- Student Dashboard Link -->
                                <NavLink
                                    v-if="$page.props.auth.user"
                                    :href="route('dashboard')"
                                    :active="route().current('dashboard') || route().current('dashboard.student')"
                                >
                                    {{ $t('nav.dashboard') }}
                                </NavLink>

                                <!-- Instructor My Courses -->
                                <NavLink
                                    v-if="$page.props.auth.user && ($page.props.auth.user.role === 'instructor' || $page.props.auth.user.role === 'admin')"
                                    :href="route('dashboard.instructor')"
                                    :active="route().current('dashboard.instructor') || route().current('courses.create') || route().current('courses.edit')"
                                >
                                    {{ $t('nav.my_courses') }}
                                </NavLink>

                                <!-- Admin Panel -->
                                <NavLink
                                    v-if="$page.props.auth.user && $page.props.auth.user.role === 'admin'"
                                    :href="route('admin.dashboard')"
                                    :active="route().current('admin.dashboard')"
                                >
                                    {{ $t('nav.admin_panel') }}
                                </NavLink>
                            </div>
                        </div>

                        <!-- Right Section: Language Switcher + Auth User Dropdown or Guest Buttons -->
                        <div class="hidden sm:flex sm:items-center sm:space-x-3">
                            <!-- Language Switcher Pill -->
                            <LanguageSwitcher variant="pill" />

                            <template v-if="$page.props.auth.user">
                                <!-- Role Badge -->
                                <span
                                    class="text-xs font-semibold px-2.5 py-1 rounded-full uppercase tracking-wider"
                                    :class="{
                                        'bg-purple-100 text-purple-700 border border-purple-200': $page.props.auth.user.role === 'admin',
                                        'bg-indigo-100 text-indigo-700 border border-indigo-200': $page.props.auth.user.role === 'instructor',
                                        'bg-emerald-100 text-emerald-700 border border-emerald-200': $page.props.auth.user.role === 'student',
                                    }"
                                >
                                    {{ $page.props.auth.user.role }}
                                </span>

                                <Dropdown align="right" width="48">
                                    <template #trigger>
                                        <button
                                            type="button"
                                            class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50 focus:outline-none"
                                        >
                                            <div class="w-7 h-7 rounded-lg bg-indigo-100 text-indigo-700 font-bold flex items-center justify-center text-xs">
                                                {{ $page.props.auth.user.name.charAt(0).toUpperCase() }}
                                            </div>
                                            <span>{{ $page.props.auth.user.name }}</span>
                                            <svg class="h-4 w-4 text-slate-400" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                            </svg>
                                        </button>
                                    </template>

                                    <template #content>
                                        <DropdownLink :href="route('profile.edit')">
                                            {{ $t('nav.profile') }}
                                        </DropdownLink>
                                        <DropdownLink :href="route('logout')" method="post" as="button">
                                            {{ $t('nav.logout') }}
                                        </DropdownLink>
                                    </template>
                                </Dropdown>
                            </template>

                            <template v-else>
                                <Link
                                    :href="route('login')"
                                    class="text-sm font-semibold text-slate-700 hover:text-indigo-600 transition"
                                >
                                    {{ $t('nav.login') }}
                                </Link>
                                <Link
                                    :href="route('register')"
                                    class="rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition"
                                >
                                    {{ $t('nav.register') }}
                                </Link>
                            </template>
                        </div>

                        <!-- Mobile Header: Lang Switcher + Hamburger -->
                        <div class="-me-2 flex items-center gap-2 sm:hidden">
                            <LanguageSwitcher variant="pill" />

                            <button
                                @click="showingNavigationDropdown = !showingNavigationDropdown"
                                class="p-2 rounded-xl text-slate-400 hover:bg-slate-100 hover:text-slate-500 focus:outline-none"
                            >
                                <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                    <path
                                        :class="{ hidden: showingNavigationDropdown, 'inline-flex': !showingNavigationDropdown }"
                                        stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"
                                    />
                                    <path
                                        :class="{ hidden: !showingNavigationDropdown, 'inline-flex': showingNavigationDropdown }"
                                        stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"
                                    />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Mobile Menu -->
                <div :class="{ block: showingNavigationDropdown, hidden: !showingNavigationDropdown }" class="sm:hidden border-t border-slate-200">
                    <div class="space-y-1 py-2 px-3">
                        <ResponsiveNavLink :href="route('courses.index')" :active="route().current('courses.index')">
                            {{ $t('nav.courses') }}
                        </ResponsiveNavLink>
                        <template v-if="$page.props.auth.user">
                            <ResponsiveNavLink :href="route('dashboard')" :active="route().current('dashboard')">
                                {{ $t('nav.dashboard') }}
                            </ResponsiveNavLink>
                            <ResponsiveNavLink
                                v-if="$page.props.auth.user.role === 'instructor' || $page.props.auth.user.role === 'admin'"
                                :href="route('dashboard.instructor')"
                                :active="route().current('dashboard.instructor')"
                            >
                                {{ $t('nav.my_courses') }}
                            </ResponsiveNavLink>
                            <ResponsiveNavLink
                                v-if="$page.props.auth.user.role === 'admin'"
                                :href="route('admin.dashboard')"
                                :active="route().current('admin.dashboard')"
                            >
                                {{ $t('nav.admin_panel') }}
                            </ResponsiveNavLink>
                        </template>
                    </div>

                    <div v-if="$page.props.auth.user" class="border-t border-slate-200 p-4">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-9 h-9 rounded-xl bg-indigo-100 text-indigo-700 font-bold flex items-center justify-center text-sm">
                                {{ $page.props.auth.user.name.charAt(0).toUpperCase() }}
                            </div>
                            <div>
                                <div class="font-bold text-slate-800 text-sm">{{ $page.props.auth.user.name }}</div>
                                <div class="text-xs text-slate-500">{{ $page.props.auth.user.email }}</div>
                            </div>
                        </div>
                        <div class="space-y-1">
                            <ResponsiveNavLink :href="route('profile.edit')">{{ $t('nav.profile') }}</ResponsiveNavLink>
                            <ResponsiveNavLink :href="route('logout')" method="post" as="button">{{ $t('nav.logout') }}</ResponsiveNavLink>
                        </div>
                    </div>
                    <div v-else class="border-t border-slate-200 p-4 space-y-2">
                        <Link :href="route('login')" class="block w-full text-center py-2 text-sm font-semibold text-slate-700 bg-slate-100 rounded-xl">{{ $t('nav.login') }}</Link>
                        <Link :href="route('register')" class="block w-full text-center py-2 text-sm font-semibold text-white bg-indigo-600 rounded-xl">{{ $t('nav.register') }}</Link>
                    </div>
                </div>
            </nav>

            <!-- Flash Message Banner -->
            <div v-if="$page.props.flash?.success" class="bg-emerald-500 text-white text-sm font-medium py-2.5 px-4 text-center shadow-xs">
                {{ $page.props.flash.success }}
            </div>
            <div v-if="$page.props.flash?.error" class="bg-rose-500 text-white text-sm font-medium py-2.5 px-4 text-center shadow-xs">
                {{ $page.props.flash.error }}
            </div>

            <!-- Page Heading Slot -->
            <header v-if="$slots.header" class="bg-white border-b border-slate-200">
                <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <!-- Main Content Slot -->
            <main>
                <slot />
            </main>
        </div>
    </div>
</template>

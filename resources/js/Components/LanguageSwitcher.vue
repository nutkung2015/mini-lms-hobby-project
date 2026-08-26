<script setup>
import { useI18n } from '@/i18n';

const props = defineProps({
    variant: {
        type: String,
        default: 'pill', // 'pill' | 'select' | 'buttons'
    },
});

const { locale, setLocale, availableLocales } = useI18n();
</script>

<template>
    <!-- Variant: Pill Switcher (Default, for Navbar) -->
    <div
        v-if="variant === 'pill'"
        class="inline-flex items-center p-1 rounded-xl bg-slate-100 border border-slate-200/80 text-xs font-semibold select-none"
    >
        <button
            type="button"
            v-for="item in availableLocales"
            :key="item.code"
            @click="setLocale(item.code)"
            :class="[
                'px-2.5 py-1 rounded-lg transition-all duration-150',
                locale === item.code
                    ? 'bg-white text-indigo-600 shadow-xs font-bold'
                    : 'text-slate-500 hover:text-slate-800'
            ]"
            :title="item.name"
        >
            {{ item.label }}
        </button>
    </div>

    <!-- Variant: Select Dropdown -->
    <div v-else-if="variant === 'select'" class="relative inline-block">
        <select
            :value="locale"
            @change="setLocale($event.target.value)"
            class="text-xs font-semibold rounded-xl border-slate-200 bg-white py-1.5 pl-3 pr-8 text-slate-700 shadow-xs focus:border-indigo-500 focus:ring-indigo-500"
        >
            <option
                v-for="item in availableLocales"
                :key="item.code"
                :value="item.code"
            >
                {{ item.name }}
            </option>
        </select>
    </div>

    <!-- Variant: Big Card Buttons (For Profile / Settings) -->
    <div v-else-if="variant === 'buttons'" class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <button
            type="button"
            v-for="item in availableLocales"
            :key="item.code"
            @click="setLocale(item.code)"
            :class="[
                'p-4 rounded-2xl border text-left transition-all duration-200 flex items-start justify-between',
                locale === item.code
                    ? 'border-indigo-600 bg-indigo-50/50 ring-2 ring-indigo-500/20'
                    : 'border-slate-200 bg-white hover:border-slate-300 hover:bg-slate-50/50'
            ]"
        >
            <div class="space-y-1">
                <div class="flex items-center gap-2">
                    <span class="text-xs font-bold px-2 py-0.5 rounded-md uppercase tracking-wider"
                          :class="locale === item.code ? 'bg-indigo-600 text-white' : 'bg-slate-100 text-slate-600'">
                        {{ item.label }}
                    </span>
                    <span class="text-sm font-bold text-slate-900">{{ item.name }}</span>
                </div>
                <p class="text-xs text-slate-500">
                    {{ item.code === 'th' ? $t('profile.preferences.thai_desc') : $t('profile.preferences.english_desc') }}
                </p>
            </div>

            <span
                v-if="locale === item.code"
                class="w-5 h-5 rounded-full bg-indigo-600 text-white flex items-center justify-center text-xs font-bold shrink-0 mt-0.5"
            >
                ✓
            </span>
            <span
                v-else
                class="w-5 h-5 rounded-full border border-slate-300 shrink-0 mt-0.5"
            ></span>
        </button>
    </div>
</template>

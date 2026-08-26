<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const user = usePage().props.auth.user;

const form = useForm({
    name: user.name,
    email: user.email,
});
</script>

<template>
    <section class="space-y-6">
        <header class="border-b border-slate-100 pb-4">
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-indigo-600"></span>
                <h2 class="text-lg font-bold text-slate-900">
                    {{ $t('profile.personal_info.title') }}
                </h2>
            </div>
            <p class="mt-1 text-xs text-slate-500">
                {{ $t('profile.personal_info.description') }}
            </p>
        </header>

        <form
            @submit.prevent="form.patch(route('profile.update'))"
            class="space-y-5"
        >
            <div class="space-y-1.5">
                <InputLabel for="name" :value="$t('profile.personal_info.name_label')" class="text-xs font-bold text-slate-700" />

                <TextInput
                    id="name"
                    type="text"
                    class="w-full rounded-xl border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-500"
                    v-model="form.name"
                    required
                    autofocus
                    autocomplete="name"
                    :placeholder="$t('profile.personal_info.name_placeholder')"
                />

                <InputError class="mt-1" :message="form.errors.name" />
            </div>

            <div class="space-y-1.5">
                <InputLabel for="email" :value="$t('profile.personal_info.email_label')" class="text-xs font-bold text-slate-700" />

                <TextInput
                    id="email"
                    type="email"
                    class="w-full rounded-xl border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-500"
                    v-model="form.email"
                    required
                    autocomplete="username"
                    :placeholder="$t('profile.personal_info.email_placeholder')"
                />

                <InputError class="mt-1" :message="form.errors.email" />
            </div>

            <!-- Email Verification Status Box -->
            <div v-if="mustVerifyEmail && user.email_verified_at === null" class="p-4 rounded-2xl bg-amber-50 border border-amber-200 space-y-2">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-amber-800">
                        {{ $t('profile.personal_info.email_unverified_warning') }}
                    </span>
                    <Link
                        :href="route('verification.send')"
                        method="post"
                        as="button"
                        class="text-xs font-bold text-indigo-600 hover:text-indigo-800 underline transition"
                    >
                        {{ $t('profile.personal_info.resend_verification') }}
                    </Link>
                </div>

                <div
                    v-show="status === 'verification-link-sent'"
                    class="text-xs font-semibold text-emerald-700 bg-emerald-50 p-2 rounded-lg border border-emerald-200"
                >
                    {{ $t('profile.personal_info.verification_sent') }}
                </div>
            </div>

            <div class="flex items-center justify-between pt-2">
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="px-5 py-2.5 bg-indigo-600 text-white text-xs font-bold rounded-xl hover:bg-indigo-500 shadow-md shadow-indigo-200 transition duration-150 disabled:opacity-50 flex items-center gap-2"
                >
                    <span v-if="form.processing">{{ $t('common.saving') }}</span>
                    <span v-else>{{ $t('profile.personal_info.save_button') }}</span>
                </button>

                <Transition
                    enter-active-class="transition ease-in-out duration-300"
                    enter-from-class="opacity-0 translate-y-1"
                    leave-active-class="transition ease-in-out duration-300"
                    leave-to-class="opacity-0"
                >
                    <span
                        v-if="form.recentlySuccessful"
                        class="text-xs font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 px-3 py-1.5 rounded-xl flex items-center gap-1.5"
                    >
                        ✓ {{ $t('profile.personal_info.save_success') }}
                    </span>
                </Transition>
            </div>
        </form>
    </section>
</template>

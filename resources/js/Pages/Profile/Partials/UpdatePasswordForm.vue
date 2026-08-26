<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const passwordInput = ref(null);
const currentPasswordInput = ref(null);

const showCurrentPassword = ref(false);
const showNewPassword = ref(false);
const showConfirmPassword = ref(false);

const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const updatePassword = () => {
    form.put(route('password.update'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
        onError: () => {
            if (form.errors.password) {
                form.reset('password', 'password_confirmation');
                passwordInput.value?.focus();
            }
            if (form.errors.current_password) {
                form.reset('current_password');
                currentPasswordInput.value?.focus();
            }
        },
    });
};
</script>

<template>
    <section class="space-y-6">
        <header class="border-b border-slate-100 pb-4">
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-violet-600"></span>
                <h2 class="text-lg font-bold text-slate-900">
                    {{ $t('profile.security.title') }}
                </h2>
            </div>
            <p class="mt-1 text-xs text-slate-500">
                {{ $t('profile.security.description') }}
            </p>
        </header>

        <form @submit.prevent="updatePassword" class="space-y-5">
            <!-- Current Password -->
            <div class="space-y-1.5">
                <InputLabel for="current_password" :value="$t('profile.security.current_password_label')" class="text-xs font-bold text-slate-700" />

                <div class="relative">
                    <TextInput
                        id="current_password"
                        ref="currentPasswordInput"
                        v-model="form.current_password"
                        :type="showCurrentPassword ? 'text' : 'password'"
                        class="w-full rounded-xl border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-500 pr-10"
                        autocomplete="current-password"
                        :placeholder="$t('profile.security.current_password_placeholder')"
                    />
                    <button
                        type="button"
                        @click="showCurrentPassword = !showCurrentPassword"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 text-xs font-semibold select-none"
                    >
                        {{ showCurrentPassword ? $t('profile.security.hide_password') : $t('profile.security.show_password') }}
                    </button>
                </div>

                <InputError :message="form.errors.current_password" class="mt-1" />
            </div>

            <!-- New Password -->
            <div class="space-y-1.5">
                <InputLabel for="password" :value="$t('profile.security.new_password_label')" class="text-xs font-bold text-slate-700" />

                <div class="relative">
                    <TextInput
                        id="password"
                        ref="passwordInput"
                        v-model="form.password"
                        :type="showNewPassword ? 'text' : 'password'"
                        class="w-full rounded-xl border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-500 pr-10"
                        autocomplete="new-password"
                        :placeholder="$t('profile.security.new_password_placeholder')"
                    />
                    <button
                        type="button"
                        @click="showNewPassword = !showNewPassword"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 text-xs font-semibold select-none"
                    >
                        {{ showNewPassword ? $t('profile.security.hide_password') : $t('profile.security.show_password') }}
                    </button>
                </div>

                <InputError :message="form.errors.password" class="mt-1" />
            </div>

            <!-- Confirm New Password -->
            <div class="space-y-1.5">
                <InputLabel
                    for="password_confirmation"
                    :value="$t('profile.security.confirm_password_label')"
                    class="text-xs font-bold text-slate-700"
                />

                <div class="relative">
                    <TextInput
                        id="password_confirmation"
                        v-model="form.password_confirmation"
                        :type="showConfirmPassword ? 'text' : 'password'"
                        class="w-full rounded-xl border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-500 pr-10"
                        autocomplete="new-password"
                        :placeholder="$t('profile.security.confirm_password_placeholder')"
                    />
                    <button
                        type="button"
                        @click="showConfirmPassword = !showConfirmPassword"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 text-xs font-semibold select-none"
                    >
                        {{ showConfirmPassword ? $t('profile.security.hide_password') : $t('profile.security.show_password') }}
                    </button>
                </div>

                <InputError :message="form.errors.password_confirmation" class="mt-1" />
            </div>

            <!-- Password Guidelines Card -->
            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 text-xs text-slate-600 space-y-1.5">
                <div class="font-bold text-slate-800">{{ $t('profile.security.requirements_title') }}</div>
                <ul class="list-disc list-inside space-y-0.5 text-slate-500">
                    <li>{{ $t('profile.security.req_length') }}</li>
                    <li>{{ $t('profile.security.req_complex') }}</li>
                    <li>{{ $t('profile.security.req_unique') }}</li>
                </ul>
            </div>

            <div class="flex items-center justify-between pt-2">
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="px-5 py-2.5 bg-indigo-600 text-white text-xs font-bold rounded-xl hover:bg-indigo-500 shadow-md shadow-indigo-200 transition duration-150 disabled:opacity-50 flex items-center gap-2"
                >
                    <span v-if="form.processing">{{ $t('common.saving') }}</span>
                    <span v-else>{{ $t('profile.security.update_button') }}</span>
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
                        ✓ {{ $t('profile.security.update_success') }}
                    </span>
                </Transition>
            </div>
        </form>
    </section>
</template>

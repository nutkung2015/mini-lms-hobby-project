<script setup>
import DangerButton from '@/Components/DangerButton.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import Modal from '@/Components/Modal.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { useForm } from '@inertiajs/vue3';
import { nextTick, ref } from 'vue';

const confirmingUserDeletion = ref(false);
const passwordInput = ref(null);

const form = useForm({
    password: '',
});

const confirmUserDeletion = () => {
    confirmingUserDeletion.value = true;
    nextTick(() => passwordInput.value?.focus());
};

const deleteUser = () => {
    form.delete(route('profile.destroy'), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onError: () => passwordInput.value?.focus(),
        onFinish: () => form.reset(),
    });
};

const closeModal = () => {
    confirmingUserDeletion.value = false;
    form.clearErrors();
    form.reset();
};
</script>

<template>
    <section class="space-y-6">
        <header class="border-b border-rose-100 pb-4">
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-rose-600"></span>
                <h2 class="text-lg font-bold text-rose-900">
                    {{ $t('profile.danger.title') }}
                </h2>
            </div>
            <p class="mt-1 text-xs text-rose-700/80">
                {{ $t('profile.danger.description') }}
            </p>
        </header>

        <!-- Warning Alert Box -->
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200/80 text-xs text-rose-800 space-y-1">
            <p class="font-semibold leading-relaxed">
                {{ $t('profile.danger.warning_box') }}
            </p>
        </div>

        <div>
            <button
                type="button"
                @click="confirmUserDeletion"
                class="px-5 py-2.5 bg-rose-600 text-white text-xs font-bold rounded-xl hover:bg-rose-700 shadow-md shadow-rose-200 transition duration-150"
            >
                {{ $t('profile.danger.delete_button') }}
            </button>
        </div>

        <!-- Deletion Confirmation Modal -->
        <Modal :show="confirmingUserDeletion" @close="closeModal">
            <div class="p-6 space-y-5">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="text-base font-bold text-slate-900">
                        {{ $t('profile.danger.modal_title') }}
                    </h3>
                    <button
                        @click="closeModal"
                        class="text-slate-400 hover:text-slate-600 text-sm font-bold"
                    >
                        ✕
                    </button>
                </div>

                <p class="text-xs text-slate-600 leading-relaxed">
                    {{ $t('profile.danger.modal_description') }}
                </p>

                <div class="space-y-1.5">
                    <InputLabel
                        for="password"
                        :value="$t('profile.danger.password_label')"
                        class="text-xs font-bold text-slate-700"
                    />

                    <TextInput
                        id="password"
                        ref="passwordInput"
                        v-model="form.password"
                        type="password"
                        class="w-full rounded-xl border-slate-200 text-sm focus:border-rose-500 focus:ring-rose-500"
                        :placeholder="$t('profile.danger.password_placeholder')"
                        @keyup.enter="deleteUser"
                    />

                    <InputError :message="form.errors.password" class="mt-1" />
                </div>

                <div class="flex items-center justify-end gap-2.5 pt-2 border-t border-slate-100">
                    <button
                        type="button"
                        @click="closeModal"
                        class="px-4 py-2.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-700 hover:bg-slate-50 transition"
                    >
                        {{ $t('common.cancel') }}
                    </button>

                    <button
                        type="button"
                        :disabled="form.processing"
                        @click="deleteUser"
                        class="px-5 py-2.5 rounded-xl bg-rose-600 text-white text-xs font-bold hover:bg-rose-700 shadow-md shadow-rose-200 transition disabled:opacity-50"
                    >
                        <span v-if="form.processing">{{ $t('common.deleting') }}</span>
                        <span v-else>{{ $t('profile.danger.confirm_delete_button') }}</span>
                    </button>
                </div>
            </div>
        </Modal>
    </section>
</template>

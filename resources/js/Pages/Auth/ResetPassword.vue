<template>
    <FullScreenLayout>
        <div class="max-w-md mx-auto mt-20 p-6 bg-white dark:bg-gray-900 rounded-lg">
            <h1 class="text-xl font-semibold mb-4 text-gray-800 dark:text-white">
                Reset password
            </h1>

            <form @submit.prevent="submit">
                <!-- Token -->
                <input type="hidden" v-model="form.token" />

                <!-- Email -->
                <input
                    v-model="form.email"
                    type="email"
                    readonly
                    class="w-full mb-1 px-3 py-2 border rounded bg-gray-100"
                />
                <p v-if="form.errors.email" class="text-sm text-red-600 mb-2">
                    {{ form.errors.email }}
                </p>

                <!-- New Password -->
                <input
                    v-model="form.password"
                    type="password"
                    placeholder="New password"
                    class="w-full mb-1 px-3 py-2 border rounded"
                />

                <!-- Show password errors EXCEPT confirmation mismatch -->
                <p
                    v-if="passwordError && !isConfirmationMismatch"
                    class="text-sm text-red-600 mb-2"
                >
                    {{ passwordError }}
                </p>

                <!-- Confirm Password -->
                <input
                    v-model="form.password_confirmation"
                    type="password"
                    placeholder="Confirm password"
                    class="w-full mb-1 px-3 py-2 border rounded"
                />

                <!-- Show confirmation mismatch ONLY here -->
                <p
                    v-if="isConfirmationMismatch"
                    class="text-sm text-red-600 mb-4"
                >
                    {{ passwordError }}
                </p>

                <button
                    type="submit"
                    :disabled="form.processing"
                    class="w-full px-4 py-2 bg-brand-500 text-white rounded disabled:opacity-50"
                >
                    Reset password
                </button>
            </form>
        </div>
    </FullScreenLayout>
</template>

<script setup>
import { computed } from 'vue'
import { useForm } from '@inertiajs/vue3'
import FullScreenLayout from '@/Layouts/FullScreenLayout.vue'

const props = defineProps({
    token: String,
    email: String,
})

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
})

// Extract password error
const passwordError = computed(() => form.errors.password || null)

// Detect confirmation mismatch
const isConfirmationMismatch = computed(() =>
    passwordError.value?.toLowerCase().includes('confirmation')
)

const submit = () => {
    form.post('/reset-password')
}
</script>

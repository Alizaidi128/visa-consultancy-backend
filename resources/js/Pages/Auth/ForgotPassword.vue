<template>
    <FullScreenLayout>
        <div class="max-w-md mx-auto mt-20 p-6 bg-white dark:bg-gray-900 rounded-lg">
            <h1 class="text-xl font-semibold mb-2 text-gray-800 dark:text-white">
                Forgot your password?
            </h1>

            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                Enter your email and we’ll send you a password reset link.
            </p>

            <div
                v-if="status"
                class="mb-4 text-sm text-green-600"
            >
                {{ status }}
            </div>

            <form @submit.prevent="submit">
                <input
                    v-model="form.email"
                    type="email"
                    placeholder="Email address"
                    class="w-full mb-3 px-3 py-2 border rounded"
                    required
                />

                <button
                    type="submit"
                    :disabled="form.processing"
                    class="w-full px-4 py-2 bg-brand-500 text-white rounded disabled:opacity-50"
                >
                    Send reset link
                </button>
            </form>
        </div>
    </FullScreenLayout>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3'
import FullScreenLayout from '@/Layouts/FullScreenLayout.vue'

defineProps({
    status: String,
})

const form = useForm({
    email: '',
})

const submit = () => {
    form.post('/forgot-password')
}
</script>

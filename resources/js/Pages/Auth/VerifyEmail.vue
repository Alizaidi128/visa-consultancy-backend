<template>
    <FullScreenLayout>
        <div class="max-w-md mx-auto mt-20 p-6 bg-white dark:bg-gray-900 rounded-lg">
            <h1 class="text-xl font-semibold mb-2 text-gray-800 dark:text-white">
                Verify your email
            </h1>

            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                Thanks for signing up! Please verify your email address by clicking the
                link we sent to your inbox.
            </p>

            <div
                v-if="status === 'verification-link-sent'"
                class="mb-4 text-sm text-green-600"
            >
                A new verification link has been sent to your email.
            </div>

            <form @submit.prevent="resend">
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="w-full px-4 py-2 bg-brand-500 text-white rounded-lg disabled:opacity-50"
                >
                    <span v-if="!form.processing">Resend verification email</span>
                    <span v-else>Sending...</span>
                </button>
            </form>

            <div class="mt-4 text-center">
                <Link
                    href="/logout"
                    method="post"
                    as="button"
                    class="text-sm text-gray-500 hover:underline"
                >
                    Log out
                </Link>
            </div>
        </div>
    </FullScreenLayout>
</template>

<script setup>
import {useForm, Link} from '@inertiajs/vue3'
import FullScreenLayout from '@/Layouts/FullScreenLayout.vue'

defineProps({
    status: {
        type: String,
        default: null,
    },
})

const form = useForm({})

const resend = () => {
    form.post('/email/verification-notification', {
        preserveScroll: true,
    })
}
</script>

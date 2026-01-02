<template>
    <FullScreenLayout>
        <div class="max-w-md mx-auto mt-20 p-6 bg-white dark:bg-gray-900 rounded-lg text-center">
            <h1 class="text-2xl font-semibold text-green-600 mb-3">
                Email Verified!
            </h1>

            <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">
                Your email has been successfully verified.
                Your account is now pending admin approval.
            </p>

            <Link
                href="/login"
                class="inline-block px-6 py-2 bg-brand-500 text-white rounded-lg"
            >
                Go to Login
            </Link>
        </div>
    </FullScreenLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import FullScreenLayout from '@/Layouts/FullScreenLayout.vue'
</script>

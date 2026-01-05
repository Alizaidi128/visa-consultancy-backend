<template>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-xl font-semibold text-gray-900 dark:text-gray-100">
                {{ title }}
            </h1>

            <p
                v-if="subtitle"
                class="mt-1 text-sm text-gray-500 dark:text-gray-400"
            >
                {{ subtitle }}
            </p>
        </div>

        <!-- 🔴 THIS WAS MISSING -->
        <div v-if="$slots.actions">
            <slot name="actions" />
        </div>
    </div>
</template>

<script setup>
defineProps({
    title: String,
    subtitle: String,
})
</script>

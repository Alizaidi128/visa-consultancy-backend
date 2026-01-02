<script setup>
defineProps({
    title: String,
});
</script>

<template>
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">
            {{ title }}
        </h1>
        <slot />
    </div>
</template>

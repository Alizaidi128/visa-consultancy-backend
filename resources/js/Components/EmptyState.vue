<script setup>
defineProps({
    title: String,
    description: String,
})
</script>

<template>
    <div class="text-center py-12 text-gray-500">
        <h3 class="text-lg font-semibold">{{ title }}</h3>
        <p class="mt-2">{{ description }}</p>
    </div>
</template>

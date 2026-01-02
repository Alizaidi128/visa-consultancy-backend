<script setup>
defineProps({
    variant: {
        type: String,
        default: 'primary',
    },
});
</script>

<template>
    <button
        class="inline-flex items-center justify-center px-4 py-2 rounded-lg text-sm font-semibold transition"
        :class="{
            'bg-primary text-white hover:bg-blue-700': variant === 'primary',
            'bg-success text-white hover:bg-green-700': variant === 'success',
            'bg-danger text-white hover:bg-red-700': variant === 'danger',
            'bg-gray-100 text-gray-700 hover:bg-gray-200': variant === 'secondary',
        }"
    >
        <slot />
    </button>
</template>

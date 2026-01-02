<script setup>
defineProps({
    title: {
        type: String,
        default: null,
    },
    description: {
        type: String,
        default: null,
    },
});
</script>

<template>
    <section
        class="
            bg-white dark:bg-gray-800
            rounded-2xl
            border border-gray-200 dark:border-gray-700
            shadow-sm
        "
    >
        <!-- Header -->
        <div
            v-if="title || description"
            class="
                px-6 py-4
                border-b border-gray-200 dark:border-gray-700
            "
        >
            <h3
                v-if="title"
                class="
                    text-sm font-semibold
                    text-gray-900 dark:text-gray-100
                "
            >
                {{ title }}
            </h3>

            <p
                v-if="description"
                class="
                    mt-1 text-xs
                    text-gray-500 dark:text-gray-400
                "
            >
                {{ description }}
            </p>
        </div>

        <!-- Body -->
        <div class="p-6 text-gray-900 dark:text-gray-100">
            <slot />
        </div>
    </section>
</template>

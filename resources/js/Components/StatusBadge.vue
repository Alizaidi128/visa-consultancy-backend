<script setup>
defineProps({
    status: {
        type: String,
        required: true,
    },
})

/*
|--------------------------------------------------------------------------
| STATUS → COLOR MAP
|--------------------------------------------------------------------------
| Light + Dark safe
| Works for Visa + Document statuses
*/
const colors = {
    /* Visa workflow */
    draft: `
        bg-gray-100 text-gray-700
        dark:bg-gray-700 dark:text-gray-200
    `,
    documents_pending: `
        bg-yellow-100 text-yellow-800
        dark:bg-yellow-900/40 dark:text-yellow-200
    `,
    submitted: `
        bg-blue-100 text-blue-800
        dark:bg-blue-900/40 dark:text-blue-200
    `,
    approved: `
        bg-green-100 text-green-800
        dark:bg-green-900/40 dark:text-green-200
    `,
    rejected: `
        bg-red-100 text-red-800
        dark:bg-red-900/40 dark:text-red-200
    `,

    /* Document workflow */
    uploaded: `
        bg-blue-100 text-blue-800
        dark:bg-blue-900/40 dark:text-blue-200
    `,
    under_review: `
        bg-yellow-100 text-yellow-800
        dark:bg-yellow-900/40 dark:text-yellow-200
    `,
    pending: `
        bg-yellow-100 text-yellow-800
        dark:bg-yellow-900/40 dark:text-yellow-200
    `,

    /* Fallback */
    default: `
        bg-gray-100 text-gray-700
        dark:bg-gray-700 dark:text-gray-200
    `,
}
</script>

<template>
    <span
        class="
            inline-flex items-center
            px-3 py-1
            rounded-full
            text-xs font-semibold
            capitalize
            whitespace-nowrap
        "
        :class="colors[status] ?? colors.default"
    >
        {{ status.replaceAll('_', ' ') }}
    </span>
</template>

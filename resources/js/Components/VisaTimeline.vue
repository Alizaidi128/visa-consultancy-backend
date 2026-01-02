<script setup>
defineProps({
    logs: Array,
})

function formatAction(log) {
    switch (log.action) {
        case 'document_uploaded':
            return `Document uploaded`
        case 'document_approved':
            return `Document approved`
        case 'document_rejected':
            return `Document rejected`
        default:
            return log.action.replaceAll('_', ' ')
    }
}

function formatMeta(log) {
    if (!log.data) return null

    try {
        const data = typeof log.data === 'string'
            ? JSON.parse(log.data)
            : log.data

        if (data.type) return `(${data.type})`
        if (data.remarks) return `— ${data.remarks}`

        return null
    } catch {
        return null
    }
}
</script>

<template>
    <div
        class="
            bg-white dark:bg-gray-800
            rounded-xl shadow-sm
            p-6
        "
    >
        <h2
            class="
                text-sm font-semibold uppercase mb-4
                text-gray-500 dark:text-gray-400
            "
        >
            Timeline
        </h2>

        <ol
            class="
                relative ml-3
                border-l border-gray-200 dark:border-gray-700
            "
        >
            <li
                v-for="log in logs"
                :key="log.id"
                class="mb-6 ml-6"
            >
                <!-- Timeline dot -->
                <span
                    class="
                        absolute -left-3 flex items-center justify-center
                        w-6 h-6 rounded-full
                        bg-primary-soft text-primary
                        text-xs
                        ring-8 ring-white dark:ring-gray-800
                    "
                >
                    •
                </span>

                <!-- Action -->
                <p
                    class="
                        text-sm font-medium
                        text-gray-900 dark:text-gray-100
                    "
                >
                    {{ formatAction(log) }}
                    <span class="text-gray-500 dark:text-gray-400">
                        {{ formatMeta(log) }}
                    </span>
                </p>

                <!-- Timestamp -->
                <p
                    class="
                        text-xs mt-1
                        text-gray-400 dark:text-gray-500
                    "
                >
                    {{ new Date(log.created_at).toLocaleString() }}
                </p>
            </li>

            <!-- Empty state -->
            <li
                v-if="!logs.length"
                class="ml-6 text-sm text-gray-400 dark:text-gray-500"
            >
                No activity yet.
            </li>
        </ol>
    </div>
</template>

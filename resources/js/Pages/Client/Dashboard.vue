<script setup>
import PageHeader from '@/Components/PageHeader.vue'
import Card from '@/Components/Card.vue'
import StatusBadge from '@/Components/StatusBadge.vue'

defineProps({
    visas: {
        type: Array,
        default: () => [],
    },
})
</script>

<template>
    <!-- Page Header -->
    <PageHeader title="My Dashboard" />

    <!-- Active Visa -->
    <Card v-if="visas.length">
        <div
            class="
                flex flex-col sm:flex-row
                sm:items-center sm:justify-between
                gap-4 mb-6
            "
        >
            <div>
                <h2
                    class="
                        text-lg font-semibold
                        text-gray-900 dark:text-gray-100
                    "
                >
                    {{ visas[0].country }} — {{ visas[0].visa_type }}
                </h2>

                <p
                    class="
                        text-sm
                        text-gray-500 dark:text-gray-400
                    "
                >
                    Your current visa application
                </p>
            </div>

            <StatusBadge :status="visas[0].status" />
        </div>

        <div
            class="
                grid grid-cols-1 sm:grid-cols-3
                gap-6 text-sm
            "
        >
            <div>
                <p class="text-gray-500 dark:text-gray-400">
                    Country
                </p>
                <p class="font-medium text-gray-900 dark:text-gray-100">
                    {{ visas[0].country }}
                </p>
            </div>

            <div>
                <p class="text-gray-500 dark:text-gray-400">
                    Visa Type
                </p>
                <p class="font-medium text-gray-900 dark:text-gray-100">
                    {{ visas[0].visa_type }}
                </p>
            </div>

            <div>
                <p class="text-gray-500 dark:text-gray-400">
                    Documents Uploaded
                </p>
                <p class="font-medium text-gray-900 dark:text-gray-100">
                    {{ visas[0].documents_count ?? 0 }}
                </p>
            </div>
        </div>
    </Card>

    <!-- Empty State -->
    <Card v-else>
        <p
            class="
                text-sm
                text-gray-500 dark:text-gray-400
            "
        >
            You do not have a visa application yet.
        </p>
    </Card>
</template>

<script>
import AppLayout from '@/Layouts/AppLayout.vue'
import ClientLayout from '@/Layouts/ClientLayout.vue'

export default {
    layout: (h, page) =>
        h(AppLayout, () =>
            h(ClientLayout, () => page)
        ),
}
</script>

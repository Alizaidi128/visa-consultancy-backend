<script setup>
import PageHeader from '@/Components/PageHeader.vue'
import StatusBadge from '@/Components/StatusBadge.vue'

defineProps({
    visas: Array,
})
</script>

<template>
    <!-- Header with action -->
<!--    <PageHeader title="My Visa Applications">
        <a
            href="/client/visas/create"
            class="
                inline-flex items-center justify-center
                px-4 py-2 rounded-lg
                text-sm font-medium
                text-gray-700 dark:text-gray-300
                bg-gray-100 dark:bg-gray-700
                border border-gray-300 dark:border-gray-600
                hover:bg-gray-200 dark:hover:bg-gray-600
                transition
            "
        >
            New Visa Application
        </a>
    </PageHeader>-->

    <!-- Table Card -->
    <div
        class="
            bg-white dark:bg-gray-800
            rounded-xl shadow-sm
            overflow-hidden
        "
    >
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700">
            <tr>
                <th
                    class="
                            px-6 py-3 text-left text-xs font-semibold uppercase
                            text-gray-600 dark:text-gray-300
                        "
                >
                    Country
                </th>
                <th
                    class="
                            px-6 py-3 text-center text-xs font-semibold uppercase
                            text-gray-600 dark:text-gray-300
                        "
                >
                    Status
                </th>
                <th
                    class="
                            px-6 py-3 text-right text-xs font-semibold uppercase
                            text-gray-600 dark:text-gray-300
                        "
                >
                    Actions
                </th>
            </tr>
            </thead>

            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
            <tr
                v-for="visa in visas"
                :key="visa.id"
                class="hover:bg-gray-50 dark:hover:bg-gray-700 transition"
            >
                <td
                    class="
                            px-6 py-4 font-medium
                            text-gray-900 dark:text-gray-100
                        "
                >
                    {{ visa.country }}
                </td>

                <td class="px-6 py-4 text-center">
                    <StatusBadge :status="visa.status" />
                </td>

                <td class="px-6 py-4 text-right">
                    <a
                        :href="`/client/visas/${visa.id}`"
                        class="
        text-sm font-medium
        text-primary
        dark:text-gray-200
        hover:underline
    "
                    >
                        View
                    </a>

                </td>
            </tr>

            <!-- Empty State -->
            <tr v-if="!visas.length">
                <td
                    colspan="3"
                    class="
                            px-6 py-10 text-center text-sm
                            text-gray-500 dark:text-gray-400
                        "
                >
                    You have no visa applications yet.
                </td>
            </tr>
            </tbody>
        </table>
    </div>
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

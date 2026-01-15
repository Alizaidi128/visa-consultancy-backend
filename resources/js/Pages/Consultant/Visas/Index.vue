<script setup>
import PageHeader from '@/Components/PageHeader.vue'
import StatusBadge from '@/Components/StatusBadge.vue'
import { Link } from '@inertiajs/vue3'
defineProps({
    visas: Array,
})
</script>

<template>
    <PageHeader title="My Assigned Visas" />

    <!-- Table Card -->
    <div
        class="
            bg-white dark:bg-gray-800
            rounded-xl shadow-sm
            overflow-hidden
        "
    >
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <!-- Header -->
            <thead class="bg-gray-50 dark:bg-gray-700">
            <tr>
                <th
                    class="
                            px-6 py-3 text-left text-xs font-semibold uppercase
                            text-gray-600 dark:text-gray-300
                        "
                >
                    Client
                </th>
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

            <!-- Body -->
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
                    {{ visa.client.first_name }} {{ visa.client.last_name }}
                </td>

                <td
                    class="
                            px-6 py-4
                            text-gray-700 dark:text-gray-300
                        "
                >
                    {{ visa.country }}
                </td>

                <td class="px-6 py-4 text-center">
                    <StatusBadge :status="visa.status" />
                </td>

                <td class="px-6 py-4 text-right">
                    <Link
                        :href="`/consultant/visas/${visa.id}`"
                        class="
                                text-sm font-medium
                                text-gray-700 dark:text-gray-300
                                hover:underline
                            "
                    >
                        View
                    </Link>
                </td>
            </tr>

            <!-- Empty state -->
            <tr v-if="!visas.length">
                <td
                    colspan="4"
                    class="
                            px-6 py-10 text-center text-sm
                            text-gray-500 dark:text-gray-400
                        "
                >
                    No assigned visas.
                </td>
            </tr>
            </tbody>
        </table>
    </div>
</template>

<script>
import AppLayout from '@/Layouts/AppLayout.vue'
import ConsultantLayout from '@/Layouts/ConsultantLayout.vue'

export default {
    layout: (h, page) =>
        h(AppLayout, () =>
            h(ConsultantLayout, () => page)
        ),
}
</script>

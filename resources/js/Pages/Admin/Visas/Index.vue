<script setup>
import PageHeader from '@/Components/PageHeader.vue'
import StatusBadge from '@/Components/StatusBadge.vue'

defineProps({
    visas: Array,
})
</script>

<template>
    <PageHeader title="Visa Applications" />
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-xl font-semibold text-gray-900 dark:text-gray-100">

        </h1>
    <a
        href="/admin/visa-applications/create"
        class="
            inline-flex items-center gap-2
            px-4 py-2 rounded-lg text-sm font-medium
            border border-gray-300 dark:border-gray-600
            text-gray-700 dark:text-gray-300
            bg-white dark:bg-gray-800
            hover:bg-gray-100 dark:hover:bg-gray-700
            transition
        "
    >
        + Create Visa
    </a>
    </div>
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

                <!-- ✅ NEW COLUMN -->
                <th
                    class="
                            px-6 py-3 text-left text-xs font-semibold uppercase
                            text-gray-600 dark:text-gray-300
                        "
                >
                    Visa Type
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
                <!-- Client -->
                <td
                    class="
                            px-6 py-4 font-medium
                            text-gray-900 dark:text-gray-100
                        "
                >
                    {{ visa.client.first_name }} {{ visa.client.last_name }}
                </td>

                <!-- Country -->
                <td
                    class="
                            px-6 py-4
                            text-gray-700 dark:text-gray-300
                        "
                >
                    {{ visa.country }}
                </td>

                <!-- ✅ Visa Type -->
                <td
                    class="
                            px-6 py-4
                            text-gray-700 dark:text-gray-300
                        "
                >
                    {{ visa.visa_type?.name ?? visa.visa_type }}
                </td>

                <!-- Status -->
                <td class="px-6 py-4 text-center">
                    <StatusBadge :status="visa.status" />
                </td>

                <!-- Actions -->
                <td class="px-6 py-4 text-right">
                    <a
                        :href="`/admin/visa-applications/${visa.id}`"
                        class="
                                text-sm font-medium
                                text-gray-700 dark:text-gray-300
                                hover:underline
                            "
                    >
                        View
                    </a>
                </td>
            </tr>

            <!-- Empty state -->
            <tr v-if="!visas.length">
                <td
                    colspan="5"
                    class="
                            px-6 py-10 text-center text-sm
                            text-gray-500 dark:text-gray-400
                        "
                >
                    No visa applications found.
                </td>
            </tr>
            </tbody>
        </table>
    </div>
</template>

<script>
import AppLayout from '@/Layouts/AppLayout.vue'
import AdminLayout from '@/Layouts/AdminLayout.vue'

export default {
    layout: (h, page) =>
        h(AppLayout, () =>
            h(AdminLayout, () => page)
        ),
}
</script>

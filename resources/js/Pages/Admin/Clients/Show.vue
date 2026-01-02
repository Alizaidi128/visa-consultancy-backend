<script setup>
import PageHeader from '@/Components/PageHeader.vue'
import StatusBadge from '@/Components/StatusBadge.vue'

defineProps({
    client: Object,
})
</script>

<template>
    <PageHeader
        :title="`${client.first_name} ${client.last_name}`"
        subtitle="Client details"
    />

    <!-- Client Info -->
    <div
        class="
            bg-white dark:bg-gray-800
            rounded-xl shadow-sm
            p-6 mb-6
        "
    >
        <h2
            class="
                text-sm font-semibold uppercase mb-4
                text-gray-600 dark:text-gray-300
            "
        >
            Client Information
        </h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
            <div>
                <p class="text-gray-500 dark:text-gray-400">Email</p>
                <p class="font-medium text-gray-900 dark:text-gray-100">
                    {{ client.email ?? '—' }}
                </p>
            </div>

            <div>
                <p class="text-gray-500 dark:text-gray-400">Phone</p>
                <p class="font-medium text-gray-900 dark:text-gray-100">
                    {{ client.phone ?? '—' }}
                </p>
            </div>

            <div>
                <p class="text-gray-500 dark:text-gray-400">Passport Number</p>
                <p class="font-medium text-gray-900 dark:text-gray-100">
                    {{ client.passport_number ?? '—' }}
                </p>
            </div>

            <div>
                <p class="text-gray-500 dark:text-gray-400">Passport Expiry</p>
                <p class="font-medium text-gray-900 dark:text-gray-100">
                    {{ client.passport_expiry ?? '—' }}
                </p>
            </div>
        </div>
    </div>

    <!-- Visa Applications -->
    <div
        class="
            bg-white dark:bg-gray-800
            rounded-xl shadow-sm
            overflow-hidden
        "
    >
        <div
            class="
                px-6 py-4 border-b
                border-gray-200 dark:border-gray-700
            "
        >
            <h2
                class="
                    text-sm font-semibold uppercase
                    text-gray-600 dark:text-gray-300
                "
            >
                Visa Applications
            </h2>
        </div>

        <!-- Table -->
        <table
            v-if="client.visa_applications?.length"
            class="min-w-full divide-y divide-gray-200 dark:divide-gray-700"
        >
            <thead class="bg-gray-50 dark:bg-gray-700">
            <tr>
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
                            px-6 py-3 text-left text-xs font-semibold uppercase
                            text-gray-600 dark:text-gray-300
                        "
                >
                    Status
                </th>
                <th
                    class="
                            px-6 py-3 text-left text-xs font-semibold uppercase
                            text-gray-600 dark:text-gray-300
                        "
                >
                    Created
                </th>
            </tr>
            </thead>

            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
            <tr
                v-for="visa in client.visa_applications"
                :key="visa.id"
                class="hover:bg-gray-50 dark:hover:bg-gray-700 transition"
            >
                <td
                    class="
                            px-6 py-4 font-medium
                            text-gray-900 dark:text-gray-100
                        "
                >
                    {{ visa.visa_type }}
                </td>

                <td class="px-6 py-4">
                    <StatusBadge :status="visa.status" />
                </td>

                <td
                    class="
                            px-6 py-4 text-sm
                            text-gray-600 dark:text-gray-400
                        "
                >
                    {{ visa.created_at }}
                </td>
            </tr>
            </tbody>
        </table>

        <!-- Empty state -->
        <div
            v-else
            class="
                p-6 text-center text-sm
                text-gray-500 dark:text-gray-400
            "
        >
            No visa applications yet.
        </div>
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

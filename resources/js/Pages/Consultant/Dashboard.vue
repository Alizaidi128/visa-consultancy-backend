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
    <PageHeader title="Consultant Dashboard" />

    <!-- KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
        <Card>
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Assigned Visas
            </p>
            <p class="text-3xl font-semibold mt-2 text-gray-900 dark:text-gray-100">
                {{ visas.length }}
            </p>
        </Card>

        <Card>
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Approved
            </p>
            <p class="text-3xl font-semibold mt-2 text-green-600 dark:text-green-400">
                {{ visas.filter(v => v.status === 'approved').length }}
            </p>
        </Card>

        <Card>
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Pending
            </p>
            <p class="text-3xl font-semibold mt-2 text-yellow-600 dark:text-yellow-400">
                {{ visas.filter(v => v.status !== 'approved').length }}
            </p>
        </Card>
    </div>

    <!-- Assigned Visas -->
    <Card>
        <h2
            class="
                text-lg font-semibold mb-4
                text-gray-900 dark:text-gray-100
            "
        >
            Assigned Visa Applications
        </h2>

        <p
            v-if="visas.length === 0"
            class="text-sm text-gray-500 dark:text-gray-400"
        >
            No visa applications assigned yet.
        </p>

        <div v-else class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                    <th
                        class="
                                px-4 py-3 text-left font-medium
                                text-gray-600 dark:text-gray-300
                            "
                    >
                        Client
                    </th>
                    <th
                        class="
                                px-4 py-3 text-left font-medium
                                text-gray-600 dark:text-gray-300
                            "
                    >
                        Country
                    </th>
                    <th
                        class="
                                px-4 py-3 text-left font-medium
                                text-gray-600 dark:text-gray-300
                            "
                    >
                        Type
                    </th>
                    <th
                        class="
                                px-4 py-3 text-center font-medium
                                text-gray-600 dark:text-gray-300
                            "
                    >
                        Status
                    </th>
                </tr>
                </thead>

                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                <tr
                    v-for="visa in visas"
                    :key="visa.id"
                    class="hover:bg-gray-50 dark:hover:bg-gray-700 transition"
                >
                    <td class="px-4 py-3 text-gray-900 dark:text-gray-100">
                        {{ visa.client.first_name }} {{ visa.client.last_name }}
                    </td>
                    <td class="px-4 py-3 text-gray-700 dark:text-gray-300">
                        {{ visa.country }}
                    </td>
                    <td class="px-4 py-3 text-gray-700 dark:text-gray-300">
                        {{ visa.visa_type }}
                    </td>
                    <td class="px-4 py-3 text-center">
                        <StatusBadge :status="visa.status" />
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
    </Card>
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

<script setup>
import PageHeader from '@/Components/PageHeader.vue'

defineProps({
    clients: Object,
})
</script>

<template>
    <PageHeader title="Clients">
        <template #actions>
            <a
                href="/admin/clients/create"
                class="
                inline-flex items-center gap-2
                px-4 py-2
                text-sm font-semibold
                text-white
                bg-primary-600 hover:bg-primary-700
                rounded-lg
                transition
            "
            >
                + Create Client
            </a>
        </template>
    </PageHeader>


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
                    Name
                </th>
                <th
                    class="
                            px-6 py-3 text-left text-xs font-semibold uppercase
                            text-gray-600 dark:text-gray-300
                        "
                >
                    Email
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
                v-for="client in clients.data"
                :key="client.id"
                class="hover:bg-gray-50 dark:hover:bg-gray-700 transition"
            >
                <td
                    class="
                            px-6 py-4 font-medium
                            text-gray-900 dark:text-gray-100
                        "
                >
                    {{ client.first_name }} {{ client.last_name }}
                </td>

                <td
                    class="
                            px-6 py-4
                            text-gray-600 dark:text-gray-400
                        "
                >
                    {{ client.email }}
                </td>

                <td class="px-6 py-4 text-right">
                    <a
                        :href="`/admin/clients/${client.id}`"
                        class="
                                inline-flex items-center
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
            <tr v-if="!clients.data.length">
                <td
                    colspan="3"
                    class="
                            px-6 py-10 text-center text-sm
                            text-gray-500 dark:text-gray-400
                        "
                >
                    No clients found.
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

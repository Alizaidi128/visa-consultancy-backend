<script setup>
import { Link } from '@inertiajs/vue3'
import PageHeader from '@/Components/PageHeader.vue'

defineProps({
    clients: Object,
})
</script>

<template>
    <PageHeader title="Clients">
        <template #actions>

            <Link
                href="/admin/clients/create"
                class="
        px-4 py-2 rounded-lg border text-sm font-medium transition
border-gray-300 text-gray-700 bg-white hover:bg-gray-50

dark:border-gray-600 dark:text-gray-300 dark:bg-gray-800
dark:hover:bg-gray-700
    "
            >
                + Create Client
            </Link>


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
                    <div class="inline-flex items-center gap-4">
                        <!-- VIEW -->
                        <Link
                            :href="`/admin/clients/${client.id}`"
                            class="
                text-sm font-medium
                text-gray-700 dark:text-gray-300
                hover:underline
            "
                        >
                            View
                        </Link>

                        <!-- EDIT -->
                        <Link
                            :href="`/admin/clients/${client.id}/edit`"
                            class="
                text-sm font-medium
                text-blue-600 dark:text-blue-400
                hover:underline
            "
                        >
                            Edit
                        </Link>
                    </div>
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

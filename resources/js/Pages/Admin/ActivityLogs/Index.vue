<script setup>
import PageHeader from '@/Components/PageHeader.vue'

defineProps({
    logs: Object, // paginated
})
</script>

<template>
    <PageHeader title="Activity Logs" />

    <!-- Card -->
    <div
        class="
            bg-white dark:bg-gray-800
            rounded-xl shadow-sm
            overflow-hidden
        "
    >
        <!-- Table -->
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
            <thead class="bg-gray-50 dark:bg-gray-700">
            <tr>
                <th
                    class="px-6 py-3 text-left text-xs font-semibold uppercase
                           text-gray-600 dark:text-gray-300"
                >
                    User
                </th>

                <th
                    class="px-6 py-3 text-left text-xs font-semibold uppercase
                           text-gray-600 dark:text-gray-300"
                >
                    Action
                </th>

                <th
                    class="px-6 py-3 text-left text-xs font-semibold uppercase
                           text-gray-600 dark:text-gray-300"
                >
                    Target
                </th>

                <th
                    class="px-6 py-3 text-right text-xs font-semibold uppercase
                           text-gray-600 dark:text-gray-300"
                >
                    Date
                </th>
            </tr>
            </thead>

            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
            <tr
                v-for="log in logs.data"
                :key="log.id"
                class="hover:bg-gray-50 dark:hover:bg-gray-700 transition"
            >
                <!-- User -->
                <td class="px-6 py-4 text-gray-900 dark:text-gray-100">
                    {{ log.user?.name ?? 'System' }}
                </td>

                <!-- Action -->
                <td class="px-6 py-4 text-gray-700 dark:text-gray-300">
                    {{ log.action.replaceAll('_', ' ') }}
                </td>

                <!-- Target -->
                <td class="px-6 py-4 text-gray-700 dark:text-gray-300">
                    {{ log.loggable_type?.split('\\').pop() ?? '—' }}
                </td>

                <!-- Date -->
                <td class="px-6 py-4 text-right text-gray-500 dark:text-gray-400">
                    {{ new Date(log.created_at).toLocaleString() }}
                </td>
            </tr>

            <!-- Empty State -->
            <tr v-if="!logs.data.length">
                <td
                    colspan="4"
                    class="
                        px-6 py-10 text-center text-sm
                        text-gray-500 dark:text-gray-400
                    "
                >
                    No activity logs found.
                </td>
            </tr>
            </tbody>
        </table>

        <!-- Pagination -->
        <div
            v-if="logs.links?.length > 3"
            class="
                px-6 py-4 border-t
                border-gray-200 dark:border-gray-700
                flex flex-wrap gap-2
            "
        >
            <a
                v-for="link in logs.links"
                :key="link.label"
                v-html="link.label"
                :href="link.url || '#'"
                class="
                    px-3 py-1 rounded-lg text-sm
                    border
                    text-gray-700 dark:text-gray-300
                    border-gray-300 dark:border-gray-600
                    hover:bg-gray-100 dark:hover:bg-gray-700
                    transition
                "
                :class="{
                    'bg-primary text-white border-primary': link.active,
                    'opacity-50 pointer-events-none': !link.url,
                }"
            />
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

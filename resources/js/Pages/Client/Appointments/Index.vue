<script setup>
import PageHeader from '@/Components/PageHeader.vue'
import StatusBadge from '@/Components/StatusBadge.vue'
import EmptyState from '@/Components/EmptyState.vue'
import { Link } from '@inertiajs/vue3'

defineProps({
    appointments: {
        type: Array,
        default: () => [],
    },
})
const formatTime = (time) => {
    if (!time) return '—'

    const [hours, minutes] = time.split(':')
    const date = new Date()
    date.setHours(hours, minutes)

    return date.toLocaleTimeString([], {
        hour: '2-digit',
        minute: '2-digit',
        hour12: true,
    })
}
</script>

<template>
    <PageHeader title="My Appointments" />

    <!-- Empty State -->
    <EmptyState
        v-if="!appointments.length"
        title="No Appointments"
        description="You don’t have any appointments scheduled yet."
        icon="calendar"
    />

    <!-- Table -->
    <div
        v-else
        class="
            bg-white dark:bg-gray-800
            rounded-xl shadow-sm
            overflow-hidden
        "
    >
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
            <thead class="bg-gray-50 dark:bg-gray-700">
            <tr>
                <th
                    class="
                            px-4 py-3 text-left font-medium
                            text-gray-600 dark:text-gray-300
                        "
                >
                    Date
                </th>
                <th
                    class="
                            px-4 py-3 text-left font-medium
                            text-gray-600 dark:text-gray-300
                        "
                >
                    Time
                </th>
                <th
                    class="
                            px-4 py-3 text-left font-medium
                            text-gray-600 dark:text-gray-300
                        "
                >
                    Status
                </th>
                <th class="px-4 py-3"></th>
            </tr>
            </thead>

            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
            <tr
                v-for="a in appointments"
                :key="a.id"
                class="
                        hover:bg-gray-50 dark:hover:bg-gray-700/40
                        transition
                    "
            >

                <td
                    class="
                            px-6 py-4
                            text-gray-700 dark:text-gray-300
                        "
                >
                    <!--                    {{ appointment.scheduled_date }}-->
                    {{ a.scheduled_date
                    ? new Date(a.scheduled_date).toLocaleDateString('en-GB')
                    : '—'
                    }}
                </td>

                <td
                    class="
                            px-6 py-4
                            text-gray-700 dark:text-gray-300
                        "
                >
                    {{ formatTime(a.from_time) }}
                    –
                    {{ formatTime(a.to_time) }}

                </td>

                <td class="px-4 py-3">
                    <StatusBadge :status="a.status" />
                </td>

                <td class="px-4 py-3 text-right">
                    <Link :href="`/client/appointments/${a.id}`"

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

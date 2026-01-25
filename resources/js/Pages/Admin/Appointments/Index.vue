<script setup>
import PageHeader from '@/Components/PageHeader.vue'
import StatusBadge from '@/Components/StatusBadge.vue'
import { Link } from '@inertiajs/vue3'

defineProps({
    appointments: Array,
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
    <PageHeader title="Appointments" />
    <Link
        href="/admin/appointments/calendar"
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
        Calender View
    </Link>
    <!-- Header Actions -->
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-xl font-semibold text-gray-900 dark:text-gray-100"></h1>


        <Link
            href="/admin/appointments/create"
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
            + Create Appointment
        </Link>
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
                    Date
                </th>

                <th
                    class="
                            px-6 py-3 text-left text-xs font-semibold uppercase
                            text-gray-600 dark:text-gray-300
                        "
                >
                    Time
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
                v-for="appointment in appointments"
                :key="appointment.id"
                class="hover:bg-gray-50 dark:hover:bg-gray-700 transition"
            >
                <!-- Client -->
                <td
                    class="
                            px-6 py-4 font-medium
                            text-gray-900 dark:text-gray-100
                        "
                >
                    {{ appointment.client.first_name }}
                    {{ appointment.client.last_name }}
                </td>

                <!-- Date -->
                <td
                    class="
                            px-6 py-4
                            text-gray-700 dark:text-gray-300
                        "
                >
<!--                    {{ appointment.scheduled_date }}-->
                    {{ appointment.scheduled_date
                    ? new Date(appointment.scheduled_date).toLocaleDateString()
                    : '—'
                    }}
                </td>

                <!-- Time -->
                <td
                    class="
                            px-6 py-4
                            text-gray-700 dark:text-gray-300
                        "
                >
                    {{ formatTime(appointment.from_time) }}
                    –
                    {{ formatTime(appointment.to_time) }}

                </td>

                <!-- Status -->
                <td class="px-6 py-4 text-center">
                    <StatusBadge :status="appointment.status" />
                </td>

                <!-- Actions -->
                <td class="px-6 py-4 text-right">
                    <Link
                        :href="`/admin/appointments/${appointment.id}`"
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

            <!-- Empty State -->
            <tr v-if="!appointments.length">
                <td
                    colspan="5"
                    class="
                            px-6 py-10 text-center text-sm
                            text-gray-500 dark:text-gray-400
                        "
                >
                    No appointments found.
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

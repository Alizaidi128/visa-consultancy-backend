<script setup>
import PageHeader from '@/Components/PageHeader.vue'
import StatusBadge from '@/Components/StatusBadge.vue'
import { Link } from '@inertiajs/vue3'

defineProps({
    appointment: Object,
})
</script>

<template>
    <PageHeader title="My Appointment">
        <template #actions>
            <Link
                href="/client/appointments"
                class="px-4 py-2 rounded-lg text-sm font-medium border
                       border-gray-300 dark:border-gray-600
                       text-gray-700 dark:text-gray-300
                       bg-white dark:bg-gray-800
                       hover:bg-gray-100 dark:hover:bg-gray-700"
            >
                Back
            </Link>
        </template>
    </PageHeader>

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">

            <div>
                <p class="text-gray-500 dark:text-gray-400">Status</p>
                <StatusBadge :status="appointment.status" />
            </div>

            <div>
                <p class="text-gray-500 dark:text-gray-400">Date</p>
                <p class="font-medium text-gray-900 dark:text-gray-100">
                    {{ new Date(appointment.scheduled_date).toLocaleDateString() }}
                </p>
            </div>

            <div>
                <p class="text-gray-500 dark:text-gray-400">Time</p>
                <p class="font-medium text-gray-900 dark:text-gray-100">
                    {{ appointment.from_time }} – {{ appointment.to_time }}
                </p>
            </div>

            <div class="md:col-span-2">
                <p class="text-gray-500 dark:text-gray-400">Notes</p>
                <p class="text-gray-900 dark:text-gray-100">
                    {{ appointment.notes || '—' }}
                </p>
            </div>

        </div>
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

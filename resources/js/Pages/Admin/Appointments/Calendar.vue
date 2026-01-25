<script setup>
import PageHeader from '@/Components/PageHeader.vue'
import { Link } from '@inertiajs/vue3'

import FullCalendar from '@fullcalendar/vue3'
import dayGridPlugin from '@fullcalendar/daygrid'
import timeGridPlugin from '@fullcalendar/timegrid'
import listPlugin from '@fullcalendar/list'
import interactionPlugin from '@fullcalendar/interaction'

import { computed, watchEffect } from 'vue'

/* ---------------------------------------
   Props
---------------------------------------- */
const props = defineProps({
    appointments: {
        type: Array,
        default: () => [],
    },
})

/* ---------------------------------------
   Map appointments → calendar events
---------------------------------------- */
const events = computed(() =>
    props.appointments.map(a => {
        // Extract YYYY-MM-DD ONLY (no timezone)
        const date = a.scheduled_date.split('T')[0]

        return {
            id: a.id,
            title: `${a.client?.first_name ?? ''} ${a.client?.last_name ?? ''}`.trim(),

            // 🔑 IMPORTANT: DO NOT let Date() parse this
            start: `${date}T${a.from_time}`,
            end: `${date}T${a.to_time}`,

            extendedProps: {
                status: a.status,
            },
        }
    })
)



/* ---------------------------------------
   Calendar Options
---------------------------------------- */
const calendarOptions = computed(() => ({
    timeZone: 'local',
    plugins: [
        dayGridPlugin,
        timeGridPlugin,
        listPlugin,
        interactionPlugin,
    ],

    initialView: 'dayGridMonth',

    headerToolbar: {
        left: 'prev,next today',
        center: 'title',
        right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek',
    },

    events: events.value,

    height: 'auto',

    displayEventTime: false, // ❌ disable default time

    eventContent(arg) {
        const time = arg.event.start
            ? arg.event.start.toLocaleTimeString([], {
                hour: 'numeric',
                minute: '2-digit',
                hour12: true,
            })
            : ''

        const title = arg.event.title

        return {
            html: `
                <div class="fc-custom-event">
                    <div class="fc-event-time">${time}</div>
                    <div class="fc-event-title">${title}</div>
                </div>
            `,
        }
    },

    eventClick(info) {
        window.location.href = `/admin/appointments/${info.event.id}`
    },
}))

</script>


<template>
    <PageHeader title="Appointment Calendar">
        <template #actions>
            <div class="flex gap-2">
                <Link
                    href="/admin/appointments"
                    class="
                        px-4 py-2 rounded-lg text-sm font-medium
                        border border-gray-300 dark:border-gray-600
                        text-gray-700 dark:text-gray-300
                        bg-white dark:bg-gray-800
                        hover:bg-gray-100 dark:hover:bg-gray-700
                        transition
                    "
                >
                    Table View
                </Link>

                <Link
                    href="/admin/appointments/create"
                    class="
                        px-4 py-2 rounded-lg text-sm font-medium
                        bg-primary text-white
                        hover:bg-primary-dark
                        transition
                    "
                >
                    + Create Appointment
                </Link>
            </div>
        </template>
    </PageHeader>

    <!-- Calendar Card -->
    <div
        class="
            bg-white dark:bg-gray-800
            rounded-xl shadow-sm
            p-4
        "
    >
        <FullCalendar :options="calendarOptions" />
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

<style>
/* -------------------------------
   Base Calendar Cleanup
-------------------------------- */
.fc .fc-daygrid-event {
    background: transparent;
    border: none;
    padding: 0;
}

/* -------------------------------
   Custom Event Card
-------------------------------- */
.fc-custom-event {
    padding: 6px 8px;
    border-radius: 8px;
    background-color: rgb(239 246 255); /* blue-50 */
    color: rgb(30 58 138); /* blue-900 */
    font-size: 12px;
    line-height: 1.2;
    cursor: pointer;
}

/* Time */
.fc-event-time {
    font-weight: 600;
    margin-bottom: 2px;
}

/* Title */
.fc-event-title {
    font-weight: 500;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* -------------------------------
   Dark Mode
-------------------------------- */
.dark .fc-custom-event {
    background-color: rgb(30 64 175); /* blue-800 */
    color: rgb(219 234 254); /* blue-100 */
}

/* -------------------------------
   Hover Effect
-------------------------------- */
.fc-custom-event:hover {
    opacity: 0.9;
}
</style>


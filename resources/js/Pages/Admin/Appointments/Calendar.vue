<script setup>
import PageHeader from '@/Components/PageHeader.vue'
import { Link } from '@inertiajs/vue3'

import FullCalendar from '@fullcalendar/vue3'
import dayGridPlugin from '@fullcalendar/daygrid'
import timeGridPlugin from '@fullcalendar/timegrid'
import listPlugin from '@fullcalendar/list'
import interactionPlugin from '@fullcalendar/interaction'

import { computed, watchEffect } from 'vue'
import axios from 'axios'
import { ref } from 'vue'

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
   Error Modal State
---------------------------------------- */
const showErrorModal = ref(false)
const errorMessage = ref('')
/* ---------------------------------------
   Status → Color Map
---------------------------------------- */
const statusColorClass = {
    scheduled: 'fc-event-scheduled',
    rescheduled: 'fc-event-rescheduled',
    completed: 'fc-event-completed',
    cancelled: 'fc-event-cancelled',
}

/* ---------------------------------------
   Map appointments → calendar events
---------------------------------------- */
const events = computed(() =>
    props.appointments.map(a => {
        const date = a.scheduled_date.split('T')[0]

        return {
            id: a.id,
            title: `${a.client?.first_name ?? ''} ${a.client?.last_name ?? ''}`.trim(),
            start: `${date}T${a.from_time}`,
            end: `${date}T${a.to_time}`,

            extendedProps: {
                status: a.status,
                colorClass: statusColorClass[a.status] ?? 'fc-event-default',
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

    editable: true,
    eventDurationEditable: true,
    eventStartEditable: true,

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

        const colorClass = arg.event.extendedProps.colorClass

        return {
            html: `
        <div class="fc-custom-event ${colorClass}">
            <div class="fc-event-time">${time}</div>
            <div class="fc-event-title">${title}</div>
        </div>
    `,
        }

    },

    eventClick(info) {
        window.location.href = `/admin/appointments/${info.event.id}`
    },
    eventDrop(info) {
        const event = info.event

        const start = event.start
        const end = event.end

        // Safety check
        if (!start || !end) {
            info.revert()
            return
        }

        const payload = {
            scheduled_date: start.toISOString().split('T')[0],
            from_time: start.toTimeString().slice(0, 5),
            to_time: end.toTimeString().slice(0, 5),
        }

        axios.put(`/admin/appointments/${event.id}/reschedule`, payload)
            .catch(error => {
                info.revert()

                errorMessage.value =
                    error?.response?.data?.message ??
                    'Selected time slot is not available.'

                showErrorModal.value = true

            })
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
    <!-- APPOINTMENT ERROR MODAL -->
    <div
        v-if="showErrorModal"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/40"
    >
        <div
            class="
            w-full max-w-sm
            rounded-xl
            bg-white dark:bg-gray-800
            p-6
            shadow-xl
        "
        >
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                Appointment Conflict
            </h3>

            <p class="mt-3 text-sm text-gray-600 dark:text-gray-400">
                {{ errorMessage }}
            </p>

            <div class="mt-6 flex justify-end">
                <button
                    @click="showErrorModal = false"
                    class="
                    px-4 py-2 rounded-lg text-sm font-medium
                    bg-blue-600 text-white
                    hover:bg-blue-700
                    transition
                "
                >
                    OK
                </button>
            </div>
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
}/* -------------------------------
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
    font-size: 12px;
    line-height: 1.2;
    cursor: pointer;
    color: white;
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
   Status Colors (Light + Dark)
-------------------------------- */
.fc-event-scheduled {
    background-color: rgb(59 130 246); /* blue-500 */
}

.fc-event-rescheduled {
    background-color: rgb(245 158 11); /* amber-500 */
}

.fc-event-completed {
    background-color: rgb(16 185 129); /* emerald-500 */
}

.fc-event-cancelled {
    background-color: rgb(239 68 68); /* red-500 */
}

.fc-event-default {
    background-color: rgb(107 114 128); /* gray-500 */
}

/* -------------------------------
   Hover Effect
-------------------------------- */
.fc-custom-event:hover {
    opacity: 0.9;
}

</style>


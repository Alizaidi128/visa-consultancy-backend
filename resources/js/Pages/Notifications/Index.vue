<script setup>
import { computed } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import PageHeader from '@/Components/PageHeader.vue'

const page = usePage()

/* ------------------------------------------------------------------
   DATA
------------------------------------------------------------------ */
const notifications = computed(
    () => page.props.notifications?.recent ?? []
)

/* ------------------------------------------------------------------
   ACTIONS
------------------------------------------------------------------ */
const markAsRead = (id) => {
    router.post(
        `/notifications/${id}/read`,
        {},
        { preserveScroll: true, preserveState: true }
    )
}

/* ------------------------------------------------------------------
   HELPERS
------------------------------------------------------------------ */
const notificationTitle = (n) => {
    if (n.data?.message) return n.data.message
    if (n.data?.document_type)
        return n.data.document_type.replaceAll('_', ' ')
    return 'Notification'
}

const notificationMeta = (n) => {
    if (n.data?.status) {
        return `${n.data.status} • ${n.created_at}`
    }
    return n.created_at
}
</script>

<template>
    <PageHeader title="Notifications" />

    <div
        class="
            bg-white dark:bg-gray-800
            rounded-xl shadow-sm
            divide-y divide-gray-200 dark:divide-gray-700
        "
    >
        <!-- Notification Row -->
        <div
            v-for="n in notifications"
            :key="n.id"
            class="
                p-5 flex justify-between items-start gap-4
                transition
                hover:bg-gray-50 dark:hover:bg-gray-700
            "
            :class="!n.read_at
                ? 'bg-blue-50/60 dark:bg-blue-900/20'
                : ''"
        >
            <div class="flex-1">
                <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                    {{ notificationTitle(n) }}
                </p>

                <p class="text-xs mt-1 text-gray-500 dark:text-gray-400">
                    {{ notificationMeta(n) }}
                </p>
            </div>

            <button
                v-if="!n.read_at"
                @click="markAsRead(n.id)"
                class="
                    text-xs font-medium
                    text-gray-700 dark:text-gray-300
                    hover:text-primary dark:hover:text-primary
                    hover:underline
                    transition
                "
            >
                Mark read
            </button>
        </div>

        <!-- Empty State -->
        <div
            v-if="!notifications.length"
            class="p-8 text-center text-sm text-gray-500 dark:text-gray-400"
        >
            No notifications yet.
        </div>
    </div>
</template>

<script>
import { h } from 'vue'
import { usePage } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import ConsultantLayout from '@/Layouts/ConsultantLayout.vue'
import ClientLayout from '@/Layouts/ClientLayout.vue'

export default {
    layout: (hFn, page) => {
        const role = usePage().props.auth?.roles?.[0]

        if (role === 'admin') {
            return hFn(AppLayout, () =>
                hFn(AdminLayout, () => page)
            )
        }

        if (role === 'consultant') {
            return hFn(AppLayout, () =>
                hFn(ConsultantLayout, () => page)
            )
        }

        if (role === 'client') {
            return hFn(AppLayout, () =>
                hFn(ClientLayout, () => page)
            )
        }

        return hFn(AppLayout, () => page)
    },
}
</script>

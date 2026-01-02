<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'

const page = usePage()

/* ------------------------------------------------------------------
   STATE
------------------------------------------------------------------ */
const dropdownOpen = ref(false)
const dropdownRef = ref(null)

/* ------------------------------------------------------------------
   NOTIFICATIONS (FROM BACKEND)
------------------------------------------------------------------ */
const notifications = computed(
    () => page.props.notifications?.recent ?? []
)

const unreadCount = computed(
    () => page.props.notifications?.unread_count ?? 0
)

const hasUnread = computed(() => unreadCount.value > 0)

/* ------------------------------------------------------------------
   ACTIONS
------------------------------------------------------------------ */
const toggleDropdown = () => {
    dropdownOpen.value = !dropdownOpen.value
}

const closeDropdown = () => {
    dropdownOpen.value = false
}

const markAsRead = (id, event) => {
    event?.stopPropagation()

    router.post(
        `/notifications/${id}/read`,
        {},
        { preserveScroll: true, preserveState: true }
    )
}

const handleNotificationClick = (notification) => {
    const go = () => {
        if (notification.data?.action_url) {
            router.visit(
                `${notification.data.action_url}?highlight_user=${notification.data.user_id}`
            )
        }
    }

    if (!notification.read_at) {
        router.post(
            `/notifications/${notification.id}/read`,
            {},
            { preserveScroll: true, onSuccess: go }
        )
    } else {
        go()
    }

    closeDropdown()
}

/* ------------------------------------------------------------------
   DISPLAY HELPERS
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

/* ------------------------------------------------------------------
   CLICK OUTSIDE
------------------------------------------------------------------ */
const handleClickOutside = (event) => {
    if (dropdownRef.value && !dropdownRef.value.contains(event.target)) {
        closeDropdown()
    }
}

onMounted(() => document.addEventListener('click', handleClickOutside))
onUnmounted(() =>
    document.removeEventListener('click', handleClickOutside)
)
</script>

<template>
    <div class="relative" ref="dropdownRef">
        <!-- Bell Button -->
        <button
            @click="toggleDropdown"
            class="
                relative flex items-center justify-center
                h-11 w-11 rounded-full
                border border-gray-200 dark:border-gray-700
                bg-white dark:bg-gray-800
                text-gray-500 dark:text-gray-400
                hover:bg-gray-100 dark:hover:bg-gray-700
                hover:text-gray-700 dark:hover:text-gray-100
                transition
            "
        >
            <!-- Unread Dot -->
            <span
                v-if="hasUnread"
                class="absolute right-1 top-1 h-2 w-2 rounded-full bg-orange-400"
            >
                <span
                    class="absolute inline-flex h-full w-full animate-ping rounded-full bg-orange-400 opacity-75"
                />
            </span>

            <!-- Bell Icon -->
            <svg
                class="fill-current"
                width="20"
                height="20"
                viewBox="0 0 20 20"
            >
                <path
                    fill-rule="evenodd"
                    clip-rule="evenodd"
                    d="M10.75 2.29248C10.75 1.87827 10.4143 1.54248 10 1.54248C9.58583 1.54248 9.25004 1.87827 9.25004 2.29248V2.83613C6.08266 3.20733 3.62504 5.9004 3.62504 9.16748V14.4591H2.58337C2.16916 14.4591 1.83337 14.7949 1.83337 15.2091C1.83337 15.6234 2.16916 15.9591 2.58337 15.9591H17.4167C17.8309 15.9591 18.1667 15.6234 18.1667 15.2091C18.1667 14.7949 17.8309 14.4591 17.4167 14.4591H16.375V9.16748C16.375 5.9004 13.9174 3.20733 10.75 2.83613V2.29248Z"
                />
            </svg>
        </button>

        <!-- Dropdown -->
        <div
            v-if="dropdownOpen"
            class="
                absolute right-0 mt-4
                h-[480px] w-[360px]
                rounded-2xl
                border border-gray-200 dark:border-gray-700
                bg-white dark:bg-gray-800
                p-3 shadow-lg
                flex flex-col
                z-50
            "
        >
            <!-- Header -->
            <div
                class="
                    mb-3 flex items-center justify-between
                    border-b border-gray-200 dark:border-gray-700
                    pb-3
                "
            >
                <h5 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                    Notifications
                </h5>

                <button
                    @click="closeDropdown"
                    class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200"
                >
                    ✕
                </button>
            </div>

            <!-- List -->
            <ul class="flex-1 overflow-y-auto custom-scrollbar">
                <li
                    v-for="n in notifications"
                    :key="n.id"
                    @click="handleNotificationClick(n)"
                    class="
                        rounded-lg p-3 cursor-pointer
                        hover:bg-gray-100 dark:hover:bg-gray-700
                        transition
                    "
                >
                    <div class="flex justify-between gap-3">
                        <div>
                            <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                {{ notificationTitle(n) }}
                            </p>

                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                {{ notificationMeta(n) }}
                            </p>
                        </div>

                        <button
                            v-if="!n.read_at"
                            @click.stop="markAsRead(n.id)"
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
                </li>

                <li
                    v-if="!notifications.length"
                    class="p-6 text-center text-sm text-gray-500 dark:text-gray-400"
                >
                    No notifications yet.
                </li>
            </ul>

            <!-- Footer -->
            <Link
                href="/notifications"
                class="
                    mt-3 flex justify-center
                    rounded-lg
                    border border-gray-200 dark:border-gray-700
                    p-3 text-sm font-medium
                    text-gray-700 dark:text-gray-300
                    hover:bg-gray-50 dark:hover:bg-gray-700
                    transition
                "
            >
                View All Notifications
            </Link>
        </div>
    </div>
</template>

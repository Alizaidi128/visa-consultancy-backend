<script setup>
import { computed } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import { useSidebar } from '@/composables/useSidebar'

import logo from '@/assets/images/logo/logo-light.png'
import logoDark from '@/assets/images/logo/logo-dark.png'
import logoIcon from '@/assets/images/logo/logo-icon.png'

// Icons
import {
    GridIcon,
    UserCircleIcon,
    ListIcon,
    HorizontalDots,
} from '@/Components/Icons'

import BoxCubeIcon from '@/Components/Icons/BoxCubeIcon.vue'
import SidebarWidget from './SidebarWidget.vue'

const page = usePage()

const { isExpanded, isMobileOpen, isHovered } = useSidebar()

/* ------------------------------------------------------------------
   ROLE DETECTION (FROM BACKEND – NO CHANGE NEEDED)
------------------------------------------------------------------ */
const roles = computed(() => page.props.auth?.roles ?? [])

const isAdmin = computed(() => roles.value.includes('admin'))
const isConsultant = computed(() => roles.value.includes('consultant'))
const isClient = computed(() => roles.value.includes('client'))
/* ------------------------------------------------------------------
   ADMIN PENDING APPROVAL COUNT
------------------------------------------------------------------ */
const pendingApprovals = computed(
    () => page.props.admin?.pending_user_approvals ?? 0
)
/* ------------------------------------------------------------------
   MENU DEFINITIONS (PER ROLE)
------------------------------------------------------------------ */
const adminMenu = [
    {
        title: 'Menu',
        items: [
            {
                icon: GridIcon,
                name: 'Dashboard',
                path: '/dashboard',
                components: ['Admin/Dashboard'],
            },
            {
                icon: UserCircleIcon,
                name: 'Clients',
                path: '/admin/clients',
                components: [
                    'Admin/Clients/Index',
                    'Admin/Clients/Show',
                    'Admin/Clients/Edit',
                ],
            },
            {
                icon: UserCircleIcon,
                name: 'Create Client',
                path: '/admin/clients/create',
                components: ['Admin/Clients/Create'],
            },
            {
                icon: UserCircleIcon,
                name: 'User Approvals',
                path: '/admin/user-approvals',
                components: ['Admin/UserApprovals/Index'],
                badge: pendingApprovals,
            },

            { icon: ListIcon, name: 'Visa Applications', path: '/admin/visa-applications' },

    {
        icon: BoxCubeIcon,
        name: 'Visa Required Documents',
        path: '/admin/visa-documents',
    },
        ],

    },
    {
        title: 'Others',
        items: [
            { icon: BoxCubeIcon, name: 'Activity Logs', path: '/admin/activity-logs' },

        ],
    },
]

const consultantMenu = [
    {
        title: 'Menu',
        items: [
            { icon: GridIcon, name: 'Dashboard', path: '/consultant/dashboard' },
            { icon: ListIcon, name: 'Assigned Visas', path: '/consultant/visas' },

        ],
    },
]

const clientMenu = [
    {
        title: 'Menu',
        items: [
            { icon: GridIcon, name: 'Dashboard', path: '/client/dashboard' },
            { icon: ListIcon, name: 'My Visa Applications', path: '/client/visas' },
        ],
    },
]

/* ------------------------------------------------------------------
   FINAL MENU (AUTO SWITCH)
------------------------------------------------------------------ */
const menuGroups = computed(() => {
    if (isAdmin.value) return adminMenu
    if (isConsultant.value) return consultantMenu
    if (isClient.value) return clientMenu
    return []
})

/* ------------------------------------------------------------------
   ACTIVE ROUTE CHECK (INERTIA)
------------------------------------------------------------------ */
const isActive = (item) => {
    return item.components?.includes(page.component)
}




</script>

<template>
    <aside
        :class="[
            'fixed mt-16 flex flex-col lg:mt-0 top-0 px-5 left-0 bg-white dark:bg-gray-900 dark:border-gray-800 text-gray-900 h-screen transition-all duration-300 ease-in-out z-99999 border-r border-gray-200',
            {
                'lg:w-[290px]': isExpanded || isMobileOpen || isHovered,
                'lg:w-[90px]': !isExpanded && !isHovered,
                'translate-x-0 w-[290px]': isMobileOpen,
                '-translate-x-full': !isMobileOpen,
                'lg:translate-x-0': true,
            },
        ]"
        @mouseenter="!isExpanded && (isHovered = true)"
        @mouseleave="isHovered = false"
    >
        <!-- Logo -->
        <div
            :class="[
                'py-8 flex',
                !isExpanded && !isHovered ? 'lg:justify-center' : 'justify-start',
            ]"
        >
            <Link href="/dashboard">
                <img
                    v-if="isExpanded || isHovered || isMobileOpen"
                    class="dark:hidden"
                    :src="logo"
                    width="150"
                />
                <img
                    v-if="isExpanded || isHovered || isMobileOpen"
                    class="hidden dark:block"
                    :src="logoDark"
                    width="150"
                />
                <img v-else :src="logoIcon" width="32" />
            </Link>
        </div>

        <!-- Navigation -->
        <div class="flex flex-col overflow-y-auto no-scrollbar">
            <nav class="mb-6">
                <div class="flex flex-col gap-4">
                    <div
                        v-for="(menuGroup, groupIndex) in menuGroups"
                        :key="groupIndex"
                    >
                        <h2
                            :class="[
                                'mb-4 text-xs uppercase flex leading-[20px] text-gray-400',
                                !isExpanded && !isHovered
                                    ? 'lg:justify-center'
                                    : 'justify-start',
                            ]"
                        >
                            <template v-if="isExpanded || isHovered || isMobileOpen">
                                {{ menuGroup.title }}
                            </template>
                            <HorizontalDots v-else />
                        </h2>

                        <ul class="flex flex-col gap-4">
                            <li
                                v-for="item in menuGroup.items"
                                :key="item.name"
                            >
                                <Link
                                    :href="item.path"
                                    :class="[
                                        'menu-item group',
                                        isActive(item)
                                            ? 'menu-item-active'
                                            : 'menu-item-inactive',
                                        !isExpanded && !isHovered
                                            ? 'lg:justify-center'
                                            : 'lg:justify-start',
                                    ]"
                                >
                                    <span
                                        :class="[
                                            isActive(item)
                                                ? 'menu-item-icon-active'
                                                : 'menu-item-icon-inactive',
                                        ]"
                                    >
                                        <component :is="item.icon" />
                                    </span>

                                    <span
                                        v-if="isExpanded || isHovered || isMobileOpen"
                                        class="menu-item-text flex items-center gap-2"
                                    >
    {{ item.name }}

                                        <!-- Admin approval badge -->
    <span
        v-if="item.badge && item.badge > 0"
        class="ml-auto px-2 py-0.5 text-xs font-semibold text-white bg-red-500 rounded-full"
    >
        {{ item.badge }}
    </span>
</span>

                                </Link>
                            </li>
                        </ul>
                    </div>
                </div>
            </nav>

            <SidebarWidget v-if="isExpanded || isHovered || isMobileOpen" />
        </div>
    </aside>
</template>

<script setup>
import {ref, computed, onMounted, onUnmounted} from 'vue'
import {Link, usePage, router} from '@inertiajs/vue3'
import {
    UserCircleIcon,
    ChevronDownIcon,
    LogoutIcon,
} from '@/Components/Icons'

const page = usePage()
const user = computed(() => page.props.auth?.user)

const dropdownOpen = ref(false)
const dropdownRef = ref(null)

const toggleDropdown = () => (dropdownOpen.value = !dropdownOpen.value)
const closeDropdown = () => (dropdownOpen.value = false)

const logout = () => {
    router.post('/logout')
}

const handleClickOutside = (e) => {
    if (dropdownRef.value && !dropdownRef.value.contains(e.target)) {
        closeDropdown()
    }
}

onMounted(() => document.addEventListener('click', handleClickOutside))
onUnmounted(() => document.removeEventListener('click', handleClickOutside))
</script>

<template>
    <div class="relative" ref="dropdownRef">
        <!-- Trigger -->
        <button
            @click.prevent="toggleDropdown"
            class="
                flex items-center gap-3
                text-gray-700 dark:text-gray-300
                hover:text-gray-900 dark:hover:text-gray-100
                transition
            "
        >
            <div
                class="
                    h-10 w-10 rounded-full
                    bg-primary/10
                    flex items-center justify-center
                    text-primary
                "
            >
                <UserCircleIcon/>
            </div>

            <span class="font-medium">
                {{ user?.name }}
            </span>

            <ChevronDownIcon
                class="transition-transform"
                :class="{ 'rotate-180': dropdownOpen }"
            />
        </button>

        <!-- Dropdown -->
        <div
            v-if="dropdownOpen"
            class="
                absolute right-0 mt-4 w-64
                rounded-xl
                border border-gray-200 dark:border-gray-700
                bg-white dark:bg-gray-800
                p-3 shadow-lg
                z-50
            "
        >
            <!-- User Info -->
            <div class="mb-3">
                <p class="font-medium text-gray-900 dark:text-gray-100">
                    {{ user?.name }}
                </p>
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    {{ user?.email }}
                </p>
            </div>

            <!-- Profile -->
            <Link
                href="/profile"
                class="
                    flex items-center gap-2
                    px-3 py-2 rounded-lg
                    text-sm
                    text-gray-700 dark:text-gray-300
                    hover:bg-gray-100 dark:hover:bg-gray-700
                    transition
                "
            >
                <UserCircleIcon/>
                Edit Profile
            </Link>

            <!-- Logout -->
            <button
                @click="logout"
                class="
                    mt-2 flex w-full items-center gap-2
                    px-3 py-2 rounded-lg
                    text-sm font-medium
                    text-red-600 dark:text-red-400
                    hover:bg-red-50 dark:hover:bg-red-900/30
                    transition
                "
            >
                <LogoutIcon/>
                Logout
            </button>
        </div>
    </div>
</template>

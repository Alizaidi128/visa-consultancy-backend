<script setup>
import AppSidebar from '@/Components/Navigation/AppSidebar.vue'
import AppHeader from '@/Components/Navigation/AppHeader.vue'
import Backdrop from '@/Components/Layout/Backdrop.vue'
import { useSidebar } from '@/composables/useSidebar'

const { isExpanded, isHovered } = useSidebar()
</script>

<template>
    <div class="min-h-screen xl:flex bg-gray-50 dark:bg-gray-900">
        <AppSidebar />
        <Backdrop />

        <div
            class="flex-1 transition-all duration-300 ease-in-out"
            :class="isExpanded || isHovered ? 'lg:ml-[290px]' : 'lg:ml-[90px]'"
        >
            <AppHeader />

            <div class="p-4 mx-auto max-w-[1400px] md:p-6">
                <slot />
            </div>
        </div>
    </div>
</template>

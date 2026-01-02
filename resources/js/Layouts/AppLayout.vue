<script setup>
import ThemeProvider from '@/Components/Layout/ThemeProvider.vue'
import SidebarProvider from '@/Components/Layout/SidebarProvider.vue'
</script>

<template>
    <ThemeProvider>
        <SidebarProvider>
            <slot />
        </SidebarProvider>
    </ThemeProvider>
</template>

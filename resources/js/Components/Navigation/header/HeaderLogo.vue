<template>
    <Link href="/dashboard" class="lg:hidden flex items-center">
        <img
            class="dark:hidden"
            :src="logo"
            alt="Logo"
        />
        <img
            class="hidden dark:block"
            :src="logoDark"
            alt="Logo"
        />
    </Link>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'

import logo from '@/assets/images/logo/logo.png'
import logoDark from '@/assets/images/logo/logo-dark.png'
</script>

<script setup>

import logoIcon from '@/assets/images/logo/logo-icon.png'
</script>
<template>
  <div
    class="mx-auto mb-10 w-full max-w-60  rounded-2xl bg-gray-50 px-4 py-5 text-center dark:bg-white/[0.03]"
  >


<!--    <h3 class="mb-2 font-semibold text-gray-900 dark:text-white">Visa Simplify</h3>-->
    <p class="mb-4 text-gray-500 text-theme-sm dark:text-gray-400">
        <img
            class="mx-auto block dark:hidden"
            :src="logoIcon"
            width="80"
        />

        <img
            class="mx-auto block hidden dark:block"
            :src="logoIcon"
            width="80"
        />

        Leading Consultants
    </p>

  </div>
</template>

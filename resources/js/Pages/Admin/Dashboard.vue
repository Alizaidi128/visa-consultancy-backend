<script setup>

import PageHeader from '@/Components/PageHeader.vue'
import Card from '@/Components/Card.vue'

defineProps({
    stats: Object,
})
</script>

<template>
    <PageHeader title="Admin Dashboard" />

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
        <Card>
            <p class="text-sm text-gray-500">Total Clients</p>
            <p class="text-3xl font-semibold mt-2">{{ stats.clients }}</p>
        </Card>

        <Card>
            <p class="text-sm text-gray-500">Visa Applications</p>
            <p class="text-3xl font-semibold mt-2">{{ stats.visas }}</p>
        </Card>

        <Card>
            <p class="text-sm text-gray-500">Pending Documents</p>
            <p class="text-3xl font-semibold mt-2">{{ stats.pendingDocs }}</p>
        </Card>

        <Card>
            <p class="text-sm text-gray-500">Consultants</p>
            <p class="text-3xl font-semibold mt-2">{{ stats.consultants }}</p>
        </Card>
    </div>

    <Card>
        <h2 class="text-lg font-semibold mb-2">System Overview</h2>
        <p class="text-sm text-gray-500">
            Monitor platform usage, consultant workload, and client onboarding.
        </p>
    </Card>
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

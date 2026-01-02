<script setup>
import { ref, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import PageHeader from '@/Components/PageHeader.vue'

const props = defineProps({
    users: Object,
    highlightUser: [Number, String, null],
})

const highlightedId = ref(null)

/* ------------------------------------------------------------------
   HIGHLIGHT NEW USER
------------------------------------------------------------------ */
watch(
    () => props.highlightUser,
    (newValue) => {
        if (newValue) {
            highlightedId.value = Number(newValue)
            setTimeout(() => {
                highlightedId.value = null
            }, 3000)
        }
    },
    { immediate: true }
)

/* ------------------------------------------------------------------
   ROLE SELECTION
------------------------------------------------------------------ */
const selectedRoles = ref({})

function approve(userId) {
    const role = selectedRoles.value[userId]

    if (!role) {
        alert('Please select a role before approving.')
        return
    }

    router.post(
        `/admin/user-approvals/${userId}/approve`,
        { role },
        { preserveScroll: true }
    )
}

function reject(userId) {
    if (!confirm('Are you sure you want to reject this user?')) return

    router.post(
        `/admin/user-approvals/${userId}/reject`,
        {},
        { preserveScroll: true }
    )
}
</script>

<template>
    <PageHeader title="User Approvals" />

    <div
        class="
            bg-white dark:bg-gray-800
            rounded-xl shadow-sm
            overflow-hidden
        "
    >
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700">
            <tr>
                <th
                    class="
                            px-6 py-3 text-left text-xs font-semibold uppercase
                            text-gray-600 dark:text-gray-300
                        "
                >
                    Name
                </th>
                <th
                    class="
                            px-6 py-3 text-left text-xs font-semibold uppercase
                            text-gray-600 dark:text-gray-300
                        "
                >
                    Email
                </th>
                <th
                    class="
                            px-6 py-3 text-left text-xs font-semibold uppercase
                            text-gray-600 dark:text-gray-300
                        "
                >
                    Assign Role
                </th>
                <th
                    class="
                            px-6 py-3 text-right text-xs font-semibold uppercase
                            text-gray-600 dark:text-gray-300
                        "
                >
                    Actions
                </th>
            </tr>
            </thead>

            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
            <tr
                v-for="user in users.data"
                :key="user.id"
                :class="[
                        'transition',
                        highlightedId === user.id
                            ? 'bg-yellow-100 dark:bg-yellow-900/30'
                            : 'hover:bg-gray-50 dark:hover:bg-gray-700'
                    ]"
            >
                <!-- Name -->
                <td
                    class="
                            px-6 py-4 font-medium
                            text-gray-900 dark:text-gray-100
                        "
                >
                    {{ user.name }}
                </td>

                <!-- Email -->
                <td
                    class="
                            px-6 py-4 text-sm
                            text-gray-600 dark:text-gray-400
                        "
                >
                    {{ user.email }}
                </td>

                <!-- Role selector -->
                <td class="px-6 py-4">
                    <select
                        v-model="selectedRoles[user.id]"
                        class="
                                w-full rounded-lg border
                                border-gray-300 dark:border-gray-600
                                bg-white dark:bg-gray-900
                                text-gray-900 dark:text-gray-100
                                text-sm
                                focus:ring-primary focus:border-primary
                            "
                    >
                        <option disabled value="">Select role</option>
                        <option value="client">Client</option>
                        <option value="consultant">Consultant</option>
                        <option value="admin">Admin</option>
                    </select>
                </td>

                <!-- Actions -->
                <td class="px-6 py-4 text-right space-x-4">
                    <button
                        @click="approve(user.id)"
                        class="
                                text-sm font-medium
                                text-green-600 dark:text-green-400
                                hover:underline
                            "
                    >
                        Approve
                    </button>

                    <button
                        @click="reject(user.id)"
                        class="
                                text-sm font-medium
                                text-red-600 dark:text-red-400
                                hover:underline
                            "
                    >
                        Reject
                    </button>
                </td>
            </tr>
            </tbody>
        </table>

        <!-- Empty state -->
        <div
            v-if="!users.data.length"
            class="
                p-6 text-sm text-center
                text-gray-500 dark:text-gray-400
            "
        >
            No users awaiting approval.
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

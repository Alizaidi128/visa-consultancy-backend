<script setup>
import PageHeader from '@/Components/PageHeader.vue'
import StatusBadge from '@/Components/StatusBadge.vue'
import { router } from '@inertiajs/vue3'
import VisaTimeline from '@/Components/VisaTimeline.vue'
import { ref } from 'vue'

const props = defineProps({
    visa: Object,
    activityLogs: Array,
    consultants: Array,
})

function approveDocument(docId) {
    router.post(
        `/documents/${docId}/approve`,
        {},
        { preserveScroll: true, preserveState: true }
    )
}

function rejectDocument(doc) {
    if (!doc._reject_reason || doc._reject_reason.trim() === '') {
        alert('Please enter a rejection reason')
        return
    }

    router.post(
        `/documents/${doc.id}/reject`,
        { remarks: doc._reject_reason },
        {
            preserveScroll: true,
            onError: (errors) => {
                console.error('Reject failed', errors)
            }
        }
    )
}



function updateStatus(status) {
    if (status === 'approved') {
        router.post(`/visas/${props.visa.id}/approve`, {}, {
            preserveScroll: true,
        })
    }

    if (status === 'rejected') {
        router.post(`/visas/${props.visa.id}/reject`, {
            remarks: 'Rejected by admin',
        }, {
            preserveScroll: true,
        })
    }
}





const selectedConsultant = ref(null)
const successMessage = ref(false)

function assignConsultant() {
    if (!selectedConsultant.value) {
        alert('Please select a consultant')
        return
    }

    router.post(
        `/admin/visa-applications/${props.visa.id}/assign-consultant`,
        { consultant_id: selectedConsultant.value },
        {
            onSuccess: () => {
                successMessage.value = true

                setTimeout(() => {
                    successMessage.value = false
                }, 3000)
            },
        }
    )
}
</script>

<template>
    <PageHeader
        title="Visa Application"
        subtitle="Application details"
    />

    <!-- Visa Summary -->
    <div
        class="
            bg-white dark:bg-gray-800
            rounded-xl shadow-sm
            p-6 mb-6
        "
    >
        <h2
            class="
                text-sm font-semibold uppercase mb-4
                text-gray-600 dark:text-gray-300
            "
        >
            Visa Information
        </h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
            <div>
                <p class="text-gray-500 dark:text-gray-400">Client</p>
                <p class="font-medium text-gray-900 dark:text-gray-100">
                    {{ visa.client.first_name }} {{ visa.client.last_name }}
                </p>
            </div>

            <div>
                <p class="text-gray-500 dark:text-gray-400">Country</p>
                <p class="font-medium text-gray-900 dark:text-gray-100">
                    {{ visa.country }}
                </p>
            </div>

            <div>
                <p class="text-gray-500 dark:text-gray-400">Status</p>
                <StatusBadge :status="visa.status" />
            </div>

            <div>
                <p class="text-gray-500 dark:text-gray-400">Created At</p>
                <p class="font-medium text-gray-900 dark:text-gray-100">
                    {{ visa.created_at }}
                </p>
            </div>
        </div>
    </div>
    <!-- Success Message -->
    <div
        v-if="successMessage"
        class="
        mb-4 px-4 py-3 rounded-lg
        bg-green-100 text-green-800
        dark:bg-green-900/40 dark:text-green-200
        text-sm font-medium
    "
    >
        Consultant assigned successfully.
    </div>

    <!-- Assign Consultant -->
    <div
        v-if="!visa.consultant_id"
        class="
        bg-white dark:bg-gray-800
        rounded-xl shadow-sm
        p-6 mb-6
    "
    >
        <h2 class="text-sm font-semibold uppercase mb-4 text-gray-600 dark:text-gray-300">
            Assign Consultant
        </h2>

        <div class="flex items-center gap-4">
            <select
                v-model="selectedConsultant"
                class="
                border rounded-lg px-3 py-2
                bg-white dark:bg-gray-900
                text-gray-900 dark:text-gray-100
                border-gray-300 dark:border-gray-600
            "
            >
                <option disabled value="">Select consultant</option>
                <option
                    v-for="c in consultants"
                    :key="c.id"
                    :value="c.id"
                >
                    {{ c.name }} ({{ c.email }})
                </option>
            </select>

            <button
                @click="assignConsultant"
                class="
                px-4 py-2 rounded-lg
                bg-blue-600 hover:bg-blue-700
                text-white text-sm font-medium
            "
            >
                Assign
            </button>
        </div>
    </div>

    <!-- Final Decision -->
    <div
        v-if="visa.status === 'submitted'"
        class="
            bg-green-50 dark:bg-green-900/30
            border border-green-200 dark:border-green-700
            rounded-lg p-4 mb-6
            flex flex-col sm:flex-row
            items-start sm:items-center
            justify-between gap-4
        "
    >
        <p
            class="
                text-sm
                text-green-800 dark:text-green-300
            "
        >
            This visa application has been submitted and is ready for final decision.
        </p>

        <div class="flex gap-3">
            <button
                @click="updateStatus('approved')"
                class="
                    px-4 py-2 rounded-lg
                    bg-green-600 hover:bg-green-700
                    text-white text-sm font-medium
                "
            >
                Approve Visa
            </button>

            <button
                @click="updateStatus('rejected')"
                class="
                    px-4 py-2 rounded-lg
                    bg-red-600 hover:bg-red-700
                    text-white text-sm font-medium
                "
            >
                Reject Visa
            </button>
        </div>
    </div>

    <!-- Documents Review -->
    <div
        class="
            bg-white dark:bg-gray-800
            rounded-xl shadow-sm
            overflow-hidden
        "
    >
        <div
            class="
                px-6 py-4 border-b
                border-gray-200 dark:border-gray-700
            "
        >
            <h2
                class="
                    text-sm font-semibold uppercase
                    text-gray-600 dark:text-gray-300
                "
            >
                Documents
            </h2>
        </div>

        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700">
            <tr>
                <th
                    class="
                            px-6 py-3 text-left text-xs font-semibold uppercase
                            text-gray-600 dark:text-gray-300
                        "
                >
                    Document
                </th>
                <th
                    class="
                            px-6 py-3 text-center text-xs font-semibold uppercase
                            text-gray-600 dark:text-gray-300
                        "
                >
                    Status
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
                v-for="doc in visa.documents"
                :key="doc.id"
                class="hover:bg-gray-50 dark:hover:bg-gray-700 transition"
            >
                <td class="px-6 py-4">
                    <div class="flex flex-col">
                            <span class="font-medium text-gray-900 dark:text-gray-100">
                                {{ doc.type }}
                            </span>

                        <a
                            :href="`/storage/${doc.file_path}`"
                            target="_blank"
                            class="
                                    mt-1 text-xs
                                    text-primary dark:text-gray-200
                                    hover:underline
                                "
                        >
                            View document
                        </a>
                    </div>
                </td>

                <td class="px-6 py-4 text-center">
                    <StatusBadge :status="doc.status" />
                </td>

                <td class="px-6 py-4 text-right space-x-3">
                    <button
                        v-if="doc.status !== 'approved'"
                        @click="approveDocument(doc.id)"
                        class="
                                text-sm font-medium
                                text-green-600 dark:text-green-400
                                hover:underline
                            "
                    >
                        Approve
                    </button>

                    <div
                        v-if="doc.status !== 'rejected'"
                        class="inline-flex items-center gap-2"
                    >
                        <input
                            v-model="doc._reject_reason"
                            type="text"
                            placeholder="Reason"
                            class="
                                    w-32 rounded-lg border
                                    border-gray-300 dark:border-gray-600
                                    bg-white dark:bg-gray-900
                                    text-gray-900 dark:text-gray-100
                                    text-sm px-2 py-1
                                "
                        />

                        <button
                            @click="rejectDocument(doc)"
                            class="
                                    text-sm font-medium
                                    text-red-600 dark:text-red-400
                                    hover:underline
                                "
                        >
                            Reject
                        </button>
                    </div>
                </td>
            </tr>

            <tr v-if="!visa.documents.length">
                <td
                    colspan="3"
                    class="
                            px-6 py-10 text-center text-sm
                            text-gray-500 dark:text-gray-400
                        "
                >
                    No documents uploaded yet.
                </td>
            </tr>
            </tbody>
        </table>
    </div>

    <VisaTimeline :logs="activityLogs" class="mt-6" />
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

<script setup>
import PageHeader from '@/Components/PageHeader.vue'
import StatusBadge from '@/Components/StatusBadge.vue'
import VisaTimeline from '@/Components/VisaTimeline.vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
    visa: Object,
    activityLogs: Array,
})

function approveDocument(docId) {
    router.post(`/documents/${docId}/approve`)
}

function rejectDocument(doc) {
    if (!doc._reject_reason) {
        alert('Please enter a rejection reason')
        return
    }

    router.post(`/documents/${doc.id}/reject`, {
        remarks: doc._reject_reason,
    })
}

function approveVisa() {
    router.post(`/visas/${props.visa.id}/approve`)
}

function rejectVisa() {
    const remarks = prompt('Enter rejection reason')
    if (!remarks) return

    router.post(`/visas/${props.visa.id}/reject`, {
        remarks,
    })
}
function allDocumentsApproved() {
    return props.visa.documents.length > 0 &&
        props.visa.documents.every(d => d.status === 'approved')
}

</script>

<template>
    <PageHeader title="Visa Details" />

    <!-- Visa Summary -->
    <div
        class="
            bg-white dark:bg-gray-800
            rounded-xl shadow-sm
            p-6 mb-6
        "
    >
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
                <p class="text-gray-500 dark:text-gray-400">Created</p>
                <p class="font-medium text-gray-900 dark:text-gray-100">
                    {{ visa.created_at }}
                </p>
            </div>
        </div>
    </div>

    <!-- Final Decision -->
    <div
        v-if="visa.status === 'submitted' && allDocumentsApproved()"
        class="
            bg-green-50 dark:bg-green-900/30
            border border-green-200 dark:border-green-700
            rounded-lg p-4 mb-6
            flex flex-col sm:flex-row
            items-start sm:items-center
            justify-between gap-4
        "
    >
        <p class="text-sm text-green-800 dark:text-green-300">
            This visa application has been submitted and is ready for final decision.
        </p>

        <div class="flex gap-3">
            <button
                @click="approveVisa"
                class="
                    px-4 py-2 rounded-lg
                    bg-green-600 hover:bg-green-700
                    text-white text-sm font-medium
                "
            >
                Approve Visa
            </button>

            <button
                @click="rejectVisa"
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
                <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-600 dark:text-gray-300">
                    Document
                </th>
                <th class="px-6 py-3 text-center text-xs font-semibold uppercase text-gray-600 dark:text-gray-300">
                    Status
                </th>
                <th class="px-6 py-3 text-right text-xs font-semibold uppercase text-gray-600 dark:text-gray-300">
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
                            class="mt-1 text-xs text-primary hover:underline"
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
                        class="text-sm font-medium text-green-600 hover:underline"
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
                            class="text-sm font-medium text-red-600 hover:underline"
                        >
                            Reject
                        </button>
                    </div>
                </td>
            </tr>

            <tr v-if="!visa.documents.length">
                <td
                    colspan="3"
                    class="px-6 py-10 text-center text-sm text-gray-500 dark:text-gray-400"
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
import ConsultantLayout from '@/Layouts/ConsultantLayout.vue'

export default {
    layout: (h, page) =>
        h(AppLayout, () =>
            h(ConsultantLayout, () => page)
        ),
}
</script>

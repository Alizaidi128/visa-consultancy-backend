<script setup>
import PageHeader from '@/Components/PageHeader.vue'
import StatusBadge from '@/Components/StatusBadge.vue'
import { router } from '@inertiajs/vue3'
import VisaTimeline from '@/Components/VisaTimeline.vue'
import { ref } from 'vue'
import { computed } from 'vue'

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
const selectedFamilyMembers = ref([])

function attachFamily() {
    if (!selectedFamilyMembers.value.length) return

    router.post(
        `/admin/visa-applications/${props.visa.id}/family-members`,
        { family_member_ids: selectedFamilyMembers.value },
        {
            preserveScroll: true,
            onSuccess: () => {
                // Move attached members from available → attached
                selectedFamilyMembers.value.forEach(id => {
                    const member = props.visa.client.family_members.find(m => m.id === id)
                    if (member) {
                        attachedMembers.value.push(member)
                    }
                })

                selectedFamilyMembers.value = []
            },
        }
    )
}


function removeFamily(memberId) {
    router.delete(
        `/admin/visa-applications/${props.visa.id}/family-members/${memberId}`,
        {
            preserveScroll: true,
            onSuccess: () => {
                attachedMembers.value = attachedMembers.value.filter(
                    m => m.id !== memberId
                )
            },
        }
    )
}


/**
 * Local reactive copies (DO NOT mutate props directly)
 */
const attachedMembers = ref([...props.visa.family_members])
const availableFamilyMembers = computed(() => {
    const attachedIds = attachedMembers.value.map(m => m.id)

    return props.visa.client.family_members.filter(
        m => !attachedIds.includes(m.id)
    )
})

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
    <!-- FAMILY MEMBERS ON VISA -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-6 mb-6">
        <div class="flex items-center justify-between mb-5">
            <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-300">
                Family Members on Visa
            </h2>

            <span class="text-xs text-gray-500 dark:text-gray-400">
            Select dependents applying with this visa
        </span>
        </div>

        <!-- ATTACH FAMILY FORM -->
        <form
            @submit.prevent="attachFamily"
            class="flex flex-col gap-4 max-w-xl"
        >
            <!-- MULTI SELECT -->
            <div>
                <label class="block mb-1 text-xs font-medium text-gray-600 dark:text-gray-400">
                    Available Family Members
                </label>

                <select
                    v-model="selectedFamilyMembers"
                    multiple
                    class="
                w-full min-h-[120px]
                rounded-xl border
                border-gray-300 dark:border-gray-600
                bg-white dark:bg-gray-900
                text-gray-900 dark:text-gray-100
                px-3 py-2 text-sm
                focus:outline-none focus:ring-2 focus:ring-blue-500
            "
                >
                    <option
                        v-for="m in availableFamilyMembers"
                        :key="m.id"
                        :value="m.id"
                    >
                        {{ m.first_name }} {{ m.last_name }} — {{ m.relationship }}
                    </option>
                </select>

                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    Hold <kbd class="px-1 border rounded">Ctrl</kbd> /
                    <kbd class="px-1 border rounded">Cmd</kbd> to select multiple
                </p>
            </div>

            <!-- ATTACH BUTTON (NOW PROPERLY PLACED) -->
            <button
                type="submit"
                class="
            self-start
            px-5 py-2.5 rounded-xl
            bg-blue-600 hover:bg-blue-700
            text-white text-sm font-medium
            transition
            disabled:opacity-50
        "
            >
                Attach
            </button>
        </form>


        <!-- ATTACHED FAMILY MEMBERS -->
        <div v-if="visa.family_members.length" class="mt-6">
            <h3 class="mb-3 text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">
                Attached to this Visa
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div
                    v-for="m in attachedMembers"
                    :key="m.id"
                    class="
                    flex items-center justify-between
                    rounded-xl border
                    border-gray-200 dark:border-gray-700
                    bg-gray-50 dark:bg-gray-900
                    px-4 py-3
                "
                >
                    <div>
                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                            {{ m.first_name }} {{ m.last_name }}
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 capitalize">
                            {{ m.relationship }}
                        </p>
                    </div>

                    <button
                        @click="removeFamily(m.id)"
                        class="
                        text-xs font-medium
                        text-red-600 dark:text-red-400
                        hover:underline
                    "
                    >
                        Remove
                    </button>
                </div>
            </div>
        </div>

        <!-- EMPTY STATE -->
        <div
            v-else
            class="mt-6 text-sm text-gray-500 dark:text-gray-400 italic"
        >
            No family members attached to this visa yet.
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

<script setup>
import PageHeader from '@/Components/PageHeader.vue'
import StatusBadge from '@/Components/StatusBadge.vue'
import VisaTimeline from '@/Components/VisaTimeline.vue'
import { router } from '@inertiajs/vue3'
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import { watch } from 'vue'


const page = usePage()
const successMessage = computed(() => page.props.flash?.success)

/* 🔹 NEW COMPONENTS (LEVEL 1 + 2) */
import DocumentUpload from '@/Components/Documents/DocumentUpload.vue'
import BulkDocumentUpload from '@/Components/Documents/BulkDocumentUpload.vue'

const props = defineProps({
    visa: Object,
    requiredDocuments: Array,
    activityLogs: Array,
})

/* -------------------------------------------------------
   HELPERS
------------------------------------------------------- */
function uploadedDoc(type) {
    return props.visa.documents
        .filter(d => d.type === type && d.status !== 'archived')
        .sort((a, b) => new Date(b.created_at) - new Date(a.created_at))[0]
}



/* -------------------------------------------------------
   DOCUMENT UPLOAD HANDLERS
------------------------------------------------------- */
function uploadSingle({ file, type, done }) {
    const formData = new FormData()
    formData.append('file', file)
    formData.append('type', type)

    router.post(
        `/client/visa-applications/${props.visa.id}/documents`,
        formData,
        {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: () => {
                router.reload({ only: ['visa'] })
                done?.()
            },
            onError: () => {
                done?.()
            }
        }
    )
}



/* -------------------------------------------------------
   SUBMIT VISA
------------------------------------------------------- */
const canSubmit = computed(() => {
    if (['submitted', 'approved', 'rejected'].includes(props.visa.status)) {
        return false
    }

    return props.requiredDocuments.every(req => {
        const doc = uploadedDoc(req.document_type)


        if (req.is_mandatory) {
            return doc && doc.status === 'approved'
        }

        return true
    })
})



function submitVisa() {
    router.post(
        `/client/visas/${props.visa.id}/submit`,
        {},
        { preserveScroll: true }
    )
}


watch(
    () => props.visa.documents,
    (docs) => {
        console.log('VISA DOCUMENTS FROM INERTIA:', docs)
    },
    { immediate: true }
)
</script>

<template>
    <PageHeader title="Visa Details" />
    <div
        v-if="successMessage"
        class="mb-4 rounded-lg bg-green-50 dark:bg-green-900/30
           border border-green-200 dark:border-green-700
           p-3 text-sm text-green-800 dark:text-green-200"
    >
        {{ successMessage }}
    </div>

    <!-- Visa Summary -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 mb-6">
        <div class="grid grid-cols-2 gap-4 text-sm">
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
        </div>
    </div>
    <!-- DEADLINES -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 mb-6">
        <h2 class="text-sm font-semibold uppercase mb-4 text-gray-600 dark:text-gray-300">
            Important Dates
        </h2>

        <div v-if="visa.deadlines?.length" class="space-y-3">
            <div
                v-for="d in visa.deadlines"
                :key="d.id"
                class="
                flex items-center justify-between
                rounded-lg border
                border-gray-200 dark:border-gray-700
                px-4 py-3
            "
            >
                <div>
                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                        {{ d.title }}
                    </p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Due by {{ new Date(d.due_date).toLocaleDateString() }}
                    </p>
                </div>

                <span
                    class="text-xs font-medium px-2 py-1 rounded-full"
                    :class="{
                    'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-200': d.status === 'pending',
                    'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-200': d.status === 'completed',
                    'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-200': d.status === 'missed',
                }"
                >
                {{ d.status }}
            </span>
            </div>
        </div>

        <p
            v-else
            class="text-sm text-gray-500 dark:text-gray-400 italic"
        >
            No important dates have been added yet.
        </p>
    </div>

    <!-- Submit Alert -->
    <div
        v-if="canSubmit"
        class="
            bg-yellow-50 dark:bg-yellow-900/30
            border border-yellow-200 dark:border-yellow-700
            rounded-lg p-4 mb-6
            flex items-center justify-between
        "
    >
        <p class="text-sm text-yellow-800 dark:text-yellow-200">
            All required documents are uploaded. You can now submit your application.
        </p>

        <button
            @click="submitVisa"
            class="
                px-4 py-2 rounded-lg text-sm font-medium
                bg-primary text-white hover:opacity-90
            "
        >
            Submit Application
        </button>
    </div>

    <!-- Bulk Upload (LEVEL 2) -->
    <BulkDocumentUpload
        class="mb-6"
        :visa-id="visa.id"
        :required-documents="requiredDocuments"
    />



    <!-- Required Documents (LEVEL 1) -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase">
                Required Documents
            </h2>
        </div>

        <div class="divide-y divide-gray-200 dark:divide-gray-700">
            <DocumentUpload
                v-for="req in requiredDocuments"
                :key="req.id"
                :label="req.document_type"
                :mandatory="req.is_mandatory"
                :existing="uploadedDoc(req.document_type)"
                @upload="payload => uploadSingle({
    file: payload.file,
    type: req.document_type,
    done: payload.done
})"

            />

            <div
                v-if="!requiredDocuments.length"
                class="p-6 text-center text-sm text-gray-500 dark:text-gray-400"
            >
                No required documents configured.
            </div>
        </div>
    </div>

    <!-- Timeline -->
    <VisaTimeline :logs="activityLogs" class="mt-6" />
</template>

<script>
import AppLayout from '@/Layouts/AppLayout.vue'
import ClientLayout from '@/Layouts/ClientLayout.vue'

export default {
    layout: (h, page) =>
        h(AppLayout, () =>
            h(ClientLayout, () => page)
        ),
}
</script>

<script setup>
import { ref, watch } from 'vue'
import { usePage, router } from '@inertiajs/vue3'
import Dropzone from './Dropzone.vue'

/* -------------------------------------------------------
   PROPS
------------------------------------------------------- */
const props = defineProps({
    visaId: {
        type: Number,
        required: true,
    },
    requiredDocuments: {
        type: Array,
        default: () => [],
    },
})
const successMessage = ref(null)

const page = usePage()

/* -------------------------------------------------------
   STATE
------------------------------------------------------- */
const uploaded = ref([])
const unmatched = ref([])
const assignments = ref({})
const uploading = ref(false)


/* -------------------------------------------------------
   BULK DROP HANDLER
------------------------------------------------------- */
function handleBulk(files) {
    if (!files.length) return

    uploading.value = true

    const formData = new FormData()
    files.forEach(file => {
        formData.append('files[]', file)
    })

    router.post(
        `/client/visa-applications/${props.visaId}/documents/bulk`,
        formData,
        {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: () => {
                uploading.value = false

                successMessage.value = 'Documents uploaded successfully'

                setTimeout(() => {
                    successMessage.value = null
                }, 3000)

                router.reload({
                    only: ['visa', 'activityLogs'],
                    preserveScroll: true,
                })
            },

        }
    )
}

/* -------------------------------------------------------
   MANUAL ASSIGN FOR UNMATCHED FILES
------------------------------------------------------- */
function assignAndUpload(file) {
    const type = assignments.value[file.name]
    if (!type) {
        alert('Please select document type')
        return
    }

    const formData = new FormData()
    formData.append('file', file.file ?? file)
    formData.append('type', type)

    router.post(
        `/client/visa-applications/${props.visaId}/documents`,
        formData,
        {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: () => {
                unmatched.value = unmatched.value.filter(
                    f => f.name !== file.name
                )

                router.visit(window.location.href, {
                    preserveScroll: true,
                    preserveState: false,
                })

            },
        }
    )
}
</script>

<template>
    <div
        class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-5"
    >
        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-200 mb-1">
            Upload All Documents
        </h3>

        <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">
            Drag & drop multiple files. We’ll auto-match what we can.
        </p>
        <div
            v-if="successMessage"
            class="mb-3 rounded-lg bg-green-50 dark:bg-green-900/30
           border border-green-200 dark:border-green-700
           p-3 text-sm text-green-800 dark:text-green-200"
        >
            {{ successMessage }}
        </div>


        <!-- Dropzone -->
        <Dropzone
            :disabled="uploading"
            @files="handleBulk"
        />

        <div
            v-if="uploading"
            class="mt-3 flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300"
        >
            <svg
                class="animate-spin h-4 w-4 text-primary dark:text-primary-300"
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
            >
                <circle
                    class="opacity-25"
                    cx="12"
                    cy="12"
                    r="10"
                    stroke="currentColor"
                    stroke-width="4"
                />
                <path
                    class="opacity-75"
                    fill="currentColor"
                    d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"
                />
            </svg>

            Uploading documents…
        </div>


        <!-- Uploaded -->
        <div v-if="uploaded.length" class="mt-5">
            <h4 class="text-xs font-semibold text-green-600 mb-2">
                Uploaded
            </h4>
            <ul class="text-sm space-y-1">
                <li v-for="u in uploaded" :key="u.file">
                    ✔ {{ u.file }} → {{ u.type }}
                </li>
            </ul>
        </div>

        <!-- Unmatched -->
        <div v-if="unmatched.length" class="mt-5">
            <h4 class="text-xs font-semibold text-yellow-600 mb-2">
                Needs Assignment
            </h4>

            <div
                v-for="file in unmatched"
                :key="file.name"
                class="flex items-center gap-3 mb-2"
            >
                <span class="text-sm flex-1">{{ file.name }}</span>

                <select
                    v-model="assignments[file.name]"
                    class="
                        rounded-md border
                        border-gray-300 dark:border-gray-600
                        bg-white dark:bg-gray-700
                        text-sm px-2 py-1
                    "
                >
                    <option value="">Select type</option>
                    <option
                        v-for="doc in requiredDocuments"
                        :key="doc.id"
                        :value="doc.document_type"
                    >
                        {{ doc.document_type }}
                    </option>
                </select>

                <button
                    @click="assignAndUpload(file)"
                    class="text-sm text-primary hover:underline"
                >
                    Upload
                </button>
            </div>
        </div>
    </div>
</template>

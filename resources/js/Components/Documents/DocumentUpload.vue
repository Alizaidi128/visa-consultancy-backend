<script setup>
import Dropzone from './Dropzone.vue'
import StatusBadge from '@/Components/StatusBadge.vue'
import { ref } from 'vue'

const props = defineProps({
    label: String,
    mandatory: Boolean,
    existing: Object,
})

const emit = defineEmits(['upload'])

const uploading = ref(false)
const uploaded = ref(false)

function handleFiles(files) {
    if (!files.length) return

    uploading.value = true
    uploaded.value = false

    emit('upload', {
        file: files[0],
        done: () => {
            uploading.value = false
            uploaded.value = true

            // auto-hide success message
            setTimeout(() => {
                uploaded.value = false
            }, 3000)
        },
    })
}
</script>

<template>
    <div class="px-6 py-4 flex flex-col gap-3">
        <!-- Header -->
        <div class="flex justify-between items-center">
            <div>
                <p class="font-medium text-gray-900 dark:text-gray-100">
                    {{ label }}
                    <span v-if="mandatory" class="text-red-500">*</span>
                </p>
            </div>

            <StatusBadge
                v-if="existing"
                :status="existing.status"
            />
            <span
                v-else
                class="text-xs text-gray-400"
            >
                Not uploaded
            </span>
        </div>

        <!-- Existing file name -->
        <div
            v-if="existing"
            class="text-xs text-gray-500 dark:text-gray-400"
        >
            {{ existing.original_name }}
        </div>

        <!-- Upload zone -->
        <Dropzone
            v-if="!existing || existing.status !== 'approved'"
            :disabled="uploading"
            @files="handleFiles"
        />

        <!-- Uploading state -->
        <div
            v-if="uploading"
            class="flex items-center gap-2 text-xs text-gray-600 dark:text-gray-300"
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

            Uploading…
        </div>


        <!-- Success state -->
        <p
            v-if="uploaded"
            class="text-xs text-green-600 dark:text-green-400"
        >
            ✔ Document uploaded successfully
        </p>

        <!-- Approved lock -->
        <p
            v-if="existing && existing.status === 'approved'"
            class="text-xs text-gray-400"
        >
            Approved — upload disabled
        </p>
    </div>
</template>

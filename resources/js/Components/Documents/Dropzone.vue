<script setup>
import { ref } from 'vue'

const emit = defineEmits(['files'])

const inputRef = ref(null)
const isDragging = ref(false)

/* ------------------------------------
   DRAG & DROP
------------------------------------ */
function onDragEnter(e) {
    e.preventDefault()
    isDragging.value = true
}

function onDragOver(e) {
    e.preventDefault()
    isDragging.value = true
}

function onDragLeave(e) {
    e.preventDefault()
    isDragging.value = false
}

function onDrop(e) {
    e.preventDefault()
    e.stopPropagation()
    isDragging.value = false

    if (e.dataTransfer?.files?.length) {
        emit('files', Array.from(e.dataTransfer.files))
    }
}

/* ------------------------------------
   BROWSE CLICK
------------------------------------ */
function openFileDialog() {
    inputRef.value?.click()
}

function onBrowse(e) {
    if (e.target.files?.length) {
        emit('files', Array.from(e.target.files))
        e.target.value = null // reset so same file can be re-selected
    }
}
</script>

<template>
    <div
        role="button"
        tabindex="0"
        @click="openFileDialog"
        @keydown.enter.prevent="openFileDialog"
        @dragenter="onDragEnter"
        @dragover.prevent="onDragOver"
        @dragleave="onDragLeave"
        @drop="onDrop"
        class="
            flex flex-col items-center justify-center
            border-2 border-dashed rounded-lg
            p-6 text-center cursor-pointer
            transition-all duration-150
            border-gray-300 dark:border-gray-600
            text-gray-600 dark:text-gray-400
            hover:border-primary
            focus:outline-none
            focus:ring-2 focus:ring-primary/40
        "
        :class="{
            'bg-primary/5 border-primary text-primary': isDragging
        }"
    >
        <p class="text-sm font-medium">
            Drag & drop files here
        </p>

        <p class="text-xs mt-1">
            or <span class="text-primary font-medium">click to browse</span>
        </p>

        <input
            ref="inputRef"
            type="file"
            multiple
            class="hidden"
            @change="onBrowse"
        />
    </div>
</template>

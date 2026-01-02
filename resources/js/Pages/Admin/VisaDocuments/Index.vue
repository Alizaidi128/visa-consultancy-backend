<script setup>
import { ref, watch, computed } from 'vue'
import axios from 'axios'
import { useForm } from '@inertiajs/vue3'
import PageHeader from '@/Components/PageHeader.vue'

const props = defineProps({
    countries: Array,
    documents: Array,
})

const selectedCountry = ref(null)
const visaTypes = ref([])
const selectedVisaType = ref(null)
const successMessage = ref(null)

const form = useForm({
    visa_type_id: null,
    documents: [],
})

watch(selectedCountry, async (countryId) => {
    selectedVisaType.value = null
    visaTypes.value = []
    form.documents = []
    successMessage.value = null

    if (!countryId) return

    const { data } = await axios.get(
        `/admin/countries/${countryId}/visa-types`
    )

    visaTypes.value = data
})

watch(selectedVisaType, async (visaTypeId) => {
    form.documents = []
    successMessage.value = null

    if (!visaTypeId) return

    form.visa_type_id = visaTypeId

    const { data } = await axios.get(
        `/admin/visa-types/${visaTypeId}/documents`
    )

    form.documents = props.documents.map(doc => {
        const mapped = data.find(d => d.id === doc.id)

        return {
            id: doc.id,
            document_type: doc.document_type,
            selected: !!mapped,
            is_mandatory: mapped ? !!mapped.is_mandatory : false,
        }
    })
})

watch(
    () => form.documents,
    (docs) => {
        docs.forEach(doc => {
            if (!doc.selected) {
                doc.is_mandatory = false
            }
        })
    },
    { deep: true }
)

const allSelected = computed({
    get() {
        return form.documents.length > 0 &&
            form.documents.every(d => d.selected)
    },
    set(value) {
        form.documents.forEach(d => {
            d.selected = value
            if (!value) d.is_mandatory = false
        })
    },
})

const allMandatory = computed({
    get() {
        const selectedDocs = form.documents.filter(d => d.selected)
        return selectedDocs.length > 0 &&
            selectedDocs.every(d => d.is_mandatory)
    },
    set(value) {
        form.documents.forEach(d => {
            if (d.selected) {
                d.is_mandatory = value
            }
        })
    },
})

function submit() {
    form.post('/admin/visa-documents', {
        preserveScroll: true,
        onSuccess: () => {
            successMessage.value = 'Mapping saved successfully'
        },
    })
}
</script>

<template>
    <div class="space-y-6">

        <PageHeader
            title="Visa Required Documents Mapping"
            subtitle="Configure mandatory and optional documents per visa type"
        />

        <div
            class="max-w-4xl bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 space-y-6"
        >

            <!-- Country -->
            <div>
                <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">
                    Country
                </label>
                <select
                    v-model="selectedCountry"
                    class="w-full rounded-lg border border-gray-300 dark:border-gray-600
                           bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100"
                >
                    <option value="">Select country</option>
                    <option v-for="c in countries" :key="c.id" :value="c.id">
                        {{ c.name }}
                    </option>
                </select>
            </div>

            <!-- Visa Type -->
            <div v-if="visaTypes.length">
                <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">
                    Visa Type
                </label>
                <select
                    v-model="selectedVisaType"
                    class="w-full rounded-lg border border-gray-300 dark:border-gray-600
                           bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100"
                >
                    <option value="">Select visa type</option>
                    <option v-for="v in visaTypes" :key="v.id" :value="v.id">
                        {{ v.name }}
                    </option>
                </select>
            </div>

            <!-- Documents -->
            <div v-if="form.documents.length" class="space-y-4">

                <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300">
                    Required Documents
                </h3>

                <div
                    class="flex items-center justify-between
                           bg-gray-50 dark:bg-gray-900
                           border-2 border-dashed border-gray-400 dark:border-gray-600
                           rounded-lg p-4"
                >
                    <label class="flex items-center gap-3 font-semibold text-gray-800 dark:text-gray-100">
                        <input type="checkbox" v-model="allSelected" class="w-5 h-5 accent-brand-500"/>
                        Select All Documents
                    </label>

                    <label class="flex items-center gap-3 font-semibold text-gray-800 dark:text-gray-100">
                        <input
                            type="checkbox"
                            v-model="allMandatory"
                            class="w-5 h-5 accent-red-500"
                            :disabled="!allSelected"
                        />
                        Mark All Mandatory
                    </label>
                </div>

                <div
                    v-for="doc in form.documents"
                    :key="doc.id"
                    class="flex items-center justify-between p-3
                           border border-gray-200 dark:border-gray-700 rounded-lg"
                >
                    <label class="flex items-center gap-3">
                        <input type="checkbox" v-model="doc.selected"/>
                        <span class="text-gray-800 dark:text-gray-200">
                            {{ doc.document_type }}
                        </span>
                    </label>

                    <label class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                        <input
                            type="checkbox"
                            v-model="doc.is_mandatory"
                            :disabled="!doc.selected"
                        />
                        Mandatory
                    </label>
                </div>
            </div>

            <!-- Actions -->
            <!-- Actions -->
            <div class="flex flex-col items-center gap-3 pt-6">

                <button
                    v-if="form.documents.length"
                    @click="submit"
                    :disabled="form.processing"
                    class="
            flex items-center justify-center gap-2
            px-8 py-2 rounded-lg text-sm font-medium
            text-white
            bg-brand-500 hover:bg-brand-600
            disabled:opacity-60 disabled:cursor-not-allowed
        "
                >
                    <!-- Spinner -->
                    <svg
                        v-if="form.processing"
                        class="w-4 h-4 animate-spin text-white"
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

                    <span>
            {{ form.processing ? 'Saving…' : 'Save Mapping' }}
        </span>
                </button>

                <!-- Success Message -->
                <div
                    v-if="successMessage"
                    class="text-sm text-green-700 dark:text-green-300"
                >
                    {{ successMessage }}
                </div>

            </div>


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

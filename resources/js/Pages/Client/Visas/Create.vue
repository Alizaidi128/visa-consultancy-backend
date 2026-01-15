<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import PageHeader from '@/Components/PageHeader.vue'
import { Link } from '@inertiajs/vue3'
const props = defineProps({
    countries: Array,
})

const form = ref({
    country_id: '',
    visa_type_id: '',
})

const visaTypes = computed(() => {
    const country = props.countries.find(
        c => c.id === form.value.country_id
    )
    return country ? country.visa_types : []
})

function submit() {
    router.post('/client/visas', form.value)
}
</script>

<template>
    <!-- Heading -->
    <PageHeader
        title="New Visa Application"
        class="text-gray-900 dark:text-gray-100"
    />

    <!-- Card -->
    <div
        class="
            max-w-xl
            bg-white dark:bg-gray-800
            rounded-xl shadow-sm
            p-6
        "
    >
        <form @submit.prevent="submit" class="space-y-6">

            <!-- Country -->
            <div>
                <label
                    class="
                        block text-sm font-medium mb-1
                        text-gray-700 dark:text-gray-300
                    "
                >
                    Destination Country
                </label>

                <select
                    v-model="form.country_id"
                    required
                    class="
                        w-full rounded-lg border
                        border-gray-300 dark:border-gray-600
                        bg-white dark:bg-gray-900
                        text-gray-900 dark:text-gray-100
                        focus:ring-primary focus:border-primary
                    "
                >
                    <option value="">Select country</option>
                    <option
                        v-for="country in countries"
                        :key="country.id"
                        :value="country.id"
                    >
                        {{ country.name }}
                    </option>
                </select>
            </div>

            <!-- Visa Type -->
            <div>
                <label
                    class="
                        block text-sm font-medium mb-1
                        text-gray-700 dark:text-gray-300
                    "
                >
                    Visa Type
                </label>

                <select
                    v-model="form.visa_type_id"
                    :disabled="!form.country_id"
                    required
                    class="
                        w-full rounded-lg border
                        border-gray-300 dark:border-gray-600
                        bg-white dark:bg-gray-900
                        text-gray-900 dark:text-gray-100
                        focus:ring-primary focus:border-primary
                        disabled:opacity-60
                    "
                >
                    <option value="">Select visa type</option>
                    <option
                        v-for="visa in visaTypes"
                        :key="visa.id"
                        :value="visa.id"
                    >
                        {{ visa.name }}
                    </option>
                </select>
            </div>
            <!-- Actions -->
            <div class="flex justify-end gap-3 pt-4">

                <!-- Cancel -->
                <Link
                    href="/client/visas"
                    class="
                        inline-flex items-center justify-center
                        px-4 py-2 rounded-lg
                        text-sm font-medium
                        text-gray-600 dark:text-gray-400
                        border border-gray-300 dark:border-gray-600
                        hover:bg-gray-100 dark:hover:bg-gray-700
                        transition
                    "
                >
                    Cancel
                </Link>

                <!-- Submit -->
                <button
                    type="submit"
                    class="
                        inline-flex items-center justify-center
                        px-6 py-2 rounded-lg
                        text-sm font-medium
                        text-gray-700 dark:text-gray-300
                        bg-gray-100 dark:bg-gray-700
                        border border-gray-300 dark:border-gray-600
                        hover:bg-gray-200 dark:hover:bg-gray-600
                        transition shadow-sm
                    "
                >
                    Create Application
                </button>
            </div>

        </form>
    </div>
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

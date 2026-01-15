<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import PageHeader from '@/Components/PageHeader.vue'
import { Link } from '@inertiajs/vue3'
const props = defineProps({
    countries: Array,
    clients: Array, // ✅ ADMIN ONLY
})

const form = ref({
    client_id: '',
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
    router.post('/admin/visa-applications', form.value)
}
</script>

<template>
    <!-- Page Header -->
    <PageHeader
        title="Create Visa Application"
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

            <!-- Client -->
            <div>
                <label
                    class="
                        block text-sm font-medium mb-1
                        text-gray-700 dark:text-gray-300
                    "
                >
                    Client
                </label>

                <select
                    v-model="form.client_id"
                    required
                    class="
                        w-full rounded-lg border
                        border-gray-300 dark:border-gray-600
                        bg-white dark:bg-gray-900
                        text-gray-900 dark:text-gray-100
                        focus:ring-primary focus:border-primary
                    "
                >
                    <option value="">Select client</option>
                    <option
                        v-for="client in clients"
                        :key="client.id"
                        :value="client.id"
                    >
                        {{ client.first_name }} {{ client.last_name }}
                    </option>
                </select>
            </div>

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
                    href="/admin/visa-applications"
                    class="
                       px-4 py-2 rounded-lg text-sm font-medium
                    border border-gray-300 dark:border-gray-600
                    text-gray-700 dark:text-gray-300
                    hover:bg-gray-50 dark:hover:bg-gray-700
                    "
                >
                    Cancel
                </Link>

                <!-- Submit -->
                <button
                    type="submit"
                    class="
                      px-4 py-2 rounded-lg text-sm font-medium
                    border border-gray-300 dark:border-gray-600
                    text-gray-700 dark:text-gray-300
                    hover:bg-gray-50 dark:hover:bg-gray-700
                    "
                >
                    Create Visa
                </button>
            </div>

        </form>
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

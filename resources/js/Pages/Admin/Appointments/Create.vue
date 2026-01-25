<script setup>
import { ref, computed } from 'vue'
import { useForm } from '@inertiajs/vue3'
import PageHeader from '@/Components/PageHeader.vue'
import { onMounted, onUnmounted } from 'vue'

const dropdownRef = ref(null)

const props = defineProps({
    clients: Array,
})
const today = new Date().toISOString().split('T')[0]

const form = useForm({
    client_id: '',
    scheduled_date: '',
    from_time: '',
    to_time: '',
    notes: '',
})

const search = ref('')
const open = ref(false)

const filteredClients = computed(() => {
    if (!search.value) return props.clients

    return props.clients.filter(c =>
        `${c.first_name} ${c.last_name}`
            .toLowerCase()
            .includes(search.value.toLowerCase())
    )
})

const selectClient = (client) => {
    form.client_id = client.id
    search.value = `${client.first_name} ${client.last_name}`
    open.value = false
}

const submit = () => {
    form.post('/admin/appointments')
}

const handleClickOutside = (e) => {
    if (dropdownRef.value && !dropdownRef.value.contains(e.target)) {
        open.value = false
    }
}

onMounted(() => document.addEventListener('click', handleClickOutside))
onUnmounted(() => document.removeEventListener('click', handleClickOutside))

</script>



<template>
    <PageHeader title="Create Appointment" />

    <div
        class="
            max-w-2xl
            bg-white dark:bg-gray-800
            rounded-xl shadow-sm
            p-6
        "
    >
        <form @submit.prevent="submit" class="space-y-5">

            <!-- Client -->
            <div class="relative"  ref="dropdownRef">
                <label
                    class="block text-sm font-medium mb-1
               text-gray-700 dark:text-gray-300"
                >
                    Client
                </label>

                <!-- Search Input -->
                <input
                    v-model="search"
                    @focus="open = true"
                    @click.stop
                    type="text"
                    placeholder="Search client by name..."
                    class="
            w-full rounded-lg border
            border-gray-300 dark:border-gray-600
            bg-white dark:bg-gray-900
            px-3 py-2 text-sm
            text-gray-900 dark:text-gray-100
            focus:ring-2 focus:ring-primary
        "
                />

                <!-- Dropdown -->
                <div
                    v-if="open"
                    @click.stop
                    class="
            absolute z-20 mt-1 w-full
            max-h-60 overflow-y-auto
            rounded-lg border
            border-gray-200 dark:border-gray-700
            bg-white dark:bg-gray-800
            shadow-lg
        "
                >
                    <div
                        v-for="client in filteredClients"
                        :key="client.id"
                        @click="selectClient(client)"
                        class="
                px-4 py-2 cursor-pointer
                text-sm
                text-gray-700 dark:text-gray-300
                hover:bg-gray-100 dark:hover:bg-gray-700
                transition
            "
                    >
                        {{ client.first_name }} {{ client.last_name }}
                    </div>

                    <div
                        v-if="!filteredClients.length"
                        class="
                px-4 py-3 text-sm
                text-gray-500 dark:text-gray-400
            "
                    >
                        No matching clients found.
                    </div>
                </div>
            </div>


            <!-- Date -->
            <div>
                <label
                    class="block text-sm font-medium mb-1
                           text-gray-700 dark:text-gray-300"
                >
                    Appointment Date
                </label>
                <input
                    type="date"
                    v-model="form.scheduled_date"
                    :min="today"
                    class="
        w-full rounded-lg border
        border-gray-300 dark:border-gray-600
        bg-white dark:bg-gray-900
        px-3 py-2 text-sm
        text-gray-900 dark:text-gray-100
        focus:ring-2 focus:ring-primary
    "
                />

            </div>

            <!-- Time -->
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label
                        class="block text-sm font-medium mb-1
                               text-gray-700 dark:text-gray-300"
                    >
                        From
                    </label>
                    <input
                        type="time"
                        v-model="form.from_time"
                        class="
                            w-full rounded-lg border
                            border-gray-300 dark:border-gray-600
                            bg-white dark:bg-gray-900
                            px-3 py-2 text-sm
                            text-gray-900 dark:text-gray-100
                            focus:ring-2 focus:ring-primary
                        "
                    />
                </div>

                <div>
                    <label
                        class="block text-sm font-medium mb-1
                               text-gray-700 dark:text-gray-300"
                    >
                        To
                    </label>
                    <input
                        type="time"
                        v-model="form.to_time"
                        class="
                            w-full rounded-lg border
                            border-gray-300 dark:border-gray-600
                            bg-white dark:bg-gray-900
                            px-3 py-2 text-sm
                            text-gray-900 dark:text-gray-100
                            focus:ring-2 focus:ring-primary
                        "
                    />
                </div>
            </div>

            <!-- Notes -->
            <div>
                <label
                    class="block text-sm font-medium mb-1
                           text-gray-700 dark:text-gray-300"
                >
                    Notes (Optional)
                </label>
                <textarea
                    v-model="form.notes"
                    rows="3"
                    class="
                        w-full rounded-lg border
                        border-gray-300 dark:border-gray-600
                        bg-white dark:bg-gray-900
                        px-3 py-2 text-sm
                        text-gray-900 dark:text-gray-100
                        focus:ring-2 focus:ring-primary
                    "
                    placeholder="Internal notes about the appointment"
                />
            </div>

            <!-- Actions -->
            <div class="flex justify-end gap-3 pt-4">
                <button
                    type="submit"
                    class="
                        inline-flex items-center gap-2
                        px-5 py-2 rounded-lg text-sm font-medium
                        bg-primary text-white
                        hover:bg-primary-dark
                        transition
                    "
                >
                    Book Appointment
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

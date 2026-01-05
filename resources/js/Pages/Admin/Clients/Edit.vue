<script setup>
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'
import PageHeader from '@/Components/PageHeader.vue'
import Card from '@/Components/Card.vue'
import axios from 'axios'

const showSuccess = ref(false)

const emailStatus = ref(null)
const emailMessage = ref(null)
const checkingEmail = ref(false)

const props = defineProps({
    client: Object,
})

const form = useForm({
    first_name: props.client.first_name,
    last_name: props.client.last_name,
    gender: props.client.gender,
    email: props.client.email,
    phone: props.client.phone,
    passport_number: props.client.passport_number,
    passport_expiry: props.client.passport_expiry,
})

async function update() {
    emailStatus.value = null
    emailMessage.value = null

    // 🔍 EMAIL CHECK (same as create, but allow same client)
    if (form.email) {
        try {
            checkingEmail.value = true

            const res = await axios.post('/admin/clients/check-email', {
                email: form.email,
            })

            emailStatus.value = res.data.status

            // ❌ another client already owns this email
            if (
                res.data.status === 'client_exists' &&
                res.data.client?.id !== props.client.id
            ) {
                emailMessage.value =
                    'A client already exists with this email.'
                checkingEmail.value = false
                return
            }

            // ℹ️ user exists but not linked
            if (res.data.status === 'user_exists') {
                emailMessage.value =
                    'A user already exists. Client will link automatically.'
            }

        } catch {
            checkingEmail.value = false
            return
        }
    }

    checkingEmail.value = false

    // ✅ UPDATE CLIENT
    form.patch(`/admin/clients/${props.client.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            showSuccess.value = true

            setTimeout(() => {
                showSuccess.value = false
            }, 3000)
        },
    })
}




/* -------------------------------------------------------
   CLIENT DELETE MODAL
------------------------------------------------------- */
const showDeleteClientModal = ref(false)

function confirmClientDelete() {
    showDeleteClientModal.value = true
}

function cancelClientDelete() {
    showDeleteClientModal.value = false
}

function proceedClientDelete() {
    form.delete(`/admin/clients/${props.client.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            showDeleteClientModal.value = false
        },
    })
}
</script>


<template>
    <PageHeader title="Edit Client" />

    <div v-if="showSuccess"
         class="mb-6 rounded-lg bg-green-50 dark:bg-green-900/30
                border border-green-200 dark:border-green-700
                px-4 py-3 text-sm text-green-800 dark:text-green-200">
        ✔ Client updated successfully
    </div>

    <div class="max-w-4xl">
        <Card>
            <form
                @submit.prevent="update"
                class="grid grid-cols-1 gap-6"
            >
                <!-- SAME FIELDS AS CREATE -->
                <!-- (reuse exactly, no change needed) -->


                            <!-- First Name -->
                            <div>
                                <label class="block text-sm font-medium mb-1">First Name</label>
                                <input v-model="form.first_name" type="text" required
                                       class="
w-full rounded-lg
border border-gray-300 dark:border-gray-600
bg-white dark:bg-gray-800
text-gray-900 dark:text-gray-100
placeholder-gray-400 dark:placeholder-gray-500
focus:outline-none
focus:ring-2 focus:ring-primary/40
focus:border-primary
" />
                            </div>

                            <!-- Last Name -->
                            <div>
                                <label class="block text-sm font-medium mb-1">Last Name</label>
                                <input v-model="form.last_name" type="text" required
                                       class="
w-full rounded-lg
border border-gray-300 dark:border-gray-600
bg-white dark:bg-gray-800
text-gray-900 dark:text-gray-100
placeholder-gray-400 dark:placeholder-gray-500
focus:outline-none
focus:ring-2 focus:ring-primary/40
focus:border-primary
" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">
                                    Gender
                                </label>
                                <select
                                    v-model="form.gender"
                                    class="w-full rounded-lg border border-gray-300 dark:border-gray-600
               bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100"
                                >
                                    <option value="">Select</option>
                                    <option value="male">Male</option>
                                    <option value="female">Female</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>

                            <!-- Email -->
                            <div>
                                <label class="block text-sm font-medium mb-1">Email</label>
                                <input v-model="form.email" type="email"
                                       class="
w-full rounded-lg
border border-gray-300 dark:border-gray-600
bg-white dark:bg-gray-800
text-gray-900 dark:text-gray-100
placeholder-gray-400 dark:placeholder-gray-500
focus:outline-none
focus:ring-2 focus:ring-primary/40
focus:border-primary
" />

                                <!-- EMAIL ERROR / INFO -->
                                <p
                                    v-if="emailMessage"
                                    :class="[
                            'mt-1 text-sm',
                            emailStatus === 'client_exists'
                                ? 'text-red-600 dark:text-red-400'
                                : 'text-yellow-600 dark:text-yellow-400'
                        ]"
                                >
                                    {{ emailMessage }}
                                </p>
                            </div>

                            <!-- Phone -->
                            <div>
                                <label class="block text-sm font-medium mb-1">Phone</label>
                                <input v-model="form.phone" type="text"
                                       class="
w-full rounded-lg
border border-gray-300 dark:border-gray-600
bg-white dark:bg-gray-800
text-gray-900 dark:text-gray-100
placeholder-gray-400 dark:placeholder-gray-500
focus:outline-none
focus:ring-2 focus:ring-primary/40
focus:border-primary
" />
                            </div>

                            <!-- Passport Number -->
                            <div>
                                <label class="block text-sm font-medium mb-1">Passport Number</label>
                                <input v-model="form.passport_number" type="text"
                                       class="
w-full rounded-lg
border border-gray-300 dark:border-gray-600
bg-white dark:bg-gray-800
text-gray-900 dark:text-gray-100
placeholder-gray-400 dark:placeholder-gray-500
focus:outline-none
focus:ring-2 focus:ring-primary/40
focus:border-primary
" />
                            </div>

                            <!-- Passport Expiry -->
                            <div>
                                <label class="block text-sm font-medium mb-1">Passport Expiry</label>
                                <input v-model="form.passport_expiry" type="date"
                                       class="
w-full rounded-lg
border border-gray-300 dark:border-gray-600
bg-white dark:bg-gray-800
text-gray-900 dark:text-gray-100
focus:outline-none
focus:ring-2 focus:ring-primary/40
focus:border-primary
" />

                                <p v-if="form.errors.passport_expiry"
                                   class="mt-1 text-sm text-red-600 dark:text-red-400">
                                    {{ form.errors.passport_expiry }}
                                </p>
                            </div>




                <div class="md:col-span-2 flex justify-between">
                    <!-- DELETE -->
                    <button
                        type="button"
                        @click="confirmClientDelete"
                        class="px-4 py-2 rounded-lg
           border border-red-300
           text-red-600 hover:bg-red-50
           dark:border-red-600 dark:text-red-400
           dark:hover:bg-red-900/30"
                    >
                        Delete Client
                    </button>


                    <!-- SAVE -->
                    <div class="flex gap-3">
                        <button
                            type="button"
                            class="px-4 py-2 rounded-lg border text-sm"
                            @click="$inertia.visit('/admin/clients')"
                        >
                            Cancel
                        </button>

                        <button
                            type="submit"
                            :disabled="form.processing || checkingEmail"
                            class="px-4 py-2 rounded-lg border text-sm"
                        >
                            {{ form.processing || checkingEmail ? 'Saving…' : 'Update Client' }}
                        </button>


                    </div>
                </div>
            </form>
        </Card>
    </div>
    <!-- DELETE CLIENT CONFIRM MODAL -->
    <div
        v-if="showDeleteClientModal"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/40"
    >
        <div class="w-full max-w-sm rounded-xl bg-white dark:bg-gray-800 p-6 shadow-xl">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                Delete Client
            </h3>

            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                Are you sure you want to delete
                <span class="font-medium">
                {{ client.first_name }} {{ client.last_name }}
            </span>?
                <br />
                This action cannot be undone.
            </p>

            <div class="mt-6 flex justify-end gap-3">
                <button
                    @click="cancelClientDelete"
                    class="px-4 py-2 rounded-lg text-sm
                       border border-gray-300 dark:border-gray-600
                       text-gray-700 dark:text-gray-300
                       hover:bg-gray-100 dark:hover:bg-gray-700"
                >
                    Cancel
                </button>

                <button
                    @click="proceedClientDelete"
                    class="px-4 py-2 rounded-lg text-sm font-medium
                       bg-red-600 text-white hover:bg-red-700"
                >
                    Delete
                </button>
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

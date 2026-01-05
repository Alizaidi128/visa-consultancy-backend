<script setup>
import { ref } from 'vue'
import axios from 'axios'
import { useForm } from '@inertiajs/vue3'
import PageHeader from '@/Components/PageHeader.vue'
import Card from '@/Components/Card.vue'

/* -------------------------------------------------------
   LOCAL SUCCESS (UNCHANGED)
------------------------------------------------------- */
const showSuccess = ref(false)

/* -------------------------------------------------------
   FORM STATE
------------------------------------------------------- */
const form = useForm({
    first_name: '',
    last_name: '',
    gender: '',
    email: '',
    phone: '',
    passport_number: '',
    passport_expiry: '',
})

/* -------------------------------------------------------
   EMAIL VALIDATION STATE (SUBMIT-BASED)
------------------------------------------------------- */
const emailStatus = ref(null)
const emailMessage = ref(null)
const checkingEmail = ref(false)

/* -------------------------------------------------------
   SUBMIT (EMAIL CHECK MOVED HERE)
------------------------------------------------------- */
async function submit() {
    emailStatus.value = null
    emailMessage.value = null

    // 🔍 EMAIL CHECK ONLY ON SUBMIT
    if (form.email) {
        try {
            checkingEmail.value = true

            const res = await axios.post('/admin/clients/check-email', {
                email: form.email,
            })

            emailStatus.value = res.data.status

            if (res.data.status === 'client_exists') {
                emailMessage.value =
                    'A client already exists with this email.'
                checkingEmail.value = false
                return // ⛔ STOP SUBMIT
            }

            if (res.data.status === 'user_exists') {
                emailMessage.value =
                    'A user already exists. Client will link automatically after approval.'
            }

        } catch {
            checkingEmail.value = false
            return
        }
    }

    checkingEmail.value = false

    // ✅ CREATE CLIENT
    form.post('/admin/clients', {
        preserveScroll: true,
        onSuccess: () => {
            showSuccess.value = true
            form.reset()
            emailStatus.value = null
            emailMessage.value = null

            setTimeout(() => {
                showSuccess.value = false
            }, 3000)
        },
    })
}
</script>

<template>
    <PageHeader title="Create Client" />

    <!-- SUCCESS MESSAGE -->
    <div
        v-if="showSuccess"
        class="
            mb-6 rounded-lg
            bg-green-50 dark:bg-green-900/30
            border border-green-200 dark:border-green-700
            px-4 py-3 text-sm
            text-green-800 dark:text-green-200
        "
    >
        ✔ Client created successfully
    </div>

    <div class="max-w-4xl">
        <Card>
            <form
                @submit.prevent="submit"
                class="grid grid-cols-1 md:grid-cols-2 gap-6"
            >

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

                <!-- Actions -->
                <div class="md:col-span-2 flex justify-end gap-3">
                    <button
                        type="button"
                        class="px-4 py-2 rounded-lg border text-sm"
                        @click="$inertia.visit('/admin/clients')"
                    >
                        Cancel
                    </button>

                    <button
                        type="submit"
                        :disabled="checkingEmail || form.processing"
                        class="px-4 py-2 rounded-lg border text-sm"
                    >
                        {{ form.processing || checkingEmail ? 'Creating…' : 'Create Client' }}
                    </button>
                </div>

            </form>
        </Card>
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

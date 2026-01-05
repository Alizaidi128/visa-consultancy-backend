<script setup>
import PageHeader from '@/Components/PageHeader.vue'
import StatusBadge from '@/Components/StatusBadge.vue'
import Card from '@/Components/Card.vue'
import { useForm } from '@inertiajs/vue3'
import { ref } from 'vue'

const props = defineProps({
    client: Object,
})
function formatDate(date) {
    if (!date) return null

    const d = new Date(date)
    return d.toLocaleDateString(undefined, {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    })
}

/* -------------------------------------------------------
   FAMILY MEMBER FORM
------------------------------------------------------- */
const showFamilyForm = ref(false)

const familyForm = useForm({
    relationship: '',
    gender: '',
    first_name: '',
    last_name: '',
    date_of_birth: '',
    passport_number: '',
    passport_expiry: '',
})

function submitFamilyMember() {
    familyForm.post(
        `/admin/clients/${props.client.id}/family-members`,
        {
            preserveScroll: true,
            onSuccess: () => {
                familyForm.reset()
                showFamilyForm.value = false
            },
        }
    )
}

const editingMemberId = ref(null)

function startEdit(member) {
    editingMemberId.value = member.id

    familyForm.relationship = member.relationship
    familyForm.gender = member.gender
    familyForm.first_name = member.first_name
    familyForm.last_name = member.last_name
    familyForm.date_of_birth = member.date_of_birth?.substring(0, 10)
    familyForm.passport_number = member.passport_number
    familyForm.passport_expiry = member.passport_expiry?.substring(0, 10)

    showFamilyForm.value = true
}


function updateFamilyMember() {
    familyForm.patch(
        `/admin/family-members/${editingMemberId.value}`,
        {
            preserveScroll: true,
            onSuccess: () => {
                familyForm.reset()
                editingMemberId.value = null
                showFamilyForm.value = false
            },
        }
    )
}


const showDeleteModal = ref(false)
const deletingMember = ref(null)

function confirmDelete(member) {
    deletingMember.value = member
    showDeleteModal.value = true
}

function cancelDelete() {
    showDeleteModal.value = false
    deletingMember.value = null
}

function proceedDelete() {
    familyForm.delete(
        `/admin/family-members/${deletingMember.value.id}`,
        {
            preserveScroll: true,
            onSuccess: cancelDelete,
        }
    )
}


</script>

<template>
    <PageHeader
        :title="`${client.first_name} ${client.last_name}`"
        subtitle="Client details"
    />

    <!-- ================= CLIENT INFO ================= -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 mb-6">
        <h2 class="text-sm font-semibold uppercase mb-4 text-gray-600 dark:text-gray-300">
            Client Information
        </h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
            <div>
                <p class="text-gray-500 dark:text-gray-400">Email</p>
                <p class="font-medium text-gray-900 dark:text-gray-100">
                    {{ client.email ?? '—' }}
                </p>
            </div>
            <div>
                <p class="text-gray-500 dark:text-gray-400">Gender</p>
                <p class="font-medium text-gray-900 dark:text-gray-100 capitalize">
                    {{ client.gender ?? '—' }}
                </p>
            </div>

            <div>
                <p class="text-gray-500 dark:text-gray-400">Phone</p>
                <p class="font-medium text-gray-900 dark:text-gray-100">
                    {{ client.phone ?? '—' }}
                </p>
            </div>

            <div>
                <p class="text-gray-500 dark:text-gray-400">Passport Number</p>
                <p class="font-medium text-gray-900 dark:text-gray-100">
                    {{ client.passport_number ?? '—' }}
                </p>
            </div>

            <div>
                <p class="text-gray-500 dark:text-gray-400">Passport Expiry</p>
                <p class="font-medium text-gray-900 dark:text-gray-100">
                    {{ client.passport_expiry ?? '—' }}
                </p>
            </div>
        </div>
    </div>

    <!-- ================= FAMILY MEMBERS ================= -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 mb-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-sm font-semibold uppercase text-gray-600 dark:text-gray-300">
                Family Members
            </h2>

            <button
                @click="showFamilyForm = !showFamilyForm"
                class="
                    px-4 py-2 rounded-lg text-sm font-medium
                    border border-gray-300 dark:border-gray-600
                    text-gray-700 dark:text-gray-300
                    hover:bg-gray-50 dark:hover:bg-gray-700
                "
            >
                + Add Family Member
            </button>
        </div>
        <p
            v-if="editingMemberId"
            class="mb-2 text-sm font-medium text-blue-600 dark:text-blue-400"
        >
            Editing family member
        </p>

        <!-- ADD FAMILY MEMBER FORM -->
        <form
            v-if="showFamilyForm"
            @submit.prevent="editingMemberId ? updateFamilyMember() : submitFamilyMember()"
            class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6"
        >


        <div>
                <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">Relationship</label>
                <select
                    v-model="familyForm.relationship"
                    required
                    class="w-full rounded-lg border border-gray-300 dark:border-gray-600
                           bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100"
                >
                    <option value="">Select</option>
                    <option value="spouse">Spouse</option>
                    <option value="child">Child</option>
                    <option value="parent">Parent</option>
                    <option value="sibling">Sibling</option>
                </select>
                <p
                    v-if="familyForm.errors.relationship"
                    class="mt-1 text-xs text-red-600 dark:text-red-400"
                >
                    {{ familyForm.errors.relationship }}
                </p>

            </div>
            <div>
                <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">
                    Gender
                </label>
                <select
                    v-model="familyForm.gender"
                    class="w-full rounded-lg border border-gray-300 dark:border-gray-600
               bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100"
                >
                    <option value="">Select</option>
                    <option value="male">Male</option>
                    <option value="female">Female</option>
                    <option value="other">Other</option>
                </select>
                <p
                    v-if="familyForm.errors.gender"
                    class="mt-1 text-xs text-red-600 dark:text-red-400"
                >
                    {{ familyForm.errors.gender }}
                </p>

            </div>


            <div>
                <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">First Name</label>
                <input v-model="familyForm.first_name" required
                       class="w-full rounded-lg border border-gray-300 dark:border-gray-600
                              bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100"/>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">Last Name</label>
                <input v-model="familyForm.last_name" required
                       class="w-full rounded-lg border border-gray-300 dark:border-gray-600
                              bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100"/>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">Date of Birth</label>
                <input type="date" v-model="familyForm.date_of_birth"
                       class="w-full rounded-lg border border-gray-300 dark:border-gray-600
                              bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100"/>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">Passport Number</label>
                <input v-model="familyForm.passport_number"
                       class="w-full rounded-lg border border-gray-300 dark:border-gray-600
                              bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100"/>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">Passport Expiry</label>
                <input type="date" v-model="familyForm.passport_expiry"
                       class="w-full rounded-lg border border-gray-300 dark:border-gray-600
                              bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100"/>
                <p
                    v-if="familyForm.errors.passport_expiry"
                    class="mt-1 text-xs text-red-600 dark:text-red-400"
                >
                    {{ familyForm.errors.passport_expiry }}
                </p>

            </div>

            <div class="md:col-span-2 flex justify-end gap-3">
                <button
                    type="button"
                    @click="familyForm.reset(); editingMemberId = null; showFamilyForm = false"
                                class=" px-4 py-2 rounded-lg text-sm font-medium
                    border border-gray-300 dark:border-gray-600
                    text-gray-700 dark:text-gray-300
                    hover:bg-gray-50 dark:hover:bg-gray-700">
                    Cancel
                </button>

                <button type="submit"
                        class=" px-4 py-2 rounded-lg text-sm font-medium
                    border border-gray-300 dark:border-gray-600
                    text-gray-700 dark:text-gray-300
                    hover:bg-gray-50 dark:hover:bg-gray-700">
                    {{ editingMemberId ? 'Update' : 'Save' }}
                </button>
            </div>
        </form>

        <!-- FAMILY LIST -->
        <div
            v-if="client.family_members?.length"
            class="grid grid-cols-1 md:grid-cols-2 gap-4"
        >
            <div
                v-for="member in client.family_members"
                :key="member.id"
                class="
            rounded-xl
            border border-gray-200 dark:border-gray-700
            bg-white dark:bg-gray-900
            p-4
            space-y-3
            transition
        "
            >
                <!-- HEADER -->
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                            {{ member.first_name }} {{ member.last_name }}
                        </p>

                        <p class="text-xs text-gray-500 dark:text-gray-400 capitalize">
                            {{ member.relationship }}
                            <span v-if="member.gender"> • {{ member.gender }}</span>
                        </p>
                    </div>

                    <!-- ACTION BUTTONS -->
                    <div class="flex gap-2">
                        <!-- EDIT -->
                        <button
                            @click="startEdit(member)"
                            class="
                        px-2 py-1 text-xs rounded-md
                        border border-gray-300 dark:border-gray-600
                        text-gray-700 dark:text-gray-300
                        hover:bg-gray-100 dark:hover:bg-gray-700
                        transition
                    "
                        >
                            Edit
                        </button>

                        <!-- DELETE -->
                        <button
                            @click="confirmDelete(member)"
                            class="
                        px-2 py-1 text-xs rounded-md
                        border border-red-300 dark:border-red-600
                        text-red-600 dark:text-red-400
                        hover:bg-red-50 dark:hover:bg-red-900/30
                        transition
                    "
                        >
                            Delete
                        </button>
                    </div>
                </div>

                <!-- DETAILS -->
                <div class="text-xs text-gray-600 dark:text-gray-400 space-y-1">
                    <p v-if="member.date_of_birth">
                        DOB:
                        <span class="font-medium">
                    {{ formatDate(member.date_of_birth) }}
                </span>
                    </p>

                    <p>
                        Passport:
                        <span class="font-medium">
                    {{ member.passport_number ?? 'Not provided' }}
                </span>
                    </p>

                    <p v-if="member.passport_expiry">
                        Expiry:
                        <span class="font-medium">
                    {{ formatDate(member.passport_expiry) }}
                </span>
                    </p>
                </div>
            </div>
        </div>


        <p v-else class="text-sm text-gray-500 dark:text-gray-400">
            No family members added.
        </p>

    </div>

    <!-- ================= VISA APPLICATIONS ================= -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-sm font-semibold uppercase text-gray-600 dark:text-gray-300">
                Visa Applications
            </h2>
        </div>

        <table
            v-if="client.visa_applications?.length"
            class="min-w-full divide-y divide-gray-200 dark:divide-gray-700"
        >
            <thead class="bg-gray-50 dark:bg-gray-700">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-600 dark:text-gray-300">
                    Visa Type
                </th>
                <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-600 dark:text-gray-300">
                    Status
                </th>
                <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-600 dark:text-gray-300">
                    Created
                </th>
            </tr>
            </thead>

            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
            <tr
                v-for="visa in client.visa_applications"
                :key="visa.id"
                class="hover:bg-gray-50 dark:hover:bg-gray-700 transition"
            >
                <td class="px-6 py-4 font-medium text-gray-900 dark:text-gray-100">
                    {{ visa.visa_type }}
                </td>

                <td class="px-6 py-4">
                    <StatusBadge :status="visa.status" />
                </td>

                <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">
                    {{ visa.created_at }}
                </td>
            </tr>
            </tbody>
        </table>

        <div v-else class="p-6 text-center text-sm text-gray-500 dark:text-gray-400">
            No visa applications yet.
        </div>
    </div>
    <!-- DELETE CONFIRM MODAL -->
    <div
        v-if="showDeleteModal"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/40"
    >
        <div class="w-full max-w-sm rounded-xl bg-white dark:bg-gray-800 p-6 shadow-xl">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                Delete Family Member
            </h3>

            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                Are you sure you want to remove
                <span class="font-medium">
                {{ deletingMember.first_name }} {{ deletingMember.last_name }}
            </span>?
                This action cannot be undone.
            </p>

            <div class="mt-6 flex justify-end gap-3">
                <button
                    @click="cancelDelete"
                    class="px-4 py-2 rounded-lg text-sm
                       border border-gray-300 dark:border-gray-600
                       text-gray-700 dark:text-gray-300
                       hover:bg-gray-100 dark:hover:bg-gray-700">
                    Cancel
                </button>

                <button
                    @click="proceedDelete"
                    class="px-4 py-2 rounded-lg text-sm font-medium
                       bg-red-600 text-white hover:bg-red-700">
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

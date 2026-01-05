<script setup>
import {ref, computed} from 'vue'
import {usePage, router} from '@inertiajs/vue3'

import PageHeader from '@/Components/PageHeader.vue'
import Card from '@/Components/Card.vue'

const page = usePage()
const user = computed(() => page.props.auth.user)

/* -------------------------------------------------------
   STATE
------------------------------------------------------- */
const isEditing = ref(false)

const form = ref({
    name: user.value?.name ?? '',
    email: user.value?.email ?? '',
    phone: user.value?.phone ?? '',
    address: user.value?.address ?? '',
    city: user.value?.city ?? '',
})

/* -------------------------------------------------------
   ACTIONS
------------------------------------------------------- */
const startEdit = () => {
    isEditing.value = true
}

const cancelEdit = () => {
    isEditing.value = false
    form.value = {
        name: user.value?.name ?? '',
        email: user.value?.email ?? '',
        phone: user.value?.phone ?? '',
        address: user.value?.address ?? '',
        city: user.value?.city ?? '',
    }
}

const updateProfile = () => {
    router.patch('/profile', {
        phone: form.value.phone,
        city: form.value.city,
        address: form.value.address,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            isEditing.value = false
        },
    })

}
</script>

<template>
    <PageHeader title="Personal Information"/>

    <div class="max-w-4xl">
        <Card>
            <!-- HEADER -->
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                    Personal Information
                </h2>

                <button
                    v-if="!isEditing"
                    @click="startEdit"
                    class="
                        inline-flex items-center gap-2
                        px-4 py-2 rounded-lg
                        text-sm font-medium
                        border border-gray-300 dark:border-gray-600
                        text-gray-700 dark:text-gray-300
                        hover:bg-gray-50 dark:hover:bg-gray-700
                        transition
                    "
                >
                    ✎ Edit
                </button>
            </div>

            <!-- READ MODE -->
            <div v-if="!isEditing" class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm">
                <div>
                    <p class="text-gray-500 dark:text-gray-400">Name</p>
                    <p class="font-medium text-gray-900 dark:text-gray-100">
                        {{ user.name }}
                    </p>
                </div>

                <div>
                    <p class="text-gray-500 dark:text-gray-400">Email</p>
                    <p class="font-medium text-gray-900 dark:text-gray-100">
                        {{ user.email }}
                    </p>
                </div>

                <div>
                    <p class="text-gray-500 dark:text-gray-400">Phone</p>
                    <p class="font-medium text-gray-900 dark:text-gray-100">
                        {{ user.phone ?? '—' }}
                    </p>
                </div>

                <div>
                    <p class="text-gray-500 dark:text-gray-400">City</p>
                    <p class="font-medium text-gray-900 dark:text-gray-100">
                        {{ user.city ?? '—' }}
                    </p>
                </div>

                <div class="md:col-span-2">
                    <p class="text-gray-500 dark:text-gray-400">Address</p>
                    <p class="font-medium text-gray-900 dark:text-gray-100">
                        {{ user.address ?? '—' }}
                    </p>
                </div>
            </div>

            <!-- EDIT MODE -->
            <form
                v-else
                @submit.prevent="updateProfile"
                class="grid grid-cols-1 md:grid-cols-2 gap-6"
            >
                <div>
                    <label class="block text-sm font-medium mb-1">Name</label>
                    <input v-model="form.name" type="text" disabled
                           class="
  w-full rounded-lg
  border border-gray-300 dark:border-gray-600
  bg-white dark:bg-gray-800
  text-gray-900 dark:text-gray-100
  placeholder-gray-400 dark:placeholder-gray-500
  focus:outline-none
  focus:ring-2 focus:ring-primary/40
  focus:border-primary
"

                    />
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Email</label>
                    <input v-model="form.email" type="email" disabled
                           class="
  w-full rounded-lg
  border border-gray-300 dark:border-gray-600
  bg-white dark:bg-gray-800
  text-gray-900 dark:text-gray-100
  placeholder-gray-400 dark:placeholder-gray-500
  focus:outline-none
  focus:ring-2 focus:ring-primary/40
  focus:border-primary
"

                    />
                </div>

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
"
                    />
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">City</label>
                    <input v-model="form.city" type="text"
                           class="
  w-full rounded-lg
  border border-gray-300 dark:border-gray-600
  bg-white dark:bg-gray-800
  text-gray-900 dark:text-gray-100
  placeholder-gray-400 dark:placeholder-gray-500
  focus:outline-none
  focus:ring-2 focus:ring-primary/40
  focus:border-primary
"
                    />
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium mb-1">Address</label>
                    <textarea v-model="form.address" rows="3"
                              class="
  w-full rounded-lg
  border border-gray-300 dark:border-gray-600
  bg-white dark:bg-gray-800
  text-gray-900 dark:text-gray-100
  placeholder-gray-400 dark:placeholder-gray-500
  focus:outline-none
  focus:ring-2 focus:ring-primary/40
  focus:border-primary
"
                    />
                </div>

                <div class="md:col-span-2 flex justify-end gap-3">
                    <button type="button" @click="cancelEdit"
                            class="  px-4 py-2 rounded-lg border text-sm font-medium transition
border-gray-300 text-gray-700 bg-white hover:bg-gray-50

dark:border-gray-600 dark:text-gray-300 dark:bg-gray-800
dark:hover:bg-gray-700">
                        Cancel
                    </button>

                    <button type="submit"
                            class="  px-4 py-2 rounded-lg border text-sm font-medium transition
border-gray-300 text-gray-700 bg-white hover:bg-gray-50

dark:border-gray-600 dark:text-gray-300 dark:bg-gray-800
dark:hover:bg-gray-700">
                        Save Changes
                    </button>
                </div>
            </form>

        </Card>
    </div>
</template>

<script>
import AppLayout from '@/Layouts/AppLayout.vue'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import ConsultantLayout from '@/Layouts/ConsultantLayout.vue'
import ClientLayout from '@/Layouts/ClientLayout.vue'

export default {
    layout: (h, page) =>
        h(AppLayout, () =>
            h(
                page.props.auth.roles.includes('admin')
                    ? AdminLayout
                    : page.props.auth.roles.includes('consultant')
                        ? ConsultantLayout
                        : ClientLayout,
                () => page
            )
        ),
}
</script>

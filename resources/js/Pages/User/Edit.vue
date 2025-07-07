<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
// import DeleteUserForm from './Partials/DeleteUserForm.vue';
// import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
// import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head } from '@inertiajs/vue3';
// import Dashboard from '../Dashboard.vue';

import { useForm, usePage } from '@inertiajs/vue3'

const { user } = usePage().props

const form = useForm({
  name: user.name,
  email: user.email,
  ativo: user.ativo,
})

function submit() {
  form.put(`/users/${user.id}/update`)
}
</script>

<template>
    <Head title="Edit User" />

    <AuthenticatedLayout>
        <template #header>
            <h2
                class="text-xl font-semibold leading-tight text-gray-800"
            >
                Editar Usuário: {{ user.name }}
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                <div class="bg-white p-4 shadow sm:rounded-lg sm:p-8">

                    <form @submit.prevent="submit" class="space-y-4">
                        <div class="">
                            <label for="name">Nome</label>
                            <input value="{{ user.name }}" v-model="form.name" id="name" type="text" class="input" />
                        </div>

                        <div>
                            <label for="email">Email</label>
                            <input value="{{ user.email }}" v-model="form.email" id="email" type="email" class="input" />
                        </div>

                        <div>
                            <label>
                            <input :checked="user.active === 1" type="checkbox" v-model="form.ativo" />
                            Ativo
                            </label>
                        </div>

                        <button type="submit" class="btn btn-primary" :disabled="form.processing">
                            Salvar
                        </button>
                        </form>

                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>

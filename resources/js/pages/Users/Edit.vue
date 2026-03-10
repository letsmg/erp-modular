<script setup>
import { Head, useForm, Link, router } from '@inertiajs/vue3';

const props = defineProps({
    user: Object,
    auth: Object // Usuário logado
});

const form = useForm({
    name: props.user.name,
    email: props.user.email,
    password: '', 
    password_confirmation: '', // Campo para confirmação
    access_level: props.user.access_level,
    is_active: props.user.is_active,
});

const submit = () => {
    form.put(route('users.update', props.user.id), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <Head :title="'Editando - ' + user.name" />

    <div class="py-12 px-4 sm:px-6 lg:px-8 bg-gray-50 min-h-screen">
        <div class="max-w-3xl mx-auto">
            
            <div class="mb-6">
                <Link :href="route('users.index')" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">
                    &larr; Voltar para a lista
                </Link>
            </div>

            <div class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl">
                <div class="px-4 py-6 sm:p-8">
                    <header class="mb-8">
                        <h2 class="text-xl font-semibold leading-7 text-gray-900">Configurações de Perfil</h2>
                        <p class="mt-1 text-sm leading-6 text-gray-600">
                            {{ user.id === auth.user.id ? 'Atualize suas informações pessoais e senha.' : 'Gerencie as informações de acesso deste colaborador.' }}
                        </p>
                    </header>

                    <form @submit.prevent="submit" class="space-y-6">
                        <div>
                            <label for="name" class="block text-sm font-medium leading-6 text-gray-900">Nome Completo</label>
                            <div class="mt-2">
                                <input v-model="form.name" type="text" id="name" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" />
                                <div v-if="form.errors.name" class="text-red-500 text-xs mt-1">{{ form.errors.name }}</div>
                            </div>
                        </div>

                        <div>
                            <label for="email" class="block text-sm font-medium leading-6 text-gray-900">Endereço de E-mail</label>
                            <div class="mt-2">
                                <input v-model="form.email" type="email" id="email" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" />
                                <div v-if="form.errors.email" class="text-red-500 text-xs mt-1">{{ form.errors.email }}</div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                            <div>
                                <label for="password" class="block text-sm font-medium leading-6 text-gray-900">Nova Senha</label>
                                <div class="mt-2">
                                    <input v-model="form.password" type="password" id="password" placeholder="Mínimo 8 caracteres" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" />
                                    <div v-if="form.errors.password" class="text-red-500 text-xs mt-1">{{ form.errors.password }}</div>
                                </div>
                            </div>

                            <div>
                                <label for="password_confirmation" class="block text-sm font-medium leading-6 text-gray-900">Confirmar Senha</label>
                                <div class="mt-2">
                                    <input v-model="form.password_confirmation" type="password" id="password_confirmation" placeholder="Repita a senha" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" />
                                </div>
                            </div>
                        </div>

                        <div v-if="auth.user.access_level === 1 && user.id !== auth.user.id" class="pt-6 border-t border-gray-100">
                            <h3 class="text-sm font-bold text-amber-600 uppercase tracking-wider mb-4">Privilégios de Administrador</h3>
                            
                            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                                <div>
                                    <label class="block text-sm font-medium text-gray-900">Nível de Permissão</label>
                                    <select v-model="form.access_level" class="mt-2 block w-full rounded-md border-0 py-1.5 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm">
                                        <option :value="0">Operador (Padrão)</option>
                                        <option :value="1">Administrador (Total)</option>
                                    </select>
                                </div>

                                <div class="flex items-center mt-8">
                                    <div class="flex h-6 items-center">
                                        <input v-model="form.is_active" id="is_active" type="checkbox" class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-600" />
                                    </div>
                                    <div class="ml-3 text-sm leading-6">
                                        <label for="is_active" class="font-medium text-gray-900">Conta Ativa</label>
                                        <p class="text-gray-500 text-xs">Se desativado, o acesso será bloqueado.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-8 flex items-center justify-end gap-x-6 border-t border-gray-900/10 pt-6">
                            <button type="button" @click="router.get(route('users.index'))" class="text-sm font-semibold leading-6 text-gray-900">Cancelar</button>
                            <button type="submit" :disabled="form.processing" class="rounded-md bg-indigo-600 px-6 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 disabled:opacity-50">
                                {{ form.processing ? 'Salvando...' : 'Salvar Alterações' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>
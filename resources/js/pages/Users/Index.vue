<script setup>
import { Head, useForm, router } from '@inertiajs/vue3';

const props = defineProps({ 
    users: Array,
    auth: Object 
});

const form = useForm({});

const handleToggleStatus = (user) => {
    const acao = user.is_active ? 'desativar' : 'ativar';
    if (confirm(`Deseja realmente ${acao} o usuário ${user.name}?`)) {
        router.patch(route('users.toggle', user.id));
    }
};

const handleResetPassword = (user) => {
    if (confirm(`Resetar senha de ${user.name} para "Mudar@123"?`)) {
        router.patch(route('users.reset', user.id));
    }
};

const handleDelete = (user) => {
    if (confirm(`EXCLUIR PERMANENTEMENTE o usuário ${user.name}?`)) {
        router.delete(route('users.destroy', user.id));
    }
};
</script>

<template>
    <Head title="Gestão de Usuários" />
    <div class="py-12 px-4 sm:px-6 lg:px-8 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto">
            <div class="bg-white shadow sm:rounded-xl overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase">Usuário</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase">Nível</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase">Gerenciamento</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <tr v-for="user in users" :key="user.id" class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900">{{ user.name }}</div>
                                <div class="text-sm text-gray-500">{{ user.email }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <span :class="user.access_level === 1 ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700'" class="px-2 py-1 rounded-full text-xs font-bold">
                                    {{ user.access_level === 1 ? 'Admin' : 'Padrão' }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span :class="user.is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'" class="px-2 py-1 rounded-full text-xs font-bold">
                                    {{ user.is_active ? 'Ativo' : 'Inativo' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right text-sm font-medium space-x-3">
                                <button @click="router.get(route('users.edit', user.id))" class="text-indigo-600 hover:text-indigo-900 font-bold">
                                    Editar
                                </button>

                                <template v-if="user.id !== auth.user.id">
                                    <button @click="handleToggleStatus(user)" 
                                            :class="user.is_active ? 'text-red-600' : 'text-green-600'" class="font-bold">
                                        {{ user.is_active ? 'Desativar' : 'Ativar' }}
                                    </button>

                                    <button @click="handleResetPassword(user)" class="text-amber-600 font-bold">
                                        Resetar Senha
                                    </button>

                                    <button @click="handleDelete(user)" class="text-gray-400 hover:text-red-600 font-bold">
                                        Excluir
                                    </button>
                                </template>
                                
                                <span v-else class="text-gray-400 italic text-xs ml-2">(Seu Perfil)</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>
<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { Search, Plus, Building2 } from 'lucide-vue-next';

const props = defineProps({ suppliers: Array, filters: Object });

// Estado do campo de busca
const searchTerm = ref(props.filters.search);

// "Vigia" o campo de busca. Quando o usuário digita, envia para o Laravel
watch(searchTerm, (value) => {
    router.get('/suppliers', { search: value }, { 
        preserveState: true, // Não reseta o scroll da página
        replace: true        // Não cria um novo histórico no navegador para cada letra
    });
});
</script>

<template>
    <AuthenticatedLayout>
        <div class="mb-6 flex justify-between items-center">
            <h2 class="text-2xl font-bold">Fornecedores</h2>
            
            <div class="relative w-72">
                <Search class="absolute left-3 top-2.5 h-4 w-4 text-gray-400" />
                <input v-model="searchTerm" type="text" placeholder="Buscar por nome ou CNPJ..." 
                    class="pl-10 w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500">
            </div>
        </div>

        <div class="bg-white shadow rounded-lg">
            <table class="w-full text-left">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="p-4">Empresa</th>
                        <th class="p-4">CNPJ</th>
                        <th class="p-4">Cidade</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="supplier in suppliers" :key="supplier.id" class="border-b">
                        <td class="p-4 font-medium">{{ supplier.company_name }}</td>
                        <td class="p-4">{{ supplier.cnpj }}</td>
                        <td class="p-4">{{ supplier.city }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AuthenticatedLayout>
</template>
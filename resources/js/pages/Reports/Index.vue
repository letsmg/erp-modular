<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import { FileText, Printer, FileSearch, Truck } from 'lucide-vue-next';

// Recebe os fornecedores do ReportController
const props = defineProps({
    suppliers: Array
});

// Estado do formulário local
const form = ref({
    type: 'sintetico',
    supplier_id: ''
});

// Função que monta a URL e abre o PDF
const generateReport = () => {
    const params = new URLSearchParams({
        type: form.value.type,
        supplier_id: form.value.supplier_id
    }).toString();
    
    window.open(route('reports.products') + '?' + params, '_blank');
};
</script>

<template>
    <Head title="Relatórios" />

    <AuthenticatedLayout>
        <div class="max-w-5xl mx-auto py-8 px-4">
            <div class="mb-10">
                <h2 class="text-3xl font-black text-slate-900 tracking-tight">Centro de Relatórios</h2>
                <p class="text-slate-500 font-medium mt-1">Gere documentos PDF detalhados do seu inventário de produtos.</p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white p-8 rounded-[32px] shadow-sm border border-slate-100">
                        <div class="space-y-8">
                            
                            <div>
                                <label class="text-[11px] font-black uppercase tracking-[0.2em] text-slate-400 block mb-4">
                                    1. Escolha o Formato
                                </label>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <button 
                                        @click="form.type = 'sintetico'"
                                        :class="form.type === 'sintetico' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-200' : 'bg-slate-50 text-slate-600 hover:bg-slate-100'"
                                        class="flex items-center gap-4 p-6 rounded-2xl font-bold transition-all border-2 border-transparent cursor-pointer"
                                    >
                                        <div class="p-3 rounded-xl bg-white/20">
                                            <FileText class="w-6 h-6" />
                                        </div>
                                        <div class="text-left">
                                            <span class="block text-lg">Sintético</span>
                                            <span class="text-xs opacity-70 font-medium">Lista simplificada</span>
                                        </div>
                                    </button>

                                    <button 
                                        @click="form.type = 'analitico'"
                                        :class="form.type === 'analitico' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-200' : 'bg-slate-50 text-slate-600 hover:bg-slate-100'"
                                        class="flex items-center gap-4 p-6 rounded-2xl font-bold transition-all border-2 border-transparent cursor-pointer"
                                    >
                                        <div class="p-3 rounded-xl bg-white/20">
                                            <FileSearch class="w-6 h-6" />
                                        </div>
                                        <div class="text-left">
                                            <span class="block text-lg">Analítico</span>
                                            <span class="text-xs opacity-70 font-medium">Completo com fotos</span>
                                        </div>
                                    </button>
                                </div>
                            </div>

                            <div>
                                <label class="text-[11px] font-black uppercase tracking-[0.2em] text-slate-400 block mb-4">
                                    2. Filtro de Fornecedor
                                </label>
                                <div class="relative flex items-center">
                                    <div class="absolute left-4 pointer-events-none">
                                        <Truck class="w-5 h-5 text-indigo-500" />
                                    </div>
                                    
                                    <select 
                                        v-model="form.supplier_id"
                                        class="w-full pl-12 pr-10 py-5 bg-slate-50 border-2 border-transparent focus:border-indigo-500 rounded-2xl font-bold text-slate-700 focus:ring-0 transition-all appearance-none cursor-pointer"
                                    >
                                        <option value="">Todos os Fornecedores</option>
                                        <option v-for="s in suppliers" :key="s.id" :value="s.id">
                                            {{ s.company_name }}
                                        </option>
                                    </select>

                                    <div class="absolute right-4 pointer-events-none text-slate-400">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                                    </div>
                                </div>
                            </div>

                            <div class="pt-4">
                                <button 
                                    @click="generateReport"
                                    style="background-color: #0f172a !important; color: white !important;"
                                    class="w-full p-6 rounded-2xl font-black flex items-center justify-center gap-4 transition-all shadow-2xl active:scale-[0.98] cursor-pointer"
                                >
                                    <Printer class="w-6 h-6" style="color: white !important;" />
                                    <span style="color: white !important;">GERAR DOCUMENTO PDF</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="bg-indigo-900 p-8 rounded-[32px] text-white shadow-xl shadow-indigo-100 relative overflow-hidden">
                        <div class="relative z-10">
                            <h3 class="font-black text-xl mb-6">Dicas de Impressão</h3>
                            <ul class="space-y-6">
                                <li class="flex gap-4">
                                    <div class="bg-indigo-500/30 p-2 rounded-lg h-fit text-indigo-200 font-bold">01</div>
                                    <p class="text-sm text-indigo-100 leading-relaxed">O relatório <strong>Sintético</strong> é otimizado para economizar papel e tinta.</p>
                                </li>
                                <li class="flex gap-4">
                                    <div class="bg-indigo-500/30 p-2 rounded-lg h-fit text-indigo-200 font-bold">02</div>
                                    <p class="text-sm text-indigo-100 leading-relaxed">O modo <strong>Analítico</strong> gera uma página em modo paisagem para acomodar as imagens e detalhes extras.</p>
                                </li>
                                <li class="flex gap-4">
                                    <div class="bg-indigo-500/30 p-2 rounded-lg h-fit text-indigo-200 font-bold">03</div>
                                    <p class="text-sm text-indigo-100 leading-relaxed">Certifique-se que o bloqueador de pop-ups permite a abertura da nova aba.</p>
                                </li>
                            </ul>
                        </div>
                        <div class="absolute -right-10 -bottom-10 opacity-10">
                            <FileText class="w-40 h-40" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
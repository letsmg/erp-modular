<script setup> 
import { Link, usePage } from '@inertiajs/vue3';
import { 
    LayoutDashboard, Users, Package, Truck, LogOut, CheckCircle2, X, AlertTriangle,
    FileBarChart, Cloud, ShoppingCart, Contact2, ClipboardList, MessageSquare, Menu, ChevronDown
} from 'lucide-vue-next';
import { ref, watch, onMounted, onUnmounted, computed } from 'vue';

const page = usePage();
const user = page.props.auth.user;

// --- Controle do Menu Mobile ---
const isMobileMenuOpen = ref(false);
const toggleMobileMenu = () => isMobileMenuOpen.value = !isMobileMenuOpen.value;

// --- Controle submenu Relatórios ---
const showReportsMenu = ref(false);

// Fecha o menu automaticamente ao navegar
watch(() => page.url, () => { 
    isMobileMenuOpen.value = false; 
});

// --- Lógica de Notificações (Toast) ---
const showToast = ref(false);
const toastMessage = ref('');
const toastType = ref('success'); 

const triggerToast = () => {
    showToast.value = true;
    const duration = toastType.value === 'error' ? 6000 : 4000;
    setTimeout(() => { showToast.value = false; }, duration);
};

watch(() => page.props.flash?.message, (newMessage) => {
    if (newMessage) {
        toastType.value = 'success';
        toastMessage.value = newMessage;
        triggerToast();
    }
}, { immediate: true });

const errors = computed(() => page.props.errors);
watch(errors, (newErrors) => {
    if (Object.keys(newErrors).length > 0) {
        toastType.value = 'error';
        toastMessage.value = Object.values(newErrors)[0];
        triggerToast();
    }
}, { deep: true });

// --- Atalhos ---
const handleKeyDown = (e) => {
    if (e.ctrlKey && e.shiftKey && e.key.toLowerCase() === 'p') {
        e.preventDefault();
        window.dispatchEvent(new CustomEvent('magic-fill'));
    }
};

onMounted(() => { window.addEventListener('keydown', handleKeyDown); });
onUnmounted(() => { window.removeEventListener('keydown', handleKeyDown); });

const isUrl = (url) => page.url === url || page.url.startsWith(url + '/');
</script>

<template>
<div class="min-h-screen bg-gray-50 flex overflow-x-hidden">

    <!-- Overlay mobile -->
    <Transition>
        <div v-if="isMobileMenuOpen" @click="isMobileMenuOpen = false"
            class="fixed inset-0 bg-slate-900/60 z-30 md:hidden"></div>
    </Transition>

    <!-- Sidebar -->
    <aside :class="[
        'fixed inset-y-0 left-0 w-64 bg-slate-950 text-white flex flex-col z-40 transition-transform md:translate-x-0',
        isMobileMenuOpen ? 'translate-x-0' : '-translate-x-full'
    ]">

        <!-- Logo -->
        <div class="p-6 border-b border-slate-900 flex justify-between">
            <span class="font-black">ERP ZENITE</span>
            <X @click="isMobileMenuOpen = false" class="md:hidden"/>
        </div>

        <!-- Menu -->
        <nav class="flex-1 p-4 space-y-1 text-sm">

            <!-- Dashboard -->
            <Link :href="route('dashboard')" :class="isUrl('/dashboard') ? 'bg-indigo-600 text-white' : 'text-slate-400'"
                class="flex items-center p-3 rounded-xl">
                <LayoutDashboard class="w-5"/>
                <span class="ml-2">Dashboard</span>
            </Link>

            <!-- Comercial -->
            <p class="text-xs text-slate-500 mt-4">Comercial</p>

            <span class="menu-item opacity-40 cursor-not-allowed flex items-center gap-2">
                <Contact2 class="w-5"/> Clientes
            </span>

            <span class="menu-item opacity-40 cursor-not-allowed flex items-center gap-2">
                <ShoppingCart class="w-5"/> Vendas
            </span>

            <!-- Logística -->
            <p class="text-xs text-slate-500 mt-4">Logística</p>

            <Link :href="route('products.index')" class="menu-item">
                <Package class="w-5"/> Produtos
            </Link>

            <!-- Gestão -->
            <p class="text-xs text-slate-500 mt-4">Gestão</p>

            <Link :href="route('users.index')" class="menu-item">
                <Users class="w-5"/> Usuários
            </Link>

            <!-- 🔥 RELATÓRIOS COM SUBMENU -->
            <div>
                <button @click="showReportsMenu = !showReportsMenu"
                    class="flex items-center justify-between w-full p-3 rounded-xl text-slate-400 hover:bg-slate-900">
                    
                    <div class="flex items-center gap-2">
                        <FileBarChart class="w-5"/>
                        <span>Relatórios</span>
                    </div>

                    <ChevronDown :class="showReportsMenu ? 'rotate-180' : ''"/>
                </button>

                <!-- Submenu -->
                <div v-if="showReportsMenu" class="ml-6 mt-1 space-y-1 text-sm">

                    <span class="block opacity-40 cursor-not-allowed p-2">Clientes</span>

                    <Link :href="route('reports.products')" class="block p-2 hover:text-white">
                        Produtos
                    </Link>

                    <span class="block opacity-40 cursor-not-allowed p-2">Fornecedores</span>
                    <span class="block opacity-40 cursor-not-allowed p-2">Vendas</span>
                    <span class="block opacity-40 cursor-not-allowed p-2">Fiscais</span>

                </div>
            </div>

        </nav>

        <!-- Logout -->
        <div class="p-4 border-t">
            <Link :href="route('logout')" method="post">Sair</Link>
        </div>
    </aside>

    <!-- Conteúdo -->
    <div class="flex-1 md:ml-64">
        <header class="p-4 bg-white shadow flex justify-between">
            <Menu @click="toggleMobileMenu"/>
            <span>{{ user.name }}</span>
        </header>

        <main class="p-6">
            <slot />
        </main>
    </div>

</div>
</template>

<style scoped>
.menu-item {
    display: flex;
    gap: 8px;
    padding: 12px;
    border-radius: 10px;
    color: #94a3b8;
}
.menu-item:hover {
    background: #0f172a;
    color: white;
}
</style>
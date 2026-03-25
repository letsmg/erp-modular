<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { 
    Home, User, ShoppingCart, Package, Heart, LogOut, Menu, X 
} from 'lucide-vue-next';
import { ref, watch } from 'vue';

const page = usePage();
const user = page.props.auth.user;

// Mobile
const isMobileMenuOpen = ref(false);
const toggleMobileMenu = () => isMobileMenuOpen.value = !isMobileMenuOpen.value;

watch(() => page.url, () => {
    isMobileMenuOpen.value = false;
});

// Menu cliente (tudo desabilitado por enquanto)
const menuCliente = [
    { nome: 'Início', icone: Home, rota: '/cliente', ativo: false },
    { nome: 'Seus Dados', icone: User, rota: '/cliente/dados', ativo: false },
    { nome: 'Pedidos', icone: Package, rota: '/cliente/pedidos', ativo: false },
    { nome: 'Carrinho', icone: ShoppingCart, rota: '/cliente/carrinho', ativo: false },
    { nome: 'Favoritos', icone: Heart, rota: '/cliente/favoritos', ativo: false }
];
</script>

<template>
<div class="min-h-screen bg-gray-50 flex overflow-x-hidden">

    <!-- Overlay -->
    <div v-if="isMobileMenuOpen"
        @click="isMobileMenuOpen = false"
        class="fixed inset-0 bg-black/50 z-30 md:hidden">
    </div>

    <!-- Sidebar -->
    <aside :class="[
        'fixed inset-y-0 left-0 w-64 bg-white border-r flex flex-col z-40 transition-transform md:translate-x-0',
        isMobileMenuOpen ? 'translate-x-0' : '-translate-x-full'
    ]">

        <!-- Logo / Loja -->
        <div class="p-5 border-b flex justify-between items-center">
            <div>
                <p class="font-black text-gray-900">Minha Loja</p>
                <p class="text-xs text-gray-400">Área do Cliente</p>
            </div>

            <X class="md:hidden cursor-pointer" @click="isMobileMenuOpen = false"/>
        </div>

        <!-- Menu -->
        <nav class="flex-1 p-4 space-y-1 text-sm">

            <div v-for="item in menuCliente" :key="item.nome">

                <!-- Ativo -->
                <Link v-if="item.ativo"
                    :href="item.rota"
                    class="flex items-center gap-3 p-3 rounded-xl text-gray-700 hover:bg-gray-100">
                    <component :is="item.icone" class="w-5"/>
                    {{ item.nome }}
                </Link>

                <!-- Desabilitado -->
                <span v-else
                    class="flex items-center gap-3 p-3 rounded-xl text-gray-400 cursor-not-allowed opacity-60">
                    <component :is="item.icone" class="w-5"/>
                    {{ item.nome }}
                </span>

            </div>

        </nav>

        <!-- Logout -->
        <div class="p-4 border-t">
            <Link :href="route('logout')" method="post"
                class="flex items-center gap-3 text-red-500 hover:bg-red-50 p-3 rounded-xl font-bold">
                <LogOut class="w-5"/>
                Sair
            </Link>
        </div>
    </aside>

    <!-- Conteúdo -->
    <div class="flex-1 md:ml-64 flex flex-col">

        <!-- Header -->
        <header class="h-16 bg-white border-b flex items-center justify-between px-4 md:px-8">

            <button @click="toggleMobileMenu" class="md:hidden">
                <Menu class="w-6 h-6"/>
            </button>

            <h1 class="font-bold text-gray-700">
                Área do Cliente
            </h1>

            <div class="flex items-center gap-3">
                <span class="text-sm font-bold text-gray-700">
                    {{ user.name }}
                </span>

                <div class="w-9 h-9 bg-gray-200 rounded-full flex items-center justify-center text-xs font-bold">
                    {{ user.name.substring(0,2) }}
                </div>
            </div>
        </header>

        <!-- Conteúdo -->
        <main class="p-6 flex-1">
            <slot />
        </main>

    </div>

</div>
</template>

<style scoped>
</style>
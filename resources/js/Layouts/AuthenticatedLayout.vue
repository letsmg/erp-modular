<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { LayoutDashboard, Users, Package, LogOut, Menu } from 'lucide-vue-next';

const page = usePage();
const user = page.props.auth.user;
</script>

<template>
    <div class="min-h-screen bg-gray-50 flex">
        <aside class="w-64 bg-slate-900 text-white hidden md:flex flex-col">
            <div class="p-6 text-2xl font-bold border-b border-slate-800">
                ERP <span class="text-blue-400">Zenite</span>
            </div>
            
            <nav class="flex-1 p-4 space-y-2">
                <Link href="/" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-slate-800 transition">
                    <LayoutDashboard class="w-5 h-5" />
                    <span>Dashboard</span>
                </Link>
                <Link href="/users" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-slate-800 transition">
                    <Users class="w-5 h-5" />
                    <span>Usuários</span>
                </Link>
                <Link href="/products" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-slate-800 transition">
                    <Package class="w-5 h-5" />
                    <span>Produtos</span>
                </Link>
            </nav>

            <div class="p-4 border-t border-slate-800">
                <Link href="/logout" method="post" as="button" class="flex items-center space-x-3 p-3 w-full text-red-400 hover:bg-slate-800 rounded-lg">
                    <LogOut class="w-5 h-5" />
                    <span>Sair</span>
                </Link>
            </div>
        </aside>

        <div class="flex-1 flex flex-col">
            <header class="bg-white shadow-sm h-16 flex items-center justify-between px-8">
                <h1 class="text-xl font-semibold text-gray-800">Bem-vindo, {{ user.name }}</h1>
                <div class="flex items-center space-x-4">
                    <span class="text-sm text-gray-500">{{ user.email }}</span>
                    <div class="w-10 h-10 bg-blue-100 text-blue-600 flex items-center justify-center rounded-full font-bold">
                        {{ user.name[0] }}
                    </div>
                </div>
            </header>

            <main class="p-8">
                <slot />
            </main>

            <footer class="mt-auto py-4 px-8 bg-white border-t text-center text-sm text-gray-400">
                &copy; 2026 ERP Zenite - Todos os direitos reservados.
            </footer>
        </div>
    </div>
</template>
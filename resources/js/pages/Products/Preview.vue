<script setup>
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import { 
    ArrowLeft, ShoppingCart, ShieldCheck, 
    Globe, Star, Loader2, Eye, EyeOff, LayoutDashboard
} from 'lucide-vue-next';
import { computed, ref } from 'vue';
import StoreLayout from '@/Layouts/StoreLayout.vue'; 

const props = defineProps({
    product: Object,
    relatedProducts: Array
});

// 🖼️ Controle da Galeria de Imagens
const activeImageIndex = ref(0);

// 🛠️ Helper para construir a URL da imagem (Ajustado para /storage/products/)
const getImageUrl = (path) => {
    if (!path) return null;
    // Garante que o caminho aponte para a pasta 'products' dentro do storage
    const cleanPath = path.startsWith('products/') ? path : `products/${path}`;
    return `/storage/${cleanPath}`;
};

const page = usePage();
const isAdmin = computed(() => page.props.auth?.user?.access_level === 1);
const isUpdating = ref(false);

const toggleStatus = () => {
    if (route().has('products.toggle')) {
        isUpdating.value = true;
        router.patch(route('products.toggle', props.product.id), {}, {
            preserveScroll: true,
            onFinish: () => isUpdating.value = false,
        });
    }
};

// Mapeamento de SEO do objeto 'seo' recebido no dump
const seoData = computed(() => props.product.seo || {});

const formatCurrency = (value) => {
    return new Number(value).toLocaleString('pt-BR', { 
        style: 'currency', 
        currency: 'BRL' 
    });
};
</script>

<template>
    <Head>
        <title>[PREVIEW] {{ seoData.meta_title || product.description }}</title>
        <meta name="robots" content="noindex, nofollow" />
    </Head>

    <StoreLayout>
        <div class="bg-indigo-600 text-white relative overflow-hidden">
            <div class="max-w-6xl mx-auto px-6 py-3 flex items-center justify-between relative z-10">
                <div class="flex items-center gap-3">
                    <div class="bg-white/20 p-2 rounded-lg backdrop-blur-md">
                        <LayoutDashboard class="w-5 h-5 text-white" />
                    </div>
                    <div>
                        <p class="text-[11px] font-black uppercase tracking-[0.2em] leading-none">Modo de Visualização</p>
                        <p class="text-[9px] font-medium opacity-80 uppercase tracking-widest mt-1">Visualizando galeria e especificações do produto</p>
                    </div>
                </div>
                
                <div v-if="isAdmin" class="flex items-center gap-4">
                    <button 
                        @click="toggleStatus"
                        :disabled="isUpdating"
                        class="bg-white text-indigo-600 px-5 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all flex items-center gap-2 shadow-lg active:scale-95 disabled:opacity-50"
                    >
                        <Loader2 v-if="isUpdating" class="w-3 h-3 animate-spin" />
                        <component v-else :is="product.is_active ? EyeOff : Eye" class="w-3.5 h-3.5" />
                        {{ product.is_active ? 'Ocultar da Loja' : 'Publicar Produto' }}
                    </button>
                </div>
            </div>
            <div class="absolute top-0 right-0 w-64 h-full bg-gradient-to-l from-white/10 to-transparent skew-x-12 transform translate-x-20"></div>
        </div>

        <div class="min-h-screen bg-[#F9FAFB] pb-24">
            <div class="max-w-6xl mx-auto px-6 py-8">
                <Link :href="route('products.index')" class="inline-flex items-center gap-2 text-slate-400 hover:text-indigo-600 text-[10px] font-black uppercase tracking-[0.2em] transition group">
                    <ArrowLeft class="w-4 h-4 group-hover:-translate-x-1 transition-transform" /> 
                    Voltar para Gestão
                </Link>
            </div>

            <main class="max-w-6xl mx-auto px-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-12 lg:gap-20 items-start">
                    
                    <div class="flex flex-col gap-6">
                        <div class="relative aspect-square bg-white rounded-[3rem] overflow-hidden flex items-center justify-center border border-gray-100 shadow-2xl shadow-gray-200/50">
                            <template v-if="product.images && product.images.length > 0">
                                <img 
                                    :src="getImageUrl(product.images[activeImageIndex].path)" 
                                    class="object-contain w-full h-full p-12 transition-all duration-500"
                                />
                            </template>
                            <div v-else class="flex flex-col items-center text-gray-200">
                                <Globe class="w-20 h-20 mb-2 opacity-20" />
                                <span class="text-[10px] font-black uppercase tracking-widest">Sem Imagem</span>
                            </div>

                            <div class="absolute top-8 right-8">
                                <span :class="product.is_active ? 'bg-emerald-500' : 'bg-red-500'" class="text-white px-5 py-2.5 rounded-2xl text-[9px] font-black uppercase tracking-widest shadow-2xl">
                                    {{ product.is_active ? 'Ativo' : 'Inativo' }}
                                </span>
                            </div>
                        </div>

                        <div v-if="product.images && product.images.length > 1" class="grid grid-cols-5 gap-4">
                            <button 
                                v-for="(img, index) in product.images" 
                                :key="img.id"
                                @click="activeImageIndex = index"
                                class="aspect-square rounded-2xl border-2 overflow-hidden bg-white p-2 transition-all duration-300"
                                :class="activeImageIndex === index ? 'border-indigo-600 shadow-lg scale-105' : 'border-transparent hover:border-gray-200 opacity-60 hover:opacity-100'"
                            >
                                <img :src="getImageUrl(img.path)" class="w-full h-full object-contain" />
                            </button>
                        </div>
                    </div>

                    <div class="flex flex-col py-4">
                        <nav class="text-[11px] uppercase font-black text-indigo-500 mb-4 tracking-[0.3em]">
                            {{ product.brand }} // {{ product.model }}
                        </nav>

                        <h1 class="text-4xl lg:text-5xl font-black text-gray-900 mb-6 leading-[0.9] tracking-tighter">
                            {{ seoData.h1 || product.description }}
                        </h1>

                        <div class="flex items-center gap-1 mb-10 text-amber-400">
                            <Star class="w-4 h-4 fill-current" v-for="i in 5" :key="i" />
                            <span class="text-gray-400 text-[10px] font-bold ml-2 uppercase tracking-widest">(Modo Preview)</span>
                        </div>

                        <div class="mb-10">
                            <div class="text-5xl font-black text-gray-900 tracking-tighter">
                                {{ formatCurrency(product.sale_price) }}
                            </div>
                            <p class="text-[11px] font-bold text-emerald-600 uppercase tracking-widest mt-3 flex items-center gap-2">
                                Estoque: {{ product.stock_quantity }} disponíveis
                            </p>
                        </div>

                        <div class="bg-white p-8 rounded-[2.5rem] border border-gray-100 shadow-sm mb-10 relative overflow-hidden">
                            <div class="absolute top-0 left-0 w-1.5 h-full bg-indigo-600"></div>
                            <p class="text-gray-600 text-[15px] italic leading-relaxed">
                                "{{ seoData.text1 || 'Nenhuma descrição resumida cadastrada.' }}"
                            </p>
                        </div>

                        <button class="w-full bg-black text-white py-6 rounded-[2rem] font-black uppercase tracking-[0.2em] text-[11px] hover:bg-indigo-600 transition-all shadow-2xl flex items-center justify-center gap-3">
                            <ShoppingCart class="w-5 h-5" />
                            Simular Compra
                        </button>
                    </div>
                </div>

                <section class="mt-24 pt-20 border-t border-gray-200">
                    <div class="max-w-3xl">
                        <h3 class="text-2xl font-black text-gray-900 uppercase tracking-tighter mb-10">Especificações e Detalhes</h3>
                        <div class="prose prose-indigo max-w-none text-gray-500 text-base leading-loose whitespace-pre-line font-medium">
                            {{ seoData.text2 || 'As especificações técnicas detalhadas não foram preenchidas.' }}
                        </div>
                    </div>
                </section>

                <section class="mt-32">
                    <div class="flex items-center gap-6 mb-12">
                        <h3 class="text-2xl font-black text-gray-900 uppercase tracking-tighter shrink-0">Produtos Relacionados</h3>
                        <div class="h-px w-full bg-gray-200"></div>
                    </div>

                    <div v-if="relatedProducts && relatedProducts.length > 0" class="grid grid-cols-2 md:grid-cols-4 gap-8">
                        <div v-for="item in relatedProducts" :key="item.id" class="group">
                            <Link :href="route('products.preview', item.id)" class="block">
                                <div class="aspect-square bg-white rounded-[2.5rem] border border-gray-100 overflow-hidden mb-5 flex items-center justify-center p-8 group-hover:shadow-2xl transition-all duration-500 group-hover:-translate-y-2">
                                    <img v-if="item.images && item.images.length > 0" 
                                         :src="getImageUrl(item.images[0].path)" 
                                         class="max-w-full max-h-full object-contain group-hover:scale-110 transition duration-500" />
                                    <Globe v-else class="w-12 h-12 text-gray-100" />
                                </div>
                                <h4 class="text-[10px] font-black uppercase text-indigo-500 tracking-widest mb-1">{{ item.brand }}</h4>
                                <p class="text-sm font-bold text-gray-800 truncate">{{ item.description }}</p>
                                <div class="text-base font-black text-gray-900 mt-2">
                                    {{ formatCurrency(item.sale_price) }}
                                </div>
                            </Link>
                        </div>
                    </div>
                </section>
            </main>
        </div>
    </StoreLayout>
</template>

<style scoped>
/* Melhora a suavidade das transições de imagem */
img {
    backface-visibility: hidden;
    transform: translateZ(0);
}
</style>
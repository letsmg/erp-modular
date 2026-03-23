<script setup>
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import { 
    ArrowLeft, ShoppingCart, Globe, Star, Loader2, 
    Eye, EyeOff, LayoutDashboard, ChevronLeft, ChevronRight
} from 'lucide-vue-next';
import { computed, ref } from 'vue';
import StoreLayout from '@/Layouts/StoreLayout.vue'; 

const props = defineProps({
    product: Object,
    relatedProducts: Array
});

// 🖼️ Controle da Galeria Manual
const activeImageIndex = ref(0);

const nextImage = () => {
    if (props.product.images && props.product.images.length > 0) {
        activeImageIndex.value = (activeImageIndex.value + 1) % props.product.images.length;
    }
};

const prevImage = () => {
    if (props.product.images && props.product.images.length > 0) {
        activeImageIndex.value = (activeImageIndex.value - 1 + props.product.images.length) % props.product.images.length;
    }
};

// 🛠️ Helper para URL da imagem
const getImageUrl = (path) => {
    if (!path) return null;
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

const seoData = computed(() => props.product.seo || {});

const formatCurrency = (value) => {
    return new Number(value).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
};
</script>

<template>
    <Head>
        <title>[PREVIEW] {{ seoData.meta_title || product.description }}</title>
    </Head>

    <StoreLayout>
        <div class="bg-indigo-600 text-white relative overflow-hidden">
            <div class="max-w-6xl mx-auto px-6 py-3 flex items-center justify-between relative z-10">
                <div class="flex items-center gap-3">
                    <LayoutDashboard class="w-5 h-5" />
                    <span class="text-[10px] font-black uppercase tracking-widest">Preview: Carrossel Manual</span>
                </div>
                <button v-if="isAdmin" @click="toggleStatus" :disabled="isUpdating" class="bg-white text-indigo-600 px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest flex items-center gap-2">
                    <Loader2 v-if="isUpdating" class="w-3 h-3 animate-spin" />
                    {{ product.is_active ? 'Ocultar' : 'Publicar' }}
                </button>
            </div>
        </div>

        <div class="min-h-screen bg-[#F9FAFB] pb-24">
            <div class="max-w-6xl mx-auto px-6 py-8">
                <Link :href="route('products.index')" class="text-slate-400 hover:text-indigo-600 text-[10px] font-black uppercase tracking-widest flex items-center gap-2">
                    <ArrowLeft class="w-4 h-4" /> Voltar ao Painel
                </Link>
            </div>

            <main class="max-w-6xl mx-auto px-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-12 lg:gap-20 items-start">
                    
                    <div class="flex flex-col gap-6">
                        <div class="relative aspect-square bg-white rounded-[3rem] overflow-hidden border border-gray-100 shadow-2xl group">
                            
                            <div class="w-full h-full flex items-center justify-center p-12">
                                <template v-if="product.images && product.images.length > 0">
                                    <img 
                                        :key="activeImageIndex"
                                        :src="getImageUrl(product.images[activeImageIndex].path)" 
                                        class="max-w-full max-h-full object-contain animate-in fade-in zoom-in duration-300"
                                    />
                                </template>
                                <Globe v-else class="w-20 h-20 text-gray-100 opacity-20" />
                            </div>

                            <template v-if="product.images && product.images.length > 1">
                                <button 
                                    @click="prevImage" 
                                    class="absolute left-6 top-1/2 -translate-y-1/2 bg-white/80 backdrop-blur-md p-3 rounded-full shadow-lg hover:bg-white transition-all active:scale-90"
                                >
                                    <ChevronLeft class="w-6 h-6 text-gray-900" />
                                </button>
                                <button 
                                    @click="nextImage" 
                                    class="absolute right-6 top-1/2 -translate-y-1/2 bg-white/80 backdrop-blur-md p-3 rounded-full shadow-lg hover:bg-white transition-all active:scale-90"
                                >
                                    <ChevronRight class="w-6 h-6 text-gray-900" />
                                </button>
                            </template>

                            <div v-if="product.images?.length" class="absolute bottom-8 left-1/2 -translate-x-1/2 bg-black/5 text-black/50 px-4 py-1 rounded-full text-[10px] font-black">
                                {{ activeImageIndex + 1 }} / {{ product.images.length }}
                            </div>
                        </div>

                        <div v-if="product.images && product.images.length > 1" class="flex gap-4 overflow-x-auto pb-2 scrollbar-hide">
                            <button 
                                v-for="(img, index) in product.images" 
                                :key="img.id"
                                @click="activeImageIndex = index"
                                class="w-20 h-20 shrink-0 rounded-2xl border-2 overflow-hidden bg-white p-2 transition-all"
                                :class="activeImageIndex === index ? 'border-indigo-600 ring-4 ring-indigo-50' : 'border-transparent opacity-50 hover:opacity-100'"
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

                        <div class="text-5xl font-black text-gray-900 mb-10 tracking-tighter">
                            {{ formatCurrency(product.sale_price) }}
                        </div>

                        <div class="bg-white p-8 rounded-[2.5rem] border border-gray-100 shadow-sm mb-10 relative overflow-hidden">
                            <div class="absolute top-0 left-0 w-1.5 h-full bg-indigo-600"></div>
                            <p class="text-gray-600 text-[15px] italic leading-relaxed">
                                "{{ seoData.text1 || 'Descrição curta não informada.' }}"
                            </p>
                        </div>

                        <button class="w-full bg-black text-white py-6 rounded-[2rem] font-black uppercase tracking-[0.2em] text-[11px] hover:bg-indigo-600 transition-all shadow-2xl active:scale-[0.98]">
                            <ShoppingCart class="w-5 h-5 inline mr-2" />
                            Adicionar ao Carrinho
                        </button>
                    </div>
                </div>

                <section class="mt-24 pt-20 border-t border-gray-200">
                    <div class="max-w-3xl">
                        <h3 class="text-2xl font-black text-gray-900 uppercase tracking-tighter mb-10">Ficha Técnica</h3>
                        <div class="prose prose-indigo max-w-none text-gray-500 text-base leading-loose whitespace-pre-line font-medium">
                            {{ seoData.text2 || 'Sem especificações detalhadas.' }}
                        </div>
                    </div>
                </section>
            </main>
        </div>
    </StoreLayout>
</template>

<style scoped>
/* Remove scrollbar das miniaturas no Chrome/Safari */
.scrollbar-hide::-webkit-scrollbar {
    display: none;
}
.scrollbar-hide {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>
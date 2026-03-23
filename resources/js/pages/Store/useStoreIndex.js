import { ref, watch, computed, onMounted, onUnmounted } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import { debounce } from 'lodash';

export function useStoreIndex(props) {
    const page = usePage();

    // --- FILTROS ---
    const search = ref(props.filters?.search || '');
    const minPrice = ref(props.filters?.min_price || '');
    const maxPrice = ref(props.filters?.max_price || '');
    const brand = ref(props.filters?.brand || '');
    const category = ref(props.filters?.category || '');

    const filterProducts = debounce(() => {
        const searchTerm = search.value.length > 0 && search.value.length < 3 ? '' : search.value;
        router.get(route('store.index'), {
            search: searchTerm,
            min_price: minPrice.value,
            max_price: maxPrice.value,
            brand: brand.value,
            category: category.value
        }, {
            preserveState: true,
            preserveScroll: true,
            replace: true
        });
    }, 500);

    watch([search, minPrice, maxPrice, brand, category], () => filterProducts());

    // --- MODAL DE PRODUTO ---
    const isModalOpen = ref(false);
    const selectedProduct = ref(null);

    const openDetails = (p) => { 
        selectedProduct.value = p; 
        isModalOpen.value = true; 
    };

    const closeModal = () => {
        isModalOpen.value = false;
        selectedProduct.value = null;
    };

    // --- MODAL DE TERMOS (LGPD) ---
    const showTermsModal = ref(false);
    const termsAccepted = ref(false);

    const acceptTerms = () => {
        if (termsAccepted.value) {
            localStorage.setItem('erp_terms_accepted', 'true');
            showTermsModal.value = false;
        }
    };

    // --- ATALHO DE TECLADO (CTRL + M) ---
    const handleKeyDown = (event) => {
        // Verifica se Ctrl + M (ou Cmd + M no Mac) foi pressionado
        if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'm') {
            event.preventDefault();
            showTermsModal.value = true;
        }
    };

    // --- CARROSSEL ---
    let timer = null;
    const scroll = (id, direction) => {
        const el = document.getElementById(id);
        if (!el) return;
        const isAtEnd = el.scrollLeft + el.offsetWidth >= el.scrollWidth - 10;
        if (direction === 'right' && isAtEnd) {
            el.scrollTo({ left: 0, behavior: 'smooth' });
        } else {
            const offset = direction === 'left' ? -el.offsetWidth : el.offsetWidth;
            el.scrollBy({ left: offset, behavior: 'smooth' });
        }
    };

    // --- CICLO DE VIDA ---
    onMounted(() => { 
        // Checagem automática de Termos ao carregar
        if (!localStorage.getItem('erp_terms_accepted')) {
            showTermsModal.value = true;
        }

        // Adiciona ouvinte para o atalho de teclado
        window.addEventListener('keydown', handleKeyDown);

        // Timer do carrossel
        timer = setInterval(() => scroll('hero-carousel', 'right'), 7000); 
    });

    onUnmounted(() => { 
        // Remove ouvintes e timers para evitar memory leaks
        window.removeEventListener('keydown', handleKeyDown);
        if (timer) clearInterval(timer); 
    });

    // --- SEO ---
    const seoData = computed(() => {
        return page.props.store_seo ?? {
            title: "Vitrine Premium",
            description: "ERP Vue Laravel - Portfólio de E-commerce",
            keywords: "laravel, vue, portfolio",
            h1: "Explore Nossa Vitrine"
        };
    });

    return {
        search, minPrice, maxPrice, brand, category,
        isModalOpen, selectedProduct, openDetails, closeModal,
        showTermsModal, termsAccepted, acceptTerms,
        scroll, seoData
    };
}
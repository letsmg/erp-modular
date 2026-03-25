import { ref, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';

export function useProductEdit(props) {
    const activeTab = ref('geral');
    const newImagePreviews = ref([]);

    const form = useForm({
        _method: 'PUT', // Necessário para enviar arquivos em rota de atualização (Multipart/form-data)
        description: props.product.description || '',
        supplier_id: props.product.supplier_id || '',
        barcode: props.product.barcode || '',
        brand: props.product.brand || '',
        model: props.product.model || '',
        collection: props.product.collection || '',
        size: props.product.size || '',
        gender: props.product.gender || 'Unissex',
        stock_quantity: props.product.stock_quantity || 0,
        cost_price: props.product.cost_price || 0,
        sale_price: props.product.sale_price || 0,
        promo_price: props.product.promo_price || null,
        promo_start_at: props.product.promo_start_at || null,
        promo_end_at: props.product.promo_end_at || null,
        
        // SEO & Marketing
        google_tag_manager: props.product.google_tag_manager || '',
        ads: props.product.ads || '',
        canonical_url: props.product.canonical_url || '',
        meta_title: props.product.meta_title || '',
        meta_keywords: props.product.meta_keywords || '',
        meta_description: props.product.meta_description || '',
        h1: props.product.h1 || '',
        h2: props.product.h2 || '',
        schema_markup: props.product.schema_markup || '',

        // Imagens
        existing_images: props.product.images || [], // O draggable vai manipular este array
        new_images: [],
        removed_images: [], // IDs das imagens que serão deletadas no server
    });

    // Cálculos de Lucro
    const profitData = computed(() => {
        const cost = parseFloat(form.cost_price) || 0;
        const sale = parseFloat(form.sale_price) || 0;
        const profit = sale - cost;
        const percentage = sale > 0 ? (profit / sale) * 100 : 0;

        return {
            value: profit.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' }),
            percentage: percentage.toFixed(1)
        };
    });

    // Upload de novas imagens
    const handleImageUpload = (e) => {
        const files = Array.from(e.target.files);
        const remainingSlots = 6 - (form.existing_images.length + form.new_images.length);
        
        files.slice(0, remainingSlots).forEach(file => {
            form.new_images.push(file);
            const reader = new FileReader();
            reader.onload = (e) => newImagePreviews.value.push(e.target.result);
            reader.readAsDataURL(file);
        });
    };

    const removeExistingImage = (index) => {
        const image = form.existing_images[index];
        form.removed_images.push(image.id); // Avisa o backend para deletar o arquivo
        form.existing_images.splice(index, 1);
    };

    const removeNewImage = (index) => {
        form.new_images.splice(index, 1);
        newImagePreviews.value.splice(index, 1);
    };

    const submit = () => {
        // A mágica acontece aqui: 
        // Como o draggable reordenou o 'form.existing_images',
        // basta enviar esse array e o backend itera sobre ele 
        // atualizando a coluna 'order' ou 'position'.
        form.post(route('products.update', props.product.id), {
            preserveScroll: true,
            onSuccess: () => {
                newImagePreviews.value = [];
                form.new_images = [];
            },
        });
    };

    return {
        form,
        activeTab,
        newImagePreviews,
        handleImageUpload,
        removeExistingImage,
        removeNewImage,
        profitData,
        submit
    };
}
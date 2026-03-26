import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import type { DefineComponent } from 'vue';
import { createApp, h } from 'vue';
import { ZiggyVue } from '../../vendor/tightenco/ziggy'; // Adicione esta linha
import '../css/app.css';
import axios from 'axios';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    resolve: (name) =>
        resolvePageComponent(
            `./pages/${name}.vue`,
            import.meta.glob<DefineComponent>('./pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue, (window as any).Ziggy) // Adicione esta linha aqui
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});

// Interceptor global para capturar falhas de sessão
axios.interceptors.response.use(
    response => response,
    error => {
        // 401: Não autenticado | 419: Sessão/CSRF expirado
        if (error.response && [401, 419].includes(error.response.status)) {
            // Força um recarregamento total da página. 
            // O Middleware 'auth' do Laravel então assumirá o controle e redirecionará para /login
            window.location.href = '/login';
        }
        return Promise.reject(error);
    }
);
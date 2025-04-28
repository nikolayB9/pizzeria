import { defineConfig, loadEnv } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig(({ mode }) => {
    const env = loadEnv(mode, process.cwd(), '');

    return {
        plugins: [
            laravel({
                input: ['resources/css/app.css', 'resources/js/app.js'],
                refresh: true,
            }),
            vue(),
        ],
        server: {
            host: '0.0.0.0',
            port: parseInt(env.VITE_PORT),
            hmr: {
                host: 'localhost',
                protocol: 'ws',
                clientPort: parseInt(env.VITE_PORT_HOST),
            },
        },
    };
});

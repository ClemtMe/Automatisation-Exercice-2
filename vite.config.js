import { defineConfig } from 'vite';


export default defineConfig({
    root: './src/assets',
    base: '/build/',
    server: {
        host: true,
        port: 3000,
    },
    build: {
        assetsDir: '',
        outDir: '../../public/build/',
        rollupOptions: {
            input: {
                'main.js': '/main.js',
            },
            output: {
                // 1. Pour les fichiers JS d'entrée (ton script principal)
                entryFileNames: '[name]',

                // 2. Pour les "chunks" (fichiers JS partagés ou lazy-loadés)
                chunkFileNames: '[name].js',

                // 3. Pour les assets (CSS, images, fonts)
                // [name] gardera le nom d'origine, [ext] l'extension
                assetFileNames: '[name].[ext]',
            }
        }
    },
});
import { defineConfig } from 'vite'

export default defineConfig({
    root: '.',
    publicDir: false,

    build: {
        outDir: 'public/assets', // où sont générés les fichiers minifiés
        emptyOutDir: true, // vider avant génération
        manifest: true, // auto-générer manifest.json
        sourcemap: false,

        rollupOptions: {
            // points d'entrée
            input: {
                app: 'resources/js/app.js',
                gallery: 'resources/js/pages/gallery.js',
                signup: 'resources/js/pages/signup.js',
                login: 'resources/js/pages/login.js',
                reserve: 'resources/js/pages/reserve.js',
                userProfile: 'resources/js/pages/user-profile.js',
                userReservations: 'resources/js/pages/user-reservations.js',
                adminService: 'resources/js/pages/admin-services.js',
                adminReservations: 'resources/js/pages/admin-reservations.js',
                adminMenu: 'resources/js/pages/admin-menu/index.js',
                cloudinary: 'resources/js/api/cloudinary.js'
            },
            // noms fichiers après build
            output: {
                entryFileNames: 'js/[name].[hash].js',
                chunkFileNames: 'js/[name].[hash].js',
                assetFileNames: (assetInfo) => {
                    if (assetInfo.name.endsWith('.css')) {
                        return 'styles/[name].[hash][extname]'
                    }
                    return 'assets/[name].[hash][extname]'
                }
            }
        }
    },

    server: {
        origin: 'http://localhost:5173',
    }
})
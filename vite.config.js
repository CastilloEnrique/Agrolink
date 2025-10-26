// import laravel from 'laravel-vite-plugin'
// import { fileURLToPath } from 'node:url'
// import VueI18nPlugin from '@intlify/unplugin-vue-i18n/vite'
// import vue from '@vitejs/plugin-vue'
// import vueJsx from '@vitejs/plugin-vue-jsx'
// import AutoImport from 'unplugin-auto-import/vite'
// import Components from 'unplugin-vue-components/vite'
// import { VueRouterAutoImports, getPascalCaseRouteName } from 'unplugin-vue-router'
// import VueRouter from 'unplugin-vue-router/vite'
// import { defineConfig } from 'vite'
// import VueDevTools from 'vite-plugin-vue-devtools'
// import MetaLayouts from 'vite-plugin-vue-meta-layouts'
// import vuetify from 'vite-plugin-vuetify'
// import svgLoader from 'vite-svg-loader'
//
// // https://vitejs.dev/config/
// export default defineConfig({
//   plugins: [// Docs: https://github.com/posva/unplugin-vue-router
//   // ℹ️ This plugin should be placed before vue plugin
//     VueRouter({
//       getRouteName: routeNode => {
//       // Convert pascal case to kebab case
//         return getPascalCaseRouteName(routeNode)
//           .replace(/([a-z\d])([A-Z])/g, '$1-$2')
//           .toLowerCase()
//       },
//
//       beforeWriteFiles: root => {
//         root.insert('/apps/email/:filter', '/resources/js/pages/apps/email/index.vue')
//         root.insert('/apps/email/:label', '/resources/js/pages/apps/email/index.vue')
//       },
//
//       routesFolder: 'resources/js/pages',
//     }),
//     vue({
//       template: {
//         compilerOptions: {
//           isCustomElement: tag => tag === 'swiper-container' || tag === 'swiper-slide',
//         },
//
//         transformAssetUrls: {
//           base: null,
//           includeAbsolute: false,
//         },
//       },
//     }),
//     laravel({
//       input: ['resources/js/main.js'],
//       refresh: true,
//     }),
//     vueJsx(), // Docs: https://github.com/vuetifyjs/vuetify-loader/tree/master/packages/vite-plugin
//     vuetify({
//       styles: {
//         configFile: 'resources/styles/variables/_vuetify.scss',
//       },
//     }), // Docs: https://github.com/dishait/vite-plugin-vue-meta-layouts?tab=readme-ov-file
//     MetaLayouts({
//       target: './resources/js/layouts',
//       defaultLayout: 'default',
//     }), // Docs: https://github.com/antfu/unplugin-vue-components#unplugin-vue-components
//     Components({
//       dirs: ['resources/js/@core/components', 'resources/js/views/demos', 'resources/js/components'],
//       dts: true,
//       resolvers: [
//         componentName => {
//         // Auto import `VueApexCharts`
//           if (componentName === 'VueApexCharts')
//             return { name: 'default', from: 'vue3-apexcharts', as: 'VueApexCharts' }
//         },
//       ],
//     }), // Docs: https://github.com/antfu/unplugin-auto-import#unplugin-auto-import
//     AutoImport({
//       imports: ['vue', VueRouterAutoImports, '@vueuse/core', '@vueuse/math', 'vue-i18n', 'pinia'],
//       dirs: [
//         './resources/js/@core/utils',
//         './resources/js/@core/composable/',
//         './resources/js/composables/',
//         './resources/js/utils/',
//         './resources/js/plugins/*/composables/*',
//       ],
//       vueTemplate: true,
//
//       // ℹ️ Disabled to avoid confusion & accidental usage
//       ignore: ['useCookies', 'useStorage'],
//       eslintrc: {
//         enabled: true,
//         filepath: './.eslintrc-auto-import.json',
//       },
//     }), // Docs: https://github.com/intlify/bundle-tools/tree/main/packages/unplugin-vue-i18n#intlifyunplugin-vue-i18n
//     VueI18nPlugin({
//       runtimeOnly: true,
//       compositionOnly: true,
//       include: [
//         fileURLToPath(new URL('./resources/js/plugins/i18n/locales/**', import.meta.url)),
//       ],
//     }),
//     svgLoader(),
//   ],
//   define: { 'process.env': {} },
//   resolve: {
//     alias: {
//       '@core-scss': fileURLToPath(new URL('./resources/styles/@core', import.meta.url)),
//       '@': fileURLToPath(new URL('./resources/js', import.meta.url)),
//       '@themeConfig': fileURLToPath(new URL('./themeConfig.js', import.meta.url)),
//       '@core': fileURLToPath(new URL('./resources/js/@core', import.meta.url)),
//       '@layouts': fileURLToPath(new URL('./resources/js/@layouts', import.meta.url)),
//       '@images': fileURLToPath(new URL('./resources/images/', import.meta.url)),
//       '@styles': fileURLToPath(new URL('./resources/styles/', import.meta.url)),
//       '@configured-variables': fileURLToPath(new URL('./resources/styles/variables/_template.scss', import.meta.url)),
//       '@db': fileURLToPath(new URL('./resources/js/plugins/fake-api/handlers/', import.meta.url)),
//       '@api-utils': fileURLToPath(new URL('./resources/js/plugins/fake-api/utils/', import.meta.url)),
//     },
//   },
//   build: {
//     chunkSizeWarningLimit: 5000,
//   },
//   optimizeDeps: {
//     exclude: ['vuetify'],
//     entries: [
//       './resources/js/**/*.vue',
//     ],
//   },
// })
import { defineConfig } from 'vite'
import laravel from 'laravel-vite-plugin'
import vue from '@vitejs/plugin-vue'
import vueJsx from '@vitejs/plugin-vue-jsx'
import path from 'path'
import { fileURLToPath, URL } from 'node:url'

// Plugins adicionales
import VueRouter from 'unplugin-vue-router/vite'
import MetaLayouts from 'vite-plugin-vue-meta-layouts'
import AutoImport from 'unplugin-auto-import/vite'
import Components from 'unplugin-vue-components/vite'
import vuetify from 'vite-plugin-vuetify'
import VueI18nPlugin from '@intlify/unplugin-vue-i18n/vite'
import svgLoader from 'vite-svg-loader'
import VueDevTools from 'vite-plugin-vue-devtools'

export default defineConfig({
  plugins: [
    // ⚙️ Router antes de vue
    // VueRouter({
    //   routesFolder: 'resources/js/pages',
    //   extensions: ['.vue'],
    //   getRouteName: routeNode => routeNode.name ?? routeNode.path,
    // }),
    VueRouter({
      routesFolder: 'resources/js/pages',
      extensions: ['.vue'],

      // ✅ FIX: evita recursión y respeta nombres automáticos

      getRouteName: routeNode => {
        if (routeNode?.value?.path) {
          return routeNode.value.path
            .replace(/\//g, '-')
            .replace(/^-|-$/g, '')
            .replace(/\[|\]/g, '')
            .toLowerCase()
        }

        return 'route-' + Math.random().toString(36).substring(2, 8)
      },
    }),

    vue({
      template: {
        compilerOptions: {
          isCustomElement: tag =>
            tag === 'swiper-container' || tag === 'swiper-slide',
        },
      },
    }),

    vueJsx(),
    VueDevTools(),

    laravel({
      input: ['resources/js/main.js'],
      refresh: true,
    }),

    vuetify({
      styles: { configFile: 'resources/styles/variables/_vuetify.scss' },
    }),

    MetaLayouts({
      target: './resources/js/layouts',
      defaultLayout: 'default',
    }),

    AutoImport({
      imports: ['vue', 'vue-router', '@vueuse/core', 'pinia', 'vue-i18n'],
      dirs: [
        'resources/js/@core/utils',
        'resources/js/@core/composable',
        'resources/js/composables',
        'resources/js/utils',
        'resources/js/plugins/*/composables/*',
      ],
      vueTemplate: true,
      eslintrc: { enabled: true, filepath: './.eslintrc-auto-import.json' },
    }),

    Components({
      dirs: [
        'resources/js/@core/components',
        'resources/js/views/demos',
        'resources/js/components',
      ],
      dts: true,
    }),

    VueI18nPlugin({
      runtimeOnly: true,
      compositionOnly: true,
      include: fileURLToPath(
        new URL('./resources/js/plugins/i18n/locales/**', import.meta.url),
      ),
    }),

    svgLoader(),
  ],

  resolve: {
    alias: {
      '@': path.resolve(__dirname, 'resources/js'),
      '@core': path.resolve(__dirname, 'resources/js/@core'),
      '@layouts': path.resolve(__dirname, 'resources/js/@layouts'),
      '@images': path.resolve(__dirname, 'resources/images'),
      '@styles': path.resolve(__dirname, 'resources/styles'),
      '@themeConfig': path.resolve(__dirname, 'themeConfig.js'),
      '@core-scss': path.resolve(__dirname, 'resources/styles/@core'),
      '@configured-variables': path.resolve(
        __dirname,
        'resources/styles/variables/_template.scss',
      ),
      '@db': path.resolve(
        __dirname,
        'resources/js/plugins/fake-api/handlers/',
      ),
      '@api-utils': path.resolve(
        __dirname,
        'resources/js/plugins/fake-api/utils/',
      ),
    },
  },

  define: {
    'process.env': {},
  },

  build: {
    chunkSizeWarningLimit: 5000,
  },

  optimizeDeps: {
    exclude: ['vuetify'],
    entries: ['./resources/js/**/*.vue'],
  },
})

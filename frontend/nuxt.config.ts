// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
  ssr: false,
  compatibilityDate: '2024-11-01',
  devtools: { enabled: false },
  modules: [
    '@pinia/nuxt'
  ],
  app: {
    head: {
      htmlAttrs: { lang: 'bn' },
      title: 'গ্রাম বাংলা রিয়েল এস্টেট | জমি, প্লট ও ফ্ল্যাট — GBREL',
      meta: [
        { charset: 'utf-8' },
        { name: 'viewport', content: 'width=device-width, initial-scale=1, viewport-fit=cover' },
        { name: 'description', content: 'জমি, প্লট, জমি শেয়ার ও ফ্ল্যাট খুঁজুন। দাম, কাগজপত্র ও লোকেশন দেখে নিন, তারপর গ্রাম বাংলা রিয়েল এস্টেট টিমের সঙ্গে কথা বলুন।' },
        { name: 'theme-color', content: '#1D4A2A' }
      ],
      link: [
        { rel: 'icon', type: 'image/png', href: '/favicon.png' },
        { rel: 'preconnect', href: 'https://fonts.googleapis.com' },
        { rel: 'preconnect', href: 'https://fonts.gstatic.com', crossorigin: '' },
        { rel: 'stylesheet', href: 'https://fonts.googleapis.com/css2?family=Anek+Bangla:wdth,wght@75..125,300..800&family=Noto+Sans+Bengali:wght@400;500;600;700&family=Plus+Jakarta+Sans:ital,wght@0,300..800;1,300..800&family=Outfit:wght@400;500;600;700;800;900&display=swap' },
        { rel: 'stylesheet', href: 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css' }
      ]
    }
  },
  css: [
    '~/assets/css/main.css',
    '~/assets/css/components.css',
    '~/assets/css/animations.css',
    '~/assets/css/admin.css',
    '~/assets/css/site.css'
  ],
  runtimeConfig: {
    public: {
      apiBase: process.env.NUXT_PUBLIC_API_BASE || 'http://127.0.0.1:8000/api',
      metaPixelId: process.env.NUXT_PUBLIC_META_PIXEL_ID || ''
    }
  },
  nitro: {
    routeRules: {
      '/api/**': { proxy: 'http://127.0.0.1:8000/api/**' },
      '/storage/**': { proxy: 'http://127.0.0.1:8000/storage/**' }
    }
  }
})

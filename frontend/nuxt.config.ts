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
      title: 'GBREL | Luxury Real Estate & Land in Bangladesh',
      meta: [
        { charset: 'utf-8' },
        { name: 'viewport', content: 'width=device-width, initial-scale=1, viewport-fit=cover' },
        { name: 'description', content: 'Explore verified luxury flats, prime residential plots, beachside resorts, and commercial lands across Dhaka, Chittagong, Sylhet, and Cox\'s Bazar. 100% RAJUK & CDA verified properties.' },
        { name: 'theme-color', content: '#0A1128' }
      ],
      link: [
        { rel: 'preconnect', href: 'https://fonts.googleapis.com' },
        { rel: 'preconnect', href: 'https://fonts.gstatic.com', crossorigin: '' },
        { rel: 'stylesheet', href: 'https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300..800;1,300..800&family=Outfit:wght@400;500;600;700;800;900&display=swap' },
        { rel: 'stylesheet', href: 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css' }
      ]
    }
  },
  css: [
    '~/assets/css/main.css',
    '~/assets/css/components.css',
    '~/assets/css/animations.css'
  ],
  runtimeConfig: {
    public: {
      apiBase: process.env.NUXT_PUBLIC_API_BASE || '/api'
    }
  }
})

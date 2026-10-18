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
      title: 'Gram Bangla Real Estate Limited | Gram Bangla Real Estate — GBREL',
      meta: [
        { charset: 'utf-8' },
        { name: 'viewport', content: 'width=device-width, initial-scale=1, viewport-fit=cover' },
        { name: 'description', content: 'Gram Bangla Real Estate Limited (GBREL) is a premier registered real estate company in Bangladesh. Official site: www.gbrel.com. Discover verified land, plots, land shares, and apartments in Dhaka. Call: +8809658510748 | মিরপুর ডিওএইচএস, ঢাকা।' },
        { name: 'keywords', content: 'Gram Bangla Real Estate Limited, Gram Bangla Real Estate, Gram Bangla, GBREL, www.gbrel.com, gbrel, গ্রাম বাংলা রিয়েল এস্টেট লিমিটেড, গ্রাম বাংলা রিয়েল এস্টেট, গ্রাম বাংলা, Real Estate Bangladesh, Mirpur DOHS Real Estate, Plots in Dhaka, Land Share Dhaka' },
        { name: 'author', content: 'Gram Bangla Real Estate Limited' },
        { name: 'robots', content: 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1' },
        { name: 'theme-color', content: '#1D4A2A' },
        // Site-wide share preview. Property pages get their own from server/plugins/share-meta.ts.
        { property: 'og:type', content: 'website' },
        { property: 'og:site_name', content: 'Gram Bangla Real Estate Limited' },
        { property: 'og:locale', content: 'bn_BD' },
        { property: 'og:title', content: 'Gram Bangla Real Estate Limited | জমি দেখে, কাগজ বুঝে, তারপর কিনুন — GBREL' },
        { property: 'og:description', content: 'Gram Bangla Real Estate Limited (GBREL) — জমি, প্লট, জমি শেয়ার ও ফ্ল্যাট খুঁজুন। দাম, কাগজপত্র ও লোকেশন দেখে নিন। Official: www.gbrel.com | ফোন: +8809658510748' },
        { property: 'og:url', content: 'https://www.gbrel.com/' },
        { property: 'og:image', content: 'https://www.gbrel.com/og-default.jpg' },
        { property: 'og:image:width', content: '1200' },
        { property: 'og:image:height', content: '630' },
        { name: 'twitter:card', content: 'summary_large_image' },
        { name: 'twitter:title', content: 'Gram Bangla Real Estate Limited | Gram Bangla Real Estate — GBREL' },
        { name: 'twitter:description', content: 'Gram Bangla Real Estate Limited (GBREL) — জমি, প্লট, জমি শেয়ার ও ফ্ল্যাট। www.gbrel.com | ফোন: +8809658510748' },
        { name: 'twitter:image', content: 'https://www.gbrel.com/og-default.jpg' }
      ],
      // Visitor journey tracker. Waits for cookie consent by itself; /gb-track.js?v= is bumped when the file changes.
      script: [{ src: '/gb-track.js?v=3', defer: true }],
      link: [
        { rel: 'canonical', href: 'https://www.gbrel.com/' },
        { rel: 'icon', type: 'image/png', href: '/favicon.png' },
        { rel: 'preconnect', href: 'https://fonts.googleapis.com' },
        { rel: 'preconnect', href: 'https://fonts.gstatic.com', crossorigin: '' },
        {
          rel: 'stylesheet',
          href: 'https://fonts.googleapis.com/css2?family=Anek+Bangla:wght@400;600;700&family=Noto+Sans+Bengali:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@400;600;700&family=Outfit:wght@500;600;700&display=swap',
          media: 'print',
          onload: "this.media='all'"
        }
      ],
      noscript: [
        { children: '<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Anek+Bangla:wght@400;600;700&family=Noto+Sans+Bengali:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@400;600;700&family=Outfit:wght@500;600;700&display=swap">' }
      ]
    }
  },
  experimental: {
    defaults: {
      nuxtLink: {
        prefetch: false,
        prefetchOn: {
          visibility: false,
          interaction: true
        }
      }
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
    // Server-only: where the Nitro server reaches the Laravel API (docker-compose sets NUXT_API_BASE_SERVER).
    apiBaseServer: process.env.NUXT_API_BASE_SERVER || 'http://127.0.0.1:8000/api',
    public: {
      apiBase: process.env.NUXT_PUBLIC_API_BASE || 'http://127.0.0.1:8000/api',
      metaPixelId: process.env.NUXT_PUBLIC_META_PIXEL_ID || ''
    }
  },
  vite: {
    server: {
      proxy: {
        '/storage': {
          target: (process.env.NUXT_API_BASE_SERVER || process.env.NUXT_PUBLIC_API_BASE || 'http://127.0.0.1:8000').replace(/\/api\/?$/, '').replace(/\/$/, '') || 'http://127.0.0.1:8000',
          changeOrigin: true
        }
      }
    }
  },
  nitro: {
    compressPublicAssets: true,
    routeRules: {
      '/agents': { redirect: { to: '/advisors', statusCode: 301 } },
      '/agents/**': { redirect: { to: '/advisors/**', statusCode: 301 } },
      '/admin/agents': { redirect: { to: '/admin/advisors', statusCode: 301 } },
      '/api/**': { proxy: `${(process.env.NUXT_API_BASE_SERVER || process.env.NUXT_PUBLIC_API_BASE || 'http://127.0.0.1:8000').replace(/\/api\/?$/, '').replace(/\/$/, '') || 'http://127.0.0.1:8000'}/api/**` },
      '/storage/**': { proxy: `${(process.env.NUXT_API_BASE_SERVER || process.env.NUXT_PUBLIC_API_BASE || 'http://127.0.0.1:8000').replace(/\/api\/?$/, '').replace(/\/$/, '') || 'http://127.0.0.1:8000'}/storage/**` }
    }
  }
})

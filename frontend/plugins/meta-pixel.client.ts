// Loads the tracking tools chosen in Admin → Settings → Tracking & analytics:
//   Meta (Facebook) Pixel, Google Tag Manager and Google Analytics 4.
// Nothing loads on /admin pages, so staff visits never reach ad audiences or reports.
// NUXT_PUBLIC_META_PIXEL_ID still works as a fallback for the Pixel when the setting is empty.
import { useSettings } from '~/composables/useSettings'

const PIXEL = /^\d{10,20}$/
const GTM = /^GTM-[A-Z0-9]{4,12}$/
const GA4 = /^G-[A-Z0-9]{4,15}$/

const addScript = (src: string) => {
  const script = document.createElement('script')
  script.async = true
  script.src = src
  document.head.appendChild(script)
}

export default defineNuxtPlugin(async () => {
  const router = useRouter()
  const isPublic = (path: string) => !path.startsWith('/admin')
  const { settings, fetchSettings } = useSettings()
  await fetchSettings().catch(() => null)

  const pixelId = String(settings.value.meta_pixel_id || useRuntimeConfig().public.metaPixelId || '').trim()
  const gtmId = String(settings.value.gtm_container_id || '').trim().toUpperCase()
  const ga4Id = String(settings.value.ga4_measurement_id || '').trim().toUpperCase()
  const w = window as any
  let started = false

  const start = () => {
    if (started) return
    started = true
    if (PIXEL.test(pixelId) && !w.fbq) {
      const n: any = (w.fbq = function (...args: unknown[]) { n.callMethod ? n.callMethod(...args) : n.queue.push(args) })
      if (!w._fbq) w._fbq = n
      n.push = n; n.loaded = true; n.version = '2.0'; n.queue = []
      addScript('https://connect.facebook.net/en_US/fbevents.js')
      w.fbq('init', pixelId)
      w.__gbrelPixelId = pixelId
    }
    if (GTM.test(gtmId)) {
      w.dataLayer = w.dataLayer || []
      w.dataLayer.push({ 'gtm.start': Date.now(), event: 'gtm.js' })
      addScript(`https://www.googletagmanager.com/gtm.js?id=${gtmId}`)
    }
    if (GA4.test(ga4Id)) {
      w.dataLayer = w.dataLayer || []
      w.gtag = w.gtag || function () { w.dataLayer.push(arguments) }
      w.gtag('js', new Date())
      // Page views are sent by hand below, because this is a single-page app.
      w.gtag('config', ga4Id, { send_page_view: false })
      addScript(`https://www.googletagmanager.com/gtag/js?id=${ga4Id}`)
    }
  }

  const isTrackingPermitted = () => {
    try {
      return localStorage.getItem('gbrel_cookie_consent') !== 'essential'
    } catch {
      return true
    }
  }

  const pageView = (path: string) => {
    if (!isPublic(path) || !isTrackingPermitted()) return
    start()
    if (typeof w.fbq === 'function') w.fbq('track', 'PageView')
    if (typeof w.gtag === 'function' && GA4.test(ga4Id)) w.gtag('event', 'page_view', { page_path: path, page_location: window.location.href, page_title: document.title })
    if (GTM.test(gtmId)) w.dataLayer.push({ event: 'gbrel_page_view', page_path: path })
  }

  // If the user accepts cookies later via the banner, trigger tracking immediately
  if (typeof window !== 'undefined') {
    window.addEventListener('gbrel:cookie-consent', ((e: CustomEvent) => {
      if (e.detail === 'all') {
        pageView(router.currentRoute.value.fullPath)
      }
    }) as EventListener)
  }

  // Wait a tick so the page title is set before the first page view.
  setTimeout(() => pageView(router.currentRoute.value.fullPath), 0)
  router.afterEach((to, from) => {
    if (to.fullPath !== from.fullPath) setTimeout(() => pageView(to.fullPath), 0)
  })
})

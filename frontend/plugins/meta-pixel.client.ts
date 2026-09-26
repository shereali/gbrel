// Loads the Meta Pixel when NUXT_PUBLIC_META_PIXEL_ID is set, and sends a PageView on every route change.
// Admin pages are skipped so staff activity does not pollute ad audiences.
export default defineNuxtPlugin(() => {
  const pixelId = String(useRuntimeConfig().public.metaPixelId || '').trim()
  if (!pixelId || !/^\d+$/.test(pixelId)) return

  const w = window as any
  if (!w.fbq) {
    const n: any = (w.fbq = function (...args: unknown[]) { n.callMethod ? n.callMethod(...args) : n.queue.push(args) })
    if (!w._fbq) w._fbq = n
    n.push = n; n.loaded = true; n.version = '2.0'; n.queue = []
    const script = document.createElement('script')
    script.async = true
    script.src = 'https://connect.facebook.net/en_US/fbevents.js'
    document.head.appendChild(script)
  }
  w.fbq('init', pixelId)

  const router = useRouter()
  const isPublic = (path: string) => !path.startsWith('/admin')
  if (isPublic(router.currentRoute.value.path)) w.fbq('track', 'PageView')
  router.afterEach((to, from) => {
    if (to.fullPath !== from.fullPath && isPublic(to.path)) w.fbq('track', 'PageView')
  })
})

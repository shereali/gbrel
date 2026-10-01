// Visitor journey tracking (public/gb-track.js, loaded from nuxt.config.ts) and Microsoft Clarity
// (session recordings and heatmaps). Both run only after the visitor chose "সব গ্রহণ করুন" in the cookie
// banner, and never on admin or sign-in pages. The Clarity project ID comes from Admin → Settings → Tracking.
import { useSettings } from '~/composables/useSettings'

const CLARITY = /^[a-z0-9]{8,12}$/i
// Clarity masks typed text, but staff and sign-in pages should not be recorded at all.
const isRecordable = (path: string) => !/^\/(admin|login|register|signup|reset-password|dashboard|my-listings)(\/|$)/.test(path)

export default defineNuxtPlugin(async () => {
  const w = window as any
  const router = useRouter()
  const { settings, fetchSettings } = useSettings()

  // Tells the tracker where the API is (same base the site uses for POST /leads).
  w.GB_TRACK_API = String(useRuntimeConfig().public.apiBase || '/api')

  await fetchSettings().catch(() => null)
  const projectId = String(settings.value.clarity_project_id || '').trim()
  let loaded = false

  const loadClarity = () => {
    if (loaded || !CLARITY.test(projectId) || !isRecordable(router.currentRoute.value.path)) return
    try {
      if (localStorage.getItem('gbrel_cookie_consent') !== 'all') return
    } catch {
      return
    }
    loaded = true
    w.clarity = w.clarity || function (...args: unknown[]) { (w.clarity.q = w.clarity.q || []).push(args) }
    const script = document.createElement('script')
    script.async = true
    script.src = `https://www.clarity.ms/tag/${projectId}`
    document.head.appendChild(script)
    // Lets a recording be matched to the visitor's journey in the admin. The tracker sets gb_vid on start.
    setTimeout(() => {
      const vid = document.cookie.match(/(?:^|; )gb_vid=([^;]*)/)
      if (vid) w.clarity('identify', decodeURIComponent(vid[1]))
    }, 500)
  }

  loadClarity()
  window.addEventListener('gbrel:cookie-consent', () => setTimeout(loadClarity, 0))
  // Pages change without a reload, so a visitor who accepts on a public page and then moves on is still covered.
  router.afterEach(() => loadClarity())
})

import { computed, ref } from 'vue'

export type CookieConsentLevel = 'all' | 'essential'

const consent = ref<CookieConsentLevel | null>(null)
let initialized = false

export function useCookieConsent() {
  const init = () => {
    if (initialized || typeof window === 'undefined') return
    try {
      const saved = localStorage.getItem('gbrel_cookie_consent') as CookieConsentLevel | null
      if (saved === 'all' || saved === 'essential') {
        consent.value = saved
      }
    } catch {
      // storage unavailable
    }
    initialized = true
  }

  const setConsent = (level: CookieConsentLevel) => {
    consent.value = level
    try {
      localStorage.setItem('gbrel_cookie_consent', level)
      window.dispatchEvent(new CustomEvent('gbrel:cookie-consent', { detail: level }))
    } catch {
      // storage unavailable
    }
  }

  const hasConsented = computed(() => consent.value !== null)
  const isMarketingAllowed = computed(() => consent.value === 'all')
  const isAnalyticsAllowed = computed(() => consent.value === 'all')

  return {
    consent,
    hasConsented,
    isMarketingAllowed,
    isAnalyticsAllowed,
    init,
    setConsent
  }
}

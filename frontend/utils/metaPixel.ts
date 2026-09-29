// One call sends a conversion event to every tracking tool that is switched on:
// Meta Pixel (standard events), Google Analytics 4 (recommended event names) and GTM (dataLayer).
// Safe to call when none of them are loaded.
const standardEvents = new Set(['PageView', 'ViewContent', 'Lead', 'Contact', 'Schedule', 'CompleteRegistration', 'Search'])
const ga4Names: Record<string, string> = {
  ViewContent: 'view_item',
  Lead: 'generate_lead',
  Contact: 'contact',
  Schedule: 'schedule_visit',
  CompleteRegistration: 'sign_up',
  Search: 'search'
}

export const trackPixel = (event: string, params: Record<string, unknown> = {}, eventID?: string) => {
  try {
    if (typeof window !== 'undefined' && localStorage.getItem('gbrel_cookie_consent') === 'essential') {
      return
    }
    const w = window as any
    if (typeof w.fbq === 'function') {
      const method = standardEvents.has(event) ? 'track' : 'trackCustom'
      if (eventID) w.fbq(method, event, params, { eventID })
      else w.fbq(method, event, params)
    }
    const gaName = ga4Names[event] || event.replace(/([a-z])([A-Z])/g, '$1_$2').toLowerCase()
    if (typeof w.gtag === 'function') w.gtag('event', gaName, { ...params, ...(eventID ? { event_id: eventID } : {}) })
    if (Array.isArray(w.dataLayer)) w.dataLayer.push({ event: `gbrel_${gaName}`, ...params, ...(eventID ? { event_id: eventID } : {}) })
  } catch { /* Analytics must never block a visitor. */ }
}

// Advanced matching: re-initialise the pixel with the lead's phone and first name right before the Lead
// event, so Meta can match the lead to a Facebook account. The pixel hashes these values (SHA-256) itself.
export const setPixelUserData = (phoneE164: string, fullName: string) => {
  try {
    if (typeof window !== 'undefined' && localStorage.getItem('gbrel_cookie_consent') === 'essential') return
    const w = window as any
    const pixelId = w.__gbrelPixelId
    if (typeof w.fbq !== 'function' || !pixelId) return
    const ph = String(phoneE164 || '').replace(/\D/g, '')
    const fn = String(fullName || '').trim().split(/\s+/)[0]?.toLowerCase() || ''
    const data: Record<string, string> = {}
    if (ph) data.ph = ph
    if (fn) data.fn = fn
    if (Object.keys(data).length) w.fbq('init', pixelId, data)
  } catch { /* Analytics must never block a visitor. */ }
}

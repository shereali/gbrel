// Thin wrapper around the Meta (Facebook) Pixel. Safe to call when the pixel is not loaded.
const standardEvents = new Set(['PageView', 'ViewContent', 'Lead', 'Contact', 'Schedule', 'CompleteRegistration', 'Search'])

export const trackPixel = (event: string, params: Record<string, unknown> = {}, eventID?: string) => {
  try {
    const fbq = (window as any).fbq
    if (typeof fbq !== 'function') return
    const method = standardEvents.has(event) ? 'track' : 'trackCustom'
    if (eventID) fbq(method, event, params, { eventID })
    else fbq(method, event, params)
  } catch { /* Analytics must never block a visitor. */ }
}

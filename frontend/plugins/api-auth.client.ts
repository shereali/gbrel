// Adds the signed-in user's token to every request made to the GBREL API, so pages don't each have to.
// The API rejects writes without it.
export default defineNuxtPlugin(() => {
  const apiBase = String(useRuntimeConfig().public.apiBase || '/api').replace(/\/$/, '')
  const apiOrigin = (() => { try { return new URL(apiBase, window.location.origin).href.replace(/\/$/, '') } catch { return '' } })()
  const originalFetch = window.fetch.bind(window)

  const readToken = (): string => {
    try {
      const cookie = document.cookie.split('; ').find(part => part.startsWith('gbrel_token='))
      const fromCookie = cookie ? decodeURIComponent(cookie.slice('gbrel_token='.length)).replace(/^"|"$/g, '') : ''
      return fromCookie && fromCookie !== 'null' ? fromCookie : (localStorage.getItem('gbrel_token') || '')
    } catch {
      return ''
    }
  }

  window.fetch = (input: RequestInfo | URL, init: RequestInit = {}) => {
    const url = typeof input === 'string' ? input : input instanceof URL ? input.href : input.url
    const absolute = (() => { try { return new URL(url, window.location.origin).href } catch { return url } })()
    const isApiCall = (apiOrigin && absolute.startsWith(apiOrigin)) || url.startsWith('/api/')
    const token = isApiCall ? readToken() : ''

    if (token) {
      const headers = new Headers(init.headers || (input instanceof Request ? input.headers : undefined))
      if (!headers.has('Authorization')) headers.set('Authorization', `Bearer ${token}`)
      if (!headers.has('Accept')) headers.set('Accept', 'application/json')
      return originalFetch(input, { ...init, headers })
    }
    return originalFetch(input, init)
  }
})

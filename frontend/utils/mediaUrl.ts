// Resolves media paths (such as `/storage/...` uploads) into accessible URLs.
// In development, if the browser is on a different origin/port than the Laravel backend API,
// this prefixes storage paths with the backend base URL.
// In production or same-origin environments, relative paths are preserved for reverse proxying.

export function resolveMediaUrl(url?: string | null): string {
  if (!url || typeof url !== 'string') return ''
  const trimmed = url.trim()
  if (!trimmed) return ''

  // Absolute external URLs or data/blob URIs remain unchanged
  if (/^(?:https?:\/\/|data:|blob:)/i.test(trimmed)) {
    return trimmed
  }

  // Frontend static assets
  if (trimmed.startsWith('/img/') || trimmed.startsWith('/docs/') || trimmed.startsWith('/favicon.')) {
    return trimmed
  }

  const isStoragePath = trimmed.startsWith('/storage/') || trimmed.startsWith('storage/')
  if (isStoragePath) {
    const normalized = trimmed.startsWith('/') ? trimmed : `/${trimmed}`

    if (typeof window !== 'undefined') {
      try {
        const config = useRuntimeConfig()
        const apiBase = String(config.public?.apiBase || '').trim()
        if (apiBase.startsWith('http://') || apiBase.startsWith('https://')) {
          const apiOrigin = new URL(apiBase).origin
          if (apiOrigin && apiOrigin !== window.location.origin) {
            return `${apiOrigin}${normalized}`
          }
        }
      } catch {
        // In case useRuntimeConfig is called outside Nuxt context
      }

      // If running on local dev server and port differs from standard backend 8000
      if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
        if (window.location.port !== '8000') {
          return `http://${window.location.hostname}:8000${normalized}`
        }
      }
    } else {
      // Server-side rendering (SSR)
      try {
        const config = useRuntimeConfig()
        const serverBase = String(config.apiBaseServer || config.public?.apiBase || '').trim()
        if (serverBase.startsWith('http://') || serverBase.startsWith('https://')) {
          const serverOrigin = new URL(serverBase).origin
          if (serverOrigin) {
            return `${serverOrigin}${normalized}`
          }
        }
      } catch {
        // Fallback for SSR
      }
    }

    return normalized
  }

  return trimmed
}

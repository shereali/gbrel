// Staff media library: photos and videos reused across listings (Admin → Media library).
import { useApiUrl } from '~/composables/useApi'

export interface MediaUsage { id: number; title: string; as: 'photo' | 'video' | 'video cover' }
export interface MediaItem {
  id: number
  type: 'image' | 'video'
  source: 'upload' | 'youtube' | 'facebook' | 'vimeo' | 'link'
  url: string
  thumbnail_url: string | null
  title: string | null
  mime_type: string | null
  size: number | null
  created_at: string | null
  used_in: MediaUsage[]
}
export interface MediaCounts { all: number; image: number; video: number }

const authHeader = (): Record<string, string> => {
  if (!process.client) return {}
  try {
    const cookie = document.cookie.split('; ').find(part => part.startsWith('gbrel_token='))
    const fromCookie = cookie ? decodeURIComponent(cookie.slice('gbrel_token='.length)).replace(/^"|"$/g, '') : ''
    const token = fromCookie && fromCookie !== 'null' ? fromCookie : (localStorage.getItem('gbrel_token') || '')
    return token ? { Authorization: `Bearer ${token}` } : {}
  } catch { return {} }
}

async function request<T>(path: string, init: RequestInit = {}): Promise<T> {
  const res = await fetch(useApiUrl(path), { ...init, headers: { Accept: 'application/json', ...(init.body && !(init.body instanceof FormData) ? { 'Content-Type': 'application/json' } : {}), ...authHeader(), ...(init.headers || {}) } })
  const body = await res.json().catch(() => null)
  if (!res.ok || body?.success === false) {
    const first = body?.errors ? Object.values(body.errors).flat()[0] : null
    const err: any = new Error(String(first || body?.message || `Request failed (${res.status})`))
    err.status = res.status
    err.body = body
    throw err
  }
  return body as T
}

export const useMediaLibrary = () => {
  const list = (params: { type?: string; q?: string } = {}) => {
    const qs = new URLSearchParams(Object.entries(params).filter(([, v]) => v) as [string, string][]).toString()
    return request<{ data: MediaItem[]; counts: MediaCounts; limits: { image_mb: number; video_mb: number } }>(`/admin/media${qs ? `?${qs}` : ''}`)
  }

  // XHR so the library can show upload progress for large videos.
  const upload = (file: File, onProgress?: (fraction: number) => void) => new Promise<MediaItem>((resolve, reject) => {
    const form = new FormData()
    form.append('file', file)
    const xhr = new XMLHttpRequest()
    xhr.open('POST', useApiUrl('/admin/media'))
    xhr.setRequestHeader('Accept', 'application/json')
    const auth = authHeader().Authorization
    if (auth) xhr.setRequestHeader('Authorization', auth)
    xhr.upload.onprogress = e => { if (e.lengthComputable) onProgress?.(e.loaded / e.total) }
    xhr.onload = () => {
      let body: any = null
      try { body = JSON.parse(xhr.responseText) } catch {}
      if (xhr.status >= 200 && xhr.status < 300 && body?.data) return resolve(body.data)
      const first = body?.errors ? Object.values(body.errors).flat()[0] : null
      reject(new Error(String(first || body?.message || (xhr.status === 413 ? 'The file is larger than the server accepts.' : `Upload failed (${xhr.status})`))))
    }
    xhr.onerror = () => reject(new Error('Upload stopped. Check the connection and try again.'))
    xhr.send(form)
  })

  const addLink = (url: string, title?: string) => request<{ data: MediaItem }>('/admin/media/link', { method: 'POST', body: JSON.stringify({ url, title }) }).then(r => r.data)
  const update = (id: number, changes: { title?: string | null; thumbnail_url?: string | null }) => request<{ data: MediaItem }>(`/admin/media/${id}`, { method: 'PATCH', body: JSON.stringify(changes) }).then(r => r.data)
  const remove = (id: number) => request<{ success: boolean }>(`/admin/media/${id}`, { method: 'DELETE' })

  return { list, upload, addLink, update, remove }
}

export const formatBytes = (bytes?: number | null) => {
  if (!bytes) return ''
  if (bytes >= 1048576) return `${(bytes / 1048576).toFixed(1)} MB`
  return `${Math.max(1, Math.round(bytes / 1024))} KB`
}

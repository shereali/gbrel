// Mirrors App\Support\VideoLink: only our own uploads or YouTube / Vimeo / Facebook links are ever embedded.
export interface VideoSource {
  kind: 'file' | 'youtube' | 'vimeo' | 'facebook'
  src: string // <video> src or iframe src (autoplay once the viewer taps play)
  thumbnail?: string
}

export function parseVideo(url?: string | null): VideoSource | null {
  const value = (url || '').trim()
  if (!value) return null
  if (/^\/storage\/media\/[\w\-./]+\.(mp4|webm|mov|m4v)$/i.test(value)) return { kind: 'file', src: value }
  if (!/^https:\/\//i.test(value)) return null
  const yt = value.match(/^https:\/\/(?:www\.|m\.)?(?:youtube\.com\/(?:watch\?(?:.*&)?v=|shorts\/|embed\/|live\/)|youtu\.be\/)([\w-]{11})/i)
  if (yt) return { kind: 'youtube', src: `https://www.youtube-nocookie.com/embed/${yt[1]}?autoplay=1&rel=0&playsinline=1`, thumbnail: `https://i.ytimg.com/vi/${yt[1]}/hqdefault.jpg` }
  const vimeo = value.match(/^https:\/\/(?:www\.)?vimeo\.com\/(\d{6,12})/i)
  if (vimeo) return { kind: 'vimeo', src: `https://player.vimeo.com/video/${vimeo[1]}?autoplay=1` }
  if (/^https:\/\/(?:www\.|m\.|web\.)?(?:facebook\.com|fb\.watch)\//i.test(value)) {
    return { kind: 'facebook', src: `https://www.facebook.com/plugins/video.php?href=${encodeURIComponent(value)}&show_text=false&autoplay=true` }
  }
  return null
}

export const videoProviderLabel: Record<VideoSource['kind'], string> = { file: 'Uploaded video', youtube: 'YouTube', vimeo: 'Vimeo', facebook: 'Facebook' }

// Mirrors App\Support\VideoLink: only our own uploads or YouTube / Vimeo / Facebook links are ever embedded.
export interface VideoSource {
  kind: 'file' | 'youtube' | 'vimeo' | 'facebook'
  src: string // <video> src or iframe src
  id?: string
  thumbnail?: string
}

export interface EmbedOptions {
  /** Start muted: browsers only allow autoplay without a tap when the sound is off. */
  muted?: boolean
}

// YouTube cannot switch suggestions off completely, so the player loops the same video (no end screen of other
// videos), shows only this video's channel when paused (rel=0) and hides annotations.
const youtubeSrc = (id: string, { muted }: EmbedOptions) => {
  const params = new URLSearchParams({
    autoplay: '1', mute: muted ? '1' : '0', loop: '1', playlist: id, rel: '0', playsinline: '1',
    iv_load_policy: '3', modestbranding: '1', enablejsapi: '1'
  })
  if (typeof window !== 'undefined') params.set('origin', window.location.origin)
  return `https://www.youtube-nocookie.com/embed/${id}?${params}`
}

export function youtubeId(url?: string | null): string | null {
  const match = (url || '').trim().match(/^https:\/\/(?:www\.|m\.)?(?:youtube\.com\/(?:watch\?(?:.*&)?v=|shorts\/|embed\/|live\/)|youtu\.be\/)([\w-]{11})/i)
  return match ? match[1] : null
}

export function parseVideo(url?: string | null, options: EmbedOptions = {}): VideoSource | null {
  const value = (url || '').trim()
  if (!value) return null
  // Our own uploads only: /storage/media/2026/09/<name>.mp4, no ".." or other folders.
  if (/^\/storage\/media\/(?:[\w-]+\/)*[\w-]+\.(mp4|webm|mov|m4v)$/i.test(value)) return { kind: 'file', src: value }
  if (!/^https:\/\//i.test(value)) return null
  const yt = youtubeId(value)
  if (yt) return { kind: 'youtube', id: yt, src: youtubeSrc(yt, options), thumbnail: `https://i.ytimg.com/vi/${yt}/hqdefault.jpg` }
  const vimeo = value.match(/^https:\/\/(?:www\.)?vimeo\.com\/(\d{6,12})/i)
  if (vimeo) return { kind: 'vimeo', id: vimeo[1], src: `https://player.vimeo.com/video/${vimeo[1]}?autoplay=1&loop=1&playsinline=1${options.muted ? '&muted=1' : ''}` }
  if (/^https:\/\/(?:www\.|m\.|web\.)?(?:facebook\.com|fb\.watch)\//i.test(value)) {
    return { kind: 'facebook', src: `https://www.facebook.com/plugins/video.php?href=${encodeURIComponent(value)}&show_text=false&autoplay=true${options.muted ? '&mute=true' : ''}` }
  }
  return null
}

/** Turns the sound on in an embedded player that was started muted. */
export function unmuteEmbed(frame: HTMLIFrameElement | null, kind: VideoSource['kind']) {
  const target = frame?.contentWindow
  if (!target) return
  // Messages go only to the player's own origin, never to whatever page the frame might have navigated to.
  if (kind === 'youtube') {
    const origin = 'https://www.youtube-nocookie.com'
    target.postMessage(JSON.stringify({ event: 'command', func: 'unMute', args: [] }), origin)
    target.postMessage(JSON.stringify({ event: 'command', func: 'setVolume', args: [100] }), origin)
    target.postMessage(JSON.stringify({ event: 'command', func: 'playVideo', args: [] }), origin)
  } else if (kind === 'vimeo') {
    const origin = 'https://player.vimeo.com'
    target.postMessage(JSON.stringify({ method: 'setVolume', value: 1 }), origin)
    target.postMessage(JSON.stringify({ method: 'setMuted', value: false }), origin)
  }
}

export const videoProviderLabel: Record<VideoSource['kind'], string> = { file: 'Uploaded video', youtube: 'YouTube', vimeo: 'Vimeo', facebook: 'Facebook' }

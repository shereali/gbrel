<template>
  <div class="ml" :class="[`ml--${mode}`, { 'ml--dragging': dragging }]" @dragenter.prevent="onDragEnter" @dragover.prevent @dragleave.prevent="onDragLeave" @drop.prevent="onDrop">
    <div class="ml-bar">
      <div class="ml-tabs" role="tablist" aria-label="Media type">
        <button v-for="tab in visibleTabs" :key="tab.key" type="button" role="tab" :aria-selected="filter === tab.key" :class="{ on: filter === tab.key }" @click="filter = tab.key">
          {{ tab.label }} <span>{{ counts[tab.key] ?? 0 }}</span>
        </button>
      </div>
      <label class="ml-search">
        <span class="sr-only">Search by name</span>
        <Search :size="16" aria-hidden="true" />
        <input v-model.trim="query" type="search" placeholder="Search by name" @input="scheduleLoad" />
      </label>
      <div class="ml-actions">
        <button v-if="accept !== 'image'" type="button" class="ml-btn ml-btn--ghost" :aria-expanded="linkOpen" @click="linkOpen = !linkOpen"><Youtube :size="16" aria-hidden="true" /> Add YouTube video</button>
        <button type="button" class="ml-btn ml-btn--solid" @click="fileInput?.click()"><Upload :size="16" aria-hidden="true" /> Upload</button>
        <input ref="fileInput" type="file" multiple :accept="acceptAttr" hidden @change="onFilesChosen" />
      </div>
    </div>

    <form v-if="linkOpen" class="ml-link" @submit.prevent="submitLink">
      <label for="ml-link-url">YouTube link <span class="ml-link-note">(Facebook and Vimeo links work too)</span></label>
      <div>
        <input id="ml-link-url" ref="linkInput" v-model.trim="linkUrl" type="url" inputmode="url" placeholder="https://www.youtube.com/watch?v=…  or  https://youtu.be/…" required />
        <button type="submit" class="ml-btn ml-btn--solid" :disabled="linkSaving">{{ linkSaving ? 'Adding…' : 'Add video' }}</button>
      </div>
      <p>Long walkthroughs work best on YouTube: they load faster for buyers on mobile data.</p>
    </form>

    <ul v-if="uploads.length" class="ml-queue" aria-live="polite">
      <li v-for="u in uploads" :key="u.key" :class="{ failed: u.error }">
        <span class="ml-queue-name">{{ u.name }}</span>
        <span v-if="u.error" class="ml-queue-err">{{ u.error }}</span>
        <span v-else class="ml-queue-bar"><span :style="{ width: Math.round(u.progress * 100) + '%' }"></span></span>
        <button v-if="u.error" type="button" aria-label="Dismiss" @click="dismissUpload(u.key)"><X :size="14" /></button>
      </li>
    </ul>

    <p v-if="error" class="ml-error" role="alert">{{ error }}</p>

    <div class="ml-body">
      <div class="ml-grid-wrap">
        <p v-if="loading && !items.length" class="ml-empty">Loading media…</p>
        <div v-else-if="!items.length" class="ml-empty">
          <ImagePlus :size="34" aria-hidden="true" />
          <strong>{{ query ? 'Nothing matches that name.' : emptyTitle }}</strong>
          <span>Drop photos or videos here, or use Upload. Photos up to {{ limits.image_mb }} MB, videos up to {{ limits.video_mb }} MB.</span>
        </div>
        <ul v-else class="ml-grid">
          <li v-for="item in items" :key="item.id">
            <button type="button" class="ml-tile" :class="{ selected: isSelected(item), active: activeId === item.id }" :aria-pressed="mode === 'pick' ? isSelected(item) : undefined" @click="onTile(item)">
              <span class="ml-thumb">
                <img v-if="thumbOf(item)" :src="thumbOf(item)!" :alt="''" loading="lazy" />
                <video v-else-if="item.type === 'video' && item.source === 'upload'" :src="item.url + '#t=0.5'" preload="metadata" muted playsinline></video>
                <span v-else class="ml-thumb-blank"><Film :size="26" aria-hidden="true" /></span>
                <span v-if="item.type === 'video'" class="ml-play" aria-hidden="true"><Play :size="16" /></span>
                <span v-if="mode === 'pick' && isSelected(item)" class="ml-check" aria-hidden="true"><Check :size="16" /></span>
              </span>
              <span class="ml-name">{{ item.title || 'Untitled' }}</span>
              <span class="ml-meta">{{ metaLine(item) }}</span>
            </button>
          </li>
        </ul>
        <div v-if="hasMore" class="ml-more">
          <button type="button" class="ml-btn ml-btn--ghost" :disabled="loadingMore" @click="loadMore">{{ loadingMore ? 'Loading…' : `Show more (${toShow} more)` }}</button>
        </div>
      </div>

      <aside v-if="mode === 'manage' && active" class="ml-detail" aria-label="Selected media">
        <div class="ml-detail-head">
          <strong>{{ active.type === 'video' ? 'Video' : 'Photo' }}</strong>
          <button type="button" class="ml-icon" aria-label="Close details" @click="activeId = null"><X :size="18" /></button>
        </div>
        <div class="ml-preview">
          <video v-if="active.type === 'video' && active.source === 'upload'" :src="active.url" controls preload="metadata" playsinline></video>
          <img v-else-if="thumbOf(active)" :src="thumbOf(active)!" alt="" />
          <span v-else class="ml-thumb-blank"><Film :size="30" aria-hidden="true" /></span>
        </div>
        <label class="ml-field">
          <span>Name</span>
          <input v-model="titleDraft" maxlength="200" @keydown.enter.prevent="($event.target as HTMLInputElement).blur()" @blur="saveTitle" />
        </label>
        <div class="ml-field">
          <span>Link</span>
          <div class="ml-copy"><code>{{ active.url }}</code><button type="button" class="ml-btn ml-btn--ghost" @click="copyUrl">{{ copied ? 'Copied' : 'Copy' }}</button></div>
        </div>
        <dl class="ml-facts">
          <div><dt>Source</dt><dd>{{ sourceLabel(active) }}</dd></div>
          <div v-if="active.size"><dt>Size</dt><dd>{{ formatBytes(active.size) }}</dd></div>
          <div v-if="active.created_at"><dt>Added</dt><dd>{{ new Date(active.created_at).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' }) }}</dd></div>
        </dl>
        <div class="ml-usage">
          <span>Used in</span>
          <ul v-if="active.used_in.length">
            <li v-for="u in active.used_in" :key="`${u.kind || 'property'}-${u.id}-${u.as}`"><NuxtLink v-if="u.kind !== 'media'" :to="`/admin/properties/${u.id}/edit`">{{ u.title }}</NuxtLink><span v-else>{{ u.title || 'Library video' }}</span><small>as {{ u.as }}</small></li>
          </ul>
          <p v-else>Not used anywhere yet.</p>
        </div>
        <div v-if="confirmDelete" class="ml-confirm" role="alert">
          <p>Delete “{{ active.title || 'this file' }}” for good? {{ active.source === 'upload' ? 'The file is removed from the server.' : 'The link is removed from the library.' }}</p>
          <div>
            <button type="button" class="ml-btn ml-btn--danger" :disabled="deleting" @click="deleteActive"><Trash2 :size="16" aria-hidden="true" /> {{ deleting ? 'Deleting…' : 'Yes, delete' }}</button>
            <button ref="cancelDeleteBtn" type="button" class="ml-btn ml-btn--ghost" :disabled="deleting" @click="confirmDelete = false">Keep it</button>
          </div>
        </div>
        <button v-else type="button" class="ml-btn ml-btn--danger" :disabled="active.used_in.length > 0" @click="askDelete">
          <Trash2 :size="16" aria-hidden="true" /> Delete
        </button>
        <p v-if="active.used_in.length" class="ml-hint">Remove it from the places above before deleting.</p>
      </aside>
    </div>

    <div v-if="mode === 'pick'" class="ml-pickbar">
      <span>{{ selection.length ? `${selection.length} selected` : pickHint }}</span>
      <button type="button" class="ml-btn ml-btn--solid" :disabled="!selection.length" @click="$emit('pick', selection)">{{ pickLabel }}</button>
    </div>

    <div v-if="dragging" class="ml-drop" aria-hidden="true"><Upload :size="28" /> Drop to upload</div>
  </div>
</template>

<script setup lang="ts">
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue'
import { Check, Film, ImagePlus, Play, Search, Trash2, Upload, X, Youtube } from 'lucide-vue-next'
import { formatBytes, useMediaLibrary, type MediaCounts, type MediaItem } from '~/composables/useMediaLibrary'
import { parseVideo, videoProviderLabel } from '~/utils/videoEmbed'

const props = withDefaults(defineProps<{
  mode?: 'manage' | 'pick'
  accept?: 'any' | 'image' | 'video'
  multiple?: boolean
  pickLabel?: string
}>(), { mode: 'manage', accept: 'any', multiple: false, pickLabel: 'Use selected' })
defineEmits<{ pick: [items: MediaItem[]] }>()

const { list, upload, addLink, update, remove } = useMediaLibrary()

type Filter = 'all' | 'image' | 'video'
const filter = ref<Filter>(props.accept === 'any' ? 'all' : props.accept)
const query = ref('')
const items = ref<MediaItem[]>([])
const counts = ref<MediaCounts>({ all: 0, image: 0, video: 0 })
const limits = ref({ image_mb: 10, video_mb: 100 })
const loading = ref(false)
const loadingMore = ref(false)
const page = ref(1)
const lastPage = ref(1)
const total = ref(0)
const confirmDelete = ref(false)
const cancelDeleteBtn = ref<HTMLButtonElement | null>(null)
const error = ref('')
const selection = ref<MediaItem[]>([])
const activeId = ref<number | null>(null)
const titleDraft = ref('')
const copied = ref(false)
const deleting = ref(false)
const linkOpen = ref(false)
const linkUrl = ref('')
const linkSaving = ref(false)
const linkInput = ref<HTMLInputElement | null>(null)
const fileInput = ref<HTMLInputElement | null>(null)
const dragging = ref(false)
let dragDepth = 0
const uploads = ref<{ key: string; name: string; progress: number; error: string }[]>([])

const visibleTabs = computed(() => ([
  { key: 'all', label: 'All' }, { key: 'image', label: 'Photos' }, { key: 'video', label: 'Videos' }
] as { key: Filter; label: string }[]).filter(t => props.accept === 'any' || t.key === props.accept))
const acceptAttr = computed(() => props.accept === 'image' ? 'image/jpeg,image/png,image/webp' : props.accept === 'video' ? 'video/mp4,video/webm,video/quicktime' : 'image/jpeg,image/png,image/webp,video/mp4,video/webm,video/quicktime')
const active = computed(() => items.value.find(i => i.id === activeId.value) || null)
const emptyTitle = computed(() => filter.value === 'video' ? 'No videos yet.' : filter.value === 'image' ? 'No photos yet.' : 'The library is empty.')
const pickHint = computed(() => props.accept === 'video' ? 'Choose one video' : props.multiple ? 'Choose one or more photos' : 'Choose a photo')

watch(active, item => { titleDraft.value = item?.title || ''; copied.value = false; confirmDelete.value = false })
watch(filter, () => load())

let timer: ReturnType<typeof setTimeout> | undefined
const scheduleLoad = () => { clearTimeout(timer); timer = setTimeout(load, 250) }
// Each request gets a number; a slower, older response (e.g. from the previous search) is ignored.
let requestSeq = 0
const hasMore = computed(() => page.value < lastPage.value)
const toShow = computed(() => Math.max(0, total.value - items.value.length))
const params = (p: number) => ({ type: filter.value === 'all' ? '' : filter.value, q: query.value, page: p })

async function load() {
  const seq = ++requestSeq
  loading.value = true
  error.value = ''
  try {
    const res = await list(params(1))
    if (seq !== requestSeq) return
    items.value = res.data
    page.value = res.meta?.page || 1
    lastPage.value = res.meta?.last_page || 1
    total.value = res.meta?.total ?? res.data.length
    counts.value = res.counts
    limits.value = res.limits
  } catch (e: any) {
    if (seq === requestSeq) error.value = e.message || 'The media library could not be loaded.'
  } finally {
    if (seq === requestSeq) loading.value = false
  }
}
async function loadMore() {
  const seq = requestSeq
  loadingMore.value = true
  try {
    const res = await list(params(page.value + 1))
    if (seq !== requestSeq) return
    const seen = new Set(items.value.map(i => i.id))
    items.value = [...items.value, ...res.data.filter(i => !seen.has(i.id))]
    page.value = res.meta?.page || page.value + 1
    lastPage.value = res.meta?.last_page || page.value
  } catch (e: any) {
    error.value = e.message || 'More items could not be loaded.'
  } finally {
    loadingMore.value = false
  }
}
onMounted(load)
// Stop pending work when the library closes (e.g. the picker dialog): no late search, no late auto-select.
let unmounted = false
onBeforeUnmount(() => { unmounted = true; clearTimeout(timer); requestSeq++ })

const thumbOf = (item: MediaItem) => item.type === 'image' ? item.url : (item.thumbnail_url || parseVideo(item.url)?.thumbnail || null)
const sourceLabel = (item: MediaItem) => item.source === 'upload' ? (item.type === 'video' ? 'Uploaded video' : 'Uploaded photo') : item.source === 'link' ? 'Web link' : videoProviderLabel[item.source as 'youtube']
const metaLine = (item: MediaItem) => {
  const where = item.used_in.length ? `In ${item.used_in.length} listing${item.used_in.length > 1 ? 's' : ''}` : 'Not used yet'
  return item.type === 'video' && item.source !== 'upload' ? `${videoProviderLabel[item.source as 'youtube'] || 'Link'} · ${where}` : where
}

const isSelected = (item: MediaItem) => selection.value.some(s => s.id === item.id)
function onTile(item: MediaItem) {
  if (props.mode === 'manage') { activeId.value = activeId.value === item.id ? null : item.id; return }
  if (props.accept !== 'any' && item.type !== props.accept) return
  if (isSelected(item)) selection.value = selection.value.filter(s => s.id !== item.id)
  else selection.value = props.multiple ? [...selection.value, item] : [item]
}

// Some systems report no type for .mov files, so the extension is checked too.
const kindOf = (file: File): 'image' | 'video' | '' => {
  if (file.type.startsWith('video/') || /\.(mp4|webm|mov|m4v)$/i.test(file.name)) return 'video'
  if (file.type.startsWith('image/') || /\.(jpe?g|png|webp)$/i.test(file.name)) return 'image'
  return ''
}
function accepts(file: File) {
  const isVideo = kindOf(file) === 'video'
  const isImage = kindOf(file) === 'image'
  if (props.accept === 'image') return isImage
  if (props.accept === 'video') return isVideo
  return isImage || isVideo
}

async function uploadFiles(files: File[]) {
  const chosen = files.filter(accepts)
  if (!chosen.length) { error.value = props.accept === 'video' ? 'Choose an MP4, WebM or MOV video.' : props.accept === 'image' ? 'Choose a JPG, PNG or WebP photo.' : 'Choose photos (JPG, PNG, WebP) or videos (MP4, WebM, MOV).'; return }
  error.value = ''
  for (const file of chosen) {
    const key = `${file.name}-${file.size}-${Date.now()}`
    const limitMb = kindOf(file) === 'video' ? limits.value.video_mb : limits.value.image_mb
    uploads.value.push({ key, name: file.name, progress: 0, error: '' })
    const entry = () => uploads.value.find(u => u.key === key)!
    if (file.size > limitMb * 1048576) { entry().error = `Larger than ${limitMb} MB`; continue }
    try {
      let item = await upload(file, p => { entry().progress = p })
      if (item.type === 'video') item = await attachVideoCover(item, file)
      items.value = [item, ...items.value.filter(i => i.id !== item.id)]
      counts.value = { ...counts.value, all: counts.value.all + 1, [item.type]: counts.value[item.type] + 1 }
      uploads.value = uploads.value.filter(u => u.key !== key)
      total.value += 1
      if (!unmounted && props.mode === 'pick' && (props.accept === 'any' || props.accept === item.type)) onTile(item)
    } catch (e: any) {
      entry().error = e.message || 'Upload failed'
    }
  }
}
// Browsers can grab a frame from the uploaded video; it becomes the video's cover so tiles and listings
// have something to show before anyone presses play. Any failure just leaves the video without a cover.
async function captureFrame(file: File): Promise<File | null> {
  return new Promise(resolve => {
    const url = URL.createObjectURL(file)
    const v = document.createElement('video')
    const done = (result: File | null) => { v.removeAttribute('src'); v.load(); URL.revokeObjectURL(url); resolve(result) }
    const timer = setTimeout(() => done(null), 8000)
    v.muted = true
    v.playsInline = true
    v.preload = 'auto'
    v.src = url
    v.onloadedmetadata = () => { v.currentTime = Math.min(1, (v.duration || 2) / 3) }
    v.onseeked = () => {
      clearTimeout(timer)
      const width = Math.min(1600, v.videoWidth || 1280)
      const height = Math.round(width * ((v.videoHeight || 720) / (v.videoWidth || 1280)))
      const canvas = document.createElement('canvas')
      canvas.width = width
      canvas.height = height
      canvas.getContext('2d')?.drawImage(v, 0, 0, width, height)
      canvas.toBlob(blob => done(blob ? new File([blob], file.name.replace(/\.[^.]+$/, '') + ' - video cover.jpg', { type: 'image/jpeg' }) : null), 'image/jpeg', 0.85)
    }
    v.onerror = () => { clearTimeout(timer); done(null) }
  })
}
async function attachVideoCover(item: MediaItem, file: File): Promise<MediaItem> {
  try {
    const frame = await captureFrame(file)
    if (!frame) return item
    const cover = await upload(frame)
    counts.value = { ...counts.value, all: counts.value.all + 1, image: counts.value.image + 1 }
    if (filter.value !== 'video') { items.value = [cover, ...items.value]; total.value += 1 }
    return await update(item.id, { thumbnail_url: cover.url })
  } catch {
    return item
  }
}
const dismissUpload = (key: string) => { uploads.value = uploads.value.filter(u => u.key !== key) }
function onFilesChosen(e: Event) {
  const input = e.target as HTMLInputElement
  uploadFiles(Array.from(input.files || []))
  input.value = ''
}
const onDragEnter = (e: DragEvent) => { if (e.dataTransfer?.types.includes('Files')) { dragDepth++; dragging.value = true } }
const onDragLeave = () => { dragDepth = Math.max(0, dragDepth - 1); if (!dragDepth) dragging.value = false }
function onDrop(e: DragEvent) { dragDepth = 0; dragging.value = false; uploadFiles(Array.from(e.dataTransfer?.files || [])) }

watch(linkOpen, open => { if (open) nextTick(() => linkInput.value?.focus()) })
async function submitLink() {
  if (!linkUrl.value) return
  linkSaving.value = true
  error.value = ''
  try {
    const item = await addLink(linkUrl.value)
    items.value = [item, ...items.value.filter(i => i.id !== item.id)]
    await load()
    linkUrl.value = ''
    linkOpen.value = false
    if (props.mode === 'pick') onTile(items.value.find(i => i.id === item.id) || item)
    else activeId.value = item.id
  } catch (e: any) {
    error.value = e.message
  } finally {
    linkSaving.value = false
  }
}

async function saveTitle() {
  if (!active.value || titleDraft.value === (active.value.title || '')) return
  try {
    const updated = await update(active.value.id, { title: titleDraft.value })
    items.value = items.value.map(i => i.id === updated.id ? updated : i)
  } catch (e: any) { error.value = e.message }
}
async function copyUrl() {
  if (!active.value) return
  const full = active.value.url.startsWith('/') ? window.location.origin + active.value.url : active.value.url
  try { await navigator.clipboard.writeText(full); copied.value = true } catch { copied.value = false }
}
function askDelete() {
  confirmDelete.value = true
  nextTick(() => cancelDeleteBtn.value?.focus())
}
async function deleteActive() {
  if (!active.value) return
  deleting.value = true
  try {
    const id = active.value.id
    const type = active.value.type
    await remove(id)
    items.value = items.value.filter(i => i.id !== id)
    counts.value = { ...counts.value, all: counts.value.all - 1, [type]: counts.value[type] - 1 }
    total.value = Math.max(0, total.value - 1)
    confirmDelete.value = false
    activeId.value = null
  } catch (e: any) {
    error.value = e.message
  } finally {
    deleting.value = false
  }
}

defineExpose({ reload: load })
</script>

<style scoped>
.ml-more { display: flex; justify-content: center; padding: 16px 0 4px; }
.ml-confirm { border: 1.5px solid #E5B4A6; background: #FDF1EE; border-radius: 10px; padding: 12px; display: grid; gap: 10px; }
.ml-confirm p { margin: 0; font-size: .88rem; line-height: 1.5; }
.ml-confirm div { display: flex; gap: 8px; flex-wrap: wrap; }
.ml { position: relative; display: flex; flex-direction: column; gap: 14px; min-height: 0; color: var(--admin-text-primary); }
.ml-bar { display: flex; flex-wrap: wrap; align-items: center; gap: 12px; }
.ml-tabs { display: inline-flex; background: var(--admin-bg-surface-alt, #EEF2E9); border-radius: 999px; padding: 4px; }
.ml-tabs button { border: 0; background: none; padding: 7px 14px; border-radius: 999px; font: inherit; font-weight: 600; font-size: .86rem; color: var(--admin-text-secondary, #4A5A4E); cursor: pointer; }
.ml-tabs button span { font-weight: 500; opacity: .7; margin-left: 4px; }
.ml-tabs button.on { background: #1D4A2A; color: #fff; }
.ml-search { flex: 1 1 200px; display: flex; align-items: center; gap: 8px; border: 1px solid var(--admin-border-hover, #CBD5BE); background: var(--admin-bg-surface, #fff); border-radius: 10px; padding: 0 12px; color: var(--admin-text-muted); }
.ml-search input { border: 0; outline: 0; background: none; font: inherit; min-height: 40px; width: 100%; color: var(--admin-text-primary); }
.ml-search:focus-within { border-color: #3F7A35; box-shadow: 0 0 0 3px rgba(63, 122, 53, .18); }
.ml-actions { display: flex; gap: 8px; margin-left: auto; }
.ml-btn { display: inline-flex; align-items: center; gap: 6px; min-height: 40px; padding: 0 14px; border-radius: 10px; font: inherit; font-weight: 700; font-size: .86rem; cursor: pointer; border: 1px solid transparent; }
.ml-btn:disabled { opacity: .5; cursor: not-allowed; }
.ml-btn--solid { background: #1D4A2A; color: #fff; }
.ml-btn--solid:hover:not(:disabled) { background: #133520; }
.ml-btn--ghost { background: transparent; color: #1D4A2A; border-color: var(--admin-border-hover, #CBD5BE); }
.ml-btn--danger { background: transparent; color: #A0361B; border-color: #E9C4B4; width: 100%; justify-content: center; }
.ml-link { display: grid; gap: 6px; padding: 14px; border-radius: 12px; background: var(--admin-bg-surface-alt, #EEF2E9); }
.ml-link label { font-weight: 700; font-size: .85rem; }
.ml-link-note { font-weight: 500; color: var(--admin-text-muted); }
.ml-link > div { display: flex; gap: 8px; }
.ml-link input { flex: 1; min-height: 40px; border-radius: 10px; border: 1px solid var(--admin-border-hover, #CBD5BE); padding: 0 12px; font: inherit; background: var(--admin-bg-surface, #fff); color: var(--admin-text-primary); }
.ml-link p { font-size: .78rem; color: var(--admin-text-muted); margin: 0; }
.ml-queue { list-style: none; margin: 0; padding: 0; display: grid; gap: 6px; }
.ml-queue li { display: grid; grid-template-columns: minmax(0, 1fr) minmax(120px, 2fr) auto; align-items: center; gap: 10px; font-size: .82rem; }
.ml-queue-name { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.ml-queue-bar { height: 6px; border-radius: 3px; background: var(--admin-bg-surface-alt, #EEF2E9); overflow: hidden; }
.ml-queue-bar span { display: block; height: 100%; background: #3F7A35; transition: width .2s; }
.ml-queue-err { color: #A0361B; font-weight: 600; }
.ml-queue button { border: 0; background: none; cursor: pointer; color: var(--admin-text-muted); }
.ml-error { margin: 0; padding: 10px 12px; border-radius: 8px; background: #FBE9DF; color: #8A3A0F; font-size: .86rem; }
.ml-body { display: grid; grid-template-columns: minmax(0, 1fr); gap: 18px; align-items: start; }
.ml--manage .ml-body:has(.ml-detail) { grid-template-columns: minmax(0, 1fr) 320px; }
.ml-grid { list-style: none; margin: 0; padding: 0; display: grid; grid-template-columns: repeat(auto-fill, minmax(168px, 1fr)); gap: 14px; }
.ml-tile { width: 100%; display: flex; flex-direction: column; gap: 4px; padding: 0 0 10px; text-align: left; background: var(--admin-bg-surface, #fff); border: 1px solid var(--admin-border-subtle, #D6DDCB); border-radius: 12px; overflow: hidden; cursor: pointer; font: inherit; color: inherit; }
.ml-tile:hover { border-color: #7FA85A; }
.ml-tile.active, .ml-tile.selected { border-color: #1D4A2A; box-shadow: 0 0 0 2px #1D4A2A; }
.ml-thumb { position: relative; display: block; aspect-ratio: 4 / 3; background: #E4EBDA; overflow: hidden; }
.ml-thumb img, .ml-thumb video { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; display: block; }
.ml-thumb-blank { display: grid; place-items: center; width: 100%; height: 100%; color: #5C6F5F; }
.ml-play { position: absolute; left: 10px; bottom: 10px; display: grid; place-items: center; width: 34px; height: 34px; border-radius: 50%; background: #E2651C; color: #fff; box-shadow: 0 2px 8px rgba(0, 0, 0, .25); }
.ml-check { position: absolute; top: 8px; right: 8px; display: grid; place-items: center; width: 28px; height: 28px; border-radius: 50%; background: #1D4A2A; color: #fff; }
.ml-name { padding: 6px 12px 0; font-weight: 600; font-size: .86rem; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.ml-meta { padding: 0 12px; font-size: .75rem; color: var(--admin-text-muted); }
.ml-empty { display: grid; justify-items: center; gap: 8px; padding: 56px 20px; text-align: center; border: 2px dashed var(--admin-border-hover, #CBD5BE); border-radius: 14px; color: var(--admin-text-muted); }
.ml-empty strong { color: var(--admin-text-primary); font-size: 1rem; }
.ml-detail { position: sticky; top: 90px; display: flex; flex-direction: column; gap: 14px; padding: 16px; border-radius: 14px; background: var(--admin-bg-surface, #fff); border: 1px solid var(--admin-border-subtle, #D6DDCB); }
.ml-detail-head { display: flex; justify-content: space-between; align-items: center; }
.ml-icon { border: 0; background: none; cursor: pointer; color: var(--admin-text-muted); padding: 4px; }
.ml-preview { border-radius: 10px; overflow: hidden; background: #E4EBDA; aspect-ratio: 4 / 3; }
.ml-preview img, .ml-preview video { width: 100%; height: 100%; object-fit: contain; background: #16241A; display: block; }
.ml-field { display: grid; gap: 6px; font-size: .8rem; font-weight: 700; color: var(--admin-text-secondary, #4A5A4E); }
.ml-field input { min-height: 40px; border-radius: 10px; border: 1px solid var(--admin-border-hover, #CBD5BE); padding: 0 12px; font: inherit; font-weight: 500; background: var(--admin-bg-surface, #fff); color: var(--admin-text-primary); }
.ml-copy { display: flex; gap: 8px; align-items: center; }
.ml-copy code { flex: 1; min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-size: .75rem; font-weight: 500; background: var(--admin-bg-surface-alt, #EEF2E9); padding: 9px 10px; border-radius: 8px; }
.ml-facts { display: grid; gap: 4px; margin: 0; font-size: .82rem; }
.ml-facts div { display: flex; justify-content: space-between; gap: 12px; }
.ml-facts dt { color: var(--admin-text-muted); }
.ml-facts dd { margin: 0; font-weight: 600; }
.ml-usage { font-size: .84rem; display: grid; gap: 6px; }
.ml-usage > span { font-weight: 700; color: var(--admin-text-secondary, #4A5A4E); font-size: .8rem; }
.ml-usage ul { list-style: none; margin: 0; padding: 0; display: grid; gap: 6px; }
.ml-usage li { display: flex; flex-direction: column; }
.ml-usage a { color: #1D4A2A; font-weight: 600; text-decoration: underline; }
.ml-usage small, .ml-usage p { color: var(--admin-text-muted); margin: 0; }
.ml-hint { margin: -6px 0 0; font-size: .76rem; color: var(--admin-text-muted); }
.ml-pickbar { position: sticky; bottom: 0; display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 12px 0 0; border-top: 1px solid var(--admin-border-subtle, #D6DDCB); background: var(--admin-bg-surface, #fff); font-weight: 600; }
.ml-drop { position: absolute; inset: 0; z-index: 5; display: flex; align-items: center; justify-content: center; gap: 10px; border: 3px dashed #3F7A35; border-radius: 16px; background: rgba(243, 245, 236, .92); color: #1D4A2A; font-weight: 800; font-size: 1.1rem; pointer-events: none; }
.ml :focus-visible { outline: 3px solid #E2651C; outline-offset: 2px; }
.sr-only { position: absolute; width: 1px; height: 1px; overflow: hidden; clip: rect(0 0 0 0); }
@media (max-width: 900px) {
  .ml--manage .ml-body:has(.ml-detail) { grid-template-columns: minmax(0, 1fr); }
  .ml-detail { position: static; order: -1; }
  .ml-actions { margin-left: 0; width: 100%; }
  .ml-actions .ml-btn { flex: 1; justify-content: center; }
  .ml-grid { grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); gap: 10px; }
}
</style>

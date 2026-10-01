<template>
  <div class="av">
    <div class="av-photo" aria-hidden="true">
      <img v-if="shown" :src="shown" alt="" />
      <span v-else>{{ initials }}</span>
    </div>
    <div class="av-body">
      <div class="av-actions">
        <label class="btn btn-sm btn-outline-white av-choose">
          <input ref="input" type="file" accept="image/jpeg,image/png,image/webp" class="av-input" @change="onPick" />
          {{ shown ? 'Change photo' : 'Upload photo' }}
        </label>
        <button v-if="shown" type="button" class="av-remove" @click="clear">Remove</button>
      </div>
      <p v-if="error" class="av-error" role="alert">{{ error }}</p>
      <p v-else class="av-help">JPG, PNG or WebP, up to 5 MB. A square photo of the face works best.</p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, onBeforeUnmount, ref, watch } from 'vue'

const MAX_BYTES = 5 * 1024 * 1024
const TYPES = ['image/jpeg', 'image/png', 'image/webp']

// current: the photo saved on the account. file: a newly chosen photo, uploaded when the form is saved.
// removed: the saved photo should be deleted when the form is saved.
const props = defineProps<{ current?: string | null; name?: string }>()
const file = defineModel<File | null>('file', { default: null })
const removed = defineModel<boolean>('removed', { default: false })

const input = ref<HTMLInputElement | null>(null)
const preview = ref('')
const error = ref('')

// Older accounts were given a stock photo of a stranger; that is not a real photo of the person.
const savedPhoto = computed(() => (props.current && !props.current.includes('images.unsplash.com') ? props.current : ''))
const shown = computed(() => preview.value || (removed.value ? '' : savedPhoto.value))
const initials = computed(() => {
  const parts = String(props.name || '').trim().split(/\s+/).filter(Boolean)
  return ((parts[0]?.[0] || '') + (parts.length > 1 ? parts[parts.length - 1][0] : '')).toUpperCase() || '?'
})

const revoke = () => { if (preview.value) URL.revokeObjectURL(preview.value); preview.value = '' }
watch(file, f => { if (!f) revoke() })
onBeforeUnmount(revoke)

const onPick = (event: Event) => {
  const picked = (event.target as HTMLInputElement).files?.[0]
  if (!picked) return
  error.value = ''
  if (!TYPES.includes(picked.type)) { error.value = 'Choose a JPG, PNG or WebP photo.'; reset(); return }
  if (picked.size > MAX_BYTES) { error.value = 'The photo is larger than 5 MB. Choose a smaller one.'; reset(); return }
  revoke()
  preview.value = URL.createObjectURL(picked)
  file.value = picked
  removed.value = false
}

const clear = () => {
  error.value = ''
  file.value = null
  revoke()
  removed.value = !!savedPhoto.value
  reset()
}
const reset = () => { if (input.value) input.value.value = '' }
</script>

<style scoped>
.av { display: flex; align-items: center; gap: 16px; margin-bottom: 18px; }
.av-photo { flex: none; width: 76px; height: 76px; border-radius: 50%; overflow: hidden; display: grid; place-items: center; background: var(--admin-bg-surface-elevated); border: 1px solid var(--admin-border-hover); color: var(--admin-text-secondary); font-size: 1.5rem; font-weight: 800; }
.av-photo img { width: 100%; height: 100%; object-fit: cover; display: block; }
.av-actions { display: flex; flex-wrap: wrap; align-items: center; gap: 10px; }
.av-choose { position: relative; cursor: pointer; }
.av-choose:focus-within { outline: 2px solid var(--admin-text-gold); outline-offset: 2px; }
.av-input { position: absolute; inset: 0; opacity: 0; cursor: pointer; width: 100%; }
.av-remove { border: 0; background: transparent; color: var(--admin-text-muted); font: inherit; font-size: 0.84rem; font-weight: 600; text-decoration: underline; text-underline-offset: 3px; cursor: pointer; min-height: 32px; }
.av-remove:hover { color: var(--admin-text-primary); }
.av-help, .av-error { margin: 6px 0 0; font-size: 0.78rem; line-height: 1.4; color: var(--admin-text-muted); }
.av-error { color: #DC2626; font-weight: 600; }
</style>

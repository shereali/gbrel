<template>
  <div class="om" :class="{ 'om--tall': tall }">
    <iframe :src="mapEmbedUrl(location)" :title="`${address} — Google ম্যাপ`" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>
    <div class="om-card">
      <MapPin :size="20" aria-hidden="true" />
      <div>
        <strong>আমাদের অফিস</strong>
        <p>{{ address }}</p>
        <div class="om-links">
          <a :href="mapDirectionsUrl(location)" target="_blank" rel="noopener noreferrer" class="om-go">পথ দেখুন</a>
          <a :href="mapOpenUrl(location)" target="_blank" rel="noopener noreferrer">Google Maps-এ খুলুন</a>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { MapPin } from 'lucide-vue-next'
import { mapDirectionsUrl, mapEmbedUrl, mapOpenUrl } from '~/utils/officeMap'

defineProps<{ address: string; location: string; tall?: boolean }>()
</script>

<style scoped>
.om { position: relative; border-radius: var(--gb-r-lg); overflow: hidden; border: 1px solid var(--gb-silt); background: #E4EBDA; min-height: 340px; }
.om--tall { min-height: 440px; }
.om iframe { position: absolute; inset: 0; width: 100%; height: 100%; border: 0; }
.om-card { position: absolute; left: 16px; bottom: 16px; right: 16px; max-width: 420px; display: flex; gap: 12px; padding: 16px 18px; border-radius: var(--gb-r); background: var(--gb-sheet); box-shadow: 0 10px 30px -12px rgba(22, 36, 26, .45); }
.om-card svg { color: var(--gb-sun); flex-shrink: 0; margin-top: 2px; }
.om-card strong { font-family: var(--gb-display); font-size: 1.05rem; color: var(--gb-paddy); }
.om-card p { margin: 2px 0 10px; color: var(--gb-ink); line-height: 1.55; }
.om-links { display: flex; flex-wrap: wrap; gap: 8px 16px; align-items: center; }
.om-links a { color: var(--gb-leaf); font-weight: 600; text-decoration: underline; text-underline-offset: 3px; }
.om-links .om-go { text-decoration: none; background: var(--gb-paddy); color: #fff; padding: 8px 14px; border-radius: 999px; }
.om a:focus-visible { outline: 3px solid var(--gb-sun); outline-offset: 2px; }
@media (max-width: 640px) { .om, .om--tall { min-height: 420px; } .om-card { left: 10px; right: 10px; bottom: 10px; } }
</style>

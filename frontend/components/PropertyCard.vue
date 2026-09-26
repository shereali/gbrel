<template>
  <article class="pc" :class="{ 'pc--wide': wide }">
    <div class="pc-media">
      <img v-if="image && !imageBroken" :src="image" :alt="property.title" loading="lazy" decoding="async" @error="imageBroken = true" />
      <div v-else class="pc-noimg" aria-hidden="true">
        <svg viewBox="0 0 120 60"><path d="M0 60 C30 30 60 36 120 20 V60Z" /><path d="M0 60 C40 44 80 48 120 38 V60Z" /></svg>
      </div>
      <span class="pc-type">{{ typeLabel(property.propertyType) }}</span>
      <div class="pc-tools">
        <button type="button" :aria-pressed="isInCompare(property.id)" :aria-label="isInCompare(property.id) ? 'তুলনা থেকে সরান' : 'তুলনায় যোগ করুন'" :title="isInCompare(property.id) ? 'তুলনা থেকে সরান' : 'তুলনায় যোগ করুন'" @click.stop="toggleCompare(property.id)">
          <ArrowLeftRight :size="16" aria-hidden="true" />
        </button>
        <button type="button" :aria-pressed="isPropertySaved(property.id)" :aria-label="isPropertySaved(property.id) ? 'সেভ থেকে সরান' : 'সেভ করুন'" :title="isPropertySaved(property.id) ? 'সেভ থেকে সরান' : 'সেভ করুন'" @click.stop="toggleSaveProperty(property.id)">
          <Heart :size="16" :fill="isPropertySaved(property.id) ? 'currentColor' : 'none'" aria-hidden="true" />
        </button>
      </div>
    </div>

    <div class="pc-body">
      <p class="pc-place"><MapPin :size="15" aria-hidden="true" />{{ place }}</p>
      <h3 class="pc-title"><NuxtLink :to="`/properties/${property.id}`">{{ property.title }}</NuxtLink></h3>

      <div class="pc-price">
        <template v-if="property.hidePrice">
          <strong class="pc-ask">দাম জানতে যোগাযোগ করুন</strong>
        </template>
        <template v-else>
          <strong>{{ priceBn(summary.amount) }}</strong>
          <span>{{ summary.label }}</span>
        </template>
      </div>

      <ul class="pc-facts">
        <li v-if="area">{{ area }}</li>
        <li v-if="property.bedrooms">{{ toBn(property.bedrooms) }} বেডরুম</li>
        <li v-if="property.completionStatus">{{ completionLabels[property.completionStatus] || property.completionStatus }}</li>
        <li v-if="property.status && property.status !== 'Active'" class="pc-status">{{ statusLabel(property.status) }}</li>
      </ul>
    </div>
  </article>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue'
import { ArrowLeftRight, Heart, MapPin } from 'lucide-vue-next'
import type { PropertyItem } from '~/composables/useProperties'
import { useAuth } from '~/composables/useAuth'
import { useCompare } from '~/composables/useCompare'
import { askingPriceSummary } from '~/utils/buyerDetails.mjs'
import { areaLabel, completionLabels, priceBn, statusLabel, toBn, typeLabel } from '~/utils/propertyLabels'

const props = defineProps<{ property: PropertyItem; wide?: boolean }>()
const { isPropertySaved, toggleSaveProperty } = useAuth()
const { isInCompare, toggleCompare } = useCompare()

const imageBroken = ref(false)
const image = computed(() => props.property.featureImage || props.property.images?.[0] || '')
const summary = computed(() => askingPriceSummary(props.property))
const area = computed(() => areaLabel(props.property.squareFootage, props.property.landSize, props.property.landUnit))
const place = computed(() => [props.property.areaName, props.property.city].filter(Boolean).filter((v, i, a) => a.indexOf(v) === i).join(', '))
</script>

<style scoped>
.pc { position: relative; display: flex; flex-direction: column; background: var(--gb-sheet); border-radius: var(--gb-r-lg); overflow: hidden; border: 1px solid var(--gb-silt); transition: border-color .2s ease; }
.pc:hover { border-color: var(--gb-leaf); }
.pc:focus-within { border-color: var(--gb-paddy); }
.pc-media { position: relative; aspect-ratio: 4 / 3; background: #DDE5D2; overflow: hidden; }
.pc-media img { width: 100%; height: 100%; object-fit: cover; transition: transform .5s ease; }
.pc:hover .pc-media img { transform: scale(1.03); }
.pc-noimg { position: absolute; inset: 0; display: flex; align-items: flex-end; }
.pc-noimg svg { width: 100%; height: 60%; }
.pc-noimg path:first-child { fill: var(--gb-shoot); }
.pc-noimg path:last-child { fill: var(--gb-leaf); }
.pc-type { position: absolute; left: 14px; top: 14px; background: var(--gb-paper); color: var(--gb-paddy); font-family: var(--gb-display); font-weight: 600; font-size: .9rem; padding: 3px 12px; border-radius: 999px; }
.pc-tools { position: absolute; right: 12px; top: 12px; display: flex; gap: 6px; z-index: 2; }
.pc-tools button { width: 38px; height: 38px; border-radius: 50%; display: grid; place-items: center; background: rgba(251, 252, 247, .92); color: var(--gb-paddy); cursor: pointer; }
.pc-tools button:hover { background: #fff; }
.pc-tools button[aria-pressed="true"] { background: var(--gb-sun); color: #fff; }

.pc-body { padding: 18px 20px 20px; display: flex; flex-direction: column; gap: 6px; flex: 1; }
.pc-place { display: flex; align-items: center; gap: 6px; font-size: .88rem; color: var(--gb-ink-soft); }
.pc-place svg { flex-shrink: 0; color: var(--gb-leaf); }
.pc-title { font-size: 1.22rem; font-weight: 600; line-height: 1.35; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
.pc-title a { color: var(--gb-paddy); }
.pc-title a::after { content: ''; position: absolute; inset: 0; z-index: 1; }
.pc-title a:focus-visible { outline: none; }
.pc-price { margin-top: auto; padding-top: 10px; display: flex; flex-direction: column; }
.pc-price strong { font-family: var(--gb-display); font-size: 1.55rem; font-weight: 700; color: var(--gb-ink); line-height: 1.25; }
.pc-price .pc-ask { font-size: 1.1rem; color: var(--gb-sun-deep); }
.pc-price span { font-size: .82rem; color: var(--gb-ink-soft); }
.pc-facts { list-style: none; display: flex; flex-wrap: wrap; gap: 6px; margin-top: 10px; padding-top: 12px; border-top: 1px dashed var(--gb-silt); }
.pc-facts li { font-size: .85rem; color: var(--gb-ink); background: rgba(168, 197, 123, .22); padding: 2px 10px; border-radius: 999px; }
.pc-facts .pc-status { background: #FBE3D4; color: #8A3A0F; }

/* Wide variant: image beside text on larger screens */
@media (min-width: 900px) {
  .pc--wide { flex-direction: row; }
  .pc--wide .pc-media { aspect-ratio: auto; flex: 1.35; min-height: 380px; }
  .pc--wide .pc-body { flex: 1; padding: 32px; justify-content: center; }
  .pc--wide .pc-title { font-size: 1.8rem; -webkit-line-clamp: 3; }
  .pc--wide .pc-price { margin-top: 18px; }
  .pc--wide .pc-price strong { font-size: 2.2rem; }
}
</style>

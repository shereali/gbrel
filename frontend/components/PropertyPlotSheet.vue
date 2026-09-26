<template>
  <!-- Shown when a listing has no photos yet: the plot drawn like a mouza map sheet, using only listed facts. -->
  <figure class="ps" :aria-label="`${sizeText} জমির নকশা-ধারণা, ${place}`">
    <svg class="ps-map" viewBox="0 0 600 360" preserveAspectRatio="xMidYMid meet" overflow="visible" aria-hidden="true">
      <defs>
        <pattern id="ps-grid" width="24" height="24" patternUnits="userSpaceOnUse"><path d="M24 0H0V24" fill="none" stroke="#C9D3B8" stroke-width="1" /></pattern>
        <pattern id="ps-hatch" width="10" height="10" patternUnits="userSpaceOnUse" patternTransform="rotate(45)"><line x1="0" y1="0" x2="0" y2="10" stroke="#3F7A35" stroke-width="2" opacity=".35" /></pattern>
      </defs>
      <rect x="-1200" width="3000" height="360" fill="#F3F5EC" />
      <rect x="-1200" width="3000" height="360" fill="url(#ps-grid)" />
      <!-- road(s) -->
      <rect x="-1200" y="286" width="3000" height="46" fill="#DCE2D1" />
      <line x1="-1200" y1="309" x2="1800" y2="309" stroke="#fff" stroke-width="3" stroke-dasharray="18 14" />
      <template v-if="corner">
        <rect x="438" y="-400" width="46" height="732" fill="#DCE2D1" />
        <line x1="461" y1="0" x2="461" y2="286" stroke="#fff" stroke-width="3" stroke-dasharray="18 14" />
      </template>
      <!-- neighbouring plots -->
      <g fill="none" stroke="#B5C2A3" stroke-width="1.5">
        <rect x="24" y="96" width="118" height="176" /><rect x="24" y="12" width="118" height="72" />
        <rect v-if="!corner" x="456" y="96" width="120" height="176" />
      </g>
      <!-- the listed plot -->
      <rect x="160" y="96" width="262" height="176" fill="#E4EDD5" />
      <rect x="160" y="96" width="262" height="176" fill="url(#ps-hatch)" />
      <rect x="160" y="96" width="262" height="176" fill="none" stroke="#1D4A2A" stroke-width="4" />
      <g v-if="building">
        <rect x="206" y="128" width="170" height="112" fill="#FBFCF7" stroke="#1D4A2A" stroke-width="2" stroke-dasharray="6 5" />
      </g>
      <!-- north arrow -->
      <g class="ps-north" transform="translate(548 52)"><circle r="22" fill="#FBFCF7" stroke="#1D4A2A" stroke-width="2" /><path d="M0 -15 L7 6 L0 2 L-7 6Z" fill="#E2651C" /><text y="17" text-anchor="middle" font-size="11" font-weight="700" fill="#1D4A2A">উ</text></g>
    </svg>

    <figcaption class="ps-info">
      <strong class="ps-size">{{ sizeText }}</strong>
      <span class="ps-place">{{ place }}</span>
      <ul class="ps-tags">
        <li v-if="corner">কর্নার প্লট</li>
        <li v-if="building">{{ building }}</li>
        <li v-if="landUse">{{ landUse }}</li>
      </ul>
    </figcaption>
    <span v-if="roadLabel" class="ps-road">{{ roadLabel }}</span>
    <small class="ps-note">ছবি শিগগিরই যুক্ত হবে। নকশাটি ধারণামূলক, মাপ অনুযায়ী নয়।</small>
  </figure>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { areaLabel } from '~/utils/propertyLabels'

const props = defineProps<{ property: any }>()
const bd = computed(() => props.property?.buyerDetails || {})
const sizeText = computed(() => areaLabel(props.property?.squareFootage, props.property?.landSize, props.property?.landUnit) || 'জমি')
const place = computed(() => [props.property?.areaName, props.property?.city].filter(Boolean).filter((v: string, i: number, a: string[]) => a.indexOf(v) === i).join(', '))
const corner = computed(() => bd.value.cornerPlot === 'Yes')
const building = computed(() => (bd.value.buildingDescription || '').split(/[,،\n]/)[0]?.trim().slice(0, 40) || '')
const landUse = computed(() => ({ Residential: 'আবাসিক', Commercial: 'বাণিজ্যিক', Mixed: 'মিশ্র ব্যবহার' } as Record<string, string>)[bd.value.landUse] || '')
const roadLabel = computed(() => {
  const m = props.property?.hideExactAddress ? null : String(props.property?.address || '').match(/(?:রোড|Road)\s*(?:নম্বর|No\.?|#)?\s*([\d০-৯]+)/i)
  const width = bd.value.roadWidth ? ` · ${bd.value.roadWidth} ফুট রাস্তা` : ''
  return m ? `রোড ${m[1]}${width}` : (width ? width.slice(3) : '')
})
</script>

<style scoped>
.ps { position: relative; width: 100%; height: 100%; margin: 0; overflow: hidden; background: #F3F5EC; font-family: 'Noto Sans Bengali', sans-serif; }
.ps-map { position: absolute; inset: 0; width: 100%; height: 100%; overflow: visible; }
.ps-info { position: absolute; left: 22px; top: 20px; display: flex; flex-direction: column; gap: 2px; background: rgba(251, 252, 247, .94); border: 1.5px solid #1D4A2A; border-radius: 12px; padding: 14px 18px; max-width: min(360px, calc(100% - 44px)); }
.ps-size { font-family: 'Anek Bangla', 'Noto Sans Bengali', sans-serif; font-size: 34px; font-weight: 800; font-stretch: 110%; line-height: 1.1; color: #1D4A2A; }
.ps-place { font-size: 14px; color: #4A5A4E; }
.ps-tags { list-style: none; padding: 0; margin: 8px 0 0; display: flex; flex-wrap: wrap; gap: 6px; }
.ps-tags li { font-size: 12px; background: #1D4A2A; color: #fff; border-radius: 999px; padding: 2px 10px; }
.ps-road { position: absolute; left: 50%; bottom: 11%; transform: translateX(-50%); font-family: 'Anek Bangla', 'Noto Sans Bengali', sans-serif; font-size: 14px; font-weight: 600; color: #1D4A2A; background: #FBFCF7; border-radius: 6px; padding: 1px 10px; white-space: nowrap; }
.ps-note { position: absolute; right: 14px; bottom: 8px; font-size: 11px; color: #4A5A4E; background: rgba(243, 245, 236, .85); padding: 1px 6px; border-radius: 4px; }
@media (max-width: 767px) {
  .ps-north { display: none; }
  .ps-info { left: 12px; top: 12px; padding: 10px 14px; }
  .ps-size { font-size: 26px; }
  .ps-note { left: 12px; right: auto; bottom: 4px; }
  .ps-road { bottom: 14%; font-size: 12px; }
}
</style>

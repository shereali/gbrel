<template>
  <div>
    <!-- Sticky Floating Bar at Bottom -->
    <div v-if="compareCount > 0 && !modalOpen" class="floating-compare-bar animate-fade-in-up">
      <div class="flex items-center gap-3">
        <div style="width: 32px; height: 32px; border-radius: 50%; background: var(--color-gold); color: #0A1128; font-weight: 800; display: flex; align-items: center; justify-content: center; font-size: 0.85rem;">
          {{ compareCount }}
        </div>
        <span style="font-weight: 600; font-size: 0.9rem;">Properties Selected to Compare</span>
      </div>

      <div class="flex items-center gap-3">
        <button class="btn btn-sm btn-gold" @click="modalOpen = true">
          <span>Compare Side-by-Side</span>
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <polyline points="16 3 21 3 21 8"/>
            <line x1="4" y1="20" x2="21" y2="3"/>
          </svg>
        </button>

        <button class="btn btn-sm btn-outline-white" @click="clearComparison">
          Clear
        </button>
      </div>
    </div>

    <!-- Comparison Modal -->
    <div v-if="modalOpen" ref="modalRoot" class="modal-overlay" @click.self="closeModal">
      <div class="modal-card animate-fade-in-up" style="max-width: 1100px; width: 95%;">
        <button class="modal-close-btn" @click="closeModal" aria-label="Close comparison">✕</button>

        <div class="flex items-center justify-between" style="margin-bottom: 24px; padding-bottom: 16px; border-bottom: 1px solid var(--color-border);">
          <div>
            <h3 style="font-size: 1.5rem; font-weight: 800; color: #0A1128;">Side-by-Side Property Comparison</h3>
            <p style="font-size: 0.9rem; color: #64748B;">Compare specs, prices in BDT, legal approvals, and amenities</p>
          </div>
          <button class="btn btn-sm btn-outline" @click="clearComparison(); closeModal();">Clear All</button>
        </div>

        <!-- Comparison Table -->
        <div style="overflow-x: auto;">
          <table style="width: 100%; border-collapse: collapse; min-width: 680px;">
            <thead>
              <tr>
                <th style="padding: 12px; text-align: left; width: 200px; color: #64748B; font-size: 0.85rem; text-transform: uppercase;">Attribute</th>
                <th v-for="prop in comparedProperties" :key="prop.id" style="padding: 12px; text-align: left; min-width: 220px; vertical-align: top;">
                  <img :src="resolveMediaUrl(prop.featureImage || prop.images?.[0] || prop.videoPoster) || 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?q=80&w=800'" style="width: 100%; height: 130px; object-fit: cover; border-radius: var(--radius-md); margin-bottom: 8px;" />
                  <div style="font-size: 0.95rem; font-weight: 800; color: #0F172A; line-height: 1.3;">{{ prop.title }}</div>
                  <button @click="toggleCompare(prop.id)" style="background: none; border: none; color: #E11D48; font-size: 0.75rem; font-weight: 600; cursor: pointer; margin-top: 4px;">
                    Remove from comparison ✕
                  </button>
                </th>
              </tr>
            </thead>
            <tbody>
              <!-- Price -->
              <tr style="border-top: 1px solid var(--color-border); background: #F8FAFC;">
                <td style="padding: 12px; font-weight: 700; color: #0F172A;">Price (BDT)</td>
                <td v-for="prop in comparedProperties" :key="prop.id" style="padding: 12px; font-family: var(--font-ui); font-size: 1.2rem; font-weight: 800; color: #059669; font-variant-numeric: tabular-nums;">
                  {{ formatBDT(prop.price) }}
                </td>
              </tr>

              <!-- Location -->
              <tr style="border-top: 1px solid var(--color-border);">
                <td style="padding: 12px; font-weight: 700; color: #0F172A;">Location / Division</td>
                <td v-for="prop in comparedProperties" :key="prop.id" style="padding: 12px; color: #334155; font-size: 0.9rem;">
                  {{ prop.areaName }}, {{ prop.city }} ({{ prop.state }})
                </td>
              </tr>

              <!-- Property Type -->
              <tr style="border-top: 1px solid var(--color-border); background: #F8FAFC;">
                <td style="padding: 12px; font-weight: 700; color: #0F172A;">Property Type</td>
                <td v-for="prop in comparedProperties" :key="prop.id" style="padding: 12px; font-size: 0.9rem;">
                  <span class="badge badge-status">{{ prop.propertyType }}</span>
                </td>
              </tr>

              <!-- Space / Land Area -->
              <tr style="border-top: 1px solid var(--color-border);">
                <td style="padding: 12px; font-weight: 700; color: #0F172A;">Total Space / Land Size</td>
                <td v-for="prop in comparedProperties" :key="prop.id" style="padding: 12px; font-weight: 700; color: #0F172A; font-size: 0.9rem;">
                  {{ formatArea(prop.squareFootage, prop.landSize, prop.landUnit) }}
                </td>
              </tr>

              <!-- Beds & Baths -->
              <tr style="border-top: 1px solid var(--color-border); background: #F8FAFC;">
                <td style="padding: 12px; font-weight: 700; color: #0F172A;">Beds & Baths</td>
                <td v-for="prop in comparedProperties" :key="prop.id" style="padding: 12px; color: #334155; font-size: 0.9rem;">
                  {{ prop.bedrooms }} Beds / {{ prop.bathrooms }} Baths
                </td>
              </tr>

              <!-- Parking -->
              <tr style="border-top: 1px solid var(--color-border);">
                <td style="padding: 12px; font-weight: 700; color: #0F172A;">Car Parking</td>
                <td v-for="prop in comparedProperties" :key="prop.id" style="padding: 12px; color: #334155; font-size: 0.9rem;">
                  {{ prop.parking }} Reserved Bay(s)
                </td>
              </tr>

              <!-- RAJUK / CDA Approval -->
              <tr style="border-top: 1px solid var(--color-border); background: #F8FAFC;">
                <td style="padding: 12px; font-weight: 700; color: #0F172A;">Regulatory Clearance</td>
                <td v-for="prop in comparedProperties" :key="prop.id" style="padding: 12px;">
                  <span v-if="prop.isRajukApproved" class="badge badge-rajuk">RAJUK Approved</span>
                  <span v-else class="badge badge-status">Municipal Cleared</span>
                </td>
              </tr>

              <!-- Facing -->
              <tr style="border-top: 1px solid var(--color-border);">
                <td style="padding: 12px; font-weight: 700; color: #0F172A;">Facing Direction</td>
                <td v-for="prop in comparedProperties" :key="prop.id" style="padding: 12px; color: #334155; font-size: 0.9rem;">
                  {{ prop.facing || 'N/A' }}
                </td>
              </tr>

              <!-- Action Link -->
              <tr style="border-top: 1px solid var(--color-border); background: #F8FAFC;">
                <td style="padding: 12px; font-weight: 700; color: #0F172A;">Action</td>
                <td v-for="prop in comparedProperties" :key="prop.id" style="padding: 12px;">
                  <NuxtLink :to="`/properties/${prop.id}`" class="btn btn-sm btn-primary" style="width: 100%;" @click="closeModal()">
                    View Full Details
                  </NuxtLink>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { useCompare } from '~/composables/useCompare'
import { useProperties } from '~/composables/useProperties'
import { formatBDT, formatArea } from '~/composables/useCurrency'
import { useOverlayBehavior } from '~/composables/useOverlayBehavior'

const { comparisonList, compareCount, toggleCompare, clearComparison } = useCompare()
const { getPropertyById } = useProperties()

const modalOpen = ref(false)
const modalRoot = ref<HTMLElement | null>(null)

const closeModal = () => {
  modalOpen.value = false
}

useOverlayBehavior(modalOpen, closeModal, modalRoot)

const comparedProperties = computed(() => {
  return comparisonList.value.map(id => getPropertyById(id))
})
</script>

<style scoped>
@media (max-width: 640px) {
  .floating-compare-bar {
    width: calc(100vw - 32px - env(safe-area-inset-left, 0px) - env(safe-area-inset-right, 0px));
    border-radius: var(--radius-lg);
    padding: 10px 14px;
    gap: 10px;
    flex-wrap: wrap;
    justify-content: space-between;
  }
}
</style>

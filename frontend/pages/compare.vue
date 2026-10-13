<template>
  <div style="background: #F8FAFC; min-height: 100vh; padding: 40px 0 80px;">
    <div class="container">
      <!-- Header -->
      <div class="flex items-center justify-between flex-wrap gap-4" style="margin-bottom: 32px;">
        <div>
          <div class="flex items-center gap-2" style="font-size: 0.85rem; color: #64748B; margin-bottom: 6px;">
            <NuxtLink to="/" style="color: #64748B;">Home</NuxtLink>
            <span>/</span>
            <span style="color: #0F172A; font-weight: 600;">Property Comparison</span>
          </div>
          <h1 style="font-size: 2.2rem; font-weight: 800; color: #0A1128;">Side-by-Side Property Comparison</h1>
          <p style="color: #64748B; font-size: 0.95rem;">Compare structural specifications, pricing, legal approvals, and investment yields</p>
        </div>

        <div class="flex items-center gap-3">
          <!-- Add Property Quick Dropdown -->
          <select @change="handleQuickAdd($event)" class="form-select" style="width: auto;">
            <option value="">+ Add Property to Compare</option>
            <option v-for="prop in allProperties" :key="prop.id" :value="prop.id" :disabled="isInCompare(prop.id)">
              {{ prop.title }} ({{ formatBDT(prop.price) }})
            </option>
          </select>

          <button v-if="compareCount > 0" class="btn btn-outline" @click="clearComparison">
            Clear All
          </button>
        </div>
      </div>

      <!-- Comparison Content -->
      <div v-if="comparedProperties.length > 0" style="background: #FFFFFF; border: 1px solid var(--color-border); border-radius: var(--radius-xl); overflow: hidden; box-shadow: var(--shadow-md);">
        <div style="overflow-x: auto;">
          <table style="width: 100%; border-collapse: collapse; min-width: 720px;">
            <thead>
              <tr style="background: #F1F5F9;">
                <th style="padding: 20px; text-align: left; width: 220px; color: #475569; font-size: 0.85rem; text-transform: uppercase;">Specifications</th>
                <th v-for="prop in comparedProperties" :key="prop.id" style="padding: 20px; text-align: left; min-width: 240px; vertical-align: top;">
                  <img :src="resolveMediaUrl(prop.featureImage || prop.images?.[0] || prop.videoPoster) || 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?q=80&w=800'" :alt="prop.title" style="width: 100%; height: 150px; object-fit: cover; border-radius: var(--radius-md); margin-bottom: 10px;" />
                  <NuxtLink :to="`/properties/${prop.id}`">
                    <h3 style="font-size: 1.05rem; font-weight: 800; color: #0A1128; line-height: 1.3;">{{ prop.title }}</h3>
                  </NuxtLink>
                  <button @click="toggleCompare(prop.id)" style="background: none; border: none; color: #E11D48; font-size: 0.8rem; font-weight: 700; cursor: pointer; margin-top: 6px;">
                    Remove ✕
                  </button>
                </th>
              </tr>
            </thead>
            <tbody>
              <!-- Price (BDT) -->
              <tr style="border-top: 1px solid var(--color-border); background: #FFFFFF;">
                <td style="padding: 16px 20px; font-weight: 800; color: #0A1128;">Price (BDT)</td>
                <td v-for="prop in comparedProperties" :key="prop.id" style="padding: 16px 20px; font-family: var(--font-ui); font-size: 1.3rem; font-weight: 800; color: #059669; font-variant-numeric: tabular-nums;">
                  {{ formatBDT(prop.price) }}
                  <div v-if="prop.squareFootage" style="font-size: 0.78rem; color: #64748B; font-family: var(--font-sans); font-weight: 500;">
                    ৳ {{ Math.round(prop.price / prop.squareFootage).toLocaleString() }} / Sq. Ft.
                  </div>
                </td>
              </tr>

              <!-- Location -->
              <tr style="border-top: 1px solid var(--color-border); background: #F8FAFC;">
                <td style="padding: 16px 20px; font-weight: 700; color: #0A1128;">Location</td>
                <td v-for="prop in comparedProperties" :key="prop.id" style="padding: 16px 20px; color: #334155; font-size: 0.92rem;">
                  {{ prop.areaName }}, {{ prop.city }} ({{ prop.state }})
                </td>
              </tr>

              <!-- Category -->
              <tr style="border-top: 1px solid var(--color-border); background: #FFFFFF;">
                <td style="padding: 16px 20px; font-weight: 700; color: #0A1128;">Category</td>
                <td v-for="prop in comparedProperties" :key="prop.id" style="padding: 16px 20px;">
                  <span class="badge badge-status">{{ prop.propertyType }}</span>
                </td>
              </tr>

              <!-- Size & Land -->
              <tr style="border-top: 1px solid var(--color-border); background: #F8FAFC;">
                <td style="padding: 16px 20px; font-weight: 700; color: #0A1128;">Total Area / Land</td>
                <td v-for="prop in comparedProperties" :key="prop.id" style="padding: 16px 20px; font-weight: 800; color: #0A1128;">
                  {{ formatArea(prop.squareFootage, prop.landSize, prop.landUnit) }}
                </td>
              </tr>

              <!-- Beds & Baths -->
              <tr style="border-top: 1px solid var(--color-border); background: #FFFFFF;">
                <td style="padding: 16px 20px; font-weight: 700; color: #0A1128;">Bedrooms & Baths</td>
                <td v-for="prop in comparedProperties" :key="prop.id" style="padding: 16px 20px; color: #334155;">
                  {{ prop.bedrooms }} Beds / {{ prop.bathrooms }} Baths
                </td>
              </tr>

              <!-- Parking -->
              <tr style="border-top: 1px solid var(--color-border); background: #F8FAFC;">
                <td style="padding: 16px 20px; font-weight: 700; color: #0A1128;">Parking Space</td>
                <td v-for="prop in comparedProperties" :key="prop.id" style="padding: 16px 20px; color: #334155;">
                  {{ prop.parking }} Reserved Bay(s)
                </td>
              </tr>

              <!-- Facing -->
              <tr style="border-top: 1px solid var(--color-border); background: #FFFFFF;">
                <td style="padding: 16px 20px; font-weight: 700; color: #0A1128;">Facing Direction</td>
                <td v-for="prop in comparedProperties" :key="prop.id" style="padding: 16px 20px; color: #334155;">
                  {{ prop.facing || 'N/A' }}
                </td>
              </tr>

              <!-- RAJUK / CDA Approval -->
              <tr style="border-top: 1px solid var(--color-border); background: #F8FAFC;">
                <td style="padding: 16px 20px; font-weight: 700; color: #0A1128;">Legal Clearance</td>
                <td v-for="prop in comparedProperties" :key="prop.id" style="padding: 16px 20px;">
                  <span v-if="prop.isRajukApproved" class="badge badge-rajuk">RAJUK Approved</span>
                  <span v-else class="badge badge-status">Municipal Cleared</span>
                </td>
              </tr>

              <!-- Action Link -->
              <tr style="border-top: 1px solid var(--color-border); background: #FFFFFF;">
                <td style="padding: 20px; font-weight: 700; color: #0A1128;">Actions</td>
                <td v-for="prop in comparedProperties" :key="prop.id" style="padding: 20px;">
                  <NuxtLink :to="`/properties/${prop.id}`" class="btn btn-primary" style="width: 100%;">
                    <span>View Listing Details</span>
                  </NuxtLink>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else class="text-center" style="background:#FFF; padding:60px 20px; border-radius:var(--radius-xl); border:1px solid var(--color-border);">
        <div style="width: 60px; height: 60px; border-radius: 50%; background: #F1F5F9; color: #64748B; margin: 0 auto 16px; display: flex; align-items: center; justify-content: center;">
          <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path d="M8 3H5a2 2 0 0 0-2 2v3M16 3h3a2 2 0 0 1 2 2v3M8 21H5a2 2 0 0 1-2-2v-3M16 21h3a2 2 0 0 0 2-2v-3M4 12h2M10 12h2M16 12h2"/>
          </svg>
        </div>
        <h3 style="font-size: 1.5rem; font-weight: 800; color: #0A1128; margin-bottom: 8px;">No Properties Added for Comparison</h3>
        <p style="color: #64748B; font-size: 1rem; max-width: 500px; margin: 0 auto 24px;">
          Browse our property catalog and click the compare icon on any property card to view side-by-side specs.
        </p>
        <NuxtLink to="/properties" class="btn btn-emerald btn-lg">Browse Properties Catalog</NuxtLink>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { useCompare } from '~/composables/useCompare'
import { useProperties } from '~/composables/useProperties'
import { formatBDT, formatArea } from '~/composables/useCurrency'

const { comparisonList, compareCount, toggleCompare, isInCompare, clearComparison } = useCompare()
const { properties: allProperties, getPropertyById } = useProperties()

const comparedProperties = computed(() => {
  return comparisonList.value.map(id => getPropertyById(id))
})

const handleQuickAdd = (event: Event) => {
  const select = event.target as HTMLSelectElement
  if (select.value) {
    toggleCompare(Number(select.value))
    select.value = ''
  }
}
</script>

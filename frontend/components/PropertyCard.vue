<template>
  <div class="property-card">
    <!-- Media Wrapper -->
    <div class="card-media-wrapper">
      <img 
        :src="(property.images && property.images.length > 0) ? property.images[0] : defaultCardImage" 
        :alt="property.title" 
        class="card-image" 
        loading="lazy" 
        @error="onImageError"
      />
      
      <!-- Top Badges -->
      <div class="card-badge-container">
        <span v-if="property.isRajukApproved" class="badge badge-rajuk">
          <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
            <polyline points="20 6 9 17 4 12"/>
          </svg>
          RAJUK Approved
        </span>
        <span v-else-if="property.state === 'Chittagong'" class="badge badge-rajuk" style="background:#0F766E;">
          CDA Approved
        </span>
        <span v-if="property.propertyType === 'Land Share'" class="badge" style="background:#7C3AED; color:#FFF; font-weight:700;">
          🤝 Land Share
        </span>
        <span v-if="property.brochureUrl" class="badge" style="background:#FEF3C7; color:#92400E; font-weight:700; border:1px solid #FCD34D;" title="Downloadable PDF brochure available">
          📄 Brochure
        </span>
        <span v-if="property.isFeatured" class="badge badge-featured">Exclusive</span>
        <span v-if="property.propertyType === 'Hotel'" class="badge badge-hotel">High ROI</span>
      </div>

      <!-- Action Overlay: Favorite & Compare -->
      <div class="card-actions-overlay">
        <!-- Compare Toggle -->
        <button 
          class="card-icon-action" 
          :class="{ active: isInCompare(property.id) }"
          :title="isInCompare(property.id) ? 'Remove from Compare' : 'Add to Compare'"
          :aria-label="isInCompare(property.id) ? 'Remove from compare' : 'Add to compare'"
          @click.stop="toggleCompare(property.id)"
        >
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <polyline points="16 3 21 3 21 8"/>
            <line x1="4" y1="20" x2="21" y2="3"/>
            <polyline points="21 16 21 21 16 21"/>
            <line x1="15" y1="15" x2="21" y2="21"/>
            <line x1="4" y1="4" x2="9" y2="9"/>
          </svg>
        </button>

        <!-- Save to Wishlist / Favorites -->
        <button 
          class="card-icon-action"
          :class="{ active: isPropertySaved(property.id) }"
          :title="isPropertySaved(property.id) ? 'Remove from Favorites' : 'Save to Favorites'"
          :aria-label="isPropertySaved(property.id) ? 'Remove from favorites' : 'Save to favorites'"
          @click.stop="toggleSaveProperty(property.id)"
        >
          <svg width="16" height="16" viewBox="0 0 24 24" :fill="isPropertySaved(property.id) ? 'currentColor' : 'none'" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
          </svg>
        </button>
      </div>

      <!-- Bottom Pill: Property Type & Status -->
      <div class="card-bottom-pill">
        <span>{{ property.propertyType }}</span>
        <span>•</span>
        <span>{{ property.listingType }}</span>
      </div>
    </div>

    <!-- Body Content -->
    <div class="card-body">
      <!-- Location -->
      <div class="card-location">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
          <circle cx="12" cy="10" r="3"/>
        </svg>
        <span>{{ property.areaName }}, {{ property.city }}</span>
      </div>

      <!-- Title -->
      <NuxtLink :to="`/properties/${property.id}`">
        <h3 class="card-title" :title="property.title">{{ property.title }}</h3>
      </NuxtLink>

      <!-- Price Row -->
      <div class="card-price-row">
        <div>
          <div class="card-price">{{ formatBDT(property.price) }}</div>
          <div v-if="property.priceUnit" class="card-price-sub">{{ property.priceUnit }}</div>
          <div v-else-if="property.squareFootage" class="card-price-sub">৳ {{ Math.round(property.price / property.squareFootage).toLocaleString() }} / Sq. Ft.</div>
        </div>
        <span class="badge badge-status">{{ property.status }}</span>
      </div>

      <!-- Specs Grid -->
      <div class="card-specs-grid">
        <div v-if="property.bedrooms > 0" class="spec-item">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M2 4v16M2 8h18a2 2 0 0 1 2 2v10M2 17h20M6 8v9"/>
          </svg>
          <span><strong>{{ property.bedrooms }}</strong> Beds</span>
        </div>

        <div v-if="property.bathrooms > 0" class="spec-item">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M9 6h6a3 3 0 0 1 3 3v2a3 3 0 0 1-3 3H9a3 3 0 0 1-3-3V9a3 3 0 0 1 3-3zM4 14h16M7 14v4M17 14v4"/>
          </svg>
          <span><strong>{{ property.bathrooms }}</strong> Baths</span>
        </div>

        <div class="spec-item">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
            <line x1="3" y1="9" x2="21" y2="9"/>
            <line x1="9" y1="21" x2="9" y2="9"/>
          </svg>
          <span><strong>{{ formatArea(property.squareFootage, property.landSize, property.landUnit) }}</strong></span>
        </div>

        <div v-if="property.parking > 0" class="spec-item">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="1" y="3" width="22" height="13" rx="2" ry="2"/>
            <path d="M16 8h-6a2 2 0 0 0-2 2v6"/>
          </svg>
          <span><strong>{{ property.parking }}</strong> Parking</span>
        </div>

        <div v-if="property.facing" class="spec-item">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="10"/>
            <polygon points="12 8 8 12 12 16 12 8"/>
          </svg>
          <span>Facing: <strong>{{ property.facing }}</strong></span>
        </div>

        <div class="spec-item">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="2">
            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
          </svg>
          <span style="color:#059669; font-weight:600;">Verified</span>
        </div>
      </div>

      <!-- Footer Action Buttons -->
      <div class="card-footer-actions">
        <NuxtLink :to="`/properties/${property.id}`" class="btn btn-primary btn-card-details">
          <span>View Details & Tabs</span>
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <line x1="5" y1="12" x2="19" y2="12"/>
            <polyline points="12 5 19 12 12 19"/>
          </svg>
        </NuxtLink>

        <!-- Direct WhatsApp Lead Hunter Button -->
        <a 
          :href="`https://wa.me/8801819987654?text=Hello%20GBREL,%20I%20am%20interested%20in%20listing:%20${encodeURIComponent(property.title)}%20(ID:%20${property.id})`" 
          target="_blank" 
          rel="noopener noreferrer"
          class="btn-card-whatsapp"
          :title="`WhatsApp inquiry for ${property.title}`"
          :aria-label="`WhatsApp inquiry for ${property.title}`"
        >
          <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
            <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.711 2.598 2.664-.698c.969.54 1.861.855 2.796.855 3.18 0 5.767-2.587 5.768-5.766 0-3.18-2.586-5.767-5.768-5.767zm7.531 5.766c-.002 4.153-3.38 7.531-7.531 7.531-.019 0-.038 0-.057 0-1.284 0-2.53-.332-3.64-.962l-4.053 1.063 1.082-3.953c-.707-1.16-1.082-2.493-1.082-3.864.002-4.153 3.38-7.531 7.531-7.531 4.153 0 7.531 3.378 7.531 7.531z"/>
          </svg>
        </a>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import type { PropertyItem } from '~/composables/useProperties'
import { formatBDT, formatArea } from '~/composables/useCurrency'
import { useAuth } from '~/composables/useAuth'
import { useCompare } from '~/composables/useCompare'

defineProps<{
  property: PropertyItem
}>()

const { isPropertySaved, toggleSaveProperty } = useAuth()
const { isInCompare, toggleCompare } = useCompare()

const defaultCardImage = 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?q=80&w=800&auto=format&fit=crop'
const onImageError = (event: Event) => {
  const target = event.target as HTMLImageElement
  if (target && target.src !== defaultCardImage) {
    target.src = defaultCardImage
  }
}
</script>

<template>
  <div style="background: #F8FAFC; min-height: 100vh; padding: 40px 0 80px;">
    <div class="container">
      <!-- Breadcrumb & Page Header -->
      <div style="margin-bottom: 28px;">
        <div class="flex items-center gap-2" style="font-size: 0.85rem; color: #64748B; margin-bottom: 8px;">
          <NuxtLink to="/" style="color: #64748B;">Home</NuxtLink>
          <span>/</span>
          <span style="color: #0F172A; font-weight: 600;">Verified Property Listings</span>
        </div>
        <div class="flex items-center justify-between flex-wrap gap-4">
          <div>
            <h1 style="font-size: 2.2rem; font-weight: 800; color: #0A1128;">Explore Properties & Lands</h1>
            <p style="color: #64748B; font-size: 0.95rem;">Showing {{ filteredProperties.length }} verified luxury flats, plots, and commercial resort assets</p>
          </div>

          <!-- Layout Switcher & Filter Trigger -->
          <div class="flex items-center gap-3 flex-wrap">
            <button 
              class="btn btn-sm btn-emerald mobile-filter-toggle"
              @click="mobileFilterOpen = !mobileFilterOpen"
            >
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <line x1="4" y1="21" x2="4" y2="14"/>
                <line x1="4" y1="10" x2="4" y2="3"/>
                <line x1="12" y1="21" x2="12" y2="12"/>
                <line x1="12" y1="8" x2="12" y2="3"/>
                <line x1="20" y1="21" x2="20" y2="16"/>
                <line x1="20" y1="12" x2="20" y2="3"/>
                <line x1="1" y1="14" x2="7" y2="14"/>
                <line x1="9" y1="8" x2="15" y2="8"/>
                <line x1="17" y1="16" x2="23" y2="16"/>
              </svg>
              <span>{{ mobileFilterOpen ? 'Hide Filters' : 'Filter Properties' }}</span>
            </button>

            <div style="background: #FFFFFF; border: 1.5px solid var(--color-border); border-radius: var(--radius-md); padding: 4px; display: flex; gap: 4px;">
              <button 
                class="btn btn-sm" 
                :class="viewMode === 'grid' ? 'btn-primary' : 'btn-outline'" 
                style="border:none;"
                @click="viewMode = 'grid'"
              >
                Grid View
              </button>
              <button 
                class="btn btn-sm" 
                :class="viewMode === 'split' ? 'btn-primary' : 'btn-outline'" 
                style="border:none;"
                @click="viewMode = 'split'"
              >
                Map & List
              </button>
            </div>

            <!-- Sort Dropdown -->
            <select v-model="sortBy" class="form-select" style="width: auto; padding: 8px 14px; font-weight: 600;">
              <option value="newest">Sort: Newest First</option>
              <option value="price_asc">Price: Low to High</option>
              <option value="price_desc">Price: High to Low</option>
              <option value="area_desc">Size: Largest Area</option>
            </select>
          </div>
        </div>

        <!-- Category Pills Bar -->
        <div class="flex items-center gap-2 flex-wrap" style="margin-top: 20px;">
          <button 
            class="badge" 
            :style="{ 
              cursor: 'pointer', 
              padding: '6px 14px', 
              borderRadius: '9999px', 
              fontWeight: '700',
              background: filters.propertyType === '' ? '#0F172A' : '#FFFFFF',
              color: filters.propertyType === '' ? '#FFFFFF' : '#475569',
              border: '1px solid var(--color-border)'
            }"
            @click="filters.propertyType = ''"
          >
            All Categories ({{ properties.length }})
          </button>
          <button 
            class="badge" 
            :style="{ 
              cursor: 'pointer', 
              padding: '6px 14px', 
              borderRadius: '9999px', 
              fontWeight: '700',
              background: filters.propertyType === 'Land Share' ? '#7C3AED' : '#FFFFFF',
              color: filters.propertyType === 'Land Share' ? '#FFFFFF' : '#7C3AED',
              border: filters.propertyType === 'Land Share' ? '1px solid #7C3AED' : '1px solid #DDD6FE'
            }"
            @click="filters.propertyType = 'Land Share'"
          >
            🤝 Land Share Projects
          </button>
          <button 
            class="badge" 
            :style="{ 
              cursor: 'pointer', 
              padding: '6px 14px', 
              borderRadius: '9999px', 
              fontWeight: '700',
              background: filters.propertyType === 'Flat' ? '#0F172A' : '#FFFFFF',
              color: filters.propertyType === 'Flat' ? '#FFFFFF' : '#475569',
              border: '1px solid var(--color-border)'
            }"
            @click="filters.propertyType = 'Flat'"
          >
            Flats & Apartments
          </button>
          <button 
            class="badge" 
            :style="{ 
              cursor: 'pointer', 
              padding: '6px 14px', 
              borderRadius: '9999px', 
              fontWeight: '700',
              background: filters.propertyType === 'Plot' ? '#0F172A' : '#FFFFFF',
              color: filters.propertyType === 'Plot' ? '#FFFFFF' : '#475569',
              border: '1px solid var(--color-border)'
            }"
            @click="filters.propertyType = 'Plot'"
          >
            Plots & Katha Lands
          </button>
          <button 
            class="badge" 
            :style="{ 
              cursor: 'pointer', 
              padding: '6px 14px', 
              borderRadius: '9999px', 
              fontWeight: '700',
              background: filters.propertyType === 'Hotel' ? '#0F172A' : '#FFFFFF',
              color: filters.propertyType === 'Hotel' ? '#FFFFFF' : '#475569',
              border: '1px solid var(--color-border)'
            }"
            @click="filters.propertyType = 'Hotel'"
          >
            Resorts & Hotel Suites
          </button>
          <button 
            class="badge" 
            :style="{ 
              cursor: 'pointer', 
              padding: '6px 14px', 
              borderRadius: '9999px', 
              fontWeight: '700',
              background: filters.propertyType === 'Duplex' ? '#0F172A' : '#FFFFFF',
              color: filters.propertyType === 'Duplex' ? '#FFFFFF' : '#475569',
              border: '1px solid var(--color-border)'
            }"
            @click="filters.propertyType = 'Duplex'"
          >
            Duplexes & Penthouses
          </button>
        </div>
      </div>

      <!-- Main Layout: Sidebar Filters + Results Grid -->
      <div style="display: grid; grid-template-columns: 280px 1fr; gap: 28px;" class="properties-layout-grid">
        <!-- 1. Filter Sidebar -->
        <aside 
          class="properties-sidebar-card"
          :class="{ 'mobile-open': mobileFilterOpen }"
          style="background: #FFFFFF; border: 1px solid var(--color-border); border-radius: var(--radius-lg); padding: 24px; height: fit-content;"
        >
          <div class="flex items-center justify-between" style="margin-bottom: 20px; padding-bottom: 12px; border-bottom: 1px solid var(--color-border);">
            <strong style="font-size: 1.1rem; color: #0A1128;">Filter Properties</strong>
            <button @click="resetFilters" style="background:none; border:none; color:#E11D48; font-size:0.8rem; font-weight:700; cursor:pointer;">Reset</button>
          </div>

          <!-- Listing Type (Sale / Lease) -->
          <div class="form-group" style="margin-bottom: 20px;">
            <label class="form-label">Listing Type</label>
            <div style="display: flex; gap: 6px;">
              <button 
                type="button"
                class="btn btn-sm flex-1"
                :class="filters.listingType === '' ? 'btn-primary' : 'btn-outline'"
                @click="filters.listingType = ''"
              >All</button>
              <button 
                type="button"
                class="btn btn-sm flex-1"
                :class="filters.listingType === 'Sale' ? 'btn-primary' : 'btn-outline'"
                @click="filters.listingType = 'Sale'"
              >Sale</button>
              <button 
                type="button"
                class="btn btn-sm flex-1"
                :class="filters.listingType === 'Lease' ? 'btn-primary' : 'btn-outline'"
                @click="filters.listingType = 'Lease'"
              >Lease</button>
            </div>
          </div>

          <!-- Property Type -->
          <div class="form-group" style="margin-bottom: 20px;">
            <label class="form-label">Property Category</label>
            <select v-model="filters.propertyType" class="form-select">
              <option value="">All Categories</option>
              <option value="Land Share">Land Share (Co-Ownership)</option>
              <option value="Flat">Flat / Apartment</option>
              <option value="Plot">Residential Plot (Katha)</option>
              <option value="Hotel">Hotel / Resort Suite</option>
              <option value="Duplex">Duplex & Penthouse</option>
              <option value="Commercial">Commercial Office</option>
              <option value="Land">Freehold Land (Bigha)</option>
            </select>
          </div>

          <!-- Division / Region -->
          <div class="form-group" style="margin-bottom: 20px;">
            <label class="form-label">Division / Region</label>
            <select v-model="filters.state" class="form-select">
              <option value="">All Regions</option>
              <option value="Dhaka North">Dhaka North (Gulshan, Banani, Uttara)</option>
              <option value="Dhaka South">Dhaka South (Dhanmondi, Motijheel)</option>
              <option value="Chittagong">Chittagong & Cox's Bazar</option>
              <option value="Sylhet">Sylhet & Sreemangal</option>
            </select>
          </div>

          <!-- Price Range Slider (BDT) -->
          <div class="form-group" style="margin-bottom: 20px;">
            <div class="flex items-center justify-between">
              <label class="form-label">Max Budget (BDT)</label>
              <span style="font-weight: 800; color: #059669; font-size: 0.95rem;">
                {{ formatBDT(filters.maxPrice) }}
              </span>
            </div>
            <input 
              v-model.number="filters.maxPrice" 
              type="range" 
              min="5000000" 
              max="200000000" 
              step="5000000" 
              style="width: 100%; accent-color: #059669; margin-top: 6px;" 
            />
          </div>

          <!-- Bedrooms Dropdown -->
          <div class="form-group" style="margin-bottom: 20px;">
            <label class="form-label">Bedrooms</label>
            <select v-model="filters.bedrooms" class="form-select">
              <option value="">Any Bedrooms</option>
              <option value="1">1+ Bedrooms</option>
              <option value="2">2+ Bedrooms</option>
              <option value="3">3+ Bedrooms</option>
              <option value="4">4+ Master Bedrooms</option>
            </select>
          </div>

          <!-- Verified Checks -->
          <div class="form-group" style="margin-bottom: 12px;">
            <label class="form-label">Due Diligence Filters</label>
            <label class="flex items-center gap-2" style="font-size: 0.88rem; color: #334155; margin-bottom: 8px; cursor:pointer;">
              <input v-model="filters.rajukOnly" type="checkbox" style="accent-color: #059669; width: 16px; height: 16px;" />
              <span>RAJUK / CDA Approved Only</span>
            </label>
            <label class="flex items-center gap-2" style="font-size: 0.88rem; color: #334155; cursor:pointer;">
              <input v-model="filters.openHouseOnly" type="checkbox" style="accent-color: #059669; width: 16px; height: 16px;" />
              <span>Open House Scheduled</span>
            </label>
          </div>
        </aside>

        <!-- 2. Results Area -->
        <main>
          <!-- Split Map View Mode -->
          <div v-if="viewMode === 'split'" style="margin-bottom: 32px;">
            <InteractiveMap :properties="filteredProperties" />
          </div>

          <!-- Property Grid -->
          <div v-if="filteredProperties.length > 0" class="grid grid-2" style="gap: 24px;">
            <PropertyCard 
              v-for="prop in filteredProperties" 
              :key="prop.id" 
              :property="prop" 
            />
          </div>

          <!-- Empty State -->
          <div v-else class="text-center" style="background: #FFFFFF; border: 1px solid var(--color-border); border-radius: var(--radius-lg); padding: 60px 20px;">
            <div style="width: 56px; height: 56px; border-radius: 50%; background: #F1F5F9; color: #64748B; margin: 0 auto 16px; display: flex; align-items: center; justify-content: center;">
              <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <circle cx="11" cy="11" r="8"/>
                <line x1="21" y1="21" x2="16.65" y2="16.65"/>
              </svg>
            </div>
            <h3 style="font-size: 1.4rem; font-weight: 800; color: #0A1128; margin-bottom: 6px;">No Properties Match Your Filters</h3>
            <p style="color: #64748B; font-size: 0.95rem; margin-bottom: 20px;">Try adjusting your price range, property category, or clearing location filters.</p>
            <button class="btn btn-emerald" @click="resetFilters">Clear All Filters</button>
          </div>
        </main>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, computed, onMounted, watch } from 'vue'
import { useRoute } from 'vue-router'
import { useProperties } from '~/composables/useProperties'
import { formatBDT } from '~/composables/useCurrency'
import PropertyCard from '~/components/PropertyCard.vue'
import InteractiveMap from '~/components/InteractiveMap.vue'

const route = useRoute()
const { properties, fetchProperties } = useProperties()

const viewMode = ref<'grid' | 'split'>('grid')
const sortBy = ref('newest')
const mobileFilterOpen = ref(false)

const filters = reactive({
  keyword: '',
  listingType: '',
  propertyType: '',
  state: '',
  areaName: '',
  maxPrice: 200000000,
  bedrooms: '',
  rajukOnly: false,
  openHouseOnly: false
})

const applyRouteQuery = () => {
  if (route.query.q) filters.keyword = String(route.query.q).toLowerCase()
  if (route.query.type) filters.propertyType = String(route.query.type)
  if (route.query.state) filters.state = String(route.query.state)
  if (route.query.area) filters.areaName = String(route.query.area)
  if (route.query.listingType) filters.listingType = String(route.query.listingType)
  if (route.query.maxPrice) filters.maxPrice = Number(route.query.maxPrice)
}

onMounted(async () => {
  applyRouteQuery()
  await fetchProperties()
})

watch(() => route.query, () => {
  applyRouteQuery()
})

const resetFilters = () => {
  filters.keyword = ''
  filters.listingType = ''
  filters.propertyType = ''
  filters.state = ''
  filters.areaName = ''
  filters.maxPrice = 200000000
  filters.bedrooms = ''
  filters.rajukOnly = false
  filters.openHouseOnly = false
}

const filteredProperties = computed(() => {
  return properties.value.filter(p => {
    if (p.status === 'Draft' || p.status === 'Delisted') return false
    if (filters.listingType && p.listingType !== filters.listingType) return false
    if (filters.propertyType && p.propertyType !== filters.propertyType) return false
    if (filters.state && p.state !== filters.state) return false
    if (filters.areaName && !p.areaName.toLowerCase().includes(filters.areaName.toLowerCase())) return false
    if (p.price > filters.maxPrice) return false
    if (filters.bedrooms && p.bedrooms < Number(filters.bedrooms)) return false
    if (filters.rajukOnly && !p.isRajukApproved) return false
    if (filters.openHouseOnly && !p.hasOpenHouse) return false
    if (filters.keyword) {
      const match = p.title.toLowerCase().includes(filters.keyword) ||
                    p.address.toLowerCase().includes(filters.keyword) ||
                    p.areaName.toLowerCase().includes(filters.keyword) ||
                    p.city.toLowerCase().includes(filters.keyword)
      if (!match) return false
    }
    return true
  }).sort((a, b) => {
    if (sortBy.value === 'price_asc') return a.price - b.price
    if (sortBy.value === 'price_desc') return b.price - a.price
    if (sortBy.value === 'area_desc') return (b.squareFootage || 0) - (a.squareFootage || 0)
    return b.id - a.id
  })
})
</script>

<style scoped>
.mobile-filter-toggle {
  display: none;
}

@media (max-width: 992px) {
  .mobile-filter-toggle {
    display: inline-flex;
  }
  .properties-layout-grid {
    grid-template-columns: 1fr !important;
  }
  .properties-sidebar-card {
    display: none;
  }
  .properties-sidebar-card.mobile-open {
    display: block;
    animation: fadeIn 0.25s ease;
  }
}
</style>

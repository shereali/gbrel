<template>
  <div class="search-box-wrapper animate-fade-in-up">
    <!-- Search Intent Tabs -->
    <div class="search-tabs">
      <button 
        v-for="tab in tabs" 
        :key="tab.id"
        class="search-tab-btn"
        :class="{ active: activeTab === tab.id }"
        @click="selectTab(tab.id)"
      >
        {{ tab.label }}
      </button>
    </div>

    <!-- Inputs Row -->
    <div class="search-inputs-grid">
      <!-- 1. Location / Keyword Search -->
      <div class="search-input-field">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="2">
          <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
          <circle cx="12" cy="10" r="3"/>
        </svg>
        <input 
          v-model="locationQuery"
          type="text" 
          :placeholder="searchPlaceholder" 
          @keyup.enter="handleSearch"
        />
      </div>

      <!-- 2. Property Type Selector -->
      <div class="search-input-field">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#64748B" stroke-width="2">
          <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
        </svg>
        <select v-model="selectedType">
          <option value="">All Property Types</option>
          <option value="Flat">Flat / Apartment</option>
          <option value="Plot">Residential Plot (Katha)</option>
          <option value="Land">Commercial / Agro Land (Bigha)</option>
          <option value="Hotel">Hotel & Beach Resort</option>
          <option value="Duplex">Luxury Duplex / Penthouse</option>
          <option value="Commercial">Commercial Office / Space</option>
        </select>
      </div>

      <!-- 3. Division / State Selector -->
      <div class="search-input-field">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#64748B" stroke-width="2">
          <circle cx="12" cy="12" r="10"/>
          <line x1="2" y1="12" x2="22" y2="12"/>
          <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>
        </svg>
        <select v-model="selectedState">
          <option value="">All Divisions</option>
          <option value="Dhaka North">Dhaka North (Gulshan, Banani, Uttara)</option>
          <option value="Dhaka South">Dhaka South (Dhanmondi, Motijheel)</option>
          <option value="Chittagong">Chittagong & Cox's Bazar</option>
          <option value="Sylhet">Sylhet & Sreemangal</option>
        </select>
      </div>

      <!-- 4. Price Bracket (BDT) -->
      <div class="search-input-field">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#D4AF37" stroke-width="2">
          <line x1="12" y1="1" x2="12" y2="23"/>
          <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
        </svg>
        <select v-model="selectedPriceMax">
          <option value="">Max Budget (BDT)</option>
          <option value="10000000">Up to ৳ 1.00 Crore</option>
          <option value="30000000">Up to ৳ 3.00 Crore</option>
          <option value="50000000">Up to ৳ 5.00 Crore</option>
          <option value="100000000">Up to ৳ 10.00 Crore</option>
          <option value="200000000">Above ৳ 10.00 Crore</option>
        </select>
      </div>

      <!-- Search CTA Button -->
      <button class="btn btn-emerald search-submit-btn" @click="handleSearch">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
          <circle cx="11" cy="11" r="8"/>
          <line x1="21" y1="21" x2="16.65" y2="16.65"/>
        </svg>
        <span>Search</span>
      </button>
    </div>

    <!-- Quick Location Chips -->
    <div class="hero-quick-chips">
      <span class="chip-label">
        <svg class="chip-label-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
          <path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/>
        </svg>
        <span>Trending Searches:</span>
      </span>
      <button type="button" class="quick-chip" @click="quickSearch('Gulshan')">Gulshan Penthouses</button>
      <button type="button" class="quick-chip" @click="quickSearch('Purbachal')">Purbachal Sector 17 Plots</button>
      <button type="button" class="quick-chip" @click="quickSearch('Bashundhara')">Bashundhara Block M</button>
      <button type="button" class="quick-chip" @click="quickSearch('Marine Drive')">Cox's Bazar Hotel Suites</button>
      <button type="button" class="quick-chip" @click="quickSearch('Dhanmondi')">Dhanmondi Duplexes</button>
      <button type="button" class="quick-chip" @click="quickSearch('Sreemangal')">Sylhet Tea Resorts</button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()

const tabs = [
  { id: 'sale', label: 'Sale Properties' },
  { id: 'buy', label: 'Buy Properties' },
  { id: 'lease', label: 'Rent / Commercial Lease' },
  { id: 'plots', label: 'Lands & Plots (Katha/Bigha)' },
  { id: 'resorts', label: 'Hotels & Resorts' }
]

const activeTab = ref('sale')
const locationQuery = ref('')
const selectedType = ref('')
const selectedState = ref('')
const selectedPriceMax = ref('')

const searchPlaceholder = computed(() => {
  if (activeTab.value === 'sale') return 'Search properties for sale (e.g. Gulshan, Banani, Purbachal...)'
  if (activeTab.value === 'buy') return 'Search properties to buy (e.g. Dhanmondi, Bashundhara...)'
  if (activeTab.value === 'lease') return 'Search rentals & commercial leases (e.g. Motijheel...)'
  if (activeTab.value === 'plots') return 'Search plots & lands (e.g. Purbachal Sector 17...)'
  if (activeTab.value === 'resorts') return 'Search resorts & hotel suites (e.g. Cox\'s Bazar...)'
  return 'Location (e.g. Gulshan, Purbachal, Cox\'s Bazar...)'
})

const selectTab = (tabId: string) => {
  activeTab.value = tabId
  if (tabId === 'plots') {
    selectedType.value = 'Plot'
  } else if (tabId === 'resorts') {
    selectedType.value = 'Hotel'
  } else {
    selectedType.value = ''
  }
}

const handleSearch = () => {
  const query: Record<string, string> = {}
  if (locationQuery.value) query.q = locationQuery.value
  if (selectedType.value) query.type = selectedType.value
  if (selectedState.value) query.state = selectedState.value
  if (selectedPriceMax.value) query.maxPrice = selectedPriceMax.value
  if (activeTab.value === 'sale' || activeTab.value === 'buy') query.listingType = 'Sale'
  if (activeTab.value === 'lease') query.listingType = 'Lease'

  router.push({ path: '/properties', query })
}

const quickSearch = (term: string) => {
  locationQuery.value = term
  handleSearch()
}
</script>

<style scoped>
.hero-quick-chips {
  display: flex;
  align-items: center;
  justify-content: flex-start;
  flex-wrap: wrap;
  gap: 8px;
  margin-top: 14px;
  padding: 12px 14px 4px;
  border-top: 1px solid rgba(15, 23, 42, 0.08);
}

.chip-label {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: 0.82rem;
  color: #334155;
  font-weight: 700;
  letter-spacing: -0.01em;
  margin-right: 4px;
  flex-shrink: 0;
}

.chip-label-icon {
  color: #D97706;
  flex-shrink: 0;
}

.quick-chip {
  padding: 6px 13px;
  background: #F1F5F9;
  border: 1px solid #CBD5E1;
  border-radius: var(--radius-full);
  color: #1E293B;
  font-size: 0.8rem;
  font-weight: 600;
  cursor: pointer;
  transition: all var(--transition-fast);
  white-space: nowrap;
}

.quick-chip:hover {
  background: #0A1128;
  border-color: #0A1128;
  color: #FFFFFF;
  box-shadow: 0 4px 12px rgba(10, 17, 40, 0.2);
  transform: translateY(-1px);
}

.quick-chip:active {
  transform: translateY(0);
}

@media (max-width: 640px) {
  .hero-quick-chips {
    justify-content: center;
    padding: 10px 4px 4px;
  }
}
</style>

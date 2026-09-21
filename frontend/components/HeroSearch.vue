<template>
  <div class="search-box-wrapper animate-fade-in-up">
    <!-- Search Intent Tabs -->
    <div class="search-tabs" role="tablist">
      <button 
        v-for="tab in tabs" 
        :key="tab.id"
        role="tab"
        :aria-selected="activeTab === tab.id"
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
      <div class="search-input-field location-field">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="2" aria-hidden="true">
          <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
          <circle cx="12" cy="10" r="3"/>
        </svg>
        <input 
          v-model="locationQuery"
          type="text" 
          :placeholder="searchPlaceholder" 
          aria-label="Location or project name"
          @keyup.enter="handleSearch"
        />
      </div>

      <!-- 2. Property Type Selector -->
      <div class="search-input-field select-field">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#64748B" stroke-width="2" aria-hidden="true">
          <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
        </svg>
        <select v-model="selectedType" aria-label="Property Type">
          <option value="">All Types</option>
          <option value="Flat">Flat / Apartment</option>
          <option value="Plot">Residential Plot (Katha)</option>
          <option value="Land">Commercial / Agro Land</option>
          <option value="Hotel">Hotel & Beach Resort</option>
          <option value="Duplex">Luxury Duplex / Villa</option>
          <option value="Commercial">Commercial Office</option>
        </select>
      </div>

      <!-- 3. Division / State Selector -->
      <div class="search-input-field select-field">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#64748B" stroke-width="2" aria-hidden="true">
          <circle cx="12" cy="12" r="10"/>
          <line x1="2" y1="12" x2="22" y2="12"/>
          <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>
        </svg>
        <select v-model="selectedState" aria-label="Division">
          <option value="">All Divisions</option>
          <option value="Dhaka North">Dhaka North</option>
          <option value="Dhaka South">Dhaka South</option>
          <option value="Chittagong">Chittagong & Cox's Bazar</option>
          <option value="Sylhet">Sylhet & Sreemangal</option>
        </select>
      </div>

      <!-- 4. Price Bracket (BDT) -->
      <div class="search-input-field select-field">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#D4AF37" stroke-width="2" aria-hidden="true">
          <line x1="12" y1="1" x2="12" y2="23"/>
          <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
        </svg>
        <select v-model="selectedPriceMax" aria-label="Max Budget">
          <option value="">Max Budget</option>
          <option value="10000000">Up to ৳ 1.00 Cr</option>
          <option value="30000000">Up to ৳ 3.00 Cr</option>
          <option value="50000000">Up to ৳ 5.00 Cr</option>
          <option value="100000000">Up to ৳ 10.00 Cr</option>
          <option value="200000000">Above ৳ 10.00 Cr</option>
        </select>
      </div>

      <!-- Search CTA Button -->
      <button class="btn btn-emerald search-submit-btn" @click="handleSearch" aria-label="Search properties">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
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
  { id: 'lease', label: 'Rent / Lease' },
  { id: 'plots', label: 'Plots & Land' },
  { id: 'resorts', label: 'Hotels & Resorts' }
]

const activeTab = ref('sale')
const locationQuery = ref('')
const selectedType = ref('')
const selectedState = ref('')
const selectedPriceMax = ref('')

const searchPlaceholder = computed(() => {
  if (activeTab.value === 'sale') return 'Search properties for sale (e.g. Gulshan, Purbachal...)'
  if (activeTab.value === 'buy') return 'Search properties to buy (e.g. Dhanmondi, Bashundhara...)'
  if (activeTab.value === 'lease') return 'Search rentals & leases (e.g. Banani, Motijheel...)'
  if (activeTab.value === 'plots') return 'Search plots & lands (e.g. Purbachal, Jalshiri...)'
  if (activeTab.value === 'resorts') return 'Search resorts & hotel suites (e.g. Cox\'s Bazar...)'
  return 'Location, sector, or project name...'
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
.search-box-wrapper {
  background: #FFFFFF;
  border-radius: var(--radius-xl);
  padding: 14px;
  box-shadow: 0 25px 55px -12px rgba(10, 17, 40, 0.35);
  border: 1px solid rgba(255, 255, 255, 0.5);
  text-align: left;
  width: 100%;
  max-width: 100%;
  box-sizing: border-box;
}

/* 1. Tabs - Clean Segmented Control */
.search-tabs {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 5px;
  background: #F1F5F9;
  border-radius: var(--radius-lg);
  margin-bottom: 12px;
  width: 100%;
  box-sizing: border-box;
  /* Eliminate native horizontal scrollbars across all operating systems and browsers */
  overflow-x: auto;
  overflow-y: hidden;
  scrollbar-width: none; /* Firefox */
  -ms-overflow-style: none; /* IE / Edge */
}

.search-tabs::-webkit-scrollbar {
  display: none; /* Chrome / Safari / Edge */
  width: 0;
  height: 0;
}

.search-tab-btn {
  flex: 1 1 0;
  min-width: max-content;
  padding: 9px 16px;
  border-radius: var(--radius-md);
  font-weight: 700;
  font-size: 0.86rem;
  color: #475569;
  background: transparent;
  cursor: pointer;
  transition: all var(--transition-fast);
  white-space: nowrap;
  text-align: center;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

.search-tab-btn:hover {
  color: #0A1128;
  background: rgba(15, 23, 42, 0.05);
}

.search-tab-btn.active {
  background: #0A1128;
  color: #FFFFFF;
  box-shadow: 0 4px 12px rgba(10, 17, 40, 0.2);
}

/* 2. Inputs Grid */
.search-inputs-grid {
  display: grid;
  grid-template-columns: 2.2fr 1.3fr 1.3fr 1.2fr auto;
  gap: 10px;
  padding: 4px;
  align-items: center;
  width: 100%;
  box-sizing: border-box;
}

.search-input-field {
  display: flex;
  align-items: center;
  gap: 8px;
  background: #FFFFFF;
  border: 1.5px solid var(--color-border);
  border-radius: var(--radius-md);
  padding: 9px 12px;
  min-width: 0;
  box-sizing: border-box;
  transition: border-color var(--transition-fast), box-shadow var(--transition-fast);
}

.search-input-field:focus-within {
  border-color: #059669;
  box-shadow: 0 0 0 3px rgba(5, 150, 105, 0.15);
}

.search-input-field svg {
  flex-shrink: 0;
}

.search-input-field input, 
.search-input-field select {
  width: 100%;
  min-width: 0;
  border: none;
  background: transparent;
  color: var(--color-text-main);
  font-weight: 500;
  font-size: 0.9rem;
  outline: none;
  cursor: pointer;
  text-overflow: ellipsis;
}

.search-input-field input {
  cursor: text;
}

.search-submit-btn {
  height: 48px;
  padding: 0 24px;
  border-radius: var(--radius-md);
  font-size: 0.92rem;
  font-weight: 700;
  white-space: nowrap;
  flex-shrink: 0;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
}

/* 3. Quick Chips */
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

/* 4. Responsive Breakpoints */
@media (max-width: 1100px) {
  .search-inputs-grid {
    grid-template-columns: 1fr 1fr;
    gap: 10px;
  }
  .search-input-field:first-child {
    grid-column: 1 / -1;
  }
  .search-submit-btn {
    grid-column: 1 / -1;
    width: 100%;
  }
}

@media (max-width: 880px) {
  .search-tabs {
    flex-wrap: wrap;
    gap: 6px;
  }

  .search-tab-btn {
    flex: 1 1 calc(33.333% - 6px);
    padding: 8px 12px;
    font-size: 0.82rem;
  }
}

@media (max-width: 640px) {
  .search-box-wrapper {
    padding: 12px 10px;
  }

  .search-inputs-grid {
    grid-template-columns: 1fr;
    gap: 8px;
  }

  .search-input-field:first-child {
    grid-column: auto;
  }

  .search-submit-btn {
    grid-column: auto;
  }

  .hero-quick-chips {
    justify-content: center;
    padding: 10px 4px 4px;
  }
}

@media (max-width: 580px) {
  .search-tab-btn {
    flex: 1 1 calc(50% - 6px);
    padding: 8px 10px;
    font-size: 0.8rem;
  }
}
</style>

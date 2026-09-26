<template>
  <div class="pl">
    <header class="pl-head">
      <div class="gb-wrap">
        <nav class="pl-crumb" aria-label="Breadcrumb"><NuxtLink to="/">হোম</NuxtLink><span aria-hidden="true">/</span><span>প্রপার্টি</span></nav>
        <div class="pl-titlerow">
          <div>
            <h1>{{ heading }}</h1>
            <p class="pl-count" aria-live="polite">
              <template v-if="isLoading && !properties.length">তালিকা আসছে…</template>
              <template v-else>{{ toBn(filteredProperties.length) }}টি প্রপার্টি পাওয়া গেছে</template>
            </p>
          </div>
          <form class="pl-search" role="search" @submit.prevent>
            <Search :size="18" aria-hidden="true" />
            <label class="sr-only" for="pl-q">এলাকা, প্রকল্প বা শিরোনাম</label>
            <input id="pl-q" v-model="filters.keyword" type="search" placeholder="এলাকা, প্রকল্প বা শিরোনাম" autocomplete="off" />
          </form>
        </div>

        <div class="pl-tabs" role="tablist" aria-label="প্রপার্টির ধরন">
          <button v-for="tab in typeTabs" :key="tab.key" type="button" role="tab" :aria-selected="filters.propertyType === tab.key" class="pl-tab" :class="{ on: filters.propertyType === tab.key }" @click="filters.propertyType = tab.key">
            <span class="pl-dot" :style="{ background: tab.color }" aria-hidden="true"></span>
            {{ tab.label }}
            <small>{{ toBn(typeCount(tab.key)) }}</small>
          </button>
        </div>
      </div>
    </header>

    <div class="gb-wrap pl-body">
      <button type="button" class="pl-filter-toggle gb-btn gb-btn--line gb-btn--sm" :aria-expanded="mobileFilterOpen" aria-controls="pl-filters" @click="mobileFilterOpen = !mobileFilterOpen">
        <SlidersHorizontal :size="16" aria-hidden="true" />
        {{ mobileFilterOpen ? 'ফিল্টার লুকান' : 'ফিল্টার' }}<template v-if="activeFilterCount"> ({{ toBn(activeFilterCount) }})</template>
      </button>

      <aside id="pl-filters" class="pl-filters" :class="{ open: mobileFilterOpen }" aria-label="ফিল্টার">
        <div class="pl-filters-head">
          <h2>ফিল্টার</h2>
          <button v-if="activeFilterCount" type="button" class="pl-reset" @click="resetFilters">সব মুছুন</button>
        </div>

        <fieldset class="pl-fs">
          <legend>উদ্দেশ্য</legend>
          <div class="pl-seg">
            <button v-for="o in listingOptions" :key="o.value" type="button" :aria-pressed="filters.listingType === o.value" :class="{ on: filters.listingType === o.value }" @click="filters.listingType = o.value">{{ o.label }}</button>
          </div>
        </fieldset>

        <label class="gb-field">
          <span>বিভাগ / অঞ্চল</span>
          <select v-model="filters.state" class="gb-select">
            <option value="">সব অঞ্চল</option>
            <option v-for="s in stateOptions" :key="s" :value="s">{{ stateLabels[s] || s }}</option>
          </select>
        </label>

        <label class="gb-field">
          <span>বাজেট (সর্বোচ্চ)</span>
          <select v-model.number="filters.maxPrice" class="gb-select">
            <option :value="0">যেকোনো বাজেট</option>
            <option v-for="b in budgetOptions" :key="b" :value="b">{{ priceBn(b) }} পর্যন্ত</option>
          </select>
        </label>

        <label class="gb-field">
          <span>বেডরুম (ফ্ল্যাট/বাড়ি)</span>
          <select v-model="filters.bedrooms" class="gb-select">
            <option value="">যেকোনো</option>
            <option v-for="n in [1, 2, 3, 4]" :key="n" :value="String(n)">{{ toBn(n) }}টি বা বেশি</option>
          </select>
        </label>

        <fieldset class="pl-fs">
          <legend>অন্যান্য</legend>
          <label class="pl-check"><input v-model="filters.readyOnly" type="checkbox" /> শুধু রেডি প্রপার্টি</label>
          <label class="pl-check"><input v-model="filters.rajukOnly" type="checkbox" /> রাজউক/সিডিএ অনুমোদিত উল্লেখ আছে</label>
          <label class="pl-check"><input v-model="filters.hideSold" type="checkbox" /> বিক্রি হয়ে যাওয়াগুলো লুকান</label>
        </fieldset>

        <div class="pl-help">
          <p>যা খুঁজছেন তা পাচ্ছেন না?</p>
          <NuxtLink to="/contact" class="gb-link">আপনার চাহিদা জানান</NuxtLink>
        </div>
      </aside>

      <section class="pl-results" aria-label="ফলাফল">
        <div class="pl-toolbar">
          <div class="pl-view" role="group" aria-label="দেখার ধরন">
            <button type="button" :aria-pressed="viewMode === 'grid'" :class="{ on: viewMode === 'grid' }" @click="viewMode = 'grid'"><LayoutGrid :size="16" aria-hidden="true" /> তালিকা</button>
            <button type="button" :aria-pressed="viewMode === 'split'" :class="{ on: viewMode === 'split' }" @click="viewMode = 'split'"><MapIcon :size="16" aria-hidden="true" /> ম্যাপ</button>
          </div>
          <label class="pl-sort">
            <span>সাজান</span>
            <select v-model="sortBy" class="gb-select">
              <option value="newest">নতুন আগে</option>
              <option value="price_asc">দাম: কম থেকে বেশি</option>
              <option value="price_desc">দাম: বেশি থেকে কম</option>
              <option value="area_desc">আয়তন: বড় আগে</option>
            </select>
          </label>
        </div>

        <ClientOnly v-if="viewMode === 'split'">
          <div class="pl-map"><InteractiveMap :properties="filteredProperties" /></div>
        </ClientOnly>

        <div v-if="filteredProperties.length" class="pl-grid">
          <PropertyCard v-for="prop in filteredProperties" :key="prop.id" :property="prop" />
        </div>
        <div v-else-if="isLoading" class="pl-grid" aria-busy="true">
          <div v-for="n in 4" :key="n" class="pl-skel"></div>
        </div>
        <div v-else class="pl-empty">
          <h2>এই ফিল্টারে কোনো প্রপার্টি নেই</h2>
          <p>বাজেট বা অঞ্চল বদলে দেখুন, অথবা সব ফিল্টার মুছে আবার খুঁজুন। আপনার চাহিদা জানালে নতুন প্রপার্টি এলে আমরা জানাব।</p>
          <div class="pl-empty-actions">
            <button type="button" class="gb-btn gb-btn--paddy" @click="resetFilters">সব ফিল্টার মুছুন</button>
            <NuxtLink to="/contact" class="gb-btn gb-btn--line">চাহিদা জানান</NuxtLink>
          </div>
        </div>
      </section>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, computed, onMounted, watch } from 'vue'
import { useRoute } from 'vue-router'
import { LayoutGrid, Map as MapIcon, Search, SlidersHorizontal } from 'lucide-vue-next'
import { useProperties, type PropertyItem } from '~/composables/useProperties'
import PropertyCard from '~/components/PropertyCard.vue'
import InteractiveMap from '~/components/InteractiveMap.vue'
import { askingPriceSummary } from '~/utils/buyerDetails.mjs'
import { priceBn, toBn } from '~/utils/propertyLabels'

const route = useRoute()
const { properties, fetchProperties, isLoading } = useProperties()

const viewMode = ref<'grid' | 'split'>('grid')
const sortBy = ref('newest')
const mobileFilterOpen = ref(false)

const typeTabs = [
  { key: '', label: 'সব', color: 'transparent' },
  { key: 'Plot,Land', label: 'জমি ও প্লট', color: '#B9D08F' },
  { key: 'Land Share', label: 'জমি শেয়ার', color: '#8FB366' },
  { key: 'Flat', label: 'ফ্ল্যাট', color: '#5C924A' },
  { key: 'Duplex,Penthouse', label: 'ডুপ্লেক্স ও পেন্টহাউস', color: '#3A7234' },
  { key: 'Hotel,Commercial', label: 'রিসোর্ট ও বাণিজ্যিক', color: '#1D4A2A' }
]
const listingOptions = [{ value: '', label: 'সব' }, { value: 'Sale', label: 'কিনতে' }, { value: 'Lease', label: 'ভাড়া/লিজ' }]
const stateLabels: Record<string, string> = { 'Dhaka North': 'ঢাকা উত্তর', 'Dhaka South': 'ঢাকা দক্ষিণ', Dhaka: 'ঢাকা', Chittagong: 'চট্টগ্রাম', Chattogram: 'চট্টগ্রাম', Sylhet: 'সিলেট', "Cox's Bazar": 'কক্সবাজার', Rajshahi: 'রাজশাহী', Khulna: 'খুলনা', Barishal: 'বরিশাল', Rangpur: 'রংপুর', Mymensingh: 'ময়মনসিংহ' }
const budgetOptions = [2500000, 5000000, 10000000, 20000000, 50000000, 100000000]

const filters = reactive({
  keyword: '',
  listingType: '',
  propertyType: '',
  state: '',
  areaName: '',
  maxPrice: 0,
  bedrooms: '',
  rajukOnly: false,
  readyOnly: false,
  hideSold: false
})

const normalizeType = (t: string) => {
  // Older links used single types (?type=Plot); map them onto the grouped tabs.
  const groups: Record<string, string> = { Plot: 'Plot,Land', Land: 'Plot,Land', Duplex: 'Duplex,Penthouse', Penthouse: 'Duplex,Penthouse', Hotel: 'Hotel,Commercial', Commercial: 'Hotel,Commercial' }
  return groups[t] || t
}

const applyRouteQuery = () => {
  filters.keyword = route.query.q ? String(route.query.q) : ''
  filters.propertyType = route.query.type ? normalizeType(String(route.query.type)) : ''
  filters.state = route.query.state ? String(route.query.state) : ''
  filters.areaName = route.query.area ? String(route.query.area) : ''
  filters.listingType = route.query.listingType ? String(route.query.listingType) : ''
  filters.maxPrice = route.query.maxPrice ? Number(route.query.maxPrice) || 0 : 0
}

onMounted(async () => { applyRouteQuery(); await fetchProperties() })
watch(() => route.query, applyRouteQuery)

const resetFilters = () => {
  Object.assign(filters, { keyword: '', listingType: '', propertyType: '', state: '', areaName: '', maxPrice: 0, bedrooms: '', rajukOnly: false, readyOnly: false, hideSold: false })
}

const publicProps = computed(() => properties.value.filter(p => p.status !== 'Draft' && p.status !== 'Delisted'))
const stateOptions = computed(() => [...new Set(publicProps.value.map(p => p.state).filter(Boolean))])
const matchesType = (p: PropertyItem, key: string) => !key || key.split(',').includes(p.propertyType)
const typeCount = (key: string) => publicProps.value.filter(p => matchesType(p, key)).length
const priceOf = (p: PropertyItem) => Number(askingPriceSummary(p).amount) || p.price || 0

const activeFilterCount = computed(() => [filters.listingType, filters.state, filters.areaName, filters.maxPrice, filters.bedrooms, filters.rajukOnly, filters.readyOnly, filters.hideSold].filter(Boolean).length)

const heading = computed(() => {
  if (filters.areaName) return `${filters.areaName}-এ প্রপার্টি`
  const tab = typeTabs.find(t => t.key === filters.propertyType)
  return tab && tab.key ? tab.label : 'প্রপার্টি খুঁজুন'
})

const filteredProperties = computed(() => {
  const kw = filters.keyword.trim().toLowerCase()
  return publicProps.value.filter(p => {
    if (filters.listingType && p.listingType !== filters.listingType) return false
    if (!matchesType(p, filters.propertyType)) return false
    if (filters.state && p.state !== filters.state) return false
    if (filters.areaName && !(p.areaName || '').toLowerCase().includes(filters.areaName.toLowerCase())) return false
    if (filters.maxPrice && !p.hidePrice && priceOf(p) > filters.maxPrice) return false
    if (filters.bedrooms && p.bedrooms < Number(filters.bedrooms)) return false
    if (filters.rajukOnly && !p.isRajukApproved) return false
    if (filters.readyOnly && p.completionStatus !== 'Ready') return false
    if (filters.hideSold && p.status === 'Sold') return false
    if (kw) {
      const hay = [p.title, p.address, p.areaName, p.city, p.state].filter(Boolean).join(' ').toLowerCase()
      if (!hay.includes(kw)) return false
    }
    return true
  }).sort((a, b) => {
    if (sortBy.value === 'price_asc') return priceOf(a) - priceOf(b)
    if (sortBy.value === 'price_desc') return priceOf(b) - priceOf(a)
    if (sortBy.value === 'area_desc') return (b.squareFootage || 0) - (a.squareFootage || 0)
    return b.id - a.id
  })
})

useSeoMeta({
  title: () => `${heading.value} | গ্রাম বাংলা রিয়েল এস্টেট`,
  description: 'জমি, প্লট, জমি শেয়ার ও ফ্ল্যাটের তালিকা — দাম, আয়তন ও লোকেশন দেখে বেছে নিন।'
})
</script>

<style scoped>
.pl { padding-bottom: 88px; }
.pl-head { padding: 28px 0 0; border-bottom: 1px solid var(--gb-silt); background: var(--gb-paper); }
.pl-crumb { display: flex; gap: 8px; font-size: .88rem; color: var(--gb-ink-soft); margin-bottom: 8px; }
.pl-crumb a { color: var(--gb-leaf); }
.pl-titlerow { display: flex; justify-content: space-between; align-items: flex-end; gap: 24px; flex-wrap: wrap; }
.pl-head h1 { font-size: var(--gb-t-h1); font-weight: 800; font-stretch: 112%; }
.pl-count { color: var(--gb-ink-soft); margin-top: 4px; }
.pl-search { display: flex; align-items: center; gap: 8px; min-width: min(380px, 100%); background: var(--gb-sheet); border: 1.5px solid var(--gb-silt); border-radius: 999px; padding: 0 18px; color: var(--gb-leaf); }
.pl-search:focus-within { border-color: var(--gb-leaf); }
.pl-search input { flex: 1; min-height: 48px; background: transparent; font-size: 1rem; color: var(--gb-ink); }

.pl-tabs { display: flex; gap: 4px; overflow-x: auto; margin-top: 24px; scrollbar-width: none; }
.pl-tabs::-webkit-scrollbar { display: none; }
.pl-tab { display: inline-flex; align-items: center; gap: 8px; white-space: nowrap; padding: 12px 16px 14px; background: transparent; color: var(--gb-ink-soft); font-family: var(--gb-display); font-size: 1.02rem; font-weight: 500; cursor: pointer; border-bottom: 3px solid transparent; }
.pl-tab:hover { color: var(--gb-paddy); }
.pl-tab.on { color: var(--gb-paddy); font-weight: 700; border-bottom-color: var(--gb-sun); }
.pl-tab small { font-family: var(--gb-body); font-size: .78rem; color: var(--gb-ink-soft); background: rgba(168,197,123,.28); border-radius: 999px; padding: 0 8px; }
.pl-dot { width: 10px; height: 10px; border-radius: 50%; }
.pl-tab:first-child .pl-dot { display: none; }

.pl-body { display: grid; grid-template-columns: 270px minmax(0, 1fr); gap: 36px; padding-top: 32px; align-items: start; }
.pl-filter-toggle { display: none; }
.pl-filters { position: sticky; top: 100px; display: flex; flex-direction: column; gap: 20px; }
.pl-filters-head { display: flex; justify-content: space-between; align-items: baseline; }
.pl-filters-head h2 { font-size: 1.3rem; font-weight: 700; }
.pl-reset { background: none; color: #A23B16; font-weight: 600; cursor: pointer; text-decoration: underline; text-underline-offset: 3px; }
.pl-fs { border: 0; display: flex; flex-direction: column; gap: 8px; }
.pl-fs legend { font-size: var(--gb-t-small); font-weight: 600; color: var(--gb-paddy); margin-bottom: 6px; }
.pl-seg { display: grid; grid-template-columns: repeat(3, 1fr); background: #fff; border: 1.5px solid var(--gb-silt); border-radius: 999px; padding: 3px; }
.pl-seg button { min-height: 40px; border-radius: 999px; background: transparent; color: var(--gb-ink); cursor: pointer; font-size: .95rem; }
.pl-seg button.on { background: var(--gb-paddy); color: #fff; }
.pl-check { display: flex; align-items: flex-start; gap: 10px; font-size: .95rem; cursor: pointer; line-height: 1.5; }
.pl-check input { width: 18px; height: 18px; margin-top: 3px; accent-color: var(--gb-paddy); flex-shrink: 0; }
.pl-help { border-top: 1px dashed var(--gb-silt); padding-top: 16px; font-size: .95rem; }

.pl-toolbar { display: flex; justify-content: space-between; align-items: center; gap: 16px; flex-wrap: wrap; margin-bottom: 20px; }
.pl-view { display: inline-flex; background: #fff; border: 1.5px solid var(--gb-silt); border-radius: 999px; padding: 3px; }
.pl-view button { display: inline-flex; align-items: center; gap: 6px; min-height: 38px; padding: 0 16px; border-radius: 999px; background: transparent; color: var(--gb-ink); cursor: pointer; }
.pl-view button.on { background: var(--gb-paddy); color: #fff; }
.pl-sort { display: flex; align-items: center; gap: 10px; font-size: .92rem; color: var(--gb-ink-soft); }
.pl-sort .gb-select { width: auto; min-height: 42px; }
.pl-map { margin-bottom: 24px; border-radius: var(--gb-r-lg); overflow: hidden; }

.pl-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 24px; }
.pl-skel { min-height: 380px; border-radius: var(--gb-r-lg); background: #E6EBDD; }
.pl-empty { background: var(--gb-sheet); border: 1.5px dashed var(--gb-silt); border-radius: var(--gb-r-lg); padding: 40px 32px; }
.pl-empty h2 { font-size: 1.5rem; margin-bottom: 8px; }
.pl-empty p { color: var(--gb-ink-soft); margin-bottom: 20px; }
.pl-empty-actions { display: flex; flex-wrap: wrap; gap: 12px; }

@media (max-width: 960px) {
  .pl-body { grid-template-columns: 1fr; gap: 16px; padding-top: 20px; }
  .pl-filter-toggle { display: inline-flex; justify-self: start; }
  .pl-filters { display: none; position: static; background: var(--gb-sheet); border: 1px solid var(--gb-silt); border-radius: var(--gb-r-lg); padding: 20px; }
  .pl-filters.open { display: flex; }
}
@media (max-width: 560px) {
  .pl-search { min-width: 100%; }
  .pl-sort span { display: none; }
}
</style>

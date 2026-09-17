<template>
  <div class="admin-page animate-fade-in">
    <!-- 1. Executive Dashboard Header -->
    <div class="admin-header-row">
      <div>
        <h1 class="page-title">Executive Intelligence & KPI Analytics</h1>
        <p class="page-subtitle">Real-time portfolio valuation, regional capital allocation, and transaction pipeline across Bangladesh.</p>
      </div>
      <div class="admin-header-actions">
        <button class="btn btn-sm btn-outline-white" @click="exportReport" title="Export Executive Audit Report">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3"/>
          </svg>
          <span>Export Report</span>
        </button>
        <NuxtLink to="/admin/properties" class="btn btn-sm btn-emerald">
          <span>Property Catalog →</span>
        </NuxtLink>
      </div>
    </div>

    <!-- 2. Top 4 KPI Metric Cards (Responsive Grid) -->
    <div class="kpi-grid">
      <!-- KPI 1: Portfolio Valuation -->
      <div class="kpi-card">
        <div class="kpi-header">
          <span class="kpi-label">Total Asset Valuation</span>
          <span class="kpi-icon-pill emerald">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <line x1="12" y1="1" x2="12" y2="23"/>
              <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
            </svg>
          </span>
        </div>
        <div class="kpi-value text-emerald">৳ {{ totalPortfolioCrores }} Cr</div>
        <div class="kpi-sub">
          <span style="color:#10B981; font-weight:700;">↑ +18.2%</span> vs last quarter in 4 divisions
        </div>
      </div>

      <!-- KPI 2: Active Mandates -->
      <div class="kpi-card">
        <div class="kpi-header">
          <span class="kpi-label">Active Mandates</span>
          <span class="kpi-icon-pill gold">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
              <polyline points="9 22 9 12 15 12 15 22"/>
            </svg>
          </span>
        </div>
        <div class="kpi-value text-gold">{{ properties.length }} Assets</div>
        <div class="kpi-sub">
          {{ flatsCount }} Flats • {{ plotsCount }} Plots • {{ resortsCount }} Resorts
        </div>
      </div>

      <!-- KPI 3: VIP Inspections -->
      <div class="kpi-card">
        <div class="kpi-header">
          <span class="kpi-label">VIP Site Viewings</span>
          <span class="kpi-icon-pill blue">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect x="3" y="4" width="18" height="18" rx="2"/>
              <line x1="16" y1="2" x2="16" y2="6"/>
              <line x1="8" y1="2" x2="8" y2="6"/>
              <line x1="3" y1="10" x2="21" y2="10"/>
            </svg>
          </span>
        </div>
        <div class="kpi-value text-blue">{{ viewingsCount }} Tours</div>
        <div class="kpi-sub">
          Gulshan, Purbachal & Inani Beach
        </div>
      </div>

      <!-- KPI 4: Active CRM Leads -->
      <div class="kpi-card">
        <div class="kpi-header">
          <span class="kpi-label">Active CRM Leads</span>
          <span class="kpi-icon-pill rose">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M22 12h-6l-2 3h-4l-2-3H2"/>
              <path d="M5.45 5.11 2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"/>
            </svg>
          </span>
        </div>
        <div class="kpi-value text-rose">{{ leadsCount }} Leads</div>
        <div class="kpi-sub">
          Avg 14.5 min response SLA
        </div>
      </div>
    </div>

    <!-- 3. Regional Allocation & Urgent Queue (Split Grid) -->
    <div class="split-grid">
      <!-- Regional Capital Allocation -->
      <div class="panel-card">
        <div class="panel-header">
          <div>
            <h3 class="panel-title">Regional Capital Allocation (BDT)</h3>
            <p class="panel-sub">Asset distribution across prime Bangladeshi corridors</p>
          </div>
        </div>

        <div class="regional-bars-list">
          <div v-for="r in regionalStats" :key="r.key" class="regional-bar-item">
            <div class="region-row-header">
              <span class="region-name"><strong>{{ r.name }}</strong> {{ r.sub }}</span>
              <span class="region-val" :class="r.textClass"><strong>৳ {{ r.valCrores }} Cr ({{ r.pct }}%)</strong></span>
            </div>
            <div class="progress-track">
              <div class="progress-fill" :style="{ width: r.pct + '%', background: r.color }"></div>
            </div>
          </div>
        </div>
      </div>

      <!-- Urgent Review & Action Queue -->
      <div class="panel-card">
        <div class="panel-header flex justify-between items-center">
          <div>
            <h3 class="panel-title">Urgent Review & Action Queue</h3>
            <p class="panel-sub">Pending title checks and high-priority logistics</p>
          </div>
          <NuxtLink to="/admin/approvals" class="btn btn-sm btn-outline-white">View All</NuxtLink>
        </div>

        <div class="action-queue-list">
          <div class="action-queue-item border-gold">
            <div class="action-queue-content">
              <span class="action-icon-badge gold">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                  <polyline points="14 2 14 8 20 8"/>
                  <line x1="9" y1="13" x2="15" y2="13"/>
                </svg>
              </span>
              <div class="action-text">
                <strong class="action-title">7.5 Katha Plot in Purbachal Sector 20</strong>
                <div class="action-meta">Seller: Kazi Rashed • Asking: ৳ 2.70 Cr • Allotment Uploaded</div>
              </div>
            </div>
            <NuxtLink to="/admin/approvals" class="btn btn-sm btn-gold action-btn">
              <span>Verify Deed</span>
            </NuxtLink>
          </div>

          <div class="action-queue-item border-blue">
            <div class="action-queue-content">
              <span class="action-icon-badge blue">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M5 17h14M5 17l-2 4M19 17l2 4M12 11a3 3 0 1 0 0-6 3 3 0 0 0 0 6zM5 17v-3a7 7 0 0 1 14 0v3"/>
                </svg>
              </span>
              <div class="action-text">
                <strong class="action-title">VIP Chauffeur Pickup Assigned</strong>
                <div class="action-meta">Dr. Kabir Hossain • Airport to Purbachal Sector 17</div>
              </div>
            </div>
            <NuxtLink to="/admin/viewings" class="btn btn-sm btn-outline-white action-btn">
              <span>Logistics</span>
            </NuxtLink>
          </div>
        </div>
      </div>
    </div>

    <!-- 4. Recently Added Property Mandates (Dual View: Desktop Table + Mobile Cards) -->
    <div class="panel-card" style="padding: 0; overflow: hidden;">
      <div class="panel-header-padded">
        <div>
          <h3 class="panel-title">Recently Added Property Mandates</h3>
          <p class="panel-sub">Latest inventory added across residential and commercial sectors</p>
        </div>
        <NuxtLink to="/admin/properties" class="btn btn-sm btn-outline-white">Full Inventory →</NuxtLink>
      </div>

      <!-- Desktop & Tablet Table View (> 768px) -->
      <div class="desktop-table-view">
        <div class="table-responsive">
          <table class="admin-table">
            <thead>
              <tr>
                <th>Property Name</th>
                <th>Category</th>
                <th>Location</th>
                <th>Price (BDT)</th>
                <th>Verification</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="p in properties.slice(0, 4)" :key="'dt-' + p.id">
                <td>
                  <strong style="color:#FFF;">{{ p.title }}</strong>
                </td>
                <td><span class="badge badge-status">{{ p.propertyType }}</span></td>
                <td style="color:#CBD5E1;">{{ p.areaName }}, {{ p.city }}</td>
                <td style="font-weight:800; color:#10B981;">{{ formatBDT(p.price) }}</td>
                <td>
                  <span v-if="p.isRajukApproved" class="badge badge-rajuk">RAJUK Pass</span>
                  <span v-else class="badge badge-status">Municipal</span>
                </td>
                <td>
                  <span class="badge badge-status">{{ p.status }}</span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Mobile Mandates Card View (<= 768px) -->
      <div class="mobile-mandates-list">
        <div v-for="p in properties.slice(0, 4)" :key="'m-' + p.id" class="mobile-mandate-card">
          <div class="flex justify-between items-start gap-2">
            <div style="flex: 1; min-width: 0;">
              <span class="badge badge-status" style="margin-bottom: 6px; font-size: 0.7rem;">{{ p.propertyType }}</span>
              <h4 class="mobile-mandate-title">{{ p.title }}</h4>
              <div class="mobile-mandate-location">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                  <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                  <circle cx="12" cy="10" r="3"/>
                </svg>
                <span>{{ p.areaName }}, {{ p.city }}</span>
              </div>
            </div>
            <span class="badge" :class="p.isRajukApproved ? 'badge-rajuk' : 'badge-status'" style="flex-shrink: 0; font-size: 0.7rem;">
              {{ p.isRajukApproved ? 'RAJUK' : 'Municipal' }}
            </span>
          </div>

          <div class="mobile-mandate-footer">
            <div>
              <span class="val-label">Valuation</span>
              <div class="val-num">{{ formatBDT(p.price) }}</div>
            </div>
            <span class="badge badge-status" style="font-size: 0.72rem;">{{ p.status }}</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useProperties } from '~/composables/useProperties'
import { formatBDT } from '~/composables/useCurrency'
import { useToast } from '~/composables/useToast'
import { useApiUrl } from '~/composables/useApi'

definePageMeta({
  layout: 'admin'
})

const { properties, fetchProperties } = useProperties()
const toast = useToast()

const leadsCount = ref(4)
const viewingsCount = ref(4)

const fetchStats = async () => {
  try {
    const res = await fetch(useApiUrl('/admin/stats'))
    if (res.ok) {
      const data = await res.json()
      if (data && data.success) {
        if (typeof data.leads_count === 'number') leadsCount.value = data.leads_count
        if (typeof data.viewings_count === 'number') viewingsCount.value = data.viewings_count
      }
    }
  } catch (err) {
    console.error('Failed to fetch admin stats:', err)
  }
}

onMounted(async () => {
  await fetchProperties()
  await fetchStats()
})

const totalPortfolioCrores = computed(() => {
  const totalBDT = properties.value.reduce((acc, p) => acc + p.price, 0)
  return (totalBDT / 10000000).toFixed(1)
})

const flatsCount = computed(() => properties.value.filter(p => p.propertyType === 'Flat' || p.propertyType === 'Penthouse' || p.propertyType === 'Duplex').length)
const plotsCount = computed(() => properties.value.filter(p => p.propertyType === 'Plot' || p.propertyType === 'Land').length)
const resortsCount = computed(() => properties.value.filter(p => p.propertyType === 'Hotel').length)

const regionalStats = computed(() => {
  const total = properties.value.reduce((acc, p) => acc + (Number(p.price) || 0), 0) || 1
  const regions = [
    { key: 'Dhaka North', name: 'Dhaka North', sub: '(Gulshan, Banani, Purbachal)', color: '#10B981', textClass: 'text-emerald' },
    { key: 'Dhaka South', name: 'Dhaka South', sub: '(Dhanmondi, Jalshiri, Keraniganj)', color: '#3B82F6', textClass: 'text-blue' },
    { key: 'Chittagong', name: "Chittagong & Cox's Bazar", sub: '(Marine Drive)', color: '#D4AF37', textClass: 'text-gold' },
    { key: 'Sylhet', name: 'Sylhet Division', sub: '(Sreemangal Tea Estates)', color: '#EC4899', textClass: 'text-pink' }
  ]

  return regions.map(r => {
    const val = properties.value
      .filter(p => (p.state || '').toLowerCase().includes(r.key.toLowerCase()))
      .reduce((acc, p) => acc + (Number(p.price) || 0), 0)
    const pct = Math.round((val / total) * 100)
    const cr = (val / 10000000).toFixed(1)
    return {
      ...r,
      valCrores: cr,
      pct: pct || 0
    }
  })
})

const exportReport = () => {
  try {
    const headers = ['ID', 'Title', 'Category', 'Division', 'Area', 'Price (BDT)', 'Status', 'RAJUK Approved']
    const rows = properties.value.map(p => [
      p.id,
      `"${p.title.replace(/"/g, '""')}"`,
      p.propertyType,
      p.state,
      `"${p.areaName}"`,
      p.price,
      p.status,
      p.isRajukApproved ? 'Yes' : 'No'
    ])
    
    const csvContent = [
      `GBREL Executive Asset Valuation & Audit Report - Generated on ${new Date().toISOString()}`,
      `Total Assets: ${properties.value.length}, Valuation: BDT ${totalPortfolioCrores.value} Crore`,
      '',
      headers.join(','),
      ...rows.map(r => r.join(','))
    ].join('\r\n')

    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' })
    const url = URL.createObjectURL(blob)
    const link = document.createElement('a')
    link.setAttribute('href', url)
    link.setAttribute('download', `GBREL_Portfolio_Audit_${new Date().toISOString().slice(0, 10)}.csv`)
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)
    URL.revokeObjectURL(url)

    toast.success('Executive Audit Report Downloaded', `Successfully exported ${properties.value.length} assets to CSV.`)
  } catch (err: any) {
    toast.error('Export Failed', err.message || 'Unable to generate report.')
  }
}
</script>

<style scoped>
/* Split Grid (Regional Allocation & Urgent Queue) */
.split-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 20px;
  margin-bottom: 24px;
}

.panel-header-padded {
  padding: 20px 22px 14px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 12px;
  border-bottom: 1px solid var(--admin-border-subtle);
}

.regional-bars-list {
  display: flex;
  flex-direction: column;
  gap: 16px;
  margin-top: 14px;
}

.region-row-header {
  display: flex;
  justify-content: space-between;
  align-items: baseline;
  flex-wrap: wrap;
  gap: 6px;
  font-size: 0.84rem;
  margin-bottom: 6px;
}

.region-name {
  color: var(--admin-text-secondary);
}

.progress-track {
  width: 100%;
  height: 8px;
  background: var(--admin-border-subtle);
  border-radius: 4px;
  overflow: hidden;
}

.progress-fill {
  height: 100%;
  border-radius: 4px;
}

.action-queue-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
  margin-top: 14px;
}

.action-queue-item {
  background: var(--admin-bg-surface-alt);
  border-left: 3px solid #D4AF37;
  border-radius: var(--radius-md);
  padding: 14px 16px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 14px;
}

.action-queue-item.border-blue {
  border-left-color: #60A5FA;
}

.action-queue-content {
  display: flex;
  align-items: center;
  gap: 12px;
  min-width: 0;
}

.action-icon-badge {
  width: 36px;
  height: 36px;
  border-radius: 8px;
  background: var(--admin-border-subtle);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.action-icon-badge.gold { color: #D4AF37; }
.action-icon-badge.blue { color: #60A5FA; }

.action-text {
  min-width: 0;
}

.action-title {
  color: var(--admin-text-primary);
  font-size: 0.9rem;
  display: block;
  line-height: 1.3;
  font-weight: 700;
}

.action-meta {
  font-size: 0.76rem;
  color: var(--admin-text-muted);
  margin-top: 2px;
  line-height: 1.3;
}

.action-btn {
  flex-shrink: 0;
}

/* Mobile Mandates View */
.desktop-table-view {
  display: block;
}

.mobile-mandates-list {
  display: none;
  padding: 14px;
  gap: 12px;
}

.mobile-mandate-card {
  background: var(--admin-bg-surface-alt);
  border: 1px solid var(--admin-border-subtle);
  border-radius: var(--radius-lg);
  padding: 14px;
}

.mobile-mandate-title {
  font-size: 0.9rem;
  font-weight: 700;
  color: var(--admin-text-primary);
  line-height: 1.3;
  margin-bottom: 4px;
}

.mobile-mandate-location {
  display: flex;
  align-items: center;
  gap: 5px;
  font-size: 0.78rem;
  color: var(--admin-text-muted);
}

.mobile-mandate-footer {
  display: flex;
  justify-content: space-between;
  align-items: flex-end;
  margin-top: 10px;
  padding-top: 10px;
  border-top: 1px solid var(--admin-border-subtle);
}

.val-label {
  display: block;
  font-size: 0.68rem;
  color: var(--admin-text-muted);
  text-transform: uppercase;
  letter-spacing: 0.03em;
}

.val-num {
  font-weight: 800;
  font-size: 1.05rem;
  color: #10B981;
}

@media (max-width: 1024px) {
  .split-grid {
    grid-template-columns: 1fr;
    gap: 16px;
  }
}

@media (max-width: 768px) {
  .desktop-table-view {
    display: none;
  }
  .mobile-mandates-list {
    display: flex;
    flex-direction: column;
  }
}

@media (max-width: 640px) {
  .action-queue-item {
    flex-direction: column;
    align-items: stretch;
  }
  .action-btn {
    width: 100%;
    justify-content: center;
    min-height: 40px;
  }
}
</style>

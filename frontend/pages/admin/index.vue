<template>
  <div class="admin-page animate-fade-in">
    <!-- 1. Executive Dashboard Header -->
    <div class="admin-header-row">
      <div>
        <div class="header-pretitle">
          <span class="pulse-dot"></span>
          <span>GBREL Executive Management Platform</span>
          <span class="live-pill">MySQL Realtime Synced</span>
        </div>
        <h1 class="page-title">Executive Intelligence & KPI Analytics</h1>
        <p class="page-subtitle">Real-time portfolio valuation, regional capital allocation, and transaction pipeline across Bangladesh.</p>
      </div>
      <div class="admin-header-actions">
        <button class="btn btn-sm btn-outline-white" :disabled="refreshing" @click="refreshAll" title="Refresh Live Analytics">
          <svg class="refresh-icon" :class="{ 'animate-spin': refreshing }" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path d="M21.5 2v6h-6M2.5 22v-6h6M2 11.5a10 10 0 0 1 18.8-4.3M22 12.5a10 10 0 0 1-18.8 4.2"/>
          </svg>
          <span>{{ refreshing ? 'Syncing...' : 'Refresh KPIs' }}</span>
        </button>
        <button class="btn btn-sm btn-outline-white" @click="exportReport" title="Export Executive Audit Report">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3"/>
          </svg>
          <span>Export CSV</span>
        </button>
        <NuxtLink to="/admin/properties/create" class="btn btn-sm btn-gold">
          <span>+ Add Property</span>
        </NuxtLink>
      </div>
    </div>

    <!-- 2. Top 4 KPI Metric Cards (Responsive Grid) -->
    <div class="kpi-grid">
      <!-- KPI 1: Portfolio Valuation -->
      <div class="kpi-card kpi-card-emerald">
        <div class="kpi-header">
          <span class="kpi-label">Total Portfolio Valuation</span>
          <span class="kpi-icon-pill emerald">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <line x1="12" y1="1" x2="12" y2="23"/>
              <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
            </svg>
          </span>
        </div>
        <div class="kpi-value text-emerald">৳ {{ totalPortfolioCrores }} Cr</div>
        <div class="kpi-sub">
          <span class="kpi-badge-positive">● Live Database</span> Across {{ regionalStats.length }} administrative regions
        </div>
      </div>

      <!-- KPI 2: Active Properties -->
      <div class="kpi-card kpi-card-gold">
        <div class="kpi-header">
          <span class="kpi-label">Active Property Assets</span>
          <span class="kpi-icon-pill gold">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
              <polyline points="9 22 9 12 15 12 15 22"/>
            </svg>
          </span>
        </div>
        <div class="kpi-value text-gold">{{ properties.length }} Assets</div>
        <div class="kpi-sub">
          <strong>{{ landSharesCount }}</strong> Land Shares • <strong>{{ flatsCount }}</strong> Flats • <strong>{{ plotsCount }}</strong> Plots
        </div>
      </div>

      <!-- KPI 3: VIP Inspections -->
      <div class="kpi-card kpi-card-blue">
        <div class="kpi-header">
          <span class="kpi-label">VIP Physical Viewings</span>
          <span class="kpi-icon-pill blue">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect x="3" y="4" width="18" height="18" rx="2"/>
              <line x1="16" y1="2" x2="16" y2="6"/>
              <line x1="8" y1="2" x2="8" y2="6"/>
              <line x1="3" y1="10" x2="21" y2="10"/>
            </svg>
          </span>
        </div>
        <div class="kpi-value text-blue">{{ viewingsCount }} Tours</div>
        <div class="kpi-sub">
          <strong>{{ confirmedViewingsCount }}</strong> Confirmed • <strong>{{ pendingViewingsCount }}</strong> In Scheduling
        </div>
      </div>

      <!-- KPI 4: Active CRM Leads -->
      <div class="kpi-card kpi-card-rose">
        <div class="kpi-header">
          <span class="kpi-label">Client Leads & Inquiries</span>
          <span class="kpi-icon-pill rose">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M22 12h-6l-2 3h-4l-2-3H2"/>
              <path d="M5.45 5.11 2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"/>
            </svg>
          </span>
        </div>
        <div class="kpi-value text-rose">{{ leadsCount }} Inquiries</div>
        <div class="kpi-sub">
          <strong>{{ activeLeadsCount }}</strong> Active Inquiries • High-Net-Worth & NRB Investors
        </div>
      </div>
    </div>

    <!-- 3. Regional Allocation & Urgent Queue (Split Grid) -->
    <div class="split-grid">
      <!-- Regional Capital Allocation -->
      <div class="panel-card">
        <div class="panel-header-custom">
          <div>
            <h3 class="panel-title">Regional Capital Allocation (BDT)</h3>
            <p class="panel-sub">Asset distribution across prime divisions</p>
          </div>
          <span class="panel-header-counter">{{ regionalStats.length }} Active Regions</span>
        </div>

        <div class="regional-bars-list">
          <div v-for="r in regionalStats" :key="r.region" class="regional-bar-item">
            <div class="region-row-header">
              <span class="region-name">
                <span class="region-dot" :style="{ background: r.color }"></span>
                <strong>{{ r.region }}</strong>
                <span class="region-count-chip">{{ r.count }} {{ r.count === 1 ? 'asset' : 'assets' }}</span>
              </span>
              <span class="region-val" :style="{ color: r.color }">
                <strong>৳ {{ r.valCrores }} Cr</strong> ({{ r.percentage }}%)
              </span>
            </div>
            <div class="progress-track">
              <div class="progress-fill" :style="{ width: r.percentage + '%', background: r.gradient }"></div>
            </div>
          </div>
        </div>

        <div class="regional-footer-summary">
          <div class="summary-col">
            <span class="summary-label">Aggregated Valuation</span>
            <span class="summary-num">৳ {{ totalPortfolioCrores }} Crore</span>
          </div>
          <div class="summary-col text-right">
            <span class="summary-label">Highest Concentration</span>
            <span class="summary-num highlight">{{ topRegionName }}</span>
          </div>
        </div>
      </div>

      <!-- Urgent Review & Action Queue (Fully Redesigned & 100% Dynamic) -->
      <div class="panel-card action-queue-panel">
        <div class="panel-header-custom">
          <div>
            <div style="display: flex; align-items: center; gap: 8px;">
              <h3 class="panel-title">Urgent Review & Action Queue</h3>
              <span class="urgent-pulse-badge">
                <span class="pulse-ring"></span>
                <span class="pulse-core"></span>
                <span>{{ urgentActionsList.length }} Urgent</span>
              </span>
            </div>
            <p class="panel-sub">Live pending title checks, VIP chauffeur tours, and buyer leads</p>
          </div>
          <NuxtLink to="/admin/approvals" class="btn btn-xs btn-outline-white">
            <span>View All Approvals →</span>
          </NuxtLink>
        </div>

        <!-- Filter Chips for Action Queue -->
        <div class="action-filter-pills">
          <button 
            type="button" 
            class="filter-pill" 
            :class="{ active: activeQueueFilter === 'all' }" 
            @click="activeQueueFilter = 'all'"
          >
            All ({{ urgentActionsList.length }})
          </button>
          <button 
            type="button" 
            class="filter-pill" 
            :class="{ active: activeQueueFilter === 'legal' }" 
            @click="activeQueueFilter = 'legal'"
          >
            ⚖️ Title & RAJUK ({{ unapprovedList.length }})
          </button>
          <button 
            type="button" 
            class="filter-pill" 
            :class="{ active: activeQueueFilter === 'viewings' }" 
            @click="activeQueueFilter = 'viewings'"
          >
            🚗 VIP Tours ({{ recentViewingsList.length }})
          </button>
          <button 
            type="button" 
            class="filter-pill" 
            :class="{ active: activeQueueFilter === 'leads' }" 
            @click="activeQueueFilter = 'leads'"
          >
            💬 Buyer Leads ({{ recentLeadsList.length }})
          </button>
        </div>

        <!-- Dynamic Action Items List -->
        <div v-if="filteredActionQueue.length > 0" class="action-queue-list">
          <div 
            v-for="item in filteredActionQueue" 
            :key="item.id" 
            class="action-queue-card" 
            :class="item.cardClass"
          >
            <div class="action-card-top-row">
              <div class="action-type-pill" :class="item.typeClass">
                <span>{{ item.typeLabel }}</span>
              </div>
              <span class="action-priority-badge" :class="item.priorityClass">
                {{ item.priorityText }}
              </span>
            </div>

            <div class="action-card-main">
              <div class="action-icon-box" :class="item.iconClass">
                <!-- Legal / Deed Icon -->
                <svg v-if="item.iconType === 'deed'" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                  <polyline points="14 2 14 8 20 8"/>
                  <line x1="9" y1="13" x2="15" y2="13"/>
                  <line x1="9" y1="17" x2="13" y2="17"/>
                </svg>
                <!-- Viewing / Car Icon -->
                <svg v-else-if="item.iconType === 'tour'" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <rect x="3" y="4" width="18" height="18" rx="2"/>
                  <line x1="16" y1="2" x2="16" y2="6"/>
                  <line x1="8" y1="2" x2="8" y2="6"/>
                  <line x1="3" y1="10" x2="21" y2="10"/>
                </svg>
                <!-- Lead / User Icon -->
                <svg v-else width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                  <circle cx="12" cy="7" r="4"/>
                </svg>
              </div>

              <div class="action-text-block">
                <div class="action-heading">{{ item.title }}</div>
                <div class="action-meta-tags">
                  <span v-for="(tag, idx) in item.tags" :key="idx" class="meta-tag">
                    {{ tag }}
                  </span>
                </div>
              </div>
            </div>

            <div class="action-card-footer">
              <span class="action-timestamp">{{ item.timeLabel }}</span>
              <NuxtLink :to="item.targetLink" class="btn btn-sm action-cta-btn" :class="item.btnClass">
                <span>{{ item.actionButtonText }}</span>
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                  <line x1="5" y1="12" x2="19" y2="12"/>
                  <polyline points="12 5 19 12 12 19"/>
                </svg>
              </NuxtLink>
            </div>
          </div>
        </div>

        <!-- Clean Empty State if no actions -->
        <div v-else class="action-queue-empty">
          <div class="empty-icon-shield">✔</div>
          <h4 class="empty-title">Queue Fully Cleared</h4>
          <p class="empty-desc">There are no pending title reviews, tour assignments, or unhandled leads in this filter.</p>
        </div>
      </div>
    </div>

    <!-- 4. Recently Added Properties (Dual View: Desktop Table + Mobile Cards) -->
    <div class="panel-card" style="padding: 0; overflow: hidden; margin-top: 24px;">
      <div class="panel-header-padded">
        <div>
          <h3 class="panel-title">Recently Added Properties</h3>
          <p class="panel-sub">Real-time inventory added to MySQL across residential and commercial sectors</p>
        </div>
        <NuxtLink to="/admin/properties" class="btn btn-sm btn-outline-white">Full Inventory ({{ properties.length }}) →</NuxtLink>
      </div>

      <!-- Desktop & Tablet Table View (> 768px) -->
      <div class="desktop-table-view">
        <div class="table-responsive">
          <table class="admin-table">
            <thead>
              <tr>
                <th>Property Name</th>
                <th>Category</th>
                <th>Location / Division</th>
                <th>Price (BDT)</th>
                <th>Legal Vetting</th>
                <th>Status</th>
                <th style="text-align: right;">Action</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="p in properties.slice(0, 5)" :key="'dt-' + p.id">
                <td>
                  <strong style="color:var(--admin-text-primary); font-size:0.92rem;">{{ p.title }}</strong>
                  <div style="font-size:0.75rem; color:var(--admin-text-muted);">ID: #{{ p.id }} • {{ p.beds ? p.beds + ' Beds' : '' }} {{ p.baths ? '• ' + p.baths + ' Baths' : '' }}</div>
                </td>
                <td>
                  <span class="badge badge-property-type" :class="'type-' + (p.propertyType || '').toLowerCase().replace(/\s+/g, '-')">
                    {{ p.propertyType }}
                  </span>
                </td>
                <td style="color:var(--admin-text-secondary);">
                  {{ p.areaName || p.city }}, {{ p.state }}
                </td>
                <td style="font-weight:800; color:#10B981; font-size:0.95rem;">
                  {{ formatBDT(p.price) }}
                </td>
                <td>
                  <span v-if="p.isRajukApproved" class="badge badge-rajuk-pass">
                    ✔ RAJUK Pass
                  </span>
                  <span v-else class="badge badge-rajuk-pending">
                    ⏳ Municipal Vetting
                  </span>
                </td>
                <td>
                  <span class="badge" :class="p.status === 'Active' ? 'badge-status-active' : 'badge-status-subtle'">
                    {{ p.status }}
                  </span>
                </td>
                <td style="text-align: right;">
                  <NuxtLink :to="'/admin/properties?edit=' + p.id" class="btn btn-xs btn-outline-white">
                    <span>Manage</span>
                  </NuxtLink>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Mobile Mandates Card View (<= 768px) -->
      <div class="mobile-mandates-list">
        <div v-for="p in properties.slice(0, 5)" :key="'m-' + p.id" class="mobile-mandate-card">
          <div class="flex justify-between items-start gap-2">
            <div style="flex: 1; min-width: 0;">
              <span class="badge badge-property-type" style="margin-bottom: 6px; font-size: 0.7rem;">{{ p.propertyType }}</span>
              <h4 class="mobile-mandate-title">{{ p.title }}</h4>
              <div class="mobile-mandate-location">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                  <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                  <circle cx="12" cy="10" r="3"/>
                </svg>
                <span>{{ p.areaName || p.city }}, {{ p.state }}</span>
              </div>
            </div>
            <span class="badge" :class="p.isRajukApproved ? 'badge-rajuk-pass' : 'badge-rajuk-pending'" style="flex-shrink: 0; font-size: 0.7rem;">
              {{ p.isRajukApproved ? 'RAJUK' : 'Pending' }}
            </span>
          </div>

          <div class="mobile-mandate-footer">
            <div>
              <span class="val-label">Valuation</span>
              <div class="val-num">{{ formatBDT(p.price) }}</div>
            </div>
            <NuxtLink :to="'/admin/properties?edit=' + p.id" class="btn btn-xs btn-outline-white">
              <span>Edit Asset</span>
            </NuxtLink>
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

useHead({
  title: 'Executive Intelligence & Analytics | GBREL Admin',
  meta: [
    { name: 'description', content: 'GBREL Executive real-time KPI metrics, portfolio asset valuation, and urgent review queue.' }
  ]
})

const { properties, fetchProperties } = useProperties()
const toast = useToast()
const { token } = useAuth()

const refreshing = ref(false)
const activeQueueFilter = ref<'all' | 'legal' | 'viewings' | 'leads'>('all')

const leadsCount = ref(1)
const activeLeadsCount = ref(1)
const viewingsCount = ref(1)
const confirmedViewingsCount = ref(1)
const pendingViewingsCount = ref(0)
const unapprovedList = ref<any[]>([])
const recentViewingsList = ref<any[]>([])
const recentLeadsList = ref<any[]>([])
const backendRegions = ref<any[]>([])

const fetchStats = async () => {
  try {
    const res = await fetch(useApiUrl('/admin/stats'), { headers: { Authorization: `Bearer ${token.value}` } })
    if (res.ok) {
      const data = await res.json()
      if (data && data.success) {
        if (typeof data.leads_count === 'number') leadsCount.value = data.leads_count
        if (typeof data.active_leads_count === 'number') activeLeadsCount.value = data.active_leads_count
        if (typeof data.viewings_count === 'number') viewingsCount.value = data.viewings_count
        if (typeof data.confirmed_viewings_count === 'number') confirmedViewingsCount.value = data.confirmed_viewings_count
        pendingViewingsCount.value = Math.max(0, viewingsCount.value - confirmedViewingsCount.value)
        
        if (Array.isArray(data.unapproved_properties)) unapprovedList.value = data.unapproved_properties
        if (Array.isArray(data.recent_viewings)) recentViewingsList.value = data.recent_viewings
        if (Array.isArray(data.recent_leads)) recentLeadsList.value = data.recent_leads
        if (Array.isArray(data.regional_allocation)) backendRegions.value = data.regional_allocation
      }
    }
  } catch (err) {
    console.error('Failed to fetch admin stats:', err)
  }
}

const refreshAll = async () => {
  refreshing.value = true
  try {
    await Promise.all([fetchProperties(), fetchStats()])
    toast.success('Analytics Updated', 'Refreshed live KPI metrics from MySQL database.')
  } catch (e: any) {
    toast.error('Sync Error', e.message || 'Failed to refresh analytics.')
  } finally {
    refreshing.value = false
  }
}

onMounted(async () => {
  await Promise.all([fetchProperties(), fetchStats()])
})

// Dynamic Computeds
const totalPortfolioCrores = computed(() => {
  const totalBDT = properties.value.reduce((acc, p) => acc + (Number(p.price) || 0), 0)
  return (totalBDT / 10000000).toFixed(1)
})

const landSharesCount = computed(() => properties.value.filter(p => p.propertyType === 'Land Share').length)
const flatsCount = computed(() => properties.value.filter(p => p.propertyType === 'Flat' || p.propertyType === 'Penthouse' || p.propertyType === 'Duplex').length)
const plotsCount = computed(() => properties.value.filter(p => p.propertyType === 'Plot' || p.propertyType === 'Land').length)

// Dynamic Regional Allocation Colors & Stats
const regionColorPalette: Record<string, { color: string; gradient: string }> = {
  'Dhaka North': { color: '#10B981', gradient: 'linear-gradient(90deg, #10B981, #34D399)' },
  'Dhaka South': { color: '#3B82F6', gradient: 'linear-gradient(90deg, #3B82F6, #60A5FA)' },
  'Chittagong': { color: '#F59E0B', gradient: 'linear-gradient(90deg, #E2651C, #F59E0B)' },
  'Sylhet': { color: '#EC4899', gradient: 'linear-gradient(90deg, #EC4899, #F472B6)' },
  'Rajshahi': { color: '#8B5CF6', gradient: 'linear-gradient(90deg, #8B5CF6, #A78BFA)' },
  'Khulna': { color: '#06B6D4', gradient: 'linear-gradient(90deg, #06B6D4, #22D3EE)' },
  'Cox\'s Bazar': { color: '#F97316', gradient: 'linear-gradient(90deg, #F97316, #FB923C)' }
}

const regionalStats = computed(() => {
  const totalBDT = properties.value.reduce((acc, p) => acc + (Number(p.price) || 0), 0) || 1
  
  // Group properties dynamically by state/division
  const grouped: Record<string, { count: number; sum: number }> = {}
  properties.value.forEach(p => {
    const rawState = (p.state || 'Dhaka North').trim()
    if (!grouped[rawState]) {
      grouped[rawState] = { count: 0, sum: 0 }
    }
    grouped[rawState].count += 1
    grouped[rawState].sum += Number(p.price) || 0
  })

  const results = Object.keys(grouped).map(reg => {
    const item = grouped[reg]
    const pct = Math.round((item.sum / totalBDT) * 100)
    const cr = (item.sum / 10000000).toFixed(2)
    const palette = regionColorPalette[reg] || { 
      color: '#A855F7', 
      gradient: 'linear-gradient(90deg, #A855F7, #C084FC)' 
    }
    return {
      region: reg,
      count: item.count,
      valCrores: cr,
      percentage: pct,
      color: palette.color,
      gradient: palette.gradient
    }
  })

  // Sort descending by valuation
  return results.sort((a, b) => Number(b.valCrores) - Number(a.valCrores))
})

const topRegionName = computed(() => {
  if (regionalStats.value.length === 0) return 'Dhaka North'
  return `${regionalStats.value[0].region} (৳ ${regionalStats.value[0].valCrores} Cr)`
})

// Unified, 100% Dynamic Urgent Action Queue Builder
const urgentActionsList = computed(() => {
  const actions: any[] = []

  // 1. Pending RAJUK & Title Deeds from live DB
  unapprovedList.value.forEach((p, idx) => {
    const priceCr = (Number(p.price || 0) / 10000000).toFixed(2)
    actions.push({
      id: 'deed-' + p.id,
      category: 'legal',
      typeLabel: 'LEGAL & DEED VETTING',
      typeClass: 'type-legal',
      priorityText: 'HIGH COMPLIANCE',
      priorityClass: 'priority-high',
      cardClass: 'card-border-gold',
      iconType: 'deed',
      iconClass: 'icon-gold',
      title: p.title,
      tags: [
        `📍 ${p.area_name || p.city || 'Dhaka'}`,
        `💰 Asking: ৳ ${priceCr} Cr`,
        '⏳ Municipal Plan Awaiting RAJUK Clearance'
      ],
      timeLabel: 'Pending Legal Review',
      actionButtonText: 'Verify Deed',
      btnClass: 'btn-gold',
      targetLink: '/admin/approvals'
    })
  })

  // 2. VIP Scheduled Tours from live DB
  recentViewingsList.value.forEach((v) => {
    const scheduledDateStr = v.scheduled_date 
      ? new Date(v.scheduled_date).toLocaleDateString('en-GB', { day: 'numeric', month: 'short' }) 
      : 'Scheduled'
    actions.push({
      id: 'tour-' + v.id,
      category: 'viewings',
      typeLabel: 'VIP TOUR LOGISTICS',
      typeClass: 'type-tour',
      priorityText: v.status ? v.status.toUpperCase() : 'CONFIRMED',
      priorityClass: v.status === 'Confirmed' ? 'priority-confirmed' : 'priority-pending',
      cardClass: 'card-border-blue',
      iconType: 'tour',
      iconClass: 'icon-blue',
      title: `${v.name} • ${v.property_title || 'VIP Inspection'}`,
      tags: [
        `📅 ${scheduledDateStr} (${v.scheduled_time || '3:00 PM'})`,
        `🚗 Pickup: ${v.pickup_location || 'Gulshan Enclave'}`,
        `👤 Advisor: ${v.assigned_agent || 'Tanvir Ahmed'}`
      ],
      timeLabel: `Contact: ${v.phone || v.email}`,
      actionButtonText: 'View Logistics',
      btnClass: 'btn-blue',
      targetLink: '/admin/viewings'
    })
  })

  // 3. High-Priority Client Leads from live DB
  recentLeadsList.value.forEach((l) => {
    const snippet = l.message ? (l.message.length > 55 ? l.message.substring(0, 55) + '...' : l.message) : 'Inquiring on property mandate'
    actions.push({
      id: 'lead-' + l.id,
      category: 'leads',
      typeLabel: 'BUYER CRM INQUIRY',
      typeClass: 'type-lead',
      priorityText: (l.status || 'ACTIVE').toUpperCase(),
      priorityClass: 'priority-active',
      cardClass: 'card-border-emerald',
      iconType: 'lead',
      iconClass: 'icon-emerald',
      title: `${l.name} (${l.lead_type || 'Investor'})`,
      tags: [
        `🏢 ${l.property_title || 'General Inquiries'}`,
        `💬 "${snippet}"`,
        `📞 ${l.phone || l.email}`
      ],
      timeLabel: 'New Inquiry',
      actionButtonText: 'Manage Lead',
      btnClass: 'btn-emerald',
      targetLink: '/admin/leads'
    })
  })

  return actions
})

const filteredActionQueue = computed(() => {
  if (activeQueueFilter.value === 'all') return urgentActionsList.value
  return urgentActionsList.value.filter(a => a.category === activeQueueFilter.value)
})

// Report Export
const exportReport = () => {
  try {
    const headers = ['ID', 'Title', 'Category', 'Division', 'Area', 'Price (BDT)', 'Status', 'RAJUK Approved']
    const rows = properties.value.map(p => [
      p.id,
      `"${p.title.replace(/"/g, '""')}"`,
      p.propertyType,
      p.state,
      `"${p.areaName || ''}"`,
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
/* Header Pretitle & Live Indicators */
.header-pretitle {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  font-size: 0.75rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  color: var(--admin-text-muted);
  margin-bottom: 6px;
}

.pulse-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: #10B981;
  box-shadow: 0 0 8px #10B981;
  animation: pulse-ring 2s infinite;
}

@keyframes pulse-ring {
  0% { transform: scale(0.95); opacity: 0.8; }
  50% { transform: scale(1.2); opacity: 1; }
  100% { transform: scale(0.95); opacity: 0.8; }
}

.live-pill {
  background: rgba(16, 185, 129, 0.12);
  color: #10B981;
  border: 1px solid rgba(16, 185, 129, 0.25);
  padding: 2px 7px;
  border-radius: 20px;
  font-size: 0.68rem;
}

.refresh-icon {
  transition: transform var(--transition-fast);
}

/* KPI Cards Custom Accents */
.kpi-card {
  position: relative;
  transition: transform 0.2s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.2s ease, border-color 0.2s ease;
}

.kpi-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 12px 28px -6px rgba(0, 0, 0, 0.35);
}

.kpi-card-emerald::before { background: linear-gradient(90deg, #10B981, transparent); }
.kpi-card-gold::before { background: linear-gradient(90deg, #E2651C, transparent); }
.kpi-card-blue::before { background: linear-gradient(90deg, #3B82F6, transparent); }
.kpi-card-rose::before { background: linear-gradient(90deg, #F43F5E, transparent); }

.kpi-badge-positive {
  color: #10B981;
  font-weight: 700;
  display: inline-flex;
  align-items: center;
  gap: 3px;
}

/* Split Grid Layout */
.split-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 22px;
  margin-top: 24px;
}

.panel-header-custom {
  padding: 20px 22px 16px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 12px;
  border-bottom: 1px solid var(--admin-border-subtle);
}

.panel-header-counter {
  font-size: 0.75rem;
  font-weight: 700;
  color: var(--admin-text-secondary);
  background: var(--admin-border-subtle);
  padding: 4px 10px;
  border-radius: 20px;
}

/* Regional Bars */
.regional-bars-list {
  padding: 20px 22px;
  display: flex;
  flex-direction: column;
  gap: 18px;
}

.region-row-header {
  display: flex;
  justify-content: space-between;
  align-items: baseline;
  flex-wrap: wrap;
  gap: 6px;
  font-size: 0.86rem;
  margin-bottom: 6px;
}

.region-name {
  color: var(--admin-text-primary);
  display: inline-flex;
  align-items: center;
  gap: 8px;
}

.region-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  flex-shrink: 0;
}

.region-count-chip {
  font-size: 0.72rem;
  background: var(--admin-border-subtle);
  color: var(--admin-text-muted);
  padding: 1px 7px;
  border-radius: 12px;
  font-weight: 600;
}

.progress-track {
  width: 100%;
  height: 10px;
  background: var(--admin-border-subtle);
  border-radius: 6px;
  overflow: hidden;
  box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.2);
}

.progress-fill {
  height: 100%;
  border-radius: 6px;
  transition: width 0.8s cubic-bezier(0.16, 1, 0.3, 1);
}

.regional-footer-summary {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 14px 22px;
  border-top: 1px solid var(--admin-border-subtle);
  background: var(--admin-bg-surface-alt);
  border-radius: 0 0 var(--radius-xl) var(--radius-xl);
}

.summary-label {
  display: block;
  font-size: 0.72rem;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  color: var(--admin-text-muted);
  font-weight: 700;
}

.summary-num {
  font-size: 0.95rem;
  font-weight: 800;
  color: var(--admin-text-primary);
}

.summary-num.highlight {
  color: #E2651C;
}

/* ======================================================== */
/* Urgent Review & Action Queue Card Redesign */
/* ======================================================== */
.action-queue-panel {
  display: flex;
  flex-direction: column;
}

.urgent-pulse-badge {
  position: relative;
  display: inline-flex;
  align-items: center;
  gap: 6px;
  background: rgba(239, 68, 68, 0.12);
  border: 1px solid rgba(239, 68, 68, 0.3);
  color: #EF4444;
  font-size: 0.73rem;
  font-weight: 700;
  padding: 3px 9px;
  border-radius: 20px;
  line-height: 1.2;
}

.pulse-ring {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background: #EF4444;
  box-shadow: 0 0 6px #EF4444;
}

.action-filter-pills {
  display: flex;
  gap: 8px;
  padding: 12px 22px;
  border-bottom: 1px solid var(--admin-border-subtle);
  overflow-x: auto;
  scrollbar-width: none;
}

.action-filter-pills::-webkit-scrollbar {
  display: none;
}

.filter-pill {
  background: transparent;
  border: 1px solid var(--admin-border-subtle);
  color: var(--admin-text-secondary);
  font-size: 0.76rem;
  font-weight: 600;
  padding: 4px 10px;
  border-radius: 20px;
  cursor: pointer;
  white-space: nowrap;
  transition: all 0.15s ease;
}

.filter-pill:hover {
  background: rgba(255, 255, 255, 0.05);
  color: var(--admin-text-primary);
}

.filter-pill.active {
  background: var(--admin-text-primary);
  color: var(--admin-bg-base);
  border-color: var(--admin-text-primary);
}

.action-queue-list {
  padding: 16px 22px;
  display: flex;
  flex-direction: column;
  gap: 12px;
  overflow-y: auto;
  max-height: 480px;
}

/* Individual Queue Card */
.action-queue-card {
  background: var(--admin-bg-surface-alt);
  border: 1px solid var(--admin-border-subtle);
  border-radius: var(--radius-lg);
  padding: 14px 16px;
  display: flex;
  flex-direction: column;
  gap: 10px;
  position: relative;
  transition: transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease;
}

.action-queue-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 20px -4px rgba(0, 0, 0, 0.35);
}

.action-queue-card.card-border-gold {
  border-left: 4px solid #E2651C;
}

.action-queue-card.card-border-blue {
  border-left: 4px solid #3B82F6;
}

.action-queue-card.card-border-emerald {
  border-left: 4px solid #10B981;
}

.action-card-top-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.action-type-pill {
  font-size: 0.68rem;
  font-weight: 800;
  letter-spacing: 0.06em;
  text-transform: uppercase;
  padding: 2px 7px;
  border-radius: 4px;
}

.action-type-pill.type-legal {
  background: rgba(226, 101, 28, 0.14);
  color: #FBBF24;
}

.action-type-pill.type-tour {
  background: rgba(59, 130, 246, 0.14);
  color: #60A5FA;
}

.action-type-pill.type-lead {
  background: rgba(16, 185, 129, 0.14);
  color: #34D399;
}

.action-priority-badge {
  font-size: 0.68rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.04em;
  padding: 2px 6px;
  border-radius: 4px;
}

.priority-high {
  background: rgba(239, 68, 68, 0.15);
  color: #F87171;
  border: 1px solid rgba(239, 68, 68, 0.3);
}

.priority-confirmed {
  background: rgba(59, 130, 246, 0.15);
  color: #93C5FD;
  border: 1px solid rgba(59, 130, 246, 0.3);
}

.priority-pending {
  background: rgba(245, 158, 11, 0.15);
  color: #FCD34D;
  border: 1px solid rgba(245, 158, 11, 0.3);
}

.priority-active {
  background: rgba(16, 185, 129, 0.15);
  color: #6EE7B7;
  border: 1px solid rgba(16, 185, 129, 0.3);
}

.action-card-main {
  display: flex;
  align-items: flex-start;
  gap: 12px;
}

.action-icon-box {
  width: 38px;
  height: 38px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.action-icon-box.icon-gold {
  background: rgba(226, 101, 28, 0.15);
  color: #E2651C;
}

.action-icon-box.icon-blue {
  background: rgba(59, 130, 246, 0.15);
  color: #60A5FA;
}

.action-icon-box.icon-emerald {
  background: rgba(16, 185, 129, 0.15);
  color: #10B981;
}

.action-text-block {
  flex: 1;
  min-width: 0;
}

.action-heading {
  font-size: 0.92rem;
  font-weight: 700;
  color: var(--admin-text-primary);
  line-height: 1.3;
  margin-bottom: 5px;
}

.action-meta-tags {
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
}

.meta-tag {
  font-size: 0.74rem;
  color: var(--admin-text-secondary);
  background: rgba(255, 255, 255, 0.04);
  padding: 2px 7px;
  border-radius: 4px;
  display: inline-flex;
  align-items: center;
  line-height: 1.3;
}

.action-card-footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding-top: 10px;
  margin-top: 4px;
  border-top: 1px solid rgba(255, 255, 255, 0.05);
}

.action-timestamp {
  font-size: 0.74rem;
  color: var(--admin-text-muted);
}

.action-cta-btn {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  font-weight: 700;
  padding: 6px 14px;
  border-radius: 6px;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
}

.btn-blue {
  background: #2563EB;
  color: #FFFFFF;
  border: 1px solid #3B82F6;
}

.btn-blue:hover {
  background: #1D4ED8;
  color: #FFFFFF;
}

/* Empty Queue State */
.action-queue-empty {
  padding: 40px 20px;
  text-align: center;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
}

.empty-icon-shield {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  background: rgba(16, 185, 129, 0.15);
  color: #10B981;
  font-size: 1.4rem;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 12px;
}

.empty-title {
  font-size: 1.05rem;
  font-weight: 700;
  color: var(--admin-text-primary);
  margin-bottom: 4px;
}

.empty-desc {
  font-size: 0.82rem;
  color: var(--admin-text-muted);
  max-width: 340px;
  line-height: 1.4;
}

/* Recently Added Table Styling */
.badge-property-type {
  font-size: 0.72rem;
  font-weight: 700;
  padding: 3px 8px;
  border-radius: 4px;
  background: rgba(255, 255, 255, 0.06);
  color: var(--admin-text-primary);
  border: 1px solid var(--admin-border-subtle);
}

.badge-rajuk-pass {
  background: rgba(16, 185, 129, 0.15);
  color: #10B981;
  border: 1px solid rgba(16, 185, 129, 0.3);
  font-size: 0.72rem;
  font-weight: 700;
  padding: 3px 8px;
  border-radius: 4px;
}

.badge-rajuk-pending {
  background: rgba(245, 158, 11, 0.15);
  color: #F59E0B;
  border: 1px solid rgba(245, 158, 11, 0.3);
  font-size: 0.72rem;
  font-weight: 700;
  padding: 3px 8px;
  border-radius: 4px;
}

.badge-status-active {
  background: rgba(16, 185, 129, 0.12);
  color: #34D399;
  border: 1px solid rgba(16, 185, 129, 0.25);
  font-size: 0.72rem;
  font-weight: 700;
  padding: 3px 8px;
  border-radius: 4px;
}

.badge-status-subtle {
  background: rgba(148, 163, 184, 0.12);
  color: #8E9B8F;
  border: 1px solid rgba(148, 163, 184, 0.25);
  font-size: 0.72rem;
  font-weight: 600;
  padding: 3px 8px;
  border-radius: 4px;
}

/* Light Mode Overrides for Action Queue & Cards */
.admin-theme-light .action-queue-card {
  background: #FFFFFF;
  border-color: #D6DDCB;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04);
}

.admin-theme-light .action-queue-card:hover {
  box-shadow: 0 8px 18px rgba(0, 0, 0, 0.08);
}

.admin-theme-light .meta-tag {
  background: #EDF1E6;
  color: #4A5A4E;
}

.admin-theme-light .action-card-footer {
  border-top-color: #EDF1E6;
}

.admin-theme-light .regional-footer-summary {
  background: #F3F5EC;
  border-top-color: #D6DDCB;
}

/* Responsive Media Queries */
@media (max-width: 1024px) {
  .split-grid {
    grid-template-columns: 1fr;
    gap: 18px;
  }
}

@media (max-width: 768px) {
  .desktop-table-view {
    display: none;
  }
  .mobile-mandates-list {
    display: flex;
    flex-direction: column;
    padding: 14px;
    gap: 12px;
  }
}

@media (max-width: 640px) {
  .action-card-footer {
    flex-direction: column;
    align-items: stretch;
    gap: 8px;
  }
  .action-cta-btn {
    width: 100%;
    justify-content: center;
    min-height: 38px;
  }
}
</style>

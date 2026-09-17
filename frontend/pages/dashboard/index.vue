<template>
  <div style="background: #F8FAFC; min-height: 100vh; padding: 40px 0 80px;">
    <div class="container">
      <!-- Dashboard Header -->
      <div style="background: #0A1128; border-radius: var(--radius-xl); padding: 32px; color: #FFFFFF; margin-bottom: 32px; box-shadow: var(--shadow-lg);">
        <div class="flex items-center justify-between flex-wrap gap-4">
          <div class="flex items-center gap-4">
            <img :src="user.avatar" :alt="user.name" style="width: 72px; height: 72px; border-radius: 50%; object-fit: cover; border: 3px solid var(--color-gold);" />
            <div>
              <div class="flex items-center gap-2">
                <h1 style="font-size: 1.85rem; font-weight: 800; color: #FFFFFF;">{{ user.name }}</h1>
                <span class="badge badge-featured" style="text-transform: uppercase;">{{ user.role }} View</span>
              </div>
              <p style="color: #CBD5E1; font-size: 0.9rem; margin-top: 2px;">{{ user.email }} • {{ user.phone }}</p>
            </div>
          </div>

          <!-- Quick Role Switcher -->
          <div class="flex items-center gap-2">
            <span style="font-size: 0.85rem; color: #CBD5E1;">Test Role:</span>
            <button 
              class="btn btn-sm" 
              :class="user.role === 'buyer' ? 'btn-gold' : 'btn-outline-white'"
              @click="switchRole('buyer')"
            >Buyer</button>
            <button 
              class="btn btn-sm" 
              :class="user.role === 'agent' ? 'btn-gold' : 'btn-outline-white'"
              @click="switchRole('agent')"
            >Agent</button>
            <button 
              class="btn btn-sm" 
              :class="user.role === 'admin' ? 'btn-gold' : 'btn-outline-white'"
              @click="switchRole('admin')"
            >Admin</button>
          </div>
        </div>
      </div>

      <!-- Dashboard Body Grid -->
      <div style="display: grid; grid-template-columns: 240px 1fr; gap: 28px;" class="dashboard-layout-grid">
        <!-- Sidebar Navigation -->
        <aside style="background: #FFFFFF; border: 1px solid var(--color-border); border-radius: var(--radius-lg); padding: 16px; height: fit-content;">
          <ul style="list-style: none; display: flex; flex-direction: column; gap: 6px;">
            <li>
              <button 
                class="btn btn-sm" 
                style="width:100%; justify-content: flex-start; text-align:left;"
                :class="activeTab === 'overview' ? 'btn-primary' : 'btn-outline'"
                @click="activeTab = 'overview'"
              >
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true" style="margin-right:8px; flex-shrink:0;">
                  <rect x="3" y="3" width="7" height="9" rx="1"/>
                  <rect x="14" y="3" width="7" height="5" rx="1"/>
                  <rect x="14" y="12" width="7" height="9" rx="1"/>
                  <rect x="3" y="16" width="7" height="5" rx="1"/>
                </svg>
                Dashboard Overview
              </button>
            </li>
            <li v-if="user.role === 'buyer'">
              <button 
                class="btn btn-sm" 
                style="width:100%; justify-content: flex-start; text-align:left;"
                :class="activeTab === 'favorites' ? 'btn-primary' : 'btn-outline'"
                @click="activeTab = 'favorites'"
              >
                <svg width="16" height="16" viewBox="0 0 24 24" :fill="user.savedProperties.length > 0 ? 'currentColor' : 'none'" stroke="currentColor" stroke-width="2" aria-hidden="true" style="margin-right:8px; flex-shrink:0;">
                  <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                </svg>
                Saved Properties ({{ user.savedProperties.length }})
              </button>
            </li>
            <li v-if="user.role === 'buyer'">
              <button 
                class="btn btn-sm" 
                style="width:100%; justify-content: flex-start; text-align:left;"
                :class="activeTab === 'viewings' ? 'btn-primary' : 'btn-outline'"
                @click="activeTab = 'viewings'"
              >
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true" style="margin-right:8px; flex-shrink:0;">
                  <rect x="3" y="4" width="18" height="18" rx="2"/>
                  <line x1="16" y1="2" x2="16" y2="6"/>
                  <line x1="8" y1="2" x2="8" y2="6"/>
                  <line x1="3" y1="10" x2="21" y2="10"/>
                </svg>
                Scheduled Viewings ({{ user.scheduledViewings.length }})
              </button>
            </li>
            <li v-if="user.role === 'agent' || user.role === 'admin'">
              <button 
                class="btn btn-sm" 
                style="width:100%; justify-content: flex-start; text-align:left;"
                :class="activeTab === 'listings' ? 'btn-primary' : 'btn-outline'"
                @click="activeTab = 'listings'"
              >
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true" style="margin-right:8px; flex-shrink:0;">
                  <path d="M3 21h18M5 21V7l7-4 7 4v14M9 21v-6h6v6"/>
                </svg>
                Property Mandates ({{ properties.length }})
              </button>
            </li>
            <li v-if="user.role === 'agent' || user.role === 'admin'">
              <button 
                class="btn btn-sm" 
                style="width:100%; justify-content: flex-start; text-align:left;"
                :class="activeTab === 'leads' ? 'btn-primary' : 'btn-outline'"
                @click="activeTab = 'leads'"
              >
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true" style="margin-right:8px; flex-shrink:0;">
                  <path d="M22 12h-6l-2 3h-4l-2-3H2"/>
                  <path d="M5.45 5.11 2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"/>
                </svg>
                Client Leads & Inquiries
              </button>
            </li>
          </ul>
        </aside>

        <!-- Main Tab Content Area -->
        <main>
          <!-- 1. Overview Tab -->
          <div v-if="activeTab === 'overview'" class="animate-fade-in">
            <h2 style="font-size: 1.5rem; font-weight: 800; color: #0A1128; margin-bottom: 20px;">Platform Activity & Metrics</h2>

            <div class="grid grid-3" style="margin-bottom: 32px;">
              <div style="background:#FFF; padding:20px; border-radius:var(--radius-lg); border:1px solid var(--color-border);">
                <div style="font-size:0.8rem; color:#64748B; text-transform:uppercase; font-weight:700;">Portfolio Volume</div>
                <div style="font-family:var(--font-ui); font-size:1.7rem; font-weight:800; color:#059669; margin:4px 0; font-variant-numeric:tabular-nums;">৳ {{ totalPortfolioCrores }} Cr</div>
                <div style="font-size:0.8rem; color:#059669;">{{ properties.length }} Active Mandates</div>
              </div>

              <div style="background:#FFF; padding:20px; border-radius:var(--radius-lg); border:1px solid var(--color-border);">
                <div style="font-size:0.8rem; color:#64748B; text-transform:uppercase; font-weight:700;">Scheduled VIP Tours</div>
                <div style="font-family:var(--font-ui); font-size:1.7rem; font-weight:800; color:#D4AF37; margin:4px 0; font-variant-numeric:tabular-nums;">28 Visits</div>
                <div style="font-size:0.8rem; color:#64748B;">Gulshan, Purbachal & Inani</div>
              </div>

              <div style="background:#FFF; padding:20px; border-radius:var(--radius-lg); border:1px solid var(--color-border);">
                <div style="font-size:0.8rem; color:#64748B; text-transform:uppercase; font-weight:700;">Legal Due Diligence</div>
                <div style="font-family:var(--font-ui); font-size:1.7rem; font-weight:800; color:#2563EB; margin:4px 0; font-variant-numeric:tabular-nums;">100% Cleared</div>
                <div style="font-size:0.8rem; color:#64748B;">RAJUK & CDA Certified</div>
              </div>
            </div>

            <!-- Recent Activity List -->
            <div style="background:#FFF; border:1px solid var(--color-border); border-radius:var(--radius-lg); padding:24px;">
              <h3 style="font-size: 1.15rem; font-weight: 800; margin-bottom: 16px;">Recent Appointments & Messages</h3>
              <div v-if="user.scheduledViewings.length > 0">
                <div v-for="v in user.scheduledViewings" :key="v.id" style="padding:14px 0; border-bottom:1px solid #F1F5F9;" class="flex items-center justify-between flex-wrap gap-2">
                  <div>
                    <strong style="color:#0F172A;">{{ v.propertyTitle }}</strong>
                    <div style="font-size:0.85rem; color:#64748B; margin-top:2px;">Scheduled for: <strong>{{ v.date }}</strong> ({{ v.timeSlot }})</div>
                  </div>
                  <span class="badge badge-rajuk">{{ v.status || 'Confirmed' }}</span>
                </div>
              </div>
              <div v-else style="color:#64748B; font-size:0.9rem;">No recent appointments.</div>
            </div>
          </div>

          <!-- 2. Saved Properties Tab -->
          <div v-if="activeTab === 'favorites'" class="animate-fade-in">
            <h2 style="font-size: 1.5rem; font-weight: 800; color: #0A1128; margin-bottom: 20px;">Your Saved Wishlist ({{ savedPropertiesList.length }})</h2>

            <div v-if="savedPropertiesList.length > 0" class="grid grid-2">
              <PropertyCard 
                v-for="prop in savedPropertiesList" 
                :key="prop.id" 
                :property="prop" 
              />
            </div>

            <div v-else class="text-center" style="background:#FFF; padding:50px 20px; border-radius:var(--radius-lg); border:1px solid var(--color-border);">
              <p style="color:#64748B; margin-bottom:16px;">You have no saved properties yet.</p>
              <NuxtLink to="/properties" class="btn btn-emerald">Explore Properties Catalog</NuxtLink>
            </div>
          </div>

          <!-- 3. Scheduled Viewings Tab -->
          <div v-if="activeTab === 'viewings'" class="animate-fade-in">
            <h2 style="font-size: 1.5rem; font-weight: 800; color: #0A1128; margin-bottom: 20px;">Scheduled VIP Site Visits</h2>

            <div v-if="user.scheduledViewings.length > 0" style="display:flex; flex-direction:column; gap:16px;">
              <div v-for="(v, index) in user.scheduledViewings" :key="v.id" style="background:#FFF; border:1px solid var(--color-border); border-radius:var(--radius-lg); padding:20px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;">
                <div>
                  <span class="badge badge-featured" style="margin-bottom:6px;">VIP Booking ID: #{{ v.id }}</span>
                  <h4 style="font-size: 1.15rem; font-weight:800; color:#0F172A;">{{ v.propertyTitle }}</h4>
                  <div style="font-size:0.9rem; color:#334155; margin-top:4px;">
                    <strong>{{ v.date }}</strong> • {{ v.timeSlot }} • {{ v.pickupRequested ? 'VIP Car Pickup Requested' : 'Direct Site Arrival' }}
                  </div>
                </div>

                <div class="flex items-center gap-2">
                  <span class="badge badge-rajuk">{{ v.status || 'Confirmed' }}</span>
                  <NuxtLink :to="`/properties/${v.propertyId}`" class="btn btn-sm btn-outline">View Property</NuxtLink>
                  <button class="btn btn-sm btn-outline" style="color:#EF4444; border-color:rgba(239,68,68,0.4);" @click="cancelViewing(index)">
                    Cancel Tour
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- 4. Property Mandates / Listings Tab (Agent/Admin) -->
          <div v-if="activeTab === 'listings'" class="animate-fade-in">
            <div class="flex items-center justify-between" style="margin-bottom: 20px;">
              <h2 style="font-size: 1.5rem; font-weight: 800; color: #0A1128;">Property Inventory & Mandates</h2>
              <NuxtLink to="/list-property" class="btn btn-sm btn-gold">+ Add New Property</NuxtLink>
            </div>

            <div style="background:#FFF; border:1px solid var(--color-border); border-radius:var(--radius-lg); overflow:hidden;">
              <div style="overflow-x:auto;">
                <table style="width:100%; border-collapse:collapse; min-width:640px;">
                  <thead>
                    <tr style="background:#F8FAFC; border-bottom:1px solid var(--color-border); font-size:0.85rem; color:#64748B;">
                      <th style="padding:14px; text-align:left;">Property</th>
                      <th style="padding:14px; text-align:left;">Location</th>
                      <th style="padding:14px; text-align:left;">Price (BDT)</th>
                      <th style="padding:14px; text-align:left;">Status</th>
                      <th style="padding:14px; text-align:right;">Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="p in properties" :key="p.id" style="border-bottom:1px solid #F1F5F9; font-size:0.9rem;">
                      <td style="padding:14px; font-weight:700; color:#0F172A;">
                        <NuxtLink :to="`/properties/${p.id}`">{{ p.title }}</NuxtLink>
                      </td>
                      <td style="padding:14px; color:#64748B;">{{ p.areaName }}</td>
                      <td style="padding:14px; font-weight:800; color:#059669;">{{ formatBDT(p.price) }}</td>
                      <td style="padding:14px;">
                        <span class="badge badge-status">{{ p.status }}</span>
                      </td>
                      <td style="padding:14px; text-align:right;">
                        <NuxtLink :to="`/properties/${p.id}`" class="btn btn-sm btn-outline">Preview</NuxtLink>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          <!-- 5. Leads Tab -->
          <div v-if="activeTab === 'leads'" class="animate-fade-in">
            <h2 style="font-size: 1.5rem; font-weight: 800; color: #0A1128; margin-bottom: 20px;">Client Leads & Inquiries Inbox</h2>
            <div style="background:#FFF; border:1px solid var(--color-border); border-radius:var(--radius-lg); padding:24px;">
              <div v-if="realLeads.length > 0">
                <div v-for="lead in realLeads" :key="lead.id" class="flex items-center justify-between flex-wrap gap-2" style="padding:14px 0; border-bottom:1px solid #F1F5F9;">
                  <div>
                    <strong style="color:#0F172A;">{{ lead.name }}</strong>
                    <span class="badge badge-featured" style="font-size:0.7rem; margin-left:8px;">{{ lead.buyer_type || 'Buyer' }}</span>
                    <div style="font-size:0.85rem; color:#64748B;">Inquiry on: <strong>{{ lead.property_title || 'Mandate' }}</strong></div>
                    <div style="font-size:0.85rem; color:#059669; margin-top:2px;">Phone: {{ lead.phone }} • Status: {{ lead.stage || 'New' }}</div>
                  </div>
                  <a :href="`https://wa.me/${(lead.phone || '').replace(/[^0-9]/g, '')}`" target="_blank" class="btn btn-sm btn-emerald" style="background:#25D366;">WhatsApp Client</a>
                </div>
              </div>
              <div v-else style="color:#64748B; font-size:0.9rem;">
                No active buyer inquiries recorded yet.
              </div>
            </div>
          </div>
        </main>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useAuth } from '~/composables/useAuth'
import { useProperties } from '~/composables/useProperties'
import { formatBDT } from '~/composables/useCurrency'
import { useApiUrl } from '~/composables/useApi'
import PropertyCard from '~/components/PropertyCard.vue'

const route = useRoute()
const { user, switchRole } = useAuth()
const { properties, getPropertyById, fetchProperties } = useProperties()

const activeTab = ref('overview')
const realLeads = ref<any[]>([])

const fetchRealLeads = async () => {
  try {
    const res = await fetch(useApiUrl('/leads'))
    if (res.ok) {
      const json = await res.json()
      if (json && json.success && Array.isArray(json.data)) {
        realLeads.value = json.data
      }
    }
  } catch (err) {
    console.error('Failed to load leads for dashboard:', err)
  }
}

onMounted(async () => {
  if (route.query.tab) {
    activeTab.value = String(route.query.tab)
  }
  await Promise.all([
    fetchProperties(),
    fetchRealLeads()
  ])
})

const totalPortfolioCrores = computed(() => {
  const totalBDT = properties.value.reduce((acc, p) => acc + (p.price || 0), 0)
  return (totalBDT / 10000000).toFixed(1)
})

const savedPropertiesList = computed(() => {
  return user.value.savedProperties.map(id => getPropertyById(id))
})

const cancelViewing = (index: number) => {
  if (confirm('Cancel this scheduled VIP site visit?')) {
    user.value.scheduledViewings.splice(index, 1)
  }
}
</script>

<style scoped>
@media (max-width: 992px) {
  .dashboard-layout-grid {
    grid-template-columns: 1fr !important;
  }
}
</style>

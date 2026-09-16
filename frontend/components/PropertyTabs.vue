<template>
  <div class="property-tabs-container">
    <!-- Tabs Header Bar -->
    <div class="tabs-navigation-bar">
      <button 
        v-for="tab in tabList" 
        :key="tab.id"
        class="tab-nav-btn"
        :class="{ active: activeTab === tab.id }"
        @click="activeTab = tab.id"
      >
        <component :is="tab.icon" style="width: 18px; height: 18px; display:inline-block; vertical-align: middle; margin-right: 6px;" />
        {{ tab.label }}
      </button>
    </div>

    <!-- ======================================================================
         TAB 1: OVERVIEW
         ====================================================================== -->
    <div v-if="activeTab === 'overview'" class="tab-pane animate-fade-in">
      <!-- High-Level Specs Highlight Box -->
      <div class="specs-highlight-box">
        <div v-if="property.bedrooms > 0" class="highlight-item">
          <div class="highlight-icon">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M2 4v16M2 8h18a2 2 0 0 1 2 2v10M2 17h20M6 8v9"/>
            </svg>
          </div>
          <div>
            <div style="font-size:0.75rem; color:#64748B; text-transform:uppercase;">Bedrooms</div>
            <div style="font-size:1.15rem; font-weight:800; color:#0F172A;">{{ property.bedrooms }} Master Beds</div>
          </div>
        </div>

        <div v-if="property.bathrooms > 0" class="highlight-item">
          <div class="highlight-icon">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M9 6h6a3 3 0 0 1 3 3v2a3 3 0 0 1-3 3H9a3 3 0 0 1-3-3V9a3 3 0 0 1 3-3zM4 14h16M7 14v4M17 14v4"/>
            </svg>
          </div>
          <div>
            <div style="font-size:0.75rem; color:#64748B; text-transform:uppercase;">Bathrooms</div>
            <div style="font-size:1.15rem; font-weight:800; color:#0F172A;">{{ property.bathrooms }} Luxury Baths</div>
          </div>
        </div>

        <div class="highlight-item">
          <div class="highlight-icon">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
              <line x1="3" y1="9" x2="21" y2="9"/>
              <line x1="9" y1="21" x2="9" y2="9"/>
            </svg>
          </div>
          <div>
            <div style="font-size:0.75rem; color:#64748B; text-transform:uppercase;">Total Space / Land</div>
            <div style="font-size:1.15rem; font-weight:800; color:#0F172A;">
              {{ formatArea(property.squareFootage, property.landSize, property.landUnit) }}
            </div>
          </div>
        </div>

        <div v-if="property.parking > 0" class="highlight-item">
          <div class="highlight-icon">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect x="1" y="3" width="22" height="13" rx="2" ry="2"/>
              <path d="M16 8h-6a2 2 0 0 0-2 2v6"/>
            </svg>
          </div>
          <div>
            <div style="font-size:0.75rem; color:#64748B; text-transform:uppercase;">Car Parking</div>
            <div style="font-size:1.15rem; font-weight:800; color:#0F172A;">{{ property.parking }} Reserved Bays</div>
          </div>
        </div>
      </div>

      <!-- Detailed Description -->
      <div style="margin-bottom: 36px;">
        <h3 style="font-size: 1.4rem; font-weight: 800; margin-bottom: 14px; color: #0F172A;">Property Description</h3>
        <p style="color: #334155; font-size: 1.05rem; line-height: 1.8; white-space: pre-line;">
          {{ property.description }}
        </p>
      </div>

      <!-- Key Specifications Table -->
      <div style="margin-bottom: 36px;">
        <h3 style="font-size: 1.4rem; font-weight: 800; margin-bottom: 16px; color: #0F172A;">Architectural & Unit Details</h3>
        <div style="display:grid; grid-template-columns: repeat(2, 1fr); gap: 12px; background: #FFFFFF; border: 1px solid var(--color-border); border-radius: var(--radius-lg); padding: 20px;">
          <div class="flex items-center justify-between" style="padding: 8px 0; border-bottom: 1px solid #F1F5F9;">
            <span style="color:#64748B;">Property Type:</span>
            <strong style="color:#0F172A;">{{ property.propertyType }}</strong>
          </div>
          <div class="flex items-center justify-between" style="padding: 8px 0; border-bottom: 1px solid #F1F5F9;">
            <span style="color:#64748B;">Listing Status:</span>
            <strong style="color:#059669;">{{ property.status }} ({{ property.listingType }})</strong>
          </div>
          <div v-if="property.floorNumber" class="flex items-center justify-between" style="padding: 8px 0; border-bottom: 1px solid #F1F5F9;">
            <span style="color:#64748B;">Floor Level:</span>
            <strong style="color:#0F172A;">Level {{ property.floorNumber }} of {{ property.totalFloors }}</strong>
          </div>
          <div v-if="property.facing" class="flex items-center justify-between" style="padding: 8px 0; border-bottom: 1px solid #F1F5F9;">
            <span style="color:#64748B;">Facing Direction:</span>
            <strong style="color:#0F172A;">{{ property.facing }}</strong>
          </div>
          <div class="flex items-center justify-between" style="padding: 8px 0; border-bottom: 1px solid #F1F5F9;">
            <span style="color:#64748B;">Construction Stage:</span>
            <strong style="color:#2563EB;">{{ property.completionStatus }}</strong>
          </div>
          <div v-if="property.yearBuilt" class="flex items-center justify-between" style="padding: 8px 0; border-bottom: 1px solid #F1F5F9;">
            <span style="color:#64748B;">Year Built / Handover:</span>
            <strong style="color:#0F172A;">{{ property.yearBuilt }}</strong>
          </div>
        </div>
      </div>

      <!-- Verified Amenities Checklist -->
      <div style="margin-bottom: 36px;">
        <h3 style="font-size: 1.4rem; font-weight: 800; margin-bottom: 16px; color: #0F172A;">Features & Premium Amenities</h3>
        <div class="amenities-grid">
          <div v-for="(amenity, idx) in property.amenities" :key="idx" class="amenity-pill">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="2.5">
              <polyline points="20 6 9 17 4 12"/>
            </svg>
            <span>{{ amenity }}</span>
          </div>
        </div>
      </div>

      <!-- Legal Document & Regulatory Verification Box (Bangladesh Specific) -->
      <div class="verified-documents-box">
        <div class="flex items-center gap-3" style="margin-bottom: 12px;">
          <div style="width: 32px; height: 32px; border-radius: 50%; background: #059669; color:#FFF; display:flex; align-items:center; justify-content:center;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
              <polyline points="20 6 9 17 4 12"/>
            </svg>
          </div>
          <div>
            <h4 style="font-size: 1.15rem; font-weight: 800; color: #065F46;">Legal & Regulatory Due Diligence (Verified by GBREL Legal)</h4>
            <p style="font-size: 0.85rem; color: #047857;">All original registry documents, khatians, and municipal approval numbers have been vetted by our in-house Supreme Court legal panel.</p>
          </div>
        </div>
        <ul style="list-style: none; display: flex; flex-direction: column; gap: 8px; margin-top: 14px;">
          <li v-for="(doc, idx) in property.documentsVerified" :key="idx" class="flex items-center gap-2" style="font-size: 0.92rem; color: #064E3B; font-weight: 600;">
            <span style="color:#059669;">✔</span> {{ doc }}
          </li>
        </ul>
      </div>
    </div>

    <!-- ======================================================================
         TAB 2: LISTING HISTORY
         ====================================================================== -->
    <div v-if="activeTab === 'history'" class="tab-pane animate-fade-in">
      <h3 style="font-size: 1.4rem; font-weight: 800; margin-bottom: 8px; color: #0F172A;">Property Listing & Price History</h3>
      <p style="color: #64748B; font-size: 0.95rem; margin-bottom: 24px;">Track transparent transaction events, title updates, and historical appreciation milestones for this property.</p>

      <div class="history-timeline">
        <div v-for="item in property.history" :key="item.id" class="history-item">
          <div class="history-dot"></div>
          <div style="font-size: 0.85rem; font-weight: 700; color: #059669; margin-bottom: 4px;">{{ item.date }}</div>
          <div style="font-size: 1.15rem; font-weight: 800; color: #0F172A; margin-bottom: 4px;">{{ item.event }}</div>
          <div class="flex items-center gap-4" style="margin-bottom: 6px;">
            <span style="font-family: var(--font-ui); font-size: 1.2rem; font-weight: 800; color: #D4AF37; font-variant-numeric: tabular-nums;">{{ formatBDT(item.price) }}</span>
            <span class="badge badge-status">{{ item.status }}</span>
          </div>
          <p style="color: #64748B; font-size: 0.9rem;">{{ item.notes }}</p>
        </div>
      </div>
    </div>

    <!-- ======================================================================
         TAB 3: MARKET ESTIMATES & VALUATION
         ====================================================================== -->
    <div v-if="activeTab === 'estimates'" class="tab-pane animate-fade-in">
      <h3 style="font-size: 1.4rem; font-weight: 800; margin-bottom: 8px; color: #0F172A;">Market Valuation & Investment ROI Analytics</h3>
      <p style="color: #64748B; font-size: 0.95rem; margin-bottom: 24px;">Calculated using verified secondary market transactions, RAJUK sub-registry data, and inflation trends in {{ property.areaName }}.</p>

      <div class="grid grid-3" style="margin-bottom: 32px;">
        <div style="background: #FFFFFF; padding: 24px; border-radius: var(--radius-lg); border: 1.5px solid var(--color-border); box-shadow: var(--shadow-sm);">
          <div style="font-size: 0.82rem; color: #64748B; text-transform: uppercase; font-weight: 700;">GBREL Valuation Estimate</div>
          <div style="font-family: var(--font-ui); font-size: 1.7rem; font-weight: 800; color: #059669; margin: 8px 0; font-variant-numeric: tabular-nums;">
            {{ formatBDT(property.estimates.marketEstimate) }}
          </div>
          <div style="font-size: 0.82rem; color: #64748B;">Range: {{ formatBDT(property.estimates.lowEstimate) }} - {{ formatBDT(property.estimates.highEstimate) }}</div>
        </div>

        <div style="background: #FFFFFF; padding: 24px; border-radius: var(--radius-lg); border: 1.5px solid var(--color-border); box-shadow: var(--shadow-sm);">
          <div style="font-size: 0.82rem; color: #64748B; text-transform: uppercase; font-weight: 700;">Projected Annual Appreciation</div>
          <div style="font-family: var(--font-ui); font-size: 1.7rem; font-weight: 800; color: #D4AF37; margin: 8px 0; font-variant-numeric: tabular-nums;">
            +{{ property.estimates.annualGrowthPct }}% / Year
          </div>
          <div style="font-size: 0.82rem; color: #059669; font-weight: 600;">Outperforming Dhaka CPI Inflation</div>
        </div>

        <div style="background: #FFFFFF; padding: 24px; border-radius: var(--radius-lg); border: 1.5px solid var(--color-border); box-shadow: var(--shadow-sm);">
          <div style="font-size: 0.82rem; color: #64748B; text-transform: uppercase; font-weight: 700;">Estimated Rental Return / ROI</div>
          <div style="font-family: var(--font-ui); font-size: 1.7rem; font-weight: 800; color: #2563EB; margin: 8px 0; font-variant-numeric: tabular-nums;">
            {{ formatBDT(property.estimates.monthlyRentEstimate) }}<span style="font-size:0.9rem; color:#64748B;"> / mo</span>
          </div>
          <div style="font-size: 0.82rem; color: #64748B;">Expected Annual Yield: <strong>{{ property.estimates.annualRoiPct }}% Net</strong></div>
        </div>
      </div>

      <!-- Area Price Benchmark -->
      <div style="background: var(--color-bg-card-alt); padding: 20px; border-radius: var(--radius-md);">
        <div class="flex items-center justify-between">
          <div>
            <strong style="color:#0F172A; font-size:1.05rem;">Area Benchmark Rate ({{ property.areaName }}):</strong>
            <div style="font-size:0.85rem; color:#64748B; margin-top:2px;">Average market asking price for premium grade assets</div>
          </div>
          <div style="font-size: 1.35rem; font-weight: 800; color: #0F172A;">
            ৳ {{ property.estimates.pricePerSqftArea.toLocaleString() }} / Sq. Ft.
          </div>
        </div>
      </div>
    </div>

    <!-- ======================================================================
         TAB 4: COMPARABLES
         ====================================================================== -->
    <div v-if="activeTab === 'comparables'" class="tab-pane animate-fade-in">
      <h3 style="font-size: 1.4rem; font-weight: 800; margin-bottom: 8px; color: #0F172A;">Nearby Comparable Properties</h3>
      <p style="color: #64748B; font-size: 0.95rem; margin-bottom: 24px;">Similar properties recently evaluated or listed within a 3km radius.</p>

      <div class="grid grid-2">
        <div v-for="comp in property.comparables" :key="comp.id" style="background:#FFFFFF; border:1px solid var(--color-border); border-radius:var(--radius-lg); overflow:hidden; display:flex;">
          <img :src="comp.imageUrl" :alt="comp.title" style="width: 140px; height: 100%; object-fit: cover;" />
          <div style="padding: 16px; flex: 1;">
            <div style="font-size: 0.8rem; color: #059669; font-weight: 700; margin-bottom: 4px;">{{ comp.distanceKm }} km away</div>
            <h4 style="font-size: 1.05rem; font-weight: 700; color: #0F172A; margin-bottom: 6px;">{{ comp.title }}</h4>
            <div style="font-size: 0.85rem; color: #64748B; margin-bottom: 10px;">{{ comp.address }}</div>
            <div class="flex items-center justify-between">
              <strong style="font-family:var(--font-display); font-size:1.15rem; color:#D4AF37;">{{ formatBDT(comp.price) }}</strong>
              <span v-if="comp.sqft" style="font-size:0.8rem; color:#64748B;">{{ comp.sqft.toLocaleString() }} Sq. Ft.</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ======================================================================
         TAB 5: NEARBY SCHOOLS
         ====================================================================== -->
    <div v-if="activeTab === 'schools'" class="tab-pane animate-fade-in">
      <h3 style="font-size: 1.4rem; font-weight: 800; margin-bottom: 8px; color: #0F172A;">Top Nearby Educational Institutions</h3>
      <p style="color: #64748B; font-size: 0.95rem; margin-bottom: 24px;">Verified ratings, Cambridge/IB accreditation, and drive-time commute from this location.</p>

      <div style="display: flex; flex-direction: column; gap: 14px;">
        <div v-for="(school, idx) in property.schools" :key="idx" style="background:#FFFFFF; border:1px solid var(--color-border); border-radius:var(--radius-md); padding: 18px; display:flex; align-items:center; justify-content:space-between;">
          <div class="flex items-center gap-4">
            <div style="width: 48px; height: 48px; border-radius: 12px; background: rgba(5,150,105,0.1); color:#059669; display:flex; align-items:center; justify-content:center;">
              <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path d="M22 9l-10-5L2 9l10 5 10-5z"/>
                <path d="M6 11.5V17c0 1.5 3 3 6 3s6-1.5 6-3v-5.5"/>
                <line x1="22" y1="9" x2="22" y2="15"/>
              </svg>
            </div>
            <div>
              <h4 style="font-size: 1.1rem; font-weight: 800; color: #0F172A; margin-bottom: 2px;">{{ school.name }}</h4>
              <div style="font-size: 0.85rem; color: #64748B;">{{ school.type }} • Grades: <strong>{{ school.grades }}</strong></div>
            </div>
          </div>

          <div style="text-align: right;">
            <div class="flex items-center gap-1 justify-end" style="color: #F59E0B; font-weight: 800; font-size: 1.05rem;">
              <span aria-hidden="true">★</span> {{ school.rating }} / 5.0
            </div>
            <div style="font-size: 0.82rem; color: #64748B; margin-top: 2px;">
              {{ school.distanceKm }} km (approx. {{ school.travelMins }} mins drive)
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ======================================================================
         TAB 6: COMMUNITY & TRANSIT
         ====================================================================== -->
    <div v-if="activeTab === 'community'" class="tab-pane animate-fade-in">
      <h3 style="font-size: 1.4rem; font-weight: 800; margin-bottom: 8px; color: #0F172A;">Neighborhood & Transit Infrastructure</h3>
      <p style="color: #64748B; font-size: 0.95rem; margin-bottom: 24px;">Community safety metrics, Metro Rail (MRT) connectivity, and healthcare access.</p>

      <div class="grid grid-2" style="margin-bottom: 24px;">
        <div style="background: #FFFFFF; border: 1px solid var(--color-border); border-radius: var(--radius-lg); padding: 22px;">
          <div class="flex items-center gap-3" style="margin-bottom: 12px;">
            <span style="width: 40px; height: 40px; border-radius: 10px; background: rgba(10,17,40,0.06); display: flex; align-items: center; justify-content: center; color: #0A1128;">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <rect x="4" y="3" width="16" height="18" rx="2"/>
                <line x1="8" y1="7" x2="16" y2="7"/>
                <line x1="8" y1="11" x2="16" y2="11"/>
                <line x1="8" y1="15" x2="13" y2="15"/>
              </svg>
            </span>
            <div>
              <strong style="color:#0F172A; font-size:1.05rem;">Metro Rail (MRT) Proximity</strong>
              <div style="font-size:0.85rem; color:#64748B;">{{ property.community.nearestMetroStation }}</div>
            </div>
          </div>
          <div style="font-size: 0.9rem; color: #334155; font-weight: 600;">Distance: {{ property.community.metroDistanceKm }} km away</div>
        </div>

        <div style="background: #FFFFFF; border: 1px solid var(--color-border); border-radius: var(--radius-lg); padding: 22px;">
          <div class="flex items-center gap-3" style="margin-bottom: 12px;">
            <span style="width: 40px; height: 40px; border-radius: 10px; background: rgba(10,17,40,0.06); display: flex; align-items: center; justify-content: center; color: #0A1128;">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path d="M12 21s-8-5.5-8-11a4 4 0 0 1 8-1 4 4 0 0 1 8 1c0 5.5-8 11-8 11z"/>
                <path d="M12 22l8-9M4 13l8 9"/>
              </svg>
            </span>
            <div>
              <strong style="color:#0F172A; font-size:1.05rem;">Tertiary Healthcare & Hospitals</strong>
              <div style="font-size:0.85rem; color:#64748B;">{{ property.community.nearestHospital }}</div>
            </div>
          </div>
          <div style="font-size: 0.9rem; color: #334155; font-weight: 600;">Distance: {{ property.community.hospitalDistanceKm }} km away</div>
        </div>
      </div>

      <div style="background: #FFFFFF; border: 1px solid var(--color-border); border-radius: var(--radius-lg); padding: 24px;">
        <h4 style="font-size: 1.1rem; font-weight: 800; color: #0F172A; margin-bottom: 8px;">Safety & Community Highlights</h4>
        <p style="color: #334155; font-size: 0.95rem; line-height: 1.7; margin-bottom: 16px;">
          <strong>Security Status:</strong> {{ property.community.safetyRating }}
        </p>
        <p style="color: #334155; font-size: 0.95rem; line-height: 1.7; margin-bottom: 16px;">
          <strong>Amenities & Lifestyle:</strong> {{ property.community.amenitiesOverview }}
        </p>
        <p style="color: #334155; font-size: 0.95rem; line-height: 1.7;">
          <strong>Highway & Transit Connectivity:</strong> {{ property.community.transitOverview }}
        </p>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import type { PropertyItem } from '~/composables/useProperties'
import { formatBDT, formatArea } from '~/composables/useCurrency'

defineProps<{
  property: PropertyItem
}>()

const activeTab = ref('overview')

const tabList = [
  { id: 'overview', label: 'Overview' },
  { id: 'history', label: 'Listing History' },
  { id: 'estimates', label: 'Valuation & ROI' },
  { id: 'comparables', label: 'Comparables' },
  { id: 'schools', label: 'Nearby Schools' },
  { id: 'community', label: 'Community & Metro' }
]
</script>

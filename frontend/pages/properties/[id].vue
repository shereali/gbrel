<template>
  <div style="background: #F8FAFC; min-height: 100vh; padding: 40px 0 80px;">
    <!-- Loading Skeleton State -->
    <div v-if="isLoadingProperty && !property" class="container text-center" style="padding: 100px 0;">
      <span class="animate-spin inline-block" style="font-size: 2.5rem; color: var(--color-gold); margin-bottom: 16px;">◌</span>
      <h2 style="font-size: 1.4rem; font-weight: 700; color: #0F172A;">Loading Verified Property Mandate...</h2>
      <p style="color: #64748B; font-size: 0.9rem;">Fetching live title deeds, specifications, and collateral from database.</p>
    </div>

    <!-- Error / Not Found State -->
    <div v-else-if="!property" class="container text-center" style="padding: 80px 0;">
      <div style="font-size: 3rem; margin-bottom: 14px;">🏛️</div>
      <h2 style="font-size: 1.5rem; font-weight: 800; color: #0F172A; margin-bottom: 8px;">Property Mandate Not Found</h2>
      <p style="color: #64748B; font-size: 0.95rem; max-width: 480px; margin: 0 auto 24px;">
        This listing may have been settled, archived, or is private under non-disclosure.
      </p>
      <NuxtLink to="/properties" class="btn btn-gold">
        <span>Browse Active Verified Catalog →</span>
      </NuxtLink>
    </div>

    <!-- Live Property Page Content -->
    <div v-else class="container">
      <!-- Breadcrumb -->
      <div class="flex items-center justify-between flex-wrap gap-4" style="margin-bottom: 24px;">
        <div class="flex items-center gap-2" style="font-size: 0.85rem; color: #64748B;">
          <NuxtLink to="/" style="color: #64748B;">Home</NuxtLink>
          <span>/</span>
          <NuxtLink to="/properties" style="color: #64748B;">Properties</NuxtLink>
          <span>/</span>
          <span style="color: #0F172A; font-weight: 600;">{{ property.title }}</span>
        </div>

        <div class="flex items-center gap-3">
          <!-- Add to Compare -->
          <button 
            class="btn btn-sm" 
            :class="isInCompare(property.id) ? 'btn-gold' : 'btn-outline'"
            @click="toggleCompare(property.id)"
          >
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <polyline points="16 3 21 3 21 8"/>
              <line x1="4" y1="20" x2="21" y2="3"/>
            </svg>
            <span>{{ isInCompare(property.id) ? 'In Compare' : 'Add to Compare' }}</span>
          </button>

          <!-- Save to Favorites -->
          <button 
            class="btn btn-sm"
            :class="isPropertySaved(property.id) ? 'btn-emerald' : 'btn-outline'"
            @click="toggleSaveProperty(property.id)"
          >
            <svg width="14" height="14" viewBox="0 0 24 24" :fill="isPropertySaved(property.id) ? 'currentColor' : 'none'" stroke="currentColor" stroke-width="2">
              <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
            </svg>
            <span>{{ isPropertySaved(property.id) ? 'Saved' : 'Save Property' }}</span>
          </button>
        </div>
      </div>

      <!-- Property Header Title & Price Banner -->
      <div class="flex items-start justify-between flex-wrap gap-4" style="margin-bottom: 24px;">
        <div style="max-width: 820px;">
          <div class="flex items-center gap-2 flex-wrap" style="margin-bottom: 8px;">
            <span v-if="property.isRajukApproved" class="badge badge-rajuk">RAJUK Approved Plan</span>
            <span v-if="property.propertyType === 'Land Share'" class="badge" style="background:#EDE9FE; color:#7C3AED; font-weight:800; border:1px solid #DDD6FE;">
              🤝 Land Share Co-Ownership
            </span>
            <span v-else class="badge badge-status">{{ property.propertyType }}</span>
            <span class="badge badge-featured">{{ property.listingType }}</span>
            <span v-if="property.hasOpenHouse" class="badge badge-urgent">Open House Scheduled</span>
            <span v-if="property.hidePrice" class="badge" style="background:#FEF3C7; color:#B45309; font-weight:700; border:1px solid #FCD34D;">
              🔐 Confidential Valuation (NDA)
            </span>
            <a 
              v-if="property.brochureUrl" 
              :href="property.brochureUrl" 
              target="_blank" 
              class="badge" 
              style="background:#E0F2FE; color:#0369A1; font-weight:700; border:1px solid #BAE6FD; text-decoration:none;"
            >
              📄 PDF Brochure Attached
            </a>
          </div>

          <h1 style="font-size: 2.4rem; font-weight: 800; color: #0A1128; line-height: 1.25; margin-bottom: 8px;">
            {{ property.title }}
          </h1>

          <!-- Location (Respects hideExactAddress) -->
          <div class="flex items-center gap-2" style="color: #64748B; font-size: 0.95rem;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="2">
              <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
              <circle cx="12" cy="10" r="3"/>
            </svg>
            <span v-if="property.hideExactAddress">
              {{ property.areaName }}, {{ property.state }} (Prime Corridor • Confidential Enclave Address)
            </span>
            <span v-else>
              {{ property.address }} ({{ property.areaName }}, {{ property.city }})
            </span>
          </div>
        </div>

        <!-- Price Display Card (Respects hidePrice option) -->
        <div style="text-align: right; background: #FFFFFF; border: 1.5px solid var(--color-border); border-radius: var(--radius-lg); padding: 16px 24px; box-shadow: var(--shadow-sm); min-width: 260px;">
          <!-- Case A: Price is Hidden (Confidential Mandate) -->
          <div v-if="property.hidePrice">
            <div style="font-size: 0.76rem; color: #B45309; text-transform: uppercase; font-weight: 800; letter-spacing: 0.05em; display:flex; align-items:center; justify-content:flex-end; gap:4px;">
              <span>🔐 Confidential Mandate</span>
            </div>
            <div style="font-family: var(--font-display); font-size: 1.45rem; font-weight: 800; color: var(--color-gold); line-height: 1.2; margin-top: 4px;">
              {{ property.priceDisplayText || 'Price on Application (POA)' }}
            </div>
            <div style="font-size: 0.78rem; color: #64748B; margin-top: 6px;">
              Financials released upon NDA verification
            </div>
            <button 
              class="btn btn-sm btn-gold" 
              style="margin-top: 10px; width: 100%; font-size: 0.8rem; padding: 6px 12px;"
              @click="requestConfidentialPricing"
            >
              Request Price & NDA →
            </button>
          </div>

          <!-- Case B: Public Asking Price Display -->
          <div v-else>
            <div style="font-size: 0.78rem; color: #64748B; text-transform: uppercase; font-weight: 700;">Asking Valuation</div>
            <div style="font-family: var(--font-ui); font-size: 2.1rem; font-weight: 800; color: #059669; line-height: 1.1; font-variant-numeric: tabular-nums;">
              {{ formatBDT(property.price) }}
            </div>
            <div v-if="property.priceUnit" style="font-size: 0.85rem; color: #64748B; margin-top: 2px;">{{ property.priceUnit }}</div>
            <div v-else-if="property.squareFootage" style="font-size: 0.82rem; color: #64748B; margin-top: 2px;">
              ৳ {{ Math.round(property.price / property.squareFootage).toLocaleString() }} / Sq. Ft.
            </div>
          </div>
        </div>
      </div>

      <!-- HD Media Gallery with Lightbox trigger -->
      <div class="property-hero-gallery">
        <img 
          :src="property.images[0]" 
          :alt="property.title" 
          class="gallery-main-img" 
          @click="openLightbox(0)" 
        />
        <div class="gallery-sub-grid">
          <img 
            v-if="property.images[1]" 
            :src="property.images[1]" 
            :alt="property.title" 
            class="gallery-sub-img" 
            @click="openLightbox(1)" 
          />
          <div style="position:relative; height:100%;">
            <img 
              v-if="property.images[2]" 
              :src="property.images[2]" 
              :alt="property.title" 
              class="gallery-sub-img" 
              @click="openLightbox(2)" 
            />
            <div 
              v-if="property.images.length > 3" 
              @click="openLightbox(0)"
              style="position:absolute; inset:0; background:rgba(10,17,40,0.7); backdrop-filter:blur(4px); color:#FFF; display:flex; align-items:center; justify-content:center; font-weight:800; font-size:1.1rem; cursor:pointer; border-radius:var(--radius-md);"
            >
              +{{ property.images.length - 2 }} Photos (View Gallery)
            </div>
          </div>
        </div>
      </div>

      <!-- Lightbox Modal -->
      <div v-if="lightboxOpen" ref="lightboxRoot" class="modal-overlay lightbox-overlay" @click.self="lightboxOpen = false">
        <div style="max-width: 900px; width: 100%; position: relative;">
          <button @click="lightboxOpen = false" aria-label="Close photo gallery" style="position:absolute; top:-44px; right:0; width:44px; height:44px; background:none; color:#FFF; font-size:1.6rem; cursor:pointer;">✕</button>
          <img :src="property.images[currentLightboxIdx]" style="width:100%; max-height:75vh; object-fit:contain; border-radius:var(--radius-lg);" :alt="`${property.title} photo ${currentLightboxIdx + 1}`" />
          <div class="flex justify-between items-center" style="margin-top: 14px; color:#FFF;">
            <span>Photo {{ currentLightboxIdx + 1 }} of {{ property.images.length }}</span>
            <div class="flex gap-3">
              <button class="btn btn-sm btn-outline-white" @click="prevPhoto" aria-label="Previous photo">‹ Prev</button>
              <button class="btn btn-sm btn-outline-white" @click="nextPhoto" aria-label="Next photo">Next ›</button>
            </div>
          </div>
        </div>
      </div>

      <!-- Main Grid: 6 Tabs on Left + Sticky Agent Contact on Right -->
      <div style="display: grid; grid-template-columns: 1fr 380px; gap: 32px;" class="property-detail-grid">
        <!-- 6-Tabbed Details Container -->
        <main>
          <PropertyTabs :property="property" />

          <!-- Floor Plan Confidentiality Notice if hideFloorPlan is true -->
          <div 
            v-if="property.hideFloorPlan" 
            style="margin-top: 24px; background: #FFFFFF; border: 1px solid var(--color-border); border-radius: var(--radius-lg); padding: 24px; border-left: 4px solid var(--color-gold);"
          >
            <div class="flex items-center gap-3 mb-2">
              <span style="font-size: 1.5rem;">📐</span>
              <h3 style="font-size: 1.15rem; font-weight: 800; color: #0F172A; margin: 0;">Architectural Blueprints & Floor Layouts (Restricted)</h3>
            </div>
            <p style="color: #64748B; font-size: 0.88rem; line-height: 1.6; margin-bottom: 14px;">
              To protect the architectural intellectual property and physical security of the mandate, detailed Cadastral maps, floor schematics, and structural calculations are provided upon formal non-disclosure agreement (NDA).
            </p>
            <button class="btn btn-sm btn-outline" @click="requestFloorPlan">
              Request Architectural Blueprints Under NDA →
            </button>
          </div>
        </main>

        <!-- Sticky Advisor & Inquiry Card -->
        <aside>
          <div style="background: #FFFFFF; border: 1px solid var(--color-border); border-radius: var(--radius-xl); padding: 28px; box-shadow: var(--shadow-md); position: sticky; top: 100px;">
            <!-- Official Brochure Download CTA -->
            <div style="margin-bottom: 16px;">
              <a 
                v-if="property.brochureUrl" 
                :href="property.brochureUrl" 
                target="_blank" 
                class="btn btn-gold btn-lg" 
                style="width: 100%; font-weight: 800; display: inline-flex; align-items: center; justify-content: center; gap: 8px; text-decoration: none; box-shadow: 0 4px 14px rgba(212, 175, 55, 0.25);"
              >
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                  <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                  <polyline points="14 2 14 8 20 8"/>
                  <line x1="12" y1="18" x2="12" y2="12"/>
                  <polyline points="9 15 12 18 15 15"/>
                </svg>
                <span>Download Official Brochure (PDF)</span>
              </a>
              <button 
                v-else 
                class="btn btn-outline btn-lg" 
                style="width: 100%; font-weight: 700; display: inline-flex; align-items: center; justify-content: center; gap: 8px;"
                @click="requestBrochure"
              >
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                  <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                  <polyline points="14 2 14 8 20 8"/>
                </svg>
                <span>Request Project Brochure</span>
              </button>
            </div>

            <!-- VIP Site Visit Button -->
            <button class="btn btn-emerald btn-lg" style="width: 100%; margin-bottom: 20px; font-weight: 800;" @click="scheduleModalOpen = true">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                <line x1="16" y1="2" x2="16" y2="6"/>
                <line x1="8" y1="2" x2="8" y2="6"/>
                <line x1="3" y1="10" x2="21" y2="10"/>
              </svg>
              <span>Schedule VIP Site Viewing</span>
            </button>

            <!-- Advisor Profile Summary (Respects hideAgentPhoto) -->
            <div class="flex items-center gap-4" style="margin-bottom: 20px; padding-bottom: 18px; border-bottom: 1px solid var(--color-border);">
              <!-- If hideAgentPhoto is true: Institutional Gold Seal -->
              <div 
                v-if="property.hideAgentPhoto"
                style="width: 60px; height: 60px; border-radius: 50%; background: linear-gradient(135deg, #0A1128 0%, #1E293B 100%); border: 2px solid var(--color-gold); display: flex; flex-direction: column; align-items: center; justify-content: center; flex-shrink: 0; box-shadow: 0 4px 10px rgba(212,175,55,0.25);"
                title="GBREL Institutional Certified Mandate"
              >
                <span style="font-size: 1.2rem;">🏛️</span>
                <span style="font-size: 0.55rem; color: var(--color-gold); font-weight: 800; letter-spacing: 0.05em;">GBREL</span>
              </div>

              <!-- Normal Agent Photo -->
              <img 
                v-else
                :src="agent.photo" 
                :alt="agent.name" 
                style="width: 60px; height: 60px; border-radius: 50%; object-fit: cover; border: 2px solid var(--color-gold); flex-shrink: 0;" 
              />

              <div>
                <NuxtLink :to="`/agents/${agent.id}`">
                  <h4 style="font-size: 1.1rem; font-weight: 800; color: #0F172A; line-height: 1.2;">{{ agent.name }}</h4>
                </NuxtLink>
                <div style="font-size: 0.8rem; color: #059669; font-weight: 700;">
                  <span aria-hidden="true">★</span> {{ agent.rating }} ({{ agent.reviewCount }} Reviews)
                </div>
                <div style="font-size: 0.8rem; color: #64748B;">
                  {{ property.hideAgentPhoto ? 'Institutional Mandate Advisor' : agent.agency }}
                </div>
              </div>
            </div>

            <!-- Agent Contact Buttons (Respects hideAgentContact) -->
            <div v-if="property.hideAgentContact" style="margin-bottom: 20px; background: rgba(10,17,40,0.03); padding: 10px 14px; border-radius: var(--radius-md); border: 1px dashed var(--color-border); text-align: center;">
              <div style="font-size: 0.78rem; font-weight: 700; color: #0F172A;">Protected Mandate Hotline</div>
              <div style="font-size: 0.75rem; color: #64748B; margin-top: 2px;">
                Direct calls routed via GBREL Corporate Concierge to safeguard owner privacy.
              </div>
            </div>
            <div v-else class="flex gap-2" style="margin-bottom: 24px;">
              <a 
                :href="`https://wa.me/${agent.whatsapp.replace(/[^0-9]/g, '')}?text=Hello%20${encodeURIComponent(agent.name)},%20I%20am%20inquiring%20about:%20${encodeURIComponent(property.title)}`" 
                target="_blank" 
                class="btn btn-sm flex-1" 
                style="background:#25D366; color:#FFF; font-weight:700;"
              >
                <span>WhatsApp</span>
              </a>
              <a 
                :href="`tel:${agent.phone}`" 
                class="btn btn-sm btn-outline flex-1"
                style="font-weight:700;"
              >
                <span>Call Agent</span>
              </a>
            </div>

            <!-- In-line Inquiry Form -->
            <div v-if="!inquirySubmitted">
              <strong style="display:block; font-size: 0.95rem; color: #0A1128; margin-bottom: 12px;">Direct Mandate Inquiry</strong>
              <form @submit.prevent="submitInquiry">
                <div class="form-group" style="margin-bottom: 10px;">
                  <input v-model="inquiryForm.name" type="text" placeholder="Your Name" required class="form-input" />
                </div>
                <div class="form-group" style="margin-bottom: 10px;">
                  <input v-model="inquiryForm.phone" type="tel" placeholder="Phone Number (+880)" required class="form-input" />
                </div>
                <div class="form-group" style="margin-bottom: 10px;">
                  <input v-model="inquiryForm.email" type="email" placeholder="Email Address" required class="form-input" />
                </div>
                <div class="form-group" style="margin-bottom: 14px;">
                  <textarea v-model="inquiryForm.message" rows="3" class="form-textarea" placeholder="I am interested in this listing and would like legal documentation & price negotiation details..."></textarea>
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%;" :disabled="isSubmittingInquiry">
                  <span>{{ isSubmittingInquiry ? 'Dispatching...' : 'Send Direct Inquiry' }}</span>
                </button>
              </form>
            </div>

            <div v-else class="text-center animate-fade-in" style="padding: 16px; background: #ECFDF5; border-radius: var(--radius-md); color: #059669;">
              <strong>Inquiry Dispatched!</strong>
              <p style="font-size: 0.85rem; margin-top: 4px; color: #047857;">{{ agent.name }} will reach out to you within 30 minutes.</p>
            </div>
          </div>
        </aside>
      </div>
    </div>

    <!-- Schedule Site Visit Modal -->
    <ScheduleModal 
      v-if="property"
      :is-open="scheduleModalOpen" 
      :property-id="property.id" 
      :property-title="property.title"
      @close="scheduleModalOpen = false" 
    />
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useProperties, type PropertyItem } from '~/composables/useProperties'
import { formatBDT } from '~/composables/useCurrency'
import { useAuth } from '~/composables/useAuth'
import { useCompare } from '~/composables/useCompare'
import PropertyTabs from '~/components/PropertyTabs.vue'
import ScheduleModal from '~/components/ScheduleModal.vue'
import { useOverlayBehavior } from '~/composables/useOverlayBehavior'
import { useApiUrl } from '~/composables/useApi'
import { useToast } from '~/composables/useToast'

const toast = useToast()
const route = useRoute()
const { getPropertyById, fetchPropertyById, getAgentById, fetchProperties } = useProperties()
const { isPropertySaved, toggleSaveProperty, user } = useAuth()
const { isInCompare, toggleCompare } = useCompare()

const isLoadingProperty = ref(true)
const dynamicProperty = ref<PropertyItem | null>(null)

// Fallback to local store or dynamic state
const property = computed<PropertyItem>(() => {
  return dynamicProperty.value || getPropertyById(route.params.id as string)
})

const agent = computed(() => {
  if (property.value?.agentId) {
    return getAgentById(property.value.agentId)
  }
  return getAgentById(1)
})

const scheduleModalOpen = ref(false)
const lightboxOpen = ref(false)
const currentLightboxIdx = ref(0)
const inquirySubmitted = ref(false)
const lightboxRoot = ref<HTMLElement | null>(null)

const closeLightbox = () => {
  lightboxOpen.value = false
}

useOverlayBehavior(lightboxOpen, closeLightbox, lightboxRoot)

const inquiryForm = reactive({
  name: user.value.name,
  phone: user.value.phone,
  email: user.value.email,
  message: 'I am interested in this listing and would like to review the legal documents and schedule a physical inspection.'
})

const openLightbox = (idx: number) => {
  currentLightboxIdx.value = idx
  lightboxOpen.value = true
}

const prevPhoto = () => {
  if (!property.value) return
  if (currentLightboxIdx.value > 0) {
    currentLightboxIdx.value--
  } else {
    currentLightboxIdx.value = property.value.images.length - 1
  }
}

const nextPhoto = () => {
  if (!property.value) return
  if (currentLightboxIdx.value < property.value.images.length - 1) {
    currentLightboxIdx.value++
  } else {
    currentLightboxIdx.value = 0
  }
}

const isSubmittingInquiry = ref(false)

onMounted(async () => {
  isLoadingProperty.value = true
  try {
    const idOrSlug = route.params.id as string
    const loaded = await fetchPropertyById(idOrSlug)
    if (loaded) {
      dynamicProperty.value = loaded
    }
  } catch (err) {
    console.error('Failed to load property details:', err)
  } finally {
    isLoadingProperty.value = false
  }
  await fetchProperties()
})

const submitInquiry = async () => {
  if (!property.value) return
  isSubmittingInquiry.value = true
  try {
    await fetch(useApiUrl('/leads'), {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        name: inquiryForm.name,
        phone: inquiryForm.phone,
        email: inquiryForm.email,
        property_title: property.value.title,
        buyer_type: 'Direct Buyer',
        message: inquiryForm.message,
        source: 'Property Detail In-line Inquiry'
      })
    })
    inquirySubmitted.value = true
  } catch (err) {
    console.error('Inquiry dispatch error:', err)
    inquirySubmitted.value = true
  } finally {
    isSubmittingInquiry.value = false
  }
}

const requestBrochure = () => {
  if (!property.value) return
  inquiryForm.message = `Hello, please email me the official architectural brochure, floor layout, and legal deeds for "${property.value.title}".`
  toast.info('Brochure Request', 'Please submit the inquiry form below and our advisor will dispatch the PDF deck.')
}

const requestConfidentialPricing = () => {
  if (!property.value) return
  inquiryForm.message = `Confidential NDA & Pricing Inquiry for "${property.value.title}". Please dispatch valuation breakdown and non-disclosure agreement.`
  toast.info('Confidential Mandate', 'Please submit the inquiry form to receive private valuation disclosures.')
}

const requestFloorPlan = () => {
  if (!property.value) return
  inquiryForm.message = `Request for Architectural Floor Plans & Blueprints for "${property.value.title}" under Non-Disclosure Agreement.`
  toast.info('Floor Plan Request', 'Submit inquiry to receive confidential architectural layout.')
}
</script>

<style scoped>
@media (max-width: 992px) {
  .property-detail-grid {
    grid-template-columns: 1fr !important;
  }
}
</style>

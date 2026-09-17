<template>
  <div style="background: #F8FAFC; min-height: 100vh; padding: 40px 0 80px;">
    <div class="container">
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
          <div class="flex items-center gap-2" style="margin-bottom: 8px;">
            <span v-if="property.isRajukApproved" class="badge badge-rajuk">RAJUK Approved Plan</span>
            <span class="badge badge-status">{{ property.propertyType }}</span>
            <span class="badge badge-featured">{{ property.listingType }}</span>
            <span v-if="property.hasOpenHouse" class="badge badge-urgent">Open House Scheduled</span>
          </div>

          <h1 style="font-size: 2.4rem; font-weight: 800; color: #0A1128; line-height: 1.25; margin-bottom: 8px;">
            {{ property.title }}
          </h1>

          <div class="flex items-center gap-2" style="color: #64748B; font-size: 0.95rem;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="2">
              <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
              <circle cx="12" cy="10" r="3"/>
            </svg>
            <span>{{ property.address }} ({{ property.areaName }}, {{ property.city }})</span>
          </div>
        </div>

        <!-- Price Display -->
        <div style="text-align: right; background: #FFFFFF; border: 1.5px solid var(--color-border); border-radius: var(--radius-lg); padding: 16px 24px; box-shadow: var(--shadow-sm);">
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
        </main>

        <!-- Sticky Client-Hunter Lead Card & Schedule Site Visit -->
        <aside>
          <div style="background: #FFFFFF; border: 1px solid var(--color-border); border-radius: var(--radius-xl); padding: 28px; box-shadow: var(--shadow-md); position: sticky; top: 100px;">
            <!-- VIP Site Visit Button (Primary Hunter CTA) -->
            <button class="btn btn-emerald btn-lg" style="width: 100%; margin-bottom: 20px; font-weight: 800;" @click="scheduleModalOpen = true">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                <line x1="16" y1="2" x2="16" y2="6"/>
                <line x1="8" y1="2" x2="8" y2="6"/>
                <line x1="3" y1="10" x2="21" y2="10"/>
              </svg>
              <span>Schedule VIP Site Viewing</span>
            </button>

            <!-- Agent Profile Summary -->
            <div class="flex items-center gap-4" style="margin-bottom: 20px; padding-bottom: 18px; border-bottom: 1px solid var(--color-border);">
              <img :src="agent.photo" :alt="agent.name" style="width: 60px; height: 60px; border-radius: 50%; object-fit: cover; border: 2px solid var(--color-gold);" />
              <div>
                <NuxtLink :to="`/agents/${agent.id}`">
                  <h4 style="font-size: 1.1rem; font-weight: 800; color: #0F172A; line-height: 1.2;">{{ agent.name }}</h4>
                </NuxtLink>
                <div style="font-size: 0.8rem; color: #059669; font-weight: 700;"><span aria-hidden="true">★</span> {{ agent.rating }} ({{ agent.reviewCount }} Reviews)</div>
                <div style="font-size: 0.8rem; color: #64748B;">{{ agent.agency }}</div>
              </div>
            </div>

            <!-- Agent Contact Buttons -->
            <div class="flex gap-2" style="margin-bottom: 24px;">
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
              <strong style="display:block; font-size: 0.95rem; color: #0A1128; margin-bottom: 12px;">Direct Property Inquiry</strong>
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
                <button type="submit" class="btn btn-primary" style="width: 100%;">
                  <span>Send Direct Inquiry</span>
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
import { useProperties } from '~/composables/useProperties'
import { formatBDT } from '~/composables/useCurrency'
import { useAuth } from '~/composables/useAuth'
import { useCompare } from '~/composables/useCompare'
import PropertyTabs from '~/components/PropertyTabs.vue'
import ScheduleModal from '~/components/ScheduleModal.vue'
import { useOverlayBehavior } from '~/composables/useOverlayBehavior'
import { useApiUrl } from '~/composables/useApi'

const route = useRoute()
const { getPropertyById, getAgentById, fetchProperties } = useProperties()
const { isPropertySaved, toggleSaveProperty, user } = useAuth()
const { isInCompare, toggleCompare } = useCompare()

const property = computed(() => getPropertyById(route.params.id as string))
const agent = computed(() => getAgentById(property.value.agentId))

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
  if (currentLightboxIdx.value > 0) {
    currentLightboxIdx.value--
  } else {
    currentLightboxIdx.value = property.value.images.length - 1
  }
}

const nextPhoto = () => {
  if (currentLightboxIdx.value < property.value.images.length - 1) {
    currentLightboxIdx.value++
  } else {
    currentLightboxIdx.value = 0
  }
}

const isSubmittingInquiry = ref(false)

onMounted(async () => {
  await fetchProperties()
})

const submitInquiry = async () => {
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
</script>

<style scoped>
@media (max-width: 992px) {
  .property-detail-grid {
    grid-template-columns: 1fr !important;
  }
}
</style>

<template>
  <div style="background: #F8FAFC; min-height: 100vh; padding: 40px 0 80px;">
    <div class="container" style="max-width: 920px;">
      <!-- Breadcrumb -->
      <div class="flex items-center gap-2" style="font-size: 0.85rem; color: #64748B; margin-bottom: 24px;">
        <NuxtLink to="/" style="color: #64748B;">Home</NuxtLink>
        <span>/</span>
        <NuxtLink to="/properties" style="color: #64748B;">Properties</NuxtLink>
        <span>/</span>
        <span style="color: #0F172A; font-weight: 600;">List Your Property / Land Share</span>
      </div>

      <div style="background: #FFFFFF; border: 1px solid var(--color-border); border-radius: var(--radius-xl); padding: 40px; box-shadow: var(--shadow-md);">
        <!-- Header Banner -->
        <div style="margin-bottom: 28px; padding-bottom: 20px; border-bottom: 1px solid var(--color-border);">
          <div class="flex items-center gap-2" style="margin-bottom: 8px;">
            <span class="badge badge-featured">Direct Seller & Developer Portal</span>
            <span class="badge" style="background: #EDE9FE; color: #7C3AED; font-weight: 700;">Land Share Enabled</span>
          </div>
          <h1 style="font-size: 2.1rem; font-weight: 800; color: #0A1128; line-height: 1.25;">
            List Property Mandate or Land Share Project
          </h1>
          <p style="color: #64748B; font-size: 0.95rem; margin-top: 6px;">
            Publish flats, residential plots, freehold land, or <strong>Land Share (co-ownership)</strong> ventures directly to the GBREL live catalog with legal documentation and PDF brochures.
          </p>
        </div>

        <div v-if="!submitted">
          <form @submit.prevent="handleSubmit">
            <!-- SECTION 1: CORE DETAILS -->
            <div style="margin-bottom: 28px;">
              <h3 style="font-size: 1.15rem; font-weight: 800; color: #0F172A; margin-bottom: 14px; display: flex; align-items: center; gap: 8px;">
                <span>1. Mandate Information & Category</span>
              </h3>

              <div class="grid grid-2" style="gap: 16px; margin-bottom: 16px;">
                <div class="form-group">
                  <label class="form-label">Property / Project Title *</label>
                  <input 
                    v-model="form.title" 
                    type="text" 
                    placeholder="e.g. 5 Katha Land Share Project at Purbachal Sector 21" 
                    required 
                    class="form-input" 
                  />
                </div>
                <div class="form-group">
                  <label class="form-label">Asset Category *</label>
                  <select v-model="form.propertyType" required class="form-select">
                    <option value="Land Share">Land Share (Co-Ownership Project)</option>
                    <option value="Flat">Flat / Luxury Apartment</option>
                    <option value="Plot">Residential Plot (Katha)</option>
                    <option value="Land">Freehold Land (Bigha / Commercial)</option>
                    <option value="Hotel">Hotel & Beach Resort Suite</option>
                    <option value="Duplex">Duplex Villa / Penthouse</option>
                    <option value="Commercial">Commercial Office / Space</option>
                  </select>
                </div>
              </div>

              <!-- Land Share Info Highlight -->
              <div 
                v-if="form.propertyType === 'Land Share'" 
                class="animate-fade-in" 
                style="background: #F5F3FF; border: 1.5px solid #DDD6FE; border-radius: var(--radius-md); padding: 14px 16px; margin-bottom: 16px;"
              >
                <div class="flex items-start gap-3">
                  <span style="font-size: 1.4rem;">🤝</span>
                  <div>
                    <strong style="color: #6D28D9; font-size: 0.95rem;">Land Share (জমি শেয়ার মডেল) Mandate Selected</strong>
                    <p style="font-size: 0.85rem; color: #5B21B6; margin-top: 2px;">
                      Specify the share price (land share + estimated construction budget), total land size (Katha/Bigha), and upload the project brochure below so investors can examine architectural plans and mutual deed terms.
                    </p>
                  </div>
                </div>
              </div>

              <div class="grid grid-2" style="gap: 16px;">
                <div class="form-group">
                  <label class="form-label">Tagline / Highlight Headline</label>
                  <input 
                    v-model="form.tagline" 
                    type="text" 
                    placeholder="e.g. Registered Sub-Deed with Direct Developer Construction at Cost" 
                    class="form-input" 
                  />
                </div>
                <div class="form-group">
                  <label class="form-label">Listing Intent</label>
                  <select v-model="form.listingType" class="form-select">
                    <option value="Sale">Outright Sale / Member Share</option>
                    <option value="Lease">Commercial Lease / Rental</option>
                  </select>
                </div>
              </div>
            </div>

            <!-- SECTION 2: LOCATION & GEOGRAPHY -->
            <div style="margin-bottom: 28px; padding-top: 20px; border-top: 1px solid var(--color-border);">
              <h3 style="font-size: 1.15rem; font-weight: 800; color: #0F172A; margin-bottom: 14px;">
                2. Location & Geographic Enclave
              </h3>

              <div class="grid grid-3" style="gap: 16px; margin-bottom: 16px;">
                <div class="form-group">
                  <label class="form-label">Division / Region *</label>
                  <select v-model="form.state" required class="form-select">
                    <option value="Dhaka North">Dhaka North (Gulshan, Banani, Uttara, Purbachal)</option>
                    <option value="Dhaka South">Dhaka South (Dhanmondi, Motijheel)</option>
                    <option value="Chittagong">Chittagong & Cox's Bazar</option>
                    <option value="Sylhet">Sylhet & Sreemangal</option>
                  </select>
                </div>
                <div class="form-group">
                  <label class="form-label">Neighborhood / Area Hub *</label>
                  <input 
                    v-model="form.areaName" 
                    type="text" 
                    placeholder="e.g. Purbachal Sector 21 / Gulshan-2" 
                    required 
                    class="form-input" 
                  />
                </div>
                <div class="form-group">
                  <label class="form-label">City *</label>
                  <input 
                    v-model="form.city" 
                    type="text" 
                    placeholder="e.g. Dhaka" 
                    required 
                    class="form-input" 
                  />
                </div>
              </div>

              <div class="form-group">
                <label class="form-label">Full Address & Landmarks *</label>
                <input 
                  v-model="form.address" 
                  type="text" 
                  placeholder="Plot #, Road #, Sector/Block, Near 300 Feet Expressway" 
                  required 
                  class="form-input" 
                />
              </div>
            </div>

            <!-- SECTION 3: PRICING & SPECIFICATIONS -->
            <div style="margin-bottom: 28px; padding-top: 20px; border-top: 1px solid var(--color-border);">
              <h3 style="font-size: 1.15rem; font-weight: 800; color: #0F172A; margin-bottom: 14px;">
                3. Financial Valuation & Physical Specs
              </h3>

              <div class="grid grid-2" style="gap: 16px; margin-bottom: 16px;">
                <div class="form-group">
                  <label class="form-label">
                    {{ form.propertyType === 'Land Share' ? 'Share Price (BDT Taka) *' : 'Asking Valuation (BDT Taka) *' }}
                  </label>
                  <input 
                    v-model.number="form.price" 
                    type="number" 
                    placeholder="e.g. 3800000 (38 Lakh)" 
                    required 
                    class="form-input" 
                  />
                  <div v-if="form.price" style="font-size: 0.85rem; color: #059669; font-weight: 700; margin-top: 4px;">
                    Formatted: {{ formatBDT(form.price) }}
                  </div>
                </div>
                <div class="form-group">
                  <label class="form-label">Price Note / Suffix</label>
                  <input 
                    v-model="form.priceUnit" 
                    type="text" 
                    :placeholder="form.propertyType === 'Land Share' ? 'Per Share (Land + Foundation)' : 'Total Price / Fixed'" 
                    class="form-input" 
                  />
                </div>
              </div>

              <div class="grid grid-4" style="gap: 14px; margin-bottom: 16px;">
                <div class="form-group">
                  <label class="form-label">Built Area (Sq. Ft.)</label>
                  <input v-model.number="form.squareFootage" type="number" placeholder="1850" class="form-input" />
                </div>
                <div class="form-group">
                  <label class="form-label">Land Size</label>
                  <input v-model.number="form.landSize" type="number" step="0.5" placeholder="5" class="form-input" />
                </div>
                <div class="form-group">
                  <label class="form-label">Land Unit</label>
                  <select v-model="form.landUnit" class="form-select">
                    <option value="Katha">Katha</option>
                    <option value="Bigha">Bigha</option>
                    <option value="Shotok">Shotok / Decimal</option>
                    <option value="Sqft">Sq. Ft.</option>
                  </select>
                </div>
                <div class="form-group">
                  <label class="form-label">Bedrooms</label>
                  <input v-model.number="form.bedrooms" type="number" placeholder="3" class="form-input" />
                </div>
              </div>

              <div class="grid grid-4" style="gap: 14px;">
                <div class="form-group">
                  <label class="form-label">Bathrooms</label>
                  <input v-model.number="form.bathrooms" type="number" placeholder="3" class="form-input" />
                </div>
                <div class="form-group">
                  <label class="form-label">Parking Bays</label>
                  <input v-model.number="form.parking" type="number" placeholder="1" class="form-input" />
                </div>
                <div class="form-group">
                  <label class="form-label">Facing</label>
                  <select v-model="form.facing" class="form-select">
                    <option value="South">South</option>
                    <option value="North">North</option>
                    <option value="East">East</option>
                    <option value="West">West</option>
                    <option value="South-East">South-East</option>
                    <option value="North-East">North-East</option>
                  </select>
                </div>
                <div class="form-group">
                  <label class="form-label">Construction Stage</label>
                  <select v-model="form.completionStatus" class="form-select">
                    <option value="Ready">Ready</option>
                    <option value="Under Construction">Under Construction</option>
                    <option value="Upcoming Project">Upcoming Project</option>
                  </select>
                </div>
              </div>
            </div>

            <!-- SECTION 4: MEDIA, IMAGES & OFFICIAL BROCHURE UPLOAD -->
            <div style="margin-bottom: 28px; padding-top: 20px; border-top: 1px solid var(--color-border);">
              <h3 style="font-size: 1.15rem; font-weight: 800; color: #0F172A; margin-bottom: 14px;">
                4. Visual Assets & Official Project Brochure
              </h3>

              <!-- Feature / Cover Image -->
              <div style="background: #F8FAFC; border: 1px solid var(--color-border); border-radius: var(--radius-md); padding: 18px; margin-bottom: 16px;">
                <label class="form-label" style="font-weight: 700; color: #0A1128;">
                  Primary Feature / Cover Photo *
                </label>
                <div class="grid grid-2" style="gap: 16px; align-items: center;">
                  <div style="position: relative; border-radius: var(--radius-md); overflow: hidden; height: 160px; background: #E2E8F0; border: 1px solid var(--color-border);">
                    <img 
                      :src="form.featureImage || 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?q=80&w=600&auto=format&fit=crop'" 
                      alt="Cover Preview" 
                      style="width: 100%; height: 100%; object-fit: cover;" 
                    />
                    <div style="position: absolute; bottom: 6px; left: 6px; background: rgba(10,17,40,0.8); color: #FFF; font-size: 0.72rem; padding: 2px 8px; border-radius: 4px; font-weight: 700;">
                      Cover Preview
                    </div>
                  </div>

                  <div>
                    <input 
                      ref="featureFileInput" 
                      type="file" 
                      accept="image/*" 
                      style="display: none;" 
                      @change="handleFeatureUpload" 
                    />
                    <button 
                      type="button" 
                      class="btn btn-sm btn-emerald" 
                      :disabled="isUploadingFeature"
                      @click="triggerFeatureUpload"
                      style="width: 100%; margin-bottom: 10px; justify-content: center;"
                    >
                      <span v-if="isUploadingFeature" class="animate-spin">◌</span>
                      <span>{{ isUploadingFeature ? 'Uploading Cover Photo...' : '📁 Upload Local Cover Photo' }}</span>
                    </button>
                    <input 
                      v-model="form.featureImage" 
                      type="url" 
                      placeholder="Or paste direct image URL (https://...)" 
                      class="form-input" 
                      style="font-size: 0.85rem;" 
                    />
                  </div>
                </div>
              </div>

              <!-- Gallery Photos -->
              <div style="background: #F8FAFC; border: 1px solid var(--color-border); border-radius: var(--radius-md); padding: 18px; margin-bottom: 16px;">
                <div class="flex items-center justify-between flex-wrap gap-2" style="margin-bottom: 12px;">
                  <label class="form-label" style="font-weight: 700; color: #0A1128; margin-bottom: 0;">
                    Additional Gallery Photos ({{ form.gallery.length }} Selected)
                  </label>
                  <input 
                    ref="galleryFileInput" 
                    type="file" 
                    multiple 
                    accept="image/*" 
                    style="display: none;" 
                    @change="handleGalleryUpload" 
                  />
                  <button 
                    type="button" 
                    class="btn btn-sm btn-outline" 
                    :disabled="isUploadingGallery"
                    @click="triggerGalleryUpload"
                    style="font-size: 0.8rem;"
                  >
                    <span v-if="isUploadingGallery" class="animate-spin">◌</span>
                    <span>{{ isUploadingGallery ? 'Uploading Photos...' : '📁 Upload Multiple Photos' }}</span>
                  </button>
                </div>

                <div v-if="form.gallery.length > 0" class="flex flex-wrap gap-3">
                  <div 
                    v-for="(img, idx) in form.gallery" 
                    :key="idx" 
                    style="position: relative; width: 80px; height: 80px; border-radius: 6px; overflow: hidden; border: 1px solid var(--color-border);"
                  >
                    <img :src="img" alt="thumb" style="width: 100%; height: 100%; object-fit: cover;" />
                    <button 
                      type="button" 
                      @click="removeGalleryPhoto(idx)" 
                      style="position: absolute; top: 2px; right: 2px; background: rgba(225,29,72,0.9); color: #FFF; border: none; border-radius: 50%; width: 20px; height: 20px; font-size: 11px; cursor: pointer; display: flex; align-items: center; justify-content: center;"
                      title="Remove"
                    >✕</button>
                  </div>
                </div>
                <div v-else style="font-size: 0.85rem; color: #64748B;">
                  No extra gallery photos uploaded yet. You can upload interior, road-width, and architectural layout shots.
                </div>
              </div>

              <!-- BROCHURE UPLOAD (PDF / DOC) -->
              <div style="background: #EFF6FF; border: 1.5px solid #BFDBFE; border-radius: var(--radius-md); padding: 18px;">
                <div class="flex items-center justify-between flex-wrap gap-2" style="margin-bottom: 8px;">
                  <label class="form-label" style="font-weight: 800; color: #1E3A8A; margin-bottom: 0; display: flex; align-items: center; gap: 6px;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#2563EB" stroke-width="2.2">
                      <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                      <polyline points="14 2 14 8 20 8"/>
                    </svg>
                    <span>Official Project Brochure (PDF / Document)</span>
                  </label>
                  <span class="badge" style="background: #DBEAFE; color: #1D4ED8; font-size: 0.75rem;">
                    Downloadable by Buyers
                  </span>
                </div>

                <p style="font-size: 0.85rem; color: #1E40AF; margin-bottom: 12px;">
                  Attach the complete master plan, floor layouts, land share agreement terms, or developer profile (PDF, DOC, DOCX up to 25MB).
                </p>

                <!-- Hidden Brochure File Input -->
                <input 
                  ref="brochureFileInput" 
                  type="file" 
                  accept=".pdf,.doc,.docx,application/pdf" 
                  style="display: none;" 
                  @change="handleBrochureUpload" 
                />

                <div v-if="form.brochureUrl" class="flex items-center justify-between gap-3" style="background: #FFFFFF; border: 1px solid #93C5FD; border-radius: var(--radius-sm); padding: 12px 16px; margin-bottom: 10px;">
                  <div class="flex items-center gap-3" style="overflow: hidden;">
                    <div style="width: 38px; height: 38px; border-radius: 6px; background: #FEE2E2; color: #DC2626; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 0.85rem; flex-shrink: 0;">
                      PDF
                    </div>
                    <div style="overflow: hidden;">
                      <a :href="form.brochureUrl" target="_blank" style="font-size: 0.9rem; font-weight: 700; color: #1D4ED8; text-decoration: underline; display: block; text-overflow: ellipsis; overflow: hidden; white-space: nowrap;">
                        {{ form.brochureUrl.split('/').pop() || 'Attached Project Brochure' }}
                      </a>
                      <span style="font-size: 0.75rem; color: #059669; font-weight: 600;">✓ Ready for download on single property page</span>
                    </div>
                  </div>
                  <div class="flex items-center gap-2 flex-shrink-0">
                    <a :href="form.brochureUrl" target="_blank" class="btn btn-sm btn-outline" style="font-size: 0.78rem; padding: 4px 10px;">
                      Preview
                    </a>
                    <button type="button" class="btn btn-sm btn-outline" style="color: #DC2626; border-color: #FCA5A5; font-size: 0.78rem; padding: 4px 10px;" @click="form.brochureUrl = ''">
                      Remove
                    </button>
                  </div>
                </div>

                <div class="flex items-center gap-3">
                  <button 
                    type="button" 
                    class="btn btn-sm btn-primary" 
                    :disabled="isUploadingBrochure"
                    @click="triggerBrochureUpload"
                    style="font-size: 0.82rem;"
                  >
                    <span v-if="isUploadingBrochure" class="animate-spin">◌</span>
                    <span>{{ isUploadingBrochure ? 'Uploading Brochure PDF...' : '📁 Upload Brochure PDF File' }}</span>
                  </button>
                  <span style="font-size: 0.8rem; color: #64748B;">or paste URL:</span>
                  <input 
                    v-model="form.brochureUrl" 
                    type="url" 
                    placeholder="https://.../brochure.pdf" 
                    class="form-input" 
                    style="flex: 1; font-size: 0.82rem;" 
                  />
                </div>
              </div>
            </div>

            <!-- SECTION 5: DESCRIPTION & DUE DILIGENCE -->
            <div style="margin-bottom: 28px; padding-top: 20px; border-top: 1px solid var(--color-border);">
              <h3 style="font-size: 1.15rem; font-weight: 800; color: #0F172A; margin-bottom: 14px;">
                5. Comprehensive Description & Legal Approval
              </h3>

              <div class="form-group" style="margin-bottom: 16px;">
                <label class="form-label">Full Mandate Description & Investment Highlights *</label>
                <textarea 
                  v-model="form.description" 
                  rows="4" 
                  placeholder="Detail road width, building height approval, sub-deed registration timelines, mutation status, and handover commitments..." 
                  class="form-textarea" 
                  required
                ></textarea>
              </div>

              <div class="flex items-center gap-6 flex-wrap" style="background: #F8FAFC; padding: 14px 18px; border-radius: var(--radius-md); border: 1px solid var(--color-border);">
                <label class="flex items-center gap-2" style="cursor: pointer; font-size: 0.9rem; font-weight: 600; color: #0F172A;">
                  <input v-model="form.isRajukApproved" type="checkbox" style="width: 18px; height: 18px; accent-color: #059669;" />
                  <span>RAJUK / CDA Approved Building Plan Verified</span>
                </label>
                <label class="flex items-center gap-2" style="cursor: pointer; font-size: 0.9rem; font-weight: 600; color: #0F172A;">
                  <input v-model="form.isVerified" type="checkbox" style="width: 18px; height: 18px; accent-color: #059669;" />
                  <span>Title Deed & Mutation Cleared</span>
                </label>
              </div>
            </div>

            <!-- Submit Button -->
            <button 
              type="submit" 
              class="btn btn-emerald btn-lg" 
              :disabled="isSubmitting"
              style="width: 100%; justify-content: center; font-size: 1.05rem;"
            >
              <span v-if="isSubmitting" class="animate-spin">◌</span>
              <span>{{ isSubmitting ? 'Recording Mandate in Live Database...' : '🚀 Submit Property & Publish to Live Catalog' }}</span>
            </button>
          </form>
        </div>

        <!-- Submission Success State -->
        <div v-else class="text-center animate-fade-in" style="padding: 30px 0;">
          <div style="width: 72px; height: 72px; border-radius: 50%; background: #ECFDF5; color: #059669; margin: 0 auto 20px; display: flex; align-items: center; justify-content: center; font-size: 2.4rem;">
            ✓
          </div>
          <h2 style="font-size: 1.9rem; font-weight: 800; color: #0F172A; margin-bottom: 8px;">
            Property Mandate Listed Successfully!
          </h2>
          <p style="color: #64748B; font-size: 1rem; max-width: 540px; margin: 0 auto 24px;">
            Your listing has been securely recorded into the live GBREL catalog with assigned ID #{{ createdId }}. Potential buyers and investors can view the specifications and download your project brochure.
          </p>
          <div class="flex justify-center gap-4 flex-wrap">
            <NuxtLink :to="`/properties/${createdId}`" class="btn btn-primary btn-lg">
              View Your Live Listing (#{{ createdId }})
            </NuxtLink>
            <NuxtLink to="/properties" class="btn btn-outline btn-lg">
              Browse All Properties
            </NuxtLink>
            <button class="btn btn-outline" @click="resetForm">
              + List Another Property
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { reactive, ref } from 'vue'
import { useProperties } from '~/composables/useProperties'
import { formatBDT } from '~/composables/useCurrency'
import { useToast } from '~/composables/useToast'

const { addProperty, uploadImage, uploadMultipleImages, uploadBrochure } = useProperties()
const toast = useToast()

const submitted = ref(false)
const createdId = ref<number | null>(null)
const isSubmitting = ref(false)

const isUploadingFeature = ref(false)
const isUploadingGallery = ref(false)
const isUploadingBrochure = ref(false)

const featureFileInput = ref<HTMLInputElement | null>(null)
const galleryFileInput = ref<HTMLInputElement | null>(null)
const brochureFileInput = ref<HTMLInputElement | null>(null)

const form = reactive({
  title: '',
  tagline: '',
  propertyType: 'Land Share' as any,
  state: 'Dhaka North',
  areaName: 'Purbachal',
  city: 'Dhaka',
  address: '',
  price: 3800000,
  priceUnit: 'Per Share (Land + Construction)',
  listingType: 'Sale',
  squareFootage: 1850,
  landSize: 5,
  landUnit: 'Katha' as const,
  description: '',
  bedrooms: 3,
  bathrooms: 3,
  parking: 1,
  facing: 'South' as const,
  completionStatus: 'Upcoming Project' as const,
  isRajukApproved: true,
  isVerified: true,
  featureImage: 'https://images.unsplash.com/photo-1540555700478-4be289fbecef?q=80&w=1600&auto=format&fit=crop',
  gallery: [] as string[],
  brochureUrl: '',
  agentId: 1
})

const triggerFeatureUpload = () => {
  featureFileInput.value?.click()
}

const handleFeatureUpload = async (e: Event) => {
  const target = e.target as HTMLInputElement
  if (!target.files || target.files.length === 0) return
  isUploadingFeature.value = true
  try {
    const url = await uploadImage(target.files[0])
    form.featureImage = url
    toast.success('Cover Uploaded', 'Feature cover photo successfully uploaded.')
  } catch (err: any) {
    toast.error('Upload Error', err.message || 'Could not upload cover image.')
  } finally {
    isUploadingFeature.value = false
    target.value = ''
  }
}

const triggerGalleryUpload = () => {
  galleryFileInput.value?.click()
}

const handleGalleryUpload = async (e: Event) => {
  const target = e.target as HTMLInputElement
  if (!target.files || target.files.length === 0) return
  isUploadingGallery.value = true
  try {
    const urls = await uploadMultipleImages(target.files)
    form.gallery.push(...urls)
    toast.success('Gallery Photos Uploaded', `${urls.length} photo(s) added.`)
  } catch (err: any) {
    toast.error('Upload Error', err.message || 'Could not upload gallery images.')
  } finally {
    isUploadingGallery.value = false
    target.value = ''
  }
}

const removeGalleryPhoto = (index: number) => {
  form.gallery.splice(index, 1)
}

const triggerBrochureUpload = () => {
  brochureFileInput.value?.click()
}

const handleBrochureUpload = async (e: Event) => {
  const target = e.target as HTMLInputElement
  if (!target.files || target.files.length === 0) return
  isUploadingBrochure.value = true
  try {
    const url = await uploadBrochure(target.files[0])
    form.brochureUrl = url
    toast.success('Brochure Uploaded', 'Project brochure file saved and attached.')
  } catch (err: any) {
    toast.error('Upload Error', err.message || 'Could not upload brochure.')
  } finally {
    isUploadingBrochure.value = false
    target.value = ''
  }
}

const handleSubmit = async () => {
  isSubmitting.value = true
  try {
    const validGallery = form.gallery.filter(Boolean)
    const cover = form.featureImage || 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?q=80&w=1600&auto=format&fit=crop'
    const allImages = [cover, ...validGallery]

    const payload = {
      ...form,
      feature_image: cover,
      featureImage: cover,
      gallery: validGallery,
      images: allImages,
      brochure_url: form.brochureUrl.trim() || undefined,
      brochureUrl: form.brochureUrl.trim() || undefined
    }

    const created = await addProperty(payload)
    createdId.value = created?.id || (typeof created === 'number' ? created : 1)
    submitted.value = true
    toast.success('Property Listed', `Your mandate "${form.title}" is now recorded live in MySQL.`)
  } catch (err: any) {
    toast.error('Listing Failed', err.message || 'Unable to record property in database.')
  } finally {
    isSubmitting.value = false
  }
}

const resetForm = () => {
  submitted.value = false
  createdId.value = null
  form.title = ''
  form.tagline = ''
  form.description = ''
  form.brochureUrl = ''
  form.gallery = []
}
</script>

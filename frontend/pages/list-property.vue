<template>
  <div style="background: #F8FAFC; min-height: 100vh; padding: 40px 0 80px;">
    <div class="container" style="max-width: 860px;">
      <!-- Breadcrumb -->
      <div class="flex items-center gap-2" style="font-size: 0.85rem; color: #64748B; margin-bottom: 24px;">
        <NuxtLink to="/" style="color: #64748B;">Home</NuxtLink>
        <span>/</span>
        <span style="color: #0F172A; font-weight: 600;">List Your Property</span>
      </div>

      <div style="background: #FFFFFF; border: 1px solid var(--color-border); border-radius: var(--radius-xl); padding: 40px; box-shadow: var(--shadow-md);">
        <div style="margin-bottom: 28px; padding-bottom: 20px; border-bottom: 1px solid var(--color-border);">
          <span class="badge badge-featured" style="margin-bottom: 8px;">Direct Seller Portal</span>
          <h1 style="font-size: 2rem; font-weight: 800; color: #0A1128;">Sell or Lease Your Property</h1>
          <p style="color: #64748B; font-size: 0.95rem;">
            List your flat, residential plot, or commercial space on GBREL. Our legal and marketing team will conduct free verification and connect you with qualified buyers.
          </p>
        </div>

        <div v-if="!submitted">
          <form @submit.prevent="handleSubmit">
            <!-- Property Title & Category -->
            <div class="grid grid-2" style="gap: 16px; margin-bottom: 16px;">
              <div class="form-group">
                <label class="form-label">Property Title / Name</label>
                <input v-model="form.title" type="text" placeholder="e.g. 5 Katha Corner Plot in Purbachal Sector 21" required class="form-input" />
              </div>
              <div class="form-group">
                <label class="form-label">Property Category</label>
                <select v-model="form.propertyType" required class="form-select">
                  <option value="Flat">Flat / Apartment</option>
                  <option value="Plot">Residential Plot (Katha)</option>
                  <option value="Land">Commercial / Agro Land (Bigha)</option>
                  <option value="Hotel">Hotel / Resort Suite</option>
                  <option value="Duplex">Duplex / Penthouse</option>
                  <option value="Commercial">Commercial Office / Space</option>
                </select>
              </div>
            </div>

            <!-- Location & Division -->
            <div class="grid grid-3" style="gap: 16px; margin-bottom: 16px;">
              <div class="form-group">
                <label class="form-label">Division / Region</label>
                <select v-model="form.state" required class="form-select">
                  <option value="Dhaka North">Dhaka North</option>
                  <option value="Dhaka South">Dhaka South</option>
                  <option value="Chittagong">Chittagong</option>
                  <option value="Sylhet">Sylhet</option>
                </select>
              </div>
              <div class="form-group">
                <label class="form-label">Neighborhood / Area</label>
                <input v-model="form.areaName" type="text" placeholder="e.g. Gulshan-2 / Purbachal" required class="form-input" />
              </div>
              <div class="form-group">
                <label class="form-label">Full Street Address</label>
                <input v-model="form.address" type="text" placeholder="Road, Block, House No." required class="form-input" />
              </div>
            </div>

            <!-- Price & Unit -->
            <div class="grid grid-2" style="gap: 16px; margin-bottom: 16px;">
              <div class="form-group">
                <label class="form-label">Asking Price (BDT Taka)</label>
                <input v-model.number="form.price" type="number" placeholder="e.g. 35000000 (3.5 Crore)" required class="form-input" />
                <div v-if="form.price" style="font-size: 0.85rem; color: #059669; font-weight: 700; margin-top: 4px;">
                  Formatted: {{ formatBDT(form.price) }}
                </div>
              </div>
              <div class="form-group">
                <label class="form-label">Listing Type</label>
                <select v-model="form.listingType" class="form-select">
                  <option value="Sale">For Sale (Outright Acquisition)</option>
                  <option value="Lease">For Lease / Rent</option>
                </select>
              </div>
            </div>

            <!-- Dimensions (Sqft / Katha) -->
            <div class="grid grid-3" style="gap: 16px; margin-bottom: 16px;">
              <div class="form-group">
                <label class="form-label">Total Size (Sq. Ft.)</label>
                <input v-model.number="form.squareFootage" type="number" placeholder="e.g. 2400" class="form-input" />
              </div>
              <div class="form-group">
                <label class="form-label">Land Size (Katha/Bigha)</label>
                <input v-model.number="form.landSize" type="number" step="0.5" placeholder="e.g. 5" class="form-input" />
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
            </div>

            <!-- Description -->
            <div class="form-group" style="margin-bottom: 24px;">
              <label class="form-label">Property Description & Highlights</label>
              <textarea v-model="form.description" rows="4" placeholder="Detail features, road width, facing direction, mutation status..." class="form-textarea" required></textarea>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="btn btn-emerald btn-lg" style="width: 100%;">
              <span>Submit Property for Verification & Live Listing</span>
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <polyline points="20 6 9 17 4 12"/>
              </svg>
            </button>
          </form>
        </div>

        <div v-else class="text-center animate-fade-in" style="padding: 30px 0;">
          <div style="width: 68px; height: 68px; border-radius: 50%; background: #ECFDF5; color: #059669; margin: 0 auto 20px; display: flex; align-items: center; justify-content: center; font-size: 2.2rem;">
            ✓
          </div>
          <h2 style="font-size: 1.8rem; font-weight: 800; color: #0F172A; margin-bottom: 8px;">Property Listed Successfully!</h2>
          <p style="color: #64748B; font-size: 1rem; max-width: 500px; margin: 0 auto 24px;">
            Your listing has been created and indexed into the GBREL live catalog. Our legal team will review the title records within 24 hours.
          </p>
          <div class="flex justify-center gap-4">
            <NuxtLink :to="`/properties/${createdId}`" class="btn btn-primary">View Your Live Listing</NuxtLink>
            <NuxtLink to="/dashboard?tab=listings" class="btn btn-outline">Go to Dashboard</NuxtLink>
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

const { addProperty } = useProperties()
const submitted = ref(false)
const createdId = ref<number | null>(null)

const form = reactive({
  title: '',
  propertyType: 'Flat',
  state: 'Dhaka North',
  areaName: '',
  address: '',
  price: 25000000,
  listingType: 'Sale',
  squareFootage: 2200,
  landSize: 0,
  landUnit: 'Katha',
  description: '',
  bedrooms: 3,
  bathrooms: 3,
  parking: 1,
  facing: 'South',
  completionStatus: 'Ready',
  lat: 23.7925,
  lng: 90.4167,
  agentId: 1
})

const handleSubmit = async () => {
  const newId = await addProperty(form)
  createdId.value = newId
  submitted.value = true
}
</script>

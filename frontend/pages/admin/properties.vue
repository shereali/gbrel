<template>
  <div class="admin-page animate-fade-in">
    <div class="admin-header-row">
      <div>
        <h1 class="page-title">Property & Land Catalog (CRUD Engine)</h1>
        <p class="page-subtitle">Add, edit, delete, verify legal titles, and manage live status of luxury properties and land mandates across Bangladesh.</p>
      </div>
      <div class="admin-header-actions">
        <button class="btn btn-emerald" @click="openAddPropertyModal">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <line x1="12" y1="5" x2="12" y2="19"/>
            <line x1="5" y1="12" x2="19" y2="12"/>
          </svg>
          <span>+ Create New Mandate</span>
        </button>
      </div>
    </div>

    <!-- Search, Filter & Bulk Actions Bar -->
    <div class="panel-card" style="padding:16px 20px; margin-bottom:20px;">
      <div class="flex justify-between items-center flex-wrap gap-4">
        <div class="flex items-center gap-3 flex-wrap flex-1">
          <!-- Search Filter -->
          <label class="sr-only" for="inventory-search">Search property inventory</label>
          <input 
            id="inventory-search"
            v-model="inventorySearch" 
            type="text" 
            placeholder="Search by title, address, area..." 
            class="form-input" 
            style="max-width: 280px;" 
          />

          <!-- Category Filter -->
          <select v-model="inventoryTypeFilter" class="form-select" style="width: auto;">
            <option value="">All Categories</option>
            <option value="Flat">Flats & Apartments</option>
            <option value="Plot">Residential Plots (Katha)</option>
            <option value="Land">Freehold Lands (Bigha)</option>
            <option value="Hotel">Hotel & Beach Resorts</option>
            <option value="Duplex">Duplexes & Penthouses</option>
            <option value="Commercial">Commercial Assets</option>
          </select>

          <!-- Division Filter -->
          <select v-model="inventoryDivisionFilter" class="form-select" style="width: auto;">
            <option value="">All Divisions</option>
            <option value="Dhaka North">Dhaka North</option>
            <option value="Dhaka South">Dhaka South</option>
            <option value="Chittagong">Chittagong & Cox's Bazar</option>
            <option value="Sylhet">Sylhet</option>
          </select>

          <!-- Status Filter -->
          <select v-model="inventoryStatusFilter" class="form-select" style="width: auto;">
            <option value="">All Statuses</option>
            <option value="Active">Active</option>
            <option value="Under Offer">Under Offer</option>
            <option value="Sold">Sold</option>
            <option value="Delisted">Delisted</option>
          </select>
        </div>

        <div class="flex items-center gap-2">
          <span style="font-size:0.85rem; color:#94A3B8; font-weight:600;">Showing {{ filteredProperties.length }} / {{ properties.length }} items</span>
        </div>
      </div>
    </div>

    <!-- Property Table -->
    <div class="panel-card" style="padding:0; overflow:hidden;">
      <div class="table-responsive">
        <table class="admin-table">
          <thead>
            <tr>
              <th>Property & ID</th>
              <th>Category & Dimensions</th>
              <th>Price (BDT)</th>
              <th>Legal Clearance</th>
              <th>Featured</th>
              <th>Status</th>
              <th style="text-align:right;">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="prop in filteredProperties" :key="prop.id">
              <td>
                <div class="flex items-center gap-3">
                  <img :src="prop.images[0] || 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?q=80&w=200&auto=format&fit=crop'" class="table-thumb" alt="thumb" />
                  <div>
                    <strong style="color:#FFF; display:block; max-width:280px; line-height:1.3; font-size:0.92rem;">{{ prop.title }}</strong>
                    <div style="font-size:0.78rem; color:#94A3B8; margin-top:2px;">ID: #{{ prop.id }} • {{ prop.areaName }}, {{ prop.city }}</div>
                  </div>
                </div>
              </td>

              <td>
                <span class="badge badge-status" style="font-size:0.75rem;">{{ prop.propertyType }}</span>
                <div style="font-size:0.8rem; color:#CBD5E1; margin-top:4px;">
                  {{ formatArea(prop.squareFootage, prop.landSize, prop.landUnit) }}
                </div>
              </td>

              <td style="font-family:var(--font-ui); font-size:1.1rem; font-weight:800; color:#10B981; font-variant-numeric:tabular-nums;">
                {{ formatBDT(prop.price) }}
              </td>

              <td>
                <button 
                  class="badge-admin" 
                  :class="prop.isRajukApproved ? 'active' : 'pending'"
                  style="cursor:pointer;"
                  @click="toggleRajuk(prop.id)"
                  title="Toggle RAJUK Approved status"
                >
                  {{ prop.isRajukApproved ? '✔ RAJUK Pass' : 'Pending Audit' }}
                </button>
              </td>

              <td>
                <button 
                  class="star-toggle-btn" 
                  :class="{ active: prop.isFeatured }"
                  @click="toggleFeature(prop.id)"
                  :title="prop.isFeatured ? 'Featured on Homepage' : 'Click to feature on Homepage'"
                >
                  ★
                </button>
              </td>

              <td>
                <select 
                  :value="prop.status" 
                  @change="handleStatusChange(prop.id, $event)"
                  class="status-inline-select"
                >
                  <option value="Active">Active</option>
                  <option value="Under Offer">Under Offer</option>
                  <option value="Sold">Sold</option>
                  <option value="Delisted">Delisted</option>
                </select>
              </td>

              <td style="text-align:right;">
                <div class="action-btn-group">
                  <NuxtLink :to="`/properties/${prop.id}`" class="action-btn" target="_blank" title="Preview on live site" aria-label="Preview on live site">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                      <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                      <circle cx="12" cy="12" r="3"/>
                    </svg>
                  </NuxtLink>
                  <button class="action-btn edit" @click="openEditPropertyModal(prop)" title="Edit details" aria-label="Edit details">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                      <path d="M17 3a2.83 2.83 0 0 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"/>
                    </svg>
                  </button>
                  <button class="action-btn delete" @click="promptDelete(prop)" title="Delete listing" aria-label="Delete listing">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                      <polyline points="3 6 5 6 21 6"/>
                      <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                    </svg>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- ======================================================================
         MODAL 1: ADD / EDIT PROPERTY MANDATE
         ====================================================================== -->
    <div v-if="showPropModal" ref="propModalRoot" class="admin-modal-overlay" @click.self="closePropModal">
      <div class="admin-modal-card wide animate-fade-in-up">
        <div class="admin-modal-header">
          <div>
            <h3 class="admin-modal-title">
              {{ editingPropId ? 'Edit Property Mandate #' + editingPropId : 'Create New Property Mandate' }}
            </h3>
            <p class="panel-sub">Configure asset specifications, pricing, legal documentation, and imagery</p>
          </div>
          <button class="admin-modal-close" @click="closePropModal" aria-label="Close modal">✕</button>
        </div>

        <form @submit.prevent="handleSaveProperty" style="display:flex; flex-direction:column; flex:1; overflow:hidden;">
          <div class="admin-modal-body">
            <!-- Row 1: Title & Category -->
            <div class="grid grid-2" style="gap:14px; margin-bottom:14px;">
              <div class="form-group">
                <label class="form-label">Property Title *</label>
                <input v-model="propForm.title" type="text" required placeholder="e.g. 10 Katha Corner Plot at Purbachal Sector 17" class="form-input" />
              </div>
              <div class="form-group">
                <label class="form-label">Category *</label>
                <select v-model="propForm.propertyType" class="form-select">
                  <option value="Flat">Flat / Luxury Apartment</option>
                  <option value="Plot">Residential Plot (Katha)</option>
                  <option value="Land">Freehold Land (Bigha)</option>
                  <option value="Hotel">Hotel & Beach Resort</option>
                  <option value="Duplex">Duplex & Penthouse</option>
                  <option value="Commercial">Commercial / Corporate Office</option>
                </select>
              </div>
            </div>

            <!-- Row 2: Location & Price -->
            <div class="grid grid-3" style="gap:14px; margin-bottom:14px;">
              <div class="form-group">
                <label class="form-label">Division / Region *</label>
                <select v-model="propForm.state" class="form-select">
                  <option value="Dhaka North">Dhaka North</option>
                  <option value="Dhaka South">Dhaka South</option>
                  <option value="Chittagong">Chittagong & Cox's Bazar</option>
                  <option value="Sylhet">Sylhet</option>
                </select>
              </div>
              <div class="form-group">
                <label class="form-label">Area Name / Hub *</label>
                <input v-model="propForm.areaName" type="text" required placeholder="e.g. Gulshan-2, Purbachal" class="form-input" />
              </div>
              <div class="form-group">
                <label class="form-label">Asking Price (BDT Taka) *</label>
                <input v-model.number="propForm.price" type="number" required placeholder="35000000" class="form-input" />
                <div v-if="propForm.price" style="font-size:0.75rem; color:#10B981; margin-top:3px; font-weight:700;">
                  Formatted: {{ formatBDT(propForm.price) }}
                </div>
              </div>
            </div>

            <!-- Row 3: Dimensions & Specs -->
            <div class="grid grid-4" style="gap:12px; margin-bottom:14px;">
              <div class="form-group">
                <label class="form-label">Sq. Footage</label>
                <input v-model.number="propForm.squareFootage" type="number" placeholder="2400" class="form-input" />
              </div>
              <div class="form-group">
                <label class="form-label">Land Size</label>
                <input v-model.number="propForm.landSize" type="number" step="0.5" placeholder="5" class="form-input" />
              </div>
              <div class="form-group">
                <label class="form-label">Land Unit</label>
                <select v-model="propForm.landUnit" class="form-select">
                  <option value="Katha">Katha</option>
                  <option value="Bigha">Bigha</option>
                  <option value="Shotok">Shotok / Decimal</option>
                  <option value="Sqft">Sq. Ft.</option>
                </select>
              </div>
              <div class="form-group">
                <label class="form-label">Beds / Baths</label>
                <div class="flex gap-2">
                  <input v-model.number="propForm.bedrooms" type="number" placeholder="Beds" class="form-input" />
                  <input v-model.number="propForm.bathrooms" type="number" placeholder="Baths" class="form-input" />
                </div>
              </div>
            </div>

            <!-- Row 4: Primary Image & Address -->
            <div class="grid grid-2" style="gap:14px; margin-bottom:14px;">
              <div class="form-group">
                <label class="form-label">Street Address</label>
                <input v-model="propForm.address" type="text" placeholder="Road, Block, Sector" class="form-input" />
              </div>
              <div class="form-group">
                <label class="form-label">Primary Showcase Image URL</label>
                <input v-model="propForm.imageUrl" type="url" placeholder="https://images.unsplash.com/..." class="form-input" />
              </div>
            </div>

            <!-- Row 5: Flags -->
            <div class="flex items-center gap-6" style="padding:12px 16px; border-radius:8px; border:1px solid var(--admin-border-subtle);">
              <label class="flex items-center gap-2" style="cursor:pointer; font-size:0.88rem;">
                <input v-model="propForm.isRajukApproved" type="checkbox" style="width:16px; height:16px; accent-color:#10B981;" />
                <span>RAJUK / CDA Approved Plan Verified</span>
              </label>
              <label class="flex items-center gap-2" style="cursor:pointer; font-size:0.88rem;">
                <input v-model="propForm.isFeatured" type="checkbox" style="width:16px; height:16px; accent-color:#D4AF37;" />
                <span>Feature on Live Homepage Showcase</span>
              </label>
            </div>
          </div>

          <div class="admin-modal-footer">
            <button type="button" class="btn btn-sm btn-outline-white" @click="closePropModal">Cancel</button>
            <button type="submit" class="btn btn-sm btn-emerald">
              <span>{{ editingPropId ? 'Save Changes' : 'Publish Property Mandate' }}</span>
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- ======================================================================
         MODAL 2: DELETE CONFIRMATION MODAL
         ====================================================================== -->
    <div v-if="deleteModalTarget" class="admin-modal-overlay" @click.self="deleteModalTarget = null">
      <div class="admin-modal-card animate-fade-in-up" style="max-width:440px;">
        <div class="admin-modal-header" style="background:#1E1622; border-bottom:1px solid rgba(239,68,68,0.2);">
          <h3 class="admin-modal-title" style="color:#F87171; display:flex; align-items:center; gap:8px;">
            <span>⚠ Confirm Deletion</span>
          </h3>
          <button class="admin-modal-close" @click="deleteModalTarget = null">✕</button>
        </div>
        <div class="admin-modal-body">
          <p style="color:#E2E8F0; font-size:0.92rem; line-height:1.5;">
            Are you sure you want to permanently delete <strong>"{{ deleteModalTarget.title }}"</strong> (ID #{{ deleteModalTarget.id }})?
          </p>
          <p style="color:#94A3B8; font-size:0.82rem; margin-top:8px;">
            This will remove the listing from the database and delist it from the live portal.
          </p>
        </div>
        <div class="admin-modal-footer">
          <button class="btn btn-sm btn-outline-white" @click="deleteModalTarget = null">Cancel</button>
          <button class="btn btn-sm" style="background:#EF4444; color:#FFF;" @click="executeDelete">
            Permanently Delete
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, computed } from 'vue'
import { useProperties, type PropertyItem } from '~/composables/useProperties'
import { formatBDT, formatArea } from '~/composables/useCurrency'
import { useOverlayBehavior } from '~/composables/useOverlayBehavior'
import { useToast } from '~/composables/useToast'

definePageMeta({
  layout: 'admin'
})

const { properties, addProperty, updateProperty, deleteProperty } = useProperties()
const toast = useToast()

const inventorySearch = ref('')
const inventoryTypeFilter = ref('')
const inventoryDivisionFilter = ref('')
const inventoryStatusFilter = ref('')

const showPropModal = ref(false)
const editingPropId = ref<number | null>(null)
const propModalRoot = ref<HTMLElement | null>(null)
const deleteModalTarget = ref<PropertyItem | null>(null)

const closePropModal = () => {
  showPropModal.value = false
  editingPropId.value = null
}

useOverlayBehavior(showPropModal, closePropModal, propModalRoot)

const propForm = reactive({
  title: '',
  propertyType: 'Flat' as any,
  state: 'Dhaka North',
  areaName: 'Gulshan-2',
  address: 'Kemal Ataturk Avenue, Dhaka',
  price: 35000000,
  listingType: 'Sale' as const,
  bedrooms: 3,
  bathrooms: 3,
  squareFootage: 2400,
  landSize: 0,
  landUnit: 'Katha' as const,
  facing: 'South' as const,
  isRajukApproved: true,
  isFeatured: false,
  imageUrl: 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?q=80&w=1600&auto=format&fit=crop',
  agentId: 1
})

const filteredProperties = computed(() => {
  return properties.value.filter(p => {
    if (inventoryTypeFilter.value && p.propertyType !== inventoryTypeFilter.value) return false
    if (inventoryDivisionFilter.value && p.state !== inventoryDivisionFilter.value) return false
    if (inventoryStatusFilter.value && p.status !== inventoryStatusFilter.value) return false
    if (inventorySearch.value) {
      const q = inventorySearch.value.toLowerCase()
      return p.title.toLowerCase().includes(q) || p.areaName.toLowerCase().includes(q) || p.address.toLowerCase().includes(q) || String(p.id).includes(q)
    }
    return true
  })
})

const openAddPropertyModal = () => {
  editingPropId.value = null
  propForm.title = ''
  propForm.propertyType = 'Flat'
  propForm.state = 'Dhaka North'
  propForm.areaName = ''
  propForm.price = 35000000
  propForm.bedrooms = 3
  propForm.bathrooms = 3
  propForm.squareFootage = 2400
  propForm.landSize = 0
  propForm.isRajukApproved = true
  propForm.isFeatured = false
  propForm.imageUrl = 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?q=80&w=1600&auto=format&fit=crop'
  showPropModal.value = true
}

const openEditPropertyModal = (p: PropertyItem) => {
  editingPropId.value = p.id
  propForm.title = p.title
  propForm.propertyType = p.propertyType as any
  propForm.state = p.state
  propForm.areaName = p.areaName
  propForm.address = p.address
  propForm.price = p.price
  propForm.bedrooms = p.bedrooms
  propForm.bathrooms = p.bathrooms
  propForm.squareFootage = p.squareFootage || 2000
  propForm.landSize = p.landSize || 0
  propForm.landUnit = p.landUnit || 'Katha'
  propForm.isRajukApproved = p.isRajukApproved
  propForm.isFeatured = p.isFeatured
  propForm.imageUrl = p.images?.[0] || 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?q=80&w=1600&auto=format&fit=crop'
  showPropModal.value = true
}

const handleSaveProperty = async () => {
  try {
    if (editingPropId.value) {
      await updateProperty(editingPropId.value, {
        title: propForm.title,
        propertyType: propForm.propertyType,
        state: propForm.state,
        areaName: propForm.areaName,
        address: propForm.address,
        price: propForm.price,
        bedrooms: propForm.bedrooms,
        bathrooms: propForm.bathrooms,
        squareFootage: propForm.squareFootage,
        landSize: propForm.landSize,
        landUnit: propForm.landUnit,
        isRajukApproved: propForm.isRajukApproved,
        isFeatured: propForm.isFeatured,
        images: [propForm.imageUrl]
      })
      toast.success('Property Updated', `Successfully updated "${propForm.title}".`)
    } else {
      await addProperty({
        ...propForm,
        images: [propForm.imageUrl]
      })
      toast.success('Property Published', `New listing "${propForm.title}" is now live!`)
    }
    closePropModal()
  } catch (err: any) {
    toast.error('Save Failed', err.message || 'Unable to save property.')
  }
}

const promptDelete = (prop: PropertyItem) => {
  deleteModalTarget.value = prop
}

const executeDelete = async () => {
  if (deleteModalTarget.value) {
    const title = deleteModalTarget.value.title
    await deleteProperty(deleteModalTarget.value.id)
    toast.info('Property Deleted', `Listing "${title}" has been removed.`)
    deleteModalTarget.value = null
  }
}

const toggleRajuk = async (id: number) => {
  const p = properties.value.find(prop => prop.id === id)
  if (p) {
    const newState = !p.isRajukApproved
    await updateProperty(id, { isRajukApproved: newState })
    toast.success('Legal Status Updated', `RAJUK status set to ${newState ? 'Verified' : 'Pending'}.`)
  }
}

const toggleFeature = async (id: number) => {
  const p = properties.value.find(prop => prop.id === id)
  if (p) {
    const newState = !p.isFeatured
    await updateProperty(id, { isFeatured: newState })
    toast.success('Feature Toggled', `Homepage showcase set to ${newState ? 'Active' : 'Disabled'}.`)
  }
}

const handleStatusChange = async (id: number, event: Event) => {
  const target = event.target as HTMLSelectElement
  const newStatus = target.value as any
  await updateProperty(id, { status: newStatus })
  toast.success('Status Changed', `Property status updated to "${newStatus}".`)
}
</script>

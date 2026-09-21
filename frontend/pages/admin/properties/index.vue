<template>
  <div class="admin-page animate-fade-in">
    <!-- Top Header & Live Sync Status -->
    <div class="admin-header-row">
      <div>
        <div class="flex items-center gap-3 flex-wrap">
          <h1 class="page-title">Property & Land Catalog</h1>
          <span 
            class="badge" 
            :style="{
              background: isLoading ? 'rgba(234, 179, 8, 0.15)' : 'rgba(16, 185, 129, 0.15)',
              color: isLoading ? '#EAB308' : '#10B981',
              border: isLoading ? '1px solid rgba(234, 179, 8, 0.3)' : '1px solid rgba(16, 185, 129, 0.3)',
              padding: '4px 10px',
              borderRadius: '9999px',
              fontSize: '0.78rem',
              fontWeight: '700',
              display: 'inline-flex',
              alignItems: 'center',
              gap: '6px'
            }"
          >
            <span :style="{ width: '8px', height: '8px', borderRadius: '50%', background: isLoading ? '#EAB308' : '#10B981', display: 'inline-block' }"></span>
            {{ isLoading ? 'Syncing...' : '🟢 Live & Synced' }}
          </span>
        </div>
        <p class="page-subtitle">
          Manage real estate inventory, prices, legal verification, and brochures • {{ properties.length }} active properties
          <span v-if="lastSyncedFormatted" style="color:var(--admin-text-muted); margin-left:6px;">(Updated: {{ lastSyncedFormatted }})</span>
        </p>
      </div>
      <div class="admin-header-actions flex items-center gap-3">
        <button 
          class="btn btn-outline-white" 
          :disabled="isLoading"
          @click="refreshData"
          title="Force fresh sync from MySQL database"
          style="display:inline-flex; align-items:center; gap:6px;"
        >
          <svg :class="{ 'animate-spin': isLoading }" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <polyline points="23 4 23 10 17 10"/>
            <path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/>
          </svg>
          <span>{{ isLoading ? 'Syncing...' : '↻ Refresh MySQL' }}</span>
        </button>
        <NuxtLink to="/admin/brochures" class="btn btn-outline-white" style="display:inline-flex; align-items:center; gap:6px;">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
            <polyline points="14 2 14 8 20 8"/>
            <line x1="16" y1="13" x2="8" y2="13"/>
            <line x1="16" y1="17" x2="8" y2="17"/>
          </svg>
          <span>📑 Brochure Vault</span>
        </NuxtLink>
        <NuxtLink to="/admin/properties/create" class="btn btn-emerald" style="display:inline-flex; align-items:center; gap:6px;">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <line x1="12" y1="5" x2="12" y2="19"/>
            <line x1="5" y1="12" x2="19" y2="12"/>
          </svg>
          <span>Add Property</span>
        </NuxtLink>
      </div>
    </div>

    <!-- Search, Filter & Summary Bar -->
    <div class="panel-card" style="padding:16px 20px; margin-bottom:20px;">
      <div class="flex justify-between items-center flex-wrap gap-4">
        <div class="flex items-center gap-3 flex-wrap flex-1">
          <!-- Search Filter -->
          <label class="sr-only" for="inventory-search">Search property inventory</label>
          <div style="position:relative; width:100%; max-width:280px;">
            <input 
              id="inventory-search"
              v-model="inventorySearch" 
              type="text" 
              placeholder="Search title, address, area..." 
              class="form-input" 
              style="width:100%; padding-right:28px;" 
            />
            <button 
              v-if="inventorySearch" 
              @click="inventorySearch = ''" 
              style="position:absolute; right:8px; top:50%; transform:translateY(-50%); background:none; border:none; color:var(--admin-text-muted); cursor:pointer; font-size:12px;"
              title="Clear search"
            >✕</button>
          </div>

          <!-- Dynamic Category Filter -->
          <select v-model="inventoryTypeFilter" class="form-select" style="width: auto;">
            <option value="">All Categories</option>
            <option v-for="cat in categories" :key="cat" :value="cat">{{ cat }}</option>
          </select>

          <!-- Dynamic Division Filter -->
          <select v-model="inventoryDivisionFilter" class="form-select" style="width: auto;">
            <option value="">All Divisions</option>
            <option v-for="div in divisions" :key="div" :value="div">{{ div }}</option>
          </select>

          <!-- Dynamic Status Filter -->
          <select v-model="inventoryStatusFilter" class="form-select" style="width: auto;">
            <option value="">All Statuses</option>
            <option v-for="s in statuses" :key="s" :value="s">{{ s }}</option>
          </select>

          <!-- Reset Filter Button -->
          <button 
            v-if="inventorySearch || inventoryTypeFilter || inventoryDivisionFilter || inventoryStatusFilter"
            class="btn btn-sm btn-outline-white" 
            @click="resetFilters"
            style="font-size:0.8rem; padding:6px 10px;"
          >
            Clear Filters
          </button>
        </div>

        <div class="flex items-center gap-2">
          <span style="font-size:0.85rem; color:var(--admin-text-muted); font-weight:600;">
            Showing {{ filteredProperties.length }} of {{ properties.length }} items
          </span>
        </div>
      </div>
    </div>

    <!-- Property Table -->
    <div class="panel-card" style="padding:0; overflow:hidden;">
      <div class="table-responsive">
        <table class="admin-table">
          <thead>
            <tr>
              <th>Property & Database ID</th>
              <th>Category & Dimensions</th>
              <th>Asking Price (BDT)</th>
              <th>Legal Clearance</th>
              <th>Featured</th>
              <th>Status</th>
              <th style="text-align:right;">Actions</th>
            </tr>
          </thead>
          <tbody>
            <!-- Loading State -->
            <tr v-if="isLoading && properties.length === 0">
              <td colspan="7" style="text-align:center; padding:48px 20px;">
                <div class="flex flex-col items-center justify-center gap-3">
                  <svg class="animate-spin" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#10B981" stroke-width="2.5">
                    <polyline points="23 4 23 10 17 10"/>
                    <path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/>
                  </svg>
                  <p style="color:var(--admin-text-primary); font-size:0.95rem; font-weight:600;">Loading properties...</p>
                </div>
              </td>
            </tr>

            <!-- Empty State -->
            <tr v-else-if="filteredProperties.length === 0">
              <td colspan="7" style="text-align:center; padding:48px 20px;">
                <div class="flex flex-col items-center justify-center gap-2">
                  <div style="font-size:2rem;">🏛</div>
                  <p style="color:var(--admin-text-primary); font-size:1rem; font-weight:600;">No properties match your current filters</p>
                  <p style="color:var(--admin-text-muted); font-size:0.85rem;">Try broadening your search or click below to add a new property listing.</p>
                  <NuxtLink to="/admin/properties/create" class="btn btn-emerald btn-sm" style="margin-top:8px;">
                    Add Property
                  </NuxtLink>
                </div>
              </td>
            </tr>

            <!-- Data Rows -->
            <tr v-for="prop in filteredProperties" :key="prop.id">
              <td>
                <div class="flex items-center gap-3">
                  <div style="position:relative; flex-shrink:0;">
                    <img 
                      :src="prop.featureImage || prop.images[0] || 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?q=80&w=200&auto=format&fit=crop'" 
                      class="table-thumb" 
                      alt="thumb" 
                      loading="lazy"
                    />
                    <span 
                      v-if="prop.images && prop.images.length > 1" 
                      style="position:absolute; bottom:2px; right:2px; background:rgba(10,17,40,0.85); color:#F1F5F9; font-size:0.68rem; font-weight:700; padding:1px 4px; border-radius:3px; border:1px solid rgba(255,255,255,0.2);"
                      title="Gallery photos attached"
                    >
                      📷 {{ prop.images.length }}
                    </span>
                  </div>
                  <div>
                    <strong style="color:var(--admin-text-primary); display:block; max-width:280px; line-height:1.3; font-size:0.92rem;">
                      {{ prop.title }}
                    </strong>
                    <div class="flex items-center gap-2 flex-wrap" style="margin-top:2px;">
                      <span style="font-size:0.78rem; color:var(--admin-text-muted);">
                        ID: #{{ prop.id }} • {{ prop.areaName }}, {{ prop.city }}
                      </span>
                      <a 
                        v-if="prop.brochureUrl" 
                        :href="prop.brochureUrl" 
                        target="_blank" 
                        style="font-size:0.7rem; font-weight:700; color:#38BDF8; background:rgba(56,189,248,0.15); border:1px solid rgba(56,189,248,0.3); padding:1px 6px; border-radius:3px; text-decoration:none; display:inline-flex; align-items:center; gap:3px;"
                        title="Download attached brochure PDF"
                      >
                        📄 PDF Brochure
                      </a>
                    </div>
                  </div>
                </div>
              </td>

              <td>
                <span class="badge badge-status" style="font-size:0.75rem;">{{ prop.propertyType }}</span>
                <div style="font-size:0.8rem; color:var(--admin-text-muted); margin-top:4px;">
                  {{ formatArea(prop.squareFootage, prop.landSize, prop.landUnit) }}
                </div>
              </td>

              <td style="font-family:var(--font-ui); font-size:1.05rem; font-weight:800; color:#10B981; font-variant-numeric:tabular-nums;">
                {{ formatBDT(prop.price) }}
              </td>

              <td>
                <button 
                  class="badge-admin" 
                  :class="prop.isRajukApproved ? 'active' : 'pending'"
                  :disabled="togglingRajukId === prop.id"
                  style="cursor:pointer; transition:all 0.2s;"
                  @click="handleToggleRajuk(prop.id)"
                  title="Click to toggle RAJUK Approved status in MySQL"
                >
                  <span v-if="togglingRajukId === prop.id" class="animate-spin" style="display:inline-block; margin-right:4px;">◌</span>
                  {{ prop.isRajukApproved ? '✔ RAJUK Pass' : 'Pending Audit' }}
                </button>
              </td>

              <td>
                <button 
                  class="star-toggle-btn" 
                  :class="{ active: prop.isFeatured }"
                  :disabled="togglingFeatureId === prop.id"
                  @click="handleToggleFeature(prop.id)"
                  :title="prop.isFeatured ? 'Featured on Homepage (Click to unfeature)' : 'Click to feature on Homepage in MySQL'"
                >
                  <span v-if="togglingFeatureId === prop.id" class="animate-spin" style="display:inline-block; font-size:0.8rem;">◌</span>
                  <span v-else>★</span>
                </button>
              </td>

              <td>
                <select 
                  :value="prop.status" 
                  :disabled="updatingStatusId === prop.id"
                  @change="handleStatusChange(prop.id, $event)"
                  class="status-inline-select"
                  :style="prop.status === 'Draft' ? 'border-color: #F59E0B; color: #FCD34D;' : (prop.status === 'Active' ? 'border-color: #10B981;' : '')"
                  title="Change property lifecycle status"
                >
                  <option v-for="s in statuses" :key="s" :value="s">{{ s }}</option>
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
                  <NuxtLink :to="`/admin/properties/${prop.id}/edit`" class="action-btn edit" title="Edit property on dedicated page" aria-label="Edit details">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                      <path d="M17 3a2.83 2.83 0 0 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"/>
                    </svg>
                  </NuxtLink>
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
         DELETE CONFIRMATION MODAL
         ====================================================================== -->
    <div v-if="deleteModalTarget" class="admin-modal-overlay" @click.self="deleteModalTarget = null">
      <div class="admin-modal-card animate-fade-in-up" style="max-width:440px;">
        <div class="admin-modal-header" style="background:#1E1622; border-bottom:1px solid rgba(239,68,68,0.2);">
          <h3 class="admin-modal-title" style="color:#F87171; display:flex; align-items:center; gap:8px;">
            <span>⚠ Confirm Deletion from Database</span>
          </h3>
          <button class="admin-modal-close" @click="deleteModalTarget = null">✕</button>
        </div>
        <div class="admin-modal-body">
          <p style="color:var(--admin-text-primary); font-size:0.92rem; line-height:1.5;">
            Are you sure you want to permanently delete <strong>"{{ deleteModalTarget.title }}"</strong> (ID #{{ deleteModalTarget.id }})?
          </p>
          <p style="color:var(--admin-text-muted); font-size:0.82rem; margin-top:8px;">
            This will permanently erase the listing from the database and delist it from the live portal.
          </p>
        </div>
        <div class="admin-modal-footer">
          <button class="btn btn-sm btn-outline-white" :disabled="isDeleting" @click="deleteModalTarget = null">Cancel</button>
          <button class="btn btn-sm" :disabled="isDeleting" style="background:#EF4444; color:#FFF; display:inline-flex; align-items:center; gap:6px;" @click="executeDelete">
            <span v-if="isDeleting" class="animate-spin">◌</span>
            <span>{{ isDeleting ? 'Deleting...' : 'Permanently Delete' }}</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useProperties, type PropertyItem } from '~/composables/useProperties'
import { usePropertyOptions } from '~/composables/usePropertyOptions'
import { formatBDT, formatArea } from '~/composables/useCurrency'
import { useToast } from '~/composables/useToast'

definePageMeta({
  layout: 'admin'
})

const { 
  properties, 
  isLoading, 
  lastSynced, 
  fetchProperties, 
  toggleFeatureProperty, 
  toggleRajukProperty, 
  updatePropertyStatus, 
  deleteProperty 
} = useProperties()

const {
  categories,
  divisions,
  statuses,
  fetchOptions
} = usePropertyOptions()

const toast = useToast()

const inventorySearch = ref('')
const inventoryTypeFilter = ref('')
const inventoryDivisionFilter = ref('')
const inventoryStatusFilter = ref('')

const deleteModalTarget = ref<PropertyItem | null>(null)
const isDeleting = ref(false)

const togglingRajukId = ref<number | null>(null)
const togglingFeatureId = ref<number | null>(null)
const updatingStatusId = ref<number | null>(null)

const lastSyncedFormatted = computed(() => {
  if (!lastSynced.value) return ''
  const date = new Date(lastSynced.value)
  return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' })
})

const filteredProperties = computed(() => {
  return properties.value.filter(p => {
    if (inventoryTypeFilter.value && p.propertyType !== inventoryTypeFilter.value) return false
    if (inventoryDivisionFilter.value && p.state !== inventoryDivisionFilter.value) return false
    if (inventoryStatusFilter.value && p.status !== inventoryStatusFilter.value) return false
    if (inventorySearch.value) {
      const q = inventorySearch.value.toLowerCase().trim()
      return (
        p.title.toLowerCase().includes(q) || 
        p.areaName.toLowerCase().includes(q) || 
        p.address.toLowerCase().includes(q) || 
        String(p.id).includes(q)
      )
    }
    return true
  })
})

const resetFilters = () => {
  inventorySearch.value = ''
  inventoryTypeFilter.value = ''
  inventoryDivisionFilter.value = ''
  inventoryStatusFilter.value = ''
}

const refreshData = async () => {
  try {
    await fetchProperties(true)
    await fetchOptions(true)
    toast.success('Database Synced', `Catalog updated (${properties.value.length} properties loaded).`)
  } catch (err: any) {
    toast.error('Sync Error', err.message || 'Unable to refresh from database.')
  }
}

onMounted(async () => {
  await Promise.all([
    fetchProperties(),
    fetchOptions()
  ])
})

const promptDelete = (prop: PropertyItem) => {
  deleteModalTarget.value = prop
}

const executeDelete = async () => {
  if (!deleteModalTarget.value) return
  isDeleting.value = true
  const target = deleteModalTarget.value
  try {
    await deleteProperty(target.id)
    toast.info('Property Removed', `Property #${target.id} "${target.title}" was deleted.`)
    deleteModalTarget.value = null
  } catch (err: any) {
    toast.error('Delete Failed', err.message || 'Unable to delete property.')
  } finally {
    isDeleting.value = false
  }
}

const handleToggleRajuk = async (id: number) => {
  togglingRajukId.value = id
  try {
    const newState = await toggleRajukProperty(id)
    toast.success('Verification Updated', `Property #${id} RAJUK status set to ${newState ? 'Verified Pass' : 'Pending Audit'}.`)
  } catch (err: any) {
    toast.error('Update Failed', err.message || 'Could not update verification status.')
  } finally {
    togglingRajukId.value = null
  }
}

const handleToggleFeature = async (id: number) => {
  togglingFeatureId.value = id
  try {
    const newState = await toggleFeatureProperty(id)
    toast.success('Feature Toggled', `Property #${id} homepage showcase is now ${newState ? 'Active' : 'Disabled'}.`)
  } catch (err: any) {
    toast.error('Update Failed', err.message || 'Could not toggle featured status.')
  } finally {
    togglingFeatureId.value = null
  }
}

const handleStatusChange = async (id: number, event: Event) => {
  updatingStatusId.value = id
  const target = event.target as HTMLSelectElement
  const newStatus = target.value
  try {
    await updatePropertyStatus(id, newStatus)
    toast.success('Status Changed', `Property #${id} status changed to "${newStatus}".`)
  } catch (err: any) {
    toast.error('Update Failed', err.message || 'Could not change status.')
  } finally {
    updatingStatusId.value = null
  }
}
</script>

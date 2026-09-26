<template>
  <div class="admin-page master-data-page">
    <!-- Top Header -->
    <div class="admin-header-row">
      <div>
        <div class="flex items-center gap-3">
          <NuxtLink to="/admin/properties" class="btn-back-link" title="Back to Property Inventory">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <polyline points="15 18 9 12 15 6"/>
            </svg>
            <span>Inventory</span>
          </NuxtLink>
          <span style="color: var(--admin-border-hover);">/</span>
          <span class="header-tag">{{ entityTitle }}</span>
        </div>
        <h1 class="page-title" style="margin-top: 6px;">{{ title }}</h1>
        <p class="page-subtitle">{{ subtitle }}</p>
      </div>

      <div class="admin-header-actions">
        <button class="btn btn-outline-white btn-sm" @click="fetchItems" :disabled="loading" title="Reload from MySQL Database">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" :class="{ 'spin-anim': loading }">
            <path d="M21.5 2v6h-6M21.34 15.57a10 10 0 1 1-.57-8.38l5.67-5.19"/>
          </svg>
          <span>Refresh</span>
        </button>
        <button class="btn btn-gold btn-sm" @click="openCreateModal" id="btn-add-master-item">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <line x1="12" y1="5" x2="12" y2="19"/>
            <line x1="5" y1="12" x2="19" y2="12"/>
          </svg>
          <span>Add {{ singularName }}</span>
        </button>
      </div>
    </div>

    <!-- Quick Switcher Tabs (All 5 Master Entities) -->
    <div class="master-tabs-bar">
      <NuxtLink to="/admin/categories" class="master-tab-item" active-class="active">
        <span class="tab-icon">🏷️</span>
        <span class="tab-label">Categories</span>
        <span class="tab-chip">{{ tabCounts.categories || 8 }}</span>
      </NuxtLink>
      <NuxtLink to="/admin/divisions" class="master-tab-item" active-class="active">
        <span class="tab-icon">📍</span>
        <span class="tab-label">Divisions / Regions</span>
        <span class="tab-chip">{{ tabCounts.divisions || 12 }}</span>
      </NuxtLink>
      <NuxtLink to="/admin/transaction-types" class="master-tab-item" active-class="active">
        <span class="tab-icon">💼</span>
        <span class="tab-label">Transaction Types</span>
        <span class="tab-chip">{{ tabCounts.transaction_types || 4 }}</span>
      </NuxtLink>
      <NuxtLink to="/admin/property-statuses" class="master-tab-item" active-class="active">
        <span class="tab-icon">🔄</span>
        <span class="tab-label">Lifecycle Statuses</span>
        <span class="tab-chip">{{ tabCounts.property_statuses || 5 }}</span>
      </NuxtLink>
      <NuxtLink to="/admin/land-units" class="master-tab-item" active-class="active">
        <span class="tab-icon">📐</span>
        <span class="tab-label">Land Units</span>
        <span class="tab-chip">{{ tabCounts.land_units || 6 }}</span>
      </NuxtLink>
    </div>

    <!-- KPI Metric Summary Row -->
    <div class="master-stats-grid">
      <div class="stat-card">
        <div class="stat-meta">
          <span class="stat-title">Total {{ title }}</span>
          <span class="stat-badge">Database Active</span>
        </div>
        <div class="stat-val">{{ items.length }}</div>
        <div class="stat-sub">Persisted in MySQL <code class="code-pill">{{ tableName }}</code></div>
      </div>

      <div class="stat-card">
        <div class="stat-meta">
          <span class="stat-title">Live in Portal</span>
          <span class="stat-badge stat-badge-emerald">Active</span>
        </div>
        <div class="stat-val" style="color: #10B981;">{{ activeCount }}</div>
        <div class="stat-sub">Available in property forms and public filters</div>
      </div>

      <div class="stat-card">
        <div class="stat-meta">
          <span class="stat-title">Inactive / Hidden</span>
          <span class="stat-badge stat-badge-amber">Draft / Archived</span>
        </div>
        <div class="stat-val" style="color: #F59E0B;">{{ inactiveCount }}</div>
        <div class="stat-sub">Disabled from new property listings</div>
      </div>

      <div class="stat-card">
        <div class="stat-meta">
          <span class="stat-title">Realtime Sync</span>
          <span class="stat-badge stat-badge-gold">Auto-Synced</span>
        </div>
        <div class="stat-val" style="color: #E2651C; font-size: 1.25rem; font-weight: 700; margin-top: 6px;">
          100% ONLINE
        </div>
        <div class="stat-sub">Dual-synced with Platform Settings</div>
      </div>
    </div>

    <!-- Main Panel: Search, Filter, and Table -->
    <div class="panel-card master-table-panel">
      <div class="panel-filter-row">
        <!-- Keyword Search -->
        <div class="search-input-wrap">
          <svg class="search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="11" cy="11" r="8"/>
            <line x1="21" y1="21" x2="16.65" y2="16.65"/>
          </svg>
          <input 
            v-model="searchQuery" 
            type="text" 
            :placeholder="`Search ${title.toLowerCase()} by name, slug...`" 
            class="filter-search-input"
            id="master-search-input"
          />
          <button v-if="searchQuery" class="search-clear-btn" @click="searchQuery = ''">✕</button>
        </div>

        <!-- Filter By Status -->
        <div class="filter-actions-wrap">
          <select v-model="statusFilter" class="filter-select" aria-label="Filter by Status">
            <option value="all">All Statuses ({{ items.length }})</option>
            <option value="active">Active Only ({{ activeCount }})</option>
            <option value="inactive">Inactive Only ({{ inactiveCount }})</option>
          </select>
        </div>
      </div>

      <!-- Table of Records -->
      <div class="table-container">
        <div v-if="loading && items.length === 0" class="loading-state-box">
          <div class="spinner-ring"></div>
          <p>Connecting to MySQL database...</p>
        </div>

        <div v-else-if="filteredItems.length === 0" class="empty-state-box">
          <div style="font-size: 2.2rem; margin-bottom: 8px;">🔍</div>
          <div style="font-size: 1.05rem; font-weight: 700; color: var(--admin-text-primary);">No records found</div>
          <p style="color: var(--admin-text-muted); font-size: 0.88rem; max-width: 400px; margin: 6px auto 16px;">
            No items matched your search query. Try clearing the filter or add a new {{ singularName.toLowerCase() }}.
          </p>
          <button class="btn btn-gold btn-sm" @click="openCreateModal">
            + Add New {{ singularName }}
          </button>
        </div>

        <table v-else class="master-data-table">
          <thead>
            <tr>
              <th style="width: 50px;">Order</th>
              <th>Name & Identifier</th>
              <th v-if="entityKey === 'categories'">Icon & Info</th>
              <th v-if="entityKey === 'divisions'">Bengali Name</th>
              <th v-if="entityKey === 'transaction-types'">Description</th>
              <th v-if="entityKey === 'property-statuses'">Color & Badge</th>
              <th v-if="entityKey === 'land-units'">Multiplier & Symbol</th>
              <th style="width: 140px;">Status</th>
              <th style="width: 130px; text-align: right;">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(item, idx) in filteredItems" :key="item.id || idx" class="master-table-row">
              <!-- Sort Order -->
              <td>
                <span class="order-badge">{{ item.sort_order ?? idx + 1 }}</span>
              </td>

              <!-- Name & Slug -->
              <td>
                <div class="item-primary-text">{{ item.name }}</div>
                <div class="item-secondary-text"><code>{{ item.slug }}</code></div>
              </td>

              <!-- Category Details -->
              <td v-if="entityKey === 'categories'">
                <div class="flex items-center gap-2">
                  <span class="icon-tag">{{ item.icon || 'folder' }}</span>
                  <span class="text-sm text-secondary truncate max-w-xs">{{ item.description || 'General real estate category' }}</span>
                </div>
              </td>

              <!-- Division Details -->
              <td v-if="entityKey === 'divisions'">
                <span class="bn-text-pill">{{ item.bn_name || '—' }}</span>
              </td>

              <!-- Transaction Type Details -->
              <td v-if="entityKey === 'transaction-types'">
                <span class="text-sm text-secondary">{{ item.description || 'Real estate transaction contract' }}</span>
              </td>

              <!-- Property Status Details -->
              <td v-if="entityKey === 'property-statuses'">
                <div class="flex items-center gap-2">
                  <span class="status-color-circle" :style="{ backgroundColor: item.color_code || '#10B981' }"></span>
                  <span class="status-badge-preview" :style="{ borderColor: item.color_code, color: item.color_code }">
                    {{ item.badge_label || item.name }}
                  </span>
                  <span class="text-xs text-muted">({{ item.color_code || '#10B981' }})</span>
                </div>
              </td>

              <!-- Land Unit Details -->
              <td v-if="entityKey === 'land-units'">
                <div class="flex items-center gap-2">
                  <span class="symbol-pill">{{ item.symbol || item.slug }}</span>
                  <span class="multiplier-text">
                    1 {{ item.name }} = <strong>{{ formatMultiplier(item.sqft_multiplier) }}</strong> sqft
                  </span>
                </div>
              </td>

              <!-- Active Toggle -->
              <td>
                <button 
                  class="toggle-status-btn" 
                  :class="{ 'is-active': item.is_active, 'is-inactive': !item.is_active }"
                  @click="toggleActive(item)"
                  :title="item.is_active ? 'Click to deactivate' : 'Click to activate'"
                >
                  <span class="toggle-dot"></span>
                  <span>{{ item.is_active ? 'Active' : 'Inactive' }}</span>
                </button>
              </td>

              <!-- Action Buttons -->
              <td style="text-align: right;">
                <div class="action-btn-group">
                  <button class="btn-table-action btn-edit" @click="openEditModal(item)" title="Edit this record">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                      <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                    </svg>
                    <span>Edit</span>
                  </button>
                  <button class="btn-table-action btn-delete" @click="confirmDelete(item)" title="Delete record">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
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
         CREATE / EDIT MODAL
         ====================================================================== -->
    <div v-if="modalOpen" class="master-modal-overlay" @click.self="modalOpen = false">
      <div class="master-modal-card">
        <div class="modal-header">
          <div>
            <h3 class="modal-title">{{ isEdit ? 'Edit ' + singularName : 'Add New ' + singularName }}</h3>
            <p class="modal-sub">Updates persist instantly to MySQL table: <code>{{ tableName }}</code></p>
          </div>
          <button class="modal-close-btn" @click="modalOpen = false">✕</button>
        </div>

        <form @submit.prevent="handleSave" class="modal-form">
          <!-- Name -->
          <div class="form-group">
            <label class="form-label">{{ singularName }} Name <span class="text-rose">*</span></label>
            <input 
              v-model="form.name" 
              type="text" 
              class="form-input" 
              required 
              :placeholder="`e.g. ${namePlaceholder}`" 
              @input="onNameInput"
            />
          </div>

          <!-- Slug -->
          <div class="form-group">
            <label class="form-label">URL Slug / System Key <span class="text-rose">*</span></label>
            <input 
              v-model="form.slug" 
              type="text" 
              class="form-input" 
              required 
              placeholder="auto-generated-slug" 
            />
          </div>

          <!-- Category Specific: Icon & Description -->
          <template v-if="entityKey === 'categories'">
            <div class="form-grid-2">
              <div class="form-group">
                <label class="form-label">Icon Identifier</label>
                <input v-model="form.icon" type="text" class="form-input" placeholder="e.g. building, home, award" />
              </div>
              <div class="form-group">
                <label class="form-label">Sort Order</label>
                <input v-model.number="form.sort_order" type="number" class="form-input" placeholder="0" />
              </div>
            </div>
            <div class="form-group">
              <label class="form-label">Description</label>
              <textarea v-model="form.description" class="form-textarea" rows="2" placeholder="Brief summary of category..."></textarea>
            </div>
          </template>

          <!-- Division Specific: Bengali Name & Sort Order -->
          <template v-if="entityKey === 'divisions'">
            <div class="form-grid-2">
              <div class="form-group">
                <label class="form-label">Bengali Name (বাংলা নাম)</label>
                <input v-model="form.bn_name" type="text" class="form-input" placeholder="e.g. ঢাকা উত্তর" />
              </div>
              <div class="form-group">
                <label class="form-label">Sort Order</label>
                <input v-model.number="form.sort_order" type="number" class="form-input" placeholder="1" />
              </div>
            </div>
          </template>

          <!-- Transaction Type Specific: Description -->
          <template v-if="entityKey === 'transaction-types'">
            <div class="form-group">
              <label class="form-label">Description</label>
              <textarea v-model="form.description" class="form-textarea" rows="2" placeholder="Explain the legal / transaction contract model..."></textarea>
            </div>
            <div class="form-group">
              <label class="form-label">Sort Order</label>
              <input v-model.number="form.sort_order" type="number" class="form-input" placeholder="1" />
            </div>
          </template>

          <!-- Property Status Specific: Color Code & Badge Label -->
          <template v-if="entityKey === 'property-statuses'">
            <div class="form-grid-2">
              <div class="form-group">
                <label class="form-label">Color Code</label>
                <div class="color-picker-wrap">
                  <input v-model="form.color_code" type="color" class="color-input" />
                  <input v-model="form.color_code" type="text" class="form-input" placeholder="#10B981" />
                </div>
              </div>
              <div class="form-group">
                <label class="form-label">Badge Label</label>
                <input v-model="form.badge_label" type="text" class="form-input" placeholder="e.g. Live on Portal" />
              </div>
            </div>
            <div class="form-group">
              <label class="form-label">Sort Order</label>
              <input v-model.number="form.sort_order" type="number" class="form-input" placeholder="1" />
            </div>
            <!-- Live Preview -->
            <div class="preview-box">
              <span class="text-xs text-muted" style="margin-bottom: 4px; display: block;">Live Badge Preview:</span>
              <span class="status-badge-preview" :style="{ borderColor: form.color_code, color: form.color_code }">
                {{ form.badge_label || form.name || 'Status Badge' }}
              </span>
            </div>
          </template>

          <!-- Land Unit Specific: Symbol & Sqft Multiplier -->
          <template v-if="entityKey === 'land-units'">
            <div class="form-grid-2">
              <div class="form-group">
                <label class="form-label">Unit Symbol</label>
                <input v-model="form.symbol" type="text" class="form-input" placeholder="e.g. katha, bigha, sqft" />
              </div>
              <div class="form-group">
                <label class="form-label">Sqft Multiplier</label>
                <input v-model.number="form.sqft_multiplier" type="number" step="0.0001" class="form-input" placeholder="e.g. 720.0" />
              </div>
            </div>
            <div class="form-group">
              <label class="form-label">Sort Order</label>
              <input v-model.number="form.sort_order" type="number" class="form-input" placeholder="1" />
            </div>
            <!-- Live Multiplier Preview -->
            <div class="preview-box">
              <span class="text-xs text-muted" style="margin-bottom: 4px; display: block;">Unit Calculation Formula:</span>
              <div style="font-size: 0.9rem; color: var(--admin-text-primary);">
                1 {{ form.name || 'Unit' }} = <span style="color: var(--color-gold); font-weight: 800;">{{ formatMultiplier(form.sqft_multiplier) }}</span> Square Feet
              </div>
            </div>
          </template>

          <!-- Active Toggle Switch -->
          <div class="form-group">
            <label class="toggle-checkbox-label">
              <input v-model="form.is_active" type="checkbox" class="toggle-checkbox" />
              <span>Enable this {{ singularName.toLowerCase() }} actively in property selectors</span>
            </label>
          </div>

          <!-- Modal Footer Actions -->
          <div class="modal-footer">
            <button type="button" class="btn btn-outline-white btn-sm" @click="modalOpen = false">Cancel</button>
            <button type="submit" class="btn btn-gold btn-sm" :disabled="saving">
              <span v-if="saving">Saving...</span>
              <span v-else>{{ isEdit ? 'Update ' + singularName : 'Save ' + singularName }}</span>
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- ======================================================================
         DELETE CONFIRMATION DIALOG
         ====================================================================== -->
    <div v-if="deleteModalOpen" class="master-modal-overlay" @click.self="deleteModalOpen = false">
      <div class="master-modal-card delete-card">
        <div class="delete-icon">⚠️</div>
        <h3 style="font-size: 1.15rem; font-weight: 800; color: var(--admin-text-primary); margin-bottom: 8px;">
          Delete {{ singularName }}?
        </h3>
        <p style="color: var(--admin-text-secondary); font-size: 0.88rem; line-height: 1.5; margin-bottom: 20px;">
          Are you sure you want to delete <strong style="color: var(--admin-text-primary);">{{ itemToDelete?.name }}</strong>? 
          This will remove it from MySQL table <code class="code-pill">{{ tableName }}</code> and update the live property options list.
        </p>

        <div class="flex items-center justify-end gap-3">
          <button class="btn btn-outline-white btn-sm" @click="deleteModalOpen = false">Cancel</button>
          <button class="btn btn-sm btn-danger" @click="executeDelete" :disabled="deleting">
            <span v-if="deleting">Deleting...</span>
            <span v-else>Confirm Delete</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useToast } from '~/composables/useToast'

const props = defineProps<{
  entityKey: 'categories' | 'divisions' | 'transaction-types' | 'property-statuses' | 'land-units'
  apiEndpoint: string
  title: string
  subtitle: string
  entityTitle: string
  singularName: string
  tableName: string
  namePlaceholder: string
}>()

const { add: addToast } = useToast()
const config = useRuntimeConfig()
const apiBase = config.public.apiBase || 'http://127.0.0.1:8000/api'

const items = ref<any[]>([])
const loading = ref(false)
const saving = ref(false)
const deleting = ref(false)
const searchQuery = ref('')
const statusFilter = ref('all')

const modalOpen = ref(false)
const isEdit = ref(false)
const editingId = ref<number | null>(null)

const deleteModalOpen = ref(false)
const itemToDelete = ref<any | null>(null)

const tabCounts = ref({
  categories: 8,
  divisions: 12,
  transaction_types: 4,
  property_statuses: 5,
  land_units: 6
})

const form = ref<any>({
  name: '',
  slug: '',
  description: '',
  icon: 'building',
  bn_name: '',
  color_code: '#10B981',
  badge_label: '',
  symbol: '',
  sqft_multiplier: 1.0,
  sort_order: 1,
  is_active: true
})

// Auto-generate slug when user types name if creating
const onNameInput = () => {
  if (!isEdit.value && form.value.name) {
    form.value.slug = form.value.name
      .toLowerCase()
      .trim()
      .replace(/[^a-z0-9]+/g, '-')
      .replace(/(^-|-$)+/g, '')
    if (props.entityKey === 'property-statuses') {
      form.value.badge_label = form.value.name
    }
    if (props.entityKey === 'land-units') {
      form.value.symbol = form.value.slug
    }
  }
}

const formatMultiplier = (val: any) => {
  if (val === null || val === undefined || val === '') return '1'
  const num = Number(val)
  return Number.isInteger(num) ? num.toLocaleString() : num.toLocaleString(undefined, { maximumFractionDigits: 4 })
}

// Filtered items list
const filteredItems = computed(() => {
  return items.value.filter(item => {
    // 1. Status Filter
    if (statusFilter.value === 'active' && !item.is_active) return false
    if (statusFilter.value === 'inactive' && item.is_active) return false

    // 2. Keyword Search
    if (!searchQuery.value.trim()) return true
    const q = searchQuery.value.toLowerCase().trim()
    const nameMatch = item.name && item.name.toLowerCase().includes(q)
    const slugMatch = item.slug && item.slug.toLowerCase().includes(q)
    const bnMatch = item.bn_name && item.bn_name.toLowerCase().includes(q)
    const descMatch = item.description && item.description.toLowerCase().includes(q)
    return nameMatch || slugMatch || bnMatch || descMatch
  })
})

const activeCount = computed(() => items.value.filter(i => i.is_active).length)
const inactiveCount = computed(() => items.value.filter(i => !i.is_active).length)

// Fetch master items from backend
const fetchItems = async () => {
  loading.value = true
  try {
    const res = await fetch(`${apiBase}${props.apiEndpoint}?all=1`)
    const data = await res.json()
    if (data.success && Array.isArray(data.data)) {
      items.value = data.data
    }
  } catch (err: any) {
    console.error('Failed to load master records:', err)
    addToast({
      title: 'Failed to fetch data',
      message: 'Could not connect to database table. Please check API server.',
      type: 'error'
    })
  } finally {
    loading.value = false
  }
}

// Fetch sidebar & tab counts
const fetchCounts = async () => {
  try {
    const res = await fetch(`${apiBase}/admin/sidebar-counts`)
    const json = await res.json()
    if (json.success && json.data) {
      tabCounts.value = {
        categories: json.data.categories || 8,
        divisions: json.data.divisions || 12,
        transaction_types: json.data.transaction_types || 4,
        property_statuses: json.data.property_statuses || 5,
        land_units: json.data.land_units || 6
      }
    }
  } catch {
    //
  }
}

// Open modal for Create
const openCreateModal = () => {
  isEdit.value = false
  editingId.value = null
  form.value = {
    name: '',
    slug: '',
    description: '',
    icon: 'building',
    bn_name: '',
    color_code: '#10B981',
    badge_label: '',
    symbol: '',
    sqft_multiplier: 1.0,
    sort_order: items.value.length + 1,
    is_active: true
  }
  modalOpen.value = true
}

// Open modal for Edit
const openEditModal = (item: any) => {
  isEdit.value = true
  editingId.value = item.id
  form.value = {
    name: item.name,
    slug: item.slug,
    description: item.description || '',
    icon: item.icon || 'building',
    bn_name: item.bn_name || '',
    color_code: item.color_code || '#10B981',
    badge_label: item.badge_label || item.name,
    symbol: item.symbol || '',
    sqft_multiplier: item.sqft_multiplier ? Number(item.sqft_multiplier) : 1.0,
    sort_order: item.sort_order ?? 1,
    is_active: Boolean(item.is_active)
  }
  modalOpen.value = true
}

// Quick toggle active status
const toggleActive = async (item: any) => {
  const newStatus = !item.is_active
  item.is_active = newStatus // optimistic update

  try {
    const url = `${apiBase}${props.apiEndpoint}/${item.id}`
    const res = await fetch(url, {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ is_active: newStatus })
    })
    const json = await res.json()
    if (json.success) {
      addToast({
        title: 'Status Updated',
        message: `${item.name} is now ${newStatus ? 'Active' : 'Inactive'}`,
        type: 'success'
      })
      fetchCounts()
    } else {
      item.is_active = !newStatus // rollback
      addToast({ title: 'Update Failed', message: json.message || 'Error updating status', type: 'error' })
    }
  } catch (err: any) {
    item.is_active = !newStatus // rollback
    addToast({ title: 'Network Error', message: 'Could not update status on server', type: 'error' })
  }
}

// Save Create or Edit
const handleSave = async () => {
  saving.value = true
  try {
    const url = isEdit.value 
      ? `${apiBase}${props.apiEndpoint}/${editingId.value}`
      : `${apiBase}${props.apiEndpoint}`
    
    const method = isEdit.value ? 'PUT' : 'POST'
    const res = await fetch(url, {
      method,
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(form.value)
    })
    const json = await res.json()

    if (json.success) {
      addToast({
        title: isEdit.value ? 'Record Updated' : 'Record Created',
        message: `${form.value.name} successfully saved to ${props.tableName}`,
        type: 'success'
      })
      modalOpen.value = false
      await fetchItems()
      await fetchCounts()
    } else {
      addToast({
        title: 'Validation Error',
        message: json.message || 'Please check the required fields',
        type: 'error'
      })
    }
  } catch (err: any) {
    addToast({
      title: 'Server Error',
      message: err.message || 'Failed to save record',
      type: 'error'
    })
  } finally {
    saving.value = false
  }
}

// Confirm Delete
const confirmDelete = (item: any) => {
  itemToDelete.value = item
  deleteModalOpen.value = true
}

// Execute Delete
const executeDelete = async () => {
  if (!itemToDelete.value) return
  deleting.value = true

  try {
    const url = `${apiBase}${props.apiEndpoint}/${itemToDelete.value.id}`
    const res = await fetch(url, { method: 'DELETE' })
    const json = await res.json()

    if (json.success) {
      addToast({
        title: 'Record Deleted',
        message: `${itemToDelete.value.name} was removed from ${props.tableName}`,
        type: 'success'
      })
      deleteModalOpen.value = false
      itemToDelete.value = null
      await fetchItems()
      await fetchCounts()
    } else {
      addToast({ title: 'Delete Failed', message: json.message || 'Could not delete item', type: 'error' })
    }
  } catch (err: any) {
    addToast({ title: 'Server Error', message: 'Failed to delete item', type: 'error' })
  } finally {
    deleting.value = false
  }
}

onMounted(() => {
  fetchItems()
  fetchCounts()
})
</script>

<style scoped>
.master-data-page {
  animation: fadeIn 0.2s ease-in-out;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(6px); }
  to { opacity: 1; transform: translateY(0); }
}

.btn-back-link {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  color: var(--admin-text-muted);
  font-size: 0.84rem;
  font-weight: 600;
  text-decoration: none;
  transition: color 0.15s;
}

.btn-back-link:hover {
  color: var(--admin-text-primary);
}

.header-tag {
  font-size: 0.84rem;
  font-weight: 700;
  color: var(--admin-text-gold);
  text-transform: uppercase;
  letter-spacing: 0.06em;
}

/* Quick Switcher Tabs */
.master-tabs-bar {
  display: flex;
  gap: 8px;
  overflow-x: auto;
  padding-bottom: 4px;
  margin-bottom: 24px;
  border-bottom: 1px solid var(--admin-border-subtle);
  -webkit-overflow-scrolling: touch;
}

.master-tab-item {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 10px 18px;
  background: rgba(255, 255, 255, 0.04);
  border: 1px solid var(--admin-border-subtle);
  border-radius: var(--radius-lg);
  color: var(--admin-text-secondary);
  font-size: 0.88rem;
  font-weight: 600;
  text-decoration: none;
  white-space: nowrap;
  transition: all var(--transition-fast);
}

.master-tab-item:hover {
  background: rgba(255, 255, 255, 0.09);
  color: var(--admin-text-primary);
  border-color: var(--admin-border-hover);
}

.master-tab-item.active {
  background: rgba(226, 101, 28, 0.14);
  border-color: var(--color-gold);
  color: var(--admin-text-primary);
  box-shadow: 0 4px 14px rgba(226, 101, 28, 0.15);
}

.tab-icon {
  font-size: 1rem;
}

.tab-chip {
  padding: 2px 7px;
  background: rgba(0, 0, 0, 0.35);
  border-radius: var(--radius-full);
  font-size: 0.72rem;
  font-weight: 700;
  color: var(--color-gold);
}

/* KPI Summary Cards */
.master-stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 16px;
  margin-bottom: 24px;
}

.stat-card {
  background: var(--admin-bg-surface);
  border: 1px solid var(--admin-border-subtle);
  border-radius: var(--radius-xl);
  padding: 18px 20px;
  box-shadow: var(--shadow-sm);
  transition: all var(--transition-fast);
}

.stat-card:hover {
  border-color: var(--admin-border-hover);
  transform: translateY(-2px);
}

.stat-meta {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 8px;
}

.stat-title {
  font-size: 0.75rem;
  color: var(--admin-text-muted);
  text-transform: uppercase;
  font-weight: 700;
  letter-spacing: 0.06em;
}

.stat-badge {
  font-size: 0.68rem;
  padding: 2px 8px;
  border-radius: var(--radius-full);
  background: rgba(255, 255, 255, 0.08);
  color: var(--admin-text-secondary);
  font-weight: 700;
}

.stat-badge-emerald {
  background: rgba(16, 185, 129, 0.15);
  color: #10B981;
}

.stat-badge-amber {
  background: rgba(245, 158, 11, 0.15);
  color: #F59E0B;
}

.stat-badge-gold {
  background: rgba(226, 101, 28, 0.18);
  color: #E2651C;
}

.stat-val {
  font-family: var(--font-display);
  font-size: 1.8rem;
  font-weight: 800;
  color: var(--admin-text-primary);
  line-height: 1.1;
}

.stat-sub {
  font-size: 0.75rem;
  color: var(--admin-text-muted);
  margin-top: 6px;
}

.code-pill {
  padding: 2px 6px;
  background: rgba(255, 255, 255, 0.06);
  border-radius: 4px;
  color: var(--color-gold);
  font-size: 0.72rem;
}

/* Master Table Panel */
.master-table-panel {
  padding: 0;
  overflow: hidden;
}

.panel-filter-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 12px;
  padding: 16px 20px;
  background: rgba(255, 255, 255, 0.02);
  border-bottom: 1px solid var(--admin-border-subtle);
}

.search-input-wrap {
  position: relative;
  flex: 1;
  min-width: 260px;
  max-width: 440px;
}

.search-icon {
  position: absolute;
  left: 14px;
  top: 50%;
  transform: translateY(-50%);
  color: var(--admin-text-muted);
  pointer-events: none;
}

.filter-search-input {
  width: 100%;
  background: var(--admin-bg-base);
  border: 1px solid var(--admin-border-subtle);
  border-radius: var(--radius-md);
  padding: 9px 36px 9px 38px;
  color: var(--admin-text-primary);
  font-size: 0.88rem;
  outline: none;
  transition: all var(--transition-fast);
}

.filter-search-input:focus {
  border-color: var(--color-gold);
  box-shadow: 0 0 0 3px rgba(226, 101, 28, 0.15);
}

.search-clear-btn {
  position: absolute;
  right: 12px;
  top: 50%;
  transform: translateY(-50%);
  background: transparent;
  border: none;
  color: var(--admin-text-muted);
  cursor: pointer;
  font-size: 0.9rem;
}

.filter-actions-wrap {
  display: flex;
  align-items: center;
  gap: 10px;
}

.filter-select {
  background: var(--admin-bg-base);
  border: 1px solid var(--admin-border-subtle);
  color: var(--admin-text-secondary);
  border-radius: var(--radius-md);
  padding: 8px 14px;
  font-size: 0.84rem;
  font-weight: 600;
  outline: none;
  cursor: pointer;
}

/* Master Data Table */
.master-data-table {
  width: 100%;
  border-collapse: collapse;
  text-align: left;
}

.master-data-table th {
  padding: 14px 20px;
  font-size: 0.75rem;
  text-transform: uppercase;
  font-weight: 800;
  letter-spacing: 0.06em;
  color: var(--admin-text-muted);
  border-bottom: 1px solid var(--admin-border-subtle);
  background: rgba(0, 0, 0, 0.15);
}

.master-data-table td {
  padding: 14px 20px;
  border-bottom: 1px solid var(--admin-border-subtle);
  vertical-align: middle;
}

.master-table-row {
  transition: background var(--transition-fast);
}

.master-table-row:hover {
  background: rgba(255, 255, 255, 0.03);
}

.order-badge {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 26px;
  height: 26px;
  border-radius: 6px;
  background: rgba(255, 255, 255, 0.06);
  font-size: 0.78rem;
  font-weight: 700;
  color: var(--admin-text-secondary);
}

.item-primary-text {
  font-weight: 700;
  color: var(--admin-text-primary);
  font-size: 0.92rem;
}

.item-secondary-text code {
  font-size: 0.76rem;
  color: var(--color-gold);
}

.icon-tag {
  padding: 3px 8px;
  background: rgba(255, 255, 255, 0.06);
  border-radius: 4px;
  font-size: 0.74rem;
  color: var(--admin-text-secondary);
  font-family: monospace;
}

.bn-text-pill {
  font-size: 0.92rem;
  color: #38BDF8;
  font-weight: 600;
  padding: 3px 10px;
  background: rgba(56, 189, 248, 0.1);
  border-radius: var(--radius-md);
  display: inline-block;
}

.status-color-circle {
  width: 14px;
  height: 14px;
  border-radius: 50%;
  box-shadow: 0 0 6px currentColor;
  flex-shrink: 0;
}

.status-badge-preview {
  padding: 2px 9px;
  border-radius: var(--radius-full);
  border: 1px solid;
  font-size: 0.72rem;
  font-weight: 700;
  letter-spacing: 0.02em;
  background: rgba(0, 0, 0, 0.35);
  display: inline-block;
}

.symbol-pill {
  padding: 2px 8px;
  background: rgba(226, 101, 28, 0.15);
  border: 1px solid rgba(226, 101, 28, 0.3);
  color: var(--color-gold);
  border-radius: var(--radius-md);
  font-size: 0.76rem;
  font-weight: 700;
}

.multiplier-text {
  font-size: 0.84rem;
  color: var(--admin-text-secondary);
}

/* Status Active / Inactive Toggle Button */
.toggle-status-btn {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 4px 10px;
  border-radius: var(--radius-full);
  font-size: 0.75rem;
  font-weight: 700;
  border: 1px solid transparent;
  cursor: pointer;
  transition: all var(--transition-fast);
}

.toggle-status-btn.is-active {
  background: rgba(16, 185, 129, 0.12);
  border-color: rgba(16, 185, 129, 0.28);
  color: #10B981;
}

.toggle-status-btn.is-active .toggle-dot {
  width: 7px;
  height: 7px;
  border-radius: 50%;
  background: #10B981;
  box-shadow: 0 0 8px #10B981;
}

.toggle-status-btn.is-inactive {
  background: rgba(148, 163, 184, 0.12);
  border-color: rgba(148, 163, 184, 0.25);
  color: #8E9B8F;
}

.toggle-status-btn.is-inactive .toggle-dot {
  width: 7px;
  height: 7px;
  border-radius: 50%;
  background: #5F6E63;
}

.toggle-status-btn:hover {
  opacity: 0.85;
  transform: scale(1.03);
}

/* Action Button Group */
.action-btn-group {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  gap: 6px;
}

.btn-table-action {
  padding: 6px 10px;
  border-radius: var(--radius-sm);
  border: 1px solid var(--admin-border-subtle);
  background: rgba(255, 255, 255, 0.05);
  color: var(--admin-text-secondary);
  font-size: 0.78rem;
  font-weight: 600;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 5px;
  transition: all var(--transition-fast);
}

.btn-table-action:hover {
  background: rgba(255, 255, 255, 0.12);
  color: var(--admin-text-primary);
}

.btn-delete:hover {
  background: rgba(239, 68, 68, 0.18);
  border-color: rgba(239, 68, 68, 0.4);
  color: #EF4444;
}

/* Modals */
.master-modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.78);
  backdrop-filter: blur(4px);
  z-index: 1000;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
}

.master-modal-card {
  background: var(--admin-bg-surface);
  border: 1px solid var(--admin-border-accent);
  border-radius: var(--radius-2xl);
  width: 100%;
  max-width: 520px;
  max-height: 90vh;
  overflow-y: auto;
  padding: 26px;
  box-shadow: 0 20px 50px rgba(0, 0, 0, 0.7);
  animation: modalPop 0.22s cubic-bezier(0.16, 1, 0.3, 1);
}

@keyframes modalPop {
  from { opacity: 0; transform: scale(0.95); }
  to { opacity: 1; transform: scale(1); }
}

.modal-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  margin-bottom: 20px;
  padding-bottom: 14px;
  border-bottom: 1px solid var(--admin-border-subtle);
}

.modal-title {
  font-family: var(--font-display);
  font-size: 1.25rem;
  font-weight: 800;
  color: var(--admin-text-primary);
}

.modal-sub {
  font-size: 0.78rem;
  color: var(--admin-text-muted);
  margin-top: 3px;
}

.modal-close-btn {
  background: rgba(255, 255, 255, 0.08);
  border: none;
  width: 32px;
  height: 32px;
  border-radius: 50%;
  color: var(--admin-text-primary);
  cursor: pointer;
  font-size: 0.95rem;
  display: flex;
  align-items: center;
  justify-content: center;
}

.modal-form {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.form-grid-2 {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 12px;
}

.form-label {
  font-size: 0.8rem;
  font-weight: 700;
  color: var(--admin-text-secondary);
}

.form-input, .form-textarea {
  background: var(--admin-bg-base);
  border: 1px solid var(--admin-border-subtle);
  border-radius: var(--radius-md);
  padding: 10px 14px;
  color: var(--admin-text-primary);
  font-size: 0.88rem;
  outline: none;
  transition: all var(--transition-fast);
}

.form-input:focus, .form-textarea:focus {
  border-color: var(--color-gold);
  box-shadow: 0 0 0 3px rgba(226, 101, 28, 0.15);
}

.color-picker-wrap {
  display: flex;
  align-items: center;
  gap: 8px;
}

.color-input {
  width: 44px;
  height: 40px;
  border-radius: var(--radius-md);
  border: 1px solid var(--admin-border-subtle);
  background: transparent;
  cursor: pointer;
  padding: 0;
}

.preview-box {
  background: rgba(255, 255, 255, 0.03);
  border: 1px dashed var(--admin-border-subtle);
  border-radius: var(--radius-lg);
  padding: 12px 16px;
}

.toggle-checkbox-label {
  display: flex;
  align-items: center;
  gap: 10px;
  font-size: 0.84rem;
  color: var(--admin-text-secondary);
  cursor: pointer;
}

.toggle-checkbox {
  accent-color: #10B981;
  width: 16px;
  height: 16px;
}

.modal-footer {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  gap: 10px;
  margin-top: 10px;
  padding-top: 16px;
  border-top: 1px solid var(--admin-border-subtle);
}

/* Delete Modal */
.delete-card {
  max-width: 420px;
  text-align: center;
}

.delete-icon {
  font-size: 2.5rem;
  margin-bottom: 12px;
}

.btn-danger {
  background: #EF4444;
  border-color: #EF4444;
  color: #FFFFFF;
}

.btn-danger:hover {
  background: #DC2626;
}

/* Spin Animation */
.spin-anim {
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

.loading-state-box, .empty-state-box {
  padding: 48px 24px;
  text-align: center;
}

.spinner-ring {
  width: 36px;
  height: 36px;
  border: 3px solid rgba(226, 101, 28, 0.2);
  border-top-color: var(--color-gold);
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
  margin: 0 auto 14px;
}
</style>

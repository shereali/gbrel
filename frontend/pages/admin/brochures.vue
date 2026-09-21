<template>
  <div class="admin-page animate-fade-in">
    <!-- Header -->
    <div class="admin-header-row">
      <div>
        <div class="flex items-center gap-3 flex-wrap">
          <h1 class="page-title">Brochure Vault & Collateral Hub</h1>
          <span 
            class="badge" 
            style="background:rgba(56,189,248,0.15); color:#38BDF8; border:1px solid rgba(56,189,248,0.3); font-size:0.75rem; font-weight:700;"
          >
            📑 PDF & Pitch Deck Management
          </span>
        </div>
        <p class="page-subtitle">
          Manage project brochures, architectural blueprints, Land Share prospectuses, and track download analytics.
        </p>
      </div>
      <div class="admin-header-actions flex items-center gap-3">
        <button class="btn btn-outline-white" @click="fetchBrochures" :disabled="isLoading">
          <span v-if="isLoading" class="animate-spin inline-block mr-1">◌</span>
          <span>↻ Refresh Vault</span>
        </button>
        <button class="btn btn-gold" @click="openUploadModal">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <line x1="12" y1="5" x2="12" y2="19"/>
            <line x1="5" y1="12" x2="19" y2="12"/>
          </svg>
          <span>+ Upload Marketing Brochure</span>
        </button>
      </div>
    </div>

    <!-- KPI Summary Cards -->
    <div class="grid grid-3" style="gap: 16px; margin-bottom: 20px;">
      <div class="panel-card" style="padding: 16px 20px;">
        <div class="text-subtle" style="font-size: 0.8rem; text-transform: uppercase; font-weight: 700;">Total Active Collateral</div>
        <div style="font-size: 1.8rem; font-weight: 800; color: #FFF; margin-top: 4px;">{{ brochures.length }}</div>
        <div style="font-size: 0.75rem; color: #38BDF8; margin-top: 4px;">Verified PDFs & Documents</div>
      </div>
      <div class="panel-card" style="padding: 16px 20px;">
        <div class="text-subtle" style="font-size: 0.8rem; text-transform: uppercase; font-weight: 700;">Total Client Downloads</div>
        <div style="font-size: 1.8rem; font-weight: 800; color: var(--color-gold); margin-top: 4px;">{{ totalDownloads }}</div>
        <div style="font-size: 0.75rem; color: #10B981; margin-top: 4px;">Lead generation & buyer engagement</div>
      </div>
      <div class="panel-card" style="padding: 16px 20px;">
        <div class="text-subtle" style="font-size: 0.8rem; text-transform: uppercase; font-weight: 700;">Associated Mandates</div>
        <div style="font-size: 1.8rem; font-weight: 800; color: #34D399; margin-top: 4px;">{{ linkedPropertiesCount }}</div>
        <div style="font-size: 0.75rem; color: var(--admin-text-muted); margin-top: 4px;">Linked with public property listings</div>
      </div>
    </div>

    <!-- Filter & Search Bar -->
    <div class="panel-card" style="padding: 14px 20px; margin-bottom: 20px;">
      <div class="flex justify-between items-center flex-wrap gap-3">
        <div class="flex items-center gap-3 flex-wrap flex-1">
          <input 
            v-model="searchQuery" 
            type="text" 
            placeholder="Search brochure title, attached property, file name..." 
            class="form-input" 
            style="max-width: 320px;" 
          />
          <select v-model="categoryFilter" class="form-select" style="width: auto;">
            <option value="">All Categories</option>
            <option value="Property Brochure">Property Brochure</option>
            <option value="Land Share Prospectus">Land Share Prospectus</option>
            <option value="Architectural Deck">Architectural Deck</option>
            <option value="Corporate Profile">Corporate Profile</option>
          </select>
          <button 
            v-if="searchQuery || categoryFilter" 
            class="btn btn-sm btn-outline-white" 
            @click="searchQuery = ''; categoryFilter = ''"
          >
            Clear Filters
          </button>
        </div>
        <div style="font-size: 0.82rem; color: var(--admin-text-muted);">
          Showing {{ filteredBrochures.length }} of {{ brochures.length }} items
        </div>
      </div>
    </div>

    <!-- Brochures Table -->
    <div class="panel-card" style="padding: 0; overflow: hidden;">
      <div class="table-responsive">
        <table class="admin-table">
          <thead>
            <tr>
              <th>Brochure Document</th>
              <th>Category</th>
              <th>Linked Mandate</th>
              <th>File Size</th>
              <th>Downloads</th>
              <th>Status</th>
              <th style="text-align: right;">Vault Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="isLoading">
              <td colspan="7" style="text-align: center; padding: 40px; color: var(--admin-text-muted);">
                <span class="animate-spin inline-block mr-2">◌</span> Loading vault catalog from database...
              </td>
            </tr>
            <tr v-else-if="filteredBrochures.length === 0">
              <td colspan="7" style="text-align: center; padding: 40px; color: var(--admin-text-muted);">
                No brochures match your filter criteria. Click "+ Upload Marketing Brochure" to add one.
              </td>
            </tr>
            <tr v-for="b in filteredBrochures" :key="b.id">
              <td>
                <div class="flex items-center gap-3">
                  <div 
                    style="width: 36px; height: 36px; border-radius: 6px; background: rgba(239,68,68,0.15); border: 1px solid rgba(239,68,68,0.3); display: flex; align-items: center; justify-content: center; color: #EF4444; font-weight: 800; font-size: 0.72rem; flex-shrink: 0;"
                  >
                    {{ b.file_type || 'PDF' }}
                  </div>
                  <div style="overflow: hidden; max-width: 280px;">
                    <strong style="display: block; font-size: 0.9rem; color: #FFF; text-overflow: ellipsis; overflow: hidden; white-space: nowrap;">
                      {{ b.title }}
                    </strong>
                    <span style="font-size: 0.75rem; color: var(--admin-text-muted); text-overflow: ellipsis; overflow: hidden; white-space: nowrap; display: block;">
                      {{ b.file_name }}
                    </span>
                  </div>
                </div>
              </td>
              <td>
                <span class="badge" style="background: rgba(255,255,255,0.06); color: #E2E8F0; font-size: 0.75rem;">
                  {{ b.category }}
                </span>
              </td>
              <td>
                <div v-if="b.property_id" style="font-size: 0.85rem; color: #38BDF8;">
                  <NuxtLink :to="`/properties/${b.property_id}`" target="_blank" style="text-decoration: underline;">
                    {{ b.property_title }}
                  </NuxtLink>
                  <div style="font-size: 0.72rem; color: var(--admin-text-muted);">{{ b.property_area }}</div>
                </div>
                <span v-else style="font-size: 0.8rem; color: var(--admin-text-muted);">
                  Corporate / Unassigned
                </span>
              </td>
              <td>
                <span style="font-size: 0.85rem; color: #CBD5E1;">{{ b.file_size }}</span>
              </td>
              <td>
                <span class="badge" style="background: rgba(16,185,129,0.15); color: #34D399; font-weight: 700; font-size: 0.78rem;">
                  {{ b.download_count }} Downloads
                </span>
              </td>
              <td>
                <span class="badge-admin" :class="b.is_public ? 'active' : 'neutral'">
                  {{ b.is_public ? 'Public' : 'Restricted' }}
                </span>
              </td>
              <td style="text-align: right;">
                <div class="action-btn-group justify-end">
                  <a 
                    :href="b.file_url" 
                    target="_blank" 
                    class="btn btn-sm btn-outline-white" 
                    style="font-size: 0.78rem; padding: 4px 10px;"
                    title="Preview / Download Document"
                    @click="onBrochureDownload(b)"
                  >
                    Download ↗
                  </a>
                  <button 
                    class="btn btn-sm btn-outline-white" 
                    style="font-size: 0.78rem; padding: 4px 10px;" 
                    @click="copyBrochureLink(b)"
                    title="Copy direct download link"
                  >
                    Copy Link
                  </button>
                  <button 
                    class="action-btn delete" 
                    @click="promptDeleteBrochure(b)" 
                    title="Remove brochure from vault"
                  >
                    ✕
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- ======================================================================
         MODAL 1: UPLOAD BROCHURE
         ====================================================================== -->
    <div v-if="showUploadModal" class="admin-modal-overlay" @click.self="showUploadModal = false">
      <div class="admin-modal-card animate-fade-in-up" style="max-width: 600px;">
        <div class="admin-modal-header">
          <h3 class="admin-modal-title">Upload Project Brochure / Document</h3>
          <button class="admin-modal-close" @click="showUploadModal = false">✕</button>
        </div>

        <form @submit.prevent="saveBrochure">
          <div class="admin-modal-body">
            <div class="form-group" style="margin-bottom: 14px;">
              <label class="form-label">Brochure Document Title *</label>
              <input 
                v-model="uploadForm.title" 
                type="text" 
                required 
                placeholder="e.g. The Crown Residence - Official Project Prospectus" 
                class="form-input" 
              />
            </div>

            <div class="grid grid-2" style="gap: 14px; margin-bottom: 14px;">
              <div class="form-group">
                <label class="form-label">Category</label>
                <select v-model="uploadForm.category" class="form-select">
                  <option value="Property Brochure">Property Brochure</option>
                  <option value="Land Share Prospectus">Land Share Prospectus</option>
                  <option value="Architectural Deck">Architectural Deck</option>
                  <option value="Corporate Profile">Corporate Profile</option>
                </select>
              </div>
              <div class="form-group">
                <label class="form-label">Attach to Mandate</label>
                <select v-model="uploadForm.property_id" class="form-select">
                  <option :value="null">-- Corporate / Standalone --</option>
                  <option v-for="p in properties" :key="p.id" :value="p.id">
                    #{{ p.id }} - {{ p.title }}
                  </option>
                </select>
              </div>
            </div>

            <!-- File Upload Box -->
            <div style="background: var(--admin-bg-surface); padding: 16px; border-radius: var(--radius-sm); border: 1px dashed var(--admin-border-subtle); margin-bottom: 14px;">
              <div class="flex items-center justify-between mb-2">
                <label class="form-label" style="margin: 0; color: #38BDF8; font-weight: 700;">
                  Document File (PDF / DOC / DOCX) *
                </label>
                <button 
                  type="button" 
                  class="btn btn-sm btn-emerald" 
                  @click="triggerFileInput"
                  :disabled="isUploadingFile"
                  style="font-size: 0.78rem; padding: 4px 10px;"
                >
                  {{ isUploadingFile ? 'Uploading...' : 'Browse Local File' }}
                </button>
                <input 
                  ref="fileInputRef" 
                  type="file" 
                  accept=".pdf,.doc,.docx,application/pdf" 
                  style="display: none;" 
                  @change="onFileSelected" 
                />
              </div>

              <div v-if="uploadForm.file_url" class="flex items-center justify-between gap-2 p-2" style="background: rgba(16,185,129,0.1); border: 1px solid rgba(16,185,129,0.3); border-radius: 4px; margin-top: 8px;">
                <div style="font-size: 0.8rem; color: #34D399; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                  ✓ {{ uploadForm.file_name || uploadForm.file_url }} ({{ uploadForm.file_size }})
                </div>
                <button type="button" class="btn btn-sm btn-outline-white" style="font-size: 0.7rem; padding: 2px 6px; color: #EF4444;" @click="uploadForm.file_url = ''">
                  Remove
                </button>
              </div>

              <div style="margin-top: 10px;">
                <label style="font-size: 0.75rem; color: var(--admin-text-muted); display: block; margin-bottom: 4px;">
                  Or paste direct URL:
                </label>
                <input 
                  v-model="uploadForm.file_url" 
                  type="url" 
                  placeholder="https://... or /storage/brochures/..." 
                  class="form-input" 
                  style="font-size: 0.82rem;" 
                />
              </div>
            </div>

            <label class="flex items-center gap-2" style="cursor: pointer; font-size: 0.85rem;">
              <input v-model="uploadForm.is_public" type="checkbox" style="accent-color: #10B981; width: 16px; height: 16px;" />
              <span>Make available for public download on property page</span>
            </label>
          </div>

          <div class="admin-modal-footer">
            <button type="button" class="btn btn-sm btn-outline-white" @click="showUploadModal = false">Cancel</button>
            <button type="submit" class="btn btn-sm btn-emerald" :disabled="isSubmitting || !uploadForm.file_url">
              {{ isSubmitting ? 'Archiving...' : 'Save to Brochure Vault' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- ======================================================================
         MODAL 2: DELETE BROCHURE CONFIRMATION
         ====================================================================== -->
    <div v-if="deleteTarget" class="admin-modal-overlay" @click.self="deleteTarget = null">
      <div class="admin-modal-card animate-fade-in-up" style="max-width: 440px;">
        <div class="admin-modal-header" style="background: #1E1622; border-bottom: 1px solid rgba(239,68,68,0.2);">
          <h3 class="admin-modal-title" style="color: #F87171;">Remove Brochure</h3>
          <button class="admin-modal-close" @click="deleteTarget = null">✕</button>
        </div>
        <div class="admin-modal-body">
          <p style="color: #E2E8F0; font-size: 0.92rem; line-height: 1.5;">
            Are you sure you want to delete <strong>"{{ deleteTarget.title }}"</strong>?
          </p>
          <p style="color: var(--admin-text-muted); font-size: 0.8rem; margin-top: 8px;">
            The document will be removed from the public website and archive.
          </p>
        </div>
        <div class="admin-modal-footer">
          <button class="btn btn-sm btn-outline-white" @click="deleteTarget = null">Cancel</button>
          <button class="btn btn-sm" style="background: #EF4444; color: #FFF;" @click="executeDeleteBrochure">
            Confirm Delete
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, computed, onMounted } from 'vue'
import { useToast } from '~/composables/useToast'
import { useApiUrl } from '~/composables/useApi'
import { useProperties } from '~/composables/useProperties'

definePageMeta({
  layout: 'admin'
})

const toast = useToast()
const { properties, fetchProperties } = useProperties()

const brochures = ref<any[]>([])
const isLoading = ref(false)
const isSubmitting = ref(false)
const isUploadingFile = ref(false)

const searchQuery = ref('')
const categoryFilter = ref('')
const showUploadModal = ref(false)
const deleteTarget = ref<any | null>(null)
const fileInputRef = ref<HTMLInputElement | null>(null)

const uploadForm = reactive({
  title: '',
  category: 'Property Brochure',
  property_id: null as number | null,
  file_url: '',
  file_name: '',
  file_size: '3.5 MB',
  file_type: 'PDF',
  is_public: true
})

const totalDownloads = computed(() => {
  return brochures.value.reduce((sum, b) => sum + (Number(b.download_count) || 0), 0)
})

const linkedPropertiesCount = computed(() => {
  const ids = new Set(brochures.value.map(b => b.property_id).filter(Boolean))
  return ids.size
})

const filteredBrochures = computed(() => {
  return brochures.value.filter(b => {
    if (categoryFilter.value && b.category !== categoryFilter.value) return false
    if (searchQuery.value) {
      const q = searchQuery.value.toLowerCase().trim()
      return (
        (b.title || '').toLowerCase().includes(q) ||
        (b.file_name || '').toLowerCase().includes(q) ||
        (b.property_title || '').toLowerCase().includes(q)
      )
    }
    return true
  })
})

const fetchBrochures = async () => {
  isLoading.value = true
  try {
    const res = await fetch(useApiUrl('/brochures'))
    if (res.ok) {
      const json = await res.json()
      if (json && json.data) {
        brochures.value = json.data
      }
    }
  } catch (err) {
    console.error('Failed to fetch brochures:', err)
  } finally {
    isLoading.value = false
  }
}

onMounted(async () => {
  await Promise.all([fetchBrochures(), fetchProperties()])
})

const openUploadModal = () => {
  uploadForm.title = ''
  uploadForm.category = 'Property Brochure'
  uploadForm.property_id = properties.value[0]?.id || null
  uploadForm.file_url = ''
  uploadForm.file_name = ''
  uploadForm.file_size = '4.0 MB'
  uploadForm.file_type = 'PDF'
  uploadForm.is_public = true
  showUploadModal.value = true
}

const triggerFileInput = () => {
  fileInputRef.value?.click()
}

const onFileSelected = async (e: Event) => {
  const target = e.target as HTMLInputElement
  if (!target.files || target.files.length === 0) return
  const file = target.files[0]

  isUploadingFile.value = true
  try {
    const formData = new FormData()
    formData.append('brochure', file)

    const res = await fetch(useApiUrl('/upload'), {
      method: 'POST',
      body: formData
    })

    if (!res.ok) {
      throw new Error(`Upload failed with status ${res.status}`)
    }

    const json = await res.json()
    if (json && json.success && json.url) {
      uploadForm.file_url = json.url
      uploadForm.file_name = json.original_name || file.name
      const sizeMB = (file.size / (1024 * 1024)).toFixed(1) + ' MB'
      uploadForm.file_size = sizeMB
      uploadForm.file_type = file.name.split('.').pop()?.toUpperCase() || 'PDF'
      if (!uploadForm.title) {
        uploadForm.title = file.name.replace(/\.[^/.]+$/, '').replace(/[_-]/g, ' ')
      }
      toast.success('File Uploaded', `${file.name} saved to storage.`)
    }
  } catch (err: any) {
    toast.error('Upload Failed', err?.message || 'Could not upload brochure.')
  } finally {
    isUploadingFile.value = false
    target.value = ''
  }
}

const saveBrochure = async () => {
  if (!uploadForm.title || !uploadForm.file_url) return
  isSubmitting.value = true

  try {
    const res = await fetch(useApiUrl('/brochures'), {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(uploadForm)
    })

    if (!res.ok) {
      const err = await res.json().catch(() => null)
      throw new Error(err?.message || 'Failed to register brochure')
    }

    toast.success('Brochure Archived', `"${uploadForm.title}" is now active in the vault.`)
    showUploadModal.value = false
    await fetchBrochures()
  } catch (err: any) {
    toast.error('Save Failed', err?.message || 'Could not save brochure.')
  } finally {
    isSubmitting.value = false
  }
}

const onBrochureDownload = async (b: any) => {
  try {
    await fetch(useApiUrl(`/brochures/${b.id}/download`), { method: 'POST' })
    b.download_count = (b.download_count || 0) + 1
  } catch (e) {
    // Non-blocking download tracking
  }
}

const copyBrochureLink = async (b: any) => {
  try {
    const fullUrl = b.file_url.startsWith('http') ? b.file_url : window.location.origin + b.file_url
    await navigator.clipboard.writeText(fullUrl)
    toast.info('Link Copied', 'Direct brochure link copied to clipboard.')
  } catch (err) {
    toast.error('Copy Failed', 'Could not copy link to clipboard.')
  }
}

const promptDeleteBrochure = (b: any) => {
  deleteTarget.value = b
}

const executeDeleteBrochure = async () => {
  if (!deleteTarget.value) return
  const id = deleteTarget.value.id
  const title = deleteTarget.value.title

  try {
    const res = await fetch(useApiUrl(`/brochures/${id}`), { method: 'DELETE' })
    if (!res.ok) throw new Error('Failed to delete brochure')
    toast.info('Brochure Deleted', `"${title}" removed from vault.`)
    deleteTarget.value = null
    await fetchBrochures()
  } catch (err: any) {
    toast.error('Delete Failed', err?.message || 'Could not delete brochure.')
  }
}
</script>

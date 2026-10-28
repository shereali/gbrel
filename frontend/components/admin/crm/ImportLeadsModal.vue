<template>
  <div class="admin-modal-overlay im-overlay" @click.self="emit('close')">
    <div class="admin-modal-card im-card animate-fade-in-up" role="dialog" aria-modal="true" aria-labelledby="im-title">
      <div class="admin-modal-header">
        <h3 id="im-title" class="admin-modal-title">Import Leads from CSV / Excel</h3>
        <button type="button" class="admin-modal-close" aria-label="Close" @click="emit('close')">✕</button>
      </div>

      <div v-if="result" class="admin-modal-body im-body">
        <div class="im-result-banner">
          <h4>Import Completed Successfully</h4>
          <p>{{ result.total_rows }} row(s) processed from <strong>{{ selectedFile?.name }}</strong></p>
        </div>

        <div class="im-stats-grid">
          <div class="im-stat im-stat--created">
            <span class="im-stat-val">{{ result.created }}</span>
            <span class="im-stat-lbl">Created</span>
          </div>
          <div class="im-stat im-stat--updated">
            <span class="im-stat-val">{{ result.updated }}</span>
            <span class="im-stat-lbl">Updated</span>
          </div>
          <div class="im-stat im-stat--skipped">
            <span class="im-stat-val">{{ result.skipped }}</span>
            <span class="im-stat-lbl">Skipped (Duplicate)</span>
          </div>
          <div class="im-stat im-stat--failed">
            <span class="im-stat-val">{{ result.failed }}</span>
            <span class="im-stat-lbl">Invalid Rows</span>
          </div>
        </div>

        <div v-if="result.errors?.length" class="im-errors-box">
          <h5>Skipped / Invalid Rows ({{ result.errors.length }})</h5>
          <ul class="im-error-list">
            <li v-for="(err, idx) in result.errors" :key="idx">
              <strong>Row {{ err.row }}</strong> ({{ err.name || 'No name' }}): {{ err.reason }}
            </li>
          </ul>
        </div>

        <div class="admin-modal-footer im-footer">
          <button type="button" class="btn btn-sm btn-outline-white" @click="resetForm">Import Another File</button>
          <button type="button" class="btn btn-sm btn-emerald" @click="emit('close')">Done</button>
        </div>
      </div>

      <form v-else novalidate @submit.prevent="submitImport">
        <div class="admin-modal-body im-body">
          <!-- File Dropzone -->
          <div
            class="im-dropzone"
            :class="{ 'is-dragging': isDragging, 'has-file': !!selectedFile }"
            @dragover.prevent="isDragging = true"
            @dragleave.prevent="isDragging = false"
            @drop.prevent="onDrop"
            @click="fileInput?.click()"
          >
            <input
              ref="fileInput"
              type="file"
              accept=".csv,.xlsx,.xls,.tsv,.txt,text/csv,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-excel"
              class="im-file-input"
              @change="onFileSelect"
            />
            <template v-if="selectedFile">
              <div class="im-file-badge">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#10B981" stroke-width="2" aria-hidden="true">
                  <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                  <polyline points="14 2 14 8 20 8"/>
                  <line x1="16" y1="13" x2="8" y2="13"/>
                  <line x1="16" y1="17" x2="8" y2="17"/>
                </svg>
                <div class="im-file-meta">
                  <strong>{{ selectedFile.name }}</strong>
                  <span>{{ formatBytes(selectedFile.size) }} · Click to change file</span>
                </div>
              </div>
            </template>
            <template v-else>
              <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="im-upload-icon" aria-hidden="true">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                <polyline points="17 8 12 3 7 8"/>
                <line x1="12" y1="3" x2="12" y2="15"/>
              </svg>
              <p class="im-drop-title">Drop your <strong>.csv</strong> or <strong>.xlsx</strong> file here, or click to browse</p>
              <p class="im-drop-sub">Supports Excel (.xlsx), CSV (.csv), Meta Lead Ads exports (.csv / .tsv), and custom question columns.</p>
            </template>
          </div>

          <div class="im-template-row">
            <span>Auto-detects Name, Phone, Email, Project, Status, Budget, Notes &amp; custom form questions.</span>
            <button type="button" class="im-template-btn" @click="downloadTemplate">
              Download Sample CSV
            </button>
          </div>

          <div class="im-grid">
            <label class="im-field">
              <span>Default Project / Property <em>(if not in file)</em></span>
              <select v-model="propertyChoice" class="form-select">
                <option value="">— Use file column or General Inquiry —</option>
                <option v-for="p in propertyOptions" :key="p.id" :value="String(p.id)">{{ p.label }}</option>
                <option value="custom">Custom project name…</option>
              </select>
            </label>

            <label v-if="isLeadAdmin && staff.length" class="im-field">
              <span>Assign Imported Leads To</span>
              <select v-model="assignedTo" class="form-select">
                <option :value="null">Unassigned</option>
                <option v-for="s in staff" :key="s.id" :value="s.id">{{ s.name }} ({{ s.role || 'Staff' }})</option>
              </select>
            </label>
          </div>

          <label v-if="propertyChoice === 'custom'" class="im-field">
            <span>Custom Project / Campaign Name</span>
            <input v-model.trim="customProperty" type="text" maxlength="255" placeholder="e.g. Mirpur DOHS Luxury Condo" class="form-input" />
          </label>

          <fieldset class="im-group">
            <legend>If a phone number already exists in CRM</legend>
            <div class="im-chips" role="radiogroup" aria-label="Duplicate phone handling">
              <label class="im-chip" :class="{ on: duplicateMode === 'skip' }">
                <input v-model="duplicateMode" type="radio" value="skip" />
                <span>Skip duplicates <em>(recommended)</em></span>
              </label>
              <label class="im-chip" :class="{ on: duplicateMode === 'update' }">
                <input v-model="duplicateMode" type="radio" value="update" />
                <span>Update existing lead</span>
              </label>
              <label class="im-chip" :class="{ on: duplicateMode === 'allow' }">
                <input v-model="duplicateMode" type="radio" value="allow" />
                <span>Create duplicate anyway</span>
              </label>
            </div>
          </fieldset>

          <p v-if="errorMsg" class="im-err" role="alert">{{ errorMsg }}</p>
        </div>

        <div class="admin-modal-footer im-footer">
          <button type="button" class="btn btn-sm btn-outline-white" :disabled="importing" @click="emit('close')">Cancel</button>
          <button type="submit" class="btn btn-sm btn-emerald" :disabled="!selectedFile || importing">
            {{ importing ? 'Importing Leads…' : 'Import Leads' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { useProperties } from '~/composables/useProperties'
import { useLeadCrm, type StaffMember } from '~/composables/useLeadCrm'

defineProps<{
  staff: StaffMember[]
  isLeadAdmin: boolean
}>()

const emit = defineEmits<{
  close: []
  imported: [payload: { created: number; updated: number; skipped: number; leads: any[] }]
}>()

const crm = useLeadCrm()
const { properties, fetchProperties } = useProperties()

const fileInput = ref<HTMLInputElement | null>(null)
const selectedFile = ref<File | null>(null)
const isDragging = ref(false)
const propertyChoice = ref('')
const customProperty = ref('')
const assignedTo = ref<number | null>(null)
const duplicateMode = ref<'skip' | 'update' | 'allow'>('skip')
const importing = ref(false)
const errorMsg = ref('')
const result = ref<{
  total_rows: number
  created: number
  updated: number
  skipped: number
  failed: number
  errors: { row: number; name: string; phone: string; reason: string }[]
  leads: any[]
} | null>(null)

onMounted(() => {
  if (!properties.value?.length) fetchProperties()
})

const propertyOptions = computed(() =>
  (properties.value || []).map((p: any) => ({
    id: p.id,
    title: p.title,
    label: p.location ? `${p.title} (${p.location})` : p.title
  }))
)

const onFileSelect = (e: Event) => {
  const input = e.target as HTMLInputElement
  const file = input.files?.[0]
  if (file) {
    selectedFile.value = file
    errorMsg.value = ''
  }
}

const onDrop = (e: DragEvent) => {
  isDragging.value = false
  const file = e.dataTransfer?.files?.[0]
  if (file) {
    selectedFile.value = file
    errorMsg.value = ''
  }
}

const formatBytes = (bytes: number) => {
  if (bytes < 1024) return `${bytes} B`
  if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(1)} KB`
  return `${(bytes / (1024 * 1024)).toFixed(1)} MB`
}

const downloadTemplate = () => {
  const csv = [
    'Name,Phone,Email,Project,Status,Budget,Preferred Contact,Best time to call,Notes',
    '"Rahim Uddin","01712345678","rahim@example.com","Mirpur DOHS Residence","New","1.5 Cr","WhatsApp","Evening (5pm-8pm)","Looking for 3 bedroom apartment"'
  ].join('\n')
  const blob = new Blob(['\uFEFF' + csv], { type: 'text/csv;charset=utf-8;' })
  const url = URL.createObjectURL(blob)
  const a = document.createElement('a')
  a.href = url
  a.download = 'grambangla-leads-template.csv'
  a.click()
  URL.revokeObjectURL(url)
}

const resetForm = () => {
  selectedFile.value = null
  result.value = null
  errorMsg.value = ''
  if (fileInput.value) fileInput.value.value = ''
}

const submitImport = async () => {
  if (!selectedFile.value) {
    errorMsg.value = 'Please choose a CSV or Excel (.xlsx) file first.'
    return
  }

  importing.value = true
  errorMsg.value = ''

  try {
    const formData = new FormData()
    formData.append('file', selectedFile.value)
    formData.append('duplicate_mode', duplicateMode.value)

    if (propertyChoice.value && propertyChoice.value !== 'custom') {
      formData.append('property_id', propertyChoice.value)
      const chosen = propertyOptions.value.find(p => String(p.id) === propertyChoice.value)
      if (chosen?.title) formData.append('default_property', chosen.title)
    } else if (propertyChoice.value === 'custom' && customProperty.value) {
      formData.append('default_property', customProperty.value)
    }

    if (assignedTo.value) {
      formData.append('assigned_to', String(assignedTo.value))
    }

    const res = await crm.importLeads(formData)
    result.value = res
    emit('imported', {
      created: res.created,
      updated: res.updated,
      skipped: res.skipped,
      leads: res.leads || []
    })
  } catch (err: any) {
    errorMsg.value = err?.message || 'Failed to import leads from spreadsheet.'
  } finally {
    importing.value = false
  }
}
</script>

<style scoped>
.im-card {
  max-width: 560px;
  width: 100%;
}
.im-body {
  display: flex;
  flex-direction: column;
  gap: 14px;
  padding: 18px 20px;
}
.im-dropzone {
  border: 1.5px dashed rgba(255, 255, 255, 0.2);
  border-radius: 10px;
  padding: 22px 16px;
  text-align: center;
  cursor: pointer;
  background: rgba(255, 255, 255, 0.02);
  transition: border-color 0.15s ease, background 0.15s ease;
}
.im-dropzone:hover,
.im-dropzone.is-dragging {
  border-color: #10B981;
  background: rgba(16, 185, 129, 0.06);
}
.im-dropzone.has-file {
  border-style: solid;
  border-color: rgba(16, 185, 129, 0.5);
  background: rgba(16, 185, 129, 0.05);
}
.im-file-input {
  display: none;
}
.im-upload-icon {
  color: #10B981;
  margin: 0 auto 8px;
}
.im-drop-title {
  margin: 0;
  font-size: 0.92rem;
  color: #F3F4F6;
}
.im-drop-sub {
  margin: 4px 0 0;
  font-size: 0.78rem;
  color: #9CA3AF;
}
.im-file-badge {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 12px;
  text-align: left;
}
.im-file-meta {
  display: flex;
  flex-direction: column;
  gap: 2px;
}
.im-file-meta strong {
  color: #FFF;
  font-size: 0.92rem;
}
.im-file-meta span {
  color: #9CA3AF;
  font-size: 0.78rem;
}
.im-template-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 10px;
  flex-wrap: wrap;
  font-size: 0.78rem;
  color: #9CA3AF;
}
.im-template-btn {
  background: none;
  border: none;
  color: #10B981;
  font-size: 0.8rem;
  font-weight: 600;
  cursor: pointer;
  padding: 0;
  text-decoration: underline;
}
.im-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
  gap: 12px;
}
.im-field {
  display: flex;
  flex-direction: column;
  gap: 5px;
  font-size: 0.82rem;
  color: #D1D5DB;
}
.im-field em {
  color: #9CA3AF;
  font-style: normal;
}
.im-group {
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 8px;
  padding: 10px 12px 12px;
  margin: 0;
}
.im-group legend {
  font-size: 0.8rem;
  font-weight: 600;
  color: #D1D5DB;
  padding: 0 6px;
}
.im-chips {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  margin-top: 4px;
}
.im-chip {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 6px 11px;
  border-radius: 6px;
  border: 1px solid rgba(255, 255, 255, 0.14);
  background: rgba(255, 255, 255, 0.03);
  color: #E5E7EB;
  font-size: 0.8rem;
  cursor: pointer;
}
.im-chip input {
  margin: 0;
}
.im-chip.on {
  border-color: #10B981;
  background: rgba(16, 185, 129, 0.12);
  color: #FFF;
}
.im-chip em {
  color: #9CA3AF;
  font-style: normal;
  font-size: 0.74rem;
}
.im-err {
  margin: 0;
  padding: 9px 12px;
  border-radius: 6px;
  background: rgba(239, 68, 68, 0.12);
  border: 1px solid rgba(239, 68, 68, 0.35);
  color: #FCA5A5;
  font-size: 0.82rem;
}
.im-result-banner {
  background: rgba(16, 185, 129, 0.1);
  border: 1px solid rgba(16, 185, 129, 0.3);
  border-radius: 8px;
  padding: 12px 14px;
}
.im-result-banner h4 {
  margin: 0 0 4px;
  color: #34D399;
  font-size: 0.95rem;
}
.im-result-banner p {
  margin: 0;
  font-size: 0.82rem;
  color: #D1D5DB;
}
.im-stats-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 8px;
}
.im-stat {
  background: rgba(255, 255, 255, 0.03);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 8px;
  padding: 10px 8px;
  text-align: center;
  display: flex;
  flex-direction: column;
  gap: 2px;
}
.im-stat-val {
  font-size: 1.25rem;
  font-weight: 700;
  color: #FFF;
}
.im-stat--created .im-stat-val { color: #34D399; }
.im-stat--updated .im-stat-val { color: #60A5FA; }
.im-stat--skipped .im-stat-val { color: #FBBF24; }
.im-stat--failed .im-stat-val { color: #F87171; }
.im-stat-lbl {
  font-size: 0.72rem;
  color: #9CA3AF;
}
.im-errors-box {
  background: rgba(239, 68, 68, 0.06);
  border: 1px solid rgba(239, 68, 68, 0.25);
  border-radius: 8px;
  padding: 10px 12px;
  max-height: 160px;
  overflow-y: auto;
}
.im-errors-box h5 {
  margin: 0 0 6px;
  font-size: 0.8rem;
  color: #FCA5A5;
}
.im-error-list {
  margin: 0;
  padding-left: 18px;
  font-size: 0.78rem;
  color: #E5E7EB;
  display: flex;
  flex-direction: column;
  gap: 4px;
}
</style>


<template>
  <div class="admin-page animate-fade-in">
    <div class="admin-header-row">
      <div>
        <h1 class="page-title">Buyer Inquiries & Leads</h1>
        <p class="page-subtitle">Track client inquiries, WhatsApp requests, and scheduled consultations.</p>
      </div>
      <div class="admin-header-actions">
        <button class="btn btn-emerald" @click="showAddLeadModal = true">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <line x1="12" y1="5" x2="12" y2="19"/>
            <line x1="5" y1="12" x2="12" y2="12"/>
          </svg>
          <span>Log New Inquiry</span>
        </button>
      </div>
    </div>

    <!-- Filters Bar -->
    <div class="panel-card" style="padding:14px 20px; margin-bottom:20px;">
      <div class="flex justify-between items-center flex-wrap gap-3">
        <div class="flex items-center gap-2 flex-wrap">
          <button 
            v-for="cat in ['All', 'Ready within 3 months', 'Own / family use', 'Investment', 'Archived / Trash']"
            :key="cat"
            class="btn btn-sm"
            :class="selectedType === cat ? (cat === 'Archived / Trash' ? 'btn-red' : 'btn-gold') : 'btn-outline-white'"
            @click="changeTab(cat)"
          >
            <span v-if="cat === 'Archived / Trash'">🗑️ {{ cat }}</span>
            <span v-else>{{ cat }}</span>
          </button>
        </div>
        <div style="font-size:0.85rem; color:#8E9B8F; font-weight:600;">
          <span v-if="isLoading">Loading inquiries...</span>
          <span v-else>{{ filteredLeads.length }} {{ selectedType === 'Archived / Trash' ? 'Deleted inquiries in trash' : 'Inquiries · readiness is self-reported' }}</span>
        </div>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="isLoading" class="panel-card" style="text-align:center; padding:48px 24px; color:#8E9B8F;">
      <div style="display:inline-block; width:32px; height:32px; border:3px solid rgba(255,255,255,0.1); border-radius:50%; border-top-color:var(--admin-accent-gold, #D4AF37); animation:spin 1s linear infinite; margin-bottom:12px;"></div>
      <p style="font-size:0.95rem; font-weight:500;">Loading leads...</p>
    </div>

    <!-- Empty State -->
    <div v-else-if="filteredLeads.length === 0" class="panel-card" style="text-align:center; padding:54px 24px; color:#8E9B8F;">
      <svg width="44" height="44" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin: 0 auto 14px; opacity: 0.5;">
        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
        <circle cx="9" cy="7" r="4"></circle>
        <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
      </svg>
      <h3 style="font-size:1.05rem; font-weight:600; color:var(--admin-text-primary, #FFF); margin-bottom:6px;">
        {{ selectedType === 'Archived / Trash' ? 'Trash is empty' : 'No buyer inquiries found' }}
      </h3>
      <p style="font-size:0.88rem; max-width:440px; margin:0 auto 18px; line-height:1.5;">
        {{ selectedType === 'Archived / Trash' ? 'No soft-deleted leads found in the archive.' : 'There are currently no inquiries in this category.' }}
      </p>
      <button v-if="selectedType !== 'Archived / Trash'" class="btn btn-sm btn-emerald" @click="showAddLeadModal = true">
        <span>Log New Inquiry</span>
      </button>
    </div>

    <!-- Leads List -->
    <div v-else style="display:flex; flex-direction:column; gap:16px;">
      <div v-for="lead in filteredLeads" :key="lead.id" class="panel-card" style="position: relative;">
        <div class="flex justify-between items-start flex-wrap gap-4" style="margin-bottom:12px;">
          <div>
            <div class="flex items-center gap-2 flex-wrap" style="margin-bottom: 6px;">
              <strong style="font-size:1.18rem; color: var(--admin-text-primary);">{{ lead.name }}</strong>
              
              <!-- Buyer Category Badge -->
              <span class="badge badge-featured" style="font-size:0.75rem;">
                {{ lead.buyer_category || lead.type }}
              </span>

              <!-- Investment Readiness Filter Badge -->
              <span 
                v-if="lead.investment_readiness" 
                class="badge" 
                :style="lead.investment_readiness.includes('30 days') ? 'background:rgba(16,185,129,0.15); color:#10B981; border:1px solid rgba(16,185,129,0.3); font-weight:700;' : 'background:rgba(59,130,246,0.15); color:#60A5FA; border:1px solid rgba(59,130,246,0.3);'"
              >
                {{ lead.investment_readiness }}
              </span>

              <!-- Ad Source Attribution -->
              <span 
                v-if="lead.utm_source === 'facebook'" 
                class="badge" 
                style="background:rgba(24,119,242,0.15); color:#3B82F6; border:1px solid rgba(24,119,242,0.35); font-weight:700;"
                :title="lead.utm_campaign ? 'Campaign: ' + lead.utm_campaign : 'Facebook Paid Lead'"
              >
                📘 Facebook Ad {{ lead.utm_campaign ? '(' + lead.utm_campaign + ')' : '' }}
              </span>

              <span class="badge badge-status" style="font-size:0.75rem;">Captured {{ lead.date }}</span>

              <span v-if="lead.deleted_at" class="badge" style="background:rgba(239,68,68,0.15); color:#F87171; border:1px solid rgba(239,68,68,0.3); font-size:0.75rem; font-weight:700;">
                Soft Deleted
              </span>
            </div>

            <div style="font-size:0.88rem; margin-top:4px;" class="text-subtle">
              <p v-if="lead.budget_range" style="margin-bottom: 8px;">Budget fit: <strong>{{ lead.budget_range }}</strong></p>
              Target Property: <strong style="color:#10B981;">{{ lead.property }}</strong>
              <span v-if="lead.preferred_contact" style="margin-left: 10px; color: #8E9B8F; font-size: 0.8rem;">
                • Preferred: <strong style="color:#FFF;">{{ lead.preferred_contact }}</strong>
              </span>
            </div>
          </div>

          <!-- Actions Row -->
          <div class="flex items-center gap-2 flex-wrap">
            <!-- Active Leads Actions -->
            <template v-if="!lead.deleted_at">
              <!-- Lifecycle Pipeline Select -->
              <select :value="lead.stage" @change="updateStage(lead.id, $event)" class="status-inline-select">
                <option value="New">Status: New</option>
                <option value="Contacted">Status: Contacted</option>
                <option value="Qualified">Status: Qualified</option>
                <option value="Converted">Status: Converted</option>
              </select>

              <a 
                v-if="lead.phone"
                :href="`https://wa.me/${lead.phone.replace(/[^0-9]/g, '')}?text=Hello%20${encodeURIComponent(lead.name)},%20this%20is%20GBREL%20Advisory%20regarding%20your%20mandate%20inquiry%20for%20${encodeURIComponent(lead.property)}`" 
                target="_blank" 
                class="btn btn-sm btn-emerald" 
                style="background:#25D366; border-color:#25D366; font-weight:700;"
              >
                <span>💬 WhatsApp Dispatch</span>
              </a>
              <a v-if="lead.phone" :href="`tel:${lead.phone}`" class="btn btn-sm btn-outline-white">
                <span>📞 Call {{ lead.phone }}</span>
              </a>
              <NuxtLink v-if="lead.visitor_id" :to="`/admin/lead-journey/${lead.id}`" class="btn btn-sm btn-outline-white" title="Pages visited, ad source and survey steps">
                <span>🧭 Journey</span>
              </NuxtLink>

              <!-- Soft Delete Button -->
              <button 
                type="button"
                class="btn btn-sm" 
                style="background:rgba(239,68,68,0.12); color:#F87171; border:1px solid rgba(239,68,68,0.3); font-weight:600; padding:6px 12px; display:inline-flex; align-items:center; gap:5px;"
                title="Soft delete lead"
                @click="promptDeleteLead(lead)"
              >
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <polyline points="3 6 5 6 21 6"></polyline>
                  <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                  <line x1="10" y1="11" x2="10" y2="17"></line>
                  <line x1="14" y1="11" x2="14" y2="17"></line>
                </svg>
                <span>Delete</span>
              </button>
            </template>

            <!-- Trashed Leads Actions -->
            <template v-else>
              <button 
                type="button"
                class="btn btn-sm btn-emerald" 
                style="display:inline-flex; align-items:center; gap:5px;"
                title="Restore soft-deleted lead"
                @click="restoreLead(lead)"
              >
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/>
                  <path d="M3 3v5h5"/>
                </svg>
                <span>Restore Lead</span>
              </button>
            </template>
          </div>
        </div>

        <div style="background:var(--admin-bg-surface-alt); border:1px solid var(--admin-border-subtle); padding:14px 18px; border-radius:var(--radius-md); font-size:0.88rem; line-height:1.5; white-space:pre-line; overflow-wrap:anywhere;">
          "{{ lead.message }}"
        </div>
      </div>
    </div>

    <!-- ======================================================================
         MODAL: LOG NEW BUYER INQUIRY
         ====================================================================== -->
    <div v-if="showAddLeadModal" class="admin-modal-overlay" @click.self="showAddLeadModal = false">
      <div class="admin-modal-card animate-fade-in-up">
        <div class="admin-modal-header">
          <h3 class="admin-modal-title">Log Buyer Inquiry</h3>
          <button class="admin-modal-close" @click="showAddLeadModal = false">✕</button>
        </div>
        <form @submit.prevent="saveNewLead">
          <div class="admin-modal-body">
            <div class="grid grid-2" style="gap:14px; margin-bottom:14px;">
              <div class="form-group">
                <label class="form-label">Prospect Name *</label>
                <input v-model="leadForm.name" type="text" required placeholder="e.g. Dr. Salman Khan" class="form-input" />
              </div>
              <div class="form-group">
                <label class="form-label">Phone / WhatsApp *</label>
                <input v-model="leadForm.phone" type="tel" required placeholder="+880 1819-..." class="form-input" />
              </div>
            </div>

            <div class="grid grid-2" style="gap:14px; margin-bottom:14px;">
              <div class="form-group">
                <label class="form-label">Target Asset / Property</label>
                <input v-model="leadForm.property" type="text" placeholder="যেমন: গুলশান ১ এ ১৫ কাঠা জমি / লেক ভিউ" class="form-input" />
              </div>
              <div class="form-group">
                <label class="form-label">Buyer Category</label>
                <select v-model="leadForm.type" class="form-select">
                  <option value="NRB Investor">NRB Investor (Expatriate)</option>
                  <option value="Direct Buyer">Direct Buyer (End-User)</option>
                  <option value="Hospitality ROI">Hospitality ROI Investor</option>
                  <option value="Corporate / Institutional">Corporate / Institutional</option>
                </select>
              </div>
            </div>

            <div class="form-group">
              <label class="form-label">Inquiry Message & Due Diligence Notes</label>
              <textarea v-model="leadForm.message" rows="3" placeholder="Buyer requirements, budget constraints, timeline..." class="form-textarea"></textarea>
            </div>
          </div>

          <div class="admin-modal-footer">
            <button type="button" class="btn btn-sm btn-outline-white" @click="showAddLeadModal = false">Cancel</button>
            <button type="submit" class="btn btn-sm btn-emerald" :disabled="isSubmitting">
              {{ isSubmitting ? 'Saving...' : 'Save Lead' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- ======================================================================
         MODAL: CONFIRM SOFT DELETE LEAD
         ====================================================================== -->
    <div v-if="deleteLeadTarget" class="admin-modal-overlay" @click.self="deleteLeadTarget = null">
      <div class="admin-modal-card animate-fade-in-up" style="max-width:440px;">
        <div class="admin-modal-header">
          <h3 class="admin-modal-title" style="color: #F87171; display:flex; align-items:center; gap:8px;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <polyline points="3 6 5 6 21 6"></polyline>
              <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
            </svg>
            Delete Lead Inquiry
          </h3>
          <button class="admin-modal-close" @click="deleteLeadTarget = null">✕</button>
        </div>
        <div class="admin-modal-body" style="padding: 20px;">
          <p style="color: var(--admin-text-secondary); font-size: 0.92rem; line-height: 1.5; margin-bottom: 14px;">
            Are you sure you want to soft delete the inquiry from <strong style="color: #FFF;">{{ deleteLeadTarget.name }}</strong>?
          </p>
          <div style="background: var(--admin-bg-surface-alt); border: 1px solid var(--admin-border-subtle); padding: 12px 14px; border-radius: var(--radius-sm); font-size: 0.85rem; color: #8E9B8F;">
            <div>Phone: <strong style="color: #FFF;">{{ deleteLeadTarget.phone }}</strong></div>
            <div v-if="deleteLeadTarget.property" style="margin-top:4px;">Property: <strong style="color: #10B981;">{{ deleteLeadTarget.property }}</strong></div>
          </div>
          <p style="color: #9CA3AF; font-size: 0.8rem; margin-top: 12px; line-height: 1.4;">
            This will soft-delete the lead from active pipeline views while keeping it safely recoverable in the Archived/Trash tab.
          </p>
        </div>
        <div class="admin-modal-footer">
          <button type="button" class="btn btn-sm btn-outline-white" :disabled="isDeleting" @click="deleteLeadTarget = null">Cancel</button>
          <button 
            type="button" 
            class="btn btn-sm" 
            :disabled="isDeleting" 
            style="background:#EF4444; color:#FFF; display:inline-flex; align-items:center; gap:6px;" 
            @click="executeDeleteLead"
          >
            <span>{{ isDeleting ? 'Deleting...' : 'Confirm Soft Delete' }}</span>
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

definePageMeta({
  layout: 'admin'
})

const toast = useToast()
const { token } = useAuth()
const selectedType = ref('All')
const showAddLeadModal = ref(false)
const isLoading = ref(false)
const isSubmitting = ref(false)

const deleteLeadTarget = ref<any | null>(null)
const isDeleting = ref(false)

const leadsList = ref<any[]>([])

const fetchLeads = async (onlyTrashed = false) => {
  isLoading.value = true
  try {
    const url = onlyTrashed ? useApiUrl('/leads?only_trashed=1') : useApiUrl('/leads')
    const res = await fetch(url, { headers: { Authorization: `Bearer ${token.value}` } })
    if (!res.ok) throw new Error('Lead access failed')
    const json = await res.json()
    if (json && json.success && Array.isArray(json.data)) {
      leadsList.value = json.data.map(l => ({
        ...l,
        property: l.property_title || l.property || 'Direct Inquiry',
        type: l.buyer_category || l.buyer_type || l.lead_type || l.type || 'Direct Buyer',
        buyer_category: l.buyer_category || l.lead_type,
        investment_readiness: l.investment_readiness,
        preferred_contact: l.preferred_contact,
        utm_source: l.utm_source,
        utm_campaign: l.utm_campaign,
        stage: l.status || l.stage || 'New',
        date: l.created_at ? new Date(l.created_at).toLocaleDateString('en-GB', { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' }) : (l.date || 'Recent')
      }))
    } else {
      leadsList.value = []
    }
  } catch (err) {
    console.error('Failed to fetch leads:', err)
    toast.error('Could not load inquiries', 'Please check your session and lead access permissions.')
    leadsList.value = []
  } finally {
    isLoading.value = false
  }
}

const changeTab = async (cat: string) => {
  selectedType.value = cat
  if (cat === 'Archived / Trash') {
    await fetchLeads(true)
  } else {
    await fetchLeads(false)
  }
}

onMounted(async () => {
  await fetchLeads()
})

const filteredLeads = computed(() => {
  if (selectedType.value === 'Archived / Trash' || selectedType.value === 'All') return leadsList.value
  if (selectedType.value === 'Ready within 3 months') return leadsList.value.filter(l =>
    ['Within 30 days', '1–3 months'].includes(l.investment_readiness) && l.budget_range === 'Listed price fits budget')
  return leadsList.value.filter(l => (l.type || '').includes(selectedType.value))
})

const leadForm = reactive({
  name: '',
  phone: '',
  property: '',
  type: 'NRB Investor',
  message: ''
})

const saveNewLead = async () => {
  isSubmitting.value = true
  try {
    const res = await fetch(useApiUrl('/leads'), {
      method: 'POST',
      headers: { 
        'Content-Type': 'application/json',
        Authorization: `Bearer ${token.value}`
      },
      body: JSON.stringify({
        name: leadForm.name,
        phone: leadForm.phone,
        property_title: leadForm.property,
        buyer_type: leadForm.type,
        message: leadForm.message || 'Direct telephone inquiry recorded at GBREL HQ.',
        source: 'Admin CRM Manual'
      })
    })

    if (!res.ok) throw new Error('Failed to create lead in database')

    toast.success('Lead Registered', `Inquiry from ${leadForm.name} saved to CRM inbox.`)
    showAddLeadModal.value = false
    leadForm.name = ''
    leadForm.phone = ''
    leadForm.property = ''
    leadForm.message = ''
    await fetchLeads(selectedType.value === 'Archived / Trash')
  } catch (err: any) {
    toast.error('Save Failed', err?.message || 'Could not save buyer inquiry.')
  } finally {
    isSubmitting.value = false
  }
}

const updateStage = async (id: number, event: Event) => {
  const target = event.target as HTMLSelectElement
  const newStage = target.value
  const lead = leadsList.value.find(l => l.id === id)
  const prevStage = lead ? lead.stage : 'New'

  if (lead) lead.stage = newStage

  try {
    const res = await fetch(useApiUrl(`/leads/${id}/stage`), {
      method: 'PATCH',
      headers: { 'Content-Type': 'application/json', Authorization: `Bearer ${token.value}` },
      body: JSON.stringify({ stage: newStage })
    })
    if (!res.ok) throw new Error('Failed to update stage on server')
    toast.info('Pipeline Updated', `${lead?.name || 'Lead'} moved to stage "${newStage}".`)
  } catch (err: any) {
    if (lead) lead.stage = prevStage
    toast.error('Update Failed', err?.message || 'Could not update lead stage.')
  }
}

const promptDeleteLead = (lead: any) => {
  deleteLeadTarget.value = lead
}

const executeDeleteLead = async () => {
  if (!deleteLeadTarget.value) return
  const target = deleteLeadTarget.value
  isDeleting.value = true
  try {
    const res = await fetch(useApiUrl(`/leads/${target.id}`), {
      method: 'DELETE',
      headers: {
        Authorization: `Bearer ${token.value}`
      }
    })
    if (!res.ok) {
      const errData = await res.json().catch(() => ({}))
      throw new Error(errData?.message || 'Failed to delete lead from server')
    }

    leadsList.value = leadsList.value.filter(l => l.id !== target.id)
    toast.info('Lead Removed', `Lead inquiry from ${target.name} has been soft-deleted.`)
    deleteLeadTarget.value = null
  } catch (err: any) {
    console.error('Delete lead error:', err)
    toast.error('Deletion Failed', err?.message || 'Could not delete lead inquiry.')
  } finally {
    isDeleting.value = false
  }
}

const restoreLead = async (lead: any) => {
  try {
    const res = await fetch(useApiUrl(`/leads/${lead.id}/restore`), {
      method: 'POST',
      headers: {
        Authorization: `Bearer ${token.value}`
      }
    })
    if (!res.ok) {
      const errData = await res.json().catch(() => ({}))
      throw new Error(errData?.message || 'Failed to restore lead')
    }

    leadsList.value = leadsList.value.filter(l => l.id !== lead.id)
    toast.success('Lead Restored', `Lead inquiry from ${lead.name} has been restored to active pipeline.`)
  } catch (err: any) {
    console.error('Restore lead error:', err)
    toast.error('Restore Failed', err?.message || 'Could not restore lead inquiry.')
  }
}
</script>

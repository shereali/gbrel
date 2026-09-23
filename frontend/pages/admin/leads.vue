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
            <line x1="5" y1="12" x2="19" y2="12"/>
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
            v-for="cat in ['All', 'NRB Investor', 'Direct Buyer', 'Hospitality ROI']" 
            :key="cat"
            class="btn btn-sm"
            :class="selectedType === cat ? 'btn-gold' : 'btn-outline-white'"
            @click="selectedType = cat"
          >
            {{ cat }}
          </button>
        </div>
        <div style="font-size:0.85rem; color:#94A3B8; font-weight:600;">
          {{ filteredLeads.length }} Qualified Leads
        </div>
      </div>
    </div>

    <!-- Leads List -->
    <div style="display:flex; flex-direction:column; gap:16px;">
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
                {{ lead.investment_readiness.includes('30 days') ? '⚡ 100% Cash Ready (30 Days)' : lead.investment_readiness }}
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
            </div>

            <div style="font-size:0.88rem; margin-top:4px;" class="text-subtle">
              Target Property: <strong style="color:#10B981;">{{ lead.property }}</strong>
              <span v-if="lead.preferred_contact" style="margin-left: 10px; color: #94A3B8; font-size: 0.8rem;">
                • Preferred: <strong style="color:#FFF;">{{ lead.preferred_contact }}</strong>
              </span>
            </div>
          </div>

          <div class="flex items-center gap-2 flex-wrap">
            <!-- Lifecycle Pipeline Select -->
            <select :value="lead.stage" @change="updateStage(lead.id, $event)" class="status-inline-select">
              <option value="New">Status: New</option>
              <option value="Contacted">Status: Contacted</option>
              <option value="Qualified">Status: Qualified</option>
              <option value="Converted">Status: Converted</option>
            </select>

            <a :href="`https://wa.me/${lead.phone.replace(/[^0-9]/g, '')}?text=Hello%20${encodeURIComponent(lead.name)},%20this%20is%20GBREL%20Advisory%20regarding%20your%20mandate%20inquiry%20for%20${encodeURIComponent(lead.property)}`" target="_blank" class="btn btn-sm btn-emerald" style="background:#25D366; border-color:#25D366; font-weight:700;">
              <span>💬 WhatsApp Dispatch</span>
            </a>
            <a :href="`tel:${lead.phone}`" class="btn btn-sm btn-outline-white">
              <span>📞 Call {{ lead.phone }}</span>
            </a>
          </div>
        </div>

        <div style="background:var(--admin-bg-surface-alt); border:1px solid var(--admin-border-subtle); padding:14px 18px; border-radius:var(--radius-md); font-size:0.88rem; line-height:1.5;">
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
                <input v-model="leadForm.property" type="text" placeholder="e.g. Gulshan Penthouse / Purbachal Plot" class="form-input" />
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
            <button type="submit" class="btn btn-sm btn-emerald">Save Lead</button>
          </div>
        </form>
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
const selectedType = ref('All')
const showAddLeadModal = ref(false)
const isLoading = ref(false)
const isSubmitting = ref(false)

const leadsList = ref<any[]>([])

const fetchLeads = async () => {
  isLoading.value = true
  try {
    const res = await fetch(useApiUrl('/leads'))
    if (res.ok) {
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
      }
    }
  } catch (err) {
    console.error('Failed to fetch leads:', err)
  } finally {
    isLoading.value = false
  }
}

onMounted(async () => {
  await fetchLeads()
})

const filteredLeads = computed(() => {
  if (selectedType.value === 'All') return leadsList.value
  return leadsList.value.filter(l => (l.type || '').includes(selectedType.value))
})

const leadForm = reactive({
  name: '',
  phone: '',
  property: '10 Katha Corner Plot in Purbachal',
  type: 'NRB Investor',
  message: ''
})

const saveNewLead = async () => {
  isSubmitting.value = true
  try {
    const res = await fetch(useApiUrl('/leads'), {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
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
    leadForm.message = ''
    await fetchLeads()
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
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ stage: newStage })
    })
    if (!res.ok) throw new Error('Failed to update stage on server')
    toast.info('Pipeline Updated', `${lead?.name || 'Lead'} moved to stage "${newStage}".`)
  } catch (err: any) {
    if (lead) lead.stage = prevStage
    toast.error('Update Failed', err?.message || 'Could not update lead stage.')
  }
}
</script>

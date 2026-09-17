<template>
  <div class="admin-page animate-fade-in">
    <div class="admin-header-row">
      <div>
        <h1 class="page-title">Lead Hunter CRM & Dispatch Inbox</h1>
        <p class="page-subtitle">High-intent buyer leads captured from WhatsApp pulse, instant callback modals, and direct property mandate inquiries.</p>
      </div>
      <div class="admin-header-actions">
        <button class="btn btn-emerald" @click="showAddLeadModal = true">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <line x1="12" y1="5" x2="12" y2="19"/>
            <line x1="5" y1="12" x2="19" y2="12"/>
          </svg>
          <span>+ Log Direct Buyer Inquiry</span>
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
      <div v-for="lead in filteredLeads" :key="lead.id" class="panel-card">
        <div class="flex justify-between items-start flex-wrap gap-4" style="margin-bottom:12px;">
          <div>
            <div class="flex items-center gap-2 flex-wrap">
              <strong style="font-size:1.18rem;">{{ lead.name }}</strong>
              <span class="badge badge-featured" style="font-size:0.75rem;">{{ lead.type }}</span>
              <span class="badge badge-status" style="font-size:0.75rem;">Captured {{ lead.date }}</span>
            </div>
            <div style="font-size:0.88rem; margin-top:4px;" class="text-subtle">
              Target Property: <strong style="color:#10B981;">{{ lead.property }}</strong>
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

            <a :href="`https://wa.me/${lead.phone.replace(/[^0-9]/g, '')}`" target="_blank" class="btn btn-sm btn-emerald" style="background:#25D366; border-color:#25D366;">
              WhatsApp Dispatch
            </a>
            <a :href="`tel:${lead.phone}`" class="btn btn-sm btn-outline-white">
              Call {{ lead.phone }}
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
                  <option value="Corporate Mandate">Corporate / Institutional</option>
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
import { ref, reactive, computed } from 'vue'
import { useToast } from '~/composables/useToast'

definePageMeta({
  layout: 'admin'
})

const toast = useToast()
const selectedType = ref('All')
const showAddLeadModal = ref(false)

const leadsList = ref([
  { id: 1, name: 'Dr. Farhan Chowdhury', phone: '+44 7911 123456', property: 'Lakeview Penthouse at Gulshan-2', type: 'NRB Investor', stage: 'Qualified', message: 'Interested in title verification deeds and bank escrow transfer options for expatriates.', date: 'Today 09:15 AM' },
  { id: 2, name: 'Engr. Mahfuzur Rahman', phone: '+880 1711-998877', property: '10 Katha Plot in Purbachal Sector 17', type: 'Direct Buyer', stage: 'Contacted', message: 'Want to inspect boundary demarcation pillars this Friday afternoon with architect.', date: 'Yesterday' },
  { id: 3, name: 'Syed Tanzeem', phone: '+971 50 1234567', property: 'Marine Drive Cox\'s Bazar Sea Suite', type: 'Hospitality ROI', stage: 'New', message: 'Interested in buying 2 fractional suite units with 14% annual guaranteed yield.', date: '2 days ago' },
  { id: 4, name: 'Advocate Munirul Islam', phone: '+880 1819-556677', property: 'South-Facing Duplex in Dhanmondi 8/A', type: 'Direct Buyer', stage: 'Converted', message: 'Signed Bayna agreement. Sub-registry clearance scheduled for next week.', date: '3 days ago' }
])

const filteredLeads = computed(() => {
  if (selectedType.value === 'All') return leadsList.value
  return leadsList.value.filter(l => l.type.includes(selectedType.value))
})

const leadForm = reactive({
  name: '',
  phone: '',
  property: '10 Katha Corner Plot in Purbachal',
  type: 'NRB Investor',
  message: ''
})

const saveNewLead = () => {
  leadsList.value.unshift({
    id: Date.now(),
    name: leadForm.name,
    phone: leadForm.phone,
    property: leadForm.property,
    type: leadForm.type,
    stage: 'New',
    message: leadForm.message || 'Direct telephone inquiry recorded at GBREL HQ.',
    date: 'Just now'
  })
  toast.success('Lead Registered', `Inquiry from ${leadForm.name} saved to CRM inbox.`)
  showAddLeadModal.value = false
  leadForm.name = ''
  leadForm.phone = ''
  leadForm.message = ''
}

const updateStage = (id: number, event: Event) => {
  const target = event.target as HTMLSelectElement
  const lead = leadsList.value.find(l => l.id === id)
  if (lead) {
    lead.stage = target.value
    toast.info('Pipeline Updated', `${lead.name} moved to stage "${lead.stage}".`)
  }
}
</script>

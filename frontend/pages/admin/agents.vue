<template>
  <div class="admin-page animate-fade-in">
    <div class="admin-header-row">
      <div>
        <h1 class="page-title">Senior Advisors & Regional Broker Network</h1>
        <p class="page-subtitle">Manage licensed regional brokers, division assignments, commission allocations, and client portfolios across Bangladesh.</p>
      </div>
      <div class="admin-header-actions">
        <button class="btn btn-emerald" @click="openAddAgentModal">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <line x1="12" y1="5" x2="12" y2="19"/>
            <line x1="5" y1="12" x2="19" y2="12"/>
          </svg>
          <span>+ Add Licensed Advisor</span>
        </button>
      </div>
    </div>

    <div class="grid grid-3">
      <div v-for="agent in agentsList" :key="agent.id" class="panel-card" style="display:flex; flex-direction:column;">
        <div class="flex items-center gap-3" style="margin-bottom:16px;">
          <img :src="agent.photo" :alt="agent.name" style="width:62px; height:62px; border-radius:50%; object-fit:cover; border:2px solid var(--color-gold);" />
          <div>
            <span class="badge-admin active" style="font-size:0.7rem; margin-bottom:4px;">{{ agent.state }}</span>
            <h3 style="font-size:1.15rem; font-weight:800; line-height:1.2;">{{ agent.name }}</h3>
            <div style="font-size:0.8rem; color:var(--color-gold);">{{ agent.title }}</div>
          </div>
        </div>

        <p style="font-size:0.85rem; line-height:1.5; margin-bottom:16px; flex:1;" class="text-subtle">
          {{ agent.bio }}
        </p>

        <div style="background:var(--admin-bg-surface-alt); border:1px solid var(--admin-border-subtle); padding:12px 14px; border-radius:8px; margin-bottom:16px; font-size:0.82rem; display:flex; justify-content:space-between;">
          <div>Exp: <strong>{{ agent.experienceYears }}+ Yrs</strong></div>
          <div>Rating: <strong style="color:#F59E0B;">★ {{ agent.rating }}</strong></div>
          <div>Mandates: <strong style="color:#10B981;">{{ agent.activeListingsCount }}</strong></div>
        </div>

        <div class="flex gap-2">
          <button class="btn btn-sm btn-outline-white flex-1" @click="openEditAgentModal(agent)">Edit Profile</button>
          <button class="btn btn-sm btn-outline-white" style="color:#EF4444; border-color:rgba(239,68,68,0.4);" @click="promptDeleteAgent(agent)">Delete</button>
        </div>
      </div>
    </div>

    <!-- ======================================================================
         MODAL 1: ADD / EDIT ADVISOR
         ====================================================================== -->
    <div v-if="showAgentModal" ref="agentModalRoot" class="admin-modal-overlay" @click.self="closeAgentModal">
      <div class="admin-modal-card animate-fade-in-up">
        <div class="admin-modal-header">
          <h3 class="admin-modal-title">
            {{ editingAgentId ? 'Edit Advisor Profile' : 'Add Senior Real Estate Advisor' }}
          </h3>
          <button class="admin-modal-close" @click="closeAgentModal">✕</button>
        </div>

        <form @submit.prevent="saveAgent">
          <div class="admin-modal-body">
            <div class="form-group" style="margin-bottom:12px;">
              <label class="form-label">Full Name *</label>
              <input v-model="agentForm.name" type="text" required class="form-input" />
            </div>

            <div class="grid grid-2" style="gap:12px; margin-bottom:12px;">
              <div class="form-group">
                <label class="form-label">Title / Position *</label>
                <input v-model="agentForm.title" type="text" required class="form-input" />
              </div>
              <div class="form-group">
                <label class="form-label">Division Assignment</label>
                <select v-model="agentForm.state" class="form-select">
                  <option value="Dhaka North">Dhaka North</option>
                  <option value="Dhaka South">Dhaka South</option>
                  <option value="Chittagong">Chittagong & Cox's Bazar</option>
                  <option value="Sylhet">Sylhet</option>
                </select>
              </div>
            </div>

            <div class="grid grid-2" style="gap:12px; margin-bottom:12px;">
              <div class="form-group">
                <label class="form-label">Phone Number *</label>
                <input v-model="agentForm.phone" type="tel" required class="form-input" />
              </div>
              <div class="form-group">
                <label class="form-label">WhatsApp Number *</label>
                <input v-model="agentForm.whatsapp" type="tel" required class="form-input" />
              </div>
            </div>

            <div class="form-group" style="margin-bottom:12px;">
              <label class="form-label">Avatar Photo URL</label>
              <input v-model="agentForm.photo" type="url" class="form-input" />
            </div>

            <div class="form-group">
              <label class="form-label">Specialties & Bio Summary</label>
              <textarea v-model="agentForm.bio" rows="3" class="form-textarea"></textarea>
            </div>
          </div>

          <div class="admin-modal-footer">
            <button type="button" class="btn btn-sm btn-outline-white" @click="closeAgentModal">Cancel</button>
            <button type="submit" class="btn btn-sm btn-emerald">Save Advisor Record</button>
          </div>
        </form>
      </div>
    </div>

    <!-- ======================================================================
         MODAL 2: DELETE ADVISOR CONFIRMATION
         ====================================================================== -->
    <div v-if="deleteAgentTarget" class="admin-modal-overlay" @click.self="deleteAgentTarget = null">
      <div class="admin-modal-card animate-fade-in-up" style="max-width:440px;">
        <div class="admin-modal-header" style="background:#1E1622; border-bottom:1px solid rgba(239,68,68,0.2);">
          <h3 class="admin-modal-title" style="color:#F87171;">Confirm Advisor Removal</h3>
          <button class="admin-modal-close" @click="deleteAgentTarget = null">✕</button>
        </div>
        <div class="admin-modal-body">
          <p style="color:#E2E8F0; font-size:0.92rem; line-height:1.5;">
            Are you sure you want to remove advisor <strong>"{{ deleteAgentTarget.name }}"</strong>?
          </p>
          <p style="color:#94A3B8; font-size:0.82rem; margin-top:8px;">
            Their assigned mandates will be reassigned to the Dhaka HQ Broker Pool.
          </p>
        </div>
        <div class="admin-modal-footer">
          <button class="btn btn-sm btn-outline-white" @click="deleteAgentTarget = null">Cancel</button>
          <button class="btn btn-sm" style="background:#EF4444; color:#FFF;" @click="executeDeleteAgent">
            Remove Advisor
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive } from 'vue'
import { useProperties, type AgentItem } from '~/composables/useProperties'
import { useOverlayBehavior } from '~/composables/useOverlayBehavior'
import { useToast } from '~/composables/useToast'

definePageMeta({
  layout: 'admin'
})

const { agents } = useProperties()
const toast = useToast()

const agentsList = ref<AgentItem[]>([...agents.value])

const showAgentModal = ref(false)
const editingAgentId = ref<number | null>(null)
const agentModalRoot = ref<HTMLElement | null>(null)
const deleteAgentTarget = ref<AgentItem | null>(null)

const closeAgentModal = () => {
  showAgentModal.value = false
  editingAgentId.value = null
}

useOverlayBehavior(showAgentModal, closeAgentModal, agentModalRoot)

const agentForm = reactive({
  name: '',
  title: '',
  state: 'Dhaka North',
  phone: '',
  whatsapp: '',
  bio: '',
  photo: 'https://images.unsplash.com/photo-1560250097-0b93528c311a?q=80&w=600&auto=format&fit=crop'
})

const openAddAgentModal = () => {
  editingAgentId.value = null
  agentForm.name = ''
  agentForm.title = 'Senior Real Estate Advisor'
  agentForm.state = 'Dhaka North'
  agentForm.phone = '+880 1819-000000'
  agentForm.whatsapp = '+880 1819-000000'
  agentForm.bio = 'Specialist in luxury residential penthouses, diplomatic enclaves, and institutional land acquisitions.'
  agentForm.photo = 'https://images.unsplash.com/photo-1560250097-0b93528c311a?q=80&w=600&auto=format&fit=crop'
  showAgentModal.value = true
}

const openEditAgentModal = (agent: AgentItem) => {
  editingAgentId.value = agent.id
  agentForm.name = agent.name
  agentForm.title = agent.title
  agentForm.state = agent.state
  agentForm.phone = agent.phone
  agentForm.whatsapp = agent.whatsapp
  agentForm.bio = agent.bio
  agentForm.photo = agent.photo
  showAgentModal.value = true
}

const saveAgent = () => {
  if (editingAgentId.value) {
    const existing = agentsList.value.find(a => a.id === editingAgentId.value)
    if (existing) {
      existing.name = agentForm.name
      existing.title = agentForm.title
      existing.state = agentForm.state
      existing.phone = agentForm.phone
      existing.whatsapp = agentForm.whatsapp
      existing.bio = agentForm.bio
      existing.photo = agentForm.photo
      toast.success('Advisor Updated', `Profile for "${existing.name}" updated.`)
    }
  } else {
    const newId = Date.now()
    agentsList.value.push({
      id: newId,
      name: agentForm.name,
      title: agentForm.title,
      agency: 'GBREL Premier Advisory',
      state: agentForm.state,
      city: 'Dhaka',
      photo: agentForm.photo,
      email: `${agentForm.name.toLowerCase().replace(/[^a-z0-9]/g, '')}@gbrel.com`,
      phone: agentForm.phone,
      whatsapp: agentForm.whatsapp,
      bio: agentForm.bio,
      experienceYears: 8,
      rating: 4.9,
      reviewCount: 35,
      activeListingsCount: 6,
      specialties: ['Luxury Penthouses', 'Commercial Land']
    })
    toast.success('Advisor Added', `Licensed advisor "${agentForm.name}" registered.`)
  }
  closeAgentModal()
}

const promptDeleteAgent = (agent: AgentItem) => {
  deleteAgentTarget.value = agent
}

const executeDeleteAgent = () => {
  if (deleteAgentTarget.value) {
    const name = deleteAgentTarget.value.name
    const index = agentsList.value.findIndex(a => a.id === deleteAgentTarget.value!.id)
    if (index > -1) {
      agentsList.value.splice(index, 1)
    }
    toast.info('Advisor Removed', `Profile for ${name} removed.`)
    deleteAgentTarget.value = null
  }
}
</script>

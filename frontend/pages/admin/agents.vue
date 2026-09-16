<template>
  <div class="admin-page animate-fade-in">
    <div class="flex justify-between items-center flex-wrap gap-4" style="margin-bottom: 24px;">
      <div>
        <h1 class="page-title">Senior Advisors & Broker Network</h1>
        <p class="page-subtitle">Manage licensed regional brokers, commission rates, and active property portfolios.</p>
      </div>
      <button class="btn btn-emerald" @click="openAddAgentModal">+ Add New Advisor</button>
    </div>

    <div class="grid grid-3">
      <div v-for="agent in agentsList" :key="agent.id" class="panel-card" style="display:flex; flex-direction:column;">
        <div class="flex items-center gap-3" style="margin-bottom:16px;">
          <img :src="agent.photo" style="width:60px; height:60px; border-radius:50%; object-fit:cover; border:2px solid var(--color-gold);" />
          <div>
            <span class="badge badge-rajuk" style="margin-bottom:4px;">{{ agent.state }}</span>
            <h3 style="font-size:1.15rem; font-weight:800; color:#FFF; line-height:1.2;">{{ agent.name }}</h3>
            <div style="font-size:0.8rem; color:#D4AF37;">{{ agent.title }}</div>
          </div>
        </div>

        <p style="font-size:0.85rem; color:#CBD5E1; line-height:1.5; margin-bottom:16px; flex:1;">
          {{ agent.bio }}
        </p>

        <div style="background:rgba(255,255,255,0.03); padding:12px; border-radius:8px; margin-bottom:16px; font-size:0.82rem; display:flex; justify-content:space-between;">
          <div>Experience: <strong>{{ agent.experienceYears }}+ Yrs</strong></div>
          <div>Rating: <strong style="color:#F59E0B;">★ {{ agent.rating }}</strong></div>
          <div>Mandates: <strong style="color:#10B981;">{{ agent.activeListingsCount }}</strong></div>
        </div>

        <div class="flex gap-2">
          <button class="btn btn-sm btn-outline-white flex-1" @click="openEditAgentModal(agent)">Edit</button>
          <button class="btn btn-sm btn-outline" style="color:#EF4444; border-color:rgba(239,68,68,0.4);" @click="deleteAgent(agent.id)">Delete</button>
        </div>
      </div>
    </div>

    <!-- Modal: Add / Edit Agent -->
    <div v-if="showAgentModal" ref="agentModalRoot" class="modal-overlay" @click.self="closeAgentModal">
      <div class="modal-card animate-fade-in-up" style="background:#0F172A; color:#FFF; border:1px solid rgba(255,255,255,0.1); max-width:560px;">
        <button class="modal-close-btn" @click="closeAgentModal" style="background:#1E293B; color:#FFF;" aria-label="Close advisor form">✕</button>

        <h3 style="font-size:1.5rem; font-weight:800; color:#FFF; margin-bottom:18px;">
          {{ editingAgentId ? 'Edit Advisor Profile' : 'Add New Senior Real Estate Advisor' }}
        </h3>

        <form @submit.prevent="saveAgent">
          <div class="form-group" style="margin-bottom:12px;">
            <label class="form-label" style="color:#CBD5E1;">Full Name</label>
            <input v-model="agentForm.name" type="text" required class="form-input" style="background:#1E293B; color:#FFF; border-color:rgba(255,255,255,0.15);" />
          </div>
          <div class="grid grid-2" style="gap:12px; margin-bottom:12px;">
            <div class="form-group">
              <label class="form-label" style="color:#CBD5E1;">Title / Position</label>
              <input v-model="agentForm.title" type="text" required class="form-input" style="background:#1E293B; color:#FFF; border-color:rgba(255,255,255,0.15);" />
            </div>
            <div class="form-group">
              <label class="form-label" style="color:#CBD5E1;">Division Assignment</label>
              <select v-model="agentForm.state" class="form-select" style="background:#1E293B; color:#FFF; border-color:rgba(255,255,255,0.15);">
                <option value="Dhaka North">Dhaka North</option>
                <option value="Dhaka South">Dhaka South</option>
                <option value="Chittagong">Chittagong</option>
                <option value="Sylhet">Sylhet</option>
              </select>
            </div>
          </div>
          <div class="grid grid-2" style="gap:12px; margin-bottom:12px;">
            <div class="form-group">
              <label class="form-label" style="color:#CBD5E1;">Phone Number</label>
              <input v-model="agentForm.phone" type="tel" required class="form-input" style="background:#1E293B; color:#FFF; border-color:rgba(255,255,255,0.15);" />
            </div>
            <div class="form-group">
              <label class="form-label" style="color:#CBD5E1;">WhatsApp Number</label>
              <input v-model="agentForm.whatsapp" type="tel" required class="form-input" style="background:#1E293B; color:#FFF; border-color:rgba(255,255,255,0.15);" />
            </div>
          </div>
          <div class="form-group" style="margin-bottom:20px;">
            <label class="form-label" style="color:#CBD5E1;">Bio & Specialty Summary</label>
            <textarea v-model="agentForm.bio" rows="3" class="form-textarea" style="background:#1E293B; color:#FFF; border-color:rgba(255,255,255,0.15);"></textarea>
          </div>

          <button type="submit" class="btn btn-emerald btn-lg" style="width:100%;">
            <span>Save Advisor Record</span>
          </button>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive } from 'vue'
import { useProperties, type AgentItem } from '~/composables/useProperties'
import { useOverlayBehavior } from '~/composables/useOverlayBehavior'

definePageMeta({
  layout: 'admin'
})

const { agents } = useProperties()
const agentsList = ref<AgentItem[]>([...agents.value])

const showAgentModal = ref(false)
const editingAgentId = ref<number | null>(null)
const agentModalRoot = ref<HTMLElement | null>(null)

const closeAgentModal = () => {
  showAgentModal.value = false
}

useOverlayBehavior(showAgentModal, closeAgentModal, agentModalRoot)

const agentForm = reactive({
  name: '',
  title: 'Senior Luxury Advisor',
  state: 'Dhaka North',
  phone: '+880 1819-000000',
  whatsapp: '+8801819000000',
  bio: 'Specialist in high-value real estate assets.'
})

const openAddAgentModal = () => {
  editingAgentId.value = null
  agentForm.name = ''
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
  showAgentModal.value = true
}

const saveAgent = () => {
  if (editingAgentId.value) {
    const a = agentsList.value.find(ag => ag.id === editingAgentId.value)
    if (a) {
      a.name = agentForm.name
      a.title = agentForm.title
      a.state = agentForm.state
      a.phone = agentForm.phone
      a.whatsapp = agentForm.whatsapp
      a.bio = agentForm.bio
    }
  } else {
    agentsList.value.push({
      id: Date.now(),
      name: agentForm.name,
      title: agentForm.title,
      agency: 'GBREL Premier Advisory',
      state: agentForm.state,
      city: 'Dhaka',
      photo: 'https://images.unsplash.com/photo-1560250097-0b93528c311a?q=80&w=200&auto=format&fit=crop',
      email: `${agentForm.name.toLowerCase().replace(/\s+/g, '')}@gbrel.com`,
      phone: agentForm.phone,
      whatsapp: agentForm.whatsapp,
      bio: agentForm.bio,
      experienceYears: 8,
      rating: 4.9,
      reviewCount: 32,
      activeListingsCount: 6,
      specialties: ['Residential Plots', 'Luxury Penthouses']
    })
  }
  closeAgentModal()
}

const deleteAgent = (id: number) => {
  if (confirm('Delete advisor record from registry?')) {
    agentsList.value = agentsList.value.filter(a => a.id !== id)
  }
}
</script>

<style scoped>
.page-title {
  font-size: 1.85rem;
  font-weight: 800;
  color: #FFFFFF;
  line-height: 1.2;
}

.page-subtitle {
  color: #CBD5E1;
  font-size: 0.95rem;
  margin-top: 4px;
}

.panel-card {
  background: #0F172A;
  border: 1px solid rgba(255, 255, 255, 0.06);
  border-radius: var(--radius-xl);
  padding: 24px;
}
</style>

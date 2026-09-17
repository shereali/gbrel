<template>
  <div class="admin-page animate-fade-in">
    <div class="admin-header-row">
      <div>
        <h1 class="page-title">System & Banking Partner Configuration</h1>
        <p class="page-subtitle">Configure Bangladeshi currency formatting rules, partner bank home loan interest rates, and WhatsApp notification numbers.</p>
      </div>
      <div class="admin-header-actions">
        <button class="btn btn-emerald" @click="saveSettings">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <polyline points="20 6 9 17 4 12"/>
          </svg>
          <span>Save System Configurations</span>
        </button>
      </div>
    </div>

    <div class="grid grid-2" style="gap:24px; max-width:1100px;">
      <!-- Panel 1: Bank Home Loan & EMI Engine -->
      <div class="panel-card">
        <div class="panel-header">
          <div>
            <h3 class="panel-title">Mortgage & Banking Rates (EMI Engine)</h3>
            <p class="panel-sub">Real-time interest benchmarks used across property mortgage calculators</p>
          </div>
        </div>

        <div class="form-group" style="margin-bottom:16px;">
          <label class="form-label">DBH Finance Benchmark Rate (%)</label>
          <input v-model="settings.dbhRate" type="text" class="form-input" />
        </div>

        <div class="form-group" style="margin-bottom:16px;">
          <label class="form-label">IDLC Finance Benchmark Rate (%)</label>
          <input v-model="settings.idlcRate" type="text" class="form-input" />
        </div>

        <div class="form-group">
          <label class="form-label">BRAC Bank Escrow Benchmark (%)</label>
          <input v-model="settings.bracRate" type="text" class="form-input" />
        </div>
      </div>

      <!-- Panel 2: WhatsApp & Chauffeur Logistics -->
      <div class="panel-card">
        <div class="panel-header">
          <div>
            <h3 class="panel-title">VIP Logistics & Messaging Dispatch</h3>
            <p class="panel-sub">Configure instant lead routing numbers and vehicle pickup hubs</p>
          </div>
        </div>

        <div class="form-group" style="margin-bottom:16px;">
          <label class="form-label">Primary WhatsApp Dispatch Number</label>
          <input v-model="settings.whatsappNumber" type="text" class="form-input" />
        </div>

        <div class="form-group" style="margin-bottom:16px;">
          <label class="form-label">VIP Chauffeur Pickup Base</label>
          <input v-model="settings.chauffeurBase" type="text" class="form-input" />
        </div>

        <div class="form-group">
          <label class="form-label">Escrow Platform Advisory Commission (%)</label>
          <input v-model="settings.commissionRate" type="text" class="form-input" />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { reactive, onMounted } from 'vue'
import { useToast } from '~/composables/useToast'

definePageMeta({
  layout: 'admin'
})

const toast = useToast()

const settings = reactive({
  dbhRate: '9.25%',
  idlcRate: '9.50%',
  bracRate: '9.75%',
  whatsappNumber: '+880 1819-987654',
  chauffeurBase: 'Gulshan-2 Diplomatic Enclave, Dhaka',
  commissionRate: '2.0%'
})

onMounted(() => {
  try {
    const saved = localStorage.getItem('gbrel_admin_settings')
    if (saved) {
      Object.assign(settings, JSON.parse(saved))
    }
  } catch {
    //
  }
})

const saveSettings = () => {
  try {
    localStorage.setItem('gbrel_admin_settings', JSON.stringify(settings))
    toast.success('Configuration Saved', 'Banking rates and WhatsApp dispatch settings updated successfully.')
  } catch {
    toast.error('Save Failed', 'Unable to persist settings to storage.')
  }
}
</script>

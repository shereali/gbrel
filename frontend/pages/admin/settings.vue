<template>
  <div class="admin-page animate-fade-in">
    <div class="admin-header-row">
      <div>
        <h1 class="page-title">System, Banking & Brand Settings</h1>
        <p class="page-subtitle">Configure real-time interest benchmarks, WhatsApp hotlines, office location, and platform commission rates stored directly in MySQL.</p>
      </div>
      <div class="admin-header-actions">
        <button class="btn btn-emerald" :disabled="isSaving" @click="handleSaveSettings">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <polyline points="20 6 9 17 4 12"/>
          </svg>
          <span v-if="isSaving">Saving to Database...</span>
          <span v-else>Save System Configurations</span>
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
          <input v-model="form.dbhRate" type="text" placeholder="e.g. 9.25%" class="form-input" />
        </div>

        <div class="form-group" style="margin-bottom:16px;">
          <label class="form-label">IDLC Finance Benchmark Rate (%)</label>
          <input v-model="form.idlcRate" type="text" placeholder="e.g. 9.50%" class="form-input" />
        </div>

        <div class="form-group" style="margin-bottom:16px;">
          <label class="form-label">BRAC Bank Escrow Benchmark (%)</label>
          <input v-model="form.bracRate" type="text" placeholder="e.g. 9.75%" class="form-input" />
        </div>

        <div class="form-group">
          <label class="form-label">Default EMI Interest Rate (%)</label>
          <input v-model.number="form.bank_financing_rate" type="number" step="0.1" class="form-input" />
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
          <input v-model="form.whatsapp_number" type="text" placeholder="+880 1819-987654" class="form-input" />
        </div>

        <div class="form-group" style="margin-bottom:16px;">
          <label class="form-label">Emergency VIP Hotline</label>
          <input v-model="form.emergency_hotline" type="text" placeholder="+880 1911 222333" class="form-input" />
        </div>

        <div class="form-group" style="margin-bottom:16px;">
          <label class="form-label">VIP Chauffeur Pickup Base / Office</label>
          <input v-model="form.office_address" type="text" placeholder="Plot 12, Road 4, Gulshan-1, Dhaka" class="form-input" />
        </div>

        <div class="form-group">
          <label class="form-label">Escrow Platform Advisory Commission (%)</label>
          <input v-model.number="form.service_fee_pct" type="number" step="0.1" class="form-input" />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { reactive, onMounted } from 'vue'
import { useToast } from '~/composables/useToast'
import { useSettings } from '~/composables/useSettings'

definePageMeta({
  layout: 'admin'
})

const toast = useToast()
const { settings, isSaving, fetchSettings, updateSettings } = useSettings()

const form = reactive({
  dbhRate: '9.25%',
  idlcRate: '9.50%',
  bracRate: '9.75%',
  bank_financing_rate: 8.5,
  whatsapp_number: '+880 1819-987654',
  emergency_hotline: '+880 1911 222333',
  office_address: 'Gulshan-2 Diplomatic Enclave, Dhaka',
  service_fee_pct: 2.0
})

onMounted(async () => {
  const current = await fetchSettings(true)
  if (current) {
    form.dbhRate = current.dbhRate || '9.25%'
    form.idlcRate = current.idlcRate || '9.50%'
    form.bracRate = current.bracRate || '9.75%'
    form.bank_financing_rate = current.bank_financing_rate || 8.5
    form.whatsapp_number = current.whatsapp_number || '+880 1819-987654'
    form.emergency_hotline = current.emergency_hotline || '+880 1911 222333'
    form.office_address = current.office_address || 'Gulshan-2 Diplomatic Enclave, Dhaka'
    form.service_fee_pct = current.service_fee_pct || 2.0
  }
})

const handleSaveSettings = async () => {
  try {
    await updateSettings({ ...form })
    toast.success('Configuration Saved', 'Banking benchmarks and WhatsApp dispatch numbers updated live in database.')
  } catch (err: any) {
    toast.error('Save Failed', err?.message || 'Unable to persist configurations.')
  }
}
</script>

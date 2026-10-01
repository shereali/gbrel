<template>
  <div class="admin-page animate-fade-in">
    <div class="admin-header-row">
      <div>
        <h1 class="page-title">Property Viewings & Site Visits</h1>
        <p class="page-subtitle">Schedule client viewings, assign advisors, and manage visit statuses.</p>
      </div>
      <div class="admin-header-actions">
        <button class="btn btn-gold" @click="openScheduleModal">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <line x1="12" y1="5" x2="12" y2="19"/>
            <line x1="5" y1="12" x2="19" y2="12"/>
          </svg>
          <span>Schedule Viewing</span>
        </button>
      </div>
    </div>

    <div class="panel-card" style="padding:0; overflow:hidden;">
      <div class="table-responsive">
        <table class="admin-table">
          <thead>
            <tr>
              <th>ID</th>
              <th>Visitor & Contact</th>
              <th>Target Property</th>
              <th>Date & Slot</th>
              <th>VIP Pickup Request</th>
              <th>Assigned Specialist</th>
              <th>Status</th>
              <th style="text-align:right;">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="v in viewingsList" :key="v.id">
              <td style="font-weight:800; color:var(--color-gold);">#{{ v.id }}</td>
              <td>
                <strong class="text-contrast">{{ v.name }}</strong>
                <div style="font-size:0.78rem;" class="text-subtle">{{ v.phone }} • {{ v.contact }}</div>
                <NuxtLink v-if="v.lead_id" :to="`/admin/leads?lead=${v.lead_id}`" style="font-size:0.78rem; color:var(--admin-text-emerald); font-weight:600;">Open lead in CRM</NuxtLink>
              </td>
              <td style="font-weight:600; max-width:240px;">{{ v.property_title || v.propertyTitle || 'Property' }}</td>
              <td>
                <div style="font-weight:700;">{{ (v.scheduled_date ? String(v.scheduled_date).slice(0, 10) : '') || v.preferred_date || v.date || 'TBD' }}</div>
                <div style="font-size:0.78rem;" class="text-subtle">{{ v.scheduled_time || v.preferred_time || v.timeSlot || 'Slot TBD' }}<template v-if="v.visit_type === 'Video call'"> · Video call</template></div>
              </td>
              <td>
                <span v-if="v.vip_pickup || v.pickup_requested || v.pickup" class="badge-admin active" style="font-size:0.72rem;">VIP Chauffeur</span>
                <span v-else style="font-size:0.8rem;" class="text-subtle">Direct Arrival</span>
              </td>
              <td>
                <span style="color:#10B981; font-weight:600; font-size:0.88rem;">{{ v.assigned_agent || v.assignedAgent || 'Tanvir Ahmed' }}</span>
              </td>
              <td>
                <select :value="v.status" @change="handleStatusChange(v.id, $event)" class="status-inline-select">
                  <option value="Confirmed">Confirmed</option>
                  <option value="Completed">Completed</option>
                  <option value="In-Progress">In-Progress</option>
                  <option value="No-show">No-show</option>
                  <option value="Cancelled">Cancelled</option>
                </select>
              </td>
              <td style="text-align:right;">
                <div class="action-btn-group">
                  <a 
                    :href="`https://wa.me/${v.phone.replace(/[^0-9]/g, '')}`" 
                    target="_blank" 
                    class="btn btn-sm btn-outline-white" 
                    style="color:#25D366; border-color:rgba(37,211,102,0.3);"
                    title="Direct WhatsApp"
                  >
                    WA
                  </a>
                  <button class="btn btn-sm btn-outline-white" @click="handleCancel(v.id)" title="Cancel inspection">
                    Cancel
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- ======================================================================
         MODAL: SCHEDULE INSPECTION
         ====================================================================== -->
    <div v-if="showModal" class="admin-modal-overlay" @click.self="showModal = false">
      <div class="admin-modal-card animate-fade-in-up">
        <div class="admin-modal-header">
          <div>
            <h3 class="admin-modal-title">Schedule VIP Site Inspection</h3>
            <p class="panel-sub">Dispatch dedicated luxury vehicle and certified advisor</p>
          </div>
          <button class="admin-modal-close" @click="showModal = false">✕</button>
        </div>

        <form @submit.prevent="saveNewViewing">
          <div class="admin-modal-body">
            <div class="grid grid-2" style="gap:14px; margin-bottom:14px;">
              <div class="form-group">
                <label class="form-label">Visitor Full Name *</label>
                <input v-model="form.name" type="text" required placeholder="e.g. Barrister Rafiqul Islam" class="form-input" />
              </div>
              <div class="form-group">
                <label class="form-label">Phone / WhatsApp *</label>
                <input v-model="form.phone" type="tel" required placeholder="+880 1711-..." class="form-input" />
              </div>
            </div>

            <div class="form-group" style="margin-bottom:14px;">
              <label class="form-label">Target Property *</label>
              <select v-model="form.propertyTitle" required class="form-select">
                <option v-for="p in properties" :key="p.id" :value="p.title">
                  {{ p.title }} ({{ p.areaName }})
                </option>
              </select>
            </div>

            <div class="grid grid-3" style="gap:12px; margin-bottom:14px;">
              <div class="form-group">
                <label class="form-label">Date *</label>
                <input v-model="form.date" type="date" required class="form-input" />
              </div>
              <div class="form-group">
                <label class="form-label">Time Slot *</label>
                <select v-model="form.timeSlot" class="form-select">
                  <option value="10:00 AM - 11:30 AM">10:00 AM - 11:30 AM</option>
                  <option value="11:30 AM - 01:00 PM">11:30 AM - 01:00 PM</option>
                  <option value="02:30 PM - 04:00 PM">02:30 PM - 04:00 PM</option>
                  <option value="04:30 PM - 06:00 PM">04:30 PM - 06:00 PM</option>
                </select>
              </div>
              <div class="form-group">
                <label class="form-label">Assigned Broker</label>
                <select v-model="form.assignedAgent" class="form-select">
                  <option value="মোঃ আবু হানিফ (Md. Abu Hanif)">মোঃ আবু হানিফ (Md. Abu Hanif)</option>
                  <option value="সিরাজুম মুনিরা খন্দকার (Sirajum Munira Khandakar)">সিরাজুম মুনিরা খন্দকার (Sirajum Munira Khandakar)</option>
                  <option value="সিয়াম তালুকদার (Siam Talukder)">সিয়াম তালুকদার (Siam Talukder)</option>
                </select>
              </div>
            </div>

            <div style="padding:12px 16px; border-radius:8px; border:1px solid var(--admin-border-subtle);">
              <label class="flex items-center gap-2" style="cursor:pointer; font-size:0.88rem;">
                <input v-model="form.pickup" type="checkbox" style="width:16px; height:16px; accent-color:#E2651C;" />
                <span>Dispatch Complimentary Chauffeur Pickup (Dhaka North / South Hub)</span>
              </label>
            </div>
          </div>

          <div class="admin-modal-footer">
            <button type="button" class="btn btn-sm btn-outline-white" @click="showModal = false">Cancel</button>
            <button type="submit" class="btn btn-sm btn-gold">Confirm Booking</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue'
import { useProperties } from '~/composables/useProperties'
import { useToast } from '~/composables/useToast'
import { useApiUrl } from '~/composables/useApi'

definePageMeta({
  layout: 'admin'
})

const { properties } = useProperties()
const toast = useToast()

const viewingsList = ref<any[]>([])
const isLoading = ref(false)
const isSubmitting = ref(false)
const showModal = ref(false)

const form = reactive({
  name: '',
  phone: '',
  propertyTitle: 'গুলশান ১, ১৩৫/৬ — ১৫ কাঠা জমি ও ২ তলা পুরাতন দালান',
  date: new Date().toISOString().slice(0, 10),
  timeSlot: '02:30 PM - 04:00 PM',
  assignedAgent: 'মোঃ আবু হানিফ (Md. Abu Hanif)',
  pickup: true
})

const fetchViewings = async () => {
  isLoading.value = true
  try {
    const res = await fetch(useApiUrl('/viewings'))
    if (res.ok) {
      const json = await res.json()
      if (json && json.success && Array.isArray(json.data)) {
        viewingsList.value = json.data
      }
    }
  } catch (err) {
    console.error('Failed to fetch viewings:', err)
  } finally {
    isLoading.value = false
  }
}

onMounted(async () => {
  await fetchViewings()
})

const openScheduleModal = () => {
  form.name = ''
  form.phone = ''
  if (properties.value.length > 0) {
    form.propertyTitle = properties.value[0].title
  }
  showModal.value = true
}

const saveNewViewing = async () => {
  isSubmitting.value = true
  try {
    const res = await fetch(useApiUrl('/schedule-viewing'), {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        name: form.name,
        phone: form.phone,
        propertyTitle: form.propertyTitle,
        preferredDate: form.date,
        preferredTime: form.timeSlot,
        assignedAgent: form.assignedAgent,
        pickupRequested: form.pickup
      })
    })

    if (!res.ok) {
      throw new Error(`Booking failed: ${res.statusText}`)
    }

    const json = await res.json()
    if (json && json.data) {
      viewingsList.value.unshift(json.data)
    } else {
      await fetchViewings()
    }

    toast.success('Inspection Scheduled', `VIP tour booked for ${form.name} with ${form.assignedAgent}.`)
    showModal.value = false
  } catch (err: any) {
    toast.error('Schedule Failed', err?.message || 'Could not schedule VIP tour.')
  } finally {
    isSubmitting.value = false
  }
}

const handleStatusChange = async (id: number, event: Event) => {
  const target = event.target as HTMLSelectElement
  const newStatus = target.value
  const v = viewingsList.value.find(item => item.id === id)
  const previousStatus = v ? v.status : 'Confirmed'

  if (v) v.status = newStatus

  try {
    const res = await fetch(useApiUrl(`/viewings/${id}/status`), {
      method: 'PATCH',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ status: newStatus })
    })
    if (!res.ok) throw new Error('Failed to update status on server')
    toast.info('Status Updated', `Tour #${id} marked as "${newStatus}".`)
  } catch (err: any) {
    if (v) v.status = previousStatus
    toast.error('Update Failed', err?.message || 'Could not update status.')
  }
}

const handleCancel = async (id: number) => {
  const v = viewingsList.value.find(item => item.id === id)
  if (!v) return

  const prev = v.status
  v.status = 'Cancelled'

  try {
    const res = await fetch(useApiUrl(`/viewings/${id}/status`), {
      method: 'PATCH',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ status: 'Cancelled' })
    })
    if (!res.ok) throw new Error('Failed to cancel tour on server')
    toast.warning('Tour Cancelled', `Inspection #${id} has been cancelled.`)
  } catch (err: any) {
    v.status = prev
    toast.error('Cancel Failed', err?.message || 'Could not cancel inspection.')
  }
}
</script>

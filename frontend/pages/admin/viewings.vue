<template>
  <div class="admin-page animate-fade-in">
    <div class="admin-header-row">
      <div>
        <h1 class="page-title">VIP Site Viewings & Inspection Logistics</h1>
        <p class="page-subtitle">Schedule, assign advisors, dispatch luxury vehicle pickups from Dhaka hubs, and manage viewing statuses.</p>
      </div>
      <div class="admin-header-actions">
        <button class="btn btn-gold" @click="openScheduleModal">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <line x1="12" y1="5" x2="12" y2="19"/>
            <line x1="5" y1="12" x2="19" y2="12"/>
          </svg>
          <span>+ Schedule VIP Inspection</span>
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
              <td style="font-weight:800; color:#D4AF37;">#{{ v.id }}</td>
              <td>
                <strong style="color:#FFF;">{{ v.name }}</strong>
                <div style="font-size:0.78rem; color:#94A3B8;">{{ v.phone }} • {{ v.contact }}</div>
              </td>
              <td style="color:#E2E8F0; font-weight:600; max-width:240px;">{{ v.propertyTitle }}</td>
              <td>
                <div style="color:#FFF; font-weight:700;">{{ v.date }}</div>
                <div style="font-size:0.78rem; color:#94A3B8;">{{ v.timeSlot }}</div>
              </td>
              <td>
                <span v-if="v.pickup" class="badge-admin active" style="font-size:0.72rem;">VIP Chauffeur</span>
                <span v-else style="color:#64748B; font-size:0.8rem;">Direct Arrival</span>
              </td>
              <td>
                <span style="color:#34D399; font-weight:600; font-size:0.88rem;">{{ v.assignedAgent }}</span>
              </td>
              <td>
                <select :value="v.status" @change="handleStatusChange(v.id, $event)" class="status-inline-select">
                  <option value="Confirmed">Confirmed</option>
                  <option value="Completed">Completed</option>
                  <option value="In-Progress">In-Progress</option>
                  <option value="Cancelled">Cancelled</option>
                </select>
              </td>
              <td style="text-align:right;">
                <div class="action-btn-group">
                  <a 
                    :href="`https://wa.me/${v.phone.replace(/[^0-9]/g, '')}`" 
                    target="_blank" 
                    class="action-btn" 
                    style="color:#25D366; border-color:rgba(37,211,102,0.3);"
                    title="Launch WhatsApp Dispatch"
                  >
                    💬
                  </a>
                  <button class="action-btn delete" @click="cancelViewing(v.id)" title="Cancel Booking">
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
         MODAL: SCHEDULE VIP INSPECTION TOUR
         ====================================================================== -->
    <div v-if="showModal" class="admin-modal-overlay" @click.self="showModal = false">
      <div class="admin-modal-card wide animate-fade-in-up">
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
                <label class="form-label" style="color:#CBD5E1;">Visitor Full Name *</label>
                <input v-model="form.name" type="text" required placeholder="e.g. Barrister Rafiqul Islam" class="form-input" style="background:#1E293B; color:#FFF; border-color:rgba(255,255,255,0.15);" />
              </div>
              <div class="form-group">
                <label class="form-label" style="color:#CBD5E1;">Phone / WhatsApp *</label>
                <input v-model="form.phone" type="tel" required placeholder="+880 1711-..." class="form-input" style="background:#1E293B; color:#FFF; border-color:rgba(255,255,255,0.15);" />
              </div>
            </div>

            <div class="form-group" style="margin-bottom:14px;">
              <label class="form-label" style="color:#CBD5E1;">Target Property Mandate *</label>
              <select v-model="form.propertyTitle" required class="form-select" style="background:#1E293B; color:#FFF; border-color:rgba(255,255,255,0.15);">
                <option v-for="p in properties" :key="p.id" :value="p.title">
                  {{ p.title }} ({{ p.areaName }})
                </option>
              </select>
            </div>

            <div class="grid grid-3" style="gap:12px; margin-bottom:14px;">
              <div class="form-group">
                <label class="form-label" style="color:#CBD5E1;">Date *</label>
                <input v-model="form.date" type="date" required class="form-input" style="background:#1E293B; color:#FFF; border-color:rgba(255,255,255,0.15);" />
              </div>
              <div class="form-group">
                <label class="form-label" style="color:#CBD5E1;">Time Slot *</label>
                <select v-model="form.timeSlot" class="form-select" style="background:#1E293B; color:#FFF; border-color:rgba(255,255,255,0.15);">
                  <option value="10:00 AM - 11:30 AM">10:00 AM - 11:30 AM</option>
                  <option value="11:30 AM - 01:00 PM">11:30 AM - 01:00 PM</option>
                  <option value="02:30 PM - 04:00 PM">02:30 PM - 04:00 PM</option>
                  <option value="04:30 PM - 06:00 PM">04:30 PM - 06:00 PM</option>
                </select>
              </div>
              <div class="form-group">
                <label class="form-label" style="color:#CBD5E1;">Assigned Broker</label>
                <select v-model="form.assignedAgent" class="form-select" style="background:#1E293B; color:#FFF; border-color:rgba(255,255,255,0.15);">
                  <option value="Tanvir Ahmed">Tanvir Ahmed (Gulshan / Purbachal)</option>
                  <option value="Nusrat Jahan">Nusrat Jahan (Coastal / Commercial)</option>
                  <option value="Syed Mahbubur Rahman">Syed Mahbubur Rahman (Land Bank)</option>
                </select>
              </div>
            </div>

            <div style="background:rgba(255,255,255,0.02); padding:12px 16px; border-radius:8px; border:1px solid rgba(255,255,255,0.06);">
              <label class="flex items-center gap-2" style="cursor:pointer; color:#FFF; font-size:0.88rem;">
                <input v-model="form.pickup" type="checkbox" style="width:16px; height:16px; accent-color:#D4AF37;" />
                <span>Dispatch Complimentary Chauffeur Pickup (Dhaka North / South Hub)</span>
              </label>
            </div>
          </div>

          <div class="admin-modal-footer">
            <button type="button" class="btn btn-sm btn-outline-white" @click="showModal = false">Cancel</button>
            <button type="submit" class="btn btn-sm btn-gold">Confirm & Dispatch Booking</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive } from 'vue'
import { useProperties } from '~/composables/useProperties'
import { useToast } from '~/composables/useToast'

definePageMeta({
  layout: 'admin'
})

const { properties } = useProperties()
const toast = useToast()

const viewingsList = ref([
  { id: 101, name: 'Shere Ali', phone: '+880 1711-234567', contact: 'WhatsApp', propertyTitle: 'Lakeview Penthouse at Gulshan-2 Diplomatic Zone', date: '2026-09-05', timeSlot: '03:00 PM - 04:00 PM', pickup: true, assignedAgent: 'Tanvir Ahmed', status: 'Confirmed' },
  { id: 102, name: 'Dr. Kabir Hossain (NRB Canada)', phone: '+1 416-555-0199', contact: 'Phone', propertyTitle: '10 Katha Corner Plot in Purbachal Sector 17', date: '2026-09-08', timeSlot: '11:30 AM - 01:00 PM', pickup: true, assignedAgent: 'Tanvir Ahmed', status: 'Confirmed' },
  { id: 103, name: 'Mrs. Tahmina Begum', phone: '+880 1819-332211', contact: 'WhatsApp', propertyTitle: 'South-Facing Duplex in Dhanmondi 8/A', date: '2026-09-10', timeSlot: '04:00 PM - 05:30 PM', pickup: false, assignedAgent: 'Tanvir Ahmed', status: 'In-Progress' },
  { id: 104, name: 'Syed Tanzeem (UAE)', phone: '+971 50 1234567', contact: 'WhatsApp', propertyTitle: 'Marine Drive Cox\'s Bazar Sea Suite', date: '2026-09-12', timeSlot: '02:30 PM - 04:00 PM', pickup: true, assignedAgent: 'Nusrat Jahan', status: 'Confirmed' }
])

const showModal = ref(false)

const form = reactive({
  name: '',
  phone: '',
  propertyTitle: 'Lakeview Penthouse at Gulshan-2 Diplomatic Zone',
  date: new Date().toISOString().slice(0, 10),
  timeSlot: '02:30 PM - 04:00 PM',
  assignedAgent: 'Tanvir Ahmed',
  pickup: true
})

const openScheduleModal = () => {
  form.name = ''
  form.phone = ''
  if (properties.value.length > 0) {
    form.propertyTitle = properties.value[0].title
  }
  showModal.value = true
}

const saveNewViewing = () => {
  const newId = 100 + viewingsList.value.length + 1
  viewingsList.value.unshift({
    id: newId,
    name: form.name,
    phone: form.phone,
    contact: 'WhatsApp',
    propertyTitle: form.propertyTitle,
    date: form.date,
    timeSlot: form.timeSlot,
    pickup: form.pickup,
    assignedAgent: form.assignedAgent,
    status: 'Confirmed'
  })
  toast.success('Inspection Scheduled', `VIP tour booked for ${form.name} with ${form.assignedAgent}.`)
  showModal.value = false
}

const handleStatusChange = (id: number, event: Event) => {
  const target = event.target as HTMLSelectElement
  const v = viewingsList.value.find(item => item.id === id)
  if (v) {
    v.status = target.value
    toast.info('Status Updated', `Tour #${id} marked as "${v.status}".`)
  }
}

const cancelViewing = (id: number) => {
  const v = viewingsList.value.find(item => item.id === id)
  if (v) {
    v.status = 'Cancelled'
    toast.warning('Tour Cancelled', `Inspection #${id} has been cancelled.`)
  }
}
</script>

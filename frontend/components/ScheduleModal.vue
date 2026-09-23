<template>
  <div v-if="isOpen" class="modal-overlay" ref="modalRoot" @click.self="close">
    <div class="modal-card animate-fade-in-up">
      <!-- Close Button -->
      <button class="modal-close-btn" @click="close" aria-label="Close modal">✕</button>

      <!-- Step 1: Form -->
      <div v-if="!isSubmitted">
        <div class="flex items-center gap-3" style="margin-bottom: 20px;">
          <div style="width: 44px; height: 44px; border-radius: 12px; background: rgba(5,150,105,0.1); color: #059669; display: flex; align-items: center; justify-content: center;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
              <rect x="3" y="4" width="18" height="18" rx="2"/>
              <line x1="16" y1="2" x2="16" y2="6"/>
              <line x1="8" y1="2" x2="8" y2="6"/>
              <line x1="3" y1="10" x2="21" y2="10"/>
            </svg>
          </div>
          <div>
            <h3 style="font-size: 1.35rem; font-weight: 800; color: #0A1128;">Schedule a Site Visit</h3>
            <p style="font-size: 0.85rem; color: #64748B;">Accompanied by a dedicated GBREL property specialist</p>
          </div>
        </div>

        <!-- Property Title Badge -->
        <div style="background: var(--color-bg-card-alt); border-radius: var(--radius-md); padding: 12px 16px; margin-bottom: 24px; border: 1px solid var(--color-border);">
          <div style="font-size: 0.75rem; color: #64748B; text-transform: uppercase; font-weight: 700;">Selected Property</div>
          <div style="font-size: 0.95rem; font-weight: 700; color: #0F172A; margin-top: 2px;">{{ propertyTitle }}</div>
        </div>

        <form @submit.prevent="submitViewing">
          <!-- Date Selection -->
          <div class="form-group" style="margin-bottom: 18px;">
            <label class="form-label">Select Preferred Date</label>
            <input v-model="form.date" type="date" required class="form-input" :min="minDate" />
          </div>

          <!-- Time Slot Selection -->
          <div class="form-group" style="margin-bottom: 20px;">
            <label class="form-label">Select Available Time Slot</label>
            <div class="time-slots-grid">
              <button 
                v-for="slot in timeSlots" 
                :key="slot" 
                type="button" 
                class="time-slot-chip"
                :class="{ selected: form.timeSlot === slot }"
                @click="form.timeSlot = slot"
              >
                {{ slot }}
              </button>
            </div>
          </div>

          <!-- Visitor Details -->
          <div class="grid grid-2" style="gap: 14px; margin-bottom: 14px;">
            <div class="form-group">
              <label class="form-label">Full Name</label>
              <input v-model="form.name" type="text" placeholder="e.g. Shere Ali" required class="form-input" />
            </div>
            <div class="form-group">
              <label class="form-label">Phone Number (BD / WhatsApp)</label>
              <input v-model="form.phone" type="tel" placeholder="+880 17XX-XXXXXX" required class="form-input" />
            </div>
          </div>

          <div class="grid grid-2" style="gap: 14px; margin-bottom: 18px;">
            <div class="form-group">
              <label class="form-label">Email Address</label>
              <input v-model="form.email" type="email" placeholder="you@example.com" required class="form-input" />
            </div>
            <div class="form-group">
              <label class="form-label">Preferred Contact Channel</label>
              <select v-model="form.contactMethod" class="form-select">
                <option value="WhatsApp">WhatsApp (Fastest Response)</option>
                <option value="Phone Call">Direct Phone Call</option>
                <option value="Email">Email Confirmation</option>
              </select>
            </div>
          </div>

          <!-- VIP Pickup Option -->
          <div style="background: rgba(212,175,55,0.08); border: 1px dashed #D4AF37; border-radius: var(--radius-md); padding: 14px; margin-bottom: 24px;">
            <label class="flex items-center gap-2" style="cursor: pointer; font-size: 0.9rem; font-weight: 600; color: #78350F;">
              <input v-model="form.pickupRequested" type="checkbox" style="width: 18px; height: 18px; accent-color: #059669;" />
              <span>Request VIP Car Pickup from Dhaka City Hub (Gulshan / Dhanmondi / Airport)</span>
            </label>
          </div>

          <!-- Submit Button -->
          <button type="submit" class="btn btn-emerald btn-lg" style="width: 100%;">
            <span>Confirm & Reserve Site Viewing</span>
          </button>
        </form>
      </div>

      <!-- Step 2: Confirmation Screen -->
      <div v-else class="text-center animate-fade-in" style="padding: 20px 0;">
        <div style="width: 64px; height: 64px; border-radius: 50%; background: #ECFDF5; color: #059669; margin: 0 auto 20px; display: flex; align-items: center; justify-content: center; font-size: 2rem;">
          ✓
        </div>
        <h3 style="font-size: 1.6rem; font-weight: 800; color: #0F172A; margin-bottom: 8px;">Viewing Request Confirmed</h3>
        <p style="color: #64748B; font-size: 0.95rem; max-width: 440px; margin: 0 auto 24px;">
          Your appointment is logged with our senior real estate advisor. An SMS and WhatsApp itinerary has been dispatched.
        </p>

        <div style="background: var(--color-bg-card-alt); border-radius: var(--radius-lg); padding: 20px; text-align: left; max-width: 480px; margin: 0 auto 24px; border: 1px solid var(--color-border);">
          <div style="font-size: 0.85rem; color: #64748B; margin-bottom: 4px;">Appointment ID: <strong>#GNG-{{ Math.floor(100000 + Math.random() * 900000) }}</strong></div>
          <div style="font-size: 0.95rem; color: #0F172A; margin-bottom: 4px;">Date: <strong>{{ form.date }}</strong></div>
          <div style="font-size: 0.95rem; color: #0F172A; margin-bottom: 4px;">Time Slot: <strong>{{ form.timeSlot }}</strong></div>
          <div style="font-size: 0.95rem; color: #0F172A;">Property: <strong>{{ propertyTitle }}</strong></div>
        </div>

        <button class="btn btn-primary" @click="close">
          Done & Return to Listing
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, computed } from 'vue'
import { useAuth } from '~/composables/useAuth'
import { useOverlayBehavior } from '~/composables/useOverlayBehavior'

const props = defineProps<{
  isOpen: boolean
  propertyId: number
  propertyTitle: string
}>()

const emit = defineEmits(['close'])

const { addScheduledViewing, user } = useAuth()
const isSubmitted = ref(false)

const isOpenRef = computed(() => props.isOpen)
const modalRoot = ref<HTMLElement | null>(null)

const close = () => {
  isSubmitted.value = false
  emit('close')
}

useOverlayBehavior(isOpenRef, close, modalRoot)

const tomorrow = new Date()
tomorrow.setDate(tomorrow.getDate() + 1)
const minDate = computed(() => tomorrow.toISOString().split('T')[0])

const timeSlots = [
  '10:00 AM - 11:30 AM',
  '11:30 AM - 01:00 PM',
  '02:30 PM - 04:00 PM',
  '04:00 PM - 05:30 PM',
  '05:30 PM - 07:00 PM'
]

const form = reactive({
  date: minDate.value,
  timeSlot: '02:30 PM - 04:00 PM',
  name: user.value?.name && user.value.name !== 'Guest User' && user.value.name !== 'Guest Buyer' ? user.value.name : '',
  phone: user.value?.phone || '',
  email: user.value?.email || '',
  contactMethod: 'WhatsApp',
  pickupRequested: false
})

const submitViewing = () => {
  addScheduledViewing({
    propertyId: props.propertyId,
    propertyTitle: props.propertyTitle,
    date: form.date,
    timeSlot: form.timeSlot,
    name: form.name,
    visitorName: form.name,
    phone: form.phone,
    visitorPhone: form.phone,
    email: form.email,
    visitorEmail: form.email,
    contactMethod: form.contactMethod,
    vipPickup: form.pickupRequested,
    pickupRequested: form.pickupRequested
  })
  isSubmitted.value = true
}
</script>

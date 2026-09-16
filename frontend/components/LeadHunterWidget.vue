<template>
  <div>
    <!-- Floating Stack on Bottom Right -->
    <div class="floating-hunter-stack">
      <!-- EMI Calculator Floating Trigger -->
      <button 
        class="card-icon-action" 
        style="width: 48px; height: 48px; background: #0A1128; color: #D4AF37; box-shadow: 0 8px 20px rgba(0,0,0,0.3); border: 1.5px solid rgba(212,175,55,0.4);"
        title="Open Home Loan EMI Calculator"
        aria-label="Open home loan EMI calculator"
        @click="emiModalOpen = true"
      >
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
          <rect x="2" y="5" width="20" height="14" rx="2"/>
          <line x1="2" y1="10" x2="22" y2="10"/>
        </svg>
      </button>

      <!-- Instant Callback Trigger -->
      <button 
        class="card-icon-action" 
        style="width: 48px; height: 48px; background: #E11D48; color: #FFFFFF; box-shadow: 0 8px 20px rgba(225,29,72,0.4);"
        title="Request Free Instant Callback"
        aria-label="Request free instant callback"
        @click="callbackModalOpen = true"
      >
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
          <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
        </svg>
      </button>

      <!-- Pulsing WhatsApp Button -->
      <a 
        href="https://wa.me/8801819987654?text=Hello%20GBREL,%20I%20am%20looking%20for%20verified%20properties%20in%20Bangladesh." 
        target="_blank" 
        rel="noopener noreferrer"
        class="floating-whatsapp-btn"
        title="Chat Live on WhatsApp with Advisor"
      >
        <svg width="30" height="30" viewBox="0 0 24 24" fill="currentColor">
          <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.711 2.598 2.664-.698c.969.54 1.861.855 2.796.855 3.18 0 5.767-2.587 5.768-5.766 0-3.18-2.586-5.767-5.768-5.767zm7.531 5.766c-.002 4.153-3.38 7.531-7.531 7.531-.019 0-.038 0-.057 0-1.284 0-2.53-.332-3.64-.962l-4.053 1.063 1.082-3.953c-.707-1.16-1.082-2.493-1.082-3.864.002-4.153 3.38-7.531 7.531-7.531 4.153 0 7.531 3.378 7.531 7.531z"/>
        </svg>
      </a>
    </div>

    <!-- EMI Modal Component -->
    <EmiCalculatorModal :is-open="emiModalOpen" @close="emiModalOpen = false" />

    <!-- Instant Callback Modal -->
    <div v-if="callbackModalOpen" ref="callbackRoot" class="modal-overlay" @click.self="closeCallback">
      <div class="modal-card animate-fade-in-up">
        <button class="modal-close-btn" @click="closeCallback" aria-label="Close callback request">✕</button>

        <div v-if="!callbackSubmitted">
          <div class="flex items-center gap-3" style="margin-bottom: 20px;">
            <div style="width: 44px; height: 44px; border-radius: 12px; background: #FFF1F2; color: #E11D48; display: flex; align-items: center; justify-content: center;">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
              </svg>
            </div>
            <div>
              <h3 style="font-size: 1.4rem; font-weight: 800; color: #0A1128;">Request an Instant Callback</h3>
              <p style="font-size: 0.85rem; color: #64748B;">A senior property advisor will call you within 15 minutes</p>
            </div>
          </div>

          <form @submit.prevent="submitCallback">
            <div class="form-group" style="margin-bottom: 14px;">
              <label class="form-label">Your Name</label>
              <input v-model="cbForm.name" type="text" placeholder="e.g. Shere Ali" required class="form-input" />
            </div>

            <div class="form-group" style="margin-bottom: 14px;">
              <label class="form-label">Phone Number (Bangladeshi or International)</label>
              <input v-model="cbForm.phone" type="tel" placeholder="+880 17XX-XXXXXX" required class="form-input" />
            </div>

            <div class="form-group" style="margin-bottom: 20px;">
              <label class="form-label">What are you looking for?</label>
              <select v-model="cbForm.interest" class="form-select">
                <option value="Buy Flat in Gulshan/Banani/Dhanmondi">Buy Flat in Gulshan / Banani / Dhanmondi</option>
                <option value="Buy Residential Plot in Purbachal/Bashundhara">Buy Residential Plot in Purbachal / Bashundhara</option>
                <option value="Invest in Cox's Bazar / Sylhet Resort Asset">Invest in Cox's Bazar / Sylhet Resort Suite</option>
                <option value="Commercial Land & Office Acquisition">Commercial Land & Office Acquisition</option>
                <option value="Sell / List My Property">Sell / List My Property</option>
              </select>
            </div>

            <button type="submit" class="btn btn-hunter-pulse btn-lg" style="width: 100%;">
              <span>Call Me Within 15 Minutes</span>
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <polyline points="20 6 9 17 4 12"/>
              </svg>
            </button>
          </form>
        </div>

        <div v-else class="text-center animate-fade-in" style="padding: 20px 0;">
          <div style="width: 60px; height: 60px; border-radius: 50%; background: #ECFDF5; color: #059669; margin: 0 auto 16px; display: flex; align-items: center; justify-content: center; font-size: 2rem;">
            ✓
          </div>
          <h3 style="font-size: 1.4rem; font-weight: 800; color: #0F172A; margin-bottom: 6px;">Callback Queued</h3>
          <p style="color: #64748B; font-size: 0.9rem; margin-bottom: 20px;">Thank you, {{ cbForm.name }}. An advisor is reviewing your request and will call {{ cbForm.phone }} shortly.</p>
          <button class="btn btn-primary" @click="closeCallback">Close</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive } from 'vue'
import EmiCalculatorModal from '~/components/EmiCalculatorModal.vue'
import { useOverlayBehavior } from '~/composables/useOverlayBehavior'

const emiModalOpen = ref(false)
const callbackModalOpen = ref(false)
const callbackSubmitted = ref(false)

const callbackRoot = ref<HTMLElement | null>(null)

const closeCallback = () => {
  callbackModalOpen.value = false
  callbackSubmitted.value = false
}

useOverlayBehavior(callbackModalOpen, closeCallback, callbackRoot)

const cbForm = reactive({
  name: '',
  phone: '',
  interest: 'Buy Flat in Gulshan/Banani/Dhanmondi'
})

const submitCallback = async () => {
  callbackSubmitted.value = true
  try {
    await fetch(useApiUrl('/leads'), {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        name: cbForm.name,
        phone: cbForm.phone,
        property_title: cbForm.interest,
        lead_type: '15-Min VIP Callback',
        message: `Client requested immediate callback for: ${cbForm.interest}`
      })
    })
  } catch {
    //
  }
}
</script>

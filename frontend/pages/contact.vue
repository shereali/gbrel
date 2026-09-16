<template>
  <div style="background: #F8FAFC; min-height: 100vh; padding: 40px 0 80px;">
    <div class="container">
      <div style="text-align:center; max-width:640px; margin:0 auto 48px;">
        <span class="section-tag">Corporate Headquarters</span>
        <h1 style="font-size: 2.4rem; font-weight: 800; color: #0A1128; margin-bottom: 12px;">Get in Touch with GBREL</h1>
        <p style="color: #64748B; font-size: 1rem;">
          Whether acquiring a luxury residence in Gulshan, securing high-value plots in Purbachal, or exploring hospitality suites in Cox's Bazar, our senior advisory desk is at your service.
        </p>
      </div>

      <div class="grid grid-2" style="gap: 36px; margin-bottom: 48px;">
        <!-- Contact Form -->
        <div style="background: #FFFFFF; border: 1px solid var(--color-border); border-radius: var(--radius-xl); padding: 36px; box-shadow: var(--shadow-md);">
          <h3 style="font-size: 1.35rem; font-weight: 800; color: #0A1128; margin-bottom: 16px;">Send Us a Message</h3>
          
          <div v-if="sent" class="alert alert-success" style="background:#ECFDF5; border:1px solid #10B981; color:#065F46; padding:14px; border-radius:8px; margin-bottom:18px;">
            Thank you! Your inquiry has been sent to our Executive Desk. We will call you back within 15 minutes.
          </div>

          <form @submit.prevent="handleSubmit">
            <div class="form-group" style="margin-bottom: 14px;">
              <label class="form-label">Full Name</label>
              <input v-model="form.name" type="text" placeholder="e.g. Shere Ali" required class="form-input" />
            </div>
            <div class="form-group" style="margin-bottom: 14px;">
              <label class="form-label">Email Address</label>
              <input v-model="form.email" type="email" placeholder="you@example.com" required class="form-input" />
            </div>
            <div class="form-group" style="margin-bottom: 14px;">
              <label class="form-label">Phone (WhatsApp)</label>
              <input v-model="form.phone" type="tel" placeholder="+880 17XX-XXXXXX" required class="form-input" />
            </div>
            <div class="form-group" style="margin-bottom: 20px;">
              <label class="form-label">Inquiry Details</label>
              <textarea v-model="form.message" rows="4" class="form-textarea" placeholder="Tell us what type of land or property you are seeking..."></textarea>
            </div>
            <button type="submit" class="btn btn-emerald btn-lg" style="width: 100%;">
              <span>{{ sent ? 'Message Dispatched' : 'Send Message to Executive Desk' }}</span>
            </button>
          </form>
        </div>

        <!-- Office Locations & Hotline -->
        <div style="display: flex; flex-direction: column; gap: 20px;">
          <div style="background: #FFFFFF; border: 1px solid var(--color-border); border-radius: var(--radius-lg); padding: 24px;">
            <div class="flex items-center gap-3" style="margin-bottom: 8px;">
              <span style="width: 40px; height: 40px; border-radius: 10px; background: rgba(10,17,40,0.06); display: flex; align-items: center; justify-content: center; color: #0A1128;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                  <path d="M3 21h18M5 21V7l7-4 7 4v14M9 21v-6h6v6"/>
                </svg>
              </span>
              <div>
                <strong style="color: #0A1128; font-size: 1.1rem;">Dhaka Headquarters (Diplomatic Zone)</strong>
                <div style="font-size: 0.85rem; color: #64748B;">Level 14, GBREL Tower, Kemal Ataturk Avenue, Gulshan-2, Dhaka-1212</div>
              </div>
            </div>
            <div style="font-size: 0.9rem; color: #059669; font-weight: 700; margin-top: 8px;">
              Direct Desk: +880 1819-987654 / +880 2 988-1234
            </div>
          </div>

          <div style="background: #FFFFFF; border: 1px solid var(--color-border); border-radius: var(--radius-lg); padding: 24px;">
            <div class="flex items-center gap-3" style="margin-bottom: 8px;">
              <span style="width: 40px; height: 40px; border-radius: 10px; background: rgba(10,17,40,0.06); display: flex; align-items: center; justify-content: center; color: #0A1128;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                  <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0z"/>
                  <circle cx="12" cy="10" r="3"/>
                </svg>
              </span>
              <div>
                <strong style="color: #0A1128; font-size: 1.1rem;">Chittagong & Coastal Division Desk</strong>
                <div style="font-size: 0.85rem; color: #64748B;">Agrabad Commercial Area, Chittagong & Kolatoli Marine Drive, Cox's Bazar</div>
              </div>
            </div>
            <div style="font-size: 0.9rem; color: #059669; font-weight: 700; margin-top: 8px;">
              Direct Desk: +880 1711-889900
            </div>
          </div>

          <div style="background: #FFFFFF; border: 1px solid var(--color-border); border-radius: var(--radius-lg); padding: 24px;">
            <div class="flex items-center gap-3" style="margin-bottom: 8px;">
              <span style="width: 40px; height: 40px; border-radius: 10px; background: rgba(10,17,40,0.06); display: flex; align-items: center; justify-content: center; color: #0A1128;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                  <path d="M7 2v8M17 2v8M3 8h18M5 8v12a1 1 0 0 0 1 1h3a1 1 0 0 0 1-1v-5h4v5a1 1 0 0 0 1 1h3a1 1 0 0 0 1-1V8"/>
                </svg>
              </span>
              <div>
                <strong style="color: #0A1128; font-size: 1.1rem;">Sylhet Tea Valley & Expatriate Desk</strong>
                <div style="font-size: 0.85rem; color: #64748B;">Shahjalal Uposhahar, Sylhet & Bhanugach Road, Sreemangal</div>
              </div>
            </div>
            <div style="font-size: 0.9rem; color: #059669; font-weight: 700; margin-top: 8px;">
              Direct Desk: +880 1912-778899
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive } from 'vue'

const sent = ref(false)
const form = reactive({
  name: '',
  email: '',
  phone: '',
  message: ''
})

const handleSubmit = async () => {
  sent.value = true
  try {
    await fetch(useApiUrl('/leads'), {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        name: form.name,
        email: form.email,
        phone: form.phone,
        property_title: 'Contact Form Inquiry',
        lead_type: 'Direct Contact Form',
        message: form.message
      })
    })
  } catch {
    //
  }
}
</script>

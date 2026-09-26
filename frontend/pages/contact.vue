<template>
  <div class="ct">
    <div class="gb-wrap ct-grid">
      <div class="ct-intro">
        <h1>কথা বলুন আমাদের সঙ্গে</h1>
        <p class="gb-lead">কোনো প্রপার্টির দাম, কাগজপত্র বা সাইট ভিজিট নিয়ে প্রশ্ন থাকলে লিখুন। বাজেট আর পছন্দের এলাকা জানালে মিলিয়ে প্রপার্টি দেখাতে পারি।</p>

        <ul class="ct-ways">
          <li v-if="settings.contact_phone">
            <Phone :size="22" aria-hidden="true" />
            <div><span>ফোন</span><a :href="`tel:${tel(settings.contact_phone)}`" class="ct-big">{{ settings.contact_phone }}</a></div>
          </li>
          <li v-if="whatsapp">
            <MessageCircle :size="22" aria-hidden="true" />
            <div><span>WhatsApp</span><a :href="`https://wa.me/${whatsapp}`" target="_blank" rel="noopener noreferrer" class="gb-link">WhatsApp-এ লিখুন</a></div>
          </li>
          <li v-if="settings.contact_email">
            <Mail :size="22" aria-hidden="true" />
            <div><span>ইমেইল</span><a :href="`mailto:${settings.contact_email}`" class="gb-link">{{ settings.contact_email }}</a></div>
          </li>
          <li v-if="settings.office_address">
            <MapPin :size="22" aria-hidden="true" />
            <div><span>অফিস</span><p>{{ settings.office_address }}</p><small v-if="settings.working_hours">{{ settings.working_hours }}</small></div>
          </li>
        </ul>
      </div>

      <div class="ct-card">
        <div v-if="sent" class="ct-done" role="status">
          <h2>বার্তা পৌঁছেছে</h2>
          <p>ধন্যবাদ, {{ form.name }}। আমাদের টিম {{ sentPhone }} নম্বরে যোগাযোগ করবে।</p>
          <NuxtLink to="/properties" class="gb-btn gb-btn--paddy">এর মধ্যে প্রপার্টি দেখুন</NuxtLink>
        </div>

        <form v-else novalidate @submit.prevent="handleSubmit">
          <h2>বার্তা পাঠান</h2>
          <label class="gb-field">
            <span>আপনার নাম</span>
            <input v-model.trim="form.name" type="text" autocomplete="name" required class="gb-input" />
          </label>
          <label class="gb-field">
            <span>মোবাইল নম্বর</span>
            <input v-model="form.phone" type="tel" inputmode="tel" autocomplete="tel" placeholder="01XXXXXXXXX বা +44…" required class="gb-input" :aria-invalid="errorField === 'phone'" />
          </label>
          <label class="gb-field">
            <span>ইমেইল <small>(ঐচ্ছিক)</small></span>
            <input v-model.trim="form.email" type="email" autocomplete="email" class="gb-input" />
          </label>
          <label class="gb-field">
            <span>কী জানতে চান?</span>
            <textarea v-model="form.message" rows="4" class="gb-textarea" placeholder="যেমন: পূর্বাচলে ৫ কাঠার প্লট খুঁজছি, বাজেট ৫০ লাখের মধ্যে।"></textarea>
          </label>
          <p v-if="error" class="ct-err" role="alert">{{ error }}</p>
          <button type="submit" class="gb-btn gb-btn--sun" :disabled="sending">{{ sending ? 'পাঠানো হচ্ছে…' : 'বার্তা পাঠান' }}</button>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, onMounted, reactive, ref } from 'vue'
import { Mail, MapPin, MessageCircle, Phone } from 'lucide-vue-next'
import { useSettings } from '~/composables/useSettings'
import { normalizePhone } from '~/utils/propertyLabels'

const { settings, fetchSettings } = useSettings()
onMounted(() => { fetchSettings() })
const tel = (v: string) => v.replace(/[^\d+]/g, '')
const whatsapp = computed(() => (settings.value.whatsapp_number || '').replace(/\D/g, ''))

const form = reactive({ name: '', email: '', phone: '', message: '' })
const sent = ref(false)
const sending = ref(false)
const sentPhone = ref('')
const error = ref('')
const errorField = ref('')

const handleSubmit = async () => {
  error.value = ''; errorField.value = ''
  if (!form.name) { error.value = 'আপনার নাম লিখুন।'; errorField.value = 'name'; return }
  const phone = normalizePhone(form.phone)
  if (!phone) { error.value = 'সঠিক মোবাইল নম্বর দিন, যেমন 01712345678। বিদেশের নম্বর হলে দেশের কোডসহ লিখুন।'; errorField.value = 'phone'; return }
  sending.value = true
  try {
    const res = await fetch(useApiUrl('/leads'), {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
      body: JSON.stringify({ name: form.name, email: form.email || null, phone, property_title: 'Contact Form Inquiry', lead_type: 'Direct Contact Form', message: form.message })
    })
    if (!res.ok) throw new Error(String(res.status))
    sentPhone.value = phone
    sent.value = true
  } catch {
    error.value = 'বার্তা পাঠানো যায়নি। একটু পরে আবার চেষ্টা করুন, অথবা সরাসরি ফোন করুন।'
  } finally {
    sending.value = false
  }
}

useSeoMeta({ title: 'যোগাযোগ | গ্রাম বাংলা রিয়েল এস্টেট', description: 'প্রপার্টির দাম, কাগজপত্র বা সাইট ভিজিট নিয়ে গ্রাম বাংলা রিয়েল এস্টেট টিমের সঙ্গে কথা বলুন।' })
</script>

<style scoped>
.ct { padding: clamp(40px, 7vw, 88px) 0 96px; }
.ct-grid { display: grid; grid-template-columns: 1fr minmax(0, 480px); gap: clamp(32px, 6vw, 88px); align-items: start; }
.ct-intro h1 { font-size: var(--gb-t-h1); font-weight: 800; font-stretch: 112%; margin-bottom: 16px; }
.ct-ways { list-style: none; margin-top: 36px; display: flex; flex-direction: column; }
.ct-ways li { display: flex; gap: 16px; padding: 18px 0; border-top: 1px solid var(--gb-silt); }
.ct-ways svg { color: var(--gb-leaf); flex-shrink: 0; margin-top: 4px; }
.ct-ways span { display: block; font-size: .85rem; color: var(--gb-ink-soft); }
.ct-ways small { color: var(--gb-ink-soft); }
.ct-big { font-family: var(--gb-display); font-size: 1.6rem; font-weight: 700; color: var(--gb-paddy); font-variant-numeric: tabular-nums; }
.ct-card { background: var(--gb-sheet); border: 1px solid var(--gb-silt); border-radius: var(--gb-r-lg); padding: clamp(24px, 4vw, 36px); }
.ct-card form { display: flex; flex-direction: column; gap: 16px; }
.ct-card h2 { font-size: 1.6rem; font-weight: 700; }
.ct-card .gb-field small { font-weight: 400; color: var(--gb-ink-soft); }
.ct-card .gb-btn:disabled { opacity: .7; cursor: wait; }
.ct-err { color: #A23B16; background: #FBE9DF; border-radius: 8px; padding: 10px 12px; font-size: .92rem; }
.ct-done { display: flex; flex-direction: column; align-items: flex-start; gap: 12px; }
@media (max-width: 900px) { .ct-grid { grid-template-columns: 1fr; } }
</style>

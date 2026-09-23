<template>
  <Teleport to="body">
    <div v-if="open" ref="dialogRoot" class="inquiry-overlay" @click.self="emit('close')">
      <section class="buyer-dialog" role="dialog" aria-modal="true" aria-labelledby="buyer-title" lang="bn">
        <button class="buyer-close" type="button" aria-label="ফর্ম বন্ধ করুন" @click="emit('close')">×</button>
        <div v-if="savedId" class="buyer-success" role="status">
          <span class="buyer-success-icon" aria-hidden="true">✓</span>
          <p class="buyer-eyebrow">অনুরোধ #{{ savedId }}</p>
          <h2 id="buyer-title">ধন্যবাদ, আপনার অনুরোধ পেয়েছি!</h2>
          <p>GBREL টিম এই প্রপার্টি সম্পর্কে আপনার সঙ্গে {{ form.channel === 'Phone Call' ? 'ফোনে' : 'WhatsApp-এ' }} যোগাযোগ করবে। আপনার দেওয়া তথ্য অনুযায়ী আলোচনা শুরু হবে।</p>
          <div class="buyer-summary">{{ property.title }}<br>{{ form.name }} · {{ normalizePhone(form.phone) }}</div>
          <p>এটি তথ্য ও যোগাযোগের অনুরোধ। কোনো বুকিং বা পেমেন্ট হয়নি।</p>
          <button class="buyer-primary" type="button" @click="emit('close')">প্রপার্টিতে ফিরে যাই</button>
        </div>
        <template v-else>
          <p class="buyer-eyebrow">আপনার পরবর্তী ঠিকানা · GBREL</p>
          <h2 id="buyer-title">সিদ্ধান্ত নিন, বিস্তারিত জেনে</h2>
          <p v-if="step === 1" class="buyer-intro">দাম, পেমেন্টের শর্ত ও সাইট ভিজিট নিয়ে কথা বলুন। আপনার প্রয়োজন অনুযায়ী তথ্য পেতে ছোট্ট ফর্মটি পূরণ করুন।</p>
          <div class="buyer-property"><strong>{{ property.title }}</strong><span>{{ property.hidePrice ? 'দাম জানতে অনুরোধ করুন' : formatBDT(property.price) }}<small v-if="!property.hidePrice"> {{ property.priceUnit }}</small></span></div>
          <div class="buyer-progress" aria-live="polite"><span :class="{ current: step === 1 }">১. আপনার পরিকল্পনা</span><span :class="{ current: step === 2 }">২. যোগাযোগ</span></div>
          <form v-if="step === 1" @submit.prevent="advance">
            <fieldset>
              <legend>কী উদ্দেশ্যে খুঁজছেন? <span>(আবশ্যক)</span></legend>
              <div class="buyer-options">
                <label v-for="option in purposes" :key="option.value" class="buyer-option" :class="{ selected: form.purpose === option.value }">
                  <input v-model="form.purpose" type="radio" name="purpose" :value="option.value" required><span>{{ option.label }}</span>
                </label>
              </div>
            </fieldset>
            <label class="buyer-label" for="buyer-timeline">কখন কেনার পরিকল্পনা? <span>(আবশ্যক)</span></label>
            <select id="buyer-timeline" v-model="form.timeline" required>
              <option value="" disabled>আপনার সময়সীমা বেছে নিন</option>
              <option value="Within 30 days">আগামী ৩০ দিনের মধ্যে</option>
              <option value="1–3 months">১–৩ মাসের মধ্যে</option>
              <option value="3–6 months">৩–৬ মাসের মধ্যে</option>
              <option value="Researching; no fixed timeline">এখন তথ্য নিচ্ছি, সময় ঠিক করিনি</option>
            </select>
            <label class="buyer-label" for="buyer-budget">{{ property.hidePrice ? 'বাজেট নিয়ে আপনার ভাবনা?' : 'এই দাম আপনার পরিকল্পনার সঙ্গে মেলে?' }} <span>(আবশ্যক)</span></label>
            <select id="buyer-budget" v-model="form.budget" required>
              <option value="" disabled>যেটি আপনার সঙ্গে মেলে</option>
              <template v-if="!property.hidePrice">
                <option value="Listed price fits budget">হ্যাঁ, এই দাম আমার বাজেটের মধ্যে</option>
                <option value="Needs payment / financing discussion">কিস্তি / অর্থায়নের শর্ত জেনে সিদ্ধান্ত নেব</option>
                <option value="Budget below listed price">আমার বাজেট এর চেয়ে কম</option>
              </template>
              <option value="Needs full cost breakdown">মোট খরচ জেনে বাজেট ঠিক করব</option>
            </select>
            <p class="buyer-hint">এখনই কেনার সিদ্ধান্ত লাগবে না। আপনার উত্তরের সঙ্গে মিলিয়ে আলোচনা করব।</p>
            <button class="buyer-primary" type="submit">পরের ধাপ: যোগাযোগের তথ্য <span aria-hidden="true">→</span></button>
          </form>
          <form v-else @submit.prevent="submit" :aria-busy="sending">
            <fieldset :disabled="sending" class="buyer-contact-fields">
              <legend class="sr-only">যোগাযোগের তথ্য</legend>
              <label class="buyer-label" for="buyer-name">আপনার নাম <span>(আবশ্যক)</span></label>
              <input id="buyer-name" v-model="form.name" name="name" autocomplete="name" maxlength="100" required placeholder="যে নামে আপনাকে ডাকব">
              <label class="buyer-label" for="buyer-phone">মোবাইল নম্বর <span>(আবশ্যক)</span></label>
              <input id="buyer-phone" v-model="form.phone" name="tel" type="tel" inputmode="tel" autocomplete="tel" maxlength="25" required placeholder="01XXXXXXXXX অথবা +দেশের কোড" :aria-invalid="!!phoneError" aria-describedby="buyer-phone-hint buyer-phone-error" @input="phoneError = ''">
              <small id="buyer-phone-hint" class="buyer-hint">দেশের বাইরে থাকলে দেশের কোডসহ নম্বর দিন।</small>
              <p v-if="phoneError" id="buyer-phone-error" class="buyer-error" role="alert">{{ phoneError }}</p>
              <label class="buyer-label" for="buyer-channel">কীভাবে যোগাযোগ করলে সুবিধা? <span>(আবশ্যক)</span></label>
              <select id="buyer-channel" v-model="form.channel" required>
                <option value="" disabled>আপনার পছন্দ বেছে নিন</option>
                <option value="Phone Call">ফোন কল</option><option value="WhatsApp">আগে WhatsApp-এ মেসেজ</option>
              </select>
              <details class="buyer-extra"><summary>কলের সময় বা সাইট ভিজিটের পছন্দ যোগ করুন (ঐচ্ছিক)</summary>
                <label class="buyer-label" for="buyer-time">কখন কথা বলা সুবিধাজনক?</label>
                <select id="buyer-time" v-model="form.time"><option value="">পরে ঠিক করব</option><option>সকাল ১০টা–দুপুর ১টা (বাংলাদেশ সময়)</option><option>দুপুর ১টা–বিকেল ৫টা (বাংলাদেশ সময়)</option><option>সন্ধ্যা ৫টা–রাত ৮টা (বাংলাদেশ সময়)</option></select>
                <label class="buyer-label" for="buyer-next">কোন বিষয়টি আগে জানতে চান?</label>
                <select id="buyer-next" v-model="form.next"><option value="">পরে আলোচনা করব</option><option>সাইট ভিজিট করতে চাই</option><option>মোট খরচ ও পেমেন্টের শর্ত</option><option>মালিকানা ও প্রকল্পের কাগজপত্র</option></select>
              </details>
              <label class="buyer-consent"><input v-model="form.consent" type="checkbox" required><span>এই প্রপার্টি নিয়ে GBREL যেন আমার দেওয়া নম্বরে নির্বাচিত মাধ্যমে যোগাযোগ করে, তাতে সম্মতি দিচ্ছি। <strong>(আবশ্যক)</strong></span></label>
            </fieldset>
            <p v-if="error" class="buyer-error" role="alert">{{ error }}</p>
            <button type="submit" class="buyer-primary" :disabled="sending">{{ sending ? 'অনুরোধ পাঠানো হচ্ছে…' : 'বিস্তারিত জানতে যোগাযোগ চাই' }} <span v-if="!sending" aria-hidden="true">→</span></button>
            <button type="button" class="buyer-back" :disabled="sending" @click="back">← আগের উত্তর পরিবর্তন করি</button>
            <p class="buyer-hint">আপনার উত্তর ও যোগাযোগের তথ্য GBREL-এর লিড তালিকায় সংরক্ষিত হবে। কোনো পেমেন্ট বা বুকিং প্রয়োজন নেই।</p>
          </form>
        </template>
      </section>
    </div>
  </Teleport>
</template>

<script setup lang="ts">
import { computed, nextTick, reactive, ref } from 'vue'
import type { PropertyItem } from '~/composables/useProperties'
import { formatBDT } from '~/composables/useCurrency'
import { useApiUrl } from '~/composables/useApi'
import { useOverlayBehavior } from '~/composables/useOverlayBehavior'
import { normalizePhone, validPhone, savePropertyInquiry } from '~/utils/propertyInquiry.mjs'

const props = defineProps<{ open: boolean; property: PropertyItem; source: string }>()
const emit = defineEmits<{ close: []; saved: [id: number] }>()
const route = useRoute()
const dialogRoot = ref<HTMLElement | null>(null)
useOverlayBehavior(computed(() => props.open), () => emit('close'), dialogRoot)
const step = ref(1)
const sending = ref(false)
const savedId = ref<number | null>(null)
const error = ref('')
const phoneError = ref('')
const requestId = ref('')
const form = reactive({ purpose: '', timeline: '', budget: '', name: '', phone: '', channel: '', time: '', next: '', consent: false })
const purposes = [{ value: 'Own / family use', label: 'নিজের / পরিবারের জন্য' }, { value: 'Investment', label: 'বিনিয়োগের জন্য' }]
const focusStep = async () => { await nextTick(); dialogRoot.value?.querySelector<HTMLElement>('form input, form select')?.focus() }
const advance = () => { step.value = 2; focusStep() }
const back = () => { step.value = 1; error.value = ''; focusStep() }
const submit = async () => {
  if (sending.value || savedId.value) return
  error.value = ''
  if (!form.name.trim()) { error.value = 'অনুগ্রহ করে আপনার নাম লিখুন।'; return }
  if (!validPhone(form.phone)) {
    phoneError.value = 'সঠিক মোবাইল নম্বর দিন, যেমন 01712345678। বিদেশি নম্বরে +দেশের কোড দিন।'
    await nextTick(); dialogRoot.value?.querySelector<HTMLElement>('#buyer-phone')?.focus(); return
  }
  if (!form.purpose || !form.timeline || !form.budget || !form.channel || !form.consent) return
  sending.value = true
  requestId.value ||= crypto.randomUUID()
  const query = (key: string) => String(route.query[key] || '').slice(0, 200) || null
  const message = [
    'Property information & callback request',
    `Preferred time: ${form.time || 'Not specified'}`,
    `Next step: ${form.next || 'Discuss details'}`,
    `Price shown: ${props.property.hidePrice ? 'On request' : formatBDT(props.property.price) + ' ' + (props.property.priceUnit || '')}`,
    `Contact consent: granted for this property via ${form.channel}; ${new Date().toISOString()}; form v1`,
    `CTA: ${props.source}`, `Landing page: ${route.path}`,
    ...(query('utm_content') ? [`Ad content: ${query('utm_content')}`] : []),
    ...(query('utm_term') ? [`Ad term: ${query('utm_term')}`] : []),
  ].join('\n')
  try {
    savedId.value = await savePropertyInquiry(useApiUrl('/leads'), {
      property_id: props.property.id, property_title: props.property.title,
      name: form.name.trim(), phone: normalizePhone(form.phone),
      buyer_category: form.purpose, investment_readiness: form.timeline,
      budget_range: form.budget, preferred_contact: form.channel,
      request_id: requestId.value, form_version: 'property_inquiry_v1', contact_consent: form.consent,
      callback_time: form.time || null, next_step: form.next || null,
      utm_content: query('utm_content'), utm_term: query('utm_term'),
      message, status: 'New', utm_source: query('utm_source'),
      utm_medium: query('utm_medium'), utm_campaign: query('utm_campaign'),
    })
    emit('saved', savedId.value!)
    await nextTick(); dialogRoot.value?.querySelector<HTMLElement>('.buyer-primary')?.focus()
  } catch {
    error.value = 'অনুরোধটি নিশ্চিত করা যায়নি। আপনার তথ্য এখানে আছে—আবার চেষ্টা করুন।'
  } finally { sending.value = false }
}
</script>

<style scoped>
.inquiry-overlay { position: fixed; inset: 0; z-index: 11000; background: #081b26b8; backdrop-filter: blur(5px); display: flex; align-items: center; justify-content: center; padding: 20px; }
.buyer-dialog { position: relative; width: 100%; max-width: 540px; max-height: calc(100dvh - 40px); overflow-y: auto; overscroll-behavior: contain; border-radius: 24px; padding: 32px; background: #fff; color: #152e32; box-shadow: 0 24px 100px #0004; font-family: 'Noto Sans Bengali', sans-serif; line-height: 1.65; }
.buyer-dialog * { box-sizing: border-box; }
.buyer-close { position: sticky; float: right; top: 0; z-index: 2; margin: -12px -12px 0 0; width: 44px; height: 44px; border: 0; border-radius: 50%; background: #eff4f1; color: #203a34; font-size: 28px; cursor: pointer; }
.buyer-eyebrow { color: #477468; font-size: 12px; font-weight: 700; padding-right: 32px; margin-bottom: 12px; }
.buyer-dialog h2 { font-size: 25px; line-height: 1.5; margin: 0 22px 8px 0; }
.buyer-intro { font-size: 14px; color: #53665f; }
.buyer-property { display: grid; gap: 5px; padding: 13px 16px; background: #f3f7f4; border-radius: 12px; margin: 18px 0; font-size: 13px; }
.buyer-property span { color: #116044; font-size: 19px; font-weight: 700; }
.buyer-property small { display: block; font-size: 12px; font-weight: 400; }
.buyer-progress { display: flex; gap: 12px; margin-bottom: 20px; font-size: 13px; color: #627268; }
.buyer-progress span { flex: 1; border-bottom: 3px solid #e5eae7; padding-bottom: 8px; }
.buyer-progress .current { border-color: #14704e; color: #116044; font-weight: 700; }
.buyer-dialog fieldset { border: 0; padding: 0; margin: 0; min-width: 0; }
.buyer-dialog legend, .buyer-label { font-size: 14px; font-weight: 600; margin-bottom: 8px; }
.buyer-label { display: block; margin-top: 16px; }
.buyer-label span, legend span { color: #66756e; font-size: 11px; font-weight: 400; }
.buyer-options { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
.buyer-option { display: flex; gap: 8px; align-items: center; padding: 12px; border: 1px solid #cddbd2; border-radius: 10px; font-size: 13px; cursor: pointer; min-height: 52px; }
.buyer-option.selected { border-color: #14704e; background: #eff9f2; }
.buyer-dialog select, .buyer-dialog input:not([type=radio]):not([type=checkbox]) { width: 100%; min-height: 50px; border: 1px solid #becdc4; border-radius: 10px; padding: 10px 12px; background: #fff; color: #203a34; font: inherit; font-size: 16px; }
.buyer-dialog input[type=radio], .buyer-dialog input[type=checkbox] { width: 20px; height: 20px; flex-shrink: 0; accent-color: #14704e; }
.buyer-primary { display: flex; justify-content: center; align-items: center; gap: 14px; width: 100%; min-height: 54px; margin-top: 18px; padding: 13px 18px; border: 0; border-radius: 12px; background: #126448; color: #fff; font: inherit; font-weight: 700; cursor: pointer; }
.buyer-primary:hover { background: #094e36; }
.buyer-primary:disabled, .buyer-back:disabled { opacity: .65; cursor: wait; }
.buyer-dialog :focus-visible { outline: 3px solid #c08b21; outline-offset: 3px; }
.buyer-hint { display: block; font-size: 12px; color: #627268; margin-top: 12px; }
.buyer-consent { display: flex; align-items: flex-start; gap: 10px; margin: 18px 0; font-size: 12px; }
.buyer-consent input { margin-top: 3px; }
.buyer-error { border-left: 3px solid #b73131; background: #fff0ef; padding: 10px; color: #922121; font-size: 14px; margin: 12px 0; }
.buyer-extra { margin-top: 18px; font-size: 13px; }
.buyer-extra summary { cursor: pointer; padding: 10px 0; min-height: 44px; }
.buyer-back { display: block; margin: 8px auto 0; min-height: 44px; padding: 8px; border: 0; background: transparent; color: #386149; font: inherit; font-size: 13px; cursor: pointer; }
.buyer-success { padding-top: 14px; }
.buyer-success p { margin: 12px 0; }
.buyer-success-icon { display: grid; place-items: center; width: 60px; height: 60px; border-radius: 50%; background: #e4f5e9; color: #126448; font-size: 30px; margin-bottom: 20px; }
.buyer-summary { background: #f3f7f4; border-radius: 12px; padding: 16px; overflow-wrap: anywhere; }
.sr-only { position: absolute; width: 1px; height: 1px; overflow: hidden; clip-path: inset(50%); }
@media (max-width: 600px) { .inquiry-overlay { padding: 8px; align-items: flex-end; } .buyer-dialog { padding: 24px 18px max(20px, env(safe-area-inset-bottom)); border-radius: 20px; max-height: calc(100dvh - 16px); } .buyer-dialog h2 { font-size: 23px; } .buyer-option { padding: 10px 8px; } }
@media (prefers-reduced-motion: reduce) { * { scroll-behavior: auto !important; } }
</style>

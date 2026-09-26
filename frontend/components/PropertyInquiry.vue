<template>
  <Teleport to="body">
    <div v-if="open" ref="dialogRoot" class="sv-overlay" @click.self="emit('close')">
      <section class="sv" role="dialog" aria-modal="true" aria-labelledby="sv-title" lang="bn">
        <header class="sv-top">
          <div class="sv-prop">
            <strong>{{ property.title }}</strong>
            <span>{{ priceText }}</span>
          </div>
          <button class="sv-close" type="button" aria-label="ফর্ম বন্ধ করুন" @click="emit('close')"><X :size="22" /></button>
        </header>

        <!-- Progress: one field band per step, light to dark like the home page -->
        <div v-if="!savedId" class="sv-progress" role="progressbar" :aria-valuenow="step + 1" aria-valuemin="1" :aria-valuemax="totalSteps" :aria-label="`ধাপ ${toBn(step + 1)} / ${toBn(totalSteps)}`">
          <span v-for="n in totalSteps" :key="n" :class="{ done: n - 1 < step, now: n - 1 === step }" :style="{ '--band': bandColors[n - 1] }"></span>
        </div>

        <!-- Success -->
        <div v-if="savedId" class="sv-done" role="status">
          <h2 id="sv-title" tabindex="-1">{{ tier === 'HOT' ? 'চলুন দ্রুত কথা বলি' : 'আপনার অনুরোধ পেয়েছি' }}</h2>
          <p v-if="tier === 'HOT'">আপনি শিগগিরই কিনতে চান, তাই আপনার অনুরোধ আমাদের কল তালিকার শুরুতে রাখা হয়েছে। অপেক্ষা না করে এখনই WhatsApp-এ কথা শুরু করতে পারেন।</p>
          <p v-else>GBREL টিম আপনার উত্তর দেখে {{ form.channel === 'WhatsApp' ? 'WhatsApp-এ' : 'ফোনে' }} যোগাযোগ করবে। এর মধ্যে প্রপার্টির কাগজপত্র আর শর্তগুলো দেখে রাখতে পারেন।</p>
          <dl class="sv-summary">
            <div><dt>অনুরোধ নম্বর</dt><dd>#{{ toBn(savedId) }}</dd></div>
            <div><dt>নাম ও নম্বর</dt><dd>{{ form.name }}, {{ normalizePhone(form.phone) }}</dd></div>
            <div><dt>কবে কিনতে চান</dt><dd>{{ labelFor(questions, 'timeline', answers.timeline) }}</dd></div>
          </dl>
          <a v-if="whatsappLink" :href="whatsappLink" target="_blank" rel="noopener noreferrer" class="sv-btn sv-btn--wa" @click="track('Contact', { method: 'WhatsApp', placement: 'survey_success' })">
            WhatsApp-এ কথা শুরু করুন
          </a>
          <button type="button" class="sv-btn" :class="whatsappLink ? 'sv-btn--line' : 'sv-btn--go'" @click="emit('close')">প্রপার্টির পেজে ফিরে যাই</button>
          <p class="sv-fine">এটি শুধু তথ্য ও যোগাযোগের অনুরোধ। কোনো বুকিং বা পেমেন্ট হয়নি।</p>
        </div>

        <!-- Question screens -->
        <form v-else-if="step < questions.length" class="sv-q" @submit.prevent>
          <fieldset>
            <legend id="sv-title" class="sv-title">{{ current.title }}</legend>
            <p v-if="current.help" class="sv-help">{{ current.help }}</p>
            <div class="sv-options">
              <label v-for="option in current.options" :key="option.value" class="sv-option" :class="{ picked: answers[current.key] === option.value }">
                <input type="radio" :name="current.key" :value="option.value" :checked="answers[current.key] === option.value" @change="choose(option.value)" />
                <span>{{ option.label }}</span>
                <Check v-if="answers[current.key] === option.value" :size="20" aria-hidden="true" />
              </label>
            </div>
          </fieldset>
          <div class="sv-nav">
            <button v-if="step > 0" type="button" class="sv-back" @click="back"><ChevronLeft :size="18" aria-hidden="true" /> আগের প্রশ্ন</button>
            <span class="sv-count">{{ toBn(step + 1) }} / {{ toBn(totalSteps) }}</span>
          </div>
        </form>

        <!-- Contact screen -->
        <form v-else class="sv-contact" novalidate :aria-busy="sending" @submit.prevent="submit">
          <h2 id="sv-title" class="sv-title">কোন নম্বরে যোগাযোগ করব?</h2>
          <p class="sv-help">{{ settings.property_cta_note || 'আপনার উত্তর অনুযায়ী দাম, কিস্তি আর সাইট ভিজিট নিয়ে কথা বলব।' }}</p>
          <fieldset :disabled="sending">
            <label class="sv-field">
              <span>আপনার নাম</span>
              <input id="sv-name" v-model="form.name" autocomplete="name" maxlength="100" required />
            </label>
            <label class="sv-field">
              <span>মোবাইল নম্বর</span>
              <input id="sv-phone" v-model="form.phone" type="tel" inputmode="tel" autocomplete="tel" maxlength="25" required placeholder="01XXXXXXXXX" :aria-invalid="!!phoneError" aria-describedby="sv-phone-hint" @input="phoneError = ''" />
              <small id="sv-phone-hint">{{ answers.residence === 'Abroad (NRB)' ? 'বিদেশের নম্বর হলে দেশের কোডসহ লিখুন, যেমন +971…' : 'বিদেশে থাকলে দেশের কোডসহ নম্বর দিন।' }}</small>
            </label>
            <div class="sv-field">
              <span id="sv-channel-label">কীভাবে যোগাযোগ করলে সুবিধা?</span>
              <div class="sv-chips" role="radiogroup" aria-labelledby="sv-channel-label">
                <label v-for="c in channels" :key="c.value" class="sv-chip" :class="{ picked: form.channel === c.value }">
                  <input v-model="form.channel" type="radio" name="channel" :value="c.value" />{{ c.label }}
                </label>
              </div>
            </div>
            <div class="sv-field">
              <span id="sv-time-label">কখন কথা বলা সুবিধা? <small>(ঐচ্ছিক)</small></span>
              <div class="sv-chips" role="radiogroup" aria-labelledby="sv-time-label">
                <label v-for="t in times" :key="t" class="sv-chip" :class="{ picked: form.time === t }">
                  <input v-model="form.time" type="radio" name="time" :value="t" />{{ t }}
                </label>
              </div>
            </div>
            <label class="sv-consent">
              <input v-model="form.consent" type="checkbox" required />
              <span>এই প্রপার্টি নিয়ে GBREL আমার দেওয়া নম্বরে যোগাযোগ করতে পারে।</span>
            </label>
          </fieldset>
          <p v-if="phoneError || error" class="sv-error" role="alert">{{ phoneError || error }}</p>
          <button type="submit" class="sv-btn sv-btn--go" :disabled="sending">{{ sending ? 'পাঠানো হচ্ছে…' : ctaLabel }}</button>
          <div class="sv-nav">
            <button type="button" class="sv-back" :disabled="sending" @click="back"><ChevronLeft :size="18" aria-hidden="true" /> আগের প্রশ্ন</button>
            <span class="sv-count">{{ toBn(totalSteps) }} / {{ toBn(totalSteps) }}</span>
          </div>
          <p class="sv-fine">কোনো পেমেন্ট বা বুকিং লাগবে না। আপনার নম্বর শুধু এই প্রপার্টির বিষয়ে ব্যবহার হবে।</p>
        </form>
      </section>
    </div>
  </Teleport>
</template>

<script setup lang="ts">
import { computed, nextTick, reactive, ref, watch } from 'vue'
import { Check, ChevronLeft, X } from 'lucide-vue-next'
import type { PropertyItem } from '~/composables/useProperties'
import { formatBDT } from '~/composables/useCurrency'
import { useApiUrl } from '~/composables/useApi'
import { useOverlayBehavior } from '~/composables/useOverlayBehavior'
import { askingPriceSummary } from '~/utils/buyerDetails.mjs'
import { normalizePhone, validPhone, savePropertyInquiry } from '~/utils/propertyInquiry.mjs'
import { buildQuestions, labelFor, scoreLead, type Answers } from '~/utils/leadSurvey'
import { priceBn, toBn } from '~/utils/propertyLabels'
import { trackPixel } from '~/utils/metaPixel'

const props = defineProps<{ open: boolean; property: PropertyItem; source: string; startPurpose?: string; whatsapp?: string }>()
const emit = defineEmits<{ close: []; saved: [id: number] }>()
const route = useRoute()
const dialogRoot = ref<HTMLElement | null>(null)
useOverlayBehavior(computed(() => props.open), () => emit('close'), dialogRoot)

const { settings } = useSettings()
const ctaLabel = computed(() => props.property.hidePrice ? settings.value.property_cta_label_hidden_price : settings.value.property_cta_label)
const questions = computed(() => buildQuestions(!!props.property.hidePrice, props.property.propertyType))
const totalSteps = computed(() => questions.value.length + 1)
const bandColors = ['#B9D08F', '#9DBE73', '#7FA85A', '#5C924A', '#3A7234', '#1D4A2A']
const channels = [{ value: 'WhatsApp', label: 'WhatsApp-এ মেসেজ' }, { value: 'Phone Call', label: 'ফোন কল' }]
const times = ['সকাল ১০টা–১টা', 'দুপুর ১টা–৫টা', 'সন্ধ্যা ৫টা–রাত ৯টা']

const step = ref(0)
const answers = reactive<Answers>({})
const form = reactive({ name: '', phone: '', channel: 'WhatsApp', time: '', consent: false })
const sending = ref(false)
const savedId = ref<number | null>(null)
const error = ref('')
const phoneError = ref('')
const requestId = ref('')
const started = ref(false)

const current = computed(() => questions.value[step.value])
const score = computed(() => scoreLead(questions.value, answers))
const tier = computed(() => score.value.tier)
const priceText = computed(() => props.property.hidePrice ? (props.property.priceDisplayText || 'দাম জানতে অনুরোধ করুন') : priceBn(askingPriceSummary(props.property).amount ?? 0))

const track = (event: string, extra: Record<string, unknown> = {}, eventID?: string) =>
  trackPixel(event, { content_ids: [String(props.property.id)], content_type: 'product', content_name: props.property.title, ...extra }, eventID)

const focusStep = async () => {
  await nextTick()
  dialogRoot.value?.querySelector<HTMLElement>('.sv-option input:checked, .sv-option input, #sv-name')?.focus()
}

const markStarted = () => {
  if (started.value) return
  started.value = true
  track('SurveyStart', { cta: props.source })
}

// Opening from the inline first question: keep that answer and continue at question 2.
watch(() => props.open, isOpen => {
  if (!isOpen || savedId.value) return
  if (props.startPurpose && !answers.purpose) { answers.purpose = props.startPurpose; step.value = 1; markStarted() }
  focusStep()
}, { immediate: true })

let advanceTimer: ReturnType<typeof setTimeout> | undefined
const choose = (value: string) => {
  answers[current.value.key] = value
  markStarted()
  clearTimeout(advanceTimer)
  // A short pause lets the person see their tap register before the next question.
  advanceTimer = setTimeout(() => { step.value++; focusStep() }, 220)
}
const back = () => { clearTimeout(advanceTimer); error.value = ''; step.value = Math.max(0, step.value - 1); focusStep() }

const submit = async () => {
  if (sending.value || savedId.value) return
  error.value = ''
  if (!form.name.trim()) { error.value = 'আপনার নাম লিখুন।'; dialogRoot.value?.querySelector<HTMLElement>('#sv-name')?.focus(); return }
  if (!validPhone(form.phone)) {
    phoneError.value = 'সঠিক মোবাইল নম্বর দিন, যেমন 01712345678। বিদেশের নম্বরে দেশের কোড (+…) দিন।'
    dialogRoot.value?.querySelector<HTMLElement>('#sv-phone')?.focus(); return
  }
  if (!form.consent) { error.value = 'যোগাযোগের অনুমতির ঘরে টিক দিন।'; return }
  const missing = questions.value.findIndex(q => !answers[q.key])
  if (missing >= 0) { step.value = missing; focusStep(); return }

  sending.value = true
  requestId.value ||= crypto.randomUUID()
  const q = (key: string) => String(route.query[key] || '').slice(0, 200) || null
  const s = score.value
  const message = [
    `Lead score: ${s.tier} (${s.points}/${s.max})`,
    `Payment plan: ${answers.payment}`,
    `Lives: ${answers.residence}`,
    `Preferred time (Bangladesh): ${form.time || 'Not specified'}`,
    `Price shown: ${props.property.hidePrice ? 'On request' : formatBDT(props.property.price) + ' ' + (props.property.priceUnit || '')}`,
    `Contact consent: granted for this property via ${form.channel}; ${new Date().toISOString()}; survey v2`,
    `CTA: ${props.source}`, `Landing page: ${route.path}`,
    ...(q('fbclid') ? ['Came from a Facebook ad click'] : []),
    ...(q('utm_content') ? [`Ad content: ${q('utm_content')}`] : []),
    ...(q('utm_term') ? [`Ad term: ${q('utm_term')}`] : [])
  ].join('\n')

  try {
    savedId.value = await savePropertyInquiry(useApiUrl('/leads'), {
      property_id: props.property.id, property_title: props.property.title,
      name: form.name.trim(), phone: normalizePhone(form.phone),
      buyer_category: answers.purpose, investment_readiness: answers.timeline,
      budget_range: answers.budget, preferred_contact: form.channel,
      request_id: requestId.value, form_version: 'property_inquiry_v1', contact_consent: form.consent,
      callback_time: form.time || null,
      next_step: `${s.tier} lead — ${answers.residence === 'Abroad (NRB)' ? 'video call walkthrough' : 'site visit'}`,
      message, status: 'New',
      utm_source: q('utm_source'), utm_medium: q('utm_medium'), utm_campaign: q('utm_campaign'),
      utm_content: q('utm_content'), utm_term: q('utm_term')
    })
    // Same event ID as the stored request, so a future server-side (Conversions API) event deduplicates.
    track('Lead', { lead_tier: s.tier, lead_score: s.points, cta: props.source }, requestId.value)
    if (s.tier === 'HOT') track('QualifiedLead', { lead_score: s.points }, `${requestId.value}-q`)
    emit('saved', savedId.value!)
    await nextTick(); dialogRoot.value?.querySelector<HTMLElement>('#sv-title')?.focus()
  } catch {
    error.value = 'অনুরোধটি পাঠানো যায়নি। আপনার উত্তরগুলো রাখা আছে — আবার চেষ্টা করুন।'
  } finally { sending.value = false }
}

const whatsappLink = computed(() => {
  const n = (props.whatsapp || '').replace(/\D/g, '')
  if (!n) return ''
  const text = [
    `আসসালামু আলাইকুম, আমি ${form.name}।`,
    `"${props.property.title}" (GBR-${props.property.id}) নিয়ে কথা বলতে চাই।`,
    `কবে কিনতে চাই: ${labelFor(questions.value, 'timeline', answers.timeline)}`,
    `অনুরোধ নম্বর: #${savedId.value}`
  ].join('\n')
  return `https://wa.me/${n}?text=${encodeURIComponent(text)}`
})
</script>

<style scoped>
.sv-overlay { position: fixed; inset: 0; z-index: 11000; background: rgba(19, 53, 32, .55); backdrop-filter: blur(4px); display: flex; align-items: center; justify-content: center; padding: 20px; }
.sv { position: relative; width: 100%; max-width: 520px; max-height: calc(100dvh - 40px); overflow-y: auto; overscroll-behavior: contain; border-radius: 22px; background: #FBFCF7; color: #16241A; font-family: 'Noto Sans Bengali', sans-serif; line-height: 1.65; box-shadow: 0 30px 80px -30px rgba(19, 53, 32, .6); }
.sv * { box-sizing: border-box; }
.sv :focus-visible { outline: 3px solid #E2651C; outline-offset: 3px; }

.sv-top { display: flex; align-items: flex-start; gap: 12px; padding: 18px 18px 14px 24px; border-bottom: 1px solid #D6DDCB; position: sticky; top: 0; background: #FBFCF7; z-index: 2; }
.sv-prop { flex: 1; min-width: 0; display: flex; flex-direction: column; }
.sv-prop strong { font-size: 14px; font-weight: 600; color: #1D4A2A; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.sv-prop span { font-family: 'Anek Bangla', 'Noto Sans Bengali', sans-serif; font-size: 18px; font-weight: 700; color: #16241A; }
.sv-close { width: 44px; height: 44px; flex-shrink: 0; border-radius: 50%; border: 0; background: #EDF1E6; color: #1D4A2A; display: grid; place-items: center; cursor: pointer; }

.sv-progress { display: flex; gap: 4px; padding: 16px 24px 0; }
.sv-progress span { flex: 1; height: 6px; border-radius: 3px; background: #E3E8DA; transition: background-color .3s ease; }
.sv-progress span.done, .sv-progress span.now { background: var(--band); }
.sv-progress span.now { box-shadow: 0 0 0 2px #FBFCF7, 0 0 0 3px var(--band); }

.sv-q, .sv-contact, .sv-done { padding: 22px 24px 24px; }
.sv fieldset { border: 0; padding: 0; margin: 0; min-width: 0; }
.sv-title { font-family: 'Anek Bangla', 'Noto Sans Bengali', sans-serif; font-size: 26px; font-weight: 700; font-stretch: 106%; line-height: 1.3; color: #1D4A2A; margin: 0 0 6px; padding: 0; }
.sv-help { font-size: 14px; color: #4A5A4E; margin: 0 0 4px; }
.sv-options { display: flex; flex-direction: column; gap: 10px; margin-top: 18px; }
.sv-option { position: relative; display: flex; align-items: center; justify-content: space-between; gap: 12px; min-height: 60px; padding: 14px 18px; border: 1.5px solid #D6DDCB; border-radius: 14px; background: #fff; cursor: pointer; font-size: 17px; font-weight: 500; transition: border-color .15s ease, background-color .15s ease; }
.sv-option:hover { border-color: #3F7A35; }
.sv-option input { position: absolute; opacity: 0; width: 1px; height: 1px; }
.sv-option:focus-within { outline: 3px solid #E2651C; outline-offset: 2px; }
.sv-option.picked { border-color: #1D4A2A; background: #E9F1DD; color: #1D4A2A; }
.sv-option svg { color: #1D4A2A; flex-shrink: 0; }

.sv-nav { display: flex; align-items: center; justify-content: space-between; margin-top: 16px; min-height: 44px; }
.sv-back { display: inline-flex; align-items: center; gap: 4px; min-height: 44px; padding: 8px 4px; border: 0; background: transparent; color: #3F7A35; font: inherit; font-size: 14px; cursor: pointer; }
.sv-count { margin-left: auto; font-size: 13px; color: #6B786E; }

.sv-contact fieldset { display: flex; flex-direction: column; gap: 16px; margin-top: 18px; }
.sv-field { display: flex; flex-direction: column; gap: 6px; }
.sv-field > span { font-size: 14px; font-weight: 600; color: #1D4A2A; }
.sv-field > span small { font-weight: 400; color: #6B786E; }
.sv-field > small { font-size: 12px; color: #6B786E; }
.sv-field input { width: 100%; min-height: 52px; border: 1.5px solid #D6DDCB; border-radius: 12px; padding: 10px 14px; background: #fff; color: #16241A; font: inherit; font-size: 17px; }
.sv-field input:focus { border-color: #3F7A35; outline: none; box-shadow: 0 0 0 3px rgba(63, 122, 53, .18); }
.sv-field input[aria-invalid="true"] { border-color: #B8441A; }
.sv-chips { display: flex; flex-wrap: wrap; gap: 8px; }
.sv-chip { position: relative; display: inline-flex; align-items: center; min-height: 44px; padding: 8px 16px; border: 1.5px solid #D6DDCB; border-radius: 999px; background: #fff; font-size: 15px; cursor: pointer; }
.sv-chip input { position: absolute; opacity: 0; width: 1px; height: 1px; }
.sv-chip:focus-within { outline: 3px solid #E2651C; outline-offset: 2px; }
.sv-chip.picked { border-color: #1D4A2A; background: #1D4A2A; color: #fff; }
.sv-consent { display: flex; align-items: flex-start; gap: 10px; font-size: 14px; cursor: pointer; }
.sv-consent input { width: 20px; height: 20px; margin-top: 3px; accent-color: #1D4A2A; flex-shrink: 0; }
.sv-error { margin: 14px 0 0; padding: 10px 12px; border-radius: 10px; background: #FBE9DF; color: #8A3A0F; font-size: 14px; }

.sv-btn { display: flex; align-items: center; justify-content: center; width: 100%; min-height: 56px; margin-top: 18px; padding: 12px 20px; border-radius: 999px; font-family: 'Anek Bangla', 'Noto Sans Bengali', sans-serif; font-size: 18px; font-weight: 600; text-decoration: none; cursor: pointer; border: 1.5px solid transparent; }
.sv-btn--go { background: #E2651C; color: #fff; }
.sv-btn--go:hover { background: #C2530F; }
.sv-btn--go:disabled { opacity: .7; cursor: wait; }
.sv-btn--wa { background: #1FA855; color: #fff; }
.sv-btn--wa:hover { background: #178A45; }
.sv-btn--line { background: transparent; color: #1D4A2A; border-color: #1D4A2A; margin-top: 10px; }
.sv-fine { font-size: 12px; color: #6B786E; margin: 12px 0 0; text-align: center; }

.sv-done h2 { font-family: 'Anek Bangla', 'Noto Sans Bengali', sans-serif; font-size: 28px; font-weight: 700; color: #1D4A2A; margin: 0 0 10px; }
.sv-done h2:focus { outline: none; }
.sv-done > p { font-size: 15px; color: #4A5A4E; }
.sv-summary { margin: 18px 0 0; border-top: 1px solid #D6DDCB; }
.sv-summary div { display: flex; justify-content: space-between; gap: 16px; padding: 10px 0; border-bottom: 1px solid #D6DDCB; font-size: 14px; }
.sv-summary dt { color: #6B786E; }
.sv-summary dd { margin: 0; text-align: right; font-weight: 600; overflow-wrap: anywhere; }

@media (max-width: 600px) {
  .sv-overlay { padding: 0; align-items: flex-end; }
  .sv { max-height: 94dvh; border-radius: 22px 22px 0 0; padding-bottom: env(safe-area-inset-bottom); }
  .sv-title { font-size: 23px; }
  .sv-option { font-size: 16px; min-height: 56px; }
}
@media (prefers-reduced-motion: reduce) { .sv * { transition: none !important; } }
</style>

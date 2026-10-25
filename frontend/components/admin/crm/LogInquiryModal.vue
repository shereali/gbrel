<template>
  <div class="admin-modal-overlay li-overlay" @click.self="emit('close')">
    <div ref="card" class="admin-modal-card li-card" role="dialog" aria-modal="true" aria-labelledby="li-title">
      <div class="admin-modal-header">
        <h3 id="li-title" class="admin-modal-title">Log Buyer Inquiry</h3>
        <button type="button" class="admin-modal-close" aria-label="Close" @click="emit('close')">✕</button>
      </div>

      <form novalidate @submit.prevent="save(false)">
        <div class="admin-modal-body li-body">
          <!-- 1. Who -->
          <fieldset class="li-group">
            <legend>ক্রেতা</legend>
            <div class="li-grid">
              <label class="li-field">
                <span>নাম *</span>
                <input v-model.trim="form.name" type="text" autocomplete="off" required maxlength="100" placeholder="যেমন: রহিম উদ্দিন" class="form-input" :aria-invalid="!!errors.name" />
                <small v-if="errors.name" class="li-err">{{ errors.name }}</small>
              </label>
              <label class="li-field">
                <span>মোবাইল / WhatsApp *</span>
                <input v-model.trim="form.phone" type="tel" inputmode="tel" autocomplete="off" required placeholder="01712345678" class="form-input" :aria-invalid="!!errors.phone" />
                <small v-if="errors.phone" class="li-err">{{ errors.phone }}</small>
                <small v-else-if="knownLead" class="li-warn">
                  এই নম্বরে আগে থেকেই লিড আছে: <strong>{{ knownLead.name }}</strong> ({{ knownLead.stage || knownLead.status }})
                  <button type="button" class="li-link" @click="emit('open', knownLead.id)">খুলুন</button>
                </small>
              </label>
            </div>
            <label class="li-field">
              <span>ইমেইল <em>(ঐচ্ছিক)</em></span>
              <input v-model.trim="form.email" type="email" autocomplete="off" placeholder="name@example.com" class="form-input" :aria-invalid="!!errors.email" />
              <small v-if="errors.email" class="li-err">{{ errors.email }}</small>
            </label>
          </fieldset>

          <!-- 2. Where from and what for -->
          <fieldset class="li-group">
            <legend>কোথা থেকে এসেছে *</legend>
            <div class="li-chips" role="radiogroup" aria-label="কোথা থেকে এসেছে">
              <label v-for="c in CHANNELS" :key="c.value" class="li-chip" :class="{ on: form.channel === c.value }">
                <input v-model="form.channel" type="radio" name="li-channel" :value="c.value" />{{ c.label }}
              </label>
            </div>
            <small v-if="errors.channel" class="li-err">{{ errors.channel }}</small>

            <label class="li-field">
              <span>কোন প্রপার্টি</span>
              <select v-model="form.propertyChoice" class="form-select">
                <option value="">— বাছাই করুন —</option>
                <option v-for="p in propertyOptions" :key="p.id" :value="String(p.id)">{{ p.label }}</option>
                <option value="other">তালিকায় নেই / সাধারণ খোঁজ</option>
              </select>
            </label>
            <label v-if="form.propertyChoice === 'other'" class="li-field">
              <span>কী খুঁজছেন</span>
              <input v-model.trim="form.propertyTitle" type="text" maxlength="255" placeholder="যেমন: উত্তরায় ৩ বেডের ফ্ল্যাট" class="form-input" />
            </label>
          </fieldset>

          <!-- 3. The same questions the website asks, so the lead gets the same Hot / Warm / Cold score -->
          <fieldset class="li-group">
            <legend>
              ক্রেতার উত্তর <em>(যা জেনেছেন)</em>
              <span class="li-score" :data-tier="answered ? score.tier : 'none'">{{ answered ? `${tierName} · ${score.points}/${score.max}` : 'Unscored' }}</span>
            </legend>
            <div class="li-grid">
              <label v-for="q in questions" :key="q.key" class="li-field">
                <span>{{ q.title }}</span>
                <select v-model="answers[q.key]" class="form-select">
                  <option value="">জানা নেই</option>
                  <option v-for="o in q.options" :key="o.value" :value="o.value">{{ o.label }}</option>
                </select>
              </label>
            </div>
            <div class="li-grid">
              <label class="li-field">
                <span>কখন কল করলে সুবিধা</span>
                <select v-model="form.callbackTime" class="form-select">
                  <option value="">জানা নেই</option>
                  <option v-for="t in CALL_TIMES" :key="t" :value="t">{{ t }}</option>
                </select>
              </label>
              <label class="li-field">
                <span>কীভাবে যোগাযোগ পছন্দ</span>
                <select v-model="form.preferredContact" class="form-select">
                  <option value="">চ্যানেল অনুযায়ী</option>
                  <option value="Phone Call">ফোন কল</option>
                  <option value="WhatsApp">WhatsApp</option>
                  <option value="Email">ইমেইল</option>
                </select>
              </label>
            </div>
          </fieldset>

          <!-- 4. Who follows up, and when -->
          <fieldset class="li-group">
            <legend>দায়িত্ব ও ফলো-আপ</legend>
            <label class="li-field">
              <span>কার দায়িত্বে</span>
              <select v-model="form.assignedTo" class="form-select">
                <option :value="null">কাউকে না</option>
                <option v-for="s in staff" :key="s.id" :value="s.id">{{ s.name }}{{ s.id === meId ? ' (আমি)' : '' }}</option>
              </select>
            </label>
            <div class="li-field">
              <span>পরের ফলো-আপ</span>
              <div class="li-chips">
                <button v-for="f in followUpChoices" :key="f.label" type="button" class="li-chip" :class="{ on: form.followUp === f.value }" @click="form.followUp = f.value">{{ f.label }}</button>
              </div>
            </div>
            <label class="li-field">
              <span>নোট</span>
              <textarea v-model="form.notes" rows="3" maxlength="2000" placeholder="কী বললেন, কী চান, বাজেট, পরের কাজ…" class="form-textarea"></textarea>
            </label>
          </fieldset>

          <!-- Same number already in the CRM (answered by the server) -->
          <div v-if="duplicate" class="li-dup" role="alert">
            <p><strong>এই নম্বরে আগে থেকেই একটা লিড আছে:</strong> {{ duplicate.name }} · {{ duplicate.status }}<template v-if="duplicate.property_title"> · {{ duplicate.property_title }}</template><template v-if="duplicate.deleted"> (Trash-এ আছে)</template></p>
            <div class="li-dup-actions">
              <button v-if="!duplicate.deleted" type="button" class="btn btn-sm btn-emerald" @click="emit('open', duplicate.id)">আগের লিডটা খুলুন</button>
              <button type="button" class="btn btn-sm btn-outline-white" :disabled="saving" @click="save(true)">তবুও নতুন লিড সেভ করুন</button>
            </div>
          </div>
          <p v-if="errors.general" class="li-err li-general" role="alert">{{ errors.general }}</p>
        </div>

        <div class="admin-modal-footer">
          <button type="button" class="btn btn-sm btn-outline-white" @click="emit('close')">বাতিল</button>
          <button type="submit" class="btn btn-sm btn-emerald" :disabled="saving">{{ saving ? 'সেভ হচ্ছে…' : 'লিড সেভ করুন' }}</button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, nextTick, onMounted, reactive, ref, watch } from 'vue'
import { useApiUrl } from '~/composables/useApi'
import { useAuth } from '~/composables/useAuth'
import { useProperties } from '~/composables/useProperties'
import { inDays, type StaffMember } from '~/composables/useLeadCrm'
import { toLatinDigits } from '~/composables/useVoiceSearch'
import { buildQuestions, scoreLead, type Answers } from '~/utils/leadSurvey'

const props = defineProps<{ staff: StaffMember[]; leads: any[]; meId?: number | null }>()
const emit = defineEmits<{ close: []; saved: [lead: any]; open: [id: number] }>()

// Stored in English (the CRM filters and reports use these values); shown in Bangla.
const CHANNELS = [
  { value: 'Phone call', label: 'ফোন কল' },
  { value: 'Messenger', label: 'Messenger' },
  { value: 'WhatsApp', label: 'WhatsApp' },
  { value: 'Walk-in', label: 'অফিসে এসেছেন' },
  { value: 'Referral', label: 'রেফারেল' },
  { value: 'Facebook comment', label: 'ফেসবুক কমেন্ট' },
  { value: 'Other', label: 'অন্যান্য' }
]
// Same choices as the website form, so highlights and the answer filter group them together.
const CALL_TIMES = ['যত দ্রুত সম্ভব (আজই)', 'সকাল ১০টা–১টা', 'দুপুর ১টা–৫টা', 'সন্ধ্যা ৫টা–রাত ৯টা']

const { token } = useAuth()
const { properties, fetchProperties } = useProperties()
const card = ref<HTMLElement | null>(null)

const form = reactive({
  name: '', phone: '', email: '', channel: 'Phone call',
  propertyChoice: '', propertyTitle: '',
  callbackTime: '', preferredContact: '',
  assignedTo: (props.meId && props.staff.some(s => s.id === props.meId) ? props.meId : null) as number | null,
  followUp: inDays(1, 11) as string | null,
  notes: ''
})
const answers = reactive<Answers>({})
const errors = reactive<Record<string, string>>({})
const saving = ref(false)
const duplicate = ref<any | null>(null)

const followUpChoices = [
  { label: 'আজ সন্ধ্যা', value: inDays(0, 18) },
  { label: 'আগামীকাল সকাল', value: inDays(1, 11) },
  { label: '৩ দিন পর', value: inDays(3, 11) },
  { label: '১ সপ্তাহ পর', value: inDays(7, 11) },
  { label: 'দরকার নেই', value: null }
]

const propertyOptions = computed(() => [...(properties.value || [])]
  .filter((p: any) => p.status !== 'Delisted' && p.status !== 'Draft')
  .map((p: any) => ({ id: p.id, label: p.areaName ? `${p.title} — ${p.areaName}` : p.title, hidePrice: !!p.hidePrice, type: p.propertyType }))
  .sort((a, b) => a.label.localeCompare(b.label)))
const pickedProperty = computed(() => propertyOptions.value.find(p => String(p.id) === form.propertyChoice) || null)

const questions = computed(() => buildQuestions(pickedProperty.value?.hidePrice ?? false, pickedProperty.value?.type))
const answered = computed(() => questions.value.filter(q => answers[q.key]).length)
const score = computed(() => scoreLead(questions.value, answers))
const tierName = computed(() => score.value.tier.charAt(0) + score.value.tier.slice(1).toLowerCase())

// Drop an answer the newly picked property no longer offers (price hidden or not changes the budget choices).
watch(questions, qs => qs.forEach(q => { if (answers[q.key] && !q.options.some(o => o.value === answers[q.key])) answers[q.key] = '' }))

// Warn while typing when the number is already in the list that is loaded.
const digits = (v: string) => toLatinDigits(String(v || '')).replace(/\D/g, '').replace(/^(?:00)?880?/, '0').slice(-11)
const knownLead = computed(() => {
  const d = digits(form.phone)
  return d.length >= 11 ? props.leads.find(l => digits(l.phone) === d) || null : null
})
watch(() => form.phone, () => { duplicate.value = null })

const clearErrors = () => Object.keys(errors).forEach(k => delete errors[k])

const save = async (allowDuplicate: boolean) => {
  if (saving.value) return
  clearErrors()
  if (!form.name) errors.name = 'ক্রেতার নাম লিখুন।'
  if (!form.phone) errors.phone = 'মোবাইল নম্বর লিখুন।'
  if (Object.keys(errors).length) { focusFirstError(); return }

  saving.value = true
  try {
    const res = await fetch(useApiUrl('/leads/manual'), {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', Accept: 'application/json', Authorization: `Bearer ${token.value}` },
      body: JSON.stringify({
        name: form.name, phone: form.phone, email: form.email || null, channel: form.channel,
        property_id: pickedProperty.value?.id ?? null,
        property_title: form.propertyChoice === 'other' ? form.propertyTitle || null : null,
        buyer_category: answers.purpose || null, investment_readiness: answers.timeline || null, budget_range: answers.budget || null,
        payment: answers.payment || null, residence: answers.residence || null,
        lead_score: answered.value ? `${score.value.tier} (${score.value.points}/${score.value.max})` : null,
        answered: answered.value, total: questions.value.length,
        callback_time: form.callbackTime || null, preferred_contact: form.preferredContact || null,
        assigned_to: form.assignedTo, follow_up_at: form.followUp, notes: form.notes.trim() || null,
        allow_duplicate: allowDuplicate
      })
    })
    const json = await res.json().catch(() => ({}))
    if (res.status === 409) { duplicate.value = json?.data?.duplicate || null; await nextTick(); card.value?.querySelector('.li-dup')?.scrollIntoView({ block: 'nearest' }); return }
    if (res.status === 422) {
      for (const [key, messages] of Object.entries(json?.errors || {})) errors[key] = (messages as string[])[0]
      if (!Object.keys(errors).length) errors.general = json?.message || 'কিছু তথ্য ঠিক নেই।'
      focusFirstError(); return
    }
    if (res.status === 401 || res.status === 403) { errors.general = 'লিড যোগ করার অনুমতি নেই। আবার লগইন করুন বা অ্যাডমিনকে বলুন।'; return }
    if (!res.ok || !json?.success) { errors.general = json?.message || 'সেভ করা যায়নি। আবার চেষ্টা করুন।'; return }
    emit('saved', json.data)
  } catch {
    errors.general = 'ইন্টারনেট সংযোগ দেখে আবার চেষ্টা করুন।'
  } finally {
    saving.value = false
  }
}

const focusFirstError = () => nextTick(() => card.value?.querySelector<HTMLElement>('[aria-invalid="true"], .li-err')?.focus?.())

onMounted(() => {
  if (!properties.value?.length) fetchProperties()
  nextTick(() => card.value?.querySelector<HTMLInputElement>('input[type="text"]')?.focus())
})
</script>

<style scoped>
.li-card { max-width: 720px; }
.li-card form { display: flex; flex-direction: column; min-height: 0; flex: 1; }
.li-body { overflow-y: auto; min-height: 0; flex: 1; display: grid; gap: 18px; }
.li-group { margin: 0; padding: 0; border: 0; display: grid; gap: 12px; min-width: 0; }
.li-group legend { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; width: 100%; padding: 0 0 8px; margin-bottom: 4px; border-bottom: 1px solid var(--admin-border-subtle); font-weight: 800; font-size: 0.95rem; }
.li-group legend em, .li-field em { font-style: normal; font-weight: 500; color: var(--admin-text-muted); font-size: 0.82rem; }
.li-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px 14px; }
.li-field { display: flex; flex-direction: column; gap: 6px; min-width: 0; }
.li-field > span { font-size: 0.84rem; font-weight: 600; color: var(--admin-text-secondary); line-height: 1.4; }
.li-field .form-input, .li-field .form-select, .li-field .form-textarea { width: 100%; min-height: 44px; }
.li-chips { display: flex; flex-wrap: wrap; gap: 8px; }
.li-chip { display: inline-flex; align-items: center; min-height: 40px; padding: 6px 14px; border-radius: 999px; border: 1px solid var(--admin-border-hover); background: transparent; color: var(--admin-text-secondary); font: inherit; font-size: 0.86rem; font-weight: 600; cursor: pointer; }
.li-chip input { position: absolute; opacity: 0; pointer-events: none; }
.li-chip:hover { background: var(--admin-chip-bg); }
.li-chip.on { background: var(--admin-text-primary); border-color: transparent; color: var(--admin-bg-surface); }
.li-chip:has(input:focus-visible), .li-chip:focus-visible { outline: 2px solid var(--admin-text-gold); outline-offset: 2px; }
.li-score { margin-left: auto; padding: 2px 10px; border-radius: 999px; font-size: 0.78rem; font-weight: 700; background: var(--admin-chip-bg); color: var(--admin-text-muted); }
.li-score[data-tier='HOT'] { background: rgba(220, 38, 38, 0.14); color: #DC2626; }
.li-score[data-tier='WARM'] { background: rgba(217, 119, 6, 0.16); color: #B45309; }
.li-score[data-tier='COLD'] { background: rgba(37, 99, 235, 0.14); color: #2563EB; }
.li-err { color: #DC2626; font-size: 0.8rem; font-weight: 600; }
.li-general { margin: 0; }
.li-warn { font-size: 0.8rem; color: #B45309; line-height: 1.5; }
.li-link { margin-left: 4px; padding: 0; border: 0; background: none; color: inherit; font: inherit; font-weight: 700; text-decoration: underline; text-underline-offset: 2px; cursor: pointer; }
.li-dup { padding: 12px 14px; border-radius: 10px; border: 1px solid rgba(217, 119, 6, 0.45); border-left-width: 4px; background: rgba(245, 158, 11, 0.1); }
.li-dup p { margin: 0 0 10px; font-size: 0.9rem; line-height: 1.5; }
.li-dup-actions { display: flex; flex-wrap: wrap; gap: 8px; }

@media (max-width: 640px) {
  .li-overlay { padding: 0; align-items: stretch; }
  .li-card { max-width: none; max-height: none; height: 100dvh; border-radius: 0; }
  .li-body { padding-bottom: 24px; }
  .li-grid { grid-template-columns: 1fr; }
  .li-field .form-input, .li-field .form-select, .li-field .form-textarea { font-size: 16px; }
}
</style>

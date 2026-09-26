<template>
  <div class="st">
    <header class="st-head">
      <div>
        <h1>Settings</h1>
        <p>Everything here shows up on the public website and in the owner sign-up flow. Changes go live as soon as you save.</p>
      </div>
      <button type="button" class="st-btn st-btn--go" :disabled="isSaving || !dirty" @click="save">{{ isSaving ? 'Saving…' : dirty ? 'Save changes' : 'Saved' }}</button>
    </header>

    <nav class="st-tabs" aria-label="Settings sections">
      <a v-for="s in sections" :key="s.id" :href="`#${s.id}`">{{ s.label }}</a>
    </nav>

    <p v-if="error" class="st-error" role="alert">{{ error }}</p>

    <section id="contact" class="st-card" aria-labelledby="st-contact">
      <h2 id="st-contact">Contact details</h2>
      <p class="st-help">Shown in the header, footer, contact page, property pages and WhatsApp buttons. Leave WhatsApp empty to hide every WhatsApp button.</p>
      <div class="st-grid">
        <label class="st-field"><span>Company name</span><input v-model="form.site_name" /></label>
        <label class="st-field"><span>Phone number</span><input v-model="form.contact_phone" placeholder="+880 1XXX-XXXXXX" /></label>
        <label class="st-field"><span>WhatsApp number</span><input v-model="form.whatsapp_number" placeholder="+8801XXXXXXXXX" /></label>
        <label class="st-field"><span>Email</span><input v-model="form.contact_email" type="email" /></label>
        <label class="st-field st-span2"><span>Office address</span><input v-model="form.office_address" /></label>
        <label class="st-field st-span2"><span>Office hours</span><input v-model="form.working_hours" placeholder="শনি–বৃহস্পতি, সকাল ১০টা–সন্ধ্যা ৭টা" /></label>
      </div>
    </section>

    <section id="homepage" class="st-card" aria-labelledby="st-home">
      <h2 id="st-home">Homepage text</h2>
      <p class="st-help">The headline breaks onto a new line after each comma.</p>
      <label class="st-field"><span>Headline</span><input v-model="form.home_headline" maxlength="120" /></label>
      <label class="st-field"><span>Text under the headline</span><textarea v-model="form.home_subtitle" rows="3" maxlength="400" /></label>
      <label class="st-field"><span>Browser tab title</span><input v-model="form.site_title" maxlength="200" /></label>
    </section>

    <section id="property-page" class="st-card" aria-labelledby="st-property">
      <h2 id="st-property">Property page button</h2>
      <p class="st-help">The orange button on every property page and at the end of the buyer form. Promise something the team really sends, so buyers who click get what the button said.</p>
      <div class="st-grid">
        <label class="st-field"><span>Button text (price shown)</span><input v-model="form.property_cta_label" maxlength="30" /></label>
        <label class="st-field"><span>Button text (price hidden)</span><input v-model="form.property_cta_label_hidden_price" maxlength="30" /></label>
      </div>
      <label class="st-field"><span>Line under the button</span><textarea v-model="form.property_cta_note" rows="2" maxlength="160" /></label>
    </section>

    <section id="owners" class="st-card" aria-labelledby="st-owners">
      <h2 id="st-owners">Owner terms & service charge</h2>
      <p class="st-help">Owners must accept these terms before submitting a property. Write one rule per line. Use <code>{commission}</code> where the percentage should appear. Changing the terms asks owners with unsubmitted drafts to read them again.</p>
      <label class="st-field st-narrow"><span>Service charge (% of sale price)</span><input v-model.number="form.owner_commission_percent" type="number" min="0" max="20" step="0.1" /></label>
      <label class="st-field"><span>Terms, one per line</span><textarea v-model="form.owner_terms" rows="8" class="st-bn" /></label>
      <div class="st-preview">
        <strong>What owners will read</strong>
        <ol><li v-for="line in termsPreview" :key="line">{{ line }}</li></ol>
      </div>
    </section>

    <section id="documents" class="st-card" aria-labelledby="st-docs">
      <h2 id="st-docs">Papers owners upload</h2>
      <p class="st-help">This list appears on the “sell your property” page and in the owner's upload step. Required papers must be uploaded before a listing can be verified. Keys are used internally; don't change the key of a paper owners have already uploaded.</p>
      <table class="st-docs">
        <thead><tr><th>Paper (Bangla)</th><th>Hint</th><th>Required</th><th>Key</th><th><span class="sr-only">Order and remove</span></th></tr></thead>
        <tbody>
          <tr v-for="(doc, i) in form.listing_document_types" :key="i">
            <td><input v-model="doc.label" class="st-bn" :aria-label="`Paper ${i + 1} name`" /></td>
            <td><input v-model="doc.hint" class="st-bn" :aria-label="`Paper ${i + 1} hint`" /></td>
            <td class="st-center"><input v-model="doc.required" type="checkbox" :aria-label="`Paper ${i + 1} required`" /></td>
            <td><input v-model="doc.key" class="st-key" :aria-label="`Paper ${i + 1} key`" pattern="[a-z0-9_]+" /></td>
            <td class="st-row-tools">
              <button type="button" :disabled="i === 0" aria-label="Move up" @click="move(i, -1)">↑</button>
              <button type="button" :disabled="i === form.listing_document_types.length - 1" aria-label="Move down" @click="move(i, 1)">↓</button>
              <button type="button" class="st-remove" aria-label="Remove" @click="form.listing_document_types.splice(i, 1)">Remove</button>
            </td>
          </tr>
        </tbody>
      </table>
      <button type="button" class="st-btn" @click="addDoc">Add a paper</button>
    </section>

    <section id="options" class="st-card" aria-labelledby="st-options">
      <h2 id="st-options">Listing form options</h2>
      <p class="st-help">Categories, regions, deal types, statuses and land units used in the property editor and filters.</p>
      <div class="st-links">
        <NuxtLink to="/admin/categories">Property categories</NuxtLink>
        <NuxtLink to="/admin/divisions">Divisions & regions</NuxtLink>
        <NuxtLink to="/admin/transaction-types">Transaction types</NuxtLink>
        <NuxtLink to="/admin/property-statuses">Listing statuses</NuxtLink>
        <NuxtLink to="/admin/land-units">Land units</NuxtLink>
      </div>
    </section>

    <div class="st-savebar" :class="{ show: dirty }" role="region" aria-label="Unsaved changes">
      <span>You have unsaved changes.</span>
      <button type="button" class="st-btn" @click="reset">Undo</button>
      <button type="button" class="st-btn st-btn--go" :disabled="isSaving" @click="save">{{ isSaving ? 'Saving…' : 'Save changes' }}</button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, onMounted, reactive, ref } from 'vue'
import { useSettings, type DocumentType } from '~/composables/useSettings'
import { useToast } from '~/composables/useToast'

definePageMeta({ layout: 'admin' })

const toast = useToast()
const { settings, isSaving, fetchSettings, updateSettings } = useSettings()
const editable = ['site_name', 'site_title', 'contact_phone', 'whatsapp_number', 'contact_email', 'office_address', 'working_hours', 'home_headline', 'home_subtitle', 'property_cta_label', 'property_cta_label_hidden_price', 'property_cta_note', 'owner_commission_percent', 'owner_terms', 'listing_document_types'] as const

const sections = [
  { id: 'contact', label: 'Contact' }, { id: 'homepage', label: 'Homepage' }, { id: 'property-page', label: 'Property button' }, { id: 'owners', label: 'Owner terms' },
  { id: 'documents', label: 'Owner papers' }, { id: 'options', label: 'Form options' }
]
const form = reactive<Record<string, any>>({ listing_document_types: [] as DocumentType[] })
const snapshot = ref('')
const error = ref('')

const current = () => JSON.stringify(Object.fromEntries(editable.map(k => [k, form[k]])))
const dirty = computed(() => snapshot.value !== '' && current() !== snapshot.value)
const termsPreview = computed(() => String(form.owner_terms || '').split('\n').map(l => l.trim()).filter(Boolean).map(l => l.replaceAll('{commission}', String(form.owner_commission_percent ?? ''))))

const reset = () => {
  editable.forEach(k => { form[k] = JSON.parse(JSON.stringify(settings.value[k] ?? '')) })
  if (!Array.isArray(form.listing_document_types)) form.listing_document_types = []
  form.listing_document_types = form.listing_document_types.map((d: DocumentType) => ({ key: d.key, label: d.label, hint: d.hint || '', required: !!d.required }))
  snapshot.value = current()
}
const move = (i: number, dir: number) => {
  const list = form.listing_document_types
  const [item] = list.splice(i, 1)
  list.splice(i + dir, 0, item)
}
const addDoc = () => form.listing_document_types.push({ key: `paper_${form.listing_document_types.length + 1}`, label: '', hint: '', required: false })

const save = async () => {
  error.value = ''
  const docs = form.listing_document_types as DocumentType[]
  if (docs.some(d => !d.label.trim() || !/^[a-z0-9_]+$/.test(d.key))) {
    error.value = 'Every paper needs a name, and keys may only use lowercase letters, numbers and _.'
    return
  }
  try {
    const payload = Object.fromEntries(editable.map(k => [k, form[k]]))
    await updateSettings(payload as any)
    reset()
    toast.success('Settings saved', 'The website now shows the new values.')
  } catch (err: any) {
    error.value = err?.message || 'Settings could not be saved.'
  }
}

onMounted(async () => { await fetchSettings(true); reset() })
useSeoMeta({ title: 'Settings | GBREL Admin' })
</script>

<style scoped>
.st { padding: 8px 0 120px; color: var(--admin-text-primary); max-width: 1000px; }
.st-head { display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 16px; margin-bottom: 14px; }
.st-head h1 { font-size: 2rem; font-weight: 800; color: var(--admin-text-primary); }
.st-head p { color: var(--admin-text-secondary); max-width: 64ch; }
.st-tabs { display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 16px; }
.st-tabs a { padding: 6px 14px; border-radius: 999px; border: 1px solid var(--admin-border-hover); color: var(--admin-text-secondary); font-weight: 600; font-size: .85rem; }
.st-tabs a:hover { border-color: #3F7A35; color: var(--admin-text-primary); }
.st-card { background: var(--admin-bg-surface); border: 1px solid var(--admin-border-subtle); border-radius: 14px; padding: 22px 24px; margin-bottom: 16px; display: flex; flex-direction: column; gap: 14px; scroll-margin-top: 90px; }
.st-card h2 { font-size: 1.35rem; font-weight: 700; color: var(--admin-text-primary); }
.st-help { color: var(--admin-text-secondary); font-size: .9rem; margin-top: -8px; max-width: 80ch; }
.st-help code { background: var(--admin-bg-surface-alt); padding: 1px 6px; border-radius: 4px; }
.st-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
.st-span2 { grid-column: span 2; }
.st-field { display: flex; flex-direction: column; gap: 6px; font-size: .85rem; font-weight: 600; color: var(--admin-text-secondary); }
.st-field input, .st-field textarea, .st-docs input:not([type=checkbox]) { min-height: 44px; padding: 8px 12px; border-radius: 8px; border: 1px solid var(--admin-border-hover); background: var(--admin-bg-surface); color: var(--admin-text-primary); font: inherit; font-weight: 400; font-size: .95rem; width: 100%; }
.st-field textarea { resize: vertical; line-height: 1.7; }
.st-field input:focus, .st-field textarea:focus, .st-docs input:focus { outline: none; border-color: #3F7A35; box-shadow: 0 0 0 3px rgba(63, 122, 53, .18); }
.st-narrow { max-width: 260px; }
.st-bn { font-family: 'Noto Sans Bengali', sans-serif !important; }
.st-preview { background: var(--admin-bg-surface-alt); border-radius: 10px; padding: 14px 18px; font-family: 'Noto Sans Bengali', sans-serif; }
.st-preview strong { font-family: 'Plus Jakarta Sans', sans-serif; font-size: .8rem; color: var(--admin-text-muted); }
.st-preview ol { padding-left: 20px; margin-top: 6px; display: flex; flex-direction: column; gap: 4px; }
.st-docs { width: 100%; border-collapse: collapse; }
.st-docs th { text-align: left; font-size: .75rem; color: var(--admin-text-muted); padding: 6px; border-bottom: 1px solid var(--admin-border-subtle); }
.st-docs td { padding: 6px; border-bottom: 1px solid var(--admin-border-subtle); vertical-align: middle; }
.st-docs td:nth-child(1) { width: 32%; }
.st-docs .st-key { font-family: ui-monospace, monospace; font-size: .82rem !important; max-width: 140px; }
.st-center { text-align: center; }
.st-center input { width: 18px; height: 18px; accent-color: #1D4A2A; }
.st-row-tools { white-space: nowrap; }
.st-row-tools button { background: none; border: 1px solid var(--admin-border-hover); border-radius: 6px; min-width: 32px; min-height: 32px; color: var(--admin-text-secondary); cursor: pointer; margin-left: 2px; }
.st-row-tools button:disabled { opacity: .35; cursor: not-allowed; }
.st-row-tools .st-remove { color: #9B2C2C; padding: 0 8px; }
.st-links { display: flex; flex-wrap: wrap; gap: 8px; }
.st-links a { padding: 10px 16px; border-radius: 10px; background: var(--admin-bg-surface-alt); color: var(--admin-text-primary); font-weight: 600; }
.st-links a:hover { box-shadow: inset 0 0 0 1.5px #3F7A35; }
.st-btn { display: inline-flex; align-items: center; justify-content: center; align-self: flex-start; min-height: 42px; padding: 8px 18px; border-radius: 999px; border: 1.5px solid var(--admin-border-hover); background: var(--admin-bg-surface); color: var(--admin-text-primary); font-weight: 700; cursor: pointer; }
.st-btn--go { background: #1D4A2A; border-color: #1D4A2A; color: #fff; }
.st-btn:disabled { opacity: .55; cursor: not-allowed; }
.st-error { color: #9B2C2C; background: #F9E6E6; border-radius: 8px; padding: 10px 12px; margin-bottom: 12px; }
.st-savebar { position: fixed; left: 50%; bottom: 20px; transform: translate(-50%, 140%); display: flex; align-items: center; gap: 12px; padding: 10px 12px 10px 20px; background: #132A1B; color: #fff; border-radius: 999px; box-shadow: 0 16px 40px -16px rgba(0, 0, 0, .5); transition: transform .25s ease; z-index: 50; }
.st-savebar.show { transform: translate(-50%, 0); }
.st-savebar .st-btn { min-height: 36px; background: transparent; color: #fff; border-color: rgba(255, 255, 255, .4); }
.st-savebar .st-btn--go { background: #E2651C; border-color: #E2651C; }
@media (prefers-reduced-motion: reduce) { .st-savebar { transition: none; } }
@media (max-width: 760px) {
  .st-grid { grid-template-columns: 1fr; } .st-span2 { grid-column: auto; }
  .st-docs thead { display: none; }
  .st-docs tr { display: grid; grid-template-columns: 1fr auto; gap: 6px; padding: 8px 0; border-bottom: 1px solid var(--admin-border-subtle); }
  .st-docs td { border: 0; padding: 0; width: auto !important; }
  .st-docs td:nth-child(2) { grid-column: 1 / -1; }
}
</style>

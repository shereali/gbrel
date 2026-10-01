<template>
  <div class="ap">
    <form class="ap-compose" @submit.prevent="submit">
      <div class="ap-kinds" role="group" aria-label="What did you do?">
        <button v-for="k in kinds" :key="k.value" type="button" :class="{ on: kind === k.value }" :aria-pressed="kind === k.value" @click="kind = k.value">{{ k.label }}</button>
      </div>

      <div v-if="kind === 'call'" class="ap-outcomes" role="group" aria-label="Call result">
        <button v-for="o in CALL_OUTCOMES" :key="o" type="button" :class="{ on: outcome === o }" :aria-pressed="outcome === o" @click="outcome = o">{{ o }}</button>
      </div>

      <textarea v-model="text" rows="3" class="ap-text" :placeholder="placeholder" :aria-label="placeholder" maxlength="2000"></textarea>

      <div class="ap-follow">
        <span class="ap-label">Follow up</span>
        <button v-for="o in followOptions" :key="o.label" type="button" :class="{ on: followUp === o.value }" :aria-pressed="followUp === o.value" @click="followUp = followUp === o.value ? null : o.value">{{ o.label }}</button>
      </div>

      <p v-if="error" class="ap-error" role="alert">{{ error }}</p>
      <button type="submit" class="ap-save" :disabled="saving">{{ saving ? 'Saving…' : saveLabel }}</button>
    </form>

    <p v-if="loading" class="ap-muted">Loading activity…</p>
    <ol v-else class="ap-list" aria-label="Activity">
      <li v-for="item in items" :key="item.key" :data-kind="item.kind">
        <span class="ap-dot" aria-hidden="true"></span>
        <div class="ap-body">
          <p class="ap-title">{{ item.title }}</p>
          <p v-if="item.text" class="ap-quote">{{ item.text }}</p>
          <p class="ap-meta">{{ item.who }}<template v-if="item.who"> · </template><time :title="item.full">{{ item.when }}</time></p>
        </div>
      </li>
    </ol>
  </div>
</template>

<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import { CALL_OUTCOMES, formatWhen, inDays, useLeadCrm, type LeadActivity } from '~/composables/useLeadCrm'
import { useToast } from '~/composables/useToast'

const props = defineProps<{ leadId: number; receivedAt: string; activities: LeadActivity[]; loading: boolean }>()
const emit = defineEmits<{ changed: [] }>()

const crm = useLeadCrm()
const toast = useToast()

const kinds = [
  { value: 'note', label: 'Add note' },
  { value: 'call', label: 'Log call' },
  { value: 'whatsapp', label: 'Log WhatsApp' }
] as const
const kind = ref<'note' | 'call' | 'whatsapp'>('note')
const outcome = ref<string>('')
const text = ref('')
const followUp = ref<string | null>(null)
const saving = ref(false)
const error = ref('')

// Chips are computed when the form is used, so "Tomorrow" is always tomorrow.
const followOptions = computed(() => [
  { label: 'Tomorrow', value: inDays(1) },
  { label: 'In 3 days', value: inDays(3) },
  { label: 'Next week', value: inDays(7) }
])

const placeholder = computed(() => ({
  note: 'What should the next person know about this buyer?',
  call: 'What did you talk about? (optional)',
  whatsapp: 'What did you send or hear back? (optional)'
}[kind.value]))
const saveLabel = computed(() => ({ note: 'Save note', call: 'Save call', whatsapp: 'Save WhatsApp' }[kind.value]))

// A new lead starts with a clean form.
watch(() => props.leadId, () => { text.value = ''; outcome.value = ''; followUp.value = null; error.value = ''; kind.value = 'note' })

const submit = async () => {
  error.value = ''
  if (kind.value === 'note' && !text.value.trim()) { error.value = 'Write the note first.'; return }
  if (kind.value === 'call' && !outcome.value) { error.value = 'Choose how the call went.'; return }
  saving.value = true
  try {
    await crm.log(props.leadId, { type: kind.value, body: text.value.trim() || undefined, outcome: kind.value === 'call' ? outcome.value : undefined, follow_up_at: followUp.value })
    toast.success('Saved', followUp.value ? 'Activity logged and follow-up set.' : 'Activity logged.')
    text.value = ''; outcome.value = ''; followUp.value = null
    emit('changed')
  } catch (err: any) {
    error.value = err?.message || 'Could not save. Try again.'
  } finally {
    saving.value = false
  }
}

const describe = (a: LeadActivity) => {
  const m = a.meta || {}
  switch (a.type) {
    case 'note': return { title: 'Note', text: a.body }
    case 'call': return { title: `Call · ${m.outcome || 'logged'}`, text: a.body }
    case 'whatsapp': return { title: 'WhatsApp', text: a.body }
    case 'email': return { title: 'Email', text: a.body }
    case 'stage': return { title: `Moved from ${m.from} to ${m.to}`, text: a.body }
    case 'assign': return { title: a.body || 'Assignment changed', text: null }
    case 'follow_up': return { title: m.at ? `Follow-up set for ${formatWhen(m.at)}` : 'Follow-up cleared', text: a.body }
    case 'visit': return { title: a.body || 'Site visit updated', text: null }
    default: return { title: a.type, text: a.body }
  }
}

const items = computed(() => [
  ...props.activities.map(a => ({
    key: `a${a.id}`, kind: a.type, ...describe(a), who: a.user?.name || 'System', when: formatWhen(a.occurred_at), full: a.occurred_at
  })),
  { key: 'received', kind: 'received', title: 'Inquiry received', text: null, who: '', when: formatWhen(props.receivedAt), full: props.receivedAt }
])
</script>

<style scoped>
.ap-compose { display: flex; flex-direction: column; gap: 12px; padding: 16px; border: 1px solid var(--admin-border-subtle); border-radius: 12px; background: var(--admin-bg-surface-alt); }
.ap-kinds, .ap-outcomes, .ap-follow { display: flex; flex-wrap: wrap; align-items: center; gap: 6px; }
.ap button { min-height: 36px; padding: 4px 14px; border-radius: 999px; border: 1px solid var(--admin-border-hover); background: transparent; color: var(--admin-text-secondary); font: inherit; font-size: 0.84rem; font-weight: 600; cursor: pointer; }
.ap button:hover { background: var(--admin-chip-bg); }
.ap button.on { background: var(--admin-text-primary); color: var(--admin-bg-surface); border-color: transparent; }
.ap button:focus-visible, .ap-text:focus-visible { outline: 2px solid var(--admin-text-gold); outline-offset: 2px; }
.ap-text { width: 100%; padding: 10px 12px; border-radius: 10px; border: 1px solid var(--admin-border-hover); background: var(--admin-bg-surface); color: inherit; font: inherit; font-size: 0.92rem; line-height: 1.5; resize: vertical; }
.ap-label { font-size: 0.8rem; font-weight: 600; color: var(--admin-text-muted); margin-right: 2px; }
.ap-error { margin: 0; color: #DC2626; font-size: 0.86rem; font-weight: 600; }
.ap button.ap-save { align-self: flex-start; min-height: 40px; padding: 6px 22px; border-radius: 10px; border: 0; background: #0B7A58; color: #fff; font-size: 0.9rem; font-weight: 700; }
.ap button.ap-save:hover { background: #096348; }
.ap button.ap-save:disabled { opacity: 0.7; cursor: wait; }
.ap-muted { margin: 20px 0 0; color: var(--admin-text-muted); font-size: 0.9rem; }

.ap-list { list-style: none; margin: 22px 0 0; padding: 0; }
.ap-list li { position: relative; display: flex; gap: 14px; padding: 0 0 20px; }
.ap-list li::before { content: ''; position: absolute; left: 5px; top: 14px; bottom: 0; width: 2px; background: var(--admin-border-subtle); }
.ap-list li:last-child::before { display: none; }
.ap-dot { flex: none; width: 12px; height: 12px; margin-top: 5px; border-radius: 50%; background: var(--admin-text-muted); }
li[data-kind='call'] .ap-dot, li[data-kind='whatsapp'] .ap-dot { background: #0B7A58; }
li[data-kind='note'] .ap-dot { background: #D97706; }
li[data-kind='stage'] .ap-dot { background: #2563EB; }
li[data-kind='visit'] .ap-dot { background: #C2530F; }
.ap-body { min-width: 0; }
.ap-title { margin: 0; font-size: 0.92rem; font-weight: 700; }
.ap-quote { margin: 4px 0 0; font-size: 0.9rem; line-height: 1.55; white-space: pre-line; overflow-wrap: anywhere; color: var(--admin-text-secondary); }
.ap-meta { margin: 4px 0 0; font-size: 0.78rem; color: var(--admin-text-muted); }
</style>

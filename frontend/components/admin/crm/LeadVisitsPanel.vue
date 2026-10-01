<template>
  <div class="vp">
    <section v-if="upcoming.length" aria-labelledby="vp-up">
      <h3 id="vp-up">Coming up</h3>
      <article v-for="v in upcoming" :key="v.id" class="vp-card">
        <header>
          <div>
            <strong>{{ formatDay(v.date) }}<template v-if="v.time"> · {{ formatTime(v.time) }}</template></strong>
            <span class="vp-type">{{ v.visit_type }}</span>
          </div>
          <span class="vp-status" data-status="Confirmed">Confirmed</span>
        </header>
        <p class="vp-detail">{{ v.property_title }}<template v-if="v.assigned_agent"> · with {{ v.assigned_agent }}</template><template v-if="v.vip_pickup"> · pickup from {{ v.pickup_location || 'buyer' }}</template></p>
        <p v-if="v.notes" class="vp-note">{{ v.notes }}</p>

        <form v-if="editing?.id === v.id && editing.mode === 'complete'" class="vp-inline" @submit.prevent="finish(v)">
          <label for="vp-outcome">How did it go?</label>
          <textarea id="vp-outcome" v-model="outcomeText" rows="3" placeholder="What did the buyer think? Any next step?"></textarea>
          <div class="vp-row">
            <button type="submit" class="vp-primary" :disabled="busy">Save as completed</button>
            <button type="button" class="vp-ghost" @click="editing = null">Cancel</button>
          </div>
        </form>

        <form v-else-if="editing?.id === v.id && editing.mode === 'reschedule'" class="vp-inline" @submit.prevent="reschedule(v)">
          <div class="vp-row">
            <label class="vp-field"><span>New date</span><input v-model="moveDate" type="date" :min="today" required /></label>
            <label class="vp-field"><span>Time</span><input v-model="moveTime" type="time" required /></label>
          </div>
          <div class="vp-row">
            <button type="submit" class="vp-primary" :disabled="busy">Move visit</button>
            <button type="button" class="vp-ghost" @click="editing = null">Cancel</button>
          </div>
        </form>

        <div v-else class="vp-actions">
          <button type="button" class="vp-primary" @click="startComplete(v)">Mark completed</button>
          <button type="button" class="vp-ghost" @click="startMove(v)">Reschedule</button>
          <button type="button" class="vp-ghost" @click="setStatus(v, 'No-show')">No-show</button>
          <button type="button" class="vp-ghost vp-danger" @click="setStatus(v, 'Cancelled')">Cancel</button>
        </div>
      </article>
    </section>

    <section aria-labelledby="vp-new">
      <h3 id="vp-new">{{ visits.length ? 'Schedule another visit' : 'Schedule a visit' }}</h3>
      <form class="vp-form" @submit.prevent="schedule">
        <div class="vp-types" role="group" aria-label="Visit type">
          <button v-for="t in types" :key="t" type="button" :class="{ on: form.visit_type === t }" :aria-pressed="form.visit_type === t" @click="form.visit_type = t">{{ t }}</button>
        </div>
        <div class="vp-row">
          <label class="vp-field"><span>Date</span><input v-model="form.date" type="date" :min="today" required /></label>
          <label class="vp-field"><span>Time</span><input v-model="form.time" type="time" required /></label>
        </div>
        <div class="vp-slots" role="group" aria-label="Common times">
          <button v-for="s in slots" :key="s" type="button" :class="{ on: form.time === s }" @click="form.time = s">{{ formatTime(s) }}</button>
        </div>
        <label class="vp-field">
          <span>Who will take the buyer?</span>
          <select v-model="form.assigned_to">
            <option :value="null">Decide later</option>
            <option v-for="s in staff" :key="s.id" :value="s.id">{{ s.name }}</option>
          </select>
        </label>
        <template v-if="form.visit_type === 'Site visit'">
          <label class="vp-check"><input v-model="form.vip_pickup" type="checkbox" /> Pick the buyer up</label>
          <label v-if="form.vip_pickup" class="vp-field"><span>Pickup place</span><input v-model="form.pickup_location" type="text" placeholder="e.g. Gulshan 1 circle" maxlength="255" /></label>
        </template>
        <label class="vp-field"><span>Notes for the team <small>(optional)</small></span><textarea v-model="form.notes" rows="2" maxlength="1000"></textarea></label>
        <p v-if="error" class="vp-error" role="alert">{{ error }}</p>
        <button type="submit" class="vp-primary vp-wide" :disabled="busy">{{ busy ? 'Saving…' : 'Schedule visit' }}</button>
      </form>
    </section>

    <section v-if="past.length" aria-labelledby="vp-past">
      <h3 id="vp-past">Earlier visits</h3>
      <article v-for="v in past" :key="v.id" class="vp-card vp-card--past">
        <header>
          <div>
            <strong>{{ formatDay(v.date) }}<template v-if="v.time"> · {{ formatTime(v.time) }}</template></strong>
            <span class="vp-type">{{ v.visit_type }}</span>
          </div>
          <span class="vp-status" :data-status="v.status">{{ v.status }}</span>
        </header>
        <p class="vp-detail">{{ v.property_title }}<template v-if="v.assigned_agent"> · with {{ v.assigned_agent }}</template></p>
        <p v-if="v.outcome_notes" class="vp-note">{{ v.outcome_notes }}</p>
      </article>
    </section>
  </div>
</template>

<script setup lang="ts">
import { computed, reactive, ref, watch } from 'vue'
import { dhakaToday, formatDay, formatTime, useLeadCrm, type LeadVisit, type StaffMember } from '~/composables/useLeadCrm'
import { useToast } from '~/composables/useToast'

const props = defineProps<{ leadId: number; visits: LeadVisit[]; staff: StaffMember[]; assignedTo: number | null; suggestVideo: boolean }>()
const emit = defineEmits<{ changed: [] }>()

const crm = useLeadCrm()
const toast = useToast()
const types = ['Site visit', 'Video call']
const slots = ['10:00', '11:30', '15:00', '16:30']
const today = computed(() => dhakaToday())

const blank = () => ({
  visit_type: props.suggestVideo ? 'Video call' : 'Site visit',
  date: '', time: '10:00', assigned_to: props.assignedTo as number | null,
  vip_pickup: false, pickup_location: '', notes: ''
})
const form = reactive(blank())
watch(() => props.leadId, () => Object.assign(form, blank(), { date: '' }))

const busy = ref(false)
const error = ref('')
const editing = ref<{ id: number; mode: 'complete' | 'reschedule' } | null>(null)
const outcomeText = ref('')
const moveDate = ref('')
const moveTime = ref('10:00')

// Open visits that are today or later are "coming up"; everything else is history.
const upcoming = computed(() => props.visits.filter(v => v.status === 'Confirmed' && v.date >= today.value).sort((a, b) => (a.date + (a.time || '')).localeCompare(b.date + (b.time || ''))))
const past = computed(() => props.visits.filter(v => !upcoming.value.includes(v)))

const run = async (action: () => Promise<unknown>, message: string) => {
  busy.value = true
  try {
    await action()
    toast.success('Saved', message)
    emit('changed')
    return true
  } catch (err: any) {
    toast.error('Could not save', err?.message || 'Try again.')
    return false
  } finally {
    busy.value = false
  }
}

const schedule = async () => {
  error.value = ''
  if (!form.date) { error.value = 'Choose a date.'; return }
  busy.value = true
  try {
    await crm.scheduleVisit(props.leadId, {
      date: form.date, time: form.time, visit_type: form.visit_type, assigned_to: form.assigned_to,
      vip_pickup: form.visit_type === 'Site visit' && form.vip_pickup,
      pickup_location: form.vip_pickup ? form.pickup_location || null : null,
      notes: form.notes || null
    })
    toast.success('Visit scheduled', `${form.visit_type} booked.`)
    Object.assign(form, blank(), { date: '' })
    emit('changed')
  } catch (err: any) {
    error.value = err?.message || 'Could not schedule. Try again.'
  } finally {
    busy.value = false
  }
}

const startComplete = (v: LeadVisit) => { editing.value = { id: v.id, mode: 'complete' }; outcomeText.value = '' }
const startMove = (v: LeadVisit) => { editing.value = { id: v.id, mode: 'reschedule' }; moveDate.value = v.date; moveTime.value = v.time || '10:00' }
const finish = async (v: LeadVisit) => {
  if (await run(() => crm.updateVisit(props.leadId, v.id, { status: 'Completed', outcome_notes: outcomeText.value.trim() || null }), 'Visit marked as completed.')) editing.value = null
}
const reschedule = async (v: LeadVisit) => {
  if (await run(() => crm.updateVisit(props.leadId, v.id, { date: moveDate.value, time: moveTime.value }), 'Visit moved.')) editing.value = null
}
const setStatus = (v: LeadVisit, status: 'No-show' | 'Cancelled') => {
  if (status === 'Cancelled' && !window.confirm('Cancel this visit?')) return
  return run(() => crm.updateVisit(props.leadId, v.id, { status }), status === 'Cancelled' ? 'Visit cancelled.' : 'Marked as no-show.')
}
</script>

<style scoped>
.vp section { margin-bottom: 26px; }
.vp h3 { margin: 0 0 10px; font-size: 0.95rem; font-weight: 700; color: var(--admin-text-secondary); }
.vp-card { padding: 14px 16px; margin-bottom: 10px; border: 1px solid var(--admin-border-hover); border-radius: 12px; background: var(--admin-bg-surface-alt); }
.vp-card--past { opacity: 0.85; border-color: var(--admin-border-subtle); }
.vp-card header { display: flex; align-items: center; justify-content: space-between; gap: 10px; flex-wrap: wrap; }
.vp-type { margin-left: 10px; font-size: 0.8rem; color: var(--admin-text-muted); }
.vp-status { padding: 2px 10px; border-radius: 999px; font-size: 0.76rem; font-weight: 700; background: var(--admin-chip-bg); color: var(--admin-text-secondary); }
.vp-status[data-status='Confirmed'] { background: rgba(37, 99, 235, 0.14); color: #2563EB; }
.vp-status[data-status='Completed'] { background: rgba(11, 122, 88, 0.16); color: #0B7A58; }
.vp-status[data-status='No-show'], .vp-status[data-status='Cancelled'] { background: rgba(220, 38, 38, 0.12); color: #DC2626; }
.vp-detail { margin: 6px 0 0; font-size: 0.88rem; color: var(--admin-text-secondary); }
.vp-note { margin: 8px 0 0; font-size: 0.88rem; line-height: 1.5; white-space: pre-line; overflow-wrap: anywhere; }
.vp-actions, .vp-row { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 12px; }
.vp-inline { margin-top: 12px; display: flex; flex-direction: column; gap: 8px; }
.vp-inline > label { font-size: 0.84rem; font-weight: 600; }

.vp-form { display: flex; flex-direction: column; gap: 12px; padding: 16px; border: 1px solid var(--admin-border-subtle); border-radius: 12px; }
.vp-types, .vp-slots { display: flex; flex-wrap: wrap; gap: 6px; }
.vp-row { margin-top: 0; }
.vp-field { display: flex; flex-direction: column; gap: 4px; flex: 1 1 150px; min-width: 0; }
.vp-field > span { font-size: 0.82rem; font-weight: 600; color: var(--admin-text-secondary); }
.vp-field small { font-weight: 400; color: var(--admin-text-muted); }
.vp input[type='date'], .vp input[type='time'], .vp input[type='text'], .vp select, .vp textarea { width: 100%; min-height: 40px; padding: 8px 10px; border-radius: 8px; border: 1px solid var(--admin-border-hover); background: var(--admin-bg-surface); color: inherit; font: inherit; font-size: 0.9rem; }
.vp-check { display: flex; align-items: center; gap: 8px; font-size: 0.9rem; cursor: pointer; }
.vp-error { margin: 0; color: #DC2626; font-size: 0.86rem; font-weight: 600; }

.vp button { min-height: 38px; padding: 4px 14px; border-radius: 999px; border: 1px solid var(--admin-border-hover); background: transparent; color: var(--admin-text-secondary); font: inherit; font-size: 0.84rem; font-weight: 600; cursor: pointer; }
.vp button:hover { background: var(--admin-chip-bg); }
.vp button.on { background: var(--admin-text-primary); color: var(--admin-bg-surface); border-color: transparent; }
.vp button.vp-primary { background: #0B7A58; border-color: #0B7A58; color: #fff; border-radius: 10px; font-weight: 700; }
.vp button.vp-primary:hover { background: #096348; }
.vp button.vp-wide { width: 100%; min-height: 44px; }
.vp button.vp-danger { color: #DC2626; }
.vp button:disabled { opacity: 0.7; cursor: wait; }
.vp button:focus-visible, .vp input:focus-visible, .vp select:focus-visible, .vp textarea:focus-visible { outline: 2px solid var(--admin-text-gold); outline-offset: 2px; }
</style>

<template>
  <div class="admin-page animate-fade-in">
    <div class="admin-header-row">
      <div>
        <h1 class="page-title">Lead journey</h1>
        <p class="page-subtitle">What this buyer did on the website before sending the form. Only visitors who accepted cookies are recorded.</p>
      </div>
      <div class="admin-header-actions">
        <NuxtLink to="/admin/leads" class="btn btn-sm btn-outline-white"><span>← Back to leads</span></NuxtLink>
      </div>
    </div>

    <div v-if="isLoading" class="panel-card lj-note">Loading journey...</div>
    <div v-else-if="error" class="panel-card lj-note">{{ error }}</div>

    <template v-else-if="journey">
      <div class="panel-card lj-lead">
        <strong>{{ journey.lead.name }}</strong>
        <span>{{ journey.lead.phone }}</span>
        <span v-if="journey.lead.tier" class="badge badge-featured">{{ journey.lead.tier }}</span>
        <span class="lj-muted">Sent the form {{ when(journey.lead.created_at) }}</span>
      </div>

      <div v-if="!journey.sessions.length" class="panel-card lj-note">
        No journey was recorded. The visitor declined cookies, or the form was sent before tracking started.
      </div>

      <section v-for="(visit, i) in journey.sessions" :key="visit.started_at" class="panel-card lj-visit">
        <h2>Visit {{ i + 1 }} · {{ when(visit.started_at) }}</h2>
        <p class="lj-muted">
          Source: <strong>{{ visit.source }}</strong>
          <template v-if="visit.campaign"> · Campaign: {{ visit.campaign }}</template>
          <template v-if="visit.adset"> · Ad set: {{ visit.adset }}</template>
          <template v-if="visit.ad"> · Ad: <strong>{{ visit.ad }}</strong></template>
          <br />
          {{ visit.device }} / {{ visit.browser }}<template v-if="visit.in_facebook_app"> (inside the Facebook app)</template>
          · Active {{ minutes(visit.seconds_active) }} · Scrolled {{ visit.max_scroll }}%
          · Furthest step: {{ visit.furthest_step || '—' }}<template v-if="visit.city"> · {{ visit.city }}</template>
        </p>
        <div class="lj-scroll">
          <table class="lj-table">
            <thead><tr><th>Time</th><th>Step</th><th>Detail</th><th>Page</th></tr></thead>
            <tbody>
              <tr v-for="(e, j) in visit.events" :key="j">
                <td>{{ clock(e.occurred_at) }}</td>
                <td>{{ e.event }}<template v-if="e.step !== null"> #{{ e.step }}</template></td>
                <td>{{ e.label }}<template v-if="e.value !== null"> → {{ e.value }}</template></td>
                <td>{{ e.path }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>
    </template>
  </div>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { useApiUrl } from '~/composables/useApi'
import { useAuth } from '~/composables/useAuth'

definePageMeta({ layout: 'admin' })
useSeoMeta({ title: 'Lead journey | GBREL Admin' })

interface JourneyEvent { event: string; label: string | null; value: string | null; step: number | null; path: string | null; occurred_at: string }
interface JourneyVisit {
  started_at: string; source: string; campaign: string | null; adset: string | null; ad: string | null
  device: string | null; browser: string | null; in_facebook_app: boolean; city: string | null
  seconds_active: number; max_scroll: number; furthest_step: string | null; events: JourneyEvent[]
}
interface Journey { lead: { id: number; name: string; phone: string; tier: string | null; created_at: string }; sessions: JourneyVisit[] }

const route = useRoute()
const { token } = useAuth()
const journey = ref<Journey | null>(null)
const isLoading = ref(true)
const error = ref('')

const dhaka = { timeZone: 'Asia/Dhaka' } as const
const when = (value: string) => new Date(value).toLocaleString('en-GB', { ...dhaka, dateStyle: 'medium', timeStyle: 'short' })
const clock = (value: string) => new Date(value).toLocaleTimeString('en-GB', dhaka)
const minutes = (seconds: number) => `${Math.floor(seconds / 60)}:${String(seconds % 60).padStart(2, '0')}`

onMounted(async () => {
  try {
    const res = await fetch(useApiUrl(`/leads/${route.params.id}/journey`), { headers: { Authorization: `Bearer ${token.value}`, Accept: 'application/json' } })
    if (res.status === 403) throw new Error('You do not have permission to view lead details.')
    if (!res.ok) throw new Error('Could not load the journey for this lead.')
    journey.value = (await res.json()).data
  } catch (err: any) {
    error.value = err?.message || 'Could not load the journey for this lead.'
  } finally {
    isLoading.value = false
  }
})
</script>

<style scoped>
.lj-note { text-align: center; padding: 40px 24px; color: #8E9B8F; }
.lj-lead { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; margin-bottom: 16px; color: var(--admin-text-primary); }
.lj-lead strong { font-size: 1.1rem; }
.lj-muted { color: #8E9B8F; font-size: 0.88rem; line-height: 1.7; }
.lj-visit { margin-bottom: 16px; color: var(--admin-text-primary); }
.lj-visit h2 { font-size: 1rem; font-weight: 700; margin: 0 0 6px; }
.lj-scroll { overflow-x: auto; margin-top: 10px; }
.lj-table { width: 100%; border-collapse: collapse; font-size: 0.82rem; }
.lj-table th, .lj-table td { text-align: left; padding: 6px 10px; border-bottom: 1px solid rgba(255, 255, 255, 0.08); white-space: nowrap; }
.lj-table th { color: #8E9B8F; font-weight: 600; }
</style>

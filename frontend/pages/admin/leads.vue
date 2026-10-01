<template>
  <div class="admin-page animate-fade-in ld">
    <div class="admin-header-row">
      <div>
        <h1 class="page-title">Buyer Inquiries & Leads</h1>
        <p class="page-subtitle">Open a lead to see everything the buyer told us, then call or message them.</p>
      </div>
      <div class="admin-header-actions">
        <button class="btn btn-emerald" @click="showAddLeadModal = true">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
            <line x1="12" y1="5" x2="12" y2="19"/>
            <line x1="5" y1="12" x2="19" y2="12"/>
          </svg>
          <span>Log New Inquiry</span>
        </button>
      </div>
    </div>

    <!-- Toolbar: search, status tabs, focus and sort -->
    <div class="ld-toolbar">
      <label class="ld-search">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="7"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <input v-model.trim="search" type="search" placeholder="Search name, phone or property" aria-label="Search leads" />
      </label>

      <div v-if="view === 'active'" class="ld-tabs" role="group" aria-label="Filter by status">
        <button v-for="tab in statusTabs" :key="tab.value" type="button" class="ld-tab" :class="{ on: statusFilter === tab.value }" :aria-pressed="statusFilter === tab.value" @click="statusFilter = tab.value">
          {{ tab.label }} <span class="ld-count">{{ tab.count }}</span>
        </button>
      </div>
      <p v-else class="ld-trash-note">Showing deleted inquiries. Restore one to bring it back to the pipeline.</p>

      <div class="ld-controls">
        <select v-model="focus" class="ld-select" aria-label="Show">
          <option value="All">All buyers</option>
          <option value="Ready within 3 months">Ready within 3 months</option>
          <option value="Own / family use">Own / family use</option>
          <option value="Investment">Investment</option>
        </select>
        <select v-model="sortBy" class="ld-select" aria-label="Sort by">
          <option value="newest">Newest first</option>
          <option value="hottest">Hottest first</option>
        </select>
        <button type="button" class="ld-trash-toggle" :class="{ on: view === 'trash' }" :aria-pressed="view === 'trash'" @click="changeView(view === 'trash' ? 'active' : 'trash')">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
          Trash
        </button>
      </div>
    </div>

    <!-- Quick views: the lists a salesperson starts the day with -->
    <div v-if="view === 'active'" class="ld-views" role="group" aria-label="Quick views">
      <button v-for="q in quickViews" :key="q.key" type="button" :class="{ on: quick === q.key, alert: q.alert && q.count > 0 }" :aria-pressed="quick === q.key" @click="quick = q.key">
        {{ q.label }}<span class="ld-count">{{ q.count }}</span>
      </button>
    </div>

    <div v-if="isLoading" class="ld-empty" role="status">Loading leads…</div>

    <div v-else-if="filteredLeads.length === 0" class="ld-empty">
      <strong>{{ view === 'trash' ? 'Trash is empty' : (hasFilters ? 'No leads match these filters' : 'No inquiries yet') }}</strong>
      <p v-if="hasFilters">Clear the search or choose "All" to see every lead.</p>
      <p v-else-if="view === 'trash'">Deleted inquiries show up here and can be restored.</p>
      <p v-else>New buyer inquiries from the website appear here. You can also log a phone inquiry yourself.</p>
      <button v-if="hasFilters" type="button" class="btn btn-sm btn-outline-white" @click="clearFilters">Clear filters</button>
      <button v-else-if="view === 'active'" type="button" class="btn btn-sm btn-emerald" @click="showAddLeadModal = true">Log New Inquiry</button>
    </div>

    <div v-else class="ld-split" :class="{ 'is-detail': detailOpen }">
      <!-- Lead list -->
      <ul class="ld-list" aria-label="Leads">
        <li v-for="lead in filteredLeads" :key="lead.id">
          <button type="button" class="ld-row" :class="{ active: selected?.id === lead.id }" :data-tier="lead.tier || 'none'" :aria-current="selected?.id === lead.id ? 'true' : undefined" @click="openLead(lead)">
            <span class="ld-main">
              <span class="ld-name">
                <span v-if="isFresh(lead)" class="ld-new" title="Not contacted yet"></span>{{ lead.name }}
              </span>
              <span class="ld-prop">{{ lead.property }}</span>
              <span v-if="flagFor(lead)" class="ld-flag" :data-tone="flagFor(lead)!.tone">{{ flagFor(lead)!.text }}</span>
            </span>
            <span class="ld-side">
              <span class="ld-tier" :data-tier="lead.tier || 'none'">{{ tierLabel(lead.tier) }}</span>
              <time :datetime="lead.created_at" :title="fullDate(lead.created_at)">{{ ago(lead.created_at) }}</time>
            </span>
          </button>
        </li>
      </ul>

      <!-- Lead detail -->
      <article v-if="selected" class="ld-detail" :aria-label="`Details for ${selected.name}`">
        <button type="button" class="ld-back" @click="detailOpen = false">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><polyline points="15 18 9 12 15 6"/></svg>
          All leads
        </button>

        <header class="ld-head">
          <div class="ld-head-top">
            <h2>{{ selected.name }}</h2>
            <span class="ld-tier ld-tier--big" :data-tier="selected.tier || 'none'">{{ tierLabel(selected.tier) }}<template v-if="selected.score"> · {{ selected.score }}</template></span>
          </div>
          <div class="ld-phone">
            <strong dir="ltr">{{ selected.phone }}</strong>
            <button type="button" class="ld-copy" :title="'Copy ' + selected.phone" @click="copyPhone(selected.phone)">Copy</button>
          </div>
          <p class="ld-received">
            Received {{ fullDate(selected.created_at) }} ({{ ago(selected.created_at) }})
            <template v-if="selected.deleted_at"> · <span class="ld-deleted">Deleted</span></template>
          </p>
        </header>

        <div v-if="!selected.deleted_at" class="ld-actions">
          <a v-if="selected.phone" :href="`tel:${selected.phone}`" class="ld-act ld-act--call">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.8 19.8 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.12 4.18 2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.96.36 1.9.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.91.34 1.85.57 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
            Call
          </a>
          <a v-if="selected.phone" :href="whatsappLink(selected)" target="_blank" rel="noopener noreferrer" class="ld-act ld-act--wa">WhatsApp</a>
          <NuxtLink v-if="selected.visitor_id" :to="`/admin/lead-journey/${selected.id}`" class="ld-act" title="Pages visited, ad source and survey steps">Website journey</NuxtLink>
        </div>

        <div v-if="!selected.deleted_at" class="ld-status">
          <span id="ld-status-label" class="ld-label">Status</span>
          <div class="ld-seg" role="group" aria-labelledby="ld-status-label">
            <button v-for="stage in stages" :key="stage" type="button" :class="{ on: selected.stage === stage }" :aria-pressed="selected.stage === stage" @click="setStage(selected, stage)">{{ stage }}</button>
          </div>
          <span v-if="!stages.includes(selected.stage)" class="ld-muted">Current status: {{ selected.stage }}</span>
        </div>
        <p v-if="selected.stage === 'Lost' && selected.lost_reason" class="ld-lost">Lost: {{ selected.lost_reason }}</p>

        <div v-if="!selected.deleted_at" class="ld-work">
          <label class="ld-field">
            <span>Assigned to</span>
            <select :value="selected.assigned_to ?? ''" class="ld-select" @change="assignLead(selected, $event)">
              <option value="">Unassigned</option>
              <option v-for="s in staff" :key="s.id" :value="s.id">{{ s.name }}</option>
            </select>
          </label>
          <div class="ld-field">
            <span id="ld-follow-label">Next follow-up</span>
            <p class="ld-follow-now" :class="followState(selected)">{{ followText(selected) }}</p>
            <div class="ld-chips" role="group" aria-labelledby="ld-follow-label">
              <button type="button" @click="setFollowUp(selected, inDays(1))">Tomorrow</button>
              <button type="button" @click="setFollowUp(selected, inDays(3))">In 3 days</button>
              <button type="button" @click="setFollowUp(selected, inDays(7))">Next week</button>
              <label class="ld-pick">
                <span class="ld-sr">Pick a date and time</span>
                <input v-model="pickedFollowUp" type="datetime-local" @change="pickFollowUp(selected)" />
              </label>
              <button v-if="selected.next_follow_up_at" type="button" class="ld-clear" @click="setFollowUp(selected, null)">Clear</button>
            </div>
          </div>
        </div>

        <div v-if="!selected.deleted_at" class="ld-tabs-detail" role="tablist" aria-label="Lead sections">
          <button v-for="t in detailTabs" :key="t.key" type="button" role="tab" :aria-selected="tab === t.key" :class="{ on: tab === t.key }" @click="tab = t.key">
            {{ t.label }}<span v-if="t.count" class="ld-count">{{ t.count }}</span>
          </button>
        </div>

        <LeadActivityPanel
          v-if="!selected.deleted_at && tab === 'activity'"
          :lead-id="selected.id" :received-at="selected.created_at" :activities="crmDetail.activities" :loading="crmLoading"
          @changed="refreshAll"
        />
        <LeadVisitsPanel
          v-if="!selected.deleted_at && tab === 'visits'"
          :lead-id="selected.id" :visits="crmDetail.visits" :staff="staff" :assigned-to="selected.assigned_to ?? null"
          :suggest-video="selected.parsed?.lives === 'Abroad (NRB)'"
          @changed="refreshAll"
        />

        <section v-for="section in detailSections" v-show="selected.deleted_at || tab === 'overview'" :key="section.title" class="ld-section">
          <h3>{{ section.title }}</h3>
          <dl>
            <div v-for="row in section.rows" :key="row.label">
              <dt>{{ row.label }}</dt>
              <dd>
                <NuxtLink v-if="row.to" :to="row.to">{{ row.value }}</NuxtLink>
                <template v-else>{{ row.value }}</template>
              </dd>
            </div>
          </dl>
        </section>

        <section v-if="selected.note" v-show="selected.deleted_at || tab === 'overview'" class="ld-section">
          <h3>Notes</h3>
          <p class="ld-note">{{ selected.note }}</p>
        </section>

        <footer class="ld-foot">
          <button v-if="selected.deleted_at" type="button" class="btn btn-sm btn-emerald" @click="restoreLead(selected)">Restore lead</button>
          <button v-else type="button" class="ld-delete" @click="promptDeleteLead(selected)">Delete this inquiry</button>
        </footer>
      </article>
    </div>

    <!-- MODAL: WHY WAS THE LEAD LOST? -->
    <div v-if="lostTarget" class="admin-modal-overlay" @click.self="lostTarget = null">
      <div class="admin-modal-card animate-fade-in-up" style="max-width: 460px;">
        <div class="admin-modal-header">
          <h3 class="admin-modal-title">Mark {{ lostTarget.name }} as lost</h3>
          <button class="admin-modal-close" aria-label="Close" @click="lostTarget = null">✕</button>
        </div>
        <form @submit.prevent="confirmLost">
          <div class="admin-modal-body">
            <label class="form-label" for="lost-reason">Why did we lose this lead?</label>
            <select id="lost-reason" v-model="lostChoice" class="form-select">
              <option v-for="r in lostReasons" :key="r" :value="r">{{ r }}</option>
            </select>
            <input v-if="lostChoice === 'Other'" v-model.trim="lostOther" type="text" class="form-input" style="margin-top: 10px;" placeholder="Write the reason" maxlength="150" aria-label="Other reason" />
            <p style="font-size: 0.82rem; color: var(--admin-text-muted); margin-top: 10px;">You can move the lead back to another status any time.</p>
          </div>
          <div class="admin-modal-footer">
            <button type="button" class="btn btn-sm btn-outline-white" @click="lostTarget = null">Cancel</button>
            <button type="submit" class="btn btn-sm" style="background:#EF4444; color:#FFF;" :disabled="lostChoice === 'Other' && !lostOther">Mark as lost</button>
          </div>
        </form>
      </div>
    </div>

    <!-- ======================================================================
         MODAL: LOG NEW BUYER INQUIRY
         ====================================================================== -->
    <div v-if="showAddLeadModal" class="admin-modal-overlay" @click.self="showAddLeadModal = false">
      <div class="admin-modal-card animate-fade-in-up">
        <div class="admin-modal-header">
          <h3 class="admin-modal-title">Log Buyer Inquiry</h3>
          <button class="admin-modal-close" aria-label="Close" @click="showAddLeadModal = false">✕</button>
        </div>
        <form @submit.prevent="saveNewLead">
          <div class="admin-modal-body">
            <div class="grid grid-2" style="gap:14px; margin-bottom:14px;">
              <div class="form-group">
                <label class="form-label">Prospect Name *</label>
                <input v-model="leadForm.name" type="text" required placeholder="e.g. Dr. Salman Khan" class="form-input" />
              </div>
              <div class="form-group">
                <label class="form-label">Phone / WhatsApp *</label>
                <input v-model="leadForm.phone" type="tel" required placeholder="+880 1819-..." class="form-input" />
              </div>
            </div>

            <div class="grid grid-2" style="gap:14px; margin-bottom:14px;">
              <div class="form-group">
                <label class="form-label">Target Asset / Property</label>
                <input v-model="leadForm.property" type="text" placeholder="যেমন: গুলশান ১ এ ১৫ কাঠা জমি / লেক ভিউ" class="form-input" />
              </div>
              <div class="form-group">
                <label class="form-label">Buyer Category</label>
                <select v-model="leadForm.type" class="form-select">
                  <option value="NRB Investor">NRB Investor (Expatriate)</option>
                  <option value="Direct Buyer">Direct Buyer (End-User)</option>
                  <option value="Hospitality ROI">Hospitality ROI Investor</option>
                  <option value="Corporate / Institutional">Corporate / Institutional</option>
                </select>
              </div>
            </div>

            <div class="form-group">
              <label class="form-label">Inquiry Message & Due Diligence Notes</label>
              <textarea v-model="leadForm.message" rows="3" placeholder="Buyer requirements, budget constraints, timeline..." class="form-textarea"></textarea>
            </div>
          </div>

          <div class="admin-modal-footer">
            <button type="button" class="btn btn-sm btn-outline-white" @click="showAddLeadModal = false">Cancel</button>
            <button type="submit" class="btn btn-sm btn-emerald" :disabled="isSubmitting">
              {{ isSubmitting ? 'Saving...' : 'Save Lead' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- ======================================================================
         MODAL: CONFIRM SOFT DELETE LEAD
         ====================================================================== -->
    <div v-if="deleteLeadTarget" class="admin-modal-overlay" @click.self="deleteLeadTarget = null">
      <div class="admin-modal-card animate-fade-in-up" style="max-width:440px;">
        <div class="admin-modal-header">
          <h3 class="admin-modal-title" style="color: #F87171; display:flex; align-items:center; gap:8px;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
              <polyline points="3 6 5 6 21 6"></polyline>
              <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
            </svg>
            Delete Lead Inquiry
          </h3>
          <button class="admin-modal-close" aria-label="Close" @click="deleteLeadTarget = null">✕</button>
        </div>
        <div class="admin-modal-body" style="padding: 20px;">
          <p style="color: var(--admin-text-secondary); font-size: 0.92rem; line-height: 1.5; margin-bottom: 14px;">
            Are you sure you want to soft delete the inquiry from <strong style="color: #FFF;">{{ deleteLeadTarget.name }}</strong>?
          </p>
          <div style="background: var(--admin-bg-surface-alt); border: 1px solid var(--admin-border-subtle); padding: 12px 14px; border-radius: var(--radius-sm); font-size: 0.85rem; color: #8E9B8F;">
            <div>Phone: <strong style="color: #FFF;">{{ deleteLeadTarget.phone }}</strong></div>
            <div v-if="deleteLeadTarget.property" style="margin-top:4px;">Property: <strong style="color: #10B981;">{{ deleteLeadTarget.property }}</strong></div>
          </div>
          <p style="color: #9CA3AF; font-size: 0.8rem; margin-top: 12px; line-height: 1.4;">
            This will soft-delete the lead from active pipeline views while keeping it safely recoverable in the Trash.
          </p>
        </div>
        <div class="admin-modal-footer">
          <button type="button" class="btn btn-sm btn-outline-white" :disabled="isDeleting" @click="deleteLeadTarget = null">Cancel</button>
          <button
            type="button"
            class="btn btn-sm"
            :disabled="isDeleting"
            style="background:#EF4444; color:#FFF; display:inline-flex; align-items:center; gap:6px;"
            @click="executeDeleteLead"
          >
            <span>{{ isDeleting ? 'Deleting...' : 'Confirm Soft Delete' }}</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, computed, watch, onMounted } from 'vue'
import { useToast } from '~/composables/useToast'
import { useApiUrl } from '~/composables/useApi'
import { buildQuestions } from '~/utils/leadSurvey'
import LeadActivityPanel from '~/components/admin/crm/LeadActivityPanel.vue'
import LeadVisitsPanel from '~/components/admin/crm/LeadVisitsPanel.vue'
import {
  LEAD_STAGES, dhakaIso, endOfDhakaDay, formatDay, formatTime, formatWhen, inDays, parseApiDate, useLeadCrm,
  type LeadActivity, type LeadVisit, type StaffMember
} from '~/composables/useLeadCrm'

definePageMeta({
  layout: 'admin'
})

const toast = useToast()
const { token } = useAuth()

const view = ref<'active' | 'trash'>('active')
const statusFilter = ref('All')
const focus = ref('All')
const sortBy = ref<'newest' | 'hottest'>('newest')
const search = ref('')
const showAddLeadModal = ref(false)
const isLoading = ref(false)
const isSubmitting = ref(false)
const detailOpen = ref(false)
const selectedId = ref<number | null>(null)

const deleteLeadTarget = ref<any | null>(null)
const isDeleting = ref(false)

const leadsList = ref<any[]>([])
const stages: readonly string[] = LEAD_STAGES

/* ---------- Reading what the website stored ---------- */

// Survey answers are stored in English; show the same Bangla wording the buyer saw.
const answerLabels = new Map<string, string>()
;[...buildQuestions(false), ...buildQuestions(true)].forEach(q => q.options.forEach(o => answerLabels.set(o.value, o.label)))
const say = (value?: string | null) => (value ? answerLabels.get(value) || value : '')

const ctaLabels: Record<string, string> = {
  inline_first_question: 'First question on the property page',
  sidebar_first_question: 'First question in the side panel'
}

// The survey saves its extra answers as "Key: value" lines in `message`; manual leads save plain text.
const readMessage = (message?: string | null) => {
  const out: Record<string, string> = {}
  const notes: string[] = []
  for (const raw of String(message || '').split('\n')) {
    const line = raw.trim()
    if (!line) continue
    const m = line.match(/^([^:]{2,40}):\s*(.+)$/)
    const key = m?.[1]
    if (key === 'Lead score') out.score = m![2]
    else if (key === 'Payment plan') out.payment = m![2]
    else if (key === 'Lives') out.lives = m![2]
    else if (key?.startsWith('Preferred time')) { if (m![2] !== 'Not specified') out.time = m![2] }
    else if (key === 'Price shown') out.price = m![2]
    else if (key === 'CTA') out.cta = m![2]
    else if (key === 'Landing page') out.landing = m![2]
    else if (key === 'Contact consent' || key === 'Ad content' || key === 'Ad term') continue
    else if (line === 'Came from a Facebook ad click') out.fbclick = '1'
    else notes.push(line)
  }
  const score = out.score?.match(/^(HOT|WARM|COLD)\s*\((\d+)\s*\/\s*(\d+)\)/)
  return {
    parsed: out,
    tier: (score?.[1] as 'HOT' | 'WARM' | 'COLD' | undefined) || null,
    score: score ? `${score[2]}/${score[3]}` : '',
    note: notes.join('\n')
  }
}

const normalizeStage = (value?: string | null) => {
  const s = String(value || 'New').trim()
  const known = LEAD_STAGES.find(stage => stage.toLowerCase() === s.toLowerCase())
  return known || s.charAt(0).toUpperCase() + s.slice(1).toLowerCase()
}

// quiet: refresh in the background without hiding the open lead behind a loading message.
const fetchLeads = async (onlyTrashed = false, quiet = false) => {
  if (!quiet) isLoading.value = true
  try {
    const url = onlyTrashed ? useApiUrl('/leads?only_trashed=1') : useApiUrl('/leads')
    const res = await fetch(url, { headers: { Authorization: `Bearer ${token.value}` } })
    if (!res.ok) throw new Error('Lead access failed')
    const json = await res.json()
    if (json && json.success && Array.isArray(json.data)) {
      leadsList.value = json.data.map((l: any) => ({
        ...l,
        ...readMessage(l.message),
        property: l.property_title || l.property || 'Direct Inquiry',
        type: l.buyer_category || l.buyer_type || l.lead_type || l.type || 'Direct Buyer',
        stage: normalizeStage(l.status || l.stage)
      }))
    } else {
      leadsList.value = []
    }
  } catch (err) {
    console.error('Failed to fetch leads:', err)
    toast.error('Could not load inquiries', 'Please check your session and lead access permissions.')
    leadsList.value = []
  } finally {
    isLoading.value = false
  }
}

const changeView = async (next: 'active' | 'trash') => {
  view.value = next
  statusFilter.value = 'All'
  quick.value = 'all'
  detailOpen.value = false
  await fetchLeads(next === 'trash')
}

onMounted(async () => {
  await Promise.all([fetchLeads(), loadOverview()])
  // /admin/leads?lead=12 (linked from the Site Viewings page) opens that lead.
  const wanted = Number(route.query.lead)
  if (wanted && leadsList.value.some(l => l.id === wanted)) {
    selectedId.value = wanted
    detailOpen.value = true
  }
})

/* ---------- Filtering, sorting, selection ---------- */

const matchesFocus = (l: any) => {
  if (focus.value === 'All') return true
  if (focus.value === 'Ready within 3 months') {
    return ['Within 30 days', '1–3 months'].includes(l.investment_readiness) && l.budget_range === 'Listed price fits budget'
  }
  return (l.type || '').includes(focus.value)
}
const matchesSearch = (l: any) => {
  const q = search.value.toLowerCase()
  if (!q) return true
  return [l.name, l.phone, l.property].some(v => String(v || '').toLowerCase().includes(q))
}

const baseLeads = computed(() => leadsList.value.filter(l => matchesFocus(l) && matchesSearch(l) && matchesQuick(l)))

const statusTabs = computed(() => [
  { value: 'All', label: 'All', count: baseLeads.value.length },
  ...stages.map(s => ({ value: s, label: s, count: baseLeads.value.filter(l => l.stage === s).length }))
])

const tierRank: Record<string, number> = { HOT: 0, WARM: 1, COLD: 2 }
const filteredLeads = computed(() => {
  const list = view.value === 'trash' || statusFilter.value === 'All'
    ? [...baseLeads.value]
    : baseLeads.value.filter(l => l.stage === statusFilter.value)
  if (quick.value === 'followups') list.sort((a, b) => (followMs(a) ?? 0) - (followMs(b) ?? 0))
  else if (quick.value === 'visits') list.sort((a, b) => visitKey(a).localeCompare(visitKey(b)))
  else if (sortBy.value === 'hottest') {
    list.sort((a, b) => (tierRank[a.tier] ?? 3) - (tierRank[b.tier] ?? 3) || String(b.created_at).localeCompare(String(a.created_at)))
  }
  return list
})

const hasFilters = computed(() => !!search.value || focus.value !== 'All' || (view.value === 'active' && (statusFilter.value !== 'All' || quick.value !== 'all')))
const clearFilters = () => { search.value = ''; focus.value = 'All'; statusFilter.value = 'All'; quick.value = 'all' }

/* ---------- CRM: owner, follow-up, site visits and activity ---------- */

const crm = useLeadCrm()
const route = useRoute()
const { user: me } = useAuth()

const staff = ref<StaffMember[]>([])
const upcomingVisits = ref<LeadVisit[]>([])
const crmDetail = ref<{ activities: LeadActivity[]; visits: LeadVisit[] }>({ activities: [], visits: [] })
const crmLoading = ref(false)
const tab = ref<'overview' | 'activity' | 'visits'>('overview')
const quick = ref<'all' | 'followups' | 'visits' | 'unassigned' | 'mine'>('all')
const pickedFollowUp = ref('')
const lostTarget = ref<any | null>(null)
const lostReasons = ['Bought elsewhere', 'Price too high', 'Not interested any more', 'Could not reach the buyer', 'Other']
const lostChoice = ref(lostReasons[0])
const lostOther = ref('')

const isClosed = (l: any) => ['Converted', 'Lost'].includes(l.stage)
const followMs = (l: any): number | null => (l.next_follow_up_at ? parseApiDate(l.next_follow_up_at).getTime() : null)
const followState = (l: any): 'none' | 'overdue' | 'today' | 'later' => {
  const t = followMs(l)
  if (t === null) return 'none'
  if (t < Date.now()) return 'overdue'
  return t <= endOfDhakaDay() ? 'today' : 'later'
}
const followText = (l: any) => (followState(l) === 'none' ? 'Not set' : (followState(l) === 'overdue' ? 'Overdue, was ' : '') + formatWhen(l.next_follow_up_at))

// First open visit per lead (the overview is already sorted by date and time).
const nextVisit = computed(() => {
  const map = new Map<number, LeadVisit>()
  for (const v of upcomingVisits.value) if (!map.has(v.lead_id)) map.set(v.lead_id, v)
  return map
})
const visitKey = (l: any) => { const v = nextVisit.value.get(l.id); return v ? v.date + (v.time || '') : '9999' }

const flagFor = (l: any): { tone: string; text: string } | null => {
  if (isClosed(l) || l.deleted_at) return null
  const state = followState(l)
  if (state === 'overdue') return { tone: 'bad', text: 'Follow-up overdue' }
  if (state === 'today') return { tone: 'warn', text: 'Follow up today' }
  const v = nextVisit.value.get(l.id)
  if (v) return { tone: 'info', text: v.visit_type + ' ' + formatDay(v.date) + (v.time ? ', ' + formatTime(v.time) : '') }
  if (state === 'later') return { tone: 'muted', text: 'Follow up ' + formatWhen(l.next_follow_up_at) }
  return null
}

const openLeads = computed(() => leadsList.value.filter(l => !isClosed(l)))
const quickViews = computed(() => [
  { key: 'all' as const, label: 'All leads', count: leadsList.value.length, alert: false },
  { key: 'followups' as const, label: 'Needs follow-up', count: openLeads.value.filter(l => ['overdue', 'today'].includes(followState(l))).length, alert: true },
  { key: 'visits' as const, label: 'Visits coming up', count: openLeads.value.filter(l => nextVisit.value.has(l.id)).length, alert: false },
  { key: 'unassigned' as const, label: 'Unassigned', count: openLeads.value.filter(l => !l.assigned_to).length, alert: false },
  { key: 'mine' as const, label: 'Assigned to me', count: openLeads.value.filter(l => l.assigned_to && l.assigned_to === me.value?.id).length, alert: false }
])
function matchesQuick(l: any) {
  if (view.value === 'trash') return true
  switch (quick.value) {
    case 'followups': return !isClosed(l) && ['overdue', 'today'].includes(followState(l))
    case 'visits': return !isClosed(l) && nextVisit.value.has(l.id)
    case 'unassigned': return !isClosed(l) && !l.assigned_to
    case 'mine': return !!l.assigned_to && l.assigned_to === me.value?.id
    default: return true
  }
}

const detailTabs = computed(() => [
  { key: 'overview' as const, label: 'Overview', count: 0 },
  { key: 'activity' as const, label: 'Activity', count: crmDetail.value.activities.length },
  { key: 'visits' as const, label: 'Site visits', count: crmDetail.value.visits.filter(v => v.status === 'Confirmed').length }
])

async function loadOverview() {
  try {
    const data = await crm.overview()
    staff.value = data.staff
    upcomingVisits.value = data.visits
  } catch {
    // The list still works without owners and visit flags.
  }
}
async function loadDetail() {
  const id = selectedId.value
  if (!id || view.value === 'trash') { crmDetail.value = { activities: [], visits: [] }; return }
  crmLoading.value = true
  try {
    const data = await crm.detail(id)
    if (selectedId.value === id) crmDetail.value = data
  } catch {
    crmDetail.value = { activities: [], visits: [] }
  } finally {
    crmLoading.value = false
  }
}
const refreshAll = () => Promise.all([fetchLeads(view.value === 'trash', true), loadOverview(), loadDetail()])

watch(selectedId, () => {
  tab.value = 'overview'
  pickedFollowUp.value = ''
  crmDetail.value = { activities: [], visits: [] }
  loadDetail()
})

// Copies the fields the CRM can change from the server's answer onto the lead shown in the list.
const syncLead = (lead: any, data: any) => Object.assign(lead, {
  status: data.status, stage: normalizeStage(data.status), lost_reason: data.lost_reason,
  assigned_to: data.assigned_to, next_follow_up_at: data.next_follow_up_at, last_contacted_at: data.last_contacted_at
})

const assignLead = async (lead: any, event: Event) => {
  const select = event.target as HTMLSelectElement
  const previous = lead.assigned_to ?? ''
  try {
    syncLead(lead, await crm.assign(lead.id, select.value ? Number(select.value) : null))
    toast.info('Owner updated', lead.name + (lead.assigned_to ? ' is assigned.' : ' is unassigned.'))
    loadDetail()
  } catch (err: any) {
    select.value = String(previous)
    toast.error('Could not assign', err?.message || 'Try again.')
  }
}

const setFollowUp = async (lead: any, at: string | null) => {
  try {
    syncLead(lead, await crm.followUp(lead.id, at))
    toast.success(at ? 'Follow-up set' : 'Follow-up cleared', at ? followText(lead) : '')
    loadDetail()
  } catch (err: any) {
    toast.error('Could not save', err?.message || 'Try again.')
  }
}
const pickFollowUp = (lead: any) => {
  if (!pickedFollowUp.value) return
  const [date, time] = pickedFollowUp.value.split('T')
  pickedFollowUp.value = ''
  return setFollowUp(lead, dhakaIso(date, time))
}

// Stays open after a status change even if the active tab no longer lists the lead.
const selected = computed(() => leadsList.value.find(l => l.id === selectedId.value) || null)

// Keep a lead open on wide screens; on phones the list stays first until a lead is tapped.
watch(filteredLeads, list => {
  if (!list.length) { selectedId.value = null; return }
  if (!leadsList.value.some(l => l.id === selectedId.value)) {
    selectedId.value = list[0].id
    detailOpen.value = false
  }
}, { immediate: true })

// Opening a different view (active/trash) or clearing the search starts from the first lead again.
watch([view, search, focus], () => {
  if (filteredLeads.value.length && !filteredLeads.value.some(l => l.id === selectedId.value)) {
    selectedId.value = filteredLeads.value[0].id
    detailOpen.value = false
  }
})

const openLead = (lead: any) => {
  selectedId.value = lead.id
  detailOpen.value = true
  // On phones the lead replaces the list, so start reading from its top.
  if (window.innerWidth <= 960) window.scrollTo({ top: 0, behavior: 'instant' })
}

/* ---------- Display helpers ---------- */

const dhaka = 'Asia/Dhaka'
// The API sends some timestamps as "2026-10-01 00:06:51" (UTC, no zone), which a browser would read as local time.
const toDate = (value: string) => new Date(/^\d{4}-\d\d-\d\d \d/.test(value) ? value.replace(' ', 'T') + 'Z' : value)
const fullDate = (iso?: string) => iso
  ? toDate(iso).toLocaleString('en-GB', { timeZone: dhaka, day: 'numeric', month: 'short', hour: 'numeric', minute: '2-digit', hour12: true })
  : ''
const ago = (iso?: string) => {
  if (!iso) return ''
  const minutes = Math.max(0, Math.round((Date.now() - toDate(iso).getTime()) / 60000))
  if (minutes < 1) return 'just now'
  if (minutes < 60) return `${minutes}m ago`
  if (minutes < 1440) return `${Math.floor(minutes / 60)}h ago`
  if (minutes < 10080) return `${Math.floor(minutes / 1440)}d ago`
  return toDate(iso).toLocaleDateString('en-GB', { timeZone: dhaka, day: 'numeric', month: 'short' })
}
const tierLabel = (tier?: string | null) => (tier ? tier.charAt(0) + tier.slice(1).toLowerCase() : 'Unscored')
const isFresh = (l: any) => l.stage === 'New' && !l.deleted_at

const whatsappLink = (l: any) => {
  const text = `আসসালামু আলাইকুম ${l.name}, গ্রাম বাংলা রিয়েল এস্টেট থেকে বলছি। "${l.property}" নিয়ে আপনার আগ্রহের বিষয়ে কথা বলতে চাই। এখন কি কথা বলা সম্ভব?`
  return `https://wa.me/${String(l.phone || '').replace(/[^0-9]/g, '')}?text=${encodeURIComponent(text)}`
}

const copyPhone = async (phone: string) => {
  try {
    await navigator.clipboard.writeText(phone)
    toast.success('Copied', `${phone} copied to the clipboard.`)
  } catch {
    toast.error('Could not copy', 'Select the number and copy it by hand.')
  }
}

type Row = { label: string; value: string; to?: string }
const detailSections = computed(() => {
  const l = selected.value
  if (!l) return []
  const surveyed = !!l.form_version
  const source = l.utm_source === 'facebook' || l.parsed?.fbclick ? 'Facebook ad' : (l.utm_source || 'Website, no ad')
  const sections: { title: string; rows: Row[] }[] = [
    {
      title: 'What the buyer told us',
      rows: [
        surveyed ? { label: 'Plans to use it for', value: say(l.buyer_category) } : { label: 'Buyer type', value: l.lead_type || '' },
        { label: 'Wants to buy', value: say(l.investment_readiness) },
        { label: 'Budget', value: say(l.budget_range) },
        { label: 'Payment', value: say(l.parsed?.payment) },
        { label: 'Lives', value: say(l.parsed?.lives) }
      ]
    },
    {
      title: 'How to reach them',
      rows: [
        { label: 'Prefers', value: l.preferred_contact || '' },
        { label: 'Best time', value: l.callback_time || l.parsed?.time || '' },
        { label: 'Suggested next step', value: l.next_step || '' },
        { label: 'Permission to contact', value: l.contact_consented_at ? `Given on ${fullDate(l.contact_consented_at)}` : (surveyed ? 'Not recorded' : '') }
      ]
    },
    {
      title: 'Property',
      rows: [
        { label: 'Interested in', value: l.property, to: l.property_id ? `/properties/${l.property_id}` : undefined },
        { label: 'Price shown to buyer', value: l.parsed?.price || '' }
      ]
    },
    {
      title: 'Where they came from',
      rows: [
        { label: 'Source', value: source },
        { label: 'Campaign', value: l.utm_campaign || '' },
        { label: 'Ad set', value: l.utm_term || '' },
        { label: 'Ad', value: l.utm_content || '' },
        { label: 'Opened from', value: ctaLabels[l.parsed?.cta] || l.parsed?.cta || '' }
      ]
    }
  ]
  return sections
    .map(s => ({ ...s, rows: s.rows.filter(r => r.value) }))
    .filter(s => s.rows.length)
})

/* ---------- Actions ---------- */

const leadForm = reactive({
  name: '',
  phone: '',
  property: '',
  type: 'NRB Investor',
  message: ''
})

const saveNewLead = async () => {
  isSubmitting.value = true
  try {
    const res = await fetch(useApiUrl('/leads'), {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        Authorization: `Bearer ${token.value}`
      },
      body: JSON.stringify({
        name: leadForm.name,
        phone: leadForm.phone,
        property_title: leadForm.property,
        buyer_type: leadForm.type,
        message: leadForm.message || 'Direct telephone inquiry recorded at GBREL HQ.',
        source: 'Admin CRM Manual'
      })
    })

    if (!res.ok) throw new Error('Failed to create lead in database')

    toast.success('Lead Registered', `Inquiry from ${leadForm.name} saved to CRM inbox.`)
    showAddLeadModal.value = false
    leadForm.name = ''
    leadForm.phone = ''
    leadForm.property = ''
    leadForm.message = ''
    await fetchLeads(view.value === 'trash')
  } catch (err: any) {
    toast.error('Save Failed', err?.message || 'Could not save buyer inquiry.')
  } finally {
    isSubmitting.value = false
  }
}

const setStage = async (lead: any, newStage: string) => {
  if (lead.stage === newStage) return
  if (newStage === 'Lost') {
    lostChoice.value = lostReasons[0]
    lostOther.value = ''
    lostTarget.value = lead
    return
  }
  await applyStage(lead, newStage)
}

const applyStage = async (lead: any, stage: string, reason?: string) => {
  const previous = lead.stage
  lead.stage = stage
  try {
    syncLead(lead, await crm.setStage(lead.id, stage, reason))
    toast.info('Status updated', lead.name + ' is now "' + stage + '".')
    loadDetail()
  } catch (err: any) {
    lead.stage = previous
    toast.error('Update Failed', err?.message || 'Could not update lead stage.')
  }
}

const confirmLost = async () => {
  const lead = lostTarget.value
  if (!lead) return
  const reason = lostChoice.value === 'Other' ? lostOther.value : lostChoice.value
  lostTarget.value = null
  await applyStage(lead, 'Lost', reason)
}

const promptDeleteLead = (lead: any) => {
  deleteLeadTarget.value = lead
}

const executeDeleteLead = async () => {
  if (!deleteLeadTarget.value) return
  const target = deleteLeadTarget.value
  isDeleting.value = true
  try {
    const res = await fetch(useApiUrl(`/leads/${target.id}`), {
      method: 'DELETE',
      headers: {
        Authorization: `Bearer ${token.value}`
      }
    })
    if (!res.ok) {
      const errData = await res.json().catch(() => ({}))
      throw new Error(errData?.message || 'Failed to delete lead from server')
    }

    leadsList.value = leadsList.value.filter(l => l.id !== target.id)
    detailOpen.value = false
    toast.info('Lead Removed', `Lead inquiry from ${target.name} has been moved to the Trash.`)
    deleteLeadTarget.value = null
  } catch (err: any) {
    console.error('Delete lead error:', err)
    toast.error('Deletion Failed', err?.message || 'Could not delete lead inquiry.')
  } finally {
    isDeleting.value = false
  }
}

const restoreLead = async (lead: any) => {
  try {
    const res = await fetch(useApiUrl(`/leads/${lead.id}/restore`), {
      method: 'POST',
      headers: {
        Authorization: `Bearer ${token.value}`
      }
    })
    if (!res.ok) {
      const errData = await res.json().catch(() => ({}))
      throw new Error(errData?.message || 'Failed to restore lead')
    }

    leadsList.value = leadsList.value.filter(l => l.id !== lead.id)
    detailOpen.value = false
    toast.success('Lead Restored', `Lead inquiry from ${lead.name} has been restored to active pipeline.`)
  } catch (err: any) {
    console.error('Restore lead error:', err)
    toast.error('Restore Failed', err?.message || 'Could not restore lead inquiry.')
  }
}
</script>

<style scoped>
.ld { color: var(--admin-text-primary); --ld-danger: #F87171; --ld-hot: #F97316; --ld-warm: #FBBF24; --ld-cold: #7DA0C4; }
.admin-theme-light .ld { --ld-danger: #B91C1C; --ld-hot: #C2410C; --ld-warm: #A16207; --ld-cold: #3B6A99; }

/* Toolbar */
.ld-toolbar { display: flex; flex-wrap: wrap; align-items: center; gap: 12px 16px; margin-bottom: 18px; }
.ld-search { position: relative; flex: 1 1 260px; max-width: 360px; display: flex; align-items: center; }
.ld-search svg { position: absolute; left: 12px; color: var(--admin-text-muted); pointer-events: none; }
.ld-search input { width: 100%; min-height: 40px; padding: 8px 12px 8px 36px; border-radius: 10px; border: 1px solid var(--admin-border-subtle); background: var(--admin-bg-surface); color: inherit; font: inherit; font-size: 0.9rem; }
.ld-search input:focus { outline: 2px solid var(--admin-text-gold); outline-offset: 1px; border-color: transparent; }
.ld-tabs { display: flex; flex-wrap: wrap; gap: 4px; }
.ld-tab { min-height: 40px; padding: 6px 14px; border-radius: 999px; border: 1px solid transparent; background: transparent; color: var(--admin-text-secondary); font: inherit; font-size: 0.88rem; font-weight: 600; cursor: pointer; }
.ld-tab:hover { background: var(--admin-chip-bg); }
.ld-tab.on { background: var(--admin-text-primary); color: var(--admin-bg-surface); }
.ld-count { margin-left: 4px; color: var(--admin-text-muted); font-weight: 500; }
.ld-tab.on .ld-count { color: inherit; opacity: 0.75; }
.ld-trash-note { margin: 0; font-size: 0.88rem; color: var(--admin-text-secondary); }
.ld-controls { display: flex; flex-wrap: wrap; gap: 8px; margin-left: auto; }
.ld-select { min-height: 40px; padding: 6px 30px 6px 12px; border-radius: 10px; border: 1px solid var(--admin-border-subtle); background-color: var(--admin-bg-surface); color: inherit; font: inherit; font-size: 0.86rem; }
.ld-trash-toggle { display: inline-flex; align-items: center; gap: 6px; min-height: 40px; padding: 6px 14px; border-radius: 10px; border: 1px solid var(--admin-border-subtle); background: transparent; color: var(--admin-text-secondary); font: inherit; font-size: 0.86rem; font-weight: 600; cursor: pointer; }
.ld-trash-toggle.on { background: rgba(239, 68, 68, 0.15); border-color: rgba(239, 68, 68, 0.4); color: var(--ld-danger); }
.ld button:focus-visible, .ld a:focus-visible, .ld select:focus-visible { outline: 2px solid var(--admin-text-gold); outline-offset: 2px; }

/* Quick views */
.ld-views { display: flex; flex-wrap: wrap; gap: 8px; margin: -4px 0 18px; }
.ld-views button { display: inline-flex; align-items: center; gap: 6px; min-height: 38px; padding: 4px 14px; border-radius: 10px; border: 1px solid var(--admin-border-hover); background: transparent; color: var(--admin-text-secondary); font: inherit; font-size: 0.86rem; font-weight: 600; cursor: pointer; }
.ld-views button:hover { background: var(--admin-chip-bg); }
.ld-views button.on { background: var(--admin-text-primary); color: var(--admin-bg-surface); border-color: transparent; }
.ld-views button.alert:not(.on) { border-color: var(--ld-danger); color: var(--ld-danger); }
.ld-views button.on .ld-count { color: inherit; opacity: 0.75; }
.ld-views button.alert:not(.on) .ld-count { color: inherit; font-weight: 800; }

/* Flags on list rows */
.ld-flag { font-size: 0.76rem; font-weight: 700; color: var(--admin-text-muted); }
.ld-flag[data-tone='bad'] { color: var(--ld-danger); }
.ld-flag[data-tone='warn'] { color: var(--ld-warm); }
.ld-flag[data-tone='info'] { color: #2563EB; }

/* Owner, follow-up and sections inside a lead */
.ld-lost { margin: 10px 0 0; font-size: 0.88rem; color: var(--ld-danger); font-weight: 600; }
.ld-work { display: grid; grid-template-columns: minmax(180px, 240px) 1fr; gap: 18px 24px; margin-top: 22px; padding: 16px 0 0; border-top: 1px solid var(--admin-border-subtle); }
.ld-field { display: flex; flex-direction: column; gap: 6px; min-width: 0; }
.ld-field > span { font-size: 0.82rem; font-weight: 600; color: var(--admin-text-secondary); }
.ld-follow-now { margin: 0; font-size: 0.95rem; font-weight: 700; }
.ld-follow-now.overdue { color: var(--ld-danger); }
.ld-follow-now.today { color: var(--ld-warm); }
.ld-follow-now.none { color: var(--admin-text-muted); font-weight: 500; }
.ld-chips { display: flex; flex-wrap: wrap; align-items: center; gap: 6px; }
.ld-chips button { min-height: 34px; padding: 2px 12px; border-radius: 999px; border: 1px solid var(--admin-border-hover); background: transparent; color: var(--admin-text-secondary); font: inherit; font-size: 0.82rem; font-weight: 600; cursor: pointer; }
.ld-chips button:hover { background: var(--admin-chip-bg); }
.ld-chips .ld-clear { border-color: transparent; color: var(--admin-text-muted); text-decoration: underline; text-underline-offset: 3px; }
.ld-pick input { min-height: 34px; padding: 2px 8px; border-radius: 8px; border: 1px solid var(--admin-border-hover); background: var(--admin-bg-surface); color: inherit; font: inherit; font-size: 0.82rem; }
.ld-sr { position: absolute; width: 1px; height: 1px; overflow: hidden; clip: rect(0 0 0 0); white-space: nowrap; }
.ld-tabs-detail { display: flex; gap: 2px; margin: 24px 0 18px; border-bottom: 1px solid var(--admin-border-subtle); overflow-x: auto; }
.ld-tabs-detail button { display: inline-flex; align-items: center; gap: 6px; min-height: 44px; padding: 8px 16px; border: 0; border-bottom: 3px solid transparent; background: transparent; color: var(--admin-text-secondary); font: inherit; font-size: 0.92rem; font-weight: 600; cursor: pointer; white-space: nowrap; }
.ld-tabs-detail button:hover { color: var(--admin-text-primary); }
.ld-tabs-detail button.on { color: var(--admin-text-primary); border-bottom-color: var(--admin-text-gold); }
.ld-detail .ld-section:first-of-type { margin-top: 0; padding-top: 0; border-top: 0; }

/* Empty / loading */
.ld-empty { text-align: center; padding: 56px 24px; color: var(--admin-text-muted); border: 1px dashed var(--admin-border-hover); border-radius: 14px; }
.ld-empty strong { display: block; color: var(--admin-text-primary); font-size: 1.05rem; margin-bottom: 6px; }
.ld-empty p { max-width: 420px; margin: 0 auto 16px; font-size: 0.9rem; line-height: 1.55; }

/* Split layout */
.ld-split { display: grid; grid-template-columns: minmax(300px, 380px) 1fr; border: 1px solid var(--admin-border-subtle); border-radius: 14px; background: var(--admin-bg-surface); overflow: hidden; align-items: start; }
.ld-list { list-style: none; margin: 0; padding: 0; max-height: calc(100vh - 290px); min-height: 360px; overflow-y: auto; border-right: 1px solid var(--admin-border-subtle); }
.ld-row { position: relative; display: flex; align-items: center; justify-content: space-between; gap: 12px; width: 100%; padding: 14px 16px 14px 20px; border: 0; border-bottom: 1px solid var(--admin-border-subtle); background: transparent; color: inherit; font: inherit; text-align: left; cursor: pointer; }
.ld-row::before { content: ''; position: absolute; left: 0; top: 0; bottom: 0; width: 4px; background: var(--tier, transparent); }
.ld-row:hover { background: var(--admin-table-row-hover); }
.ld-row.active { background: color-mix(in srgb, var(--admin-text-emerald) 12%, transparent); }
.ld-main { min-width: 0; display: flex; flex-direction: column; gap: 3px; }
.ld-name { display: flex; align-items: center; gap: 8px; font-weight: 700; font-size: 0.98rem; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.ld-new { flex: none; width: 8px; height: 8px; border-radius: 50%; background: var(--admin-text-emerald); }
.ld-prop { font-size: 0.82rem; color: var(--admin-text-muted); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.ld-side { flex: none; display: flex; flex-direction: column; align-items: flex-end; gap: 4px; font-size: 0.76rem; color: var(--admin-text-muted); }
[data-tier='HOT'] { --tier: var(--ld-hot); }
[data-tier='WARM'] { --tier: var(--ld-warm); }
[data-tier='COLD'] { --tier: var(--ld-cold); }
.ld-tier { display: inline-flex; align-items: center; gap: 6px; font-size: 0.78rem; font-weight: 700; color: var(--tier, var(--admin-text-muted)); }
.ld-tier::before { content: ''; width: 8px; height: 8px; border-radius: 50%; background: var(--tier, var(--admin-text-muted)); opacity: 0.9; }
.ld-tier--big { font-size: 0.88rem; padding: 4px 12px; border-radius: 999px; background: color-mix(in srgb, var(--tier, #8E9B8F) 16%, transparent); }

/* Detail */
.ld-detail { padding: 24px 28px 28px; min-width: 0; max-height: calc(100vh - 290px); min-height: 360px; overflow-y: auto; }
.ld-back { display: none; align-items: center; gap: 4px; min-height: 44px; margin: -8px 0 8px -8px; padding: 8px; border: 0; background: transparent; color: var(--admin-text-secondary); font: inherit; font-weight: 600; cursor: pointer; }
.ld-head-top { display: flex; align-items: center; flex-wrap: wrap; gap: 10px 14px; }
.ld-head h2 { margin: 0; font-size: 1.5rem; font-weight: 800; line-height: 1.2; }
.ld-phone { display: flex; align-items: center; gap: 10px; margin-top: 8px; font-size: 1.1rem; }
.ld-copy { min-height: 32px; padding: 2px 12px; border-radius: 8px; border: 1px solid var(--admin-border-hover); background: transparent; color: var(--admin-text-secondary); font: inherit; font-size: 0.8rem; font-weight: 600; cursor: pointer; }
.ld-copy:hover { background: var(--admin-chip-bg); color: var(--admin-text-primary); }
.ld-received { margin: 6px 0 0; font-size: 0.84rem; color: var(--admin-text-muted); }
.ld-deleted { color: var(--ld-danger); font-weight: 700; }

.ld-actions { display: flex; flex-wrap: wrap; gap: 10px; margin: 20px 0 0; }
.ld-act { display: inline-flex; align-items: center; justify-content: center; gap: 8px; min-height: 44px; padding: 8px 20px; border-radius: 10px; border: 1px solid var(--admin-border-hover); color: var(--admin-text-primary); font-weight: 700; font-size: 0.92rem; text-decoration: none; }
.ld-act:hover { background: var(--admin-chip-bg); }
.ld-act--call, .ld-act--wa { color: #fff; }
.ld-act--call { background: #C2530F; border-color: #C2530F; }
.ld-act--call:hover { background: #A8460C; }
.ld-act--wa { background: #178A45; border-color: #178A45; }
.ld-act--wa:hover { background: #126F37; }

.ld-status { display: flex; flex-wrap: wrap; align-items: center; gap: 10px 14px; margin-top: 20px; }
.ld-label { font-size: 0.84rem; font-weight: 600; color: var(--admin-text-secondary); }
.ld-seg { display: inline-flex; flex-wrap: wrap; border: 1px solid var(--admin-border-hover); border-radius: 10px; overflow: hidden; }
.ld-seg button { min-height: 40px; padding: 6px 16px; border: 0; border-right: 1px solid var(--admin-border-subtle); background: transparent; color: var(--admin-text-secondary); font: inherit; font-size: 0.86rem; font-weight: 600; cursor: pointer; }
.ld-seg button:last-child { border-right: 0; }
.ld-seg button:hover { background: var(--admin-chip-bg); }
.ld-seg button.on { background: #0B7A58; color: #fff; }
.ld-muted { font-size: 0.82rem; color: var(--admin-text-muted); }

.ld-section { margin-top: 26px; padding-top: 18px; border-top: 1px solid var(--admin-border-subtle); }
.ld-section h3 { margin: 0 0 8px; font-size: 0.95rem; font-weight: 700; color: var(--admin-text-secondary); }
.ld-section dl { margin: 0; }
.ld-section dl > div { display: grid; grid-template-columns: 170px 1fr; gap: 12px; padding: 7px 0; font-size: 0.92rem; line-height: 1.5; }
.ld-section dt { color: var(--admin-text-muted); }
.ld-section dd { margin: 0; overflow-wrap: anywhere; }
.ld-section dd a { color: var(--admin-text-emerald); }
.ld-note { margin: 0; padding: 12px 14px; border-radius: 10px; background: var(--admin-bg-surface-alt); border: 1px solid var(--admin-border-subtle); font-size: 0.9rem; line-height: 1.6; white-space: pre-line; overflow-wrap: anywhere; }

.ld-foot { margin-top: 30px; padding-top: 16px; border-top: 1px solid var(--admin-border-subtle); }
.ld-delete { min-height: 40px; padding: 4px 0; border: 0; background: transparent; color: var(--ld-danger); font: inherit; font-size: 0.86rem; font-weight: 600; text-decoration: underline; text-underline-offset: 3px; cursor: pointer; }

/* Phones and narrow tablets: the list first, the lead opens full screen */
@media (max-width: 960px) {
  .ld-split { display: block; border-radius: 12px; }
  .ld-list { max-height: none; border-right: 0; }
  .ld-detail { display: none; }
  .ld-split.is-detail .ld-list { display: none; }
  .ld-split.is-detail .ld-detail { display: block; max-height: none; padding: 16px 16px 28px; }
  .ld-back { display: inline-flex; }
  .ld-search { max-width: none; }
  .ld-controls { margin-left: 0; width: 100%; }
  .ld-controls .ld-select { flex: 1 1 140px; }
  .ld-act { flex: 1 1 140px; }
  .ld-work { grid-template-columns: 1fr; }
  .ld-section dl > div { grid-template-columns: 1fr; gap: 0; padding: 8px 0; }
  .ld-section dt { font-size: 0.8rem; }
}
</style>

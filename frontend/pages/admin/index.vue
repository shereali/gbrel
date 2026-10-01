<template>
  <div class="admin-page db">
    <header class="db-head">
      <div>
        <h1>{{ greeting }}, {{ firstName }}</h1>
        <p class="db-date">{{ todayLabel }}</p>
        <p class="db-summary" role="status">{{ summary }}</p>
      </div>
      <div class="db-head-actions">
        <button type="button" class="db-ghost" :disabled="refreshing" @click="refreshAll">{{ refreshing ? 'Refreshing…' : 'Refresh' }}</button>
        <NuxtLink v-if="canLeads" to="/admin/leads" class="db-ghost">Log an inquiry</NuxtLink>
        <NuxtLink to="/admin/properties/create" class="db-primary">Add property</NuxtLink>
      </div>
    </header>

    <div class="db-grid">
      <!-- The day's work, in the order a salesperson would do it -->
      <section class="db-agenda" aria-labelledby="db-today">
        <h2 id="db-today">Today</h2>

        <p v-if="loading" class="db-muted" role="status">Loading your day…</p>

        <template v-else>
          <div v-for="group in agenda" :key="group.key" class="db-group">
            <h3>{{ group.title }} <span class="db-n">{{ group.items.length }}</span></h3>
            <ul>
              <li v-for="item in group.items" :key="item.key" class="db-row" :data-tone="item.tone">
                <div class="db-time">
                  <span>{{ item.time }}</span>
                  <small v-if="item.timeNote">{{ item.timeNote }}</small>
                </div>
                <div class="db-what">
                  <strong>{{ item.title }}</strong>
                  <span>{{ item.detail }}</span>
                </div>
                <div class="db-actions">
                  <a v-if="item.phone" :href="item.tel" class="db-chip db-chip--call">Call</a>
                  <a v-if="item.wa" :href="item.wa" target="_blank" rel="noopener noreferrer" class="db-chip db-chip--wa">WhatsApp</a>
                  <NuxtLink :to="item.to" class="db-chip">{{ item.cta }}</NuxtLink>
                </div>
              </li>
            </ul>
          </div>

          <div v-if="!agenda.length" class="db-clear">
            <strong>Nothing is waiting on you.</strong>
            <p>No calls are due, no visits are booked for today and no listings need review. New inquiries will show up here.</p>
          </div>
        </template>
      </section>

      <!-- Where every open lead stands -->
      <aside v-if="canLeads" class="db-pipeline" aria-labelledby="db-pipe">
        <h2 id="db-pipe">Pipeline</h2>
        <p class="db-muted">{{ openLeadCount }} open {{ openLeadCount === 1 ? 'lead' : 'leads' }}</p>
        <div v-if="openLeadCount" class="db-bar" role="img" :aria-label="pipelineLabel">
          <span v-for="s in pipeline.filter(x => x.open && x.count)" :key="s.stage" :style="{ flexGrow: s.count, background: s.color }" :title="s.stage + ': ' + s.count"></span>
        </div>
        <ul class="db-stages">
          <li v-for="s in pipeline" :key="s.stage">
            <span class="db-swatch" :style="{ background: s.color }" aria-hidden="true"></span>
            <span class="db-stage">{{ s.stage }}</span>
            <strong>{{ s.count }}</strong>
          </li>
        </ul>
        <dl class="db-facts">
          <div><dt>New in the last 7 days</dt><dd>{{ newThisWeek }}</dd></div>
          <div><dt>Not assigned to anyone</dt><dd>{{ unassignedCount }}</dd></div>
        </dl>
        <NuxtLink to="/admin/leads" class="db-link">Open all leads</NuxtLink>
      </aside>
    </div>

    <!-- The inventory: quieter, because it changes less often than the day's work -->
    <section class="db-listings" aria-labelledby="db-list">
      <div class="db-listings-head">
        <div>
          <h2 id="db-list">Listings</h2>
          <p class="db-muted">{{ properties.length }} properties worth ৳ {{ totalCrores }} crore in total</p>
        </div>
        <div class="db-head-actions">
          <button type="button" class="db-ghost" @click="exportReport">Download listings (CSV)</button>
          <NuxtLink to="/admin/properties" class="db-ghost">See all listings</NuxtLink>
        </div>
      </div>

      <div class="db-listings-body">
        <div>
          <h3>By type</h3>
          <ul class="db-types">
            <li v-for="t in typeCounts" :key="t.label"><span>{{ t.label }}</span><strong>{{ t.count }}</strong></li>
          </ul>
          <h3 class="db-gap">Value by region</h3>
          <ul class="db-regions">
            <li v-for="r in regionalStats" :key="r.region">
              <div class="db-region-top"><span>{{ r.region }} <small>{{ r.count }} {{ r.count === 1 ? 'listing' : 'listings' }}</small></span><strong>৳ {{ r.valCrores }} crore</strong></div>
              <div class="db-track"><span :style="{ width: Math.max(r.percentage, 2) + '%' }"></span></div>
            </li>
          </ul>
        </div>

        <div>
          <h3>Newest listings</h3>
          <ul class="db-recent">
            <li v-for="p in properties.slice(0, 6)" :key="p.id">
              <NuxtLink :to="`/admin/properties/${p.id}`" class="db-recent-title">{{ p.title }}</NuxtLink>
              <span class="db-recent-meta">{{ [p.areaName || p.city, p.state].filter(Boolean).join(', ') }}</span>
              <span class="db-recent-price">{{ formatBDT(p.price) }}</span>
              <span class="db-legal" :class="{ ok: p.isRajukApproved }">{{ p.isRajukApproved ? 'Legally cleared' : 'Awaiting legal review' }}</span>
            </li>
          </ul>
          <p v-if="!properties.length" class="db-muted">No listings yet. Add the first property to see it here.</p>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { useProperties } from '~/composables/useProperties'
import { formatBDT } from '~/composables/useCurrency'
import { useToast } from '~/composables/useToast'
import { useApiUrl } from '~/composables/useApi'
import { useAuth } from '~/composables/useAuth'
import { LEAD_STAGES, endOfDhakaDay, formatDay, formatWhen, parseApiDate } from '~/composables/useLeadCrm'
import { telHref, whatsappLink } from '~/utils/contact.mjs'

definePageMeta({ layout: 'admin' })
useHead({ title: 'Today | GBREL Admin' })

const { properties, fetchProperties } = useProperties()
const toast = useToast()
const { token, user, hasPermission, isSuperAdmin } = useAuth()

const DHAKA = 'Asia/Dhaka'
const loading = ref(true)
const refreshing = ref(false)
const leads = ref<any[]>([])
const viewings = ref<any[]>([])
const counts = ref({ pending: 0, listing_requests: 0 })

const canLeads = computed(() => isSuperAdmin.value || hasPermission('leads.view'))
const canViewings = computed(() => isSuperAdmin.value || hasPermission('viewings.view'))
const canReview = computed(() => isSuperAdmin.value || hasPermission('properties.verify_rajuk') || hasPermission('properties.edit'))

const authed = () => ({ headers: { Authorization: `Bearer ${token.value}`, Accept: 'application/json' } })
const load = async (path: string) => {
  try {
    const res = await fetch(useApiUrl(path), authed())
    return res.ok ? (await res.json())?.data ?? null : null
  } catch {
    return null
  }
}

const loadAll = async () => {
  const [, leadData, viewingData, countData] = await Promise.all([
    fetchProperties(),
    canLeads.value ? load('/leads') : null,
    canViewings.value ? load('/viewings') : null,
    load('/admin/sidebar-counts')
  ])
  if (Array.isArray(leadData)) leads.value = leadData
  if (Array.isArray(viewingData)) viewings.value = viewingData
  if (countData) counts.value = { pending: countData.pending || 0, listing_requests: countData.listing_requests || 0 }
}

const refreshAll = async () => {
  refreshing.value = true
  try {
    await loadAll()
    toast.success('Refreshed', 'The dashboard shows the latest data.')
  } catch (err: any) {
    toast.error('Could not refresh', err?.message || 'Check your connection and try again.')
  } finally {
    refreshing.value = false
  }
}

onMounted(async () => {
  try { await loadAll() } finally { loading.value = false }
})

/* ---------- Greeting ---------- */

const hourInDhaka = () => Number(new Date().toLocaleString('en-GB', { timeZone: DHAKA, hour: 'numeric', hour12: false }))
const greeting = computed(() => {
  const h = hourInDhaka()
  return h < 12 ? 'Good morning' : h < 17 ? 'Good afternoon' : 'Good evening'
})
const firstName = computed(() => String(user.value?.name || 'there').trim().split(/\s+/)[0])
const todayLabel = computed(() => new Date().toLocaleDateString('en-GB', { timeZone: DHAKA, weekday: 'long', day: 'numeric', month: 'long' }))

/* ---------- Leads ---------- */

const stageOf = (l: any) => {
  const s = String(l.status || 'New').trim()
  return LEAD_STAGES.find(stage => stage.toLowerCase() === s.toLowerCase()) || s
}
const isClosed = (l: any) => ['Converted', 'Lost'].includes(stageOf(l))
const openLeads = computed(() => leads.value.filter(l => !isClosed(l)))
const openLeadCount = computed(() => openLeads.value.length)

const stageColors: Record<string, string> = {
  New: '#94A3B8', Contacted: '#60A5FA', Qualified: '#3B82F6', 'Site Visit': '#E2651C', Negotiation: '#D97706', Converted: '#0B7A58', Lost: '#9CA3AF'
}
const pipeline = computed(() => LEAD_STAGES.map(stage => ({
  stage, color: stageColors[stage], open: !['Converted', 'Lost'].includes(stage),
  count: leads.value.filter(l => stageOf(l) === stage).length
})))
const pipelineLabel = computed(() => pipeline.value.filter(s => s.open && s.count).map(s => `${s.count} ${s.stage}`).join(', '))
const newThisWeek = computed(() => leads.value.filter(l => Date.now() - parseApiDate(l.created_at).getTime() < 7 * 86400000).length)
const unassignedCount = computed(() => openLeads.value.filter(l => !l.assigned_to).length)

/* ---------- Agenda ---------- */

const clock = (iso: string) => parseApiDate(iso).toLocaleTimeString('en-GB', { timeZone: DHAKA, hour: 'numeric', minute: '2-digit', hour12: true })
const ago = (iso: string) => {
  const m = Math.max(0, Math.round((Date.now() - parseApiDate(iso).getTime()) / 60000))
  if (m < 60) return `${Math.max(m, 1)} min ago`
  if (m < 1440) return `${Math.floor(m / 60)} h ago`
  return `${Math.floor(m / 1440)} d ago`
}
const callBits = (l: any) => ({
  phone: l.phone, tel: telHref(l.phone),
  wa: whatsappLink(l.phone, `আসসালামু আলাইকুম ${l.name}, গ্রাম বাংলা রিয়েল এস্টেট থেকে বলছি। "${l.property_title || 'প্রপার্টি'}" নিয়ে আপনার আগ্রহের বিষয়ে কথা বলতে চাই।`)
})

const callBacks = computed(() => openLeads.value
  .filter(l => l.next_follow_up_at && parseApiDate(l.next_follow_up_at).getTime() <= endOfDhakaDay())
  .sort((a, b) => parseApiDate(a.next_follow_up_at).getTime() - parseApiDate(b.next_follow_up_at).getTime())
  .map(l => {
    const overdue = parseApiDate(l.next_follow_up_at).getTime() < Date.now()
    const sameDay = parseApiDate(l.next_follow_up_at).toLocaleDateString('en-CA', { timeZone: DHAKA }) === new Date().toLocaleDateString('en-CA', { timeZone: DHAKA })
    return {
      key: 'f' + l.id, tone: overdue ? 'late' : 'today',
      time: overdue && !sameDay ? 'Overdue' : clock(l.next_follow_up_at),
      timeNote: overdue ? (sameDay ? 'Due earlier' : formatDay(parseApiDate(l.next_follow_up_at).toLocaleDateString('en-CA', { timeZone: DHAKA }))) : '',
      title: l.name, detail: l.property_title || 'General inquiry',
      ...callBits(l), to: `/admin/leads?lead=${l.id}`, cta: 'Open'
    }
  }))

const todaysVisits = computed(() => {
  const today = new Date().toLocaleDateString('en-CA', { timeZone: DHAKA })
  return viewings.value
    .filter(v => String(v.scheduled_date || '').slice(0, 10) === today && !['completed', 'cancelled', 'no-show'].includes(String(v.status).toLowerCase()))
    .sort((a, b) => String(a.scheduled_at || a.scheduled_time).localeCompare(String(b.scheduled_at || b.scheduled_time)))
    .map(v => ({
      key: 'v' + v.id, tone: 'visit',
      time: String(v.scheduled_time || '').split(' - ')[0].replace(/^0/, '').toLowerCase() || 'Today', timeNote: v.visit_type === 'Video call' ? 'Video call' : '',
      title: v.name, detail: [v.property_title, v.assigned_agent ? 'with ' + v.assigned_agent : '', v.vip_pickup ? 'pickup from ' + (v.pickup_location || 'buyer') : ''].filter(Boolean).join(', '),
      phone: v.phone, tel: telHref(v.phone), wa: '',
      to: v.lead_id ? `/admin/leads?lead=${v.lead_id}` : '/admin/viewings', cta: v.lead_id ? 'Open lead' : 'Open visit'
    }))
})

const untouched = computed(() => openLeads.value
  .filter(l => stageOf(l) === 'New' && !l.last_contacted_at && !(l.next_follow_up_at && parseApiDate(l.next_follow_up_at).getTime() <= endOfDhakaDay()))
  .sort((a, b) => parseApiDate(b.created_at).getTime() - parseApiDate(a.created_at).getTime())
  .slice(0, 6)
  .map(l => ({
    key: 'n' + l.id, tone: 'new', time: ago(l.created_at), timeNote: '',
    title: l.name, detail: [l.property_title || 'General inquiry', /HOT/.test(String(l.message)) ? 'hot lead' : ''].filter(Boolean).join(', '),
    ...callBits(l), to: `/admin/leads?lead=${l.id}`, cta: 'Open'
  })))

const awaitingLegal = computed(() => properties.value.filter(p => !p.isRajukApproved).length)
const reviews = computed(() => {
  if (!canReview.value) return []
  const rows: any[] = []
  if (counts.value.listing_requests) {
    rows.push({ key: 'r1', tone: 'review', time: String(counts.value.listing_requests), timeNote: counts.value.listing_requests === 1 ? 'listing' : 'listings', title: 'Owner submissions to review', detail: 'Owners sent these for approval before they can go live.', to: '/admin/listing-requests', cta: 'Review' })
  }
  if (awaitingLegal.value) {
    rows.push({ key: 'r2', tone: 'review', time: String(awaitingLegal.value), timeNote: awaitingLegal.value === 1 ? 'listing' : 'listings', title: 'Listings awaiting legal review', detail: 'Deeds and RAJUK clearance still need checking.', to: '/admin/approvals', cta: 'Check deeds' })
  }
  return rows
})

const agenda = computed(() => [
  { key: 'calls', title: 'Call back', items: callBacks.value },
  { key: 'visits', title: 'Site visits today', items: todaysVisits.value },
  { key: 'new', title: 'New inquiries nobody has called', items: untouched.value },
  { key: 'review', title: 'Waiting for review', items: reviews.value }
].filter(g => g.items.length))

const plural = (n: number, one: string, many: string) => `${n} ${n === 1 ? one : many}`
const summary = computed(() => {
  if (loading.value) return ''
  const parts = [
    callBacks.value.length ? `${plural(callBacks.value.length, 'call', 'calls')} to make` : '',
    todaysVisits.value.length ? `${plural(todaysVisits.value.length, 'site visit', 'site visits')} today` : '',
    untouched.value.length ? `${plural(untouched.value.length, 'new inquiry', 'new inquiries')} nobody has called` : '',
    reviews.value.reduce((n, r) => n + Number(r.time), 0) ? `${plural(reviews.value.reduce((n, r) => n + Number(r.time), 0), 'listing', 'listings')} waiting for review` : ''
  ].filter(Boolean)
  if (!parts.length) return 'Nothing is due right now.'
  return 'You have ' + (parts.length > 1 ? parts.slice(0, -1).join(', ') + ' and ' + parts[parts.length - 1] : parts[0]) + '.'
})

/* ---------- Listings ---------- */

const totalBDT = computed(() => properties.value.reduce((sum, p) => sum + (Number(p.price) || 0), 0))
const totalCrores = computed(() => (totalBDT.value / 10000000).toFixed(1))
const typeCounts = computed(() => {
  const by = (types: string[]) => properties.value.filter(p => types.includes(p.propertyType)).length
  const rows = [
    { label: 'Plots and land', count: by(['Plot', 'Land']) },
    { label: 'Land shares', count: by(['Land Share']) },
    { label: 'Flats and duplexes', count: by(['Flat', 'Penthouse', 'Duplex']) }
  ]
  const other = properties.value.length - rows.reduce((n, r) => n + r.count, 0)
  return [...rows, ...(other > 0 ? [{ label: 'Other', count: other }] : [])].filter(r => r.count)
})
const regionalStats = computed(() => {
  const total = totalBDT.value || 1
  const grouped: Record<string, { count: number; sum: number }> = {}
  properties.value.forEach(p => {
    const key = String(p.state || 'Other').trim() || 'Other'
    grouped[key] ||= { count: 0, sum: 0 }
    grouped[key].count += 1
    grouped[key].sum += Number(p.price) || 0
  })
  return Object.entries(grouped)
    .map(([region, g]) => ({ region, count: g.count, valCrores: (g.sum / 10000000).toFixed(2).replace(/\.00$/, ''), percentage: Math.round((g.sum / total) * 100), sum: g.sum }))
    .sort((a, b) => b.sum - a.sum)
})

const exportReport = () => {
  try {
    const headers = ['ID', 'Title', 'Category', 'Division', 'Area', 'Price (BDT)', 'Status', 'RAJUK Approved']
    const rows = properties.value.map(p => [p.id, `"${String(p.title).replace(/"/g, '""')}"`, p.propertyType, p.state, `"${p.areaName || ''}"`, p.price, p.status, p.isRajukApproved ? 'Yes' : 'No'])
    const csv = [`GBREL listings report, generated ${new Date().toISOString()}`, `Total listings: ${properties.value.length}, value: BDT ${totalCrores.value} crore`, '', headers.join(','), ...rows.map(r => r.join(','))].join('\r\n')
    const url = URL.createObjectURL(new Blob([csv], { type: 'text/csv;charset=utf-8;' }))
    const link = document.createElement('a')
    link.href = url
    link.download = `GBREL_listings_${new Date().toISOString().slice(0, 10)}.csv`
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)
    URL.revokeObjectURL(url)
    toast.success('Downloaded', `${properties.value.length} listings saved as a CSV file.`)
  } catch (err: any) {
    toast.error('Download failed', err?.message || 'The report could not be created.')
  }
}
</script>

<style scoped>
.db { color: var(--admin-text-primary); --db-late: #DC2626; --db-today: #D97706; --db-visit: #2563EB; --db-new: #0B7A58; --db-review: #7C3AED; }
.admin-theme-light .db { --db-today: #B45309; --db-visit: #1D4ED8; --db-review: #6D28D9; }
.db :is(a, button):focus-visible { outline: 2px solid var(--admin-text-gold); outline-offset: 2px; }

/* Opening: who, when, and what is waiting */
.db-head { display: flex; flex-wrap: wrap; align-items: flex-end; justify-content: space-between; gap: 18px 28px; margin-bottom: 32px; }
.db-head h1 { margin: 0; font-family: 'Anek Bangla', 'Noto Sans Bengali', sans-serif; font-size: clamp(1.9rem, 3.2vw, 2.7rem); font-weight: 800; font-stretch: 110%; line-height: 1.1; }
.db-date { margin: 8px 0 0; font-size: 0.95rem; color: var(--admin-text-muted); }
.db-summary { margin: 10px 0 0; max-width: 60ch; font-size: 1.12rem; line-height: 1.5; color: var(--admin-text-secondary); }
.db-head-actions { display: flex; flex-wrap: wrap; gap: 8px; }
.db-ghost, .db-primary { display: inline-flex; align-items: center; min-height: 42px; padding: 6px 18px; border-radius: 10px; font: inherit; font-size: 0.9rem; font-weight: 700; text-decoration: none; cursor: pointer; }
.db-ghost { border: 1px solid var(--admin-border-hover); background: transparent; color: var(--admin-text-secondary); }
.db-ghost:hover:not(:disabled) { background: var(--admin-chip-bg); color: var(--admin-text-primary); }
.db-ghost:disabled { opacity: 0.7; cursor: wait; }
.db-primary { border: 1px solid #C2530F; background: #C2530F; color: #fff; }
.db-primary:hover { background: #A8460C; }

.db-grid { display: grid; grid-template-columns: minmax(0, 1fr) 320px; gap: 32px 48px; align-items: start; }
.db h2 { margin: 0 0 14px; font-size: 1.2rem; font-weight: 800; }
.db h3 { margin: 0 0 6px; font-size: 0.95rem; font-weight: 700; color: var(--admin-text-secondary); }
.db-muted { margin: 0; font-size: 0.9rem; color: var(--admin-text-muted); line-height: 1.5; }
.db-n { margin-left: 4px; font-weight: 500; color: var(--admin-text-muted); }

/* The agenda: a time rail, what to do, and the one-tap actions */
.db-agenda { min-width: 0; }
.db-group { margin-bottom: 28px; }
.db-group ul, .db-types, .db-regions, .db-recent, .db-stages { list-style: none; margin: 0; padding: 0; }
.db-row { display: grid; grid-template-columns: 96px minmax(0, 1fr) auto; align-items: center; gap: 16px; padding: 14px 0 14px 16px; border-top: 1px solid var(--admin-border-subtle); border-left: 4px solid var(--tone, var(--admin-border-hover)); margin-left: 0; }
.db-row:last-child { border-bottom: 1px solid var(--admin-border-subtle); }
.db-row[data-tone='late'] { --tone: var(--db-late); }
.db-row[data-tone='today'] { --tone: var(--db-today); }
.db-row[data-tone='visit'] { --tone: var(--db-visit); }
.db-row[data-tone='new'] { --tone: var(--db-new); }
.db-row[data-tone='review'] { --tone: var(--db-review); }
.db-time { display: flex; flex-direction: column; font-family: 'Outfit', sans-serif; font-variant-numeric: tabular-nums; }
.db-time span { font-size: 1.2rem; font-weight: 700; line-height: 1.2; }
.db-row[data-tone='late'] .db-time span { color: var(--db-late); }
.db-time small { font-family: inherit; font-size: 0.78rem; color: var(--admin-text-muted); }
.db-what { min-width: 0; display: flex; flex-direction: column; gap: 2px; }
.db-what strong { font-size: 1rem; overflow-wrap: anywhere; }
.db-what span { font-size: 0.88rem; color: var(--admin-text-muted); overflow-wrap: anywhere; }
.db-actions { display: flex; flex-wrap: wrap; justify-content: flex-end; gap: 6px; }
.db-chip { display: inline-flex; align-items: center; min-height: 38px; padding: 4px 14px; border-radius: 999px; border: 1px solid var(--admin-border-hover); color: var(--admin-text-primary); font-size: 0.84rem; font-weight: 700; text-decoration: none; }
.db-chip:hover { background: var(--admin-chip-bg); }
.db-chip--call { background: #C2530F; border-color: #C2530F; color: #fff; }
.db-chip--call:hover { background: #A8460C; }
.db-chip--wa { background: #178A45; border-color: #178A45; color: #fff; }
.db-chip--wa:hover { background: #126F37; }
.db-clear { padding: 36px 0; border-top: 1px solid var(--admin-border-subtle); }
.db-clear strong { font-size: 1.1rem; }
.db-clear p { margin: 6px 0 0; max-width: 52ch; color: var(--admin-text-muted); line-height: 1.55; }

/* Pipeline: one bar for every open lead */
.db-pipeline { padding-left: 32px; border-left: 1px solid var(--admin-border-subtle); }
.db-bar { display: flex; gap: 2px; height: 16px; margin: 14px 0 16px; border-radius: 8px; overflow: hidden; }
.db-bar span { min-width: 6px; }
.db-stages li { display: flex; align-items: center; gap: 10px; padding: 7px 0; border-bottom: 1px solid var(--admin-border-subtle); font-size: 0.92rem; }
.db-swatch { width: 10px; height: 10px; border-radius: 3px; flex: none; }
.db-stage { flex: 1; color: var(--admin-text-secondary); }
.db-facts { margin: 18px 0 0; }
.db-facts > div { display: flex; justify-content: space-between; gap: 12px; padding: 6px 0; font-size: 0.9rem; }
.db-facts dt { color: var(--admin-text-muted); }
.db-facts dd { margin: 0; font-weight: 800; }
.db-link { display: inline-block; margin-top: 14px; color: var(--admin-text-emerald); font-weight: 700; font-size: 0.9rem; text-underline-offset: 3px; }

/* Listings */
.db-listings { margin-top: 56px; padding-top: 28px; border-top: 1px solid var(--admin-border-hover); }
.db-listings-head { display: flex; flex-wrap: wrap; align-items: flex-end; justify-content: space-between; gap: 14px; margin-bottom: 22px; }
.db-listings-head h2 { margin-bottom: 4px; }
.db-listings-body { display: grid; grid-template-columns: minmax(0, 380px) minmax(0, 1fr); gap: 32px 56px; }
.db-types li { display: flex; justify-content: space-between; padding: 7px 0; border-bottom: 1px solid var(--admin-border-subtle); font-size: 0.92rem; }
.db-gap { margin-top: 24px !important; }
.db-regions li { padding: 8px 0; }
.db-region-top { display: flex; justify-content: space-between; gap: 12px; font-size: 0.9rem; }
.db-region-top small { color: var(--admin-text-muted); margin-left: 4px; }
.db-track { height: 6px; margin-top: 6px; border-radius: 3px; background: var(--admin-chip-bg); overflow: hidden; }
.db-track span { display: block; height: 100%; border-radius: 3px; background: var(--admin-text-emerald); }
.db-recent li { display: grid; grid-template-columns: minmax(0, 1fr) auto; gap: 2px 16px; padding: 12px 0; border-bottom: 1px solid var(--admin-border-subtle); }
.db-recent-title { grid-column: 1; font-weight: 700; color: var(--admin-text-primary); text-decoration: none; overflow-wrap: anywhere; }
.db-recent-title:hover { text-decoration: underline; text-underline-offset: 3px; }
.db-recent-meta { grid-column: 1; font-size: 0.84rem; color: var(--admin-text-muted); }
.db-recent-price { grid-column: 2; grid-row: 1; font-weight: 800; white-space: nowrap; text-align: right; }
.db-legal { grid-column: 2; grid-row: 2; font-size: 0.8rem; font-weight: 600; text-align: right; color: var(--db-today); }
.db-legal.ok { color: var(--admin-text-emerald); }

@media (max-width: 1024px) {
  .db-grid { grid-template-columns: 1fr; }
  .db-pipeline { padding-left: 0; border-left: 0; border-top: 1px solid var(--admin-border-subtle); padding-top: 24px; }
  .db-listings-body { grid-template-columns: 1fr; }
}
@media (max-width: 640px) {
  .db-row { grid-template-columns: 72px minmax(0, 1fr); gap: 4px 12px; padding-left: 12px; }
  .db-actions { grid-column: 1 / -1; justify-content: flex-start; margin-top: 6px; }
  .db-head-actions { width: 100%; }
  .db-head-actions > * { flex: 1 1 auto; justify-content: center; }
}
</style>

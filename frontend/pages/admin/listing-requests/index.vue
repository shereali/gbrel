<template>
  <div class="rq">
    <header class="rq-head">
      <div>
        <h1>Owner submissions</h1>
        <p>Properties owners have sent us to sell. Check their papers, ask for anything missing, then approve and publish. Owners never see buyers; we handle every sale.</p>
      </div>
      <NuxtLink to="/list-property" target="_blank" class="rq-link">See the owner sign-up page</NuxtLink>
    </header>

    <nav class="rq-tabs" aria-label="Filter by review stage">
      <button v-for="tab in tabs" :key="tab.key" type="button" :class="{ on: active === tab.key }" :aria-pressed="active === tab.key" @click="select(tab.key)">
        {{ tab.label }}
        <span v-if="countFor(tab.key)" class="rq-count">{{ countFor(tab.key) }}</span>
      </button>
    </nav>

    <p v-if="loading" class="rq-empty" role="status">Loading submissions…</p>
    <p v-else-if="error" class="rq-empty" role="alert">{{ error }} <button type="button" class="rq-link" @click="load">Try again</button></p>
    <div v-else-if="!items.length" class="rq-empty-box">
      <strong>{{ emptyText }}</strong>
      <p>New submissions appear here as soon as an owner presses “যাচাইয়ের জন্য জমা দিন”.</p>
    </div>

    <ul v-else class="rq-list">
      <li v-for="item in items" :key="item.id">
        <NuxtLink :to="`/admin/listing-requests/${item.id}`" class="rq-row">
          <span class="rq-thumb" :style="item.cover ? { backgroundImage: `url(${item.cover})` } : undefined" aria-hidden="true"></span>
          <span class="rq-main">
            <strong>{{ item.title }}</strong>
            <span class="rq-sub">{{ [typeLabel(item.property_type || ''), item.land_size ? `${item.land_size} ${item.land_unit}` : '', item.area_name, item.city].filter(Boolean).join(', ') }}</span>
            <span class="rq-owner">{{ item.owner?.name }} · {{ item.owner?.phone }}</span>
          </span>
          <span class="rq-price">
            <strong>{{ item.expected_price ? priceBn(item.expected_price) : '—' }}</strong>
            <small>{{ item.price_basis === 'Per land unit' ? `per ${item.land_unit}` : item.price_basis === 'Per sqft' ? 'per sqft' : 'total' }} · owner's ask</small>
          </span>
          <span class="rq-docs" :class="{ warn: item.missing_required_documents > 0 }">
            {{ item.documents_verified }}/{{ item.documents_total }} papers verified
            <small v-if="item.missing_required_documents">{{ item.missing_required_documents }} required missing</small>
            <small v-if="item.has_pending_changes">Owner sent edits</small>
          </span>
          <span class="rq-state" :class="item.review_status">{{ stageLabel(item.review_status) }}<small>{{ item.submitted_at ? formatDate(item.submitted_at) : 'not submitted' }}</small></span>
        </NuxtLink>
      </li>
    </ul>
  </div>
</template>

<script setup lang="ts">
import { onMounted, ref, computed } from 'vue'
import { useListingReview, type ReviewQueueItem } from '~/composables/useListingReview'
import { priceBn, typeLabel } from '~/utils/propertyLabels'

definePageMeta({ layout: 'admin' })

const route = useRoute()
const router = useRouter()
const api = useListingReview()
const tabs = [
  { key: '', label: 'All open' },
  { key: 'submitted', label: 'New' },
  { key: 'in_review', label: 'In review' },
  { key: 'changes_requested', label: 'Waiting on owner' },
  { key: 'update_submitted', label: 'Edits to check' },
  { key: 'approved', label: 'Approved' },
  { key: 'rejected', label: 'Rejected' },
  { key: 'draft', label: 'Unfinished drafts' }
]
const stageLabels: Record<string, string> = Object.fromEntries(tabs.filter(t => t.key).map(t => [t.key, t.label]))
const stageLabel = (key: string) => stageLabels[key] || key

const active = ref(String(route.query.stage || ''))
const items = ref<ReviewQueueItem[]>([])
const counts = ref<Record<string, number>>({})
const loading = ref(true)
const error = ref('')

const countFor = (key: string) => key ? counts.value[key] || 0 : Object.entries(counts.value).filter(([k]) => k !== 'draft').reduce((sum, [, n]) => sum + n, 0)
const emptyText = computed(() => active.value ? `Nothing in “${stageLabel(active.value)}”.` : 'No submissions yet.')
const formatDate = (iso: string) => new Date(iso).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' })

const load = async () => {
  loading.value = true
  error.value = ''
  try {
    const result = await api.queue(active.value)
    items.value = result.items
    counts.value = result.counts
  } catch (err: any) {
    error.value = err?.message || 'Could not load submissions.'
  } finally {
    loading.value = false
  }
}
const select = (key: string) => {
  active.value = key
  router.replace({ query: key ? { stage: key } : {} })
  load()
}

onMounted(load)
useSeoMeta({ title: 'Owner submissions | GBREL Admin' })
</script>

<style scoped>
.rq { padding: 8px 0 48px; color: var(--admin-text-primary); }
.rq-head { display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 16px; margin-bottom: 20px; }
.rq-head h1 { font-size: 2rem; font-weight: 800; margin-bottom: 4px; color: var(--admin-text-primary); }
.rq-head p { color: var(--admin-text-secondary); max-width: 70ch; }
.rq-link { background: none; color: #3F7A35; font-weight: 600; text-decoration: underline; text-underline-offset: 3px; cursor: pointer; }
.rq-tabs { display: flex; gap: 4px; overflow-x: auto; border-bottom: 1px solid var(--admin-border-subtle); margin-bottom: 16px; }
.rq-tabs button { display: inline-flex; align-items: center; gap: 8px; white-space: nowrap; padding: 10px 14px; background: none; color: var(--admin-text-secondary); font-weight: 600; cursor: pointer; border-bottom: 3px solid transparent; }
.rq-tabs button.on { color: var(--admin-text-primary); border-bottom-color: #E2651C; }
.rq-count { font-size: .75rem; background: #E2651C; color: #fff; border-radius: 999px; padding: 0 8px; }
.rq-empty { padding: 32px 0; color: var(--admin-text-muted); }
.rq-empty-box { border: 1.5px dashed var(--admin-border-hover); border-radius: 14px; padding: 32px; color: var(--admin-text-secondary); }
.rq-empty-box strong { display: block; font-size: 1.15rem; color: var(--admin-text-primary); margin-bottom: 4px; }
.rq-list { list-style: none; display: flex; flex-direction: column; gap: 8px; padding: 0; }
.rq-row { display: grid; grid-template-columns: 64px minmax(0, 2.2fr) minmax(0, 1fr) minmax(0, 1fr) 150px; align-items: center; gap: 16px; padding: 12px 16px; background: var(--admin-bg-surface); border: 1px solid var(--admin-border-subtle); border-radius: 12px; text-decoration: none; color: inherit; }
.rq-row:hover { border-color: #3F7A35; }
.rq-row:focus-visible { outline: 3px solid #E2651C; outline-offset: 2px; }
.rq-thumb { width: 64px; height: 48px; border-radius: 8px; background: #DDE5D2 linear-gradient(160deg, #B9D08F 40%, #3F7A35 40.5%) center / cover; }
.rq-main { display: flex; flex-direction: column; min-width: 0; }
.rq-main strong { font-family: 'Noto Sans Bengali', sans-serif; font-size: 1rem; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.rq-sub, .rq-owner { font-size: .82rem; color: var(--admin-text-muted); font-family: 'Noto Sans Bengali', sans-serif; }
.rq-owner { color: var(--admin-text-secondary); }
.rq-price { display: flex; flex-direction: column; }
.rq-price strong { font-family: 'Anek Bangla', sans-serif; font-size: 1.15rem; }
.rq-price small, .rq-docs small, .rq-state small { font-size: .75rem; color: var(--admin-text-muted); }
.rq-docs { display: flex; flex-direction: column; font-size: .88rem; }
.rq-docs.warn small:first-of-type { color: #C2530F; font-weight: 600; }
.rq-state { display: flex; flex-direction: column; align-items: flex-start; font-size: .82rem; font-weight: 700; }
.rq-state.submitted, .rq-state.update_submitted { color: #C2530F; }
.rq-state.approved { color: #2E6B2E; }
.rq-state.rejected { color: #9B2C2C; }
@media (max-width: 1100px) {
  .rq-row { grid-template-columns: 56px minmax(0, 1fr) auto; }
  .rq-docs { display: none; }
  .rq-state { grid-column: 2 / 4; flex-direction: row; gap: 8px; }
}
@media (max-width: 640px) { .rq-price { display: none; } .rq-row { grid-template-columns: 48px minmax(0, 1fr); } .rq-thumb { width: 48px; height: 40px; } }
</style>

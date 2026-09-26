<template>
  <div class="rv">
    <nav class="rv-crumb" aria-label="Breadcrumb"><NuxtLink to="/admin/listing-requests">Owner submissions</NuxtLink><span aria-hidden="true">/</span><span>#{{ id }}</span></nav>

    <p v-if="loading" class="rv-muted" role="status">Loading submission…</p>
    <p v-else-if="!p" class="rv-muted" role="alert">This submission could not be found.</p>

    <div v-else class="rv-layout">
      <div class="rv-main">
        <header class="rv-head">
          <div>
            <h1>{{ p.title }}</h1>
            <p class="rv-sub">{{ [labelOf(propertyTypeOptions, p.property_type), p.land_size ? `${toBn(p.land_size)} ${labelOf(landUnitOptions, p.land_unit)}` : '', p.area_name, p.city].filter(Boolean).join(', ') }}</p>
          </div>
          <span class="rv-stage" :class="p.review_status">{{ p.is_live ? 'Live on site' : stageLabels[p.review_status] || p.review_status }}</span>
        </header>

        <section class="rv-owner" aria-label="Owner">
          <div><span>Owner</span><strong>{{ p.owner?.name }}</strong></div>
          <div><span>Phone</span><a :href="`tel:${p.owner?.phone}`">{{ p.owner?.phone }}</a></div>
          <div v-if="p.owner?.email"><span>Email</span><a :href="`mailto:${p.owner.email}`">{{ p.owner.email }}</a></div>
          <div><span>Submitted</span><strong>{{ p.submitted_at ? formatDate(p.submitted_at) : 'Not yet' }}</strong></div>
          <div v-if="d.bestTimeToCall"><span>Best time to call</span><strong>{{ d.bestTimeToCall }}</strong></div>
        </section>

        <!-- Owner's edits to an approved/live listing -->
        <section v-if="pendingKeys.length" class="rv-card rv-changes" aria-labelledby="rv-changes">
          <h2 id="rv-changes">Owner sent changes</h2>
          <p class="rv-muted">These are not on the site yet. Apply them to update the listing, or discard.</p>
          <table class="rv-diff">
            <thead><tr><th>Field</th><th>Now</th><th>Owner wants</th></tr></thead>
            <tbody>
              <tr v-for="key in pendingKeys" :key="key"><th>{{ key }}</th><td>{{ show((p as any)[key]) }}</td><td>{{ show(p.owner_pending_changes[key]) }}</td></tr>
            </tbody>
          </table>
          <div class="rv-row-actions">
            <button type="button" class="rv-btn rv-btn--go" :disabled="busy" @click="decideChanges('apply')">Apply changes</button>
            <button type="button" class="rv-btn" :disabled="busy" @click="decideChanges('discard')">Discard</button>
          </div>
        </section>

        <section class="rv-card" aria-labelledby="rv-facts">
          <h2 id="rv-facts">What the owner told us</h2>
          <div class="rv-facts">
            <dl>
              <h3>Property</h3>
              <div v-for="row in propertyRows" :key="row[0]"><dt>{{ row[0] }}</dt><dd>{{ row[1] }}</dd></div>
            </dl>
            <dl>
              <h3>Ownership</h3>
              <div v-for="row in ownershipRows" :key="row[0]" :class="{ flag: row[2] }"><dt>{{ row[0] }}</dt><dd>{{ row[1] }}</dd></div>
            </dl>
            <dl>
              <h3>Price & timing</h3>
              <div v-for="row in priceRows" :key="row[0]"><dt>{{ row[0] }}</dt><dd>{{ row[1] }}</dd></div>
            </dl>
          </div>
          <p v-if="p.description" class="rv-desc"><strong>Description</strong>{{ p.description }}</p>
          <p v-if="d.notes" class="rv-desc"><strong>Note to GBREL</strong>{{ d.notes }}</p>
          <p v-if="d.disputeNote" class="rv-desc rv-flag"><strong>Dispute / case</strong>{{ d.disputeNote }}</p>
        </section>

        <section class="rv-card" aria-labelledby="rv-docs">
          <h2 id="rv-docs">Ownership papers</h2>
          <p class="rv-muted">Private files. Open each one, check it against the owner's answers, then mark it verified or not accepted (the owner sees your note).</p>
          <ul class="rv-docs">
            <li v-for="type in documentTypesInUse" :key="type.key">
              <div class="rv-doc-type">
                <strong>{{ type.label }}</strong>
                <span :class="{ miss: type.required && !docsOf(type.key).length }">{{ type.required ? (docsOf(type.key).length ? 'Required' : 'Required · not uploaded') : 'If applicable' }}</span>
              </div>
              <ul v-if="docsOf(type.key).length" class="rv-files">
                <li v-for="doc in docsOf(type.key)" :key="doc.id" :class="doc.status">
                  <button type="button" class="rv-file" @click="openDoc(doc.id)">{{ doc.original_name }}</button>
                  <span class="rv-doc-status">{{ docStatus[doc.status] }}</span>
                  <input v-model="docNotes[doc.id]" class="rv-note" type="text" placeholder="Note to owner (needed if not accepted)" :aria-label="`Note for ${doc.original_name}`" />
                  <button type="button" class="rv-btn rv-btn--sm rv-btn--go" :disabled="busy || doc.status === 'verified'" @click="setDoc(doc.id, 'verified')">Verified</button>
                  <button type="button" class="rv-btn rv-btn--sm rv-btn--warn" :disabled="busy" @click="setDoc(doc.id, 'rejected')">Not accepted</button>
                </li>
              </ul>
            </li>
          </ul>
        </section>

        <section class="rv-card" aria-labelledby="rv-photos">
          <h2 id="rv-photos">Owner's photos</h2>
          <div v-if="p.images?.length" class="rv-photos"><img v-for="(url, i) in p.images" :key="url" :src="url" :alt="`Photo ${i + 1}`" loading="lazy" /></div>
          <p v-else class="rv-muted">No photos. Plan a site visit to take them.</p>
        </section>

        <section v-if="p.owner_agreement" class="rv-card" aria-labelledby="rv-terms">
          <h2 id="rv-terms">Terms the owner accepted</h2>
          <p>Accepted {{ formatDate(p.owner_agreement.accepted_at) }} · service charge {{ p.owner_agreement.commission_percent }}% · version {{ p.owner_agreement.terms_version }}</p>
          <details><summary>Show the exact wording</summary><ol class="rv-terms"><li v-for="t in (p.owner_agreement.terms || '').split('\n').filter(Boolean)" :key="t">{{ t.replaceAll('{commission}', String(p.owner_agreement.commission_percent)) }}</li></ol></details>
        </section>
      </div>

      <aside class="rv-side" aria-label="Decision">
        <div class="rv-card rv-decide">
          <h2>Decision</h2>
          <ol class="rv-track">
            <li v-for="(stage, i) in trackStages" :key="stage" :class="{ done: trackIndex > i, now: trackIndex === i }">{{ stage }}</li>
          </ol>
          <p v-if="p.review_note" class="rv-last"><strong>Last note to owner</strong>{{ p.review_note }}</p>
          <p v-if="p.missing_required_documents?.length" class="rv-warn">Missing: {{ p.missing_required_documents.map((x: any) => x.label).join(', ') }}</p>

          <button v-if="p.review_status === 'submitted'" type="button" class="rv-btn rv-btn--go rv-wide" :disabled="busy" @click="act('start_review')">Start review</button>

          <template v-if="['submitted', 'in_review', 'approved'].includes(p.review_status)">
            <label class="rv-field"><span>Message to the owner</span><textarea v-model="note" rows="3" placeholder="What they need to add or fix" /></label>
            <button type="button" class="rv-btn rv-wide" :disabled="busy" @click="act('request_changes', note)">Ask owner for changes</button>
          </template>

          <button v-if="['submitted', 'in_review'].includes(p.review_status)" type="button" class="rv-btn rv-btn--go rv-wide" :disabled="busy" @click="act('approve')">Approve (papers & agreement done)</button>

          <template v-if="['approved', 'update_submitted'].includes(p.review_status)">
            <button type="button" class="rv-btn rv-wide" :disabled="busy" @click="copyFacts">Copy owner facts to the public listing</button>
            <NuxtLink :to="`/admin/properties/${p.id}/edit`" class="rv-btn rv-wide">Edit public listing (price, photos, text)</NuxtLink>
            <button v-if="!p.is_live" type="button" class="rv-btn rv-btn--sun rv-wide" :disabled="busy" @click="act('publish')">Publish on the website</button>
            <button v-else type="button" class="rv-btn rv-wide" :disabled="busy" @click="act('unpublish')">Take off the website</button>
            <a v-if="p.is_live" :href="`/properties/${p.id}`" target="_blank" class="rv-link">Open live page</a>
          </template>

          <details v-if="!['rejected', 'draft'].includes(p.review_status)" class="rv-reject">
            <summary>Reject this property</summary>
            <label class="rv-field"><span>Reason (the owner will see this)</span><textarea v-model="rejectNote" rows="3" /></label>
            <button type="button" class="rv-btn rv-btn--warn rv-wide" :disabled="busy" @click="act('reject', rejectNote)">Reject</button>
          </details>
          <p v-if="error" class="rv-error" role="alert">{{ error }}</p>
        </div>
      </aside>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, onMounted, reactive, ref } from 'vue'
import { useListingReview, ownerFactsToBuyerDetails } from '~/composables/useListingReview'
import { useOwnerListings } from '~/composables/useOwnerListings'
import { useSettings } from '~/composables/useSettings'
import { useToast } from '~/composables/useToast'
import { priceBn, toBn } from '~/utils/propertyLabels'
import {
  allOwnersAgreeOptions, completionOptions, facingOptions, labelOf, landUnitOptions, landUseOptions, mutationOptions, noneExistsOptions,
  ownershipSourceOptions, possessionOptions, priceBasisOptions, propertyTypeOptions, sellTimelineOptions, submitterRoleOptions, yesNo
} from '~/utils/ownerListingOptions'

definePageMeta({ layout: 'admin' })

const route = useRoute()
const toast = useToast()
const api = useListingReview()
const { openDocument } = useOwnerListings()
const { settings, fetchSettings } = useSettings()

const id = Number(route.params.id)
const p = ref<any>(null)
const loading = ref(true)
const busy = ref(false)
const error = ref('')
const note = ref('')
const rejectNote = ref('')
const docNotes = reactive<Record<number, string>>({})

const stageLabels: Record<string, string> = { draft: 'Unfinished draft', submitted: 'New', in_review: 'In review', changes_requested: 'Waiting on owner', approved: 'Approved', update_submitted: 'Edits to check', rejected: 'Rejected' }
const docStatus: Record<string, string> = { pending: 'Not checked', verified: 'Verified', rejected: 'Not accepted' }
const trackStages = ['Submitted', 'In review', 'Approved', 'Live']
const trackIndex = computed(() => {
  if (!p.value) return 0
  if (p.value.is_live) return 4
  return ({ submitted: 0, changes_requested: 1, in_review: 1, approved: 2, update_submitted: 2, rejected: 1 } as Record<string, number>)[p.value.review_status] ?? 0
})

const d = computed<Record<string, any>>(() => p.value?.owner_details || {})
const na = '—'
const propertyRows = computed(() => [
  ['Type', labelOf(propertyTypeOptions, p.value.property_type) || na],
  ['Public address', p.value.address || na],
  ['Full address (private)', d.value.fullAddress || na],
  ['Mouza', d.value.mouza || na],
  ['Dag no.', d.value.dagNumbers || na],
  ['Khatian no.', d.value.khatianNumbers || na],
  ['Land', p.value.land_size ? `${toBn(p.value.land_size)} ${labelOf(landUnitOptions, p.value.land_unit)}` : na],
  ['Built area', p.value.square_footage ? `${toBn(p.value.square_footage)} বর্গফুট` : na],
  ['Floors', p.value.total_floors ?? na],
  ['Facing', labelOf(facingOptions, p.value.facing) || na],
  ['Condition', labelOf(completionOptions, p.value.completion_status) || na],
  ['Land use', labelOf(landUseOptions, d.value.landUse) || na],
  ['Corner plot', labelOf(yesNo, d.value.cornerPlot) || na],
  ['Road width', d.value.roadWidth ? `${toBn(d.value.roadWidth)} ফুট` : na],
  ['On the land now', d.value.buildingDescription || na]
])
const ownershipRows = computed(() => [
  ['Submitted by', labelOf(submitterRoleOptions, d.value.submitterRole) || na, d.value.submitterRole === 'Authorized representative'],
  ['Number of owners', d.value.ownerCount ?? na, false],
  ['All owners agree', labelOf(allOwnersAgreeOptions, d.value.allOwnersAgree) || na, d.value.allOwnersAgree && d.value.allOwnersAgree !== 'Yes'],
  ['Owned through', labelOf(ownershipSourceOptions, d.value.ownershipSource) || na, false],
  ['Possession', labelOf(possessionOptions, d.value.possession) || na, d.value.possession && d.value.possession !== 'Owner' && d.value.possession !== 'Vacant'],
  ['Namjari', labelOf(mutationOptions, d.value.mutationStatus) || na, d.value.mutationStatus && d.value.mutationStatus !== 'Done'],
  ['Khajna paid through', d.value.taxPaidThrough || na, false],
  ['Bank loan / mortgage', labelOf(noneExistsOptions, d.value.bankLoan) || na, d.value.bankLoan === 'Exists'],
  ['Other bayna / agreement', labelOf(noneExistsOptions, d.value.existingAgreement) || na, d.value.existingAgreement === 'Exists'],
  ['Case or dispute', d.value.disputeOrCase === 'Yes' ? 'হ্যাঁ' : d.value.disputeOrCase === 'No' ? 'না' : na, d.value.disputeOrCase === 'Yes']
])
const priceRows = computed(() => {
  const basis = d.value.priceBasis
  const total = basis === 'Per land unit' && p.value.land_size ? d.value.expectedPrice * p.value.land_size : basis === 'Per sqft' && p.value.square_footage ? d.value.expectedPrice * p.value.square_footage : null
  return [
    ['Owner asks', d.value.expectedPrice ? priceBn(d.value.expectedPrice) : na],
    ['Quoted as', labelOf(priceBasisOptions, basis) || na],
    ...(total ? [['Approx. total', priceBn(total)]] : []),
    ['Negotiable', labelOf(yesNo, d.value.negotiable) || na],
    ['Wants to sell', labelOf(sellTimelineOptions, d.value.sellTimeline) || na]
  ]
})

const documentTypesInUse = computed(() => {
  const types = settings.value.listing_document_types
  const known = new Set(types.map(t => t.key))
  const extra = (p.value?.documents || []).filter((doc: any) => !known.has(doc.document_type)).map((doc: any) => ({ key: doc.document_type, label: doc.document_type, required: false }))
  return [...types, ...extra.filter((t: any, i: number, a: any[]) => a.findIndex(x => x.key === t.key) === i)]
})
const docsOf = (key: string) => (p.value?.documents || []).filter((doc: any) => doc.document_type === key)
const pendingKeys = computed(() => Object.keys(p.value?.owner_pending_changes || {}).filter(key => key !== 'owner_details'))

const formatDate = (iso: string) => new Date(iso).toLocaleString('en-GB', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' })
const show = (value: unknown) => Array.isArray(value) ? `${value.length} item(s)` : value === null || value === undefined || value === '' ? na : String(value)

const run = async (fn: () => Promise<any>, success: string) => {
  busy.value = true
  error.value = ''
  try {
    const result = await fn()
    if (result && result.id) p.value = result
    toast.success(success, '')
    return true
  } catch (err: any) {
    error.value = err?.message || 'Something went wrong.'
    return false
  } finally {
    busy.value = false
  }
}

const act = async (action: string, message?: string) => {
  const labels: Record<string, string> = { start_review: 'Review started', request_changes: 'Owner asked for changes', approve: 'Approved', publish: 'Published', unpublish: 'Taken off the website', reject: 'Rejected' }
  if (await run(() => api.act(id, action, message || undefined), labels[action] || 'Saved')) { note.value = ''; rejectNote.value = '' }
}
const decideChanges = (decision: 'apply' | 'discard') => run(() => api.pendingChanges(id, decision), decision === 'apply' ? 'Changes applied' : 'Changes discarded')
const setDoc = async (documentId: number, status: string) => {
  if (await run(() => api.reviewDocument(documentId, status, docNotes[documentId]), status === 'verified' ? 'Marked verified' : 'Marked not accepted')) await reload()
}
const copyFacts = async () => {
  const current = await api.get(id)
  const buyerDetails = { ...(current.buyer_details || {}), ...ownerFactsToBuyerDetails(current.owner_details || {}), updatedOn: new Date().toISOString().slice(0, 10) }
  await run(() => api.updatePublicDetails(id, { buyer_details: buyerDetails }), 'Owner facts copied to the public listing')
  await reload()
}
const openDoc = async (documentId: number) => { try { await openDocument(documentId) } catch { toast.error('Could not open the file', '') } }

const reload = async () => {
  try { p.value = await api.get(id) } catch { p.value = null }
}

onMounted(async () => {
  await Promise.all([reload(), fetchSettings()])
  loading.value = false
})
useSeoMeta({ title: 'Review submission | GBREL Admin' })
</script>

<style scoped>
.rv { padding: 8px 0 48px; color: var(--admin-text-primary); }
.rv-crumb { display: flex; gap: 8px; font-size: .85rem; color: var(--admin-text-muted); margin-bottom: 12px; }
.rv-crumb a { color: #3F7A35; }
.rv-muted { color: var(--admin-text-muted); font-size: .9rem; }
.rv-layout { display: grid; grid-template-columns: minmax(0, 1fr) 340px; gap: 24px; align-items: start; }
.rv-main { display: flex; flex-direction: column; gap: 16px; min-width: 0; }
.rv-head { display: flex; justify-content: space-between; align-items: flex-start; gap: 16px; }
.rv-head h1 { font-family: 'Anek Bangla', 'Noto Sans Bengali', sans-serif; font-size: 2rem; font-weight: 800; color: var(--admin-text-primary); line-height: 1.2; }
.rv-sub { color: var(--admin-text-secondary); font-family: 'Noto Sans Bengali', sans-serif; }
.rv-stage { flex-shrink: 0; font-weight: 700; font-size: .85rem; padding: 4px 12px; border-radius: 999px; background: var(--admin-bg-surface-alt); }
.rv-stage.submitted, .rv-stage.update_submitted { background: #FBE3D4; color: #8A3A0F; }
.rv-stage.approved { background: #1D4A2A; color: #fff; }
.rv-stage.rejected { background: #F3E1E1; color: #7A2222; }
.rv-owner { display: flex; flex-wrap: wrap; gap: 8px 28px; padding: 14px 18px; border-radius: 12px; background: var(--admin-bg-surface-alt); border: 1px solid var(--admin-border-subtle); }
.rv-owner div { display: flex; flex-direction: column; }
.rv-owner span { font-size: .75rem; color: var(--admin-text-muted); }
.rv-owner a { color: #3F7A35; font-weight: 600; }
.rv-card { background: var(--admin-bg-surface); border: 1px solid var(--admin-border-subtle); border-radius: 14px; padding: 20px 22px; }
.rv-card h2 { font-size: 1.3rem; font-weight: 700; margin-bottom: 6px; color: var(--admin-text-primary); }
.rv-facts { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 20px; margin-top: 12px; }
.rv-facts h3 { font-size: 1rem; font-weight: 700; margin-bottom: 6px; color: #3F7A35; }
.rv-facts dl div { display: flex; justify-content: space-between; gap: 12px; padding: 6px 0; border-bottom: 1px solid var(--admin-border-subtle); font-size: .88rem; }
.rv-facts dt { color: var(--admin-text-muted); }
.rv-facts dd { text-align: right; font-family: 'Noto Sans Bengali', sans-serif; overflow-wrap: anywhere; }
.rv-facts .flag dd { color: #C2530F; font-weight: 700; }
.rv-desc { margin-top: 14px; font-family: 'Noto Sans Bengali', sans-serif; white-space: pre-line; max-width: 80ch; }
.rv-desc strong { display: block; font-family: 'Plus Jakarta Sans', sans-serif; font-size: .8rem; color: var(--admin-text-muted); }
.rv-flag { color: #C2530F; }
.rv-docs { list-style: none; padding: 0; margin-top: 10px; }
.rv-docs > li { padding: 10px 0; border-bottom: 1px solid var(--admin-border-subtle); }
.rv-doc-type { display: flex; justify-content: space-between; gap: 12px; }
.rv-doc-type strong { font-family: 'Noto Sans Bengali', sans-serif; }
.rv-doc-type span { font-size: .78rem; color: var(--admin-text-muted); }
.rv-doc-type span.miss { color: #C2530F; font-weight: 700; }
.rv-files { list-style: none; padding: 0; margin-top: 8px; display: flex; flex-direction: column; gap: 6px; }
.rv-files li { display: grid; grid-template-columns: minmax(0, 1fr) auto minmax(140px, 1fr) auto auto; align-items: center; gap: 8px; padding: 8px 10px; border-radius: 8px; background: var(--admin-bg-surface-alt); }
.rv-files li.verified { box-shadow: inset 3px 0 0 #3F7A35; }
.rv-files li.rejected { box-shadow: inset 3px 0 0 #C2530F; }
.rv-file { background: none; color: #3F7A35; text-decoration: underline; cursor: pointer; text-align: left; overflow-wrap: anywhere; }
.rv-doc-status { font-size: .75rem; color: var(--admin-text-muted); }
.rv-note { min-height: 36px; padding: 6px 10px; border-radius: 8px; border: 1px solid var(--admin-border-hover); background: var(--admin-bg-surface); color: var(--admin-text-primary); font-size: .85rem; }
.rv-photos { display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 8px; margin-top: 8px; }
.rv-photos img { width: 100%; aspect-ratio: 4 / 3; object-fit: cover; border-radius: 8px; }
.rv-terms { padding-left: 20px; margin-top: 8px; font-family: 'Noto Sans Bengali', sans-serif; display: flex; flex-direction: column; gap: 4px; }
.rv-card details summary { cursor: pointer; color: #3F7A35; font-weight: 600; margin-top: 6px; }
.rv-diff { width: 100%; border-collapse: collapse; margin: 10px 0; font-size: .88rem; }
.rv-diff th, .rv-diff td { text-align: left; padding: 6px 8px; border-bottom: 1px solid var(--admin-border-subtle); font-family: 'Noto Sans Bengali', sans-serif; }
.rv-diff td:last-child { color: #2E6B2E; font-weight: 600; }
.rv-changes { border-color: #EFB98F; }
.rv-row-actions { display: flex; gap: 8px; }

.rv-side { position: sticky; top: 90px; }
.rv-decide { display: flex; flex-direction: column; gap: 10px; }
.rv-track { list-style: none; padding: 0; display: grid; grid-template-columns: repeat(4, 1fr); gap: 4px; margin-bottom: 4px; }
.rv-track li { position: relative; padding-top: 12px; font-size: .72rem; color: var(--admin-text-muted); }
.rv-track li::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 6px; border-radius: 3px; background: var(--admin-border-subtle); }
.rv-track li.done::before { background: #3F7A35; }
.rv-track li.now::before { background: #E2651C; }
.rv-track li.now { color: var(--admin-text-primary); font-weight: 700; }
.rv-last { font-size: .88rem; background: var(--admin-bg-surface-alt); border-radius: 8px; padding: 8px 10px; font-family: 'Noto Sans Bengali', sans-serif; white-space: pre-line; }
.rv-last strong { display: block; font-family: 'Plus Jakarta Sans', sans-serif; font-size: .75rem; color: var(--admin-text-muted); }
.rv-warn { font-size: .85rem; color: #C2530F; font-family: 'Noto Sans Bengali', sans-serif; }
.rv-field { display: flex; flex-direction: column; gap: 4px; font-size: .82rem; color: var(--admin-text-secondary); }
.rv-field textarea { padding: 8px 10px; border-radius: 8px; border: 1px solid var(--admin-border-hover); background: var(--admin-bg-surface); color: var(--admin-text-primary); font-family: 'Noto Sans Bengali', sans-serif; resize: vertical; }
.rv-btn { display: inline-flex; align-items: center; justify-content: center; min-height: 42px; padding: 8px 16px; border-radius: 999px; border: 1.5px solid var(--admin-border-hover); background: var(--admin-bg-surface); color: var(--admin-text-primary); font-weight: 700; font-size: .88rem; cursor: pointer; text-decoration: none; }
.rv-btn:hover { border-color: #3F7A35; }
.rv-btn:disabled { opacity: .55; cursor: not-allowed; }
.rv-btn--go { background: #1D4A2A; border-color: #1D4A2A; color: #fff; }
.rv-btn--sun { background: #E2651C; border-color: #E2651C; color: #fff; }
.rv-btn--warn { color: #9B2C2C; border-color: #E5B8B8; }
.rv-btn--sm { min-height: 34px; padding: 4px 12px; font-size: .8rem; }
.rv-wide { width: 100%; }
.rv-link { color: #3F7A35; font-weight: 600; text-align: center; }
.rv-reject summary { cursor: pointer; color: #9B2C2C; font-weight: 600; font-size: .88rem; padding: 6px 0; }
.rv-reject[open] { display: flex; flex-direction: column; gap: 8px; }
.rv-error { color: #9B2C2C; background: #F9E6E6; border-radius: 8px; padding: 8px 10px; font-size: .88rem; }
.rv-btn:focus-visible, .rv-file:focus-visible { outline: 3px solid #E2651C; outline-offset: 2px; }

@media (max-width: 1200px) { .rv-facts { grid-template-columns: 1fr 1fr; } }
@media (max-width: 980px) {
  .rv-layout { grid-template-columns: 1fr; }
  .rv-side { position: static; order: -1; }
  .rv-files li { grid-template-columns: 1fr auto; }
  .rv-note { grid-column: 1 / -1; }
}
@media (max-width: 640px) { .rv-facts { grid-template-columns: 1fr; } }
</style>

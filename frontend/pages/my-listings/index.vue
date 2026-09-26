<template>
  <div class="ml">
    <div class="gb-wrap">
      <header class="ml-head">
        <div>
          <h1>আমার প্রপার্টি</h1>
          <p class="gb-lead">জমা দেওয়া প্রপার্টির যাচাই কোথায় আছে, এখানে দেখুন।</p>
        </div>
        <NuxtLink to="/my-listings/new" class="gb-btn gb-btn--sun">নতুন প্রপার্টি জমা দিন</NuxtLink>
      </header>

      <p v-if="loading" class="ml-empty" role="status">তথ্য আসছে…</p>
      <p v-else-if="loadError" class="ml-empty" role="alert">{{ loadError }} <button type="button" class="gb-link" @click="load">আবার চেষ্টা করুন</button></p>

      <div v-else-if="!listings.length" class="ml-empty-box">
        <h2>এখনো কোনো প্রপার্টি জমা দেননি</h2>
        <p>প্রপার্টির তথ্য, দাম আর কাগজপত্র দিন। আমাদের টিম যাচাই করে যোগাযোগ করবে।</p>
        <NuxtLink to="/my-listings/new" class="gb-btn gb-btn--sun">প্রথম প্রপার্টি জমা দিন</NuxtLink>
      </div>

      <ul v-else class="ml-list">
        <li v-for="item in listings" :key="item.id" class="ml-card">
          <div class="ml-top">
            <div>
              <h2>{{ item.title === 'নতুন প্রপার্টি' ? 'শিরোনাম দেওয়া হয়নি' : item.title }}</h2>
              <p class="ml-meta">{{ [labelOf(propertyTypeOptions, item.property_type), item.area_name, item.city].filter(Boolean).join(', ') || 'তথ্য অসম্পূর্ণ' }}</p>
            </div>
            <span class="ml-badge" :class="item.review_status">{{ item.is_live ? 'ওয়েবসাইটে প্রকাশিত' : reviewStatusLabels[item.review_status] }}</span>
          </div>

          <!-- Where the listing is in the process: a real sequence -->
          <ol class="ml-track" :aria-label="`অবস্থা: ${reviewStatusLabels[item.review_status]}`">
            <li v-for="(stage, i) in stages" :key="stage" :class="{ done: stageIndex(item) > i, now: stageIndex(item) === i }">{{ stage }}</li>
          </ol>

          <div v-if="item.review_status === 'changes_requested' && item.review_note" class="ml-note">
            <strong>আমাদের টিমের বার্তা</strong>
            <p>{{ item.review_note }}</p>
          </div>
          <p v-if="item.missing_required_documents.length && !['approved', 'rejected'].includes(item.review_status)" class="ml-docs">
            {{ toBn(item.missing_required_documents.length) }}টি আবশ্যক কাগজ বাকি: {{ item.missing_required_documents.map(d => d.label).join(', ') }}
          </p>

          <div class="ml-actions">
            <NuxtLink :to="`/my-listings/${item.id}`" class="gb-btn gb-btn--paddy gb-btn--sm">{{ actionLabel(item) }}</NuxtLink>
            <NuxtLink v-if="item.is_live" :to="`/properties/${item.id}`" class="gb-link">ওয়েবসাইটে দেখুন</NuxtLink>
            <span class="ml-updated">শেষ হালনাগাদ: {{ new Date(item.updated_at).toLocaleDateString('bn-BD') }}</span>
          </div>
        </li>
      </ul>

      <aside class="ml-help">
        <p>ক্রেতার সঙ্গে যোগাযোগ, সাইট ভিজিট আর দরদাম GBREL করে। কোনো প্রশ্ন থাকলে<template v-if="settings.contact_phone"> <a :href="`tel:${settings.contact_phone.replace(/[^\d+]/g, '')}`" class="gb-link">{{ settings.contact_phone }}</a> নম্বরে</template> ফোন করুন।</p>
      </aside>
    </div>
  </div>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { reviewStatusLabels, useOwnerListings, type OwnerListing } from '~/composables/useOwnerListings'
import { useSettings } from '~/composables/useSettings'
import { labelOf, propertyTypeOptions } from '~/utils/ownerListingOptions'
import { toBn } from '~/utils/propertyLabels'

const api = useOwnerListings()
const { settings, fetchSettings } = useSettings()
const listings = ref<OwnerListing[]>([])
const loading = ref(true)
const loadError = ref('')

const stages = ['খসড়া', 'জমা', 'যাচাই', 'অনুমোদন', 'প্রকাশিত']
const stageIndex = (item: OwnerListing) => {
  if (item.is_live) return 5
  return ({ draft: 0, changes_requested: 1, submitted: 1, in_review: 2, approved: 3, update_submitted: 3, rejected: 2 } as Record<string, number>)[item.review_status] ?? 0
}
const actionLabel = (item: OwnerListing) => ({
  draft: 'তথ্য দেওয়া শেষ করুন', changes_requested: 'তথ্য ঠিক করে আবার জমা দিন', submitted: 'দেখুন বা ঠিক করুন',
  in_review: 'দেখুন', approved: 'দেখুন বা পরিবর্তন পাঠান', update_submitted: 'দেখুন', rejected: 'দেখুন'
} as Record<string, string>)[item.review_status] || 'দেখুন'

const load = async () => {
  loading.value = true
  loadError.value = ''
  try {
    listings.value = await api.list()
  } catch (err: any) {
    loadError.value = err?.status === 401 ? 'সাইন ইন করা নেই।' : 'তালিকা আনা যায়নি।'
    if (err?.status === 401) navigateTo('/login?redirect=/my-listings')
  } finally {
    loading.value = false
  }
}

onMounted(() => { load(); fetchSettings() })
useSeoMeta({ title: 'আমার প্রপার্টি | গ্রাম বাংলা রিয়েল এস্টেট' })
</script>

<style scoped>
.ml { padding: clamp(32px, 6vw, 72px) 0 96px; }
.ml-head { display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 20px; margin-bottom: 28px; }
.ml-head h1 { font-size: var(--gb-t-h1); font-weight: 800; font-stretch: 110%; }
.ml-empty { padding: 48px 0; color: var(--gb-ink-soft); }
.ml-empty-box { background: var(--gb-sheet); border: 1.5px dashed var(--gb-silt); border-radius: var(--gb-r-lg); padding: 40px; display: flex; flex-direction: column; align-items: flex-start; gap: 12px; }
.ml-empty-box h2 { font-size: 1.5rem; }
.ml-list { list-style: none; display: flex; flex-direction: column; gap: 16px; }
.ml-card { background: var(--gb-sheet); border: 1px solid var(--gb-silt); border-radius: var(--gb-r-lg); padding: clamp(18px, 3vw, 28px); display: flex; flex-direction: column; gap: 14px; }
.ml-top { display: flex; justify-content: space-between; align-items: flex-start; gap: 16px; }
.ml-top h2 { font-size: 1.35rem; font-weight: 700; }
.ml-meta { color: var(--gb-ink-soft); font-size: .92rem; }
.ml-badge { flex-shrink: 0; font-size: .85rem; font-weight: 600; padding: 4px 12px; border-radius: 999px; background: #EEF1E6; color: var(--gb-ink-soft); }
.ml-badge.changes_requested { background: #FBE3D4; color: #8A3A0F; }
.ml-badge.approved { background: var(--gb-paddy); color: #fff; }
.ml-badge.rejected { background: #F3E1E1; color: #7A2222; }
.ml-track { list-style: none; display: grid; grid-template-columns: repeat(5, 1fr); gap: 4px; }
.ml-track li { position: relative; padding-top: 14px; font-size: .82rem; color: var(--gb-ink-soft); }
.ml-track li::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 6px; border-radius: 3px; background: #E3E8DA; }
.ml-track li.done::before { background: var(--gb-leaf); }
.ml-track li.done { color: var(--gb-paddy); }
.ml-track li.now::before { background: var(--gb-sun); }
.ml-track li.now { color: var(--gb-ink); font-weight: 600; }
.ml-note { background: #FBEBDD; border: 1.5px solid #EFB98F; border-radius: var(--gb-r); padding: 12px 16px; }
.ml-note strong { font-family: var(--gb-display); color: var(--gb-paddy); }
.ml-note p { white-space: pre-line; font-size: .95rem; }
.ml-docs { font-size: .9rem; color: var(--gb-sun-deep); }
.ml-actions { display: flex; flex-wrap: wrap; align-items: center; gap: 16px; }
.ml-updated { margin-left: auto; font-size: .82rem; color: var(--gb-ink-soft); }
.ml-help { margin-top: 32px; color: var(--gb-ink-soft); font-size: .95rem; }
@media (max-width: 560px) { .ml-top { flex-direction: column; } .ml-updated { margin-left: 0; } .ml-track li { font-size: .72rem; } }
</style>

<template>
  <div class="lg">
    <header class="gb-wrap lg-head">
      <h1>আমরা সরকারি নিবন্ধিত কোম্পানি</h1>
      <p class="gb-lead">গ্রাম বাংলা রিয়েল এস্টেট লিমিটেড যৌথ মূলধন কোম্পানি ও ফার্মসমূহের পরিদপ্তরে (RJSC) নিবন্ধিত এবং ঢাকা দক্ষিণ সিটি কর্পোরেশনের ট্রেড লাইসেন্সধারী। নিচে দুটি কাগজই দেওয়া আছে। নম্বর মিলিয়ে বা QR কোড স্ক্যান করে নিজেই যাচাই করে নিন।</p>
    </header>

    <section class="lg-board" aria-label="নিবন্ধনের কাগজপত্র">
      <div class="gb-wrap lg-docs">
        <article v-for="(doc, i) in documents" :key="doc.key" class="lg-doc" :class="{ 'lg-doc--flip': i % 2 === 1 }">
          <a :href="doc.full" target="_blank" rel="noopener" class="lg-paper" :aria-label="`${doc.title} বড় করে দেখুন`">
            <span class="lg-tape" aria-hidden="true"></span>
            <img :src="doc.thumb" :alt="doc.title" width="640" :height="doc.height" loading="lazy" decoding="async" />
            <span class="lg-zoom"><Expand :size="15" aria-hidden="true" /> বড় করে দেখুন</span>
          </a>
          <div class="lg-facts">
            <p class="lg-issuer">{{ doc.issuer }}</p>
            <h2>{{ doc.title }}</h2>
            <dl>
              <div v-for="row in doc.rows" :key="row[0]"><dt>{{ row[0] }}</dt><dd>{{ row[1] }}</dd></div>
            </dl>
            <p class="lg-verify"><ScanLine :size="18" aria-hidden="true" /> {{ doc.verify }}</p>
          </div>
        </article>
      </div>
    </section>

    <section class="gb-wrap lg-office" aria-labelledby="lg-office-h">
      <div class="lg-office-text">
        <h2 id="lg-office-h">নিবন্ধিত অফিস</h2>
        <p>ট্রেড লাইসেন্সে যে ঠিকানা আছে, আমাদের অফিসও সেখানে।</p>
        <address>{{ settings.office_address }}</address>
        <p v-if="settings.working_hours" class="lg-hours">{{ settings.working_hours }}</p>
        <NuxtLink to="/contact" class="gb-btn gb-btn--paddy">যোগাযোগ করুন</NuxtLink>
      </div>
      <OfficeMap :address="settings.office_address" :location="settings.map_location" />
    </section>

    <p class="gb-wrap lg-note">ব্যক্তিগত গোপনীয়তার জন্য ট্রেড লাইসেন্সে ব্যবস্থাপনা পরিচালকের জাতীয় পরিচয়পত্র নম্বর ঢেকে দেওয়া হয়েছে।</p>
  </div>
</template>

<script setup lang="ts">
import { computed, onMounted } from 'vue'
import { Expand, ScanLine } from 'lucide-vue-next'
import { useSettings } from '~/composables/useSettings'
import { bnDate } from '~/utils/officeMap'

const { settings, fetchSettings } = useSettings()
onMounted(() => { fetchSettings() })

const documents = computed(() => [
  {
    key: 'rjsc',
    title: 'কোম্পানি নিবন্ধন সনদ (Certificate of Incorporation)',
    issuer: 'যৌথ মূলধন কোম্পানি ও ফার্মসমূহের পরিদপ্তর (RJSC), বাংলাদেশ সরকার',
    thumb: '/docs/legal/certificate-of-incorporation-rjsc-thumb.jpg',
    full: '/docs/legal/certificate-of-incorporation-rjsc.jpg',
    height: 887,
    rows: [
      ['নিবন্ধন নম্বর', settings.value.company_registration_no],
      ['কোম্পানির নাম', 'Grambangla Real Estate Limited'],
      ['নিবন্ধনের তারিখ', bnDate(settings.value.company_registration_date)],
      ['আইন', 'কোম্পানি আইন, ১৯৯৪'],
      ['ধরন', 'লিমিটেড কোম্পানি']
    ].filter(r => r[1]),
    verify: 'সনদটি ডিজিটাল স্বাক্ষরিত। নিচের বাঁ দিকের QR কোড মোবাইলে স্ক্যান করে যাচাই করুন।'
  },
  {
    key: 'dscc',
    title: 'ই-ট্রেড লাইসেন্স',
    issuer: 'ঢাকা দক্ষিণ সিটি কর্পোরেশন',
    thumb: '/docs/legal/trade-license-dscc-thumb.jpg',
    full: '/docs/legal/trade-license-dscc.jpg',
    height: 907,
    rows: [
      ['লাইসেন্স নম্বর', settings.value.trade_license_no],
      ['মেয়াদ', settings.value.trade_license_valid_until ? `${bnDate(settings.value.trade_license_valid_until)} পর্যন্ত` : ''],
      ['ব্যবসার ধরন', 'রিয়েল এস্টেট, জমি ক্রয়-বিক্রয়, উন্নয়ন ও কনস্ট্রাকশন'],
      ['ব্যবস্থাপনা পরিচালক', 'মোঃ আবদুল কাদির তালুকদার'],
      ['ব্যবসা শুরু', '১৫ ডিসেম্বর ২০২২']
    ].filter(r => r[1]),
    verify: 'লাইসেন্সের উপরের বাঁ দিকের QR কোড স্ক্যান করে সিটি কর্পোরেশনের তথ্যের সঙ্গে মিলিয়ে নিন।'
  }
])

useHead({
  title: 'নিবন্ধন ও ট্রেড লাইসেন্স | গ্রাম বাংলা রিয়েল এস্টেট লিমিটেড',
  meta: [{ name: 'description', content: 'গ্রাম বাংলা রিয়েল এস্টেট লিমিটেডের RJSC কোম্পানি নিবন্ধন সনদ ও ঢাকা দক্ষিণ সিটি কর্পোরেশনের ট্রেড লাইসেন্স, অফিসের ঠিকানা ও ম্যাপ।' }]
})
</script>

<style scoped>
.lg { padding: clamp(40px, 7vw, 88px) 0 72px; }
.lg-head { max-width: 860px; margin-bottom: clamp(32px, 5vw, 56px); }
.lg-head h1 { font-size: var(--gb-t-h1); font-weight: 800; font-stretch: 112%; line-height: 1.15; margin-bottom: 16px; color: var(--gb-paddy); }
.lg-head .gb-lead { max-width: 68ch; }

/* The documents sit pinned on a dark field-green board: the one bold moment on the page. */
.lg-board { background: var(--gb-paddy); color: #EEF3E3; padding: clamp(48px, 7vw, 96px) 0; background-image: radial-gradient(rgba(168, 197, 123, .12) 1px, transparent 1px); background-size: 22px 22px; }
.lg-docs { display: flex; flex-direction: column; gap: clamp(56px, 8vw, 104px); }
.lg-doc { display: grid; grid-template-columns: minmax(0, 5fr) minmax(0, 6fr); gap: clamp(28px, 5vw, 72px); align-items: center; }
.lg-doc--flip .lg-paper { order: 2; transform: rotate(1.2deg); }
.lg-paper { position: relative; display: block; background: #fff; padding: 10px; border-radius: 4px; transform: rotate(-1.2deg); box-shadow: 0 30px 60px -24px rgba(0, 0, 0, .55), 0 2px 0 rgba(0, 0, 0, .15); transition: transform .25s ease; }
.lg-paper:hover { transform: rotate(0deg) scale(1.01); }
.lg-paper img { display: block; width: 100%; height: auto; }
.lg-tape { position: absolute; top: -14px; left: 50%; width: 120px; height: 30px; transform: translateX(-50%) rotate(-3deg); background: rgba(168, 197, 123, .82); box-shadow: 0 2px 4px rgba(0, 0, 0, .15); }
.lg-zoom { position: absolute; right: 18px; bottom: 18px; display: inline-flex; align-items: center; gap: 6px; padding: 7px 12px; border-radius: 999px; background: var(--gb-paddy); color: #fff; font-size: .85rem; font-weight: 600; }
.lg-issuer { color: var(--gb-shoot); font-weight: 600; margin-bottom: 8px; }
.lg-facts h2 { font-family: var(--gb-display); font-size: var(--gb-t-h2); font-weight: 700; line-height: 1.2; color: #fff; margin-bottom: 22px; }
.lg-facts dl { display: grid; margin: 0 0 22px; border-top: 1px solid rgba(238, 243, 227, .2); }
.lg-facts dl div { display: grid; grid-template-columns: minmax(120px, 1fr) 2fr; gap: 16px; padding: 12px 0; border-bottom: 1px solid rgba(238, 243, 227, .2); }
.lg-facts dt { color: #C7D6B4; }
.lg-facts dd { margin: 0; font-weight: 600; color: #fff; font-variant-numeric: tabular-nums; overflow-wrap: anywhere; }
.lg-verify { display: flex; gap: 10px; align-items: flex-start; padding: 14px 16px; border-radius: var(--gb-r); background: rgba(255, 255, 255, .07); color: #EEF3E3; line-height: 1.6; }
.lg-verify svg { color: var(--gb-shoot); flex-shrink: 0; margin-top: 3px; }

.lg-office { display: grid; grid-template-columns: minmax(0, 4fr) minmax(0, 7fr); gap: clamp(24px, 5vw, 64px); align-items: center; padding-top: clamp(56px, 8vw, 96px); }
.lg-office h2 { font-size: var(--gb-t-h2); font-weight: 700; color: var(--gb-paddy); margin-bottom: 10px; }
.lg-office p { color: var(--gb-ink-soft); }
.lg-office address { font-style: normal; font-family: var(--gb-display); font-size: 1.35rem; font-weight: 600; line-height: 1.5; color: var(--gb-ink); margin: 18px 0 8px; }
.lg-hours { margin-bottom: 20px; }
.lg-office .gb-btn { margin-top: 12px; }
.lg-note { margin-top: 40px; font-size: var(--gb-t-small); color: var(--gb-ink-soft); }
.lg a:focus-visible { outline: 3px solid var(--gb-sun); outline-offset: 4px; }
@media (max-width: 860px) {
  .lg-doc, .lg-office { grid-template-columns: minmax(0, 1fr); }
  .lg-doc--flip .lg-paper { order: 0; }
  .lg-paper, .lg-doc--flip .lg-paper { transform: none; max-width: 520px; }
  .lg-facts dl div { grid-template-columns: minmax(0, 1fr); gap: 2px; }
}
@media (prefers-reduced-motion: reduce) { .lg-paper { transition: none; } }
</style>

<template>
  <div class="lp">
    <section class="lp-hero">
      <div class="gb-wrap lp-hero-grid">
        <div>
          <h1>জমি বা ফ্ল্যাট বিক্রি করবেন? ক্রেতা খোঁজার কাজ আমাদের।</h1>
          <p class="gb-lead">প্রপার্টির তথ্য আর কাগজপত্র জমা দিন। আমরা যাচাই করে প্রকাশ করি, ক্রেতা খুঁজি, দরদাম করি, বিক্রি সম্পন্ন করে আপনার টাকা বুঝিয়ে দিই।</p>
          <div class="lp-cta">
            <NuxtLink :to="startLink" class="gb-btn gb-btn--sun">প্রপার্টি জমা দিন</NuxtLink>
            <NuxtLink v-if="isOwner" to="/my-listings" class="gb-btn gb-btn--line">আমার প্রপার্টি দেখুন</NuxtLink>
            <NuxtLink v-else-if="!isAuthenticated" to="/login?redirect=/my-listings" class="gb-btn gb-btn--line">সাইন ইন</NuxtLink>
          </div>
        </div>
        <dl class="lp-facts">
          <div><dt>আমাদের সার্ভিস চার্জ</dt><dd>বিক্রয়মূল্যের {{ toBn(settings.owner_commission_percent) }}%</dd></div>
          <div><dt>কখন দিতে হবে</dt><dd>শুধু বিক্রি সম্পন্ন হলে</dd></div>
          <div><dt>আপনার কাগজপত্র</dt><dd>শুধু যাচাই টিম দেখে, প্রকাশ হয় না</dd></div>
        </dl>
      </div>
    </section>

    <section class="gb-section lp-steps-sec" aria-labelledby="lp-steps">
      <div class="gb-wrap lp-two">
        <div class="lp-sticky">
          <h2 id="lp-steps" class="gb-h2">জমা থেকে টাকা হাতে পাওয়া পর্যন্ত</h2>
          <p class="gb-lead">প্রতিটি ধাপে আপনার অ্যাকাউন্টে অবস্থা দেখতে পাবেন।</p>
        </div>
        <ol class="lp-steps">
          <li v-for="(step, i) in steps" :key="step.title" :style="{ '--band': bandColors[i] }">
            <span class="lp-n" aria-hidden="true">{{ toBn(i + 1) }}</span>
            <div>
              <h3>{{ step.title }}</h3>
              <p>{{ step.text }}</p>
            </div>
          </li>
        </ol>
      </div>
    </section>

    <section class="gb-section lp-docs-sec" aria-labelledby="lp-docs">
      <div class="gb-wrap lp-two">
        <div class="lp-sticky">
          <h2 id="lp-docs" class="gb-h2">যে কাগজগুলো লাগবে</h2>
          <p class="gb-lead">স্ক্যান করা PDF বা মোবাইলে তোলা পরিষ্কার ছবি দিলেই হবে। সব একবারে না থাকলেও শুরু করতে পারেন, পরে যোগ করা যায়।</p>
        </div>
        <ul class="lp-docs">
          <li v-for="doc in documentTypes" :key="doc.key">
            <div>
              <strong>{{ doc.label }}</strong>
              <span v-if="doc.hint">{{ doc.hint }}</span>
            </div>
            <em :class="{ req: doc.required }">{{ doc.required ? 'অবশ্যই লাগবে' : 'প্রযোজ্য হলে' }}</em>
          </li>
        </ul>
      </div>
    </section>

    <section class="gb-section lp-terms-sec" aria-labelledby="lp-terms">
      <div class="gb-wrap lp-two">
        <div class="lp-sticky">
          <h2 id="lp-terms" class="gb-h2">যে শর্তে আমরা কাজ করি</h2>
          <p class="gb-lead">জমা দেওয়ার সময় এই শর্তগুলোতে সম্মতি দিতে হবে। যাচাই শেষে বিস্তারিত লিখিত চুক্তি হবে।</p>
        </div>
        <ol class="lp-terms">
          <li v-for="term in ownerTermsList()" :key="term">{{ term }}</li>
        </ol>
      </div>
    </section>

    <section class="gb-section lp-faq-sec" aria-labelledby="lp-faq">
      <div class="gb-wrap lp-faq-wrap">
        <h2 id="lp-faq" class="gb-h2">যা জানতে চাইতে পারেন</h2>
        <details v-for="item in faq" :key="item.q">
          <summary>{{ item.q }}</summary>
          <p>{{ item.a }}</p>
        </details>
        <div class="lp-end">
          <NuxtLink :to="startLink" class="gb-btn gb-btn--sun">প্রপার্টি জমা দিন</NuxtLink>
          <a v-if="settings.contact_phone" :href="`tel:${settings.contact_phone.replace(/[^\d+]/g, '')}`" class="gb-link">প্রশ্ন থাকলে কল করুন {{ settings.contact_phone }}</a>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup lang="ts">
import { computed, onMounted } from 'vue'
import { useAuth } from '~/composables/useAuth'
import { useSettings } from '~/composables/useSettings'
import { toBn } from '~/utils/propertyLabels'

const { isAuthenticated, isOwner } = useAuth()
const { settings, fetchSettings, ownerTermsList } = useSettings()
onMounted(() => { fetchSettings() })

const startLink = computed(() => (isAuthenticated.value ? '/my-listings/new' : '/signup?redirect=/my-listings/new'))
const documentTypes = computed(() => settings.value.listing_document_types)
const bandColors = ['#B9D08F', '#9DBE73', '#7FA85A', '#5C924A', '#3A7234', '#1D4A2A']

const steps = computed(() => [
  { title: 'অ্যাকাউন্ট খুলে তথ্য দিন', text: 'প্রপার্টির ধরন, লোকেশন, আয়তন, মালিকানা আর আপনার প্রত্যাশিত দাম লিখুন। ছবি থাকলে যোগ করুন।' },
  { title: 'কাগজপত্র আপলোড করুন', text: 'দলিল, খতিয়ান, নামজারি, খাজনার দাখিলা, মালিকদের পরিচয়পত্র। কাগজগুলো শুধু আমাদের যাচাই টিম দেখে।' },
  { title: 'আমরা যাচাই করি', text: 'কাগজপত্র মিলিয়ে দেখা, দরকার হলে সরেজমিনে প্রপার্টি দেখা। কিছু বাকি থাকলে অ্যাকাউন্টে জানিয়ে দেব।' },
  { title: 'লিখিত চুক্তি', text: 'দাম, সার্ভিস চার্জ আর বিক্রির শর্ত নিয়ে আপনার সঙ্গে চুক্তি হবে।' },
  { title: 'প্রকাশ ও বিক্রি', text: 'প্রপার্টি ওয়েবসাইটে প্রকাশ করি, বিজ্ঞাপন দিই, ক্রেতা দেখাই আর দরদাম করি। ক্রেতার সঙ্গে সব যোগাযোগ আমরা করি।' },
  { title: 'টাকা আপনার হাতে', text: `বিক্রি সম্পন্ন হলে বিক্রয়মূল্য থেকে ${toBn(settings.value.owner_commission_percent)}% সার্ভিস চার্জ রেখে বাকি টাকা আপনাকে পরিশোধ করি।` }
])

const faq = [
  { q: 'আমি কি ক্রেতার সঙ্গে সরাসরি কথা বলতে পারব?', a: 'না। ক্রেতা খোঁজা, দেখানো, দরদাম ও লেনদেন GBREL করবে। এতে আপনাকে অচেনা মানুষের ফোন সামলাতে হয় না, আর দামের আলোচনা একজায়গা থেকে হয়।' },
  { q: 'জমা দেওয়ার পর তথ্য বদলাতে পারব?', a: 'যাচাই শুরু হওয়ার আগে পর্যন্ত নিজেই বদলাতে পারবেন। প্রকাশের পর কিছু বদলালে আমাদের টিম দেখে নিয়ে তবেই ওয়েবসাইটে দেখাবে।' },
  { q: 'আমার দলিল বা পরিচয়পত্র কারা দেখবে?', a: 'শুধু GBREL-এর যাচাই টিম। এগুলো ওয়েবসাইটে কখনো প্রকাশ করা হয় না, ক্রেতাকেও দেওয়া হয় না।' },
  { q: 'সব কাগজ এখনই না থাকলে?', a: 'যা আছে তা দিয়ে শুরু করুন। বাকি কাগজ পরে আপলোড করা যাবে। যাচাই শেষ করতে তালিকার আবশ্যক কাগজগুলো লাগবে।' },
  { q: 'সার্ভিস চার্জ কখন দিতে হয়?', a: `শুধু বিক্রি সম্পন্ন হলে। বিক্রয়মূল্যের ${settings.value.owner_commission_percent}% রেখে বাকি টাকা আপনাকে দেওয়া হয়। আগে কোনো টাকা দিতে হয় না।` }
]

useSeoMeta({
  title: 'প্রপার্টি বিক্রি করুন | গ্রাম বাংলা রিয়েল এস্টেট',
  description: 'জমি, প্লট বা ফ্ল্যাটের তথ্য ও কাগজপত্র জমা দিন। GBREL যাচাই করে প্রকাশ করে, ক্রেতা খোঁজে ও বিক্রি সম্পন্ন করে।'
})
</script>

<style scoped>
.lp-hero { padding: clamp(40px, 7vw, 88px) 0 clamp(40px, 6vw, 72px); border-bottom: 1px solid var(--gb-silt); }
.lp-hero-grid { display: grid; grid-template-columns: minmax(0, 1.5fr) minmax(0, 1fr); gap: clamp(32px, 6vw, 88px); align-items: end; }
.lp-hero h1 { font-size: clamp(2.2rem, 4.8vw, 3.8rem); font-weight: 800; font-stretch: 112%; line-height: 1.12; margin-bottom: 18px; }
.lp-cta { display: flex; flex-wrap: wrap; gap: 12px; margin-top: 26px; }
.lp-facts { display: flex; flex-direction: column; border-top: 2px solid var(--gb-paddy); }
.lp-facts div { display: flex; justify-content: space-between; gap: 16px; padding: 14px 0; border-bottom: 1px solid var(--gb-silt); }
.lp-facts dt { color: var(--gb-ink-soft); font-size: .95rem; }
.lp-facts dd { font-family: var(--gb-display); font-weight: 700; color: var(--gb-paddy); text-align: right; }

.lp-two { display: grid; grid-template-columns: minmax(0, 5fr) minmax(0, 7fr); gap: clamp(32px, 6vw, 80px); align-items: start; }
.lp-sticky { position: sticky; top: 110px; }
.lp-docs-sec, .lp-faq-sec { background: var(--gb-sheet); border-top: 1px solid var(--gb-silt); border-bottom: 1px solid var(--gb-silt); }

.lp-steps { list-style: none; display: flex; flex-direction: column; }
.lp-steps li { display: grid; grid-template-columns: 56px 1fr; gap: 18px; position: relative; padding-bottom: 28px; }
.lp-steps li::before { content: ''; position: absolute; left: 27px; top: 56px; bottom: 4px; width: 2px; background: var(--band); }
.lp-steps li:last-child::before { display: none; }
.lp-n { display: grid; place-items: center; width: 56px; height: 56px; border-radius: 50%; background: var(--band); color: #fff; font-family: var(--gb-display); font-size: 1.6rem; font-weight: 700; }
.lp-steps li:nth-child(-n+2) .lp-n { color: var(--gb-paddy); }
.lp-steps h3 { font-size: var(--gb-t-h3); font-weight: 700; margin: 10px 0 4px; }
.lp-steps p { color: var(--gb-ink-soft); }

.lp-docs { list-style: none; border-top: 2px solid var(--gb-paddy); }
.lp-docs li { display: flex; justify-content: space-between; align-items: flex-start; gap: 16px; padding: 14px 0; border-bottom: 1px solid var(--gb-silt); }
.lp-docs strong { display: block; font-family: var(--gb-display); font-weight: 600; font-size: 1.08rem; color: var(--gb-paddy); }
.lp-docs span { font-size: .88rem; color: var(--gb-ink-soft); }
.lp-docs em { font-style: normal; font-size: .82rem; white-space: nowrap; padding: 2px 10px; border-radius: 999px; background: #EEF1E6; color: var(--gb-ink-soft); }
.lp-docs em.req { background: var(--gb-paddy); color: #fff; }

.lp-terms { padding-left: 24px; display: flex; flex-direction: column; gap: 14px; }
.lp-terms li { padding-left: 6px; line-height: 1.8; }
.lp-terms li::marker { font-family: var(--gb-display); font-weight: 700; color: var(--gb-sun); }

.lp-faq-wrap { max-width: 820px; }
.lp-faq-wrap details { border-bottom: 1px solid var(--gb-silt); }
.lp-faq-wrap summary { cursor: pointer; padding: 16px 0; font-family: var(--gb-display); font-weight: 600; font-size: 1.1rem; color: var(--gb-paddy); }
.lp-faq-wrap details p { padding: 0 0 18px; color: var(--gb-ink-soft); }
.lp-end { display: flex; flex-wrap: wrap; align-items: center; gap: 18px; margin-top: 32px; }

@media (max-width: 900px) {
  .lp-hero-grid, .lp-two { grid-template-columns: 1fr; }
  .lp-sticky { position: static; }
}
</style>

<template>
  <div class="bp">
    <!-- Hero: the book standing on its own plot of a mouza map -->
    <section ref="heroRef" class="bp-hero">
      <svg class="bp-map" viewBox="0 0 800 600" preserveAspectRatio="xMidYMid slice" aria-hidden="true" focusable="false">
        <g class="bp-plots">
          <path d="M-20 40 L170 20 L205 150 L-10 175 Z" />
          <path d="M170 20 L360 -10 L385 130 L205 150 Z" />
          <path d="M360 -10 L560 10 L540 140 L385 130 Z" />
          <path d="M560 10 L820 -5 L820 160 L540 140 Z" /><text x="680" y="80">২০৫</text>
          <path d="M-10 175 L205 150 L225 330 L-20 350 Z" />
          <path d="M205 150 L385 130 L400 300 L225 330 Z" />
          <path d="M540 140 L820 160 L820 370 L575 345 Z" /><text x="690" y="260">৩০৩</text>
          <path d="M-20 350 L225 330 L245 520 L-20 540 Z" />
          <path d="M225 330 L400 300 L430 480 L245 520 Z" />
          <path d="M575 345 L820 370 L820 620 L600 620 Z" /><text x="700" y="490">৩২১</text>
          <path d="M245 520 L430 480 L600 520 L600 620 L240 620 Z" />
          <path class="bp-road" d="M385 130 L540 140 L575 345 L600 520 L430 480 L400 300 Z" />
          <path d="M400 300 L575 345 L600 520 L430 480 Z" />
        </g>
      </svg>

      <div class="gb-wrap bp-hero-grid">
        <div class="bp-hero-text">
          <p class="bp-kicker">{{ toBn(BOOK.pages) }} পাতার ফ্রি বাংলা ই-বুক</p>
          <h1>সোনার হরিণ আপনার পায়ের নিচে</h1>
          <p class="bp-lead">জমি, ফ্ল্যাট আর ল্যান্ড শেয়ার কীভাবে নিরাপদে কিনবেন, ন্যায্য দামে বেচবেন, আর পুঁজি থাকুক বা না থাকুক এই ব্যবসায় কোন পথে আয় করবেন: সব এক বইয়ে।</p>
          <div class="bp-cta-row">
            <button type="button" class="gb-btn gb-btn--sun bp-cta" @click="openModal('hero')">
              <Download :size="20" aria-hidden="true" /> ফ্রি বইটি ডাউনলোড করুন
            </button>
            <a :href="BOOK.facebookPage" target="_blank" rel="noopener" class="bp-like-link" @click="likeClick('hero')">
              <ThumbsUp :size="17" aria-hidden="true" /> পেজে লাইক দিন
            </a>
          </div>
          <p class="bp-how">একটা প্রশ্নের উত্তর, তারপর নাম আর মোবাইল নম্বর। বইটা সঙ্গে সঙ্গে খুলে যাবে।</p>
        </div>
        <div class="bp-hero-book">
          <div class="bp-stage">
            <svg class="bp-parcel" viewBox="0 0 400 150" aria-hidden="true" focusable="false">
              <path class="bp-gold" d="M30 62 L292 18 L384 104 L88 146 Z" />
            </svg>
            <img :src="BOOK.cover" :alt="`${BOOK.title} বইয়ের প্রচ্ছদ`" width="720" height="1019" fetchpriority="high" />
            <span class="bp-dag" aria-hidden="true">দাগ ১৮৯</span>
          </div>
        </div>
      </div>
    </section>

    <!-- Who it is for -->
    <section class="gb-section bp-who" aria-labelledby="bp-who-title">
      <div class="gb-wrap">
        <h2 id="bp-who-title" class="gb-h2">তিন ধরনের মানুষের জন্য লেখা</h2>
        <div class="bp-who-grid">
          <div v-for="item in audiences" :key="item.who" class="bp-who-item">
            <h3>{{ item.who }}</h3>
            <p>{{ item.gets }}</p>
          </div>
        </div>
      </div>
    </section>

    <!-- The contents, as a khatian ledger -->
    <section class="gb-section bp-ledger-sec" aria-labelledby="bp-ledger-title">
      <div class="gb-wrap bp-ledger-wrap">
        <div class="bp-ledger-intro">
          <h2 id="bp-ledger-title" class="gb-h2">বইয়ের খতিয়ান</h2>
          <p class="gb-lead">১১টি অধ্যায়, প্রিন্ট করে রাখার চেকলিস্ট, জমির মাপের হিসাব আর দরকারি সরকারি ওয়েবসাইটের তালিকা।</p>
          <button type="button" class="gb-btn gb-btn--paddy" @click="openModal('contents')">
            <Download :size="19" aria-hidden="true" /> পুরো বইটি নিন
          </button>
        </div>
        <table class="bp-ledger">
          <caption class="sr-only">বইয়ের অধ্যায়সমূহ</caption>
          <thead><tr><th scope="col">অধ্যায়</th><th scope="col">বিষয়</th></tr></thead>
          <tbody>
            <tr v-for="(c, i) in chapters" :key="c">
              <td>{{ toBn(i + 1) }}</td>
              <td>{{ c }}</td>
            </tr>
            <tr class="bp-ledger-extra"><td>পরিশিষ্ট</td><td>চেকলিস্ট, জমির মাপ, শব্দকোষ ও দরকারি ওয়েবসাইট</td></tr>
          </tbody>
        </table>
      </div>
    </section>

    <!-- One idea from the book -->
    <section class="bp-quote-sec" aria-labelledby="bp-quote">
      <div class="gb-wrap bp-quote-wrap">
        <blockquote id="bp-quote" class="bp-quote">দলিল হাতে থাকলেই জমি আপনার না।</blockquote>
        <div class="bp-pillars">
          <p>মালিকানা দাঁড়িয়ে থাকে পাঁচটা খুঁটির ওপর। একটা না মিললেই বিপদ:</p>
          <ol>
            <li>দলিল ও বায়া দলিলের অবিচ্ছিন্ন চেইন</li>
            <li>খতিয়ানে বিক্রেতার নাম</li>
            <li>নামজারি ও ডিসিআর</li>
            <li>হালনাগাদ ভূমি উন্নয়ন কর</li>
            <li>জমিতে বাস্তব দখল</li>
          </ol>
          <p class="bp-pillars-more">কোনটা কোথায় যাচাই করবেন, ধাপে ধাপে বইয়ের অধ্যায় ২ ও ৩-এ।</p>
        </div>
      </div>
    </section>

    <!-- Final call -->
    <section class="gb-section bp-final" aria-labelledby="bp-final-title">
      <div class="gb-wrap bp-final-wrap">
        <img :src="BOOK.cover" alt="" width="180" height="255" loading="lazy" class="bp-final-cover" />
        <div>
          <h2 id="bp-final-title" class="gb-h2">টাকা নড়ানোর আগে বইটা পড়ে নিন</h2>
          <p class="gb-lead">লেখক {{ BOOK.author }}, গ্রাম বাংলা রিয়েল এস্টেট লিমিটেড। বইটা বিনামূল্যে, বিক্রির জন্য নয়।</p>
          <button type="button" class="gb-btn gb-btn--sun bp-cta" @click="openModal('final')">
            <Download :size="20" aria-hidden="true" /> ফ্রি বইটি ডাউনলোড করুন
          </button>
          <div class="bp-final-social">
            <p>বইটার কথা অন্যকে জানাতে চাইলে:</p>
            <BookSocialActions placement="page" />
          </div>
        </div>
      </div>
    </section>

    <!-- Phones: the download button stays in reach once the hero has scrolled away -->
    <transition name="bp-bar">
      <div v-if="showBar && !modalOpen" class="bp-bar">
        <button type="button" class="gb-btn gb-btn--sun" @click="openModal('sticky_bar')">
          <Download :size="19" aria-hidden="true" /> ফ্রি বইটি ডাউনলোড করুন
        </button>
      </div>
    </transition>

    <BookDownloadModal :open="modalOpen" :source="modalSource" @close="modalOpen = false" />
  </div>
</template>

<script setup lang="ts">
import { onBeforeUnmount, onMounted, ref } from 'vue'
import { Download, ThumbsUp } from 'lucide-vue-next'
import BookDownloadModal from '~/components/BookDownloadModal.vue'
import BookSocialActions from '~/components/BookSocialActions.vue'
import { BOOK } from '~/utils/book'
import { toBn } from '~/utils/propertyLabels'
import { trackPixel } from '~/utils/metaPixel'

// Share previews for crawlers come from server/plugins/share-meta.ts; this sets the tab title in the browser.
useHead({
  title: 'ফ্রি ই-বুক: The Hidden Business of Land | গ্রাম বাংলা রিয়েল এস্টেট',
  meta: [{ name: 'description', content: 'জমি, ফ্ল্যাট ও ল্যান্ড শেয়ার নিরাপদে কেনা, ন্যায্য দামে বেচা আর এই ব্যবসায় আয়ের পথ নিয়ে ৩৭ পাতার ফ্রি বাংলা ই-বুক।' }]
})

const audiences = [
  { who: 'ক্রেতা', gets: 'কোন কাগজ দেখবেন, কোথায় যাচাই করবেন, আর টাকা দেওয়ার আগে ১৫টা ধাপ। সঞ্চয় থাকবে নিরাপদে।' },
  { who: 'বিক্রেতা', gets: 'কাগজের ফাইল কীভাবে গোছাবেন, ঠিক দাম কীভাবে বের করবেন, আর ঝামেলা ছাড়া দ্রুত বেচবেন কীভাবে।' },
  { who: 'উদ্যোক্তা', gets: 'পুঁজি থাকুক বা না থাকুক, জমি-ফ্ল্যাট-ল্যান্ড শেয়ার ঘিরে আয়ের ৮টা পথ আর ৯০ দিনের শুরুর পরিকল্পনা।' }
]

const chapters = [
  'পায়ের নিচের সোনার হরিণ: জমির ব্যবসা আসলে কী',
  'জমির কাগজের ভাষা: খতিয়ান, দলিল, নামজারি',
  'ক্রেতার ১৫ ধাপের যাচাই',
  'রেজিস্ট্রি, খরচ আর নামজারি',
  'বিক্রেতার গাইড',
  'ফ্ল্যাট কেনা: ডেভেলপার যাচাই ও আইনি অধিকার',
  'ল্যান্ড শেয়ার: সুযোগ, ঝুঁকি আর নিরাপদ থাকার নিয়ম',
  'জমির ব্যবসায় আয়ের ৮টি পথ',
  '১২টি ফাঁদ, যেখানে মানুষ সবচেয়ে বেশি ঠকে',
  'প্রবাসীদের জন্য: দূরে থেকেও নিরাপদ বিনিয়োগ',
  '৯০ দিনের রোডম্যাপ'
]

const modalOpen = ref(false)
const modalSource = ref('hero')
const openModal = (source: string) => {
  modalSource.value = source
  modalOpen.value = true
}

const likeClick = (placement: string) =>
  trackPixel('FacebookPageLikeClick', { content_ids: [BOOK.contentId], content_name: BOOK.title, placement })

const heroRef = ref<HTMLElement | null>(null)
const showBar = ref(false)
let observer: IntersectionObserver | undefined

onMounted(() => {
  // The ad's landing view. Lets Meta optimise for people who actually reach the page, not just click.
  setTimeout(() => trackPixel('ViewContent', { content_ids: [BOOK.contentId], content_name: BOOK.title, content_category: 'ebook', content_type: 'product' }), 0)
  if (heroRef.value && 'IntersectionObserver' in window) {
    observer = new IntersectionObserver(([entry]) => { showBar.value = !entry.isIntersecting }, { threshold: 0.05 })
    observer.observe(heroRef.value)
  }
})
onBeforeUnmount(() => observer?.disconnect())
</script>

<style scoped>
/* ---------- Hero ---------- */
.bp-hero { position: relative; overflow: hidden; background: var(--gb-paddy-deep); color: #fff; padding: 40px 0 56px; }
.bp-map { position: absolute; inset: 0; width: 100%; height: 100%; }
.bp-plots path { fill: none; stroke: var(--gb-shoot); stroke-width: 1.2; opacity: .22; }
.bp-plots .bp-road { fill: rgba(168, 197, 123, .06); stroke: none; opacity: 1; }
.bp-plots text { fill: var(--gb-shoot); opacity: .22; font-family: var(--gb-body); font-size: 15px; text-anchor: middle; }
.bp-gold { fill: rgba(201, 162, 74, .22); stroke: #C9A24A; stroke-width: 2.5; stroke-linejoin: round; stroke-dasharray: 900; stroke-dashoffset: 900; animation: bp-trace 1.4s .2s cubic-bezier(.65, 0, .35, 1) forwards; }
@keyframes bp-trace { to { stroke-dashoffset: 0; } }
@keyframes bp-fade { to { opacity: 1; } }

.bp-hero-grid { position: relative; display: grid; gap: 28px; align-items: center; }
.bp-kicker { font-family: var(--gb-display); color: #E4C77A; font-weight: 600; margin: 0 0 10px; }
.bp-hero h1 { color: #fff; font-size: clamp(2.3rem, 7.5vw, 4.4rem); font-weight: 800; font-stretch: 112%; line-height: 1.12; margin: 0 0 16px; max-width: 13ch; }
.bp-lead { font-size: 1.12rem; color: #DCE6CF; margin: 0 0 24px; }
.bp-cta-row { display: flex; flex-wrap: wrap; align-items: center; gap: 14px 20px; }
.bp-cta { min-height: 56px; padding: 14px 28px; font-size: 1.12rem; }
.bp-like-link { display: inline-flex; align-items: center; gap: 8px; color: #fff; font-weight: 600; text-decoration: underline; text-underline-offset: 5px; text-decoration-color: rgba(255, 255, 255, .4); }
.bp-like-link:hover { text-decoration-color: #fff; }
.bp-how { font-size: .9rem; color: #B9C9A9; margin: 14px 0 0; }

.bp-hero-book { display: flex; justify-content: center; }
.bp-stage { position: relative; width: var(--book-w); padding-bottom: calc(var(--book-w) * .22); --book-w: min(62vw, 300px); }
.bp-parcel { position: absolute; left: -22%; right: -22%; bottom: 0; width: 144%; height: auto; overflow: visible; }
.bp-stage img { position: relative; display: block; width: 100%; height: auto; border-radius: 3px; transform: rotate(-3deg); box-shadow: 14px 22px 40px rgba(0, 0, 0, .5), 0 0 0 1px rgba(255, 255, 255, .06); animation: bp-rise .7s .9s both cubic-bezier(.16, 1, .3, 1); }
.bp-dag { position: absolute; right: -18%; bottom: 2%; font-family: var(--gb-display); font-weight: 600; font-size: .95rem; color: #E4C77A; opacity: 0; animation: bp-fade .4s 1.5s forwards; }
@keyframes bp-rise { from { opacity: 0; transform: rotate(-3deg) translateY(18px); } }

@media (min-width: 900px) {
  .bp-hero { padding: 88px 0 104px; }
  .bp-hero-grid { grid-template-columns: 1.15fr .85fr; gap: 48px; }
  .bp-stage { --book-w: 320px; }
}
@media (max-width: 899px) {
  .bp-hero-book { order: -1; }
  .bp-stage { --book-w: min(44vw, 200px); }
  .bp-plots text { display: none; }
}

/* ---------- Who ---------- */
.bp-who-grid { display: grid; gap: 0; margin-top: 18px; }
.bp-who-item { padding: 20px 0; border-top: 1.5px solid var(--gb-silt); }
.bp-who-item h3 { font-size: 1.35rem; margin: 0 0 6px; }
.bp-who-item p { margin: 0; color: var(--gb-ink-soft); }
@media (min-width: 820px) {
  .bp-who-grid { grid-template-columns: repeat(3, 1fr); }
  .bp-who-item { border-top: 0; border-left: 1.5px solid var(--gb-silt); padding: 4px 28px; }
  .bp-who-item:first-child { border-left: 0; padding-left: 0; }
}

/* ---------- Ledger ---------- */
.bp-ledger-sec { background: var(--gb-sheet); border-block: 1px solid var(--gb-silt); }
.bp-ledger-wrap { display: grid; gap: 28px; }
.bp-ledger-intro .gb-btn { margin-top: 8px; }
@media (min-width: 900px) { .bp-ledger-wrap { grid-template-columns: .8fr 1.2fr; gap: 56px; align-items: start; } .bp-ledger-intro { position: sticky; top: 110px; } }
.bp-ledger { width: 100%; border-collapse: collapse; background: #fff; border: 1.5px solid var(--gb-paddy); }
.bp-ledger thead th { text-align: left; font-family: var(--gb-display); font-weight: 600; color: var(--gb-paddy); padding: 10px 14px; border-bottom: 4px double var(--gb-paddy); background: #EEF3E6; }
.bp-ledger td { padding: 11px 14px; border-bottom: 1px solid var(--gb-silt); vertical-align: top; }
.bp-ledger td:first-child { width: 4.5em; font-family: var(--gb-display); font-weight: 700; color: var(--gb-leaf); border-right: 1px solid var(--gb-silt); text-align: center; }
.bp-ledger tbody tr:last-child td { border-bottom: 0; }
.bp-ledger-extra td { background: #FBF6E9; }
.bp-ledger-extra td:first-child { font-size: .85rem; color: #8A6A1C; }
.sr-only { position: absolute; width: 1px; height: 1px; overflow: hidden; clip: rect(0 0 0 0); white-space: nowrap; }

/* ---------- Quote ---------- */
.bp-quote-sec { background: var(--gb-paddy); color: #fff; padding: clamp(56px, 8vw, 96px) 0; }
.bp-quote-wrap { display: grid; gap: 28px; }
.bp-quote { margin: 0; font-family: var(--gb-display); font-size: clamp(2rem, 5vw, 3.4rem); font-weight: 800; font-stretch: 110%; line-height: 1.15; color: #fff; max-width: 14ch; }
.bp-pillars p { color: #DCE6CF; margin: 0 0 10px; }
.bp-pillars ol { margin: 0 0 14px; padding-left: 1.4em; display: grid; gap: 6px; }
.bp-pillars li { padding-left: 4px; }
.bp-pillars li::marker { color: #E4C77A; font-weight: 700; }
.bp-pillars .bp-pillars-more { font-size: .95rem; color: var(--gb-shoot); margin: 0; }
@media (min-width: 900px) { .bp-quote-wrap { grid-template-columns: 1fr 1fr; gap: 64px; align-items: center; } }

/* ---------- Final ---------- */
.bp-final-wrap { display: grid; gap: 28px; align-items: start; }
.bp-final-cover { width: 140px; height: auto; border-radius: 3px; box-shadow: 10px 14px 30px rgba(19, 53, 32, .28); }
.bp-final .gb-lead { margin-bottom: 20px; }
.bp-final-social { margin-top: 32px; max-width: 460px; }
.bp-final-social p { font-weight: 600; color: var(--gb-paddy); margin: 0 0 10px; }
@media (min-width: 760px) { .bp-final-wrap { grid-template-columns: 200px 1fr; gap: 48px; } .bp-final-cover { width: 200px; } }

/* ---------- Sticky bar (phones) ---------- */
.bp-bar { position: fixed; left: 0; right: 0; bottom: 0; z-index: 60; padding: 10px 16px calc(10px + env(safe-area-inset-bottom)); background: rgba(251, 252, 247, .96); border-top: 1px solid var(--gb-silt); backdrop-filter: blur(6px); }
.bp-bar .gb-btn { width: 100%; }
@media (min-width: 900px) { .bp-bar { display: none; } }
.bp-bar-enter-active, .bp-bar-leave-active { transition: transform .25s ease; }
.bp-bar-enter-from, .bp-bar-leave-to { transform: translateY(100%); }
@media (max-width: 899px) { .bp { padding-bottom: 76px; } }

@media (prefers-reduced-motion: reduce) {
  .bp-gold { animation: none; stroke-dashoffset: 0; }
  .bp-dag { animation: none; opacity: 1; }
  .bp-stage img { animation: none; }
}
</style>

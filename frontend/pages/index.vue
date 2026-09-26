<template>
  <div class="home">
    <!-- Hero: headline + search, sun rising over the field bands -->
    <section class="hero" aria-labelledby="hero-title">
      <div class="gb-wrap hero-top">
        <div class="hero-copy">
          <h1 id="hero-title" class="hero-title"><template v-for="(line, i) in headlineLines" :key="i"><br v-if="i" />{{ line }}</template></h1>
          <p class="hero-lead">{{ settings.home_subtitle || defaultSubtitle }}</p>

          <form class="hero-search" role="search" @submit.prevent="runSearch">
            <label class="sr-only" for="hero-type">প্রপার্টির ধরন</label>
            <select id="hero-type" v-model="search.type" class="hero-select">
              <option value="">সব ধরন</option>
              <option v-for="band in bands" :key="band.key" :value="band.key">{{ band.name }}</option>
            </select>
            <label class="sr-only" for="hero-q">এলাকা বা প্রকল্পের নাম</label>
            <input id="hero-q" v-model="search.q" class="hero-input" type="search" placeholder="এলাকা বা প্রকল্প, যেমন পূর্বাচল" autocomplete="off" />
            <button type="submit" class="gb-btn gb-btn--sun hero-go">
              <Search :size="18" aria-hidden="true" />
              <span>খুঁজুন</span>
            </button>
          </form>
        </div>
      </div>

      <div class="fields">
        <div class="sun" aria-hidden="true">
          <span v-for="n in 9" :key="n" class="ray" :style="{ '--i': n }"></span>
          <span class="disc"></span>
        </div>
        <nav class="bands" aria-label="ধরন অনুযায়ী প্রপার্টি">
          <NuxtLink
            v-for="(band, i) in bands"
            :key="band.key"
            :to="`/properties?type=${encodeURIComponent(band.key)}`"
            class="band"
            :style="{ '--i': i }"
          >
            <span class="gb-wrap band-row">
              <span class="band-name">{{ band.name }}</span>
              <span class="band-note">{{ band.note }}</span>
              <span class="band-count">{{ countLabel(band) }}</span>
            </span>
          </NuxtLink>
        </nav>
      </div>
    </section>

    <!-- Current listings -->
    <section class="gb-section listings" aria-labelledby="listings-title">
      <div class="gb-wrap">
        <div class="sec-head">
          <div>
            <h2 id="listings-title" class="gb-h2">এখন তালিকায় আছে</h2>
            <p class="gb-lead">দাম, আয়তন আর লোকেশন দেখে পছন্দের প্রপার্টি খুলুন। প্রতিটি পেজে মূল্যের শর্ত ও কাগজপত্রের তথ্য দেওয়া আছে।</p>
          </div>
          <NuxtLink to="/properties" class="gb-btn gb-btn--line">সব প্রপার্টি দেখুন<template v-if="publicProps.length"> ({{ toBn(publicProps.length) }})</template></NuxtLink>
        </div>

        <div v-if="featured.length" class="list-grid">
          <PropertyCard :property="featured[0]" wide class="list-lead" />
          <PropertyCard v-for="p in featured.slice(1)" :key="p.id" :property="p" />
        </div>
        <div v-else-if="isPropertiesLoading" class="list-grid" aria-busy="true">
          <div v-for="n in 4" :key="n" class="skeleton" :class="{ 'list-lead': n === 1 }"></div>
        </div>
        <div v-else class="empty">
          <p>এই মুহূর্তে কোনো প্রপার্টি প্রকাশিত নেই। কী খুঁজছেন জানালে নতুন তালিকা এলে আমরা যোগাযোগ করব।</p>
          <NuxtLink to="/contact" class="gb-btn gb-btn--paddy">কী খুঁজছেন জানান</NuxtLink>
        </div>
      </div>
    </section>

    <!-- Document ledger: what to check before buying -->
    <section class="gb-section ledger-sec" aria-labelledby="ledger-title">
      <div class="gb-wrap ledger-layout">
        <div class="ledger-intro">
          <h2 id="ledger-title" class="gb-h2">কেনার আগে যে কাগজগুলো মিলিয়ে দেখবেন</h2>
          <p class="gb-lead">জমি বা ফ্ল্যাট কেনার সবচেয়ে বড় ঝুঁকি থাকে কাগজে। আমাদের তালিকার যেকোনো প্রপার্টির জন্য এই কাগজগুলো দেখতে চাইতে পারেন — আমরা দেখাতে ও বুঝিয়ে দিতে সাহায্য করব।</p>
          <NuxtLink to="/contact" class="gb-btn gb-btn--paddy">কাগজপত্র নিয়ে প্রশ্ন করুন</NuxtLink>
          <p class="ledger-note">এটি সাধারণ নির্দেশনা। চূড়ান্ত সিদ্ধান্তের আগে নিজের আইনজীবীর পরামর্শ নিন।</p>
        </div>

        <div class="ledger" role="table" aria-label="যাচাই করার কাগজপত্র">
          <div class="ledger-head" role="row">
            <span role="columnheader">কাগজ</span>
            <span role="columnheader">কী জানা যায়</span>
            <span role="columnheader">কোথায় মেলাবেন</span>
          </div>
          <div v-for="doc in documents" :key="doc.name" class="ledger-row" role="row">
            <span role="cell" class="ledger-doc">{{ doc.name }}</span>
            <span role="cell">{{ doc.proves }}</span>
            <span role="cell" class="ledger-where">{{ doc.where }}</span>
          </div>
        </div>
      </div>
    </section>

    <!-- How buying works: a true sequence -->
    <section class="gb-section steps-sec" aria-labelledby="steps-title">
      <div class="gb-wrap">
        <h2 id="steps-title" class="gb-h2">আমাদের মাধ্যমে কেনা যেভাবে এগোয়</h2>
        <ol class="steps">
          <li v-for="(step, i) in steps" :key="step.title">
            <span class="step-n" aria-hidden="true">{{ toBn(i + 1) }}</span>
            <h3>{{ step.title }}</h3>
            <p>{{ step.text }}</p>
          </li>
        </ol>
      </div>
    </section>

    <!-- Browse by area, built from live listings -->
    <section v-if="areas.length" class="gb-section areas-sec" aria-labelledby="areas-title">
      <div class="gb-wrap"><div class="areas-layout">
        <h2 id="areas-title" class="gb-h2">এলাকা অনুযায়ী খুঁজুন</h2>
        <ul class="areas">
          <li v-for="a in areas" :key="a.name">
            <NuxtLink :to="`/properties?area=${encodeURIComponent(a.name)}`">
              <span>{{ a.name }}</span>
              <small>{{ toBn(a.count) }}টি</small>
            </NuxtLink>
          </li>
        </ul>
      </div></div>
    </section>

    <!-- Buyers living abroad -->
    <section class="abroad" aria-labelledby="abroad-title">
      <div class="gb-wrap abroad-grid">
        <div>
          <h2 id="abroad-title" class="abroad-title">বিদেশে থেকে দেশে জমি কিনছেন?</h2>
          <p class="abroad-lead">দূরে থেকেও যেন সব নিজের চোখে দেখে সিদ্ধান্ত নিতে পারেন, সেভাবে কাজ করি।</p>
          <div class="abroad-cta">
            <a v-if="whatsapp" :href="`https://wa.me/${whatsapp}?text=${encodeURIComponent('আসসালামু আলাইকুম, আমি বিদেশ থেকে প্রপার্টি কেনার বিষয়ে জানতে চাই।')}`" target="_blank" rel="noopener noreferrer" class="gb-btn gb-btn--sun">
              <MessageCircle :size="18" aria-hidden="true" /> WhatsApp-এ কথা বলুন
            </a>
            <NuxtLink to="/contact" class="gb-btn gb-btn--ghost-light">ভিডিও কলের সময় ঠিক করুন</NuxtLink>
          </div>
        </div>
        <ul class="abroad-list">
          <li><Video :size="22" aria-hidden="true" /><span><strong>ভিডিও কলে সাইট দেখা</strong>জমি বা ফ্ল্যাটে দাঁড়িয়ে লাইভ ঘুরিয়ে দেখানো হয়।</span></li>
          <li><FileText :size="22" aria-hidden="true" /><span><strong>কাগজের কপি আগে হাতে</strong>খতিয়ান, দলিল, নকশার কপি পাঠিয়ে দিই, যাতে আপনার আইনজীবী দেখে নিতে পারেন।</span></li>
          <li><Clock :size="22" aria-hidden="true" /><span><strong>আপনার সময়ে কল</strong>যে দেশেই থাকুন, আপনার সুবিধামতো সময়ে কথা বলা যায়।</span></li>
          <li><Users :size="22" aria-hidden="true" /><span><strong>দেশে থাকা পরিবারের সঙ্গে সমন্বয়</strong>আপনার প্রতিনিধি সাইটে গেলে আমরা সঙ্গে থাকি।</span></li>
        </ul>
      </div>
    </section>

    <!-- Sellers -->
    <section class="gb-section sell" aria-labelledby="sell-title">
      <div class="gb-wrap"><div class="sell-box">
        <div>
          <h2 id="sell-title" class="gb-h2">জমি, প্লট বা ফ্ল্যাট বিক্রি করবেন?</h2>
          <p class="gb-lead">প্রপার্টির তথ্য আর ছবি দিন। আমাদের টিম কাগজপত্র দেখে যোগাযোগ করবে, তারপর তালিকায় তোলা হবে।</p>
        </div>
        <div class="sell-actions">
          <NuxtLink to="/list-property" class="gb-btn gb-btn--sun">প্রপার্টির তথ্য দিন</NuxtLink>
          <a v-if="phone" :href="`tel:${phoneHref}`" class="gb-link">অথবা কল করুন {{ phone }}</a>
        </div>
      </div></div>
    </section>
  </div>
</template>

<script setup lang="ts">
import { computed, onMounted, reactive } from 'vue'
import { Clock, FileText, MessageCircle, Search, Users, Video } from 'lucide-vue-next'
import { useProperties } from '~/composables/useProperties'
import { useSettings } from '~/composables/useSettings'
import PropertyCard from '~/components/PropertyCard.vue'
import { toBn } from '~/utils/propertyLabels'

definePageMeta({ layout: 'default' })

const router = useRouter()
const { properties, fetchProperties, isLoading: isPropertiesLoading } = useProperties()
const { settings, fetchSettings } = useSettings()

onMounted(() => { fetchProperties(); fetchSettings() })

const bands = [
  { key: 'Plot,Land', name: 'জমি ও প্লট', note: 'কাঠা, শতক ও বিঘায়', types: ['Plot', 'Land'] },
  { key: 'Land Share', name: 'জমি শেয়ার', note: 'অংশীদারিতে জমির মালিকানা', types: ['Land Share'] },
  { key: 'Flat', name: 'ফ্ল্যাট', note: 'রেডি ও নির্মাণাধীন', types: ['Flat'] },
  { key: 'Duplex,Penthouse', name: 'ডুপ্লেক্স ও পেন্টহাউস', note: 'পরিবারের জন্য বড় পরিসর', types: ['Duplex', 'Penthouse'] },
  { key: 'Hotel,Commercial', name: 'রিসোর্ট ও বাণিজ্যিক', note: 'ব্যবসা ও বিনিয়োগের জন্য', types: ['Hotel', 'Commercial'] }
]

const publicProps = computed(() => properties.value.filter(p => p.status !== 'Draft' && p.status !== 'Delisted'))
const countLabel = (band: typeof bands[number]) => {
  const n = publicProps.value.filter(p => band.types.includes(p.propertyType)).length
  return n ? `${toBn(n)}টি তালিকা` : 'দেখুন'
}

const featured = computed(() => {
  const list = publicProps.value.filter(p => p.status !== 'Sold')
  const picked = list.filter(p => p.isFeatured)
  const pool = (picked.length >= 4 ? picked : [...picked, ...list.filter(p => !p.isFeatured)]).slice(0, 7)
  // One wide lead card, then full rows of three
  const rest = pool.length - 1
  return rest >= 3 ? pool.slice(0, 1 + Math.floor(rest / 3) * 3) : pool
})

const areas = computed(() => {
  const counts = new Map<string, number>()
  publicProps.value.forEach(p => { if (p.areaName) counts.set(p.areaName, (counts.get(p.areaName) || 0) + 1) })
  return [...counts.entries()].map(([name, count]) => ({ name, count })).sort((a, b) => b.count - a.count).slice(0, 12)
})

const search = reactive({ type: '', q: '' })
const runSearch = () => {
  const query: Record<string, string> = {}
  if (search.type) query.type = search.type
  if (search.q.trim()) query.q = search.q.trim()
  router.push({ path: '/properties', query })
}

const defaultSubtitle = 'প্লট, জমি শেয়ার আর ফ্ল্যাটের তালিকা — প্রতিটির দাম, আয়তন, লোকেশন ও কাগজপত্রের তথ্য এক জায়গায়। পছন্দ হলে আমাদের টিমের সঙ্গে সরাসরি কথা বলুন।'
// The headline breaks after each comma, the way it is set on the page.
const headlineLines = computed(() => (settings.value.home_headline || 'জমি দেখে, কাগজ বুঝে, তারপর কিনুন।').split(/(?<=,)\s*/).filter(Boolean))
const phone = computed(() => settings.value.contact_phone || '')
const phoneHref = computed(() => phone.value.replace(/[^\d+]/g, ''))
const whatsapp = computed(() => (settings.value.whatsapp_number || '').replace(/\D/g, ''))

const documents = [
  { name: 'খতিয়ান (সিএস, এসএ, আরএস, বিএস)', proves: 'জমির মালিকানা কোন জরিপে কার নামে ছিল — মালিকানার ধারাবাহিকতা।', where: 'জেলা রেকর্ড রুম, ভূমি অফিস বা অনলাইনে ই-পর্চা' },
  { name: 'দলিল ও বায়া দলিল', proves: 'বর্তমান বিক্রেতা কীভাবে জমির মালিক হয়েছেন, আগের হস্তান্তরগুলো কী ছিল।', where: 'সংশ্লিষ্ট সাব-রেজিস্ট্রি অফিস' },
  { name: 'নামজারি খতিয়ান ও ডিসিআর', proves: 'সরকারি রেকর্ডে বর্তমান মালিকের নাম হালনাগাদ আছে কি না।', where: 'উপজেলা/সার্কেল ভূমি অফিস (এসি ল্যান্ড)' },
  { name: 'খাজনার দাখিলা', proves: 'ভূমি উন্নয়ন কর পরিশোধিত ও হালনাগাদ কি না।', where: 'ইউনিয়ন বা পৌর ভূমি অফিস' },
  { name: 'মৌজা ম্যাপ ও দাগ নম্বর', proves: 'কাগজের জমি আর মাঠের জমি একই কি না, সীমানা ও রাস্তা কোথায়।', where: 'জেলা প্রশাসকের কার্যালয় / সরেজমিন মাপ' },
  { name: 'তল্লাশি (নির্দায় যাচাই)', proves: 'জমি ব্যাংকে বন্ধক, অন্য কারও কাছে বিক্রি বা মামলায় আটকে আছে কি না।', where: 'সাব-রেজিস্ট্রি অফিসে তল্লাশি' },
  { name: 'ফ্ল্যাটের নকশা অনুমোদন ও ডেভেলপার চুক্তি', proves: 'ভবন অনুমোদিত নকশায় হচ্ছে কি না, ডেভেলপার বিক্রির অধিকার রাখে কি না।', where: 'রাজউক/সিডিএ ও জমির মালিক–ডেভেলপার চুক্তিপত্র' }
]

const steps = [
  { title: 'পছন্দ জানান', text: 'তালিকা থেকে প্রপার্টি বেছে নিন, অথবা বাজেট আর এলাকা জানালে আমরা মিলিয়ে দেখাই।' },
  { title: 'দাম ও কাগজ বুঝে নিন', text: 'মোট খরচ, পেমেন্টের শর্ত আর কাগজপত্রের কপি নিয়ে খোলামেলা আলোচনা।' },
  { title: 'সরেজমিনে দেখুন', text: 'নিজে বা প্রতিনিধি পাঠিয়ে সাইট ভিজিট। দূরে থাকলে ভিডিও কলে।' },
  { title: 'চুক্তি ও রেজিস্ট্রি', text: 'বায়নানামা থেকে দলিল রেজিস্ট্রি ও নামজারি পর্যন্ত প্রতিটি ধাপে পাশে থাকি।' }
]

useSeoMeta({
  title: 'গ্রাম বাংলা রিয়েল এস্টেট | জমি, প্লট ও ফ্ল্যাট — GBREL',
  ogTitle: 'জমি দেখে, কাগজ বুঝে, তারপর কিনুন — গ্রাম বাংলা রিয়েল এস্টেট',
  description: 'প্লট, জমি শেয়ার ও ফ্ল্যাটের তালিকা। দাম, আয়তন, লোকেশন ও কাগজপত্রের তথ্য দেখে গ্রাম বাংলা রিয়েল এস্টেট টিমের সঙ্গে কথা বলুন।'
})
</script>

<style scoped>
/* ---------- Hero ---------- */
.hero { position: relative; overflow: hidden; }
.hero-top { padding-top: clamp(40px, 7vw, 88px); padding-bottom: clamp(36px, 5vw, 56px); position: relative; z-index: 2; }
.hero-copy { max-width: 760px; }
.hero-title {
  font-size: var(--gb-t-hero);
  font-weight: 800;
  font-stretch: 116%;
  line-height: 1.08;
  color: var(--gb-paddy);
  margin-bottom: 24px;
}
.hero-lead { font-size: var(--gb-t-lead); color: var(--gb-ink-soft); max-width: 58ch; margin-bottom: 30px; }

.hero-search { display: flex; background: var(--gb-sheet); border: 1.5px solid var(--gb-silt); border-radius: 999px; padding: 6px; max-width: 640px; box-shadow: 0 14px 30px -22px rgba(29, 74, 42, .5); }
.hero-select, .hero-input { background: transparent; color: var(--gb-ink); font-family: var(--gb-body); font-size: 1rem; min-height: 48px; padding: 0 16px; }
.hero-select { border-right: 1px solid var(--gb-silt); max-width: 190px; cursor: pointer; appearance: none; padding-right: 30px; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%231D4A2A' stroke-width='3'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 12px center; }
.hero-input { flex: 1; min-width: 0; }
.hero-input::placeholder { color: #7D8B80; }
.hero-go { flex-shrink: 0; }

/* Field bands: the categories, drawn as terraced paddy rows */
.fields { position: relative; }
.sun { position: absolute; right: max(24px, calc((100vw - var(--gb-wrap)) / 2 + 60px)); bottom: calc(100% - 84px); width: 270px; height: 270px; z-index: 0; animation: sunrise 1.4s cubic-bezier(.2,.8,.2,1) both; }
.sun .disc { position: absolute; inset: 56px; border-radius: 50%; background: var(--gb-sun); }
.sun .ray { position: absolute; left: 50%; top: 50%; width: 12px; height: 44px; margin: -22px 0 0 -6px; border-radius: 7px; background: var(--gb-sun); transform: rotate(calc((var(--i) - 5) * 20deg)) translateY(-118px); }

.bands { position: relative; z-index: 1; display: flex; flex-direction: column; }
.band {
  --h: 30px;
  position: relative;
  display: block;
  color: var(--ink);
  background: var(--c);
  text-decoration: none;
  animation: bandrise .9s cubic-bezier(.2,.8,.2,1) both;
  animation-delay: calc(.25s + var(--i) * .08s);
}
.band::before {
  content: '';
  position: absolute; left: 0; right: 0; top: 0; height: var(--h);
  background: var(--c);
  -webkit-mask: var(--wave) top / 100% 100% no-repeat;
          mask: var(--wave) top / 100% 100% no-repeat;
  transform: translateY(calc(-100% + 1px));
}
.band:nth-child(1) { --c: #B9D08F; --ink: var(--gb-paddy); --wave: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 1440 30' preserveAspectRatio='none'%3E%3Cpath d='M0 30 C 300 0 700 4 1000 18 S 1350 26 1440 10 V30Z'/%3E%3C/svg%3E"); }
.band:nth-child(2) { --c: #8FB366; --ink: var(--gb-paddy-deep); --wave: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 1440 30' preserveAspectRatio='none'%3E%3Cpath d='M0 12 C 260 30 560 26 860 10 S 1260 2 1440 22 V30 H0Z'/%3E%3C/svg%3E"); }
.band:nth-child(3) { --c: #5C924A; --ink: #fff; --wave: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 1440 30' preserveAspectRatio='none'%3E%3Cpath d='M0 24 C 380 2 760 0 1080 16 S 1380 28 1440 20 V30 H0Z'/%3E%3C/svg%3E"); }
.band:nth-child(4) { --c: #3A7234; --ink: #fff; --wave: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 1440 30' preserveAspectRatio='none'%3E%3Cpath d='M0 8 C 320 26 640 30 960 14 S 1320 0 1440 8 V30 H0Z'/%3E%3C/svg%3E"); }
.band:nth-child(5) { --c: var(--gb-paddy); --ink: #fff; --wave: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 1440 30' preserveAspectRatio='none'%3E%3Cpath d='M0 20 C 420 0 820 6 1120 20 S 1400 22 1440 12 V30 H0Z'/%3E%3C/svg%3E"); }

.band-row { display: grid; grid-template-columns: minmax(0, auto) 1fr auto; align-items: baseline; gap: 20px; padding-top: 10px; padding-bottom: calc(var(--h) + 12px); }
.band-name { font-family: var(--gb-display); font-size: clamp(1.45rem, 2.6vw, 2.2rem); font-weight: 700; font-stretch: 112%; line-height: 1.15; }
.band-note { font-size: .95rem; opacity: .85; }
.band-count { font-family: var(--gb-display); font-size: 1.05rem; font-weight: 600; display: inline-flex; align-items: center; gap: 10px; }
.band-count::after { content: ''; width: 28px; height: 2px; background: currentColor; transition: width .25s ease; }
.band:hover .band-count::after, .band:focus-visible .band-count::after { width: 48px; }
.band:hover .band-name { text-decoration: underline; text-decoration-thickness: 2px; text-underline-offset: 6px; }
.band:focus-visible { outline: 3px solid var(--gb-sun); outline-offset: -6px; }

@keyframes sunrise { from { transform: translateY(80px); opacity: 0; } to { transform: none; opacity: 1; } }
@keyframes bandrise { from { transform: translateY(24px); opacity: 0; } to { transform: none; opacity: 1; } }

/* ---------- Section heads ---------- */
.sec-head { display: flex; justify-content: space-between; align-items: flex-end; gap: 24px; flex-wrap: wrap; margin-bottom: 36px; }

/* ---------- Listings ---------- */
.listings { background: var(--gb-paper); }
.list-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 24px; }
.list-lead { grid-column: 1 / -1; }
.skeleton { min-height: 360px; border-radius: var(--gb-r-lg); background: linear-gradient(90deg, #E6EBDD, #EFF2E8, #E6EBDD); background-size: 200% 100%; animation: shimmer 1.4s linear infinite; }
@keyframes shimmer { to { background-position: -200% 0; } }
.empty { background: var(--gb-sheet); border: 1.5px dashed var(--gb-silt); border-radius: var(--gb-r-lg); padding: 40px; display: flex; flex-direction: column; align-items: flex-start; gap: 18px; }

/* ---------- Ledger ---------- */
.ledger-sec { background: var(--gb-sheet); border-top: 1px solid var(--gb-silt); border-bottom: 1px solid var(--gb-silt); }
.ledger-layout { display: grid; grid-template-columns: minmax(0, 5fr) minmax(0, 8fr); gap: clamp(32px, 5vw, 72px); align-items: start; }
.ledger-intro { position: sticky; top: 110px; display: flex; flex-direction: column; align-items: flex-start; gap: 18px; }
.ledger-intro .gb-h2 { margin-bottom: 0; }
.ledger-note { font-size: .85rem; color: var(--gb-ink-soft); border-left: 3px solid var(--gb-shoot); padding-left: 12px; }
.ledger { border: 1.5px solid var(--gb-paddy); border-radius: var(--gb-r-sm); overflow: hidden; background: #fff; }
.ledger-head, .ledger-row { display: grid; grid-template-columns: 1.1fr 1.5fr 1fr; }
.ledger-head span { background: var(--gb-paddy); color: #fff; font-family: var(--gb-display); font-weight: 600; padding: 12px 16px; }
.ledger-row span { padding: 16px; font-size: .95rem; line-height: 1.65; border-top: 1px solid var(--gb-silt); }
.ledger-row span + span, .ledger-head span + span { border-left: 1px solid var(--gb-silt); }
.ledger-head span + span { border-left-color: rgba(255,255,255,.18); }
.ledger-row:nth-child(odd) { background: #F7F9F1; }
.ledger-doc { font-family: var(--gb-display); font-weight: 600; font-size: 1.05rem !important; color: var(--gb-paddy); }
.ledger-where { color: var(--gb-ink-soft); font-size: .9rem !important; }

/* ---------- Steps ---------- */
.steps { list-style: none; display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 0; margin-top: 36px; counter-reset: s; }
.steps li { position: relative; padding: 0 28px 0 0; }
.steps li::before { content: ''; position: absolute; left: 64px; right: 16px; top: 30px; border-top: 2px dashed var(--gb-shoot); }
.steps li:last-child::before { display: none; }
.step-n { display: grid; place-items: center; width: 60px; height: 60px; border-radius: 50%; border: 2px solid var(--gb-paddy); font-family: var(--gb-display); font-size: 1.7rem; font-weight: 700; color: var(--gb-paddy); background: var(--gb-paper); margin-bottom: 18px; }
.steps li:last-child .step-n { background: var(--gb-paddy); color: #fff; }
.steps h3 { font-size: var(--gb-t-h3); font-weight: 700; margin-bottom: 6px; }
.steps p { font-size: .97rem; color: var(--gb-ink-soft); }

/* ---------- Areas ---------- */
.areas-sec { padding-top: 0; }
.areas-layout { border-top: 1px solid var(--gb-silt); padding-top: clamp(48px, 6vw, 80px); }
.areas { list-style: none; display: flex; flex-wrap: wrap; gap: 10px; margin-top: 20px; }
.areas a { display: inline-flex; align-items: baseline; gap: 10px; padding: 10px 18px; border-radius: 999px; background: var(--gb-sheet); border: 1.5px solid var(--gb-silt); font-family: var(--gb-display); font-size: 1.08rem; font-weight: 600; color: var(--gb-paddy); }
.areas a:hover { border-color: var(--gb-leaf); background: rgba(168, 197, 123, .22); }
.areas small { font-family: var(--gb-body); font-size: .8rem; font-weight: 500; color: var(--gb-ink-soft); }

/* ---------- Abroad ---------- */
.abroad { background: var(--gb-paddy-deep); color: #E3EBDA; padding: clamp(56px, 8vw, 96px) 0; }
.abroad-grid { display: grid; grid-template-columns: 1fr 1fr; gap: clamp(32px, 6vw, 88px); align-items: center; }
.abroad-title { font-size: var(--gb-t-h1); font-weight: 800; font-stretch: 112%; color: #fff; margin-bottom: 16px; }
.abroad-lead { font-size: var(--gb-t-lead); color: #C6D5BB; margin-bottom: 28px; }
.abroad-cta { display: flex; flex-wrap: wrap; gap: 12px; }
.abroad-list { list-style: none; display: flex; flex-direction: column; }
.abroad-list li { display: flex; gap: 16px; padding: 18px 0; border-top: 1px solid rgba(255,255,255,.14); }
.abroad-list li:last-child { border-bottom: 1px solid rgba(255,255,255,.14); }
.abroad-list svg { color: var(--gb-shoot); flex-shrink: 0; margin-top: 4px; }
.abroad-list strong { display: block; font-family: var(--gb-display); font-size: 1.15rem; font-weight: 600; color: #fff; }
.abroad-list span { font-size: .95rem; }

/* ---------- Sell ---------- */
.sell-box { display: grid; grid-template-columns: 1.4fr 1fr; gap: 32px; align-items: center; padding: clamp(28px, 4vw, 48px); border-radius: var(--gb-r-lg); background: #F8E6D8; border: 1.5px solid #EFC9AC; }
.sell-box .gb-h2 { color: var(--gb-ink); }
.sell-actions { display: flex; flex-direction: column; align-items: flex-start; gap: 14px; justify-self: end; }

/* ---------- Responsive ---------- */
@media (max-width: 1024px) {
  .list-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
  .ledger-layout { grid-template-columns: 1fr; }
  .ledger-intro { position: static; }
  .steps { grid-template-columns: repeat(2, minmax(0, 1fr)); row-gap: 36px; }
  .steps li:nth-child(2)::before { display: none; }
  .abroad-grid, .sell-box { grid-template-columns: 1fr; }
  .sell-actions { justify-self: start; }
}
@media (max-width: 720px) {
  .sun { width: 120px; height: 120px; right: 16px; bottom: calc(100% - 70px); }
  .sun .disc { inset: 24px; }
  .sun .ray { height: 22px; margin-top: -11px; width: 7px; transform: rotate(calc((var(--i) - 5) * 20deg)) translateY(-54px); }
  .hero-top { padding-bottom: 72px; }
  .hero-search { flex-wrap: wrap; border-radius: var(--gb-r); gap: 4px; }
  .hero-select { max-width: none; width: 100%; border-right: 0; border-bottom: 1px solid var(--gb-silt); }
  .hero-input { width: 100%; flex-basis: 100%; }
  .hero-go { width: 100%; border-radius: var(--gb-r-sm); }
  .band-row { grid-template-columns: 1fr auto; gap: 4px 12px; }
  .band-note { grid-column: 1; grid-row: 2; font-size: .85rem; }
  .band-count { grid-row: 1 / 3; grid-column: 2; align-self: center; font-size: .95rem; }
  .band-count::after { width: 16px; }
  .list-grid { grid-template-columns: 1fr; }
  .ledger { border-radius: var(--gb-r); }
  .ledger-head { display: none; }
  .ledger-row { grid-template-columns: 1fr; padding: 16px; }
  .ledger-row span { padding: 0; border: 0 !important; }
  .ledger-row .ledger-doc { margin-bottom: 4px; }
  .ledger-row .ledger-where { margin-top: 8px; }
  .ledger-row .ledger-where::before { content: 'কোথায়: '; font-weight: 600; }
  .steps { grid-template-columns: 1fr; }
  .steps li::before { display: none; }
}
</style>

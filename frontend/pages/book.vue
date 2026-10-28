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
          <p class="bp-kicker">জমি · ফ্ল্যাট · ল্যান্ড শেয়ার — {{ toBn(BOOK.chapters) }} অধ্যায়ের বাংলা ই-বুক</p>
          <h1>একটা ভুল কাগজে লাখ টাকা যায়</h1>
          <p class="bp-lead">জমি বা ফ্ল্যাটে টাকা দেওয়ার আগে জেনে নিন কোন কাগজ দেখবেন, কোন মুখী আর কোন তলা নেবেন, কোথায় মানুষ সবচেয়ে বেশি ঠকে, আর এই বাজার থেকে নিজে কীভাবে আয় করবেন।</p>

          <div class="bp-price" aria-label="বইয়ের দাম">
            <span class="bp-price-old">৳{{ bnMoney(BOOK.listPrice) }}</span>
            <span class="bp-price-now">৳{{ bnMoney(BOOK.price) }}</span>
            <span class="bp-price-tag">{{ toBn(BOOK_DISCOUNT_PERCENT) }}% ছাড় চলছে</span>
          </div>

          <div class="bp-cta-row">
            <button type="button" class="gb-btn gb-btn--sun bp-cta" @click="openModal('hero')">
              <ShoppingBag :size="20" aria-hidden="true" /> ৳{{ bnMoney(BOOK.price) }}-এ বইটি কিনুন
            </button>
          </div>
          <p class="bp-how">বিকাশ · নগদ · রকেট। পেমেন্টের সঙ্গে সঙ্গে PDF খুলে যাবে, ফোনেই পড়া যায়।</p>
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

    <!-- What ignorance costs: sourced numbers -->
    <section class="gb-section bp-risk" aria-labelledby="bp-risk-title">
      <div class="gb-wrap">
        <h2 id="bp-risk-title" class="gb-h2">না জানার দাম কত, দেখুন</h2>
        <div class="bp-stats">
          <div v-for="s in riskStats" :key="s.figure" class="bp-stat">
            <p class="bp-stat-fig">{{ s.figure }}</p>
            <p class="bp-stat-text">{{ s.text }}</p>
            <p class="bp-stat-src">সূত্র: {{ s.source }}</p>
          </div>
        </div>
      </div>
    </section>

    <!-- 500 taka, put in perspective -->
    <section class="gb-section bp-math" aria-labelledby="bp-math-title">
      <div class="gb-wrap bp-math-wrap">
        <div>
          <h2 id="bp-math-title" class="gb-h2">৫০০ টাকা আসলে কত টাকা?</h2>
          <p class="gb-lead">এক বেলা রেস্তোরাঁয় খেলে ৫০০–১,০০০ টাকা চলে যায়। চা-পানেও মাসে অনায়াসে এর বেশি যায়। অথচ জমি-ফ্ল্যাটের একটা সিদ্ধান্তে জড়িয়ে থাকে লাখ লাখ টাকা।</p>
        </div>
        <ul class="bp-scale">
          <li><span>এক বেলা রেস্তোরাঁ</span><strong>৳৫০০–১,০০০</strong></li>
          <li><span>২০ লাখ টাকার জমির রেজিস্ট্রি খরচ (একটি উদাহরণ)<small>সূত্র: প্রথম আলো</small></span><strong>৳১,৩০,৭১০</strong></li>
          <li class="bp-scale-bad"><span>ভুল কাগজের জমি বা ফ্ল্যাট</span><strong>সারা জীবনের সঞ্চয়</strong></li>
          <li class="bp-scale-good"><span>এই বই, যা সেই ভুল চেনায়</span><strong>৳{{ bnMoney(BOOK.price) }}</strong></li>
        </ul>
      </div>
    </section>

    <!-- Curiosity: things most buyers never get told -->
    <section class="gb-section bp-secrets" aria-labelledby="bp-secrets-title">
      <div class="gb-wrap">
        <h2 id="bp-secrets-title" class="gb-h2">যে কথাগুলো দালাল আপনাকে বলবে না</h2>
        <p class="gb-lead">বইয়ের ভেতরে, উৎসসহ:</p>
        <ul class="bp-secret-list">
          <li v-for="s in secrets" :key="s.q"><strong>{{ s.q }}</strong><span>{{ s.where }}</span></li>
        </ul>
        <button type="button" class="gb-btn gb-btn--paddy" @click="openModal('secrets')">
          <ShoppingBag :size="19" aria-hidden="true" /> উত্তরগুলো জেনে নিন — ৳{{ bnMoney(BOOK.price) }}
        </button>
      </div>
    </section>

    <!-- Who it is for, with the money each one saves or makes -->
    <section class="gb-section bp-who" aria-labelledby="bp-who-title">
      <div class="gb-wrap">
        <h2 id="bp-who-title" class="gb-h2">আপনি কে, সেই অনুযায়ী লাভ</h2>
        <div class="bp-who-grid">
          <div v-for="item in audiences" :key="item.who" class="bp-who-item">
            <h3>{{ item.who }}</h3>
            <p>{{ item.gets }}</p>
            <p class="bp-who-win">{{ item.win }}</p>
          </div>
        </div>
        <div class="bp-earn">
          <p class="bp-earn-title">বই কিনেই ব্যবসা শুরু: একটা হিসাব</p>
          <p>পুঁজি ছাড়াই বিশ্বস্ত এজেন্ট হিসেবে শুরু করা যায়; কমিশন এলাকাভেদে সাধারণত দামের ১–২% (অধ্যায় ৮)। ৩০ লাখ টাকার <em>একটা</em> জমির লেনদেনে ১% মানে <strong>৳৩০,০০০</strong> — বইয়ের দামের ৬০ গুণ। সঙ্গে কাগজ যাচাই, নামজারি সহায়তা, প্রবাসী গ্রাহক সেবা, জয়েন্ট ভেঞ্চার মধ্যস্থতাসহ আয়ের ৮টা পথ, আর ৯০ দিনের ধাপে ধাপে পরিকল্পনা।</p>
          <p class="bp-earn-note">এটা উদাহরণ, নিশ্চয়তা নয়। আয় নির্ভর করে আপনার পরিশ্রম, এলাকা আর সততার ওপর।</p>
        </div>
      </div>
    </section>

    <!-- The contents, as a khatian ledger -->
    <section class="gb-section bp-ledger-sec" aria-labelledby="bp-ledger-title">
      <div class="gb-wrap bp-ledger-wrap">
        <div class="bp-ledger-intro">
          <h2 id="bp-ledger-title" class="gb-h2">বইয়ের খতিয়ান</h2>
          <p class="gb-lead">{{ toBn(BOOK.chapters) }}টি অধ্যায়, প্রিন্ট করে রাখার চেকলিস্ট, জমির মাপের হিসাব, শব্দকোষ আর দরকারি সরকারি ওয়েবসাইটের তালিকা।</p>
          <button type="button" class="gb-btn gb-btn--paddy" @click="openModal('contents')">
            <ShoppingBag :size="19" aria-hidden="true" /> পুরো বইটি নিন
          </button>
        </div>
        <table class="bp-ledger">
          <caption class="sr-only">বইয়ের অধ্যায়সমূহ</caption>
          <thead><tr><th scope="col">অধ্যায়</th><th scope="col">বিষয়</th></tr></thead>
          <tbody>
            <tr v-for="(c, i) in chapters" :key="c" :class="{ 'bp-ledger-new': c === NEW_CHAPTER }">
              <td>{{ toBn(i + 1) }}</td>
              <td>{{ c }}<span v-if="c === NEW_CHAPTER" class="bp-new">নতুন</span></td>
            </tr>
            <tr class="bp-ledger-extra"><td>পরিশিষ্ট</td><td>চেকলিস্ট, জমির মাপ, শব্দকোষ ও দরকারি ওয়েবসাইট</td></tr>
          </tbody>
        </table>
      </div>
    </section>

    <!-- One idea from the book, as a free taste -->
    <section class="bp-quote-sec" aria-labelledby="bp-quote">
      <div class="gb-wrap bp-quote-wrap">
        <div>
          <blockquote id="bp-quote" class="bp-quote">দলিল হাতে থাকলেই জমি আপনার না।</blockquote>
          <p class="bp-quote-src">"খতিয়ান হচ্ছে দখলের প্রামাণ্য দলিল, মালিকানার নয়।" — ভূমি মন্ত্রণালয়ের সিনিয়র সচিব (রাইজিংবিডি)</p>
        </div>
        <div class="bp-pillars">
          <p>বই থেকে একটা অংশ, বিনামূল্যে: মালিকানা দাঁড়িয়ে থাকে পাঁচটা খুঁটির ওপর। একটা না মিললেই থামুন।</p>
          <ol>
            <li>দলিল ও বায়া দলিলের অবিচ্ছিন্ন চেইন</li>
            <li>খতিয়ানে বিক্রেতার নাম</li>
            <li>নামজারি ও ডিসিআর</li>
            <li>হালনাগাদ ভূমি উন্নয়ন কর</li>
            <li>জমিতে বাস্তব দখল</li>
          </ol>
          <p class="bp-pillars-more">কোনটা কোথায়, কীভাবে যাচাই করবেন, আর বাকি ১৪টা ধাপ: বইয়ের অধ্যায় ২ ও ৩-এ।</p>
        </div>
      </div>
    </section>

    <!-- Why trust it: where the facts come from -->
    <section class="gb-section bp-sources" aria-labelledby="bp-sources-title">
      <div class="gb-wrap">
        <h2 id="bp-sources-title" class="gb-h2">মনগড়া কথা নয়, সব তথ্যের উৎস দেওয়া</h2>
        <p class="gb-lead">আইন, ফি আর নিয়ম মিলিয়ে নেওয়া হয়েছে সরকারি ওয়েবসাইট ও দেশের প্রধান সংবাদমাধ্যম থেকে। গুরুত্বপূর্ণ প্রতিটা সংখ্যা আর আইনের পাশে উৎস লেখা আছে।</p>
        <ul class="bp-source-list">
          <li v-for="s in sources" :key="s">{{ s }}</li>
        </ul>
      </div>
    </section>

    <!-- Questions people ask before paying -->
    <section class="gb-section bp-faq" aria-labelledby="bp-faq-title">
      <div class="gb-wrap bp-faq-wrap">
        <h2 id="bp-faq-title" class="gb-h2">কেনার আগে যা জানতে চান</h2>
        <details v-for="f in faqs" :key="f.q">
          <summary>{{ f.q }}</summary>
          <p>{{ f.a }}</p>
        </details>
      </div>
    </section>

    <!-- Final call -->
    <section class="gb-section bp-final" aria-labelledby="bp-final-title">
      <div class="gb-wrap bp-final-wrap">
        <img :src="BOOK.cover" alt="" width="180" height="255" loading="lazy" class="bp-final-cover" />
        <div>
          <h2 id="bp-final-title" class="gb-h2">টাকা নড়ানোর আগে বইটা পড়ে নিন</h2>
          <p class="gb-lead">এক বেলা বাইরে খাওয়ার দামে সারা জীবনের সঞ্চয়ের পাহারাদার। লেখক {{ BOOK.author }}, গ্রামবাংলা রিয়েল এস্টেট লিমিটেড (Grambangla Real Estate Limited)।</p>
          <div class="bp-price bp-price--light">
            <span class="bp-price-old">৳{{ bnMoney(BOOK.listPrice) }}</span>
            <span class="bp-price-now">৳{{ bnMoney(BOOK.price) }}</span>
            <span class="bp-price-tag">{{ toBn(BOOK_DISCOUNT_PERCENT) }}% ছাড়</span>
          </div>
          <button type="button" class="gb-btn gb-btn--sun bp-cta" @click="openModal('final')">
            <ShoppingBag :size="20" aria-hidden="true" /> এখনই কিনুন, এখনই পড়ুন
          </button>
          <p class="bp-final-pay">বিকাশ · নগদ · রকেট &nbsp;|&nbsp; পেমেন্টের পর সঙ্গে সঙ্গে ডাউনলোড</p>
        </div>
      </div>
    </section>

    <!-- Phones: the buy button stays in reach once the hero has scrolled away -->
    <transition name="bp-bar">
      <div v-if="showBar && !modalOpen" class="bp-bar">
        <span class="bp-bar-price"><s>৳{{ bnMoney(BOOK.listPrice) }}</s> <strong>৳{{ bnMoney(BOOK.price) }}</strong></span>
        <button type="button" class="gb-btn gb-btn--sun" @click="openModal('sticky_bar')">
          <ShoppingBag :size="19" aria-hidden="true" /> বইটি কিনুন
        </button>
      </div>
    </transition>

    <BookDownloadModal :open="modalOpen" :source="modalSource" @close="modalOpen = false" />
  </div>
</template>

<script setup lang="ts">
import { onBeforeUnmount, onMounted, ref } from 'vue'
import { ShoppingBag } from 'lucide-vue-next'
import BookDownloadModal from '~/components/BookDownloadModal.vue'
import { BOOK, BOOK_DISCOUNT_PERCENT, bnMoney } from '~/utils/book'
import { toBn } from '~/utils/propertyLabels'
import { trackPixel } from '~/utils/metaPixel'

// Share previews for crawlers come from server/plugins/share-meta.ts; this sets the tab title in the browser.
useHead({
  title: 'The Hidden Business of Land: জমি-ফ্ল্যাট কেনা-বেচার বাংলা গাইড | Grambangla Real Estate',
  meta: [
    { name: 'description', content: 'জমি, ফ্ল্যাট ও ল্যান্ড শেয়ার নিরাপদে কেনা, ন্যায্য দামে বেচা, কোন মুখী ও কোন তলা নেবেন, আর এই ব্যবসায় আয়ের পথ। Grambangla Real Estate Limited (GBREL) প্রকাশিত বাংলা ই-বুক।' },
    { name: 'keywords', content: 'Grambangla Real Estate, Grambangla Real Estate Limited, Gram Bangla Real Estate, জমি কেনা বেচার বই, The Hidden Business of Land, GBREL' }
  ],
  link: [{ rel: 'canonical', href: 'https://www.gbrel.com/book' }]
})

const riskStats = [
  { figure: '৯,০০০+', text: 'শুধু ঢাকার আদালতে ঝুলে থাকা প্লট-সংক্রান্ত মামলা', source: 'দ্য বিজনেস স্ট্যান্ডার্ড' },
  { figure: '৳১৫,০০০ কোটি', text: 'সেই মামলাগুলোতে আটকে থাকা আনুমানিক টাকা', source: 'দ্য বিজনেস স্ট্যান্ডার্ড' },
  { figure: '৫৪টি', text: 'অনুমোদন ছাড়া চলা আবাসন প্রকল্প, যা রাজউক বন্ধ করেছে; অনেকে কিস্তিতে টাকা দিয়েছিলেন', source: 'রাজউক, দ্য বিজনেস স্ট্যান্ডার্ড' },
  { figure: '৭ বছর', text: 'জাল দলিল বা অন্যের জমি নিজের বলে বিক্রির সর্বোচ্চ সাজা। জানলে ঢাল, না জানলে ফাঁদ', source: 'ভূমি অপরাধ প্রতিরোধ ও প্রতিকার আইন, ২০২৩' }
]

const NEW_CHAPTER = 'জিতে যাওয়ার বাছাই: কোন মুখী, কোন তলা, কোন জমি'

const secrets = [
  { q: 'দক্ষিণমুখী, পূর্বমুখী না উত্তরমুখী: কোন মুখী ফ্ল্যাট বা প্লটে আলো-বাতাস বেশি, আর কেন দামেও তফাত হয়?', where: 'অধ্যায় ৭' },
  { q: 'কোন তলার ফ্ল্যাট নিলে পরে আফসোস করবেন না, আর কোন তলাটা আইনত ঝুঁকির?', where: 'অধ্যায় ৬–৭' },
  { q: 'জমির সামনের রাস্তা কত ফুট, তার ওপরেই কেন ঠিক হয় সেখানে কত তলা বাড়ি উঠবে?', where: 'অধ্যায় ৭' },
  { q: 'খতিয়ানে নাম আছে, দলিলও আছে — তবু জমিটা আপনার না-ও হতে পারে। কেন?', where: 'অধ্যায় ২' },
  { q: 'একই জমি দুজনের কাছে বিক্রির ফাঁদ কোন এক তল্লাশিতে ধরা পড়ে?', where: 'অধ্যায় ৩' },
  { q: 'ডেভেলপার পুরো প্রকল্প ব্যাংকে বন্ধক রাখলে আপনার রেজিস্ট্রি কীভাবে আটকে যায়?', where: 'অধ্যায় ৬' },
  { q: 'খরচ বাঁচাতে দলিলে কম দাম লিখলে পরে কেন বেশি কর গুনতে হয়?', where: 'অধ্যায় ৪' },
  { q: 'ফ্ল্যাট দেরিতে বুঝিয়ে দিলে বা বরাদ্দ বাতিল হলে আইন আপনাকে কত দিনে টাকা ফেরত দেওয়ায়?', where: 'অধ্যায় ৬' },
  { q: 'একজন ভাই-বোনের সই বাদ পড়লে কেনা জমিতে বছর পরেও কেন মামলা হতে পারে?', where: 'অধ্যায় ৩' }
]

const audiences = [
  { who: 'ক্রেতা', gets: 'টাকা দেওয়ার আগে ১৫ ধাপের যাচাই, কোন মুখী-কোন তলা-কোন জমি বাছবেন, রেজিস্ট্রি আর নামজারির পুরো খরচ।', win: 'লাভ: একটা ভুল এড়ানো মানেই লাখ টাকা বাঁচানো।' },
  { who: 'বিক্রেতা', gets: 'কাগজের ফাইল গোছানো, সরকারি দর-সাম্প্রতিক বিক্রি মিলিয়ে ঠিক দাম, সঠিক ক্রেতার কাছে পৌঁছানো।', win: 'লাভ: কম দামে না বেচে দ্রুত, ন্যায্য দামে বিক্রি।' },
  { who: 'উদ্যোক্তা', gets: 'পুঁজি থাকুক বা না থাকুক, জমি-ফ্ল্যাট-ল্যান্ড শেয়ার ঘিরে আয়ের ৮টা পথ আর ৯০ দিনের রোডম্যাপ।', win: 'লাভ: এই বই দিয়েই নিজের জমির ব্যবসা শুরু।' }
]

const chapters = [
  'পায়ের নিচের সোনার হরিণ: জমির ব্যবসা আসলে কী',
  'জমির কাগজের ভাষা: খতিয়ান, দলিল, নামজারি',
  'ক্রেতার ১৫ ধাপের যাচাই',
  'রেজিস্ট্রি, খরচ আর নামজারি',
  'বিক্রেতার গাইড',
  'ফ্ল্যাট কেনা: ডেভেলপার যাচাই ও আইনি অধিকার',
  NEW_CHAPTER,
  'ল্যান্ড শেয়ার: সুযোগ, ঝুঁকি আর নিরাপদ থাকার নিয়ম',
  'জমির ব্যবসায় আয়ের ৮টি পথ',
  '১২টি ফাঁদ, যেখানে মানুষ সবচেয়ে বেশি ঠকে',
  'প্রবাসীদের জন্য: দূরে থেকেও নিরাপদ বিনিয়োগ',
  '৯০ দিনের রোডম্যাপ'
]

const sources = [
  'ভূমি মন্ত্রণালয় · land.gov.bd · ldtax.gov.bd',
  'আইন মন্ত্রণালয়ের নিবন্ধন অধিদপ্তর · dolil.gov.bd',
  'রাজউক · বিশদ অঞ্চল পরিকল্পনা (ড্যাপ)',
  'রিয়েল এস্টেট উন্নয়ন ও ব্যবস্থাপনা আইন, ২০১০',
  'ভূমি অপরাধ প্রতিরোধ ও প্রতিকার আইন, ২০২৩',
  'প্রথম আলো · দ্য ডেইলি স্টার · দ্য বিজনেস স্ট্যান্ডার্ড · দ্য ফাইন্যান্সিয়াল এক্সপ্রেস'
]

const faqs = [
  { q: 'টাকা দেওয়ার পর বই কীভাবে পাব?', a: 'বিকাশ, নগদ বা রকেটে পেমেন্ট নিশ্চিত হলেই এই সাইটে বইয়ের পেজ খুলে যাবে। সেখান থেকে বইটা পড়তে বা ফোনে সেভ করতে পারবেন।' },
  { q: 'বইটা কি ছাপা বই?', a: `না, এটা PDF ই-বুক (${toBn(BOOK.chapters)} অধ্যায়)। মোবাইল, ট্যাব বা কম্পিউটারে পড়া যায়, চাইলে প্রিন্টও করা যায়।` },
  { q: 'লিংক হারিয়ে গেলে?', a: 'পেমেন্টের পর যে পেজে বই পান, সেটার লিংক রেখে দিন; যেকোনো সময় সেখান থেকে আবার নামানো যায়। হারালে ফেসবুক পেজে আপনার মোবাইল নম্বর দিয়ে মেসেজ দিন।' },
  { q: 'পেমেন্টে সমস্যা হলে?', a: 'টাকা কেটে গেছে কিন্তু বই খোলেনি? আমাদের ফেসবুক পেজে TrxID দিয়ে মেসেজ দিন, যাচাই করে বই পাঠিয়ে দেব।' }
]

const modalOpen = ref(false)
const modalSource = ref('hero')
const openModal = (source: string) => {
  modalSource.value = source
  modalOpen.value = true
}

const heroRef = ref<HTMLElement | null>(null)
const showBar = ref(false)
let observer: IntersectionObserver | undefined

onMounted(() => {
  // The ad's landing view. Lets Meta optimise for people who actually reach the page, not just click.
  setTimeout(() => trackPixel('ViewContent', { content_ids: [BOOK.contentId], content_name: BOOK.title, content_category: 'ebook', content_type: 'product', value: BOOK.price, currency: 'BDT' }), 0)
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
.bp-hero h1 { color: #fff; font-size: clamp(2.2rem, 7vw, 4.2rem); font-weight: 800; font-stretch: 112%; line-height: 1.12; margin: 0 0 16px; max-width: 14ch; }
.bp-lead { font-size: 1.12rem; color: #DCE6CF; margin: 0 0 22px; max-width: 46ch; }
.bp-cta-row { display: flex; flex-wrap: wrap; align-items: center; gap: 14px 20px; }
.bp-cta { min-height: 58px; padding: 14px 30px; font-size: 1.15rem; }
.bp-how { font-size: .92rem; color: #B9C9A9; margin: 14px 0 0; }

.bp-price { display: flex; align-items: baseline; flex-wrap: wrap; gap: 6px 14px; margin: 0 0 18px; }
.bp-price-old { font-size: 1.3rem; color: #B9C9A9; text-decoration: line-through; text-decoration-color: #E4785A; text-decoration-thickness: 2px; }
.bp-price-now { font-family: var(--gb-display); font-size: clamp(2.4rem, 7vw, 3.2rem); font-weight: 800; color: #F2D27A; line-height: 1; }
.bp-price-tag { font-size: .85rem; font-weight: 700; color: #3B1A07; background: #F2D27A; padding: 4px 10px; border-radius: 999px; }
.bp-price--light .bp-price-old { color: var(--gb-ink-soft); }
.bp-price--light .bp-price-now { color: var(--gb-paddy); }
.bp-price--light .bp-price-tag { background: #FCE9D9; color: #8A3B12; }

.bp-hero-book { display: flex; justify-content: center; }
.bp-stage { position: relative; width: var(--book-w); padding-bottom: calc(var(--book-w) * .22); --book-w: min(62vw, 300px); }
.bp-parcel { position: absolute; left: -22%; right: -22%; bottom: 0; width: 144%; height: auto; overflow: visible; }
.bp-stage img { position: relative; display: block; width: 100%; height: auto; border-radius: 3px; transform: rotate(-3deg); box-shadow: 14px 22px 40px rgba(0, 0, 0, .5), 0 0 0 1px rgba(255, 255, 255, .06); animation: bp-rise .7s .9s both cubic-bezier(.16, 1, .3, 1); }
.bp-dag { position: absolute; right: -18%; bottom: 2%; font-family: var(--gb-display); font-weight: 600; font-size: .95rem; color: #E4C77A; opacity: 0; animation: bp-fade .4s 1.5s forwards; }
@keyframes bp-rise { from { opacity: 0; transform: rotate(-3deg) translateY(18px); } }

@media (min-width: 900px) {
  .bp-hero { padding: 80px 0 96px; }
  .bp-hero-grid { grid-template-columns: 1.15fr .85fr; gap: 48px; }
  .bp-stage { --book-w: 320px; }
}
@media (max-width: 899px) {
  .bp-hero-book { order: -1; }
  .bp-stage { --book-w: min(40vw, 180px); }
  .bp-plots text { display: none; }
}

/* ---------- Risk numbers ---------- */
.bp-stats { display: grid; gap: 0; margin-top: 18px; }
.bp-stat { padding: 18px 0; border-top: 1.5px solid var(--gb-silt); }
.bp-stat-fig { font-family: var(--gb-display); font-size: clamp(1.9rem, 5vw, 2.5rem); font-weight: 800; color: #B42318; margin: 0 0 4px; line-height: 1.1; }
.bp-stat-text { margin: 0 0 6px; }
.bp-stat-src { margin: 0; font-size: .8rem; color: var(--gb-ink-soft); }
@media (min-width: 820px) {
  .bp-stats { grid-template-columns: repeat(4, 1fr); }
  .bp-stat { border-top: 0; border-left: 1.5px solid var(--gb-silt); padding: 4px 22px; }
  .bp-stat:first-child { border-left: 0; padding-left: 0; }
}

/* ---------- 500 taka in perspective ---------- */
.bp-math { background: var(--gb-sheet); border-block: 1px solid var(--gb-silt); }
.bp-math-wrap { display: grid; gap: 24px; align-items: center; }
@media (min-width: 900px) { .bp-math-wrap { grid-template-columns: 1fr 1fr; gap: 56px; } }
.bp-scale { list-style: none; margin: 0; padding: 0; background: #fff; border: 1.5px solid var(--gb-paddy); }
.bp-scale li { display: flex; justify-content: space-between; align-items: center; gap: 16px; padding: 13px 16px; border-bottom: 1px solid var(--gb-silt); }
.bp-scale li:last-child { border-bottom: 0; }
.bp-scale span { display: flex; flex-direction: column; }
.bp-scale small { font-size: .75rem; color: var(--gb-ink-soft); }
.bp-scale strong { font-family: var(--gb-display); white-space: nowrap; text-align: right; }
.bp-scale-bad strong { color: #B42318; }
.bp-scale-good { background: #EEF3E6; }
.bp-scale-good strong { color: var(--gb-paddy); font-size: 1.3rem; }

/* ---------- Secrets ---------- */
.bp-secret-list { list-style: none; margin: 8px 0 24px; padding: 0; display: grid; gap: 10px; }
@media (min-width: 900px) { .bp-secret-list { grid-template-columns: 1fr 1fr; gap: 12px 28px; } }
.bp-secret-list li { display: flex; justify-content: space-between; gap: 14px; padding: 12px 14px 12px 16px; background: #fff; border: 1px solid var(--gb-silt); border-left: 4px solid #C9A24A; border-radius: var(--gb-r-sm); }
.bp-secret-list strong { font-weight: 600; }
.bp-secret-list span { flex: none; font-size: .78rem; color: var(--gb-leaf); font-weight: 600; white-space: nowrap; }

/* ---------- Who ---------- */
.bp-who { background: var(--gb-sheet); border-block: 1px solid var(--gb-silt); }
.bp-who-grid { display: grid; gap: 0; margin-top: 18px; }
.bp-who-item { padding: 20px 0; border-top: 1.5px solid var(--gb-silt); }
.bp-who-item h3 { font-size: 1.35rem; margin: 0 0 6px; }
.bp-who-item p { margin: 0; color: var(--gb-ink-soft); }
.bp-who-item .bp-who-win { margin-top: 10px; color: var(--gb-paddy); font-weight: 600; }
@media (min-width: 820px) {
  .bp-who-grid { grid-template-columns: repeat(3, 1fr); }
  .bp-who-item { border-top: 0; border-left: 1.5px solid var(--gb-silt); padding: 4px 28px; }
  .bp-who-item:first-child { border-left: 0; padding-left: 0; }
}
.bp-earn { margin-top: 30px; padding: 20px 22px; background: #FBF6E9; border: 1px solid #E8D9A8; border-radius: var(--gb-r); }
.bp-earn-title { font-family: var(--gb-display); font-weight: 700; font-size: 1.2rem; color: #6B4E0F; margin: 0 0 8px; }
.bp-earn p { margin: 0 0 8px; }
.bp-earn strong { color: var(--gb-paddy); }
.bp-earn .bp-earn-note { font-size: .82rem; color: var(--gb-ink-soft); margin: 0; }

/* ---------- Ledger ---------- */
.bp-ledger-wrap { display: grid; gap: 28px; }
.bp-ledger-intro .gb-btn { margin-top: 8px; }
@media (min-width: 900px) { .bp-ledger-wrap { grid-template-columns: .8fr 1.2fr; gap: 56px; align-items: start; } .bp-ledger-intro { position: sticky; top: 110px; } }
.bp-ledger { width: 100%; border-collapse: collapse; background: #fff; border: 1.5px solid var(--gb-paddy); }
.bp-ledger thead th { text-align: left; font-family: var(--gb-display); font-weight: 600; color: var(--gb-paddy); padding: 10px 14px; border-bottom: 4px double var(--gb-paddy); background: #EEF3E6; }
.bp-ledger td { padding: 11px 14px; border-bottom: 1px solid var(--gb-silt); vertical-align: top; }
.bp-ledger td:first-child { width: 4.5em; font-family: var(--gb-display); font-weight: 700; color: var(--gb-leaf); border-right: 1px solid var(--gb-silt); text-align: center; }
.bp-ledger tbody tr:last-child td { border-bottom: 0; }
.bp-ledger-new td { background: #FFFBEF; }
.bp-new { margin-left: 8px; font-size: .72rem; font-weight: 700; color: #8A3B12; background: #FCE9D9; padding: 1px 8px; border-radius: 999px; vertical-align: 2px; }
.bp-ledger-extra td { background: #FBF6E9; }
.bp-ledger-extra td:first-child { font-size: .85rem; color: #8A6A1C; }
.sr-only { position: absolute; width: 1px; height: 1px; overflow: hidden; clip: rect(0 0 0 0); white-space: nowrap; }

/* ---------- Quote ---------- */
.bp-quote-sec { background: var(--gb-paddy); color: #fff; padding: clamp(56px, 8vw, 96px) 0; }
.bp-quote-wrap { display: grid; gap: 28px; }
.bp-quote { margin: 0 0 12px; font-family: var(--gb-display); font-size: clamp(2rem, 5vw, 3.4rem); font-weight: 800; font-stretch: 110%; line-height: 1.15; color: #fff; max-width: 14ch; }
.bp-quote-src { color: var(--gb-shoot); font-size: .92rem; margin: 0; max-width: 40ch; }
.bp-pillars p { color: #DCE6CF; margin: 0 0 10px; }
.bp-pillars ol { margin: 0 0 14px; padding-left: 1.4em; display: grid; gap: 6px; }
.bp-pillars li { padding-left: 4px; }
.bp-pillars li::marker { color: #E4C77A; font-weight: 700; }
.bp-pillars .bp-pillars-more { font-size: .95rem; color: var(--gb-shoot); margin: 0; }
@media (min-width: 900px) { .bp-quote-wrap { grid-template-columns: 1fr 1fr; gap: 64px; align-items: center; } }

/* ---------- Sources ---------- */
.bp-source-list { list-style: none; margin: 12px 0 0; padding: 0; display: flex; flex-wrap: wrap; gap: 8px; }
.bp-source-list li { padding: 6px 12px; border: 1px solid var(--gb-silt); border-radius: 999px; background: #fff; font-size: .9rem; }

/* ---------- FAQ ---------- */
.bp-faq { background: var(--gb-sheet); border-block: 1px solid var(--gb-silt); }
.bp-faq-wrap { max-width: 760px; }
.bp-faq details { border-bottom: 1px solid var(--gb-silt); padding: 14px 0; }
.bp-faq summary { cursor: pointer; font-weight: 600; font-size: 1.05rem; }
.bp-faq details p { margin: 10px 0 0; color: var(--gb-ink-soft); }

/* ---------- Final ---------- */
.bp-final-wrap { display: grid; gap: 28px; align-items: start; }
.bp-final-cover { width: 140px; height: auto; border-radius: 3px; box-shadow: 10px 14px 30px rgba(19, 53, 32, .28); }
.bp-final .gb-lead { margin-bottom: 16px; }
.bp-final-pay { font-size: .9rem; color: var(--gb-ink-soft); margin: 12px 0 0; }
@media (min-width: 760px) { .bp-final-wrap { grid-template-columns: 200px 1fr; gap: 48px; } .bp-final-cover { width: 200px; } }

/* ---------- Sticky bar (phones) ---------- */
.bp-bar { position: fixed; left: 0; right: 0; bottom: 0; z-index: 60; display: flex; align-items: center; gap: 12px; padding: 10px 16px calc(10px + env(safe-area-inset-bottom)); background: rgba(251, 252, 247, .97); border-top: 1px solid var(--gb-silt); backdrop-filter: blur(6px); }
.bp-bar-price { display: flex; flex-direction: column; line-height: 1.15; flex: none; }
.bp-bar-price s { font-size: .8rem; color: var(--gb-ink-soft); }
.bp-bar-price strong { font-family: var(--gb-display); font-size: 1.3rem; color: var(--gb-paddy); }
.bp-bar .gb-btn { flex: 1; }
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

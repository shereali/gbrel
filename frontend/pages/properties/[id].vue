<template>
  <div class="property-page" lang="bn">
    <div v-if="loading" class="property-state" role="status"><div class="loading-block"></div><h1>প্রপার্টির তথ্য আসছে…</h1></div>
    <div v-else-if="!property" class="property-state"><h1>প্রপার্টির তথ্য পাওয়া যায়নি</h1><p>লিংকটি পরীক্ষা করুন অথবা আবার চেষ্টা করুন।</p><button class="primary" @click="loadProperty">আবার চেষ্টা করুন</button><NuxtLink to="/properties">সব প্রপার্টি দেখুন →</NuxtLink></div>
    <div v-else class="property-shell">
      <div class="property-topline">
        <nav aria-label="Breadcrumb"><NuxtLink to="/properties">প্রপার্টি</NuxtLink><span aria-hidden="true"> / </span><span>GBR-{{ property.id }}</span></nav>
        <div class="property-tools">
          <button @click="shareProperty"><Share2 :size="16" /> শেয়ার</button>
          <button :aria-pressed="isPropertySaved(property.id)" @click="toggleSaveProperty(property.id)"><Heart :size="16" :fill="isPropertySaved(property.id) ? 'currentColor' : 'none'" /> {{ isPropertySaved(property.id) ? 'সেভ করা' : 'সেভ' }}</button>
          <button :aria-pressed="isInCompare(property.id)" @click="toggleCompare(property.id)"><Copy :size="16" /> তুলনা</button>
        </div>
      </div>
      <header class="property-heading">
        <div><div class="property-badges"><span>{{ typeLabel(property.propertyType) }}</span><span>{{ listingLabels[property.listingType] || property.listingType }}</span><span v-if="property.completionStatus">{{ completionLabels[property.completionStatus] || property.completionStatus }}</span></div><h1>{{ property.title }}</h1><p class="property-location"><MapPin :size="17" /> {{ location }}</p></div>
        <div class="asking-price"><span>{{ price.hidden ? 'তালিকাভুক্ত মূল্য' : price.per }}</span><strong>{{ price.amount }}</strong><small v-if="price.note">{{ price.note }}</small>
          <ul v-if="trustFacts.length" class="trust-facts" aria-label="বিক্রেতার দেওয়া তথ্য"><li v-for="fact in trustFacts" :key="fact"><Check :size="15" aria-hidden="true" />{{ fact }}</li></ul>
          <div ref="heroCta" class="hero-cta"><button class="cta-sun" @click="openInquiry('hero_button')">{{ ctaLabel }}</button><a v-if="waLink" class="cta-wa" :href="waLink" target="_blank" rel="noopener noreferrer" @click="track('Contact', { method: 'WhatsApp', placement: 'hero' })"><MessageCircle :size="18" aria-hidden="true" /> WhatsApp-এ জিজ্ঞেস করুন</a></div>
          <small v-if="settings.property_cta_note" class="cta-note">{{ settings.property_cta_note }}</small>
        </div>
      </header>
      <section class="property-gallery" aria-label="প্রপার্টির ছবি" :class="{ single: slides.length < 2 }">
        <div class="gallery-tools"><button :aria-label="'শেয়ার করুন'" @click="shareProperty"><Share2 :size="18" /></button><button :aria-pressed="isPropertySaved(property.id)" aria-label="সেভ করুন" @click="toggleSaveProperty(property.id)"><Heart :size="18" :fill="isPropertySaved(property.id) ? 'currentColor' : 'none'" /></button></div>
        <template v-if="slides.length">
          <template v-if="slides[0].kind === 'video' && video">
            <div v-if="heroPlaying" class="gallery-primary gallery-player">
              <video v-if="video.kind === 'file'" :src="video.src" :poster="videoPoster || undefined" autoplay controls playsinline></video>
              <iframe v-else :src="video.src" title="প্রপার্টির ভিডিও" allow="autoplay; encrypted-media; picture-in-picture; fullscreen" allowfullscreen></iframe>
            </div>
            <button v-else class="gallery-primary gallery-video" aria-label="প্রপার্টির ভিডিও চালু করুন" @click="playHeroVideo">
              <img v-if="videoPoster" :src="videoPoster" :alt="property.title" fetchpriority="high" decoding="async" @error="imageFailed" />
              <video v-else-if="video.kind === 'file'" :src="video.src + '#t=0.5'" preload="metadata" muted playsinline aria-hidden="true"></video>
              <span class="play-disc" aria-hidden="true"><Play :size="30" fill="currentColor" /></span>
              <span class="gallery-caption"><Play :size="16" /> ভিডিও দেখুন</span>
            </button>
          </template>
          <button v-else-if="slides[0].kind === 'image'" class="gallery-primary" aria-label="বড় করে ছবি দেখুন" @click="openPhoto(0)"><img :src="slides[0].src" :alt="property.title" fetchpriority="high" decoding="async" @error="imageFailed" /><span class="gallery-caption"><Expand :size="16" /> {{ images.length }}টি ছবি দেখুন</span></button>
          <template v-if="slides[1]">
            <button v-if="slides[1].kind === 'video'" class="gallery-secondary gallery-video" aria-label="প্রপার্টির ভিডিও দেখুন" @click="openPhoto(1)"><img v-if="videoPoster" :src="videoPoster" alt="" loading="lazy" @error="imageFailed" /><span class="play-disc" aria-hidden="true"><Play :size="26" fill="currentColor" /></span></button>
            <button v-else class="gallery-secondary" aria-label="দ্বিতীয় ছবি বড় করে দেখুন" @click="openPhoto(1)"><img :src="slides[1].src" :alt="`${property.title} — ছবি ২`" loading="lazy" @error="imageFailed" /></button>
          </template>
          <div v-if="!heroPlaying && (videoIndex > 0 || (videoIndex === 0 && images.length))" class="gallery-chips">
            <button v-if="videoIndex > 0" type="button" class="gallery-chip gallery-chip--video" @click="openPhoto(videoIndex)"><Play :size="15" fill="currentColor" /> ভিডিও দেখুন</button>
            <button v-else type="button" class="gallery-chip" @click="openPhoto(1)"><Expand :size="15" /> {{ images.length }}টি ছবি</button>
          </div>
        </template>
        <PropertyPlotSheet v-else :property="property" />
      </section>
      <div class="property-at-a-glance">
        <div><Ruler :size="20" /><span>আয়তন<strong>{{ areaLabel(property.squareFootage, property.landSize, property.landUnit) || 'জেনে নিন' }}</strong></span></div>
        <div><Building2 :size="20" /><span>ধরন<strong>{{ typeLabel(property.propertyType) }}</strong></span></div>
        <div><Compass :size="20" /><span>{{ property.bedrooms ? 'বেডরুম' : 'অভিমুখ' }}<strong>{{ property.bedrooms ? toBn(property.bedrooms) : (facingLabel(property.facing) || 'জেনে নিন') }}</strong></span></div>
        <div><CircleCheck :size="20" /><span>অবস্থা<strong>{{ statusLabel(property.status) }}</strong></span></div>
      </div>
      <div ref="inlineStart"><PropertySurveyStart :property-type="property.propertyType" @choose="purpose => openInquiry('inline_first_question', purpose)" /></div>
      <div class="property-body">
        <div class="property-information">
          <nav class="section-nav" aria-label="প্রপার্টির বিভাগ"><a href="#property-overview">বিস্তারিত</a><a v-if="hasBuyerDetails" href="#property-buyer-details">মূল্য ও শর্ত</a><a href="#property-documents">কাগজপত্র</a><a href="#property-location">লোকেশন</a><a href="#property-questions">আপনার প্রশ্ন</a></nav>
          <section id="property-overview" class="detail-section"><p class="eyebrow">এক নজরে প্রপার্টি</p><h2>এখানে কী পাচ্ছেন?</h2><p v-if="property.tagline" class="property-tagline">{{ property.tagline }}</p><p class="description">{{ property.description }}</p><dl class="property-specs"><div v-for="item in specifications" :key="item.label"><dt>{{ item.label }}</dt><dd>{{ item.value }}</dd></div></dl><template v-if="property.amenities?.length"><h3>সুবিধাসমূহ</h3><ul class="amenity-list"><li v-for="amenity in property.amenities" :key="amenity"><Check :size="16" />{{ amenity }}</li></ul></template></section>
          <PropertyBuyerDetails :property="property" @inquire="openInquiry('sale_terms')" />
          <section id="property-documents" class="detail-section">
            <p class="eyebrow">সিদ্ধান্তের আগে পরিষ্কার ধারণা</p><h2>কাগজপত্র ও খরচ বুঝে নিন</h2><p>মালিকানা, অনুমোদন, রেজিস্ট্রেশন ও প্রযোজ্য খরচ নিয়ে প্রশ্ন করুন। তালিকায় থাকা তথ্যের সঙ্গে মূল নথি মিলিয়ে দেখুন।</p>
            <ul v-if="property.documentsVerified?.length" class="document-list"><li v-for="document in property.documentsVerified" :key="document"><FileText :size="18" /><span>{{ document }} <small>তালিকায় উল্লেখিত</small></span></li></ul>
            <div v-if="property.propertyType === 'Land Share'" class="land-share-note"><strong>জমির শেয়ার কিনছেন?</strong><p>এই মূল্যে জমির কতটুকু অংশ পাবেন, নির্মাণ খরচ আলাদা কি না, এবং হস্তান্তরের শর্ত—সবগুলো আলাদা করে নিশ্চিত করুন।</p></div>
            <div v-if="brochures.length" class="brochure-list"><a v-for="doc in brochures" :key="doc.url" :href="doc.url" target="_blank" rel="noopener noreferrer"><FileDown :size="20" /><span>{{ doc.title }}</span><ArrowUpRight :size="18" /></a></div>
            <p v-else class="muted-note">এই পেজে ব্রোশিওর প্রকাশ করা হয়নি। উপলব্ধ কাগজপত্র সম্পর্কে টিমকে জিজ্ঞেস করতে পারেন।</p><button class="text-action" @click="openInquiry('documents')">কাগজপত্র ও খরচ নিয়ে কথা বলতে চাই <ArrowRight :size="16" /></button>
          </section>
          <section id="property-location" class="detail-section"><p class="eyebrow">নিজে দেখে সিদ্ধান্ত নিন</p><h2>লোকেশন ও সাইট ভিজিট</h2><p class="property-location"><MapPin :size="20" />{{ location }}</p><p>সাইট ভিজিটের আগে সঠিক লোকেশন, যাতায়াতের পথ ও সময় টিমের সঙ্গে মিলিয়ে নিন।</p><a v-if="!property.hideExactAddress && property.lat && property.lng" class="text-action" :href="`https://www.google.com/maps/search/?api=1&query=${property.lat},${property.lng}`" target="_blank" rel="noopener noreferrer">ম্যাপে লোকেশন দেখুন <ArrowUpRight :size="16" /></a><button class="secondary" @click="openInquiry('site_visit')">সাইট ভিজিট নিয়ে কথা বলি <ArrowRight :size="16" /></button></section>
          <section id="property-questions" class="detail-section faq-section"><p class="eyebrow">সহজ উত্তর</p><h2>আপনার মনে হতে পারে</h2><details><summary>ফর্ম পূরণ করলে কি বুকিং হয়ে যাবে?</summary><p>না। এটি শুধু এই প্রপার্টি সম্পর্কে তথ্য ও যোগাযোগের অনুরোধ। কোনো টাকা বা বুকিংয়ের অঙ্গীকার প্রয়োজন নেই।</p></details><details><summary>তালিকাভুক্ত দামের বাইরে খরচ আছে?</summary><p>রেজিস্ট্রেশন, কর, সার্ভিস চার্জ এবং প্রযোজ্য হলে নির্মাণ খরচ মূল্যের মধ্যে আছে কি না, টিমের কাছে পূর্ণ হিসাব চেয়ে নিন। প্রকাশিত খরচের বিবরণ দেখুন; কোনো খরচ উল্লেখ না থাকলে তা অন্তর্ভুক্ত ধরে নেবেন না।</p></details><details><summary>এখনই কিনব না, তবু কথা বলা যাবে?</summary><p>অবশ্যই। ফর্মে আপনার আসল সময়সীমা বেছে নিন। আপনার প্রস্তুতি অনুযায়ী আলোচনা করা যাবে।</p></details><details><summary>ফর্ম জমা দেওয়ার পর কী হবে?</summary><p>GBREL টিম আপনার দেওয়া নম্বরে, পছন্দের মাধ্যমে যোগাযোগ করবে। ঐচ্ছিকভাবে সময় বা আগে জানতে চাওয়া বিষয়ও জানাতে পারবেন।</p></details></section>
        </div>
        <aside class="inquiry-sidebar"><PropertySurveyStart v-show="!inlineVisible" side :property-type="property.propertyType" class="side-start" @choose="purpose => openInquiry('sidebar_first_question', purpose)" /><div v-if="agent && !property.hideAgentContact" class="advisor-direct"><span>প্রশ্ন ছাড়াই সরাসরি কথা বলতে চান?</span><div><a :href="whatsappUrl" target="_blank" rel="noopener noreferrer" @click="track('Contact', { method: 'WhatsApp' })">WhatsApp <ArrowUpRight :size="15" /></a><a :href="`tel:${agent.phone}`" @click="track('Contact', { method: 'Phone' })"><Phone :size="15" /> কল করুন</a></div></div></aside>
      </div>

      <!-- Bottom Closing CTA Section for Desktop & Wide Devices -->
      <section class="property-bottom-cta" aria-labelledby="bottom-cta-heading">
        <div class="bottom-cta-card">
          <div class="bottom-cta-content">
            <span class="bottom-cta-badge">সাইট ভিজিট ও কাগজপত্র</span>
            <h2 id="bottom-cta-heading">প্রপার্টিটি নিজের চোখে দেখতে চান বা কোনো প্রশ্ন আছে?</h2>
            <p class="bottom-cta-desc">
              দাম, কাগজপত্র আর সাইট ভিজিট নিয়ে আপনার সব প্রশ্নের উত্তর দেবে GBREL টিম। বিক্রির পুরো প্রক্রিয়া আমরাই পরিচালনা করি। কোনো অগ্রিম বা লুকানো ফি নেই।
            </p>
            <ul class="bottom-cta-highlights">
              <li><Check :size="16" aria-hidden="true" /> কেনার আগে মূল কাগজপত্র নিজে দেখে নেওয়ার সুযোগ</li>
              <li><Check :size="16" aria-hidden="true" /> GBREL প্রতিনিধির সাথে সাইট ভিজিট</li>
              <li><Check :size="16" aria-hidden="true" /> দাম ও শর্তের আলোচনা GBREL-এর সাথে, এক জায়গায়</li>
            </ul>
          </div>
          <div class="bottom-cta-actions">
            <div class="bottom-price-tag">
              <span class="price-caption">{{ price.hidden ? 'তালিকাভুক্ত মূল্য' : price.per }}</span>
              <strong class="price-val">{{ price.amount }}</strong>
              <small v-if="price.note">{{ price.note }}</small>
            </div>
            <button class="cta-sun bottom-cta-btn" @click="openInquiry('bottom_section_cta')">
              {{ ctaLabel }}
            </button>
            <a v-if="waLink" class="cta-wa bottom-wa-btn" :href="waLink" target="_blank" rel="noopener noreferrer" @click="track('Contact', { method: 'WhatsApp', placement: 'bottom_banner' })">
              <MessageCircle :size="19" aria-hidden="true" />
              WhatsApp-এ কথা বলুন
            </a>
            <a v-if="agent?.phone && !property.hideAgentContact" class="bottom-phone-pill" :href="`tel:${agent.phone}`" @click="track('Contact', { method: 'Phone', placement: 'bottom_banner' })">
              <Phone :size="15" aria-hidden="true" />
              সরাসরি কথা বলুন: <span>{{ agent.phone }}</span>
            </a>
          </div>
        </div>
      </section>

      <aside v-if="!inquiryOpen && !lightboxOpen" class="mobile-inquiry-bar" :class="{ shown: !heroCtaVisible }" aria-label="প্রপার্টি সম্পর্কে যোগাযোগ"><div><strong>{{ price.amount }}</strong><small>{{ price.hidden ? 'বিস্তারিত জেনে নিন' : price.per }}</small></div><a v-if="waLink" class="bar-wa" :href="waLink" target="_blank" rel="noopener noreferrer" aria-label="WhatsApp-এ জিজ্ঞেস করুন" @click="track('Contact', { method: 'WhatsApp', placement: 'sticky' })"><MessageCircle :size="22" /></a><button class="cta-sun" @click="openInquiry('mobile_sticky')">{{ ctaLabel }}</button></aside>
      <PropertyInquiry :key="property.id" :open="inquiryOpen" :property="property" :source="inquirySource" :start-purpose="startPurpose" :whatsapp="leadWhatsapp" @close="inquiryOpen = false" @saved="leadSaved" />
    </div>
    <Teleport to="body"><div v-if="lightboxOpen && currentSlide" ref="lightboxRoot" class="photo-overlay" role="dialog" aria-modal="true" aria-label="প্রপার্টির ছবি ও ভিডিও" @click.self="lightboxOpen = false" @keydown.left="previousPhoto" @keydown.right="nextPhoto"><button class="photo-close" aria-label="বন্ধ করুন" @click="lightboxOpen = false"><X :size="25" /></button><img v-if="currentSlide.kind === 'image'" :src="currentSlide.src" :alt="`${property?.title} — ছবি ${photoIndex + 1}`" /><div v-else-if="video" class="photo-video"><video v-if="video.kind === 'file'" :key="video.src" :src="video.src" :poster="videoPoster || undefined" autoplay controls playsinline></video><iframe v-else :key="video.src" :src="video.src" title="প্রপার্টির ভিডিও" allow="autoplay; encrypted-media; picture-in-picture; fullscreen" allowfullscreen></iframe></div><div class="photo-controls"><button :disabled="slides.length < 2" aria-label="আগেরটি" @click="previousPhoto"><ChevronLeft /></button><span aria-live="polite">{{ photoIndex + 1 }} / {{ slides.length }}</span><button :disabled="slides.length < 2" aria-label="পরেরটি" @click="nextPhoto"><ChevronRight /></button></div></div></Teleport>
  </div>
</template>

<script setup lang="ts">
import { computed, onBeforeUnmount, ref, watch } from 'vue'
import { ArrowRight, ArrowUpRight, Building2, Check, ChevronLeft, ChevronRight, CircleCheck, Compass, Copy, Expand, FileDown, FileText, Heart, Image as ImageIcon, MapPin, MessageCircle, MessagesSquare, Phone, Play, Ruler, Share2, X } from 'lucide-vue-next'
import { useProperties, type PropertyItem } from '~/composables/useProperties'
import { formatBDT, formatArea } from '~/composables/useCurrency'
import { useAuth } from '~/composables/useAuth'
import { useCompare } from '~/composables/useCompare'
import { useOverlayBehavior } from '~/composables/useOverlayBehavior'
import { detailGroups } from '~/utils/buyerDetails.mjs'
import { priceDisplay } from '~/utils/priceDisplay'
import { parseVideo } from '~/utils/videoEmbed'
import { safeBrochureUrl } from '~/utils/propertyInquiry.mjs'
import { trackPixel } from '~/utils/metaPixel'
import { useSettings } from '~/composables/useSettings'
import PropertySurveyStart from '~/components/PropertySurveyStart.vue'
import PropertyPlotSheet from '~/components/PropertyPlotSheet.vue'
import { areaLabel, completionLabels, facingLabel, listingLabels, statusLabel, toBn, typeLabel } from '~/utils/propertyLabels'
const route = useRoute()
const { fetchPropertyById, fetchAgents, agents } = useProperties()
const { isPropertySaved, toggleSaveProperty } = useAuth()
const { isInCompare, toggleCompare } = useCompare()
const toast = useToast()
const property = ref<PropertyItem | null>(null)
const loading = ref(true)
const inquiryOpen = ref(false)
const inquirySource = ref('hero')
const lightboxOpen = ref(false)
const lightboxRoot = ref<HTMLElement | null>(null)
const photoIndex = ref(0)
useOverlayBehavior(lightboxOpen, () => { lightboxOpen.value = false }, lightboxRoot)
const agent = computed(() => agents.value.find(item => item.id === property.value?.agentId))
const { settings, fetchSettings } = useSettings()
fetchSettings()
const leadWhatsapp = computed(() => (!property.value?.hideAgentContact && agent.value?.whatsapp) || settings.value.whatsapp_number || '')
const images = computed(() => [...new Set((property.value?.images || []).filter(Boolean))])
// Video: the listing chooses whether buyers land on the video or the main photo. Nothing autoplays; a tap plays it.
const video = computed(() => parseVideo(property.value?.videoUrl))
const videoPoster = computed(() => property.value?.videoPoster || video.value?.thumbnail || images.value[0] || '')
type Slide = { kind: 'image'; src: string } | { kind: 'video' }
const slides = computed<Slide[]>(() => {
  const photos: Slide[] = images.value.map(src => ({ kind: 'image', src }))
  if (!video.value) return photos
  const clip: Slide = { kind: 'video' }
  return property.value?.coverMedia === 'video' || !photos.length ? [clip, ...photos] : [photos[0], clip, ...photos.slice(1)]
})
const videoIndex = computed(() => slides.value.findIndex(slide => slide.kind === 'video'))
const currentSlide = computed(() => slides.value[photoIndex.value])
const heroPlaying = ref(false)
const playHeroVideo = () => { heroPlaying.value = true; track('ViewContent', { content_category: 'video' }) }
const price = computed(() => priceDisplay(property.value))
const hasBuyerDetails = computed(() => property.value && detailGroups(property.value).length > 0)
// Button text and the promise under it are edited in Admin → Settings → Property page.
const ctaLabel = computed(() => property.value?.hidePrice ? (settings.value.property_cta_label_hidden_price || 'সর্বশেষ দাম ও সাইট ভিজিট') : (settings.value.property_cta_label || 'ক্রয় তথ্য ও সাইট ভিজিট'))
const location = computed(() => { const p = property.value; return p ? (p.hideExactAddress ? [p.areaName, p.city].filter(Boolean).join(', ') : p.address || [p.areaName, p.city].filter(Boolean).join(', ')) : '' })
const brochures = computed(() => {
  const p = property.value
  if (p?.hideFloorPlan) return []
  const docs = (p?.brochuresVault || []).map(doc => ({ title: doc.title || 'প্রপার্টি ব্রোশিওর', url: safeBrochureUrl(doc.file_url) })).filter(doc => doc.url)
  const url = safeBrochureUrl(p?.brochureUrl)
  if (url && !docs.some(doc => doc.url === url)) docs.unshift({ title: 'প্রপার্টি ব্রোশিওর (PDF)', url })
  return docs
})
const specifications = computed(() => {
  const p = property.value
  if (!p) return []
  return [{ label: 'আয়তন', value: areaLabel(p.squareFootage, p.landSize, p.landUnit) }, { label: 'প্রপার্টির ধরন', value: typeLabel(p.propertyType) }, { label: 'নির্মাণের অবস্থা', value: completionLabels[p.completionStatus] || p.completionStatus }, { label: 'অভিমুখ', value: facingLabel(p.facing) }, { label: 'পার্কিং', value: p.parking ? `${toBn(p.parking)}টি` : '' }, { label: 'মোট তলা', value: p.totalFloors ? toBn(p.totalFloors) : '' }, { label: 'বেডরুম', value: p.bedrooms ? toBn(p.bedrooms) : '' }, { label: 'বাথরুম', value: p.bathrooms ? toBn(p.bathrooms) : '' }, { label: 'তালিকায় দেওয়া হস্তান্তর / নির্মাণ বছর', value: p.yearBuilt ? toBn(p.yearBuilt) : '' }].filter(item => item.value)
})
const whatsappUrl = computed(() => `https://wa.me/${(agent.value?.whatsapp || '').replace(/\D/g, '')}?text=${encodeURIComponent(`Hello GBREL, I would like details about ${property.value?.title} (GBR-${property.value?.id}).`)}`)
const track = (event: string, extra: Record<string, unknown> = {}) => trackPixel(event, { content_ids: [String(property.value?.id)], content_type: 'product', content_name: property.value?.title, ...extra })
const startPurpose = ref('')
// The sidebar repeats the first question only once the inline one has scrolled away, so both are never on screen together.
const inlineStart = ref<HTMLElement | null>(null)
const inlineVisible = ref(false)
let startObserver: IntersectionObserver | null = null
watch(inlineStart, el => {
  startObserver?.disconnect()
  if (!el || typeof IntersectionObserver === 'undefined') return
  startObserver = new IntersectionObserver(([entry]) => { inlineVisible.value = entry.isIntersecting || entry.boundingClientRect.top > 0 })
  startObserver.observe(el)
})
onBeforeUnmount(() => { startObserver?.disconnect(); ctaObserver?.disconnect() })
// The sticky bar appears only after the price-box button scrolls away, so there is one clear action on screen at a time.
const heroCta = ref<HTMLElement | null>(null)
const heroCtaVisible = ref(true)
let ctaObserver: IntersectionObserver | null = null
watch(heroCta, el => {
  ctaObserver?.disconnect()
  if (!el || typeof IntersectionObserver === 'undefined') return
  ctaObserver = new IntersectionObserver(([entry]) => { heroCtaVisible.value = entry.intersectionRatio > 0.95 }, { threshold: [0, 0.95, 1] })
  ctaObserver.observe(el)
})
// Up to three facts from the seller's details, shown next to the price. Only what was actually declared.
const trustFacts = computed(() => {
  const d: Record<string, any> = property.value?.buyerDetails || {}
  const facts = [
    d.bankLoan === 'None declared' && 'কোনো ব্যাংক ঋণ নেই',
    d.mutationStatus === 'Available' && 'নামজারি সম্পন্ন',
    d.possession === 'Owner' && 'মালিকের দখলে',
    d.existingAgreement === 'None declared' && 'অন্য কারও সঙ্গে বায়না নেই',
    d.taxPaidThrough && 'খাজনা পরিশোধিত',
    d.negotiable === 'Yes' && 'দাম আলোচনা সাপেক্ষ'
  ].filter(Boolean) as string[]
  return facts.slice(0, 3)
})
const waLink = computed(() => {
  const n = String(leadWhatsapp.value || '').replace(/\D/g, '')
  if (!n || /^8801711000000$/.test(n)) return ''
  return `https://wa.me/${n}?text=${encodeURIComponent(`আসসালামু আলাইকুম, "${property.value?.title}" (GBR-${property.value?.id}) নিয়ে জানতে চাই।`)}`
})
const openInquiry = (source: string, purpose = '') => { inquirySource.value = source; if (purpose) startPurpose.value = purpose; inquiryOpen.value = true }
// The survey sends the Lead pixel event itself (with a dedup event ID).
const leadSaved = (_id: number) => {}
const openPhoto = (index: number) => { photoIndex.value = index; lightboxOpen.value = true }
const nextPhoto = () => { photoIndex.value = (photoIndex.value + 1) % slides.value.length }
const previousPhoto = () => { photoIndex.value = (photoIndex.value + slides.value.length - 1) % slides.value.length }
const imageFailed = (event: Event) => { (event.target as HTMLImageElement).alt = 'ছবি লোড হয়নি — অন্য ছবি দেখুন'; }
let loadVersion = 0
const loadProperty = async () => {
  const version = ++loadVersion
  loading.value = true; property.value = null; inquiryOpen.value = false; lightboxOpen.value = false; heroPlaying.value = false
  const result = await fetchPropertyById(String(route.params.id), { strict: true })
  if (version !== loadVersion) return
  property.value = result; loading.value = false
  if (result) track('ViewContent')
  await fetchAgents()
}
watch(() => route.params.id, loadProperty, { immediate: true })
useSeoMeta({ title: () => property.value ? `${property.value.title} | GBREL` : 'Property | GBREL', description: () => property.value?.description?.slice(0, 160) || 'প্রপার্টির তথ্য, মূল্য ও যোগাযোগ।', ogTitle: () => property.value?.title || 'GBREL', ogImage: () => images.value[0] || videoPoster.value })
const shareProperty = async () => {
  const url = `${window.location.origin}${route.path}`
  try { if (navigator.share) { await navigator.share({ title: property.value?.title, url }); return }; await navigator.clipboard.writeText(url); toast.success('লিংক কপি হয়েছে', 'পছন্দের মাধ্যমে শেয়ার করুন।') }
  catch (error: any) { if (error?.name !== 'AbortError') toast.info('প্রপার্টির লিংক', url) }
}
</script>

<style scoped>
.property-page { background:#f8faf7; color:#173c34; font-family:'Noto Sans Bengali','Plus Jakarta Sans',sans-serif; padding:26px 0 70px; }
.property-shell { max-width:1216px; margin:auto; padding:0 28px; }
.property-page button,.property-page a { -webkit-tap-highlight-color:transparent; }
.property-page button { cursor:pointer; font-family:inherit; }
.property-page :focus-visible { outline:3px solid #b88322; outline-offset:4px; }
.property-topline { display:flex; align-items:center; justify-content:space-between; gap:16px; margin-bottom:24px; font-size:12px; color:#65736b; }
.property-topline nav a { color:#477465; }.property-tools { display:flex; gap:6px; }
.property-tools button { display:flex; align-items:center; justify-content:center; gap:7px; border:1px solid #dce4dc; background:#fff; border-radius:9px; padding:10px 12px; min-height:44px; color:#455e52; font-size:12px; }
.property-tools button[aria-pressed=true] { color:#126448; background:#e3f0e5; }
.property-heading { display:grid; grid-template-columns:minmax(0,1fr) 285px; gap:42px; align-items:center; margin-bottom:24px; }
.property-badges { display:flex; flex-wrap:wrap; gap:7px; margin-bottom:12px; }.property-badges span { background:#e8efe7; color:#416653; font-size:11px; padding:5px 10px; border-radius:5px; font-weight:600; }
.property-heading h1 { font-family:'Outfit',sans-serif; font-size:clamp(27px,3.2vw,42px); line-height:1.15; letter-spacing:-.8px; font-weight:600; margin-bottom:16px; overflow-wrap:anywhere; }
.property-location { display:flex; align-items:flex-start; gap:7px; font-size:13px; line-height:1.6; color:#647269; }.property-location svg { flex-shrink:0; margin-top:2px; }
.asking-price { border-left:1px solid #dce4dc; padding:12px 0 12px 28px; display:flex; flex-direction:column; gap:5px; }.asking-price > span { font-size:12px; color:#65736b; }.asking-price strong { font-family:'Outfit','Noto Sans Bengali',sans-serif; font-size:32px; line-height:1.4; font-weight:600; }.asking-price small { font-size:12px; color:#647269; }
.text-action { display:inline-flex; align-items:center; gap:7px; background:transparent; border:0; color:#16684b; font-size:13px; padding:12px 0; min-height:44px; text-align:left; text-decoration:none; line-height:1.6; }
.property-gallery { display:grid; grid-template-columns:1.65fr 1fr; gap:10px; height:360px; border-radius:18px; overflow:hidden; background:#e5ebe3; }.property-gallery.single { grid-template-columns:1fr; }.property-gallery button { border:0; padding:0; position:relative; overflow:hidden; background:#dfe7dc; min-width:0; }.property-gallery img { display:block; width:100%; height:100%; object-fit:cover; transition:transform .25s; }.property-gallery button:hover img { transform:scale(1.025); }
.gallery-caption { position:absolute; bottom:16px; left:16px; display:flex; gap:8px; align-items:center; background:#fff; border-radius:8px; padding:11px 14px; color:#264c3d; font-size:12px; box-shadow:0 3px 15px #0001; }.gallery-empty { display:flex; align-items:center; justify-content:center; flex-direction:column; gap:12px; font-size:14px; }
.property-at-a-glance { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); border-bottom:1px solid #dee5dc; padding:24px 0; margin-bottom:28px; }.property-at-a-glance > div { display:flex; align-items:center; justify-content:center; gap:13px; padding:0 15px; border-right:1px solid #dee5dc; }.property-at-a-glance > div:last-child { border:0; }.property-at-a-glance svg { color:#76917c; flex-shrink:0; }.property-at-a-glance span { font-size:11px; color:#6a786c; }.property-at-a-glance strong { display:block; font-size:15px; color:#254a3c; font-family:'Outfit',sans-serif; font-weight:500; margin-top:4px; }
.interest-banner { display:flex; align-items:center; justify-content:space-between; gap:26px; padding:25px 30px; background:#eaf1e7; border:1px solid #dbe6d5; border-radius:14px; margin-bottom:32px; }.eyebrow { display:block; font-size:11px; color:#597954; font-weight:600; margin-bottom:9px; }.interest-banner h2 { font-size:21px; line-height:1.65; margin-bottom:6px; }.interest-banner p { font-size:13px; color:#5b6f5c; line-height:1.7; }
.primary,.secondary { display:inline-flex; align-items:center; justify-content:center; gap:12px; min-height:52px; padding:13px 22px; border-radius:10px; font-size:14px; line-height:1.6; font-weight:600; border:1px solid #165c43; background:#165c43; color:#fff; text-decoration:none; }.primary:hover { background:#0d4531; }.interest-banner .primary { flex-shrink:0; }.secondary { background:#fff; color:#165c43; margin-top:12px; }
.property-body { display:grid; grid-template-columns:minmax(0,1fr) 330px; gap:40px; align-items:start; }.property-information { min-width:0; }.section-nav { display:flex; overflow-x:auto; gap:22px; border-bottom:1px solid #dce4dc; }.section-nav a { padding:15px 0; color:#456454; font-size:13px; white-space:nowrap; min-height:48px; }
.detail-section { padding:32px 0; border-bottom:1px solid #dce4dc; scroll-margin-top:100px; }.detail-section h2 { font-size:24px; line-height:1.5; margin-bottom:16px; }.detail-section h3 { font-size:17px; margin:25px 0 14px; }.detail-section p { font-size:14px; color:#53665b; line-height:1.9; margin-bottom:14px; }.description { white-space:pre-line; overflow-wrap:anywhere; }.property-tagline { font-weight:600; }
.property-specs { display:grid; grid-template-columns:1fr 1fr; gap:0 28px; margin-top:22px; }.property-specs > div { display:flex; justify-content:space-between; gap:14px; border-bottom:1px solid #e4eae0; padding:13px 0; font-size:12px; }.property-specs dt { color:#69786d; }.property-specs dd { text-align:right; color:#2d4d3d; font-weight:600; overflow-wrap:anywhere; }
.amenity-list { display:grid; grid-template-columns:1fr 1fr; gap:14px; list-style:none; padding:0; }.amenity-list li { display:flex; align-items:flex-start; gap:9px; font-size:13px; color:#53665b; }.amenity-list svg { flex-shrink:0; margin-top:2px; color:#377e57; }
.document-list { display:flex; flex-direction:column; gap:14px; list-style:none; padding:0; margin:20px 0; }.document-list li { display:flex; align-items:center; gap:12px; font-size:13px; }.document-list small { display:block; color:#768575; font-size:10px; margin-top:3px; }
.land-share-note { padding:17px 20px; border-left:3px solid #b29e61; background:#f4f0e4; border-radius:0 10px 10px 0; margin:20px 0; }.land-share-note strong { font-size:14px; }.land-share-note p { margin:6px 0 0; font-size:13px; }
.brochure-list a { display:flex; gap:12px; align-items:center; border:1px solid #d9e3d8; padding:15px; border-radius:9px; margin-top:10px; color:#1b6346; font-size:14px; }.brochure-list span { flex:1; }.detail-section .muted-note { font-size:12px; color:#6f7b70; }
.faq-section details { border-bottom:1px solid #e0e7dc; padding:8px 0; }.faq-section summary { cursor:pointer; padding:14px 0; font-size:14px; font-weight:600; min-height:48px; }.faq-section details p { padding:0 12px 0 0; }
.inquiry-sidebar { position:sticky; top:105px; }.advisor-card { padding:28px; border:1px solid #dbe4d7; border-radius:17px; background:#fff; box-shadow:0 8px 24px #203b2810; }.advisor-symbol { display:inline-flex; padding:12px; background:#eaf2e8; border-radius:13px; margin-bottom:22px; color:#336947; }.advisor-card h2 { font-size:26px; line-height:1.6; margin-bottom:12px; }.advisor-card > p:not(.eyebrow) { font-size:13px; line-height:1.9; color:#687967; }.advisor-card ul { padding:0; list-style:none; display:grid; gap:12px; margin:24px 0; }.advisor-card li { display:flex; gap:9px; font-size:12px; align-items:center; }.advisor-card li svg { flex-shrink:0; color:#548459; }.advisor-card .primary { width:100%; padding:14px 10px; }.advisor-card > small { display:block; text-align:center; font-size:10px; margin-top:12px; color:#71836b; }
.advisor-direct { border:1px solid #dbe4d7; border-radius:14px; background:#fff; padding:14px 18px 6px; margin-top:14px; }.advisor-direct > span { display:block; font-size:11px; color:#71836b; margin-bottom:8px; }.advisor-direct > div { display:flex; justify-content:space-between; }.advisor-direct a { display:inline-flex; align-items:center; gap:5px; min-height:44px; font-size:13px; color:#386b45; }
.mobile-inquiry-bar { display:none; }
.trust-facts { list-style:none; padding:0; margin:10px 0 2px; display:flex; flex-direction:column; gap:5px; }.trust-facts li { display:flex; align-items:center; gap:7px; font-size:13px; color:#1D4A2A; font-weight:600; }.trust-facts svg { color:#3F7A35; flex-shrink:0; }
.hero-cta { display:flex; flex-direction:column; gap:8px; margin-top:14px; }
.cta-sun { display:inline-flex; align-items:center; justify-content:center; min-height:54px; padding:12px 22px; border:0; border-radius:999px; background:#E2651C; color:#fff; font-family:'Anek Bangla','Noto Sans Bengali',sans-serif; font-size:18px; font-weight:700; cursor:pointer; box-shadow:0 10px 22px -12px rgba(194,83,15,.9); }.cta-sun:hover { background:#C2530F; }
.cta-wa { display:inline-flex; align-items:center; justify-content:center; gap:8px; min-height:48px; border:1.5px solid #1FA855; border-radius:999px; color:#137A3D; font-size:14px; font-weight:600; text-decoration:none; background:#fff; }.cta-wa:hover { background:#EAF7EF; }
.cta-note { display:block; text-align:center; font-size:11px; color:#647269; margin-top:6px; }
.property-bottom-cta { margin:48px 0 28px; }
.bottom-cta-card { background:linear-gradient(135deg, #0e2a1e 0%, #153e2c 60%, #1a4a35 100%); border-radius:20px; padding:38px 44px; color:#fff; display:grid; grid-template-columns:1.35fr 1fr; gap:36px; align-items:center; box-shadow:0 18px 42px -12px rgba(14,42,30,.4); border:1px solid rgba(226,101,28,.3); position:relative; overflow:hidden; }
.bottom-cta-card::after { content:''; position:absolute; top:-50%; right:-20%; width:380px; height:380px; background:radial-gradient(circle, rgba(226,101,28,.14) 0%, transparent 70%); pointer-events:none; }
.bottom-cta-badge { display:inline-block; background:rgba(226,101,28,.22); color:#f7a26d; border:1px solid rgba(226,101,28,.4); padding:5px 14px; border-radius:999px; font-size:12px; font-weight:700; letter-spacing:.04em; margin-bottom:12px; }
.bottom-cta-content h2 { font-family:'Outfit','Noto Sans Bengali',sans-serif; font-size:clamp(22px,2.2vw,28px); color:#fff; line-height:1.35; margin-bottom:12px; font-weight:700; }
.bottom-cta-desc { font-size:14px; color:#c9dbd1; line-height:1.75; margin-bottom:18px; }
.bottom-cta-highlights { list-style:none; padding:0; margin:0; display:flex; flex-direction:column; gap:9px; }
.bottom-cta-highlights li { display:flex; align-items:center; gap:10px; font-size:13px; color:#e2ede7; }
.bottom-cta-highlights svg { color:#38c172; flex-shrink:0; }
.bottom-cta-actions { display:flex; flex-direction:column; gap:12px; background:rgba(255,255,255,.07); padding:24px 26px; border-radius:16px; border:1px solid rgba(255,255,255,.14); backdrop-filter:blur(8px); }
.bottom-price-tag { display:flex; flex-direction:column; margin-bottom:4px; border-bottom:1px solid rgba(255,255,255,.14); padding-bottom:10px; }
.bottom-price-tag .price-caption { font-size:11px; color:#a4c4b5; }
.bottom-price-tag .price-val { font-family:'Outfit','Noto Sans Bengali',sans-serif; font-size:24px; color:#fff; font-weight:700; }
.bottom-price-tag small { font-size:11px; color:#a4c4b5; }
.bottom-cta-btn { width:100%; font-size:17px; }
.bottom-wa-btn { width:100%; }
.bottom-phone-pill { display:inline-flex; align-items:center; justify-content:center; gap:8px; font-size:12px; color:#b9d7cb; text-decoration:none; padding:6px 0; transition:color .2s; }
.bottom-phone-pill span { color:#fff; font-weight:700; }
.bottom-phone-pill:hover { color:#38c172; }
.gallery-tools { display:none; }.property-gallery { position:relative; }.property-state { max-width:650px; margin:auto; padding:80px 24px; text-align:center; }.property-state h1 { font-size:24px; }.property-state p { margin:20px 0; }.property-state a { display:block; margin-top:24px; }.loading-block { height:180px; background:#e9efe6; border-radius:20px; margin-bottom:24px; }
.photo-overlay { position:fixed; inset:0; z-index:11000; background:#07130ef5; display:flex; flex-direction:column; justify-content:center; align-items:center; padding:60px 20px 25px; }.photo-overlay > img { max-width:100%; max-height:calc(100dvh - 155px); object-fit:contain; }.photo-overlay button { background:#fff2; border:1px solid #ffffff40; color:white; width:48px; height:48px; display:grid; place-items:center; border-radius:50%; cursor:pointer; }.photo-overlay button:focus-visible { outline:3px solid #e0bc68; }.photo-close { position:absolute; top:14px; right:18px; }.photo-controls { display:flex; align-items:center; gap:24px; color:white; margin-top:16px; }.photo-controls button:disabled { opacity:.3; }
@media (max-width:1024px) { .property-body { grid-template-columns:minmax(0,1fr) 300px; gap:24px; }.advisor-card { padding:22px; }.property-heading { gap:24px; grid-template-columns:minmax(0,1fr) 250px; }.asking-price { padding-left:20px; }.property-specs { grid-template-columns:1fr; } }
@media (max-width:767px) {
  .property-page { padding:18px 0 100px; }.property-shell { padding:0 18px; }.property-topline { flex-wrap:wrap; margin-bottom:20px; gap:10px; }.property-tools { gap:5px; }.property-tools button { padding:8px 10px; font-size:11px; }.property-topline nav { font-size:11px; }
  .property-heading { grid-template-columns:1fr; gap:17px; }.property-heading h1 { font-size:29px; letter-spacing:-.5px; margin-bottom:10px; }.property-badges { margin-bottom:11px; }.property-badges span { font-size:10px; }.property-location { font-size:12px; }
  .asking-price { border-left:0; padding:14px 17px; background:#eef2e9; border-radius:12px; gap:3px; }.asking-price strong { font-size:29px; }.asking-price .text-action { padding:7px 0 0; min-height:36px; font-size:12px; }
  .property-gallery { height:240px; grid-template-columns:1fr; border-radius:13px; }.gallery-secondary { display:none; }.gallery-caption { bottom:12px; left:12px; }
  .property-at-a-glance { grid-template-columns:1fr 1fr; gap:22px 0; padding:23px 0; margin-bottom:22px; }.property-at-a-glance > div { justify-content:flex-start; padding:0 12px; gap:12px; }.property-at-a-glance > div:nth-child(2) { border:0; }.property-at-a-glance strong { font-size:15px; }
  .interest-banner { flex-direction:column; align-items:stretch; padding:22px; gap:15px; margin-bottom:24px; }.interest-banner h2 { font-size:21px; }.interest-banner p { font-size:12px; }.interest-banner .primary { width:100%; }
  .property-body { grid-template-columns:minmax(0,1fr); gap:24px; }.section-nav { gap:23px; }.section-nav a { font-size:12px; }.detail-section { padding:28px 0; }.detail-section h2 { font-size:22px; }.detail-section p { font-size:14px; }.property-specs { grid-template-columns:1fr; }.amenity-list { grid-template-columns:1fr; }.inquiry-sidebar { position:static; }.advisor-card h2 br { display:none; }.advisor-card { padding:24px; }.advisor-symbol { margin-bottom:16px; }
  .mobile-inquiry-bar { position:fixed; bottom:0; left:0; right:0; z-index:900; display:flex; align-items:center; justify-content:space-between; gap:12px; background:#fff; border-top:1px solid #d9e2d3; padding:12px 18px max(12px,env(safe-area-inset-bottom)); box-shadow:0 -5px 30px #1a332216; }.mobile-inquiry-bar > div { min-width:0; flex:1; }.mobile-inquiry-bar strong { font-size:18px; line-height:1.4; display:block; white-space:nowrap; }.mobile-inquiry-bar small { font-size:10px; display:block; color:#667562; line-height:1.5; }.mobile-inquiry-bar .primary { padding:12px 16px; font-size:13px; min-height:48px; flex-shrink:0; }
}
@media (max-width:767px) {
  .property-shell { display:flex; flex-direction:column; }
  .property-topline { display:none; }
  .property-gallery { order:-1; margin:-18px -18px 18px; border-radius:0; height:clamp(200px,58vw,300px); }
  .trust-facts { flex-direction:row; flex-wrap:wrap; gap:6px; margin-top:8px; }
  .trust-facts li { background:#fff; border-radius:999px; padding:3px 10px 3px 8px; font-size:12px; }
  .hero-cta { margin-top:12px; }
  .gallery-tools { display:flex; position:absolute; top:12px; right:12px; gap:8px; z-index:2; }
  .gallery-tools button { width:42px; height:42px; border-radius:50%; background:rgba(255,255,255,.92); color:#1D4A2A; display:grid; place-items:center; box-shadow:0 2px 10px #0002; }
  .gallery-tools button[aria-pressed=true] { background:#E2651C; color:#fff; }
  .hero-cta .cta-sun { width:100%; }
  .mobile-inquiry-bar { transform:translateY(110%); transition:transform .25s ease; gap:10px; }
  .mobile-inquiry-bar.shown { transform:none; }
  .mobile-inquiry-bar .cta-sun { min-height:48px; font-size:15px; padding:10px 16px; flex-shrink:1; min-width:0; white-space:nowrap; }
  .bar-wa { width:48px; height:48px; border-radius:50%; display:grid; place-items:center; background:#1FA855; color:#fff; flex-shrink:0; }
  .bottom-cta-card { grid-template-columns:1fr; padding:28px 20px; gap:24px; border-radius:16px; }
  .property-bottom-cta { margin:32px 0 20px; }
}
@media (prefers-reduced-motion:reduce) { .mobile-inquiry-bar { transition:none; } }
@media (max-width:360px) { .property-shell { padding:0 14px; }.property-topline { gap:7px; }.property-tools button { padding:8px; }.property-heading h1 { font-size:26px; }.mobile-inquiry-bar { padding-left:12px; padding-right:12px; }.mobile-inquiry-bar strong { font-size:16px; }.mobile-inquiry-bar .primary { padding:11px; }.property-gallery { height:210px; } }
@media (prefers-reduced-motion:reduce) { .property-gallery img { transition:none; } }

/* Video in the gallery: a poster with one clear play control; it only loads when tapped. */
.property-gallery .gallery-video { background:#16241A; }
.gallery-video img, .gallery-video video { opacity:.88; }
.play-disc { position:absolute; left:50%; top:50%; transform:translate(-50%,-50%); width:78px; height:78px; border-radius:50%; display:grid; place-items:center; padding-left:5px; background:#E2651C; color:#fff; box-shadow:0 10px 30px rgba(0,0,0,.35), 0 0 0 10px rgba(226,101,28,.25); transition:transform .2s; }
.gallery-secondary .play-disc { width:60px; height:60px; box-shadow:0 8px 22px rgba(0,0,0,.35), 0 0 0 7px rgba(226,101,28,.25); }
.gallery-video:hover .play-disc { transform:translate(-50%,-50%) scale(1.06); }
.gallery-player { position:relative; background:#000; }
.gallery-player video, .gallery-player iframe { position:absolute; inset:0; width:100%; height:100%; border:0; object-fit:contain; background:#000; }
.gallery-chips { position:absolute; right:16px; bottom:16px; z-index:2; display:flex; gap:8px; }
.property-gallery .gallery-chip { display:inline-flex; overflow:visible; align-items:center; gap:7px; min-height:40px; padding:0 14px; border:0; border-radius:999px; background:#fff; color:#1D4A2A; font:inherit; font-size:13px; font-weight:700; box-shadow:0 3px 15px rgba(0,0,0,.15); cursor:pointer; }
.property-gallery .gallery-chip--video { background:#E2651C; color:#fff; }
.photo-video { width:min(1100px,100%); aspect-ratio:16/9; max-height:calc(100dvh - 155px); background:#000; border-radius:10px; overflow:hidden; }
.photo-video video, .photo-video iframe { width:100%; height:100%; border:0; display:block; }
@media (max-width:768px) { .gallery-chips { right:12px; bottom:12px; } .play-disc { width:64px; height:64px; } }
@media (prefers-reduced-motion:reduce) { .play-disc { transition:none; } }
</style>

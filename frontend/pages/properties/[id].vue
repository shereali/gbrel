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
      <div class="property-main-layout">
        <!-- Left Primary Column: Title, Main Showcase Media, Gallery Thumbs, Specs, and Information Sections (All sharing same width) -->
        <main class="property-primary-col">
          <header class="property-heading-block">
            <div class="property-badges">
              <span>{{ typeLabel(property.propertyType) }}</span>
              <span>{{ listingLabels[property.listingType] || property.listingType }}</span>
              <span v-if="property.completionStatus">{{ completionLabels[property.completionStatus] || property.completionStatus }}</span>
            </div>
            <h1>{{ property.title }}</h1>
            <p class="property-location"><MapPin :size="17" /> {{ location }}</p>
          </header>

          <!-- Main Showcase (Feature Image or Uncropped Video Player) -->
          <section class="property-media-showcase" aria-label="প্রপার্টির মূল মিডিয়া">
            <div class="gallery-tools">
              <button :aria-pressed="isInCompare(property.id)" aria-label="তুলনা করুন" :title="isInCompare(property.id) ? 'তুলনা থেকে সরান' : 'তুলনা করুন'" @click="toggleCompare(property.id)"><Copy :size="18" /></button>
              <button :aria-label="'শেয়ার করুন'" @click="shareProperty"><Share2 :size="18" /></button>
              <button :aria-pressed="isPropertySaved(property.id)" aria-label="সেভ করুন" @click="toggleSaveProperty(property.id)"><Heart :size="18" :fill="isPropertySaved(property.id) ? 'currentColor' : 'none'" /></button>
            </div>

            <template v-if="images.length || video">
              <!-- Video Player: Uncropped, Full Aspect Ratio, Cinematic Ambient Backdrop -->
              <div v-if="activeMediaTab === 'video' && video" class="showcase-player">
                <div v-if="videoPoster" class="showcase-player-backdrop" :style="{ backgroundImage: `url(${resolveMediaUrl(videoPoster)})` }" aria-hidden="true"></div>
                <video v-if="video.kind === 'file'" ref="heroVideoEl" :src="resolveMediaUrl(video.src)" :poster="resolveMediaUrl(videoPoster) || undefined" autoplay :muted="heroMuted" loop controls playsinline></video>
                <iframe v-else ref="heroFrame" :src="heroEmbed?.src" title="প্রপার্টির ভিডিও" allow="autoplay; encrypted-media; picture-in-picture; fullscreen" allowfullscreen></iframe>
                <button v-if="heroMuted && video.kind !== 'facebook'" type="button" class="unmute-btn" @click="unmuteHero"><Volume2 :size="18" aria-hidden="true" /> শব্দ চালু করুন</button>
                <button type="button" class="player-photos-back-btn" @click="switchToPhotos"><ImageIcon :size="15" /> ছবি দেখুন</button>
              </div>

              <!-- Main Feature Photo Showcase -->
              <div v-else-if="images.length" class="showcase-photo-wrapper">
                <img :src="resolveMediaUrl(activePhotoSrc)" :alt="property.title" class="showcase-img" fetchpriority="high" decoding="async" @click="openImageAt(activePhotoIndex)" @error="imageFailed" />
                <span v-if="activePhotoIndex === 0" class="showcase-badge"><ImageIcon :size="13" /> ফিচার ছবি</span>
                <span v-else class="showcase-badge"><ImageIcon :size="13" /> ছবি {{ toBn(activePhotoIndex + 1) }} / {{ toBn(images.length) }}</span>

                <!-- Prev / Next Navigation Arrows -->
                <button v-if="images.length > 1" type="button" class="showcase-arrow showcase-arrow--prev" aria-label="পূর্ববর্তী ছবি" @click.stop="prevShowcasePhoto">
                  <ChevronLeft :size="22" />
                </button>
                <button v-if="images.length > 1" type="button" class="showcase-arrow showcase-arrow--next" aria-label="পরবর্তী ছবি" @click.stop="nextShowcasePhoto">
                  <ChevronRight :size="22" />
                </button>

                <!-- Expand Button -->
                <button type="button" class="showcase-expand-btn" aria-label="সব ছবি বড় করে দেখুন" @click.stop="openImageAt(activePhotoIndex)">
                  <Expand :size="15" /> বড় করে দেখুন
                </button>
              </div>
            </template>
            <PropertyPlotSheet v-else :property="property" />
          </section>

          <!-- Gallery Images Positioned at the Bottom of Main Image -->
          <div v-if="images.length > 1 || video" class="gallery-thumbs-strip" aria-label="প্রপার্টির গ্যালারি ছবিসমূহ">
            <!-- Video Thumbnail (if property has video) -->
            <button
              v-if="video"
              type="button"
              class="thumb-box thumb-box--video"
              :class="{ 'is-active': activeMediaTab === 'video' }"
              aria-label="ভিডিও ট্যুর দেখুন"
              @click="switchToVideo"
            >
              <img v-if="videoPoster" :src="resolveMediaUrl(videoPoster)" alt="ভিডিও পোস্টার" loading="lazy" />
              <span class="thumb-play-overlay"><Play :size="18" fill="currentColor" /></span>
              <span class="thumb-tag">ভিডিও</span>
            </button>

            <!-- Photo Thumbnails -->
            <button
              v-for="(imgSrc, idx) in images"
              :key="imgSrc"
              type="button"
              class="thumb-box"
              :class="{ 'is-active': activeMediaTab === 'photos' && activePhotoIndex === idx }"
              :aria-label="`ছবি ${idx + 1}`"
              @click="selectThumb(idx)"
            >
              <img :src="resolveMediaUrl(imgSrc)" :alt="`ছবি ${idx + 1}`" loading="lazy" decoding="async" @error="imageFailed" />
              <span v-if="idx === 0" class="thumb-feature-tag">ফিচার</span>
            </button>

            <!-- View All Photos button -->
            <button
              type="button"
              class="thumb-all-btn"
              aria-label="সব ছবি একসাথে দেখুন"
              @click="openImageAt(activePhotoIndex)"
            >
              <Expand :size="16" />
              <span>সকল ছবি ({{ toBn(images.length) }})</span>
            </button>
          </div>

          <!-- Specs Glance Cards (Same Width as Title & Main Image) -->
          <div class="property-at-a-glance" role="region" aria-label="প্রপার্টির মূল বৈশিষ্ট্য">
            <div v-for="item in glanceItems" :key="item.label" class="glance-card">
              <component :is="item.icon" :size="22" class="glance-icon" aria-hidden="true" />
              <div class="glance-info">
                <span class="glance-label">{{ item.label }}</span>
                <strong class="glance-val">{{ item.value }}</strong>
              </div>
            </div>
          </div>

          <div ref="inlineStart"><PropertySurveyStart :property-type="property.propertyType" @choose="purpose => openInquiry('inline_first_question', purpose)" /></div>

          <div class="property-information">
            <nav class="section-nav" aria-label="প্রপার্টির বিভাগ">
              <a href="#property-overview">বিস্তারিত</a>
              <a v-if="flatVariants.length" href="#property-flat-variants">ফ্ল্যাট সাইজ</a>
              <a v-if="hasBuyerDetails" href="#property-buyer-details">মূল্য ও শর্ত</a>
              <a href="#property-documents">কাগজপত্র</a>
              <a href="#property-location">লোকেশন</a>
              <a href="#property-questions">আপনার প্রশ্ন</a>
            </nav>
            <section id="property-overview" class="detail-section"><p class="eyebrow">এক নজরে প্রপার্টি</p><h2>এখানে কী পাচ্ছেন?</h2><p v-if="property.tagline" class="property-tagline">{{ property.tagline }}</p><p class="description">{{ property.description }}</p><dl class="property-specs"><div v-for="item in specifications" :key="item.label"><dt>{{ item.label }}</dt><dd>{{ item.value }}</dd></div></dl><template v-if="property.amenities?.length"><h3>সুবিধাসমূহ</h3><ul class="amenity-list"><li v-for="amenity in property.amenities" :key="amenity"><Check :size="16" />{{ amenity }}</li></ul></template></section>
            <PropertyFlatVariants 
              v-if="flatVariants.length" 
              :variants="flatVariants" 
              :property="property" 
              :whatsapp="leadWhatsapp" 
              @select="onVariantSelect"
              @inquire="v => openInquiry('flat_variant_' + v.name, v.name)"
            />
            <PropertyBuyerDetails :property="property" :cta-label="ctaLabel" @inquire="openInquiry('sale_terms')" />
            <section id="property-documents" class="detail-section">
              <p class="eyebrow">সিদ্ধান্তের আগে পরিষ্কার ধারণা</p><h2>কাগজপত্র ও খরচ বুঝে নিন</h2><p>মালিকানা, অনুমোদন, রেজিস্ট্রেশন ও প্রযোজ্য খরচ নিয়ে প্রশ্ন করুন। তালিকায় থাকা তথ্যের সঙ্গে মূল নথি মিলিয়ে দেখুন।</p>
              <ul v-if="property.documentsVerified?.length" class="document-list"><li v-for="document in property.documentsVerified" :key="document"><FileText :size="18" /><span>{{ document }} <small>তালিকায় উল্লেখিত</small></span></li></ul>
              <div v-if="property.propertyType === 'Land Share'" class="land-share-note"><strong>জমির শেয়ার কিনছেন?</strong><p>এই মূল্যে জমির কতটুকু অংশ পাবেন, নির্মাণ খরচ আলাদা কি না, এবং হস্তান্তরের শর্ত—সবগুলো আলাদা করে নিশ্চিত করুন।</p></div>
              <div v-if="brochures.length" class="brochure-list"><a v-for="doc in brochures" :key="doc.url" :href="resolveMediaUrl(doc.url)" target="_blank" rel="noopener noreferrer"><FileDown :size="20" /><span>{{ doc.title }}</span><ArrowUpRight :size="18" /></a></div>
              <p v-else class="muted-note">এই পেজে ব্রোশিওর প্রকাশ করা হয়নি। উপলব্ধ কাগজপত্র সম্পর্কে টিমকে জিজ্ঞেস করতে পারেন।</p><button class="text-action" @click="openInquiry('documents')">{{ ctaLabel }} <ArrowRight :size="16" /></button>
            </section>
            <section id="property-location" class="detail-section"><p class="eyebrow">নিজে দেখে সিদ্ধান্ত নিন</p><h2>লোকেশন ও সাইট ভিজিট</h2><p class="property-location"><MapPin :size="20" />{{ location }}</p><p>সাইট ভিজিটের আগে সঠিক লোকেশন, যাতায়াতের পথ ও সময় টিমের সঙ্গে মিলিয়ে নিন।</p><a v-if="!property.hideExactAddress && property.lat && property.lng" class="text-action" :href="`https://www.google.com/maps/search/?api=1&query=${property.lat},${property.lng}`" target="_blank" rel="noopener noreferrer">ম্যাপে লোকেশন দেখুন <ArrowUpRight :size="16" /></a><button class="secondary" @click="openInquiry('site_visit')">{{ ctaLabel }} <ArrowRight :size="16" /></button></section>
            <section id="property-questions" class="detail-section faq-section"><p class="eyebrow">সহজ উত্তর</p><h2>আপনার মনে হতে পারে</h2><details><summary>ফর্ম পূরণ করলে কি বুকিং হয়ে যাবে?</summary><p>না। এটি শুধু এই প্রপার্টি সম্পর্কে তথ্য ও যোগাযোগের অনুরোধ। কোনো টাকা বা বুকিংয়ের অঙ্গীকার প্রয়োজন নেই।</p></details><details><summary>তালিকাভুক্ত দামের বাইরে খরচ আছে?</summary><p>রেজিস্ট্রেশন, কর, সার্ভিস চার্জ{{ buildCostApplies ? ' এবং নির্মাণ খরচ' : '' }} মূল্যের মধ্যে আছে কি না, টিমের কাছে পূর্ণ হিসাব চেয়ে নিন। প্রকাশিত খরচের বিবরণ দেখুন; কোনো খরচ উল্লেখ না থাকলে তা অন্তর্ভুক্ত ধরে নেবেন না।</p></details><details><summary>এখনই কিনব না, তবু কথা বলা যাবে?</summary><p>অবশ্যই। ফর্মে আপনার আসল সময়সীমা বেছে নিন। আপনার প্রস্তুতি অনুযায়ী আলোচনা করা যাবে।</p></details><details><summary>ফর্ম জমা দেওয়ার পর কী হবে?</summary><p>GBREL টিম আপনার দেওয়া নম্বরে, পছন্দের মাধ্যমে যোগাযোগ করবে। ঐচ্ছিকভাবে সময় বা আগে জানতে চাওয়া বিষয়ও জানাতে পারবেন।</p></details></section>
          </div>
        </main>

        <!-- Right Sticky Sidebar: Asking Price Card & Direct Advisory -->
        <aside class="property-sidebar-col" :class="{ 'is-resting': bottomCtaVisible }" :aria-hidden="(bottomCtaVisible && isDesktop) || undefined">
          <!-- Price & Hero Action Card -->
          <div class="asking-price-card">
            <div class="price-header">
              <span class="price-type">{{ price.hidden ? 'তালিকাভুক্ত মূল্য' : price.per }}</span>
              <strong class="price-number">{{ price.amount }}</strong>
              <small v-if="price.note" class="price-subnote">{{ price.note }}</small>
            </div>
            <ul v-if="trustFacts.length" class="trust-facts" aria-label="বিক্রেতার দেওয়া তথ্য">
              <li v-for="fact in trustFacts" :key="fact"><Check :size="15" aria-hidden="true" />{{ fact }}</li>
            </ul>
            <small v-if="trustFacts.length" class="trust-source">বিক্রেতার দেওয়া তথ্য · কাগজ দেখে মিলিয়ে নিন</small>
            <div ref="heroCta" class="hero-cta">
              <button class="cta-sun" @click="openInquiry('hero_button')">{{ ctaLabel }}</button>
              <a v-if="waLink" class="cta-wa" :href="waLink" target="_blank" rel="noopener noreferrer" @click="track('Contact', { method: 'WhatsApp', placement: 'hero' })">
                <MessageCircle :size="18" aria-hidden="true" /> WhatsApp-এ কথা বলুন
              </a>
            </div>
            <small v-if="settings.property_cta_note" class="cta-note">{{ settings.property_cta_note }}</small>
            <NuxtLink to="/legal" class="trust-line"><ShieldCheck :size="15" aria-hidden="true" /> সরকারি নিবন্ধিত কোম্পানি · লাইসেন্স দেখুন</NuxtLink>
          </div>

          <!-- Direct Phone / WhatsApp Advisor -->
          <div v-if="waLink || agentTel" class="advisor-direct">
            <span>প্রশ্ন ছাড়াই সরাসরি কথা বলতে চান?</span>
            <div>
              <a v-if="waLink" :href="waLink" target="_blank" rel="noopener noreferrer" @click="track('Contact', { method: 'WhatsApp', placement: 'sidebar' })">WhatsApp <ArrowUpRight :size="15" /></a>
              <a v-if="agentTel" :href="agentTel" @click="track('Contact', { method: 'Phone', placement: 'sidebar' })"><Phone :size="15" /> কল করুন</a>
            </div>
          </div>

          <!-- Survey widget in sidebar -->
          <PropertySurveyStart v-show="!inlineVisible" side :property-type="property.propertyType" class="side-start" @choose="purpose => openInquiry('sidebar_first_question', purpose)" />
        </aside>
      </div>

      <!-- Bottom Closing CTA Section for Desktop & Wide Devices -->
      <section ref="bottomCta" class="property-bottom-cta" aria-labelledby="bottom-cta-heading">
        <div class="bottom-cta-card">
          <div class="bottom-cta-content">
            <span class="bottom-cta-badge">সাইট ভিজিট ও কাগজপত্র</span>
            <h2 id="bottom-cta-heading">প্রপার্টিটি নিজের চোখে দেখতে চান বা কোনো প্রশ্ন আছে?</h2>
            <p class="bottom-cta-desc">
              {{ bottomPromise }}
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
            <a v-if="agentTel" class="bottom-phone-pill" :href="agentTel" @click="track('Contact', { method: 'Phone', placement: 'bottom_banner' })">
              <Phone :size="15" aria-hidden="true" />
              সরাসরি কথা বলুন: <span>{{ directPhone }}</span>
            </a>
          </div>
        </div>
      </section>

      <aside v-if="!inquiryOpen && !lightboxOpen" class="mobile-inquiry-bar" :class="{ shown: !heroCtaVisible && !bottomCtaVisible }" aria-label="প্রপার্টি সম্পর্কে যোগাযোগ">
        <div class="bar-info">
          <strong>{{ price.amount }}</strong>
          <small>{{ price.hidden ? 'বিস্তারিত জেনে নিন' : price.per }}</small>
        </div>
        <div class="bar-actions">
          <a v-if="waLink" class="bar-wa" :href="waLink" target="_blank" rel="noopener noreferrer" aria-label="WhatsApp-এ জিজ্ঞেস করুন" @click="track('Contact', { method: 'WhatsApp', placement: 'sticky' })">
            <MessageCircle :size="20" />
          </a>
          <button class="cta-sun bar-cta-btn" @click="openInquiry('mobile_sticky')">
            {{ ctaLabel }}
          </button>
        </div>
      </aside>
      <PropertyInquiry :key="property.id" :open="inquiryOpen" :property="property" :source="inquirySource" :start-purpose="startPurpose" :whatsapp="leadWhatsapp" @close="inquiryOpen = false" @saved="leadSaved" />
    </div>
    <Teleport to="body"><div v-if="lightboxOpen && currentSlide" ref="lightboxRoot" class="photo-overlay" role="dialog" aria-modal="true" aria-label="প্রপার্টির ছবি ও ভিডিও" @click.self="lightboxOpen = false" @keydown.left="previousPhoto" @keydown.right="nextPhoto"><button class="photo-close" aria-label="বন্ধ করুন" @click="lightboxOpen = false"><X :size="25" /></button><img v-if="currentSlide.kind === 'image'" :src="resolveMediaUrl(currentSlide.src)" :alt="`${property?.title} — ছবি ${photoIndex + 1}`" /><div v-else-if="video" class="photo-video"><video v-if="video.kind === 'file'" :key="video.src" :src="resolveMediaUrl(video.src)" :poster="resolveMediaUrl(videoPoster) || undefined" autoplay loop controls playsinline></video><iframe v-else :key="video.src" :src="video.src" title="প্রপার্টির ভিডিও" allow="autoplay; encrypted-media; picture-in-picture; fullscreen" allowfullscreen></iframe></div><div class="photo-controls"><button :disabled="slides.length < 2" aria-label="আগেরটি" @click="previousPhoto"><ChevronLeft /></button><span aria-live="polite">{{ photoIndex + 1 }} / {{ slides.length }}</span><button :disabled="slides.length < 2" aria-label="পরেরটি" @click="nextPhoto"><ChevronRight /></button></div></div></Teleport>
  </div>
</template>

<script setup lang="ts">
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue'
import { ArrowRight, ArrowUpRight, Bath, Bed, Building2, Check, ChevronLeft, ChevronRight, CircleCheck, Compass, Copy, Expand, FileDown, FileText, Film, Heart, Image as ImageIcon, MapPin, MessageCircle, MessagesSquare, Phone, Play, ShieldCheck, Volume2, Ruler, Share2, X } from 'lucide-vue-next'
import { useProperties, type PropertyItem } from '~/composables/useProperties'
import { formatBDT, formatArea } from '~/composables/useCurrency'
import { useAuth } from '~/composables/useAuth'
import { useCompare } from '~/composables/useCompare'
import { useOverlayBehavior } from '~/composables/useOverlayBehavior'
import { detailGroups } from '~/utils/buyerDetails.mjs'
import { priceDisplay } from '~/utils/priceDisplay'
import { parseVideo, unmuteEmbed } from '~/utils/videoEmbed'
import { resolveMediaUrl } from '~/utils/mediaUrl'
import { safeBrochureUrl } from '~/utils/propertyInquiry.mjs'
import { trackPixel } from '~/utils/metaPixel'
import { telHref, whatsappLink } from '~/utils/contact'
import { absoluteImage, propertyShareDescription } from '~/utils/shareMeta'
import { useSettings } from '~/composables/useSettings'
import PropertySurveyStart from '~/components/PropertySurveyStart.vue'
import PropertyPlotSheet from '~/components/PropertyPlotSheet.vue'
import PropertyFlatVariants, { type FlatVariant } from '~/components/PropertyFlatVariants.vue'
import { getFlatVariants, flatVariantsSummary } from '~/utils/buyerDetails.mjs'
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
const images = computed(() => [...new Set((property.value?.images || []).filter(Boolean))].map(url => resolveMediaUrl(url)))
// Video: the listing chooses whether buyers land on the video or the main photo. Nothing autoplays; a tap plays it.
const video = computed(() => parseVideo(property.value?.videoUrl))
const videoPoster = computed(() => resolveMediaUrl(property.value?.videoPoster || video.value?.thumbnail || images.value[0] || ''))
type Slide = { kind: 'image'; src: string } | { kind: 'video' }
const slides = computed<Slide[]>(() => {
  const photos: Slide[] = images.value.map(src => ({ kind: 'image', src }))
  if (!video.value) return photos
  const clip: Slide = { kind: 'video' }
  return property.value?.coverMedia === 'video' || !photos.length ? [clip, ...photos] : [photos[0], clip, ...photos.slice(1)]
})
const videoIndex = computed(() => slides.value.findIndex(slide => slide.kind === 'video'))
const currentSlide = computed(() => slides.value[photoIndex.value])

// Media Showcase & Tabs
const activeMediaTab = ref<'photos' | 'video'>('photos')
const activePhotoIndex = ref(0)
const activePhotoSrc = computed(() => images.value[activePhotoIndex.value] || images.value[0] || '')

const selectThumb = (idx: number) => {
  activePhotoIndex.value = idx
  activeMediaTab.value = 'photos'
  if (heroVideoEl.value) {
    heroVideoEl.value.pause()
  }
}

const prevShowcasePhoto = () => {
  if (!images.value.length) return
  activePhotoIndex.value = (activePhotoIndex.value + images.value.length - 1) % images.value.length
}

const nextShowcasePhoto = () => {
  if (!images.value.length) return
  activePhotoIndex.value = (activePhotoIndex.value + 1) % images.value.length
}

const secondaryImages = computed(() => images.value.slice(1, 5))

const openImageAt = (imgIdx: number) => {
  const targetSrc = images.value[imgIdx]
  const slideIdx = slides.value.findIndex(s => s.kind === 'image' && s.src === targetSrc)
  photoIndex.value = slideIdx >= 0 ? slideIdx : 0
  lightboxOpen.value = true
}

const switchToVideo = () => {
  activeMediaTab.value = 'video'
  if (!heroPlaying.value) playHeroVideo()
}

const switchToPhotos = () => {
  activeMediaTab.value = 'photos'
  if (heroVideoEl.value) {
    heroVideoEl.value.pause()
  }
}

const heroPlaying = ref(false)
// Autoplay only works with the sound off, so the hero starts muted with a clear "sound on" button.
const heroMuted = ref(false)
const heroFrame = ref<HTMLIFrameElement | null>(null)
const heroVideoEl = ref<HTMLVideoElement | null>(null)
// Built once when playback starts, so turning the sound on does not reload the player.
const heroEmbed = ref<ReturnType<typeof parseVideo>>(null)
const playHeroVideo = () => { heroMuted.value = false; heroEmbed.value = parseVideo(property.value?.videoUrl); heroPlaying.value = true; track('ViewContent', { content_category: 'video' }) }
const unmuteHero = () => {
  heroMuted.value = false
  if (heroVideoEl.value) { heroVideoEl.value.muted = false; heroVideoEl.value.play().catch(() => {}) }
  else if (video.value) unmuteEmbed(heroFrame.value, video.value.kind)
}
// Video-first listings start playing on arrival, unless the visitor asked for less motion or to save data.
const startHeroAutoplay = () => {
  if (!process.client || !video.value || slides.value[0]?.kind !== 'video') return
  const reduceMotion = window.matchMedia?.('(prefers-reduced-motion: reduce)').matches
  const saveData = (navigator as any).connection?.saveData
  if (reduceMotion || saveData) return
  heroMuted.value = true
  heroEmbed.value = parseVideo(property.value?.videoUrl, { muted: true })
  heroPlaying.value = true
}
const price = computed(() => priceDisplay(property.value))
// "No advance fee" is always true; "no hidden fee" is only honest because the buyer's charge sits next to the price.
const bottomPromise = computed(() => `দাম, কাগজপত্র আর সাইট ভিজিট নিয়ে আপনার সব প্রশ্নের উত্তর দেবে GBREL টিম। বিক্রির পুরো প্রক্রিয়া আমরাই পরিচালনা করি। কোনো অগ্রিম ফি নেই।${price.value.fee && !price.value.fee.includes('নেই') ? ` ${price.value.fee}, দামের পাশেই লেখা।` : ''}`)
// Facts strip under the photos (Responsive cards, never empty slots)
const glanceItems = computed(() => {
  const p = property.value
  if (!p) return []
  const list: Array<{ icon: any; label: string; value: string }> = [
    { icon: Ruler, label: 'আয়তন', value: areaGlanceText.value },
    { icon: Building2, label: 'ধরন', value: typeLabel(p.propertyType) }
  ]
  if (p.bedrooms) {
    list.push({ icon: Bed, label: 'বেডরুম', value: `${toBn(p.bedrooms)}টি` })
  }
  if (p.bathrooms) {
    list.push({ icon: Bath, label: 'বাথরুম', value: `${toBn(p.bathrooms)}টি` })
  } else if (p.facing) {
    list.push({ icon: Compass, label: 'অভিমুখ', value: facingLabel(p.facing) })
  } else if (p.totalFloors) {
    list.push({ icon: Building2, label: 'মোট তলা', value: `${toBn(p.totalFloors)} তলা` })
  } else if (p.parking) {
    list.push({ icon: Building2, label: 'পার্কিং', value: `${toBn(p.parking)}টি` })
  }
  if (p.status) {
    list.push({ icon: CircleCheck, label: 'অবস্থা', value: statusLabel(p.status) })
  }
  return list.filter(item => Boolean(item.value && String(item.value).trim()))
})
const glanceThird = computed(() => glanceItems.value[2] || null)
const buildCostApplies = computed(() => property.value?.propertyType === 'Land Share' || ['Under Construction', 'Upcoming Project'].includes(property.value?.completionStatus || ''))
// Same sentence the server puts in link previews (server/plugins/share-meta.ts), so the tab and the preview agree.
const metaDescription = computed(() => propertyShareDescription(property.value))

useSeoMeta({
  title: () => property.value ? `${property.value.title} | Gram Bangla Real Estate Limited` : 'প্রপার্টি | Gram Bangla Real Estate Limited',
  description: () => metaDescription.value,
  ogTitle: () => property.value?.title || '',
  ogDescription: () => metaDescription.value
})

useHead({
  link: () => [{ rel: 'canonical', href: `https://www.gbrel.com/properties/${route.params.id}` }]
})
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
// Direct call link: the listing's agent unless their contact is hidden, otherwise the office number.
const directPhone = computed(() => String((!property.value?.hideAgentContact && agent.value?.phone) || settings.value.contact_phone || ''))
const agentTel = computed(() => telHref(directPhone.value))
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
onBeforeUnmount(() => { startObserver?.disconnect(); ctaObserver?.disconnect(); bottomObserver?.disconnect() })
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
// The sidebar stays on screen while reading (price + button first, then the first-question box once the inline
// one has scrolled away) and steps aside when the closing CTA at the bottom scrolls into view.
// On phones the sidebar is not sticky (it sits in the page flow), so it only steps aside on desktop.
const isDesktop = ref(false)
let desktopQuery: MediaQueryList | null = null
const syncDesktop = () => { isDesktop.value = !!desktopQuery?.matches }
onMounted(() => { desktopQuery = window.matchMedia('(min-width:768px)'); syncDesktop(); desktopQuery.addEventListener('change', syncDesktop) })
onBeforeUnmount(() => desktopQuery?.removeEventListener('change', syncDesktop))
const bottomCta = ref<HTMLElement | null>(null)
const bottomCtaVisible = ref(false)
let bottomObserver: IntersectionObserver | null = null
watch(bottomCta, el => {
  bottomObserver?.disconnect()
  if (!el || typeof IntersectionObserver === 'undefined') return
  bottomObserver = new IntersectionObserver(([entry]) => { bottomCtaVisible.value = entry.isIntersecting }, { rootMargin: '0px 0px -15% 0px' })
  bottomObserver.observe(el)
})
// "খাজনা পরিশোধিত" alone would read as up to date even for an old year, so the fact names the year it covers.
// Free text without a year (e.g. "হালনাগাদ") stays in the details table only.
const taxPaidFact = (value: unknown) => {
  const year = /([0-9০-৯]{4})/.exec(String(value || ''))?.[1]
  return year ? `খাজনা পরিশোধ ${toBn(year)} সাল পর্যন্ত` : ''
}
// Up to three facts from the seller's details, shown next to the price. Only what was actually declared.
const trustFacts = computed(() => {
  const d: Record<string, any> = property.value?.buyerDetails || {}
  const facts = [
    d.bankLoan === 'None declared' && 'কোনো ব্যাংক ঋণ নেই',
    d.mutationStatus === 'Available' && 'নামজারি সম্পন্ন',
    d.possession === 'Owner' && 'মালিকের দখলে',
    d.existingAgreement === 'None declared' && 'অন্য কারও সঙ্গে বায়না নেই',
    taxPaidFact(d.taxPaidThrough),
    d.negotiable === 'Yes' && 'দাম আলোচনা সাপেক্ষ'
  ].filter(Boolean) as string[]
  return facts.slice(0, 3)
})
const selectedVariant = ref<FlatVariant | null>(null)
const flatVariants = computed<FlatVariant[]>(() => getFlatVariants(property.value))
const variantsSummary = computed(() => flatVariantsSummary(flatVariants.value))
const onVariantSelect = (v: FlatVariant) => { selectedVariant.value = v }

const areaGlanceText = computed(() => {
  if (variantsSummary.value?.minSqft && variantsSummary.value?.maxSqft) {
    if (variantsSummary.value.minSqft === variantsSummary.value.maxSqft) {
      return `${toBn(variantsSummary.value.minSqft)} বর্গফুট`
    }
    return `${toBn(variantsSummary.value.minSqft)} – ${toBn(variantsSummary.value.maxSqft)} বর্গফুট`
  }
  return areaLabel(property.value?.squareFootage, property.value?.landSize, property.value?.landUnit) || 'জেনে নিন'
})

const waLink = computed(() => {
  const p = property.value
  if (!p) return ''
  const variantNote = selectedVariant.value ? ` (${selectedVariant.value.name})` : ''
  return whatsappLink(leadWhatsapp.value, `আসসালামু আলাইকুম, "${p.title}" (GBR-${p.id})${variantNote} নিয়ে জানতে চাই।`)
})
const openInquiry = (source: string, purpose = '') => { if (!inquiryOpen.value) track('SurveyOpen', { cta: source }); inquirySource.value = source; if (purpose) startPurpose.value = purpose; inquiryOpen.value = true }
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
  activePhotoIndex.value = 0
  const result = await fetchPropertyById(String(route.params.id), { strict: true })
  if (version !== loadVersion) return
  property.value = result; loading.value = false
  if (result) {
    if (result.coverMedia === 'video' && video.value) {
      activeMediaTab.value = 'video'
      startHeroAutoplay()
    } else {
      activeMediaTab.value = 'photos'
    }
    track('ViewContent')
  }
  if (!agent.value) { await fetchAgents() }
}
watch(() => route.params.id, loadProperty, { immediate: true })
useSeoMeta({ title: () => property.value ? `${property.value.title} | GBREL` : 'Property | GBREL', description: () => metaDescription.value, ogDescription: () => metaDescription.value, ogTitle: () => property.value?.title || 'GBREL', ogImage: () => absoluteImage(images.value[0] || videoPoster.value) })
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
.property-topline nav a { color:#477465; }
.property-tools { display:flex; gap:6px; }
.property-tools button { display:flex; align-items:center; justify-content:center; gap:7px; border:1px solid #dce4dc; background:#fff; border-radius:9px; padding:10px 12px; min-height:44px; color:#455e52; font-size:12px; }
.property-tools button[aria-pressed=true] { color:#126448; background:#e3f0e5; }

/* 2-Column Main Layout: Left = Title, Showcase, Thumbs, Specs, Info (shared width) | Right = Sticky Asking Price & Advisor */
.property-main-layout { display:grid; grid-template-columns:minmax(0,1fr) 330px; gap:36px; align-items:start; }
.property-primary-col { min-width:0; width:100%; display:flex; flex-direction:column; }
.property-sidebar-col { position:sticky; top:96px; display:flex; flex-direction:column; gap:16px; transition:opacity .25s ease, transform .25s ease; }
@media (min-width:768px) { .property-sidebar-col.is-resting { opacity:0; transform:translateY(-8px); pointer-events:none; } }
@media (prefers-reduced-motion:reduce) { .property-sidebar-col { transition:none; } }

/* Title & Header Block */
.property-heading-block { width:100%; margin-bottom:18px; }
.property-badges { display:flex; flex-wrap:wrap; gap:7px; margin-bottom:12px; }
.property-badges span { background:#e8efe7; color:#416653; font-size:11px; padding:5px 10px; border-radius:5px; font-weight:600; }
.property-heading-block h1 { font-family:'Outfit','Noto Sans Bengali',sans-serif; font-size:clamp(24px,2.6vw,38px); line-height:1.25; letter-spacing:-.5px; font-weight:700; color:#173c34; margin-bottom:12px; overflow-wrap:anywhere; }
.property-location { display:flex; align-items:flex-start; gap:7px; font-size:13.5px; line-height:1.5; color:#647269; }
.property-location svg { flex-shrink:0; margin-top:2px; color:#1D4A2A; }

/* Main Media Showcase (Shares exact width with Title & Specs) */
.property-media-showcase { position:relative; width:100%; aspect-ratio:16/10; max-height:480px; border-radius:16px; overflow:hidden; background:#0e1813; box-shadow:0 8px 24px rgba(22,60,52,.07); }
.showcase-photo-wrapper { position:relative; width:100%; height:100%; cursor:pointer; }
.showcase-img { display:block; width:100%; height:100%; object-fit:cover; transition:transform .35s ease; }
.showcase-photo-wrapper:hover .showcase-img { transform:scale(1.02); }
.showcase-badge { position:absolute; top:14px; left:14px; z-index:2; display:inline-flex; align-items:center; gap:6px; padding:6px 14px; border-radius:999px; background:rgba(14,42,30,.82); backdrop-filter:blur(8px); -webkit-backdrop-filter:blur(8px); border:1px solid rgba(255,255,255,.2); color:#fff; font-size:11.5px; font-weight:600; pointer-events:none; }
.showcase-expand-btn { position:absolute; bottom:14px; left:14px; z-index:2; display:inline-flex; align-items:center; gap:7px; padding:8px 16px; border-radius:999px; background:rgba(255,255,255,.94); backdrop-filter:blur(8px); -webkit-backdrop-filter:blur(8px); border:1px solid rgba(255,255,255,.8); color:#173c34; font-size:12.5px; font-weight:700; box-shadow:0 4px 14px rgba(0,0,0,.2); cursor:pointer; transition:transform .15s, background .15s; }
.showcase-expand-btn:hover { background:#fff; transform:scale(1.03); }
.showcase-arrow { position:absolute; top:50%; transform:translateY(-50%); z-index:3; width:44px; height:44px; border-radius:50%; background:rgba(255,255,255,.92); backdrop-filter:blur(8px); -webkit-backdrop-filter:blur(8px); border:1px solid rgba(255,255,255,.8); color:#1D4A2A; display:grid; place-items:center; box-shadow:0 4px 16px rgba(0,0,0,.2); cursor:pointer; transition:transform .15s, background .15s; }
.showcase-arrow:hover { background:#fff; transform:translateY(-50%) scale(1.08); }
.showcase-arrow--prev { left:14px; }
.showcase-arrow--next { right:14px; }

/* Uncropped Video Player Showcase with Ambient Blur Backdrop */
.showcase-player { position:relative; width:100%; height:100%; background:#08110d; overflow:hidden; display:flex; align-items:center; justify-content:center; }
.showcase-player-backdrop { position:absolute; inset:-20px; background-size:cover; background-position:center; filter:blur(28px) brightness(0.35); opacity:.65; transform:scale(1.1); pointer-events:none; }
.showcase-player video, .showcase-player iframe { position:relative; z-index:1; width:100%; height:100%; max-width:100%; max-height:100%; border:0; object-fit:contain !important; background:transparent; }
.player-photos-back-btn { position:absolute; top:14px; left:14px; z-index:4; display:inline-flex; align-items:center; gap:6px; padding:8px 14px; border-radius:999px; background:rgba(255,255,255,.94); color:#173c34; border:1px solid rgba(255,255,255,.8); font-size:12px; font-weight:700; box-shadow:0 4px 14px rgba(0,0,0,.25); cursor:pointer; transition:transform .15s, background .15s; }
.player-photos-back-btn:hover { background:#fff; transform:scale(1.03); }
.unmute-btn { position:absolute; left:16px; bottom:16px; z-index:4; display:inline-flex; align-items:center; gap:8px; min-height:44px; padding:0 16px; border:0; border-radius:999px; background:#E2651C; color:#fff; font:inherit; font-size:14px; font-weight:700; box-shadow:0 4px 16px rgba(0,0,0,.3); cursor:pointer; }

/* Gallery Thumbnails Strip directly at the bottom of the main image */
.gallery-thumbs-strip { display:flex; align-items:center; gap:10px; margin-top:12px; margin-bottom:20px; overflow-x:auto; padding:4px 2px; scrollbar-width:thin; scrollbar-color:#c4d4c7 transparent; width:100%; }
.gallery-thumbs-strip::-webkit-scrollbar { height:5px; }
.gallery-thumbs-strip::-webkit-scrollbar-thumb { background:#c4d4c7; border-radius:999px; }
.thumb-box { position:relative; flex:0 0 94px; height:68px; border-radius:10px; overflow:hidden; border:2px solid transparent; background:#16241a; padding:0; cursor:pointer; transition:transform .15s, border-color .15s, box-shadow .15s; }
.thumb-box img { display:block; width:100%; height:100%; object-fit:cover; }
.thumb-box:hover { transform:translateY(-2px); border-color:#8da693; }
.thumb-box.is-active { border-color:#1D4A2A; box-shadow:0 0 0 2px #fff, 0 4px 12px rgba(29,74,42,.35); transform:translateY(-2px); }
.thumb-box--video { background:#111e15; }
.thumb-box--video.is-active { border-color:#E2651C; box-shadow:0 0 0 2px #fff, 0 4px 12px rgba(226,101,28,.4); }
.thumb-play-overlay { position:absolute; inset:0; background:rgba(0,0,0,.35); display:grid; place-items:center; color:#fff; transition:background .15s; }
.thumb-box:hover .thumb-play-overlay { background:rgba(226,101,28,.5); }
.thumb-tag { position:absolute; bottom:4px; left:4px; background:#E2651C; color:#fff; font-size:10px; font-weight:700; padding:1px 6px; border-radius:4px; }
.thumb-feature-tag { position:absolute; top:4px; left:4px; background:rgba(14,42,30,.85); color:#fff; font-size:9.5px; font-weight:600; padding:1px 5px; border-radius:3px; }
.thumb-all-btn { flex:0 0 auto; display:inline-flex; align-items:center; gap:7px; height:68px; padding:0 16px; border-radius:10px; border:1.5px dashed #c4d4c7; background:#fff; color:#1D4A2A; font-size:12.5px; font-weight:700; cursor:pointer; white-space:nowrap; transition:background .15s, border-color .15s; }
.thumb-all-btn:hover { background:#eaf2e9; border-color:#1D4A2A; }

/* Specs Glance Cards (Same Width as Title & Main Showcase) */
.property-at-a-glance { display:grid; grid-template-columns:repeat(auto-fit,minmax(135px,1fr)); gap:12px; padding:0 0 22px; margin-bottom:26px; border-bottom:1px solid #dee5dc; width:100%; }
.glance-card { display:flex; align-items:center; gap:12px; padding:14px 16px; background:#fff; border:1px solid #dce5dc; border-radius:12px; box-shadow:0 2px 6px rgba(22,60,52,.03); transition:border-color .2s,box-shadow .2s; min-width:0; }
.glance-card:hover { border-color:#b7cab8; box-shadow:0 4px 12px rgba(22,60,52,.06); }
.glance-icon { color:#1D4A2A; flex-shrink:0; }
.glance-info { display:flex; flex-direction:column; min-width:0; }
.glance-label { font-size:11.5px; color:#6a786c; line-height:1.25; }
.glance-val { display:block; font-size:15px; color:#173c34; font-family:'Outfit','Noto Sans Bengali',sans-serif; font-weight:600; margin-top:3px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }

/* Asking Price Card in Sticky Sidebar */
.asking-price-card { background:#fff; border:1px solid #dce5dc; border-radius:16px; padding:22px 24px; box-shadow:0 6px 20px rgba(22,60,52,.06); display:flex; flex-direction:column; }
.asking-price-card .price-header { display:flex; flex-direction:column; gap:2px; margin-bottom:12px; padding-bottom:12px; border-bottom:1px solid #eef2ed; }
.asking-price-card .price-type { font-size:12px; color:#65736b; }
.asking-price-card .price-number { font-family:'Outfit','Noto Sans Bengali',sans-serif; font-size:28px; line-height:1.25; font-weight:700; color:#1D4A2A; }
.asking-price-card .price-subnote { font-size:11.5px; color:#647269; }

.trust-facts { list-style:none; padding:0; margin:10px 0 2px; display:flex; flex-direction:column; gap:6px; }
.trust-facts li { display:flex; align-items:center; gap:7px; font-size:13px; color:#1D4A2A; font-weight:600; }
.trust-facts svg { color:#3F7A35; flex-shrink:0; }
.trust-source { display:block; font-size:11px; color:#647269; margin-top:4px; }
.hero-cta { display:flex; flex-direction:column; gap:8px; margin-top:14px; }
.cta-sun { display:inline-flex; align-items:center; justify-content:center; min-height:54px; padding:12px 22px; border:0; border-radius:999px; background:#E2651C; color:#fff; font-family:'Anek Bangla','Noto Sans Bengali',sans-serif; font-size:18px; font-weight:700; cursor:pointer; box-shadow:0 10px 22px -12px rgba(194,83,15,.9); }
.cta-sun:hover { background:#C2530F; }
.cta-wa { display:inline-flex; align-items:center; justify-content:center; gap:8px; min-height:48px; border:1.5px solid #1FA855; border-radius:999px; color:#137A3D; font-size:14px; font-weight:600; text-decoration:none; background:#fff; }
.cta-wa:hover { background:#EAF7EF; }
.cta-note { display:block; text-align:center; font-size:11px; color:#647269; margin-top:6px; }
.trust-line { display:flex; align-items:center; justify-content:center; gap:6px; margin-top:10px; font-size:12px; font-weight:600; color:#3F7A35; text-decoration:underline; text-underline-offset:3px; }

.advisor-direct { border:1px solid #dbe4d7; border-radius:14px; background:#fff; padding:14px 18px 6px; }
.advisor-direct > span { display:block; font-size:11px; color:#71836b; margin-bottom:8px; }
.advisor-direct > div { display:flex; justify-content:space-between; }
.advisor-direct a { display:inline-flex; align-items:center; gap:5px; min-height:44px; font-size:13px; color:#386b45; }

/* Information Sections */
.property-information { min-width:0; width:100%; }
.section-nav { display:flex; overflow-x:auto; gap:22px; border-bottom:1px solid #dce4dc; }
.section-nav a { padding:15px 0; color:#456454; font-size:13px; white-space:nowrap; min-height:48px; }
.detail-section { padding:32px 0; border-bottom:1px solid #dce4dc; scroll-margin-top:100px; }
.detail-section h2 { font-size:24px; line-height:1.5; margin-bottom:16px; }
.detail-section h3 { font-size:17px; margin:25px 0 14px; }
.detail-section p { font-size:14px; color:#53665b; line-height:1.9; margin-bottom:14px; }
.description { white-space:pre-line; overflow-wrap:anywhere; }
.property-tagline { font-weight:600; }
.eyebrow { display:block; font-size:11px; color:#597954; font-weight:600; margin-bottom:9px; }
.property-specs { display:grid; grid-template-columns:1fr 1fr; gap:0 28px; margin-top:22px; }
.property-specs > div { display:flex; justify-content:space-between; gap:14px; border-bottom:1px solid #e4eae0; padding:13px 0; font-size:12px; }
.property-specs dt { color:#69786d; }
.property-specs dd { text-align:right; color:#2d4d3d; font-weight:600; overflow-wrap:anywhere; }
.amenity-list { display:grid; grid-template-columns:1fr 1fr; gap:14px; list-style:none; padding:0; }
.amenity-list li { display:flex; align-items:flex-start; gap:9px; font-size:13px; color:#53665b; }
.amenity-list svg { flex-shrink:0; margin-top:2px; color:#377e57; }
.document-list { display:flex; flex-direction:column; gap:14px; list-style:none; padding:0; margin:20px 0; }
.document-list li { display:flex; align-items:center; gap:12px; font-size:13px; }
.document-list small { display:block; color:#768575; font-size:10px; margin-top:3px; }
.land-share-note { padding:17px 20px; border-left:3px solid #b29e61; background:#f4f0e4; border-radius:0 10px 10px 0; margin:20px 0; }
.land-share-note strong { font-size:14px; }
.land-share-note p { margin:6px 0 0; font-size:13px; }
.brochure-list a { display:flex; gap:12px; align-items:center; border:1px solid #d9e3d8; padding:15px; border-radius:9px; margin-top:10px; color:#1b6346; font-size:14px; }
.brochure-list span { flex:1; }
.detail-section .muted-note { font-size:12px; color:#6f7b70; }
.faq-section details { border-bottom:1px solid #e0e7dc; padding:8px 0; }
.faq-section summary { cursor:pointer; padding:14px 0; font-size:14px; font-weight:600; min-height:48px; }
.faq-section details p { padding:0 12px 0 0; }
.text-action { display:inline-flex; align-items:center; gap:7px; background:transparent; border:0; color:#16684b; font-size:13px; padding:12px 0; min-height:44px; text-align:left; text-decoration:none; line-height:1.6; }
.primary,.secondary { display:inline-flex; align-items:center; justify-content:center; gap:12px; min-height:52px; padding:13px 22px; border-radius:10px; font-size:14px; line-height:1.6; font-weight:600; border:1px solid #165c43; background:#165c43; color:#fff; text-decoration:none; }
.primary:hover { background:#0d4531; }
.secondary { background:#fff; color:#165c43; margin-top:12px; }

/* Bottom Banner CTA */
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

/* Lightbox & Overlay */
.gallery-tools { display:none; }
.property-state { max-width:650px; margin:auto; padding:80px 24px; text-align:center; }
.property-state h1 { font-size:24px; }
.property-state p { margin:20px 0; }
.property-state a { display:block; margin-top:24px; }
.loading-block { height:180px; background:#e9efe6; border-radius:20px; margin-bottom:24px; }
.photo-overlay { position:fixed; inset:0; z-index:11000; background:#07130ef5; display:flex; flex-direction:column; justify-content:center; align-items:center; padding:60px 20px 25px; }
.photo-overlay > img { max-width:100%; max-height:calc(100dvh - 155px); object-fit:contain; }
.photo-overlay button { background:#fff2; border:1px solid #ffffff40; color:white; width:48px; height:48px; display:grid; place-items:center; border-radius:50%; cursor:pointer; }
.photo-overlay button:focus-visible { outline:3px solid #e0bc68; }
.photo-close { position:absolute; top:14px; right:18px; }
.photo-controls { display:flex; align-items:center; gap:24px; color:white; margin-top:16px; }
.photo-controls button:disabled { opacity:.3; }
.photo-video { width:min(1100px,100%); aspect-ratio:16/9; max-height:calc(100dvh - 155px); background:#000; border-radius:10px; overflow:hidden; }
.photo-video video, .photo-video iframe { width:100%; height:100%; border:0; display:block; object-fit:contain; }

.mobile-inquiry-bar { display:none; }

@media (max-width:1024px) {
  .property-main-layout { grid-template-columns:minmax(0,1fr) 280px; gap:20px; }
  .property-specs { grid-template-columns:1fr; }
  .asking-price-card { padding:18px 20px; }
  .asking-price-card .price-number { font-size:25px; }
}

@media (max-width:767px) {
  .property-page { padding:12px 0 90px; }
  .property-shell { display:flex; flex-direction:column; padding:0 14px; }
  .property-topline { display:none; }

  /* Flex Column Flow for Mobile Ordering */
  .property-main-layout { display:flex; flex-direction:column; gap:16px; }
  .property-primary-col { display:contents; }

  .property-heading-block { order: 1; margin-bottom: 12px; }
  .property-heading-block h1 { font-size: 21px; line-height: 1.28; letter-spacing: 0; margin-bottom: 6px; }
  .property-badges { margin-bottom: 8px; gap: 6px; }
  .property-badges span { font-size: 10.5px; padding: 4px 8px; }
  .property-location { font-size: 12px; line-height: 1.5; }

  .property-media-showcase { order: 2; aspect-ratio: 16/10; border-radius: 12px; }
  .gallery-tools { display:flex; position:absolute; top:12px; right:12px; gap:7px; z-index:4; }
  .gallery-tools button { width:38px; height:38px; border-radius:50%; background:rgba(255,255,255,.94); backdrop-filter:blur(8px); -webkit-backdrop-filter:blur(8px); color:#1D4A2A; display:grid; place-items:center; box-shadow:0 3px 12px rgba(0,0,0,.18); border:1px solid rgba(255,255,255,.7); transition:transform .15s, background .15s; cursor:pointer; }
  .gallery-tools button:active { transform:scale(.92); }
  .gallery-tools button[aria-pressed=true] { background:#E2651C; color:#fff; border-color:#E2651C; }

  .gallery-thumbs-strip { order: 3; margin-top: 8px; margin-bottom: 14px; -webkit-overflow-scrolling: touch; }
  .thumb-box { flex: 0 0 76px; height: 56px; border-radius: 8px; }
  .thumb-all-btn { height: 56px; padding: 0 12px; font-size: 11.5px; }

  /* Asking price & actions appear right after gallery on mobile */
  .property-sidebar-col { order: 4; position: static; top: auto; width: 100%; opacity: 1 !important; transform: none !important; pointer-events: auto !important; margin-bottom: 6px; }
  .property-sidebar-col .side-start { display: none; }
  .asking-price-card { padding: 16px 18px; border-radius: 14px; }
  .asking-price-card .price-number { font-size: 26px; }
  .hero-cta .cta-sun { width: 100%; min-height: 50px; font-size: 16px; padding: 10px 18px; }
  .hero-cta .cta-wa { width: 100%; min-height: 46px; font-size: 14px; background: #f0fbf4; border: 1.5px solid #25d366; color: #128c46; }
  .hero-cta .cta-wa:active { background: #e2f7ea; transform: scale(.99); }
  .trust-facts li { font-size: 12px; }

  /* Specs Cards */
  .property-at-a-glance { order: 5; grid-template-columns: 1fr 1fr; gap: 10px; padding-bottom: 16px; margin-bottom: 18px; }
  .glance-card { padding: 12px 14px; gap: 10px; }
  .glance-val { font-size: 14px; margin-top: 2px; }

  /* Information */
  .property-information { order: 6; }
  .section-nav { gap: 16px; }
  .section-nav a { font-size: 12px; padding: 12px 0; min-height: 44px; }
  .detail-section { padding: 22px 0; }
  .detail-section h2 { font-size: 20px; }
  .detail-section p { font-size: 13.5px; }
  .property-specs { grid-template-columns: 1fr; }
  .amenity-list { grid-template-columns: 1fr; }
  .secondary { width: 100%; border-radius: 999px; min-height: 48px; font-size: 14px; }

  .bottom-cta-card { grid-template-columns: 1fr; padding: 22px 16px; gap: 18px; border-radius: 16px; }
  .bottom-cta-actions { padding: 16px 14px; gap: 10px; }
  .bottom-cta-btn { min-height: 50px; font-size: 16px; }
  .bottom-wa-btn { min-height: 46px; font-size: 14px; }
  .property-bottom-cta { margin: 24px 0 16px; }

  .mobile-inquiry-bar { position: fixed; bottom: 0; left: 0; right: 0; z-index: 900; display: flex; align-items: center; justify-content: space-between; gap: 10px; background: rgba(255,255,255,.96); backdrop-filter: blur(14px); -webkit-backdrop-filter: blur(14px); border-top: 1px solid #dce5dc; padding: 10px 16px max(12px,env(safe-area-inset-bottom)); box-shadow: 0 -4px 24px rgba(18,54,40,.12); transform: translateY(115%); transition: transform .25s ease; }
  .mobile-inquiry-bar.shown { transform: none; }
  .mobile-inquiry-bar .bar-info { min-width: 0; flex: 1 1 auto; display: flex; flex-direction: column; justify-content: center; }
  .mobile-inquiry-bar .bar-info strong { font-size: clamp(14px,4vw,17px); font-weight: 700; color: #173c34; line-height: 1.25; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; font-family: 'Outfit','Noto Sans Bengali',sans-serif; }
  .mobile-inquiry-bar .bar-info small { font-size: 11px; color: #647568; line-height: 1.3; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
  .mobile-inquiry-bar .bar-actions { display: flex; align-items: center; gap: 8px; flex-shrink: 0; }
  .mobile-inquiry-bar .bar-wa { width: 44px; height: 44px; border-radius: 50%; display: grid; place-items: center; background: #25D366; color: #fff; flex-shrink: 0; box-shadow: 0 4px 12px rgba(37,211,102,.3); transition: transform .15s; }
  .mobile-inquiry-bar .bar-wa:active { transform: scale(.93); }
  .mobile-inquiry-bar .bar-cta-btn { min-height: 44px; padding: 8px 16px; font-size: 14px; font-weight: 700; border-radius: 999px; white-space: nowrap; max-width: 175px; overflow: hidden; text-overflow: ellipsis; flex-shrink: 0; }
}

@media (max-width:360px) {
  .property-shell { padding: 0 10px; }
  .mobile-inquiry-bar { padding: 8px 10px max(10px,env(safe-area-inset-bottom)); gap: 6px; }
  .mobile-inquiry-bar .bar-wa { width: 38px; height: 38px; }
  .mobile-inquiry-bar .bar-cta-btn { font-size: 12.5px; padding: 8px 10px; max-width: 135px; }
  .glance-card { padding: 10px; gap: 6px; }
  .glance-val { font-size: 13px; }
  .glance-label { font-size: 10.5px; }
}
@media (prefers-reduced-motion:reduce) { .mobile-inquiry-bar { transition:none; } .showcase-img { transition:none; } }
</style>

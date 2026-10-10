<template>
  <Teleport to="body">
    <transition name="bk-modal">
      <div v-if="open" class="bk-overlay gb" @click.self="close">
        <div ref="dialogRoot" class="bk-dialog" role="dialog" aria-modal="true" aria-labelledby="bk-title">
          <button type="button" class="bk-close" aria-label="বন্ধ করুন" @click="close"><X :size="22" aria-hidden="true" /></button>

          <div class="bk-head">
            <img :src="BOOK.cover" alt="" width="72" height="102" class="bk-thumb" />
            <div>
              <p class="bk-book">{{ BOOK.title }}</p>
              <ol class="bk-progress" aria-label="ধাপ">
                <li v-for="(label, i) in stepLabels" :key="label" :class="{ on: stepIndex >= i }" :aria-current="stepIndex === i ? 'step' : undefined">{{ label }}</li>
              </ol>
            </div>
          </div>

          <!-- Step 1: the question -->
          <div v-if="step === 'question'" class="bk-body">
            <h2 id="bk-title" tabindex="-1">বইটা আপনি কেন পড়তে চান?</h2>
            <p class="bk-hint">আপনার উত্তর দেখে বইয়ের কোন অধ্যায় আগে পড়বেন, তা বলে দেব।</p>
            <div class="bk-options" role="radiogroup" aria-labelledby="bk-title">
              <label v-for="option in BOOK_REASONS" :key="option.value" class="bk-option" :class="{ picked: reason === option.value }">
                <input type="radio" name="bk-reason" :value="option.value" :checked="reason === option.value" @change="choose(option.value)" />
                <span>{{ option.label }}</span>
              </label>
            </div>
          </div>

          <!-- Step 2: name and number -->
          <form v-else-if="step === 'contact'" class="bk-body" novalidate @submit.prevent="submit">
            <h2 id="bk-title" tabindex="-1">ডাউনলোড লিংক কোথায় পাঠাব?</h2>
            <p class="bk-hint">নাম আর মোবাইল নম্বর দিলেই বইটা এখানেই খুলে যাবে।</p>
            <label class="gb-field">
              <span>আপনার নাম</span>
              <input id="bk-name" v-model="form.name" class="gb-input" type="text" autocomplete="name" maxlength="100" required />
            </label>
            <label class="gb-field">
              <span>মোবাইল নম্বর (WhatsApp থাকলে ভালো)</span>
              <input id="bk-phone" v-model="form.phone" class="gb-input" type="tel" inputmode="tel" autocomplete="tel" maxlength="25" placeholder="01XXXXXXXXX" required :aria-invalid="!!phoneError" aria-describedby="bk-phone-hint" @input="phoneError = ''" />
              <small id="bk-phone-hint" :class="{ 'bk-error': phoneError }">{{ phoneError || 'বিদেশের নম্বর হলে দেশের কোডসহ লিখুন, যেমন +971…' }}</small>
            </label>
            <p v-if="error" class="bk-error" role="alert">{{ error }}</p>
            <button type="submit" class="gb-btn gb-btn--sun bk-wide" :disabled="sending">
              <Download :size="19" aria-hidden="true" />
              {{ sending ? 'লিংক তৈরি হচ্ছে…' : 'ফ্রি বইটি ডাউনলোড করুন' }}
            </button>
            <p class="bk-fine">ডাউনলোড চাপলে আপনি সম্মতি দিচ্ছেন যে গ্রাম বাংলা রিয়েল এস্টেট এই নম্বরে জমি-সংক্রান্ত তথ্য দিতে যোগাযোগ করতে পারে। <NuxtLink to="/privacy-policy" class="gb-link" target="_blank">গোপনীয়তা নীতি</NuxtLink></p>
            <button type="button" class="bk-back" @click="goTo('question')"><ChevronLeft :size="16" aria-hidden="true" /> উত্তর বদলান</button>
          </form>

          <!-- Step 3: the book, plus like and share -->
          <div v-else class="bk-body">
            <h2 id="bk-title" tabindex="-1">আপনার বই তৈরি</h2>
            <p class="bk-hint">{{ readingTip }}</p>
            <div class="bk-get">
              <a :href="downloadUrl" target="_blank" rel="noopener" class="gb-btn gb-btn--sun" @click="trackGet('open')">
                <BookOpen :size="19" aria-hidden="true" /> বইটি খুলুন
              </a>
              <a :href="`${downloadUrl}&save=1`" download class="gb-btn gb-btn--line" @click="trackGet('save')">
                <Download :size="19" aria-hidden="true" /> ফোনে সেভ করুন
              </a>
            </div>
            <p v-if="inApp" class="bk-note">ফেসবুক অ্যাপের ভেতরে সেভ না হলে ওপরের ⋮ মেনু থেকে "Open in browser" চাপুন, তারপর আবার সেভ করুন।</p>

            <div class="bk-social">
              <p class="bk-social-title">একটা ছোট্ট অনুরোধ</p>
              <p class="bk-social-text">বইটা কাজে লাগলে আমাদের পেজে লাইক দিন, আর যিনি জমি কিনতে বা বেচতে যাচ্ছেন তাঁকে পাঠিয়ে দিন।</p>
              <BookSocialActions placement="modal" />
            </div>
          </div>
        </div>
      </div>
    </transition>
  </Teleport>
</template>

<script setup lang="ts">
import { computed, nextTick, onBeforeUnmount, reactive, ref, toRef, watch } from 'vue'
import { BookOpen, ChevronLeft, Download, X } from 'lucide-vue-next'
import { BOOK, BOOK_REASONS, isInAppBrowser, rememberBookLink, savedBookLink, type BookReason } from '~/utils/book'
import { normalizePhone, validPhone } from '~/utils/propertyInquiry.mjs'
import { setPixelUserData, trackingIds, trackPixel } from '~/utils/metaPixel'
import { useApiUrl } from '~/composables/useApi'
import { useOverlayBehavior } from '~/composables/useOverlayBehavior'
import BookSocialActions from '~/components/BookSocialActions.vue'

const props = defineProps<{ open: boolean; source: string }>()
const emit = defineEmits<{ close: [] }>()

type Step = 'question' | 'contact' | 'done'
const stepLabels = ['প্রশ্ন', 'যোগাযোগ', 'বই']
const route = useRoute()
const dialogRoot = ref<HTMLElement | null>(null)
const step = ref<Step>('question')
const reason = ref<BookReason | ''>('')
const form = reactive({ name: '', phone: '' })
const sending = ref(false)
const error = ref('')
const phoneError = ref('')
const requestId = ref('')
const downloadUrl = ref('')
const inApp = ref(false)

const stepIndex = computed(() => ['question', 'contact', 'done'].indexOf(step.value))
const close = () => emit('close')
useOverlayBehavior(toRef(props, 'open'), close, dialogRoot)

const track = (event: string, extra: Record<string, unknown> = {}, eventID?: string) =>
  trackPixel(event, { content_ids: [BOOK.contentId], content_name: BOOK.title, content_category: 'ebook', cta: props.source, ...extra }, eventID)

const focusTitle = async () => {
  await nextTick()
  dialogRoot.value?.querySelector<HTMLElement>(step.value === 'contact' ? '#bk-name' : '#bk-title')?.focus()
}
const goTo = (next: Step) => { step.value = next; focusTitle() }

watch(() => props.open, isOpen => {
  if (!isOpen) return
  inApp.value = isInAppBrowser()
  const saved = savedBookLink()
  if (saved && step.value !== 'done') { downloadUrl.value = saved; step.value = 'done' }
  track('BookDownloadStart', { returning: !!saved })
  focusTitle()
})

let advanceTimer: ReturnType<typeof setTimeout> | undefined
const choose = (value: BookReason) => {
  reason.value = value
  track('BookQuestion', { answer: value })
  clearTimeout(advanceTimer)
  // A short pause so the tap visibly registers before the next step.
  advanceTimer = setTimeout(() => goTo('contact'), 220)
}

const readingTip = computed(() => ({
  buy: 'আগে অধ্যায় ৩ (ক্রেতার ১৫ ধাপের যাচাই) আর অধ্যায় ৯ (১২টি ফাঁদ) পড়ুন।',
  sell: 'আগে অধ্যায় ৫ (বিক্রেতার গাইড) পড়ুন, তারপর অধ্যায় ২।',
  business: 'আগে অধ্যায় ৮ (আয়ের ৮টি পথ) আর অধ্যায় ১১ (৯০ দিনের রোডম্যাপ) পড়ুন।',
  invest_abroad: 'আগে অধ্যায় ১০ (প্রবাসীদের জন্য) পড়ুন, তারপর অধ্যায় ৩।',
  learn: 'শুরু করুন অধ্যায় ২ দিয়ে: জমির কাগজের ভাষা।'
} as Record<string, string>)[reason.value] || 'পরিশিষ্টের চেকলিস্টগুলো প্রিন্ট করে সঙ্গে রাখুন।')

const submit = async () => {
  if (sending.value) return
  error.value = ''
  if (!form.name.trim()) { error.value = 'আপনার নাম লিখুন।'; dialogRoot.value?.querySelector<HTMLElement>('#bk-name')?.focus(); return }
  if (!validPhone(form.phone)) {
    phoneError.value = 'সঠিক মোবাইল নম্বর দিন, যেমন 01712345678। বিদেশের নম্বরে দেশের কোড (+…) দিন।'
    dialogRoot.value?.querySelector<HTMLElement>('#bk-phone')?.focus()
    return
  }
  if (!reason.value) { goTo('question'); return }

  sending.value = true
  requestId.value ||= crypto.randomUUID()
  const q = (key: string) => String(route.query[key] || '').slice(0, 200) || null
  const phone = normalizePhone(form.phone)
  const message = [
    `Book: ${BOOK.title}`,
    `Reason: ${BOOK_REASONS.find(r => r.value === reason.value)?.label}`,
    `CTA: ${props.source}`, `Landing page: ${route.path}`,
    ...(q('fbclid') ? ['Came from a Facebook ad click'] : []),
    ...(q('utm_content') ? [`Ad content: ${q('utm_content')}`] : []),
    ...(q('utm_term') ? [`Ad term: ${q('utm_term')}`] : [])
  ].join('\n')

  let status = 0
  try {
    const res = await fetch(useApiUrl('/book-leads'), {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
      body: JSON.stringify({
        name: form.name.trim(), phone, reason: reason.value, preferred_contact: 'WhatsApp',
        contact_consent: true, request_id: requestId.value, message,
        utm_source: q('utm_source'), utm_medium: q('utm_medium'), utm_campaign: q('utm_campaign'),
        utm_content: q('utm_content'), utm_term: q('utm_term'),
        ...trackingIds()
      })
    })
    status = res.status
    const result = await res.json().catch(() => null)
    if (res.status === 422) {
      if (result?.errors?.phone) { phoneError.value = 'এই নম্বরটি ঠিক নেই। আবার দেখে লিখুন।'; return }
      throw new Error('validation')
    }
    if (!res.ok || !result?.data?.download_url) throw new Error('not saved')

    downloadUrl.value = result.data.download_url
    rememberBookLink(downloadUrl.value)
    // Same event id as the stored request, so the server-side (Conversions API) copy is counted once.
    setPixelUserData(phone, form.name)
    track('Lead', { lead_type: 'Book Download', reason: reason.value }, requestId.value)
    goTo('done')
  } catch {
    error.value = status === 429 ? 'অনেকবার চেষ্টা হয়েছে। এক মিনিট পরে আবার চাপুন।' : 'লিংক তৈরি করা যায়নি। ইন্টারনেট দেখে আবার চাপুন; আপনার লেখা রাখা আছে।'
  } finally {
    sending.value = false
  }
}
const trackGet = (method: 'open' | 'save') => track('BookDownload', { method })

onBeforeUnmount(() => clearTimeout(advanceTimer))
</script>

<style scoped>
.bk-overlay {
  position: fixed; inset: 0; z-index: 1000; display: flex; align-items: flex-end; justify-content: center;
  background: rgba(19, 53, 32, .62);
}
@media (min-width: 640px) { .bk-overlay { align-items: center; padding: 24px; } }
.bk-dialog {
  position: relative; width: 100%; max-width: 480px; max-height: 92vh; overflow-y: auto;
  background: var(--gb-sheet); border-radius: var(--gb-r-lg) var(--gb-r-lg) 0 0;
  padding: 22px 20px calc(22px + env(safe-area-inset-bottom));
}
@media (min-width: 640px) { .bk-dialog { border-radius: var(--gb-r-lg); padding: 28px; } }
.bk-close { position: absolute; top: 12px; right: 12px; width: 40px; height: 40px; display: grid; place-items: center; border: 0; background: transparent; color: var(--gb-ink-soft); border-radius: 50%; cursor: pointer; }
.bk-close:hover { background: var(--gb-paper); }

.bk-head { display: flex; gap: 14px; align-items: center; padding-right: 36px; margin-bottom: 18px; }
.bk-thumb { width: 52px; height: auto; border-radius: 3px; box-shadow: 4px 6px 14px rgba(19, 53, 32, .25); }
.bk-book { font-family: Georgia, 'Times New Roman', serif; font-weight: 700; color: var(--gb-paddy); margin: 0 0 6px; line-height: 1.3; }
.bk-progress { display: flex; gap: 6px; list-style: none; margin: 0; padding: 0; font-size: .78rem; color: var(--gb-ink-soft); }
.bk-progress li { padding: 2px 10px; border-radius: 999px; background: var(--gb-paper); }
.bk-progress li.on { background: var(--gb-paddy); color: #fff; }

.bk-body { display: flex; flex-direction: column; gap: 14px; }
.bk-body h2 { font-size: 1.45rem; font-weight: 700; margin: 0; outline: none; }
.bk-hint { margin: -6px 0 2px; color: var(--gb-ink-soft); font-size: .95rem; }

.bk-options { display: flex; flex-direction: column; gap: 8px; }
.bk-option {
  display: flex; align-items: center; gap: 12px; min-height: 52px; padding: 10px 16px;
  border: 1.5px solid var(--gb-silt); border-radius: var(--gb-r); background: #fff; cursor: pointer;
  font-weight: 500; transition: border-color .15s ease, background-color .15s ease;
}
.bk-option:hover { border-color: var(--gb-leaf); }
.bk-option input { width: 18px; height: 18px; accent-color: var(--gb-paddy); flex: none; }
.bk-option.picked { border-color: var(--gb-paddy); background: #EEF3E6; }
.bk-option:has(input:focus-visible) { outline: 3px solid var(--gb-sun); outline-offset: 2px; }

.gb-field small { color: var(--gb-ink-soft); font-size: .82rem; }
.bk-error { color: #B42318 !important; font-size: .88rem; margin: 0; }
.bk-wide { width: 100%; }
.bk-wide:disabled { opacity: .7; cursor: progress; }
.bk-fine { font-size: .78rem; color: var(--gb-ink-soft); margin: -4px 0 0; line-height: 1.6; }
.bk-back { align-self: flex-start; display: inline-flex; align-items: center; gap: 4px; border: 0; background: none; color: var(--gb-leaf); font: inherit; font-size: .9rem; cursor: pointer; padding: 4px 0; }

.bk-get { display: grid; gap: 10px; }
.bk-get .gb-btn { width: 100%; }
.bk-note { font-size: .85rem; background: #FFF6E8; border-left: 3px solid var(--gb-sun); padding: 8px 12px; margin: 0; border-radius: 0 var(--gb-r-sm) var(--gb-r-sm) 0; }

.bk-social { margin-top: 6px; padding-top: 16px; border-top: 1px dashed var(--gb-silt); }
.bk-social-title { font-family: var(--gb-display); font-weight: 700; color: var(--gb-paddy); margin: 0 0 2px; }
.bk-social-text { font-size: .9rem; color: var(--gb-ink-soft); margin: 0 0 12px; }

.bk-modal-enter-active, .bk-modal-leave-active { transition: opacity .2s ease; }
.bk-modal-enter-active .bk-dialog, .bk-modal-leave-active .bk-dialog { transition: transform .25s cubic-bezier(.16, 1, .3, 1); }
.bk-modal-enter-from, .bk-modal-leave-to { opacity: 0; }
.bk-modal-enter-from .bk-dialog, .bk-modal-leave-to .bk-dialog { transform: translateY(24px); }
</style>

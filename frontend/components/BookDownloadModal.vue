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
              <p class="bk-price"><s>৳{{ bnMoney(BOOK.listPrice) }}</s> <strong>৳{{ bnMoney(BOOK.price) }}</strong> <span>{{ toBn(BOOK_DISCOUNT_PERCENT) }}% ছাড়</span></p>
            </div>
          </div>

          <!-- Paid before on this phone: straight to the book -->
          <div v-if="savedOrder" class="bk-body">
            <h2 id="bk-title" tabindex="-1">বইটা আপনি আগেই কিনেছেন</h2>
            <p class="bk-hint">এই ফোন থেকে আগেই কেনা হয়েছে। নিচে চাপলেই বইটা খুলবে।</p>
            <NuxtLink :to="`/book-thanks?order=${savedOrder}`" class="gb-btn gb-btn--sun bk-wide" @click="close">
              <BookOpen :size="19" aria-hidden="true" /> আমার বই খুলুন
            </NuxtLink>
            <button type="button" class="bk-back" @click="savedOrder = ''">আরেকজনের জন্য কিনতে চাই</button>
          </div>

          <form v-else class="bk-body" novalidate @submit.prevent="submit">
            <h2 id="bk-title" tabindex="-1">বইটি আপনার করে নিন</h2>
            <p class="bk-hint">নাম আর মোবাইল নম্বর দিন। তারপর বিকাশ/নগদ/রকেটে ৳{{ bnMoney(BOOK.price) }} পাঠালেই বই খুলে যাবে।</p>
            <label class="gb-field">
              <span>আপনার নাম</span>
              <input id="bk-name" v-model="form.name" class="gb-input" type="text" autocomplete="name" maxlength="100" required />
            </label>
            <label class="gb-field">
              <span>মোবাইল নম্বর</span>
              <input id="bk-phone" v-model="form.phone" class="gb-input" type="tel" inputmode="tel" autocomplete="tel" maxlength="25" placeholder="01XXXXXXXXX" required :aria-invalid="!!phoneError" aria-describedby="bk-phone-hint" @input="phoneError = ''" />
              <small id="bk-phone-hint" :class="{ 'bk-error': phoneError }">{{ phoneError || 'এই নম্বরেই অর্ডারের খবর পাবেন। বিদেশের নম্বরে দেশের কোড দিন (+971…)।' }}</small>
            </label>
            <fieldset class="bk-reasons">
              <legend>বইটা কেন নিচ্ছেন? <small>(ঐচ্ছিক)</small></legend>
              <button v-for="option in BOOK_REASONS" :key="option.value" type="button" class="bk-chip" :class="{ picked: reason === option.value }" :aria-pressed="reason === option.value" @click="reason = reason === option.value ? '' : option.value">
                {{ option.label }}
              </button>
            </fieldset>
            <p v-if="error" class="bk-error" role="alert">{{ error }}</p>
            <button type="submit" class="gb-btn gb-btn--sun bk-wide" :disabled="sending">
              <Lock :size="18" aria-hidden="true" />
              {{ sending ? 'পেমেন্ট পেজ খুলছে…' : `৳${bnMoney(BOOK.price)} পেমেন্ট করুন` }}
            </button>
            <p class="bk-pay">বিকাশ · নগদ · রকেট &nbsp;|&nbsp; নিরাপদ পেমেন্ট EasyPay-এর মাধ্যমে</p>
            <p class="bk-fine">চাপ দিলে আপনি সম্মতি দিচ্ছেন যে গ্রাম বাংলা রিয়েল এস্টেট এই নম্বরে অর্ডার ও জমি-সংক্রান্ত তথ্য দিতে যোগাযোগ করতে পারে। <NuxtLink to="/privacy-policy" class="gb-link" target="_blank">গোপনীয়তা নীতি</NuxtLink></p>
          </form>
        </div>
      </div>
    </transition>
  </Teleport>
</template>

<script setup lang="ts">
// Order form for the paid e-book: saves the buyer, then sends them to EasyPay's checkout page.
// After paying, EasyPay returns them to /book-thanks, which unlocks the download.
import { nextTick, reactive, ref, toRef, watch } from 'vue'
import { BookOpen, Lock, X } from 'lucide-vue-next'
import { BOOK, BOOK_DISCOUNT_PERCENT, BOOK_REASONS, bnMoney, rememberBookOrder, savedBookOrder, type BookReason } from '~/utils/book'
import { toBn } from '~/utils/propertyLabels'
import { normalizePhone, validPhone } from '~/utils/propertyInquiry.mjs'
import { setPixelUserData, trackingIds, trackPixel } from '~/utils/metaPixel'
import { useApiUrl } from '~/composables/useApi'
import { useOverlayBehavior } from '~/composables/useOverlayBehavior'

const props = defineProps<{ open: boolean; source: string }>()
const emit = defineEmits<{ close: [] }>()

const route = useRoute()
const dialogRoot = ref<HTMLElement | null>(null)
const reason = ref<BookReason | ''>('')
const form = reactive({ name: '', phone: '' })
const sending = ref(false)
const error = ref('')
const phoneError = ref('')
const requestId = ref('')
const savedOrder = ref('')

const close = () => emit('close')
useOverlayBehavior(toRef(props, 'open'), close, dialogRoot)

const track = (event: string, extra: Record<string, unknown> = {}, eventID?: string) =>
  trackPixel(event, { content_ids: [BOOK.contentId], content_name: BOOK.title, content_type: 'product', value: BOOK.price, currency: 'BDT', cta: props.source, ...extra }, eventID)

watch(() => props.open, async isOpen => {
  if (!isOpen) return
  savedOrder.value = savedBookOrder()
  track('InitiateCheckout', { returning: !!savedOrder.value })
  await nextTick()
  dialogRoot.value?.querySelector<HTMLElement>(savedOrder.value ? '#bk-title' : '#bk-name')?.focus()
})

const submit = async () => {
  if (sending.value) return
  error.value = ''
  if (!form.name.trim()) { error.value = 'আপনার নাম লিখুন।'; dialogRoot.value?.querySelector<HTMLElement>('#bk-name')?.focus(); return }
  if (!validPhone(form.phone)) {
    phoneError.value = 'সঠিক মোবাইল নম্বর দিন, যেমন 01712345678। বিদেশের নম্বরে দেশের কোড (+…) দিন।'
    dialogRoot.value?.querySelector<HTMLElement>('#bk-phone')?.focus()
    return
  }

  sending.value = true
  requestId.value ||= crypto.randomUUID()
  const q = (key: string) => String(route.query[key] || '').slice(0, 200) || null
  const phone = normalizePhone(form.phone)
  const message = [
    `Book order: ${BOOK.title} (${BOOK.price} BDT)`,
    `CTA: ${props.source}`, `Landing page: ${route.path}`,
    ...(q('fbclid') ? ['Came from a Facebook ad click'] : [])
  ].join('\n')

  let status = 0
  try {
    const res = await fetch(useApiUrl('/book-orders'), {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
      body: JSON.stringify({
        name: form.name.trim(), phone, reason: reason.value || null, preferred_contact: 'WhatsApp',
        contact_consent: true, request_id: requestId.value, message,
        utm_source: q('utm_source'), utm_medium: q('utm_medium'), utm_campaign: q('utm_campaign'),
        utm_content: q('utm_content'), utm_term: q('utm_term'),
        ...trackingIds()
      })
    })
    status = res.status
    const result = await res.json().catch(() => null)
    if (res.status === 422 && result?.errors?.phone) { phoneError.value = 'এই নম্বরটি ঠিক নেই। আবার দেখে লিখুন।'; return }
    const data = result?.data
    if (!res.ok || !data?.order) throw new Error('not saved')

    // Same event id as the stored lead, so the server-side (Conversions API) copy is counted once.
    setPixelUserData(phone, form.name)
    track('Lead', { lead_type: 'Book Order', reason: reason.value || 'none' }, requestId.value)

    if (data.status === 'paid') {
      rememberBookOrder(data.order, true)
      await navigateTo(`/book-thanks?order=${data.order}`)
      return
    }
    rememberBookOrder(data.order, false)
    window.location.href = data.checkout_url
  } catch {
    error.value = status === 503
      ? 'অনলাইন পেমেন্ট এই মুহূর্তে বন্ধ। আমাদের ফেসবুক পেজে মেসেজ দিন, আমরা বইটা পাঠিয়ে দেব।'
      : status === 429
        ? 'অনেকবার চেষ্টা হয়েছে। এক মিনিট পরে আবার চাপুন।'
        : 'পেমেন্ট পেজ খোলা যায়নি। ইন্টারনেট দেখে আবার চাপুন; আপনার লেখা রাখা আছে।'
  } finally {
    sending.value = false
  }
}
</script>

<style scoped>
.bk-overlay {
  position: fixed; inset: 0; z-index: 1000; display: flex; align-items: flex-end; justify-content: center;
  background: rgba(19, 53, 32, .62);
}
@media (min-width: 640px) { .bk-overlay { align-items: center; padding: 24px; } }
.bk-dialog {
  position: relative; width: 100%; max-width: 460px; max-height: 92vh; overflow-y: auto;
  background: var(--gb-sheet); border-radius: var(--gb-r-lg) var(--gb-r-lg) 0 0;
  padding: 22px 20px calc(22px + env(safe-area-inset-bottom));
}
@media (min-width: 640px) { .bk-dialog { border-radius: var(--gb-r-lg); padding: 28px; } }
.bk-close { position: absolute; top: 12px; right: 12px; width: 40px; height: 40px; display: grid; place-items: center; border: 0; background: transparent; color: var(--gb-ink-soft); border-radius: 50%; cursor: pointer; }
.bk-close:hover { background: var(--gb-paper); }

.bk-head { display: flex; gap: 14px; align-items: center; padding-right: 36px; margin-bottom: 16px; }
.bk-thumb { width: 52px; height: auto; border-radius: 3px; box-shadow: 4px 6px 14px rgba(19, 53, 32, .25); }
.bk-book { font-family: Georgia, 'Times New Roman', serif; font-weight: 700; color: var(--gb-paddy); margin: 0 0 4px; line-height: 1.3; }
.bk-price { margin: 0; display: flex; align-items: baseline; gap: 8px; flex-wrap: wrap; }
.bk-price s { color: var(--gb-ink-soft); }
.bk-price strong { font-family: var(--gb-display); font-size: 1.4rem; color: var(--gb-paddy); }
.bk-price span { font-size: .78rem; font-weight: 700; color: #8A3B12; background: #FCE9D9; padding: 2px 8px; border-radius: 999px; }

.bk-body { display: flex; flex-direction: column; gap: 14px; }
.bk-body h2 { font-size: 1.4rem; font-weight: 700; margin: 0; outline: none; }
.bk-hint { margin: -6px 0 2px; color: var(--gb-ink-soft); font-size: .95rem; }

.bk-reasons { border: 0; margin: 0; padding: 0; display: flex; flex-wrap: wrap; gap: 8px; }
.bk-reasons legend { font-weight: 600; font-size: .92rem; margin-bottom: 8px; padding: 0; }
.bk-reasons legend small { font-weight: 400; color: var(--gb-ink-soft); }
.bk-chip { min-height: 40px; padding: 6px 14px; border: 1.5px solid var(--gb-silt); border-radius: 999px; background: #fff; font: inherit; font-size: .9rem; cursor: pointer; }
.bk-chip:hover { border-color: var(--gb-leaf); }
.bk-chip.picked { border-color: var(--gb-paddy); background: #EEF3E6; color: var(--gb-paddy); font-weight: 600; }
.bk-chip:focus-visible { outline: 3px solid var(--gb-sun); outline-offset: 2px; }

.gb-field small { color: var(--gb-ink-soft); font-size: .82rem; }
.bk-error { color: #B42318 !important; font-size: .88rem; margin: 0; }
.bk-wide { width: 100%; min-height: 54px; font-size: 1.08rem; }
.bk-wide:disabled { opacity: .7; cursor: progress; }
.bk-pay { text-align: center; font-size: .82rem; color: var(--gb-ink-soft); margin: -4px 0 0; }
.bk-fine { font-size: .76rem; color: var(--gb-ink-soft); margin: 0; line-height: 1.6; }
.bk-back { align-self: flex-start; border: 0; background: none; color: var(--gb-leaf); font: inherit; font-size: .9rem; cursor: pointer; padding: 4px 0; text-decoration: underline; }

.bk-modal-enter-active, .bk-modal-leave-active { transition: opacity .2s ease; }
.bk-modal-enter-active .bk-dialog, .bk-modal-leave-active .bk-dialog { transition: transform .25s cubic-bezier(.16, 1, .3, 1); }
.bk-modal-enter-from, .bk-modal-leave-to { opacity: 0; }
.bk-modal-enter-from .bk-dialog, .bk-modal-leave-to .bk-dialog { transform: translateY(24px); }
</style>

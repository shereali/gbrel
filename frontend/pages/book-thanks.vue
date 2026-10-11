<template>
  <div class="bt">
    <section class="gb-section">
      <div class="gb-wrap bt-wrap">
        <img :src="BOOK.cover" :alt="`${BOOK.title} বইয়ের প্রচ্ছদ`" width="180" height="255" class="bt-cover" />

        <div class="bt-body" aria-live="polite">
          <!-- Paid -->
          <template v-if="state === 'paid'">
            <p class="bt-kicker"><CheckCircle2 :size="20" aria-hidden="true" /> পেমেন্ট সফল{{ trxId ? ` · TrxID ${trxId}` : '' }}</p>
            <h1>{{ name ? `${name}, ` : '' }}বইটা এখন আপনার</h1>
            <p class="gb-lead">শুরু করুন অধ্যায় ৩ (ক্রেতার ১৫ ধাপের যাচাই) দিয়ে। পরিশিষ্টের চেকলিস্ট প্রিন্ট করে সঙ্গে রাখুন।</p>
            <div class="bt-get">
              <a :href="downloadUrl" target="_blank" rel="noopener" class="gb-btn gb-btn--sun" @click="trackGet('open')">
                <BookOpen :size="19" aria-hidden="true" /> বইটি খুলুন
              </a>
              <a :href="`${downloadUrl}&save=1`" download class="gb-btn gb-btn--line" @click="trackGet('save')">
                <Download :size="19" aria-hidden="true" /> ফোনে সেভ করুন
              </a>
            </div>
            <p v-if="inApp" class="bt-note">ফেসবুক অ্যাপের ভেতরে সেভ না হলে ওপরের ⋮ মেনু থেকে "Open in browser" চাপুন, তারপর আবার সেভ করুন।</p>
            <p class="bt-keep">এই পেজের লিংকটা রেখে দিন। পরে যেকোনো সময় এখান থেকে আবার বই নামাতে পারবেন।</p>
            <div class="bt-social">
              <p>যিনি জমি বা ফ্ল্যাট কিনতে যাচ্ছেন, তাঁকে বইটার কথা জানান:</p>
              <BookSocialActions placement="thanks" />
            </div>
          </template>

          <!-- Waiting for EasyPay to confirm -->
          <template v-else-if="state === 'pending' || state === 'loading'">
            <p class="bt-kicker bt-kicker--wait"><Loader2 :size="20" class="bt-spin" aria-hidden="true" /> পেমেন্ট যাচাই হচ্ছে</p>
            <h1>এক মিনিট অপেক্ষা করুন</h1>
            <p class="gb-lead">বিকাশ/নগদ থেকে টাকা পৌঁছানো নিশ্চিত হলেই বইয়ের লিংক এখানে চলে আসবে। পেজটা বন্ধ করবেন না।</p>
            <template v-if="slow">
              <p class="bt-note">অনেক সময় লাগছে। টাকা পাঠিয়ে থাকলে চিন্তা করবেন না: আমাদের ফেসবুক পেজে TrxID দিয়ে মেসেজ দিন, আমরা বই পাঠিয়ে দেব।</p>
              <div class="bt-get">
                <a :href="BOOK.facebookPage" target="_blank" rel="noopener" class="gb-btn gb-btn--paddy">ফেসবুক পেজে মেসেজ দিন</a>
                <a v-if="checkoutUrl" :href="checkoutUrl" class="gb-btn gb-btn--line">পেমেন্ট পেজে ফিরে যান</a>
              </div>
            </template>
          </template>

          <!-- Problems -->
          <template v-else>
            <p class="bt-kicker bt-kicker--bad"><AlertTriangle :size="20" aria-hidden="true" /> {{ state === 'underpaid' ? 'পুরো টাকা পৌঁছায়নি' : 'অর্ডার পাওয়া যায়নি' }}</p>
            <h1>{{ state === 'underpaid' ? 'আমরা আপনার সঙ্গে যোগাযোগ করছি' : 'আবার চেষ্টা করুন' }}</h1>
            <p class="gb-lead">{{ state === 'underpaid'
              ? `বইয়ের দাম ৳${bnMoney(BOOK.price)}, কিন্তু কম টাকা পৌঁছেছে। ফেসবুক পেজে TrxID দিয়ে মেসেজ দিন।`
              : 'লিংকটা ঠিক নেই বা মেয়াদ শেষ। বইয়ের পেজ থেকে আবার অর্ডার করুন।' }}</p>
            <div class="bt-get">
              <a :href="BOOK.facebookPage" target="_blank" rel="noopener" class="gb-btn gb-btn--paddy">ফেসবুক পেজে মেসেজ দিন</a>
              <NuxtLink to="/book" class="gb-btn gb-btn--line">বইয়ের পেজে যান</NuxtLink>
            </div>
          </template>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup lang="ts">
import { onBeforeUnmount, onMounted, ref } from 'vue'
import { AlertTriangle, BookOpen, CheckCircle2, Download, Loader2 } from 'lucide-vue-next'
import BookSocialActions from '~/components/BookSocialActions.vue'
import { BOOK, bnMoney, isInAppBrowser, rememberBookOrder } from '~/utils/book'
import { trackPixel } from '~/utils/metaPixel'
import { useApiUrl } from '~/composables/useApi'

useHead({ title: 'আপনার বই | গ্রাম বাংলা রিয়েল এস্টেট', meta: [{ name: 'robots', content: 'noindex' }] })

type State = 'loading' | 'pending' | 'paid' | 'underpaid' | 'missing'
const route = useRoute()
const order = String(route.query.order || '')
const state = ref<State>('loading')
const name = ref('')
const trxId = ref('')
const downloadUrl = ref('')
const checkoutUrl = ref('')
const slow = ref(false)
const inApp = ref(false)

const POLL_MS = 4000
const SLOW_AFTER_MS = 90000
let timer: ReturnType<typeof setTimeout> | undefined
const started = Date.now()

const purchaseTracked = () => {
  try { return sessionStorage.getItem(`gbrel_book_purchase_${order}`) === '1' } catch { return false }
}
const trackPurchase = () => {
  if (purchaseTracked()) return
  // Same event id as the server's Conversions API Purchase, so Meta counts the sale once.
  trackPixel('Purchase', { content_ids: [BOOK.contentId], content_name: BOOK.title, content_type: 'product', value: BOOK.price, currency: 'BDT' }, `book-${order}`)
  try { sessionStorage.setItem(`gbrel_book_purchase_${order}`, '1') } catch { /* ignore */ }
}

const check = async () => {
  if (!/^[A-Za-z0-9]{20,40}$/.test(order)) { state.value = 'missing'; return }
  try {
    const res = await fetch(useApiUrl(`/book-orders/${order}`), { headers: { Accept: 'application/json' } })
    if (res.status === 404) { state.value = 'missing'; return }
    const data = (await res.json().catch(() => null))?.data
    if (data) {
      name.value = String(data.name || '').split(' ')[0]
      if (data.status === 'paid' && data.download_url) {
        downloadUrl.value = data.download_url
        trxId.value = data.trx_id || ''
        state.value = 'paid'
        rememberBookOrder(order, true)
        trackPurchase()
        return
      }
      if (data.status === 'underpaid' || data.status === 'failed') { state.value = data.status === 'failed' ? 'missing' : 'underpaid'; return }
      state.value = 'pending'
    }
  } catch {
    state.value = 'pending'
  }
  slow.value = Date.now() - started > SLOW_AFTER_MS
  timer = setTimeout(check, POLL_MS)
}

const trackGet = (method: 'open' | 'save') => trackPixel('BookDownload', { content_ids: [BOOK.contentId], method })

onMounted(() => {
  inApp.value = isInAppBrowser()
  check()
})
onBeforeUnmount(() => clearTimeout(timer))
</script>

<style scoped>
.bt-wrap { display: grid; gap: 28px; align-items: start; max-width: 860px; }
.bt-cover { width: 120px; height: auto; border-radius: 3px; box-shadow: 10px 14px 30px rgba(19, 53, 32, .28); }
@media (min-width: 760px) { .bt-wrap { grid-template-columns: 180px 1fr; gap: 48px; } .bt-cover { width: 180px; } }
.bt-body h1 { font-size: clamp(1.8rem, 5vw, 2.6rem); margin: 0 0 12px; line-height: 1.2; }
.bt-kicker { display: inline-flex; align-items: center; gap: 8px; font-weight: 700; color: var(--gb-leaf); margin: 0 0 10px; }
.bt-kicker--wait { color: #8A6A1C; }
.bt-kicker--bad { color: #B42318; }
.bt-spin { animation: bt-spin 1s linear infinite; }
@keyframes bt-spin { to { transform: rotate(360deg); } }
@media (prefers-reduced-motion: reduce) { .bt-spin { animation: none; } }
.bt-get { display: flex; flex-wrap: wrap; gap: 10px; margin: 20px 0 12px; }
.bt-get .gb-btn { min-height: 52px; }
@media (max-width: 520px) { .bt-get .gb-btn { width: 100%; } }
.bt-note { font-size: .9rem; background: #FFF6E8; border-left: 3px solid var(--gb-sun); padding: 10px 12px; border-radius: 0 var(--gb-r-sm) var(--gb-r-sm) 0; }
.bt-keep { font-size: .9rem; color: var(--gb-ink-soft); }
.bt-social { margin-top: 28px; padding-top: 18px; border-top: 1px dashed var(--gb-silt); max-width: 480px; }
.bt-social p { font-weight: 600; color: var(--gb-paddy); margin: 0 0 10px; }
</style>

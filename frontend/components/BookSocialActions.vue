<template>
  <div class="bs-wrap">
  <!-- Facebook's own Page plugin (Like / Follow button inside) and Share button. Shown unless the visitor chose
       "only essential cookies", because Facebook's frames set their own cookies. -->
  <div v-if="pluginsAllowed" class="bs-fb">
    <iframe :src="pagePluginSrc" class="bs-page" title="ফেসবুক পেজ: গ্রাম বাংলা রিয়েল এস্টেট" width="340" height="130" scrolling="no" frameborder="0" allow="encrypted-media" loading="lazy" />
    <div class="bs-fb-share">
      <iframe :src="sharePluginSrc" title="ফেসবুকে শেয়ার করুন" width="96" height="30" scrolling="no" frameborder="0" loading="lazy" />
      <small>ওপরের পেজে Like / Follow চাপুন, পাশে Share চাপুন</small>
    </div>
  </div>
  <div class="bs" :class="`bs--${tone}`">
    <a :href="BOOK.facebookPage" target="_blank" rel="noopener" class="bs-btn bs-like" @click="onLike">
      <ThumbsUp :size="18" aria-hidden="true" /> পেজে লাইক দিন
    </a>
    <a :href="bookShareLinks.facebook()" target="_blank" rel="noopener" class="bs-btn" @click="onShare('facebook')">
      <Share2 :size="18" aria-hidden="true" /> ফেসবুকে শেয়ার
    </a>
    <a :href="bookShareLinks.whatsapp()" target="_blank" rel="noopener" class="bs-btn" @click="onShare('whatsapp')">
      <MessageCircle :size="18" aria-hidden="true" /> WhatsApp-এ পাঠান
    </a>
    <button type="button" class="bs-btn" @click="copyOrShare">
      <component :is="copied ? Check : Link2" :size="18" aria-hidden="true" />
      <span aria-live="polite">{{ copied ? 'লিংক কপি হয়েছে' : canNativeShare ? 'অন্য অ্যাপে পাঠান' : 'লিংক কপি করুন' }}</span>
    </button>
  </div>
  </div>
</template>

<script setup lang="ts">
// Like and share for the free book. Meta retired the stand-alone Like button plugin in February 2026, but the
// Page plugin (with its Like/Follow button) and the Share button plugin still work, so those come first.
// The plain buttons below them always work (in-app browsers, blocked frames) and are the ones we can count.
import { onMounted, ref } from 'vue'
import { Check, Link2, MessageCircle, Share2, ThumbsUp } from 'lucide-vue-next'
import { BOOK, BOOK_SHARE_TEXT, bookShareLinks, bookShareUrl } from '~/utils/book'
import { trackPixel } from '~/utils/metaPixel'

const props = withDefaults(defineProps<{ placement: string; tone?: 'light' | 'dark' }>(), { tone: 'light' })

const copied = ref(false)
const canNativeShare = ref(false)
const pluginsAllowed = ref(false)
const pagePluginSrc = `https://www.facebook.com/plugins/page.php?href=${encodeURIComponent(BOOK.facebookPage)}&tabs&width=340&height=130&small_header=true&adapt_container_width=true&hide_cover=false&show_facepile=true`
const sharePluginSrc = `https://www.facebook.com/plugins/share_button.php?href=${encodeURIComponent(bookShareUrl('fb_share_button'))}&layout=button&size=large&width=96&height=30`
onMounted(() => {
  canNativeShare.value = typeof navigator !== 'undefined' && typeof navigator.share === 'function'
  try { pluginsAllowed.value = localStorage.getItem('gbrel_cookie_consent') !== 'essential' } catch { pluginsAllowed.value = true }
})

const base = () => ({ content_ids: [BOOK.contentId], content_name: BOOK.title, placement: props.placement })
const onLike = () => trackPixel('FacebookPageLikeClick', base())
const onShare = (channel: string) => trackPixel('BookShare', { ...base(), channel })

const copyOrShare = async () => {
  if (canNativeShare.value) {
    try {
      await navigator.share({ title: BOOK.title, text: BOOK_SHARE_TEXT, url: bookShareUrl('native_share') })
      onShare('native')
    } catch { /* the visitor closed the share sheet */ }
    return
  }
  try {
    await navigator.clipboard.writeText(bookShareUrl('copy_link'))
    copied.value = true
    onShare('copy')
    setTimeout(() => { copied.value = false }, 2500)
  } catch { /* clipboard blocked: nothing to do */ }
}
</script>

<style scoped>
.bs-wrap { display: grid; gap: 12px; }
.bs-fb { display: grid; gap: 8px; justify-items: start; }
.bs-page { width: 100%; max-width: 340px; height: 130px; border: 0; border-radius: var(--gb-r); overflow: hidden; background: #fff; }
.bs-fb-share { display: flex; align-items: center; gap: 10px; }
.bs-fb-share iframe { border: 0; overflow: hidden; flex: none; }
.bs-fb-share small { font-size: .8rem; color: var(--gb-ink-soft); line-height: 1.4; }
.bs-wrap:has(.bs--dark) small { color: #DCE6CF; }
.bs { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
.bs-btn {
  display: inline-flex; align-items: center; justify-content: center; gap: 8px; min-height: 46px; padding: 8px 12px;
  border-radius: var(--gb-r); border: 1.5px solid var(--gb-silt); background: #fff; color: var(--gb-paddy);
  font-family: var(--gb-display); font-weight: 600; font-size: .95rem; text-decoration: none; cursor: pointer; text-align: center; line-height: 1.3;
}
.bs-btn:hover { border-color: var(--gb-paddy); }
.bs-btn:last-child { grid-column: 1 / -1; }
.bs-like { grid-column: 1 / -1; background: #1877F2; border-color: #1877F2; color: #fff; }
.bs-like:hover { background: #0F63D0; border-color: #0F63D0; }
.bs--dark .bs-btn:not(.bs-like) { background: transparent; color: #fff; border-color: rgba(255, 255, 255, .35); }
.bs--dark .bs-btn:not(.bs-like):hover { border-color: #fff; }
</style>

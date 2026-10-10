<template>
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
</template>

<script setup lang="ts">
// Like and share for the free book. Meta retired the embeddable Like button for websites in February 2026,
// so "Like" opens the Facebook page itself (the Facebook app on phones), where the visitor taps Like/Follow.
import { onMounted, ref } from 'vue'
import { Check, Link2, MessageCircle, Share2, ThumbsUp } from 'lucide-vue-next'
import { BOOK, BOOK_SHARE_TEXT, bookShareLinks, bookShareUrl } from '~/utils/book'
import { trackPixel } from '~/utils/metaPixel'

const props = withDefaults(defineProps<{ placement: string; tone?: 'light' | 'dark' }>(), { tone: 'light' })

const copied = ref(false)
const canNativeShare = ref(false)
onMounted(() => { canNativeShare.value = typeof navigator !== 'undefined' && typeof navigator.share === 'function' })

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

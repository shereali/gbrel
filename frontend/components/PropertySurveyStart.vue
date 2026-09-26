<template>
  <section class="ss" :class="{ 'ss--side': side }" :aria-labelledby="`ss-title-${uid}`">
    <h2 :id="`ss-title-${uid}`" class="ss-title">এই প্রপার্টি নিয়ে ভাবছেন?</h2>
    <p class="ss-lead">কয়েকটি প্রশ্নের উত্তর দিন, আপনার পরিকল্পনা অনুযায়ী দাম, কিস্তি আর সাইট ভিজিটের তথ্য জানাব।</p>
    <p class="ss-q" :id="`ss-q-${uid}`">প্রথম প্রশ্ন: প্রপার্টিটি কী কাজে লাগাতে চান?</p>
    <div class="ss-options" role="group" :aria-labelledby="`ss-q-${uid}`">
      <button v-for="o in purposeOptions" :key="o.value" type="button" class="ss-option" @click="emit('choose', o.value)">
        <span>{{ o.label }}</span>
        <ChevronRight :size="20" aria-hidden="true" />
      </button>
    </div>
    <p class="ss-fine">এক মিনিটের কম লাগবে। কোনো বুকিং বা পেমেন্ট নেই।</p>
  </section>
</template>

<script setup lang="ts">
import { ChevronRight } from 'lucide-vue-next'
import { purposeOptions } from '~/utils/leadSurvey'

defineProps<{ side?: boolean }>()
const emit = defineEmits<{ choose: [purpose: string] }>()
const uid = Math.random().toString(36).slice(2, 8)
</script>

<style scoped>
.ss { position: relative; overflow: hidden; background: #1D4A2A; color: #E3EBDA; border-radius: 18px; padding: 28px 30px 26px; margin-bottom: 32px; }
.ss::after { content: ''; position: absolute; right: -40px; top: -40px; width: 130px; height: 130px; border-radius: 50%; background: #E2651C; opacity: .9; pointer-events: none; }
.ss > * { position: relative; z-index: 1; }
.ss-title { font-family: 'Anek Bangla', 'Noto Sans Bengali', sans-serif; font-size: 28px; font-weight: 700; font-stretch: 108%; line-height: 1.25; color: #fff; margin: 0 90px 8px 0; }
.ss-lead { font-size: 14px; line-height: 1.8; color: #C6D5BB; max-width: 56ch; margin: 0 0 18px; }
.ss-q { font-size: 15px; font-weight: 600; color: #fff; margin: 0 0 10px; }
.ss-options { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 10px; }
.ss-option { display: flex; align-items: center; justify-content: space-between; gap: 8px; min-height: 58px; padding: 12px 16px; border-radius: 12px; border: 1.5px solid rgba(255, 255, 255, .28); background: rgba(255, 255, 255, .06); color: #fff; font: inherit; font-size: 15px; font-weight: 500; text-align: left; line-height: 1.45; cursor: pointer; transition: background-color .15s ease, border-color .15s ease; }
.ss-option:hover { background: #fff; color: #1D4A2A; border-color: #fff; }
.ss-option:focus-visible { outline: 3px solid #E2651C; outline-offset: 3px; }
.ss-option svg { flex-shrink: 0; opacity: .8; }
.ss-fine { font-size: 12px; color: #A9BAA0; margin: 14px 0 0; }

.ss--side { margin-bottom: 0; padding: 26px 22px 22px; }
.ss--side .ss-title { font-size: 25px; margin-right: 60px; }
.ss--side .ss-options { grid-template-columns: 1fr; }
.ss--side::after { width: 100px; height: 100px; right: -34px; top: -34px; }

@media (max-width: 767px) {
  .ss { padding: 24px 18px 20px; margin-left: -4px; margin-right: -4px; }
  .ss-title { font-size: 24px; margin-right: 56px; }
  .ss::after { width: 96px; height: 96px; right: -30px; top: -30px; }
  .ss-options { grid-template-columns: 1fr; }
}
</style>

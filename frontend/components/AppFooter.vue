<template>
  <footer class="ft">
    <svg class="ft-edge" viewBox="0 0 1440 60" preserveAspectRatio="none" aria-hidden="true">
      <path d="M0 60 L0 34 C 240 8, 520 4, 760 22 S 1220 50, 1440 18 L1440 60 Z" />
    </svg>
    <div class="ft-body">
      <div class="gb-wrap ft-grid">
        <div class="ft-brand">
          <img src="/img/logo-mark.png" alt="" width="64" height="64" />
          <p class="ft-name">গ্রাম বাংলা রিয়েল এস্টেট লিমিটেড</p>
          <p class="ft-about">জমি, প্লট, জমি শেয়ার ও ফ্ল্যাট কেনাবেচায় সহায়তা। প্রতিটি প্রপার্টির দাম, কাগজপত্র ও লোকেশন নিয়ে খোলামেলা আলোচনা করি।</p>
        </div>

        <nav aria-label="প্রপার্টির ধরন">
          <h2 class="ft-h">প্রপার্টি খুঁজুন</h2>
          <ul>
            <li><NuxtLink to="/properties?type=Plot">জমি ও প্লট</NuxtLink></li>
            <li><NuxtLink to="/properties?type=Land+Share">জমি শেয়ার</NuxtLink></li>
            <li><NuxtLink to="/properties?type=Flat">ফ্ল্যাট</NuxtLink></li>
            <li><NuxtLink to="/properties?type=Duplex">ডুপ্লেক্স</NuxtLink></li>
            <li><NuxtLink to="/properties?type=Commercial">বাণিজ্যিক</NuxtLink></li>
            <li><NuxtLink to="/properties">সব প্রপার্টি</NuxtLink></li>
          </ul>
        </nav>

        <nav aria-label="প্রতিষ্ঠান">
          <h2 class="ft-h">প্রতিষ্ঠান</h2>
          <ul>
            <li><NuxtLink to="/directors">পরিচালনা পর্ষদ</NuxtLink></li>
            <li><NuxtLink to="/agents">আমাদের এজেন্ট</NuxtLink></li>
            <li><NuxtLink to="/list-property">প্রপার্টি বিক্রি করুন</NuxtLink></li>
            <li><NuxtLink to="/compare">প্রপার্টি তুলনা</NuxtLink></li>
          </ul>
        </nav>

        <div class="ft-contact">
          <h2 class="ft-h">যোগাযোগ</h2>
          <a v-if="settings.contact_phone" :href="`tel:${tel(settings.contact_phone)}`" class="ft-phone">{{ settings.contact_phone }}</a>
          <a v-if="whatsapp" :href="`https://wa.me/${whatsapp}`" target="_blank" rel="noopener noreferrer">WhatsApp-এ লিখুন</a>
          <a v-if="settings.contact_email" :href="`mailto:${settings.contact_email}`">{{ settings.contact_email }}</a>
          <p v-if="settings.office_address">{{ settings.office_address }}</p>
          <p v-if="settings.working_hours" class="ft-hours">{{ settings.working_hours }}</p>
        </div>
      </div>

      <div class="gb-wrap"><div class="ft-base">
        <span>© {{ year }} গ্রাম বাংলা রিয়েল এস্টেট লিমিটেড (GBREL)</span>
        <span>তালিকায় দেওয়া তথ্য কেনার আগে মূল কাগজপত্রের সঙ্গে মিলিয়ে নিন।</span>
      </div></div>
    </div>
  </footer>
</template>

<script setup lang="ts">
import { computed, onMounted } from 'vue'
import { useSettings } from '~/composables/useSettings'

const { settings, fetchSettings } = useSettings()
const year = new Date().getFullYear()
const tel = (v: string) => v.replace(/[^\d+]/g, '')
const whatsapp = computed(() => (settings.value.whatsapp_number || '').replace(/\D/g, ''))
onMounted(() => { fetchSettings() })
</script>

<style scoped>
.ft { margin-top: auto; color: #DCE6D2; }
.ft-edge { display: block; width: 100%; height: 48px; fill: var(--gb-paddy); margin-bottom: -1px; }
.ft-body { background: var(--gb-paddy); padding: 40px 0 28px; }
.ft-grid { display: grid; grid-template-columns: 1.4fr 1fr 1fr 1.2fr; gap: 40px; padding-bottom: 44px; }
.ft-brand img { background: var(--gb-paper); border-radius: 50%; padding: 6px; margin-bottom: 14px; }
.ft-name { font-family: var(--gb-display); font-size: 1.35rem; font-weight: 700; font-stretch: 106%; color: #fff; margin-bottom: 8px; line-height: 1.3; }
.ft-about { font-size: .95rem; color: #C3D1B8; max-width: 38ch; }
.ft-h { font-family: var(--gb-display); font-size: 1.05rem; font-weight: 600; color: var(--gb-shoot); margin-bottom: 14px; }
.ft ul { list-style: none; display: flex; flex-direction: column; gap: 8px; }
.ft a { color: #E9F0E2; }
.ft a:hover { color: #fff; text-decoration: underline; text-underline-offset: 4px; }
.ft-contact { display: flex; flex-direction: column; gap: 8px; font-size: .95rem; }
.ft-contact p { color: #C3D1B8; }
.ft-phone { font-family: var(--gb-display); font-size: 1.45rem; font-weight: 700; color: #fff !important; font-variant-numeric: tabular-nums; }
.ft-hours { font-size: .85rem; }
.ft-base { display: flex; flex-wrap: wrap; justify-content: space-between; gap: 8px 24px; border-top: 1px solid rgba(255,255,255,.14); padding-top: 22px; font-size: .85rem; color: #A9BAA0; }
@media (max-width: 900px) { .ft-grid { grid-template-columns: 1fr 1fr; } }
@media (max-width: 560px) { .ft-grid { grid-template-columns: 1fr; gap: 32px; } }
</style>

<template>
  <Transition name="cookie-fade">
    <aside
      v-if="visible"
      class="cookie-banner"
      role="region"
      aria-label="কুকি সম্মতি নোটিশ"
    >
      <div class="cookie-banner-content">
        <div class="cookie-banner-icon" aria-hidden="true">
          <Cookie :size="24" />
        </div>
        <div class="cookie-banner-text">
          <strong class="cookie-banner-title">আমরা কুকি ব্যবহার করি</strong>
          <p>
            সাইট ঠিকভাবে চালাতে এবং কোন বিজ্ঞাপন কাজ করছে বুঝতে আমরা কুকি ব্যবহার করি।
            <NuxtLink to="/cookie-policy" class="cookie-link">কুকি নীতি</NuxtLink>
          </p>
        </div>
      </div>
      <div class="cookie-banner-actions">
        <button type="button" class="cookie-btn cookie-btn-essential" @click="accept('essential')">
          শুধু প্রয়োজনীয়
        </button>
        <button type="button" class="cookie-btn cookie-btn-accept" @click="accept('all')">
          সব গ্রহণ করুন
        </button>
      </div>
    </aside>
  </Transition>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { Cookie } from 'lucide-vue-next'
import { useCookieConsent, type CookieConsentLevel } from '~/composables/useCookieConsent'

const { hasConsented, init, setConsent } = useCookieConsent()
const visible = ref(false)

onMounted(() => {
  init()
  if (!hasConsented.value) {
    setTimeout(() => {
      visible.value = true
    }, 800)
  }
})

const accept = (level: CookieConsentLevel) => {
  setConsent(level)
  visible.value = false
}
</script>

<style scoped>
.cookie-banner {
  position: fixed;
  bottom: 24px;
  right: 24px;
  max-width: 480px;
  width: calc(100vw - 32px);
  z-index: 10000;
  background: #153822;
  color: #E3EBDA;
  border: 1px solid rgba(255, 255, 255, 0.16);
  border-radius: 18px;
  padding: 20px 22px;
  box-shadow: 0 16px 40px rgba(0, 0, 0, 0.35), 0 0 0 1px rgba(226, 101, 28, 0.25);
  backdrop-filter: blur(12px);
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.cookie-banner-content {
  display: flex;
  align-items: flex-start;
  gap: 14px;
}

.cookie-banner-icon {
  width: 42px;
  height: 42px;
  border-radius: 12px;
  background: rgba(226, 101, 28, 0.18);
  color: #F7941D;
  display: grid;
  place-items: center;
  flex-shrink: 0;
}

.cookie-banner-text {
  flex: 1;
  min-width: 0;
}

.cookie-banner-title {
  display: block;
  font-family: 'Anek Bangla', 'Noto Sans Bengali', sans-serif;
  font-size: 16px;
  font-weight: 700;
  color: #fff;
  margin-bottom: 4px;
}

.cookie-banner-text p {
  font-size: 13px;
  line-height: 1.65;
  color: #C6D5BB;
  margin: 0;
}

.cookie-link {
  color: #F7A26D;
  text-decoration: underline;
  text-underline-offset: 3px;
  font-weight: 600;
  transition: color 0.15s;
}

.cookie-link:hover {
  color: #fff;
}

.cookie-banner-actions {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  gap: 10px;
}

.cookie-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-height: 40px;
  padding: 8px 18px;
  border-radius: 999px;
  font-family: 'Anek Bangla', 'Noto Sans Bengali', sans-serif;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
  white-space: nowrap;
}

.cookie-btn-essential {
  background: rgba(255, 255, 255, 0.08);
  border: 1px solid rgba(255, 255, 255, 0.22);
  color: #E3EBDA;
}

.cookie-btn-essential:hover {
  background: rgba(255, 255, 255, 0.16);
  color: #fff;
}

.cookie-btn-accept {
  background: #E2651C;
  border: 1px solid #E2651C;
  color: #fff;
  box-shadow: 0 4px 14px rgba(194, 83, 15, 0.45);
}

.cookie-btn-accept:hover {
  background: #C2530F;
  border-color: #C2530F;
  transform: translateY(-1px);
}

.cookie-fade-enter-active,
.cookie-fade-leave-active {
  transition: opacity 0.3s ease, transform 0.3s ease;
}

.cookie-fade-enter-from,
.cookie-fade-leave-to {
  opacity: 0;
  transform: translateY(16px);
}

@media (max-width: 600px) {
  /* A slim strip: the first screen of a property page belongs to the price and the main button. */
  .cookie-banner {
    bottom: 84px; /* leaves room above mobile navigation / sticky bars */
    right: 12px;
    left: 12px;
    width: auto;
    padding: 12px 14px;
    gap: 10px;
    border-radius: 14px;
  }
  .cookie-banner-icon,
  .cookie-banner-title {
    display: none;
  }
  .cookie-banner-text p {
    font-size: 12.5px;
    line-height: 1.5;
  }
  .cookie-banner-actions {
    gap: 8px;
  }
  .cookie-btn {
    flex: 1;
    min-height: 42px;
    padding: 6px 10px;
    font-size: 13.5px;
  }
}
</style>

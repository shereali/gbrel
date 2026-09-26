<template>
  <header class="hd" :class="{ 'is-scrolled': isScrolled }">
    <a href="#main" class="hd-skip">মূল অংশে যান</a>
    <div class="gb-wrap hd-row">
      <NuxtLink to="/" class="hd-brand" aria-label="গ্রাম বাংলা রিয়েল এস্টেট লিমিটেড — হোম">
        <img src="/img/logo-wordmark.png" alt="" class="hd-logo" width="986" height="231" />
      </NuxtLink>

      <nav class="hd-nav" aria-label="প্রধান মেনু">
        <NuxtLink v-for="item in navItems" :key="item.to" :to="item.to" class="hd-link" :class="{ active: isActive(item) }">{{ item.label }}</NuxtLink>
      </nav>

      <div class="hd-actions">
        <a v-if="phone" :href="`tel:${phoneHref}`" class="hd-phone">
          <Phone :size="17" aria-hidden="true" />
          <span>{{ phone }}</span>
        </a>

        <NuxtLink to="/dashboard?tab=favorites" class="hd-icon" aria-label="সেভ করা প্রপার্টি">
          <Heart :size="19" aria-hidden="true" />
          <span v-if="savedCount" class="hd-count">{{ savedCount }}</span>
        </NuxtLink>

        <div v-if="isAuthenticated" ref="userDropdownRef" class="hd-user">
          <button class="hd-user-btn" :aria-expanded="userMenuOpen" aria-haspopup="true" @click="userMenuOpen = !userMenuOpen">
            <UserRound :size="18" aria-hidden="true" />
            <span>{{ shortName }}</span>
            <ChevronDown :size="15" aria-hidden="true" :class="{ flip: userMenuOpen }" />
          </button>
          <transition name="hd-pop">
            <div v-if="userMenuOpen" class="hd-menu">
              <div class="hd-menu-who">
                <strong>{{ user.name }}</strong>
                <span>{{ user.email }}</span>
              </div>
              <NuxtLink v-if="isOwner" to="/my-listings" @click="userMenuOpen = false">আমার প্রপার্টি</NuxtLink>
              <NuxtLink to="/dashboard" @click="userMenuOpen = false">আমার ড্যাশবোর্ড</NuxtLink>
              <NuxtLink to="/dashboard?tab=favorites" @click="userMenuOpen = false">সেভ করা প্রপার্টি ({{ savedCount }})</NuxtLink>
              <NuxtLink to="/dashboard?tab=viewings" @click="userMenuOpen = false">সাইট ভিজিটের সময়সূচি</NuxtLink>
              <NuxtLink v-if="isAdmin" to="/admin" @click="userMenuOpen = false">অ্যাডমিন প্যানেল</NuxtLink>
              <button class="hd-menu-out" @click="handleLogout">সাইন আউট</button>
            </div>
          </transition>
        </div>
        <NuxtLink v-else to="/login" class="hd-signin">সাইন ইন</NuxtLink>

        <NuxtLink to="/list-property" class="gb-btn gb-btn--sun gb-btn--sm hd-cta">প্রপার্টি বিক্রি করুন</NuxtLink>

        <button class="hd-burger" aria-label="মেনু খুলুন" @click="mobileMenuOpen = true">
          <Menu :size="24" aria-hidden="true" />
        </button>
      </div>
    </div>

    <transition name="hd-drawer">
      <div v-if="mobileMenuOpen" ref="drawerRoot" class="hd-overlay" @click.self="mobileMenuOpen = false">
        <div class="hd-panel" role="dialog" aria-modal="true" aria-label="মেনু">
          <div class="hd-panel-top">
            <img src="/img/logo-mark.png" alt="" width="40" height="40" />
            <button class="hd-close" aria-label="মেনু বন্ধ করুন" @click="mobileMenuOpen = false"><X :size="24" /></button>
          </div>
          <nav class="hd-panel-nav" aria-label="মোবাইল মেনু">
            <NuxtLink v-for="item in drawerItems" :key="item.to" :to="item.to" @click="mobileMenuOpen = false">{{ item.label }}</NuxtLink>
            <NuxtLink v-if="isOwner" to="/my-listings" @click="mobileMenuOpen = false">আমার প্রপার্টি</NuxtLink>
            <NuxtLink v-if="isAuthenticated" to="/dashboard" @click="mobileMenuOpen = false">আমার ড্যাশবোর্ড</NuxtLink>
          </nav>
          <div class="hd-panel-foot">
            <NuxtLink to="/list-property" class="gb-btn gb-btn--sun" @click="mobileMenuOpen = false">প্রপার্টি বিক্রি করুন</NuxtLink>
            <button v-if="isAuthenticated" class="gb-btn gb-btn--line" @click="handleLogout">সাইন আউট</button>
            <NuxtLink v-else to="/login" class="gb-btn gb-btn--line" @click="mobileMenuOpen = false">সাইন ইন</NuxtLink>
            <a v-if="phone" :href="`tel:${phoneHref}`" class="hd-panel-phone"><Phone :size="17" /> {{ phone }}</a>
          </div>
        </div>
      </div>
    </transition>
  </header>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { ChevronDown, Heart, Menu, Phone, UserRound, X } from 'lucide-vue-next'
import { useAuth } from '~/composables/useAuth'
import { useSettings } from '~/composables/useSettings'
import { useOverlayBehavior } from '~/composables/useOverlayBehavior'

const router = useRouter()
const route = useRoute()
const { user, isAuthenticated, isAdmin, isOwner, logout } = useAuth()
const { settings, fetchSettings } = useSettings()

const mobileMenuOpen = ref(false)
const userMenuOpen = ref(false)
const isScrolled = ref(false)
const drawerRoot = ref<HTMLElement | null>(null)
const userDropdownRef = ref<HTMLElement | null>(null)

useOverlayBehavior(mobileMenuOpen, () => { mobileMenuOpen.value = false }, drawerRoot)

const navItems = [
  { to: '/properties', label: 'সব প্রপার্টি' },
  { to: '/properties?type=Plot', label: 'জমি ও প্লট', type: 'Plot' },
  { to: '/properties?type=Land+Share', label: 'জমি শেয়ার', type: 'Land Share' },
  { to: '/properties?type=Flat', label: 'ফ্ল্যাট', type: 'Flat' },
  { to: '/directors', label: 'আমাদের সম্পর্কে' },
  { to: '/contact', label: 'যোগাযোগ' }
]
const drawerItems = [{ to: '/', label: 'হোম' }, ...navItems, { to: '/agents', label: 'আমাদের এজেন্ট' }, { to: '/compare', label: 'প্রপার্টি তুলনা' }]

const isActive = (item: { to: string; type?: string }) => {
  const path = item.to.split('?')[0]
  if (route.path !== path) return false
  const type = route.query.type ? String(route.query.type) : undefined
  return item.type ? type === item.type : !type
}

const phone = computed(() => settings.value.contact_phone || '')
const phoneHref = computed(() => phone.value.replace(/[^\d+]/g, ''))
const savedCount = computed(() => user.value?.savedProperties?.length || 0)
const shortName = computed(() => user.value?.name ? user.value.name.split(' ')[0] : 'অ্যাকাউন্ট')

const handleLogout = async () => {
  userMenuOpen.value = false
  mobileMenuOpen.value = false
  await logout()
  router.push('/')
}

const handleScroll = () => { isScrolled.value = window.scrollY > 12 }
const handleClickOutside = (e: MouseEvent) => {
  if (userMenuOpen.value && userDropdownRef.value && !userDropdownRef.value.contains(e.target as Node)) userMenuOpen.value = false
}

onMounted(() => {
  fetchSettings()
  handleScroll()
  window.addEventListener('scroll', handleScroll, { passive: true })
  document.addEventListener('click', handleClickOutside)
})
onUnmounted(() => {
  window.removeEventListener('scroll', handleScroll)
  document.removeEventListener('click', handleClickOutside)
})
</script>

<style scoped>
.hd { position: sticky; top: 0; z-index: 900; background: var(--gb-paper); border-bottom: 1px solid transparent; transition: border-color .2s ease, background-color .2s ease; }
.hd.is-scrolled { background: rgba(243, 245, 236, .94); backdrop-filter: blur(10px); border-bottom-color: var(--gb-silt); }
.hd-skip { position: absolute; left: 12px; top: -60px; background: var(--gb-paddy); color: #fff; padding: 8px 14px; border-radius: 6px; z-index: 10; }
.hd-skip:focus { top: 10px; }
.hd-row { display: flex; align-items: center; gap: 24px; height: 76px; }
.hd-brand { flex-shrink: 0; display: block; }
.hd-logo { height: 46px; width: auto; }

.hd-nav { display: flex; gap: 4px; margin-right: auto; }
.hd-link { font-family: var(--gb-display); font-size: 1.02rem; font-weight: 500; color: var(--gb-ink); padding: 8px 12px; border-radius: 999px; white-space: nowrap; }
.hd-link:hover { background: rgba(29, 74, 42, .07); color: var(--gb-paddy); }
.hd-link.active { color: var(--gb-paddy); font-weight: 700; background: rgba(168, 197, 123, .35); }

.hd-actions { flex-shrink: 0; display: flex; align-items: center; gap: 10px; }
.hd-phone { display: inline-flex; align-items: center; gap: 8px; font-family: var(--gb-display); font-weight: 600; color: var(--gb-paddy); padding: 8px 10px; font-variant-numeric: tabular-nums; white-space: nowrap; }
.hd-icon { position: relative; width: 42px; height: 42px; border-radius: 50%; display: grid; place-items: center; color: var(--gb-paddy); }
.hd-icon:hover { background: rgba(29, 74, 42, .07); }
.hd-count { position: absolute; top: 3px; right: 2px; min-width: 18px; height: 18px; padding: 0 4px; border-radius: 9px; background: var(--gb-sun); color: #fff; font-size: 11px; font-weight: 700; display: grid; place-items: center; line-height: 1; }
.hd-signin { white-space: nowrap; font-family: var(--gb-display); font-weight: 600; color: var(--gb-paddy); padding: 8px 12px; }
.hd-signin:hover { text-decoration: underline; text-underline-offset: 4px; }

.hd-user { position: relative; }
.hd-user-btn { display: inline-flex; align-items: center; gap: 6px; background: transparent; color: var(--gb-paddy); padding: 8px 12px; border-radius: 999px; font-family: var(--gb-display); font-weight: 600; cursor: pointer; border: 1.5px solid var(--gb-silt); }
.hd-user-btn .flip { transform: rotate(180deg); }
.hd-menu { position: absolute; right: 0; top: calc(100% + 10px); width: 260px; background: var(--gb-sheet); border: 1px solid var(--gb-silt); border-radius: var(--gb-r); padding: 8px; box-shadow: 0 18px 40px -18px rgba(22, 36, 26, .35); display: flex; flex-direction: column; }
.hd-menu-who { padding: 10px 12px 12px; border-bottom: 1px solid var(--gb-silt); margin-bottom: 6px; display: flex; flex-direction: column; }
.hd-menu-who span { font-size: .82rem; color: var(--gb-ink-soft); overflow: hidden; text-overflow: ellipsis; }
.hd-menu a { padding: 9px 12px; border-radius: 8px; color: var(--gb-ink); font-size: .95rem; }
.hd-menu a:hover { background: rgba(168, 197, 123, .28); }
.hd-menu-out { text-align: left; padding: 9px 12px; border-radius: 8px; background: transparent; color: #A23B16; cursor: pointer; font-size: .95rem; }
.hd-menu-out:hover { background: #FBE9DF; }

.hd-burger { display: none; width: 44px; height: 44px; border-radius: 50%; background: transparent; color: var(--gb-paddy); cursor: pointer; place-items: center; }

.hd-overlay { position: fixed; inset: 0; background: rgba(19, 53, 32, .45); z-index: 1000; display: flex; justify-content: flex-end; }
.hd-panel { width: min(360px, 88vw); height: 100%; background: var(--gb-paper); display: flex; flex-direction: column; padding: 18px 20px calc(24px + env(safe-area-inset-bottom)); overflow-y: auto; }
.hd-panel-top { display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px; }
.hd-close { width: 44px; height: 44px; display: grid; place-items: center; background: transparent; color: var(--gb-paddy); cursor: pointer; border-radius: 50%; }
.hd-panel-nav { display: flex; flex-direction: column; }
.hd-panel-nav a { font-family: var(--gb-display); font-size: 1.3rem; font-weight: 600; color: var(--gb-paddy); padding: 12px 2px; border-bottom: 1px solid var(--gb-silt); }
.hd-panel-foot { margin-top: auto; padding-top: 24px; display: flex; flex-direction: column; gap: 10px; }
.hd-panel-phone { display: inline-flex; align-items: center; justify-content: center; gap: 8px; color: var(--gb-paddy); font-weight: 600; padding: 10px; }

.hd-pop-enter-active, .hd-pop-leave-active { transition: opacity .15s ease, transform .15s ease; }
.hd-pop-enter-from, .hd-pop-leave-to { opacity: 0; transform: translateY(-4px); }
.hd-drawer-enter-active, .hd-drawer-leave-active { transition: opacity .2s ease; }
.hd-drawer-enter-active .hd-panel, .hd-drawer-leave-active .hd-panel { transition: transform .25s ease; }
.hd-drawer-enter-from, .hd-drawer-leave-to { opacity: 0; }
.hd-drawer-enter-from .hd-panel, .hd-drawer-leave-to .hd-panel { transform: translateX(100%); }

.hd-cta { white-space: nowrap; }
@media (max-width: 1500px) { .hd-phone { display: none; } }
@media (max-width: 1080px) {
  .hd-nav, .hd-signin, .hd-user { display: none; }
  .hd-burger { display: grid; }
  .hd-actions { margin-left: auto; }
}
@media (max-width: 560px) {
  .hd-row { height: 64px; gap: 8px; }
  .hd-logo { height: 34px; }
  .hd-cta { display: none; }
}
</style>

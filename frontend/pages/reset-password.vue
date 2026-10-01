<template>
  <div class="auth">
    <div class="gb-wrap auth-wrap">
      <section class="auth-card" aria-labelledby="reset-title">
        <p v-if="state === 'checking'" role="status">লিংকটি যাচাই করা হচ্ছে…</p>

        <template v-else-if="state === 'invalid'">
          <h1 id="reset-title">লিংকটি কাজ করছে না</h1>
          <p class="auth-lead">{{ error }}</p>
          <NuxtLink to="/login" class="gb-btn gb-btn--sun auth-submit">সাইন ইন পেজে যান</NuxtLink>
        </template>

        <template v-else-if="state === 'done'">
          <h1 id="reset-title">পাসওয়ার্ড বদলানো হয়েছে</h1>
          <p class="auth-lead">নতুন পাসওয়ার্ড দিয়ে এখন সাইন ইন করতে পারবেন।</p>
          <NuxtLink to="/login" class="gb-btn gb-btn--sun auth-submit">সাইন ইন করুন</NuxtLink>
        </template>

        <template v-else>
          <h1 id="reset-title">নতুন পাসওয়ার্ড দিন</h1>
          <p class="auth-lead">{{ name }}, আপনার অ্যাকাউন্টের জন্য নতুন পাসওয়ার্ড বেছে নিন। কমপক্ষে ৮ অক্ষর।</p>
          <form novalidate @submit.prevent="submit">
            <label class="gb-field">
              <span>নতুন পাসওয়ার্ড</span>
              <input v-model="password" class="gb-input" type="password" autocomplete="new-password" minlength="8" required />
            </label>
            <label class="gb-field">
              <span>আবার লিখুন</span>
              <input v-model="confirmation" class="gb-input" type="password" autocomplete="new-password" minlength="8" required />
            </label>
            <p v-if="error" class="auth-error" role="alert">{{ error }}</p>
            <button type="submit" class="gb-btn gb-btn--sun auth-submit" :disabled="loading">{{ loading ? 'সেভ হচ্ছে…' : 'পাসওয়ার্ড সেভ করুন' }}</button>
          </form>
        </template>
      </section>
    </div>
  </div>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { useApiUrl } from '~/composables/useApi'

const route = useRoute()
const state = ref<'checking' | 'form' | 'invalid' | 'done'>('checking')
const name = ref('')
const password = ref('')
const confirmation = ref('')
const loading = ref(false)
const error = ref('')
let token = ''

// The link carries a secret: no referrer, no search engines, and not shown to analytics (see gb-track.js, meta-pixel plugin).
useHead({ meta: [{ name: 'referrer', content: 'no-referrer' }, { name: 'robots', content: 'noindex, nofollow' }] })
useSeoMeta({ title: 'নতুন পাসওয়ার্ড | গ্রাম বাংলা রিয়েল এস্টেট' })

const post = async (path: string, body: Record<string, unknown>) => {
  const res = await fetch(useApiUrl(path), { method: 'POST', headers: { 'Content-Type': 'application/json', Accept: 'application/json' }, body: JSON.stringify(body) })
  const json = await res.json().catch(() => ({}))
  return { ok: res.ok, json }
}

onMounted(async () => {
  token = typeof route.query.token === 'string' ? route.query.token : ''
  // Keep the secret out of the address bar and browser history from here on.
  window.history.replaceState(window.history.state, '', route.path)
  const { ok, json } = await post('/auth/reset-password/check', { token })
  if (ok) {
    name.value = json?.data?.name || ''
    state.value = 'form'
  } else {
    error.value = json?.message || 'লিংকটি যাচাই করা যায়নি। আবার চেষ্টা করুন।'
    state.value = 'invalid'
  }
})

const submit = async () => {
  error.value = ''
  if (password.value.length < 8) { error.value = 'পাসওয়ার্ড কমপক্ষে ৮ অক্ষরের হতে হবে।'; return }
  if (password.value !== confirmation.value) { error.value = 'দুটি পাসওয়ার্ড মিলছে না।'; return }
  loading.value = true
  try {
    const { ok, json } = await post('/auth/reset-password', { token, password: password.value, password_confirmation: confirmation.value })
    if (ok) {
      token = ''
      state.value = 'done'
    } else if (json?.errors?.password?.[0]) {
      error.value = json.errors.password[0]
    } else {
      error.value = json?.message || 'পাসওয়ার্ড সেভ করা যায়নি। আবার চেষ্টা করুন।'
      if (/লিংক/.test(error.value)) state.value = 'invalid'
    }
  } catch {
    error.value = 'ইন্টারনেট সংযোগ পরীক্ষা করে আবার চেষ্টা করুন।'
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.auth { padding: clamp(40px, 7vw, 88px) 0 96px; }
.auth-wrap { display: flex; justify-content: center; }
.auth-card { width: 100%; max-width: 460px; background: var(--gb-sheet); border: 1px solid var(--gb-silt); border-radius: var(--gb-r-lg); padding: clamp(24px, 4vw, 36px); }
.auth-card h1 { font-size: var(--gb-t-h1); font-weight: 800; font-stretch: 110%; margin-bottom: 6px; }
.auth-lead { color: var(--gb-ink-soft); margin-bottom: 22px; }
.auth-card form { display: flex; flex-direction: column; gap: 16px; }
.auth-submit { display: flex; justify-content: center; width: 100%; margin-top: 4px; }
.auth-submit:disabled { opacity: .7; cursor: wait; }
.auth-error { color: #8A3A0F; background: #FBE9DF; border-radius: 8px; padding: 10px 12px; font-size: .92rem; }
</style>

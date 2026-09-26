<template>
  <div class="auth">
    <div class="gb-wrap auth-grid">
      <section class="auth-card" aria-labelledby="login-title">
        <h1 id="login-title">সাইন ইন করুন</h1>
        <p class="auth-lead">মোবাইল নম্বর বা ইমেইল আর পাসওয়ার্ড দিয়ে ঢুকুন।</p>

        <form novalidate @submit.prevent="handleLogin">
          <label class="gb-field">
            <span>মোবাইল নম্বর বা ইমেইল</span>
            <input v-model.trim="identifier" class="gb-input" type="text" inputmode="email" autocomplete="username" required />
          </label>
          <label class="gb-field">
            <span>পাসওয়ার্ড</span>
            <input v-model="password" class="gb-input" type="password" autocomplete="current-password" required />
          </label>
          <p v-if="error" class="auth-error" role="alert">{{ error }}</p>
          <button type="submit" class="gb-btn gb-btn--sun auth-submit" :disabled="loading">{{ loading ? 'সাইন ইন হচ্ছে…' : 'সাইন ইন' }}</button>
        </form>

        <p class="auth-switch">প্রপার্টি বিক্রি করতে চান, কিন্তু অ্যাকাউন্ট নেই? <NuxtLink :to="{ path: '/signup', query: route.query }" class="gb-link">অ্যাকাউন্ট খুলুন</NuxtLink></p>
      </section>

      <aside class="auth-aside">
        <h2>জমি বা ফ্ল্যাট বিক্রি করবেন?</h2>
        <p>অ্যাকাউন্ট থেকে প্রপার্টির তথ্য আর কাগজপত্র জমা দিন। আমাদের টিম যাচাই করে প্রকাশ করবে, ক্রেতা খুঁজবে আর বিক্রি সম্পন্ন করবে।</p>
        <NuxtLink to="/list-property" class="gb-link">কীভাবে কাজ করে দেখুন</NuxtLink>
      </aside>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useAuth } from '~/composables/useAuth'

const route = useRoute()
const router = useRouter()
const { login } = useAuth()

const identifier = ref('')
const password = ref('')
const loading = ref(false)
const error = ref('')

const handleLogin = async () => {
  error.value = ''
  if (!identifier.value || !password.value) {
    error.value = 'মোবাইল নম্বর বা ইমেইল আর পাসওয়ার্ড দুটোই লিখুন।'
    return
  }
  loading.value = true
  try {
    const user = await login(identifier.value, password.value, true)
    const redirect = typeof route.query.redirect === 'string' && route.query.redirect.startsWith('/') ? route.query.redirect : ''
    if (redirect) return router.push(redirect)
    if (user.is_staff || user.is_admin) return router.push('/admin')
    if (user.role === 'owner') return router.push('/my-listings')
    return router.push('/dashboard')
  } catch {
    error.value = 'নম্বর/ইমেইল বা পাসওয়ার্ড মেলেনি। আবার চেষ্টা করুন।'
  } finally {
    loading.value = false
  }
}

useSeoMeta({ title: 'সাইন ইন | গ্রাম বাংলা রিয়েল এস্টেট' })
</script>

<style scoped>
.auth { padding: clamp(40px, 7vw, 88px) 0 96px; }
.auth-grid { display: grid; grid-template-columns: minmax(0, 460px) minmax(0, 1fr); gap: clamp(32px, 6vw, 88px); align-items: start; }
.auth-card { background: var(--gb-sheet); border: 1px solid var(--gb-silt); border-radius: var(--gb-r-lg); padding: clamp(24px, 4vw, 36px); }
.auth-card h1 { font-size: var(--gb-t-h1); font-weight: 800; font-stretch: 110%; margin-bottom: 6px; }
.auth-lead { color: var(--gb-ink-soft); margin-bottom: 22px; }
.auth-card form { display: flex; flex-direction: column; gap: 16px; }
.auth-submit { width: 100%; margin-top: 4px; }
.auth-submit:disabled { opacity: .7; cursor: wait; }
.auth-error { color: #8A3A0F; background: #FBE9DF; border-radius: 8px; padding: 10px 12px; font-size: .92rem; }
.auth-switch { margin-top: 20px; font-size: .95rem; color: var(--gb-ink-soft); }
.auth-aside { padding-top: 12px; }
.auth-aside h2 { font-size: var(--gb-t-h2); font-weight: 700; margin-bottom: 10px; }
.auth-aside p { color: var(--gb-ink-soft); margin-bottom: 14px; }
@media (max-width: 860px) { .auth-grid { grid-template-columns: 1fr; } }
</style>

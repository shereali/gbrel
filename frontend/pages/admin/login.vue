<template>
  <div class="al">
    <main class="al-card">
      <NuxtLink to="/" class="al-logo" aria-label="Back to the website"><img src="/img/logo-wordmark.png" alt="Gram Bangla Real Estate Limited" width="986" height="231" /></NuxtLink>
      <h1>Staff sign in</h1>
      <p class="al-lead">For GBREL team members only. Property owners sign in on the <NuxtLink to="/login">main sign-in page</NuxtLink>.</p>

      <form novalidate @submit.prevent="handleAdminLogin">
        <label class="al-field">
          <span>Email or phone</span>
          <input v-model.trim="email" type="text" autocomplete="username" required />
        </label>
        <label class="al-field">
          <span>Password</span>
          <span class="al-pass">
            <input v-model="password" :type="showPassword ? 'text' : 'password'" autocomplete="current-password" required />
            <button type="button" :aria-pressed="showPassword" @click="showPassword = !showPassword">{{ showPassword ? 'Hide' : 'Show' }}</button>
          </span>
        </label>
        <p v-if="error" class="al-error" role="alert">{{ error }}</p>
        <button type="submit" class="al-submit" :disabled="loading">{{ loading ? 'Signing in…' : 'Sign in' }}</button>
      </form>
    </main>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuth } from '~/composables/useAuth'

definePageMeta({ layout: false })
useHead({ title: 'Staff sign in | GBREL', meta: [{ name: 'robots', content: 'noindex, nofollow' }] })

const router = useRouter()
const route = useRoute()
const { login, logout } = useAuth()

const email = ref('')
const password = ref('')
const showPassword = ref(false)
const loading = ref(false)
const error = ref('')

const handleAdminLogin = async () => {
  error.value = ''
  if (!email.value || !password.value) {
    error.value = 'Enter your email or phone and your password.'
    return
  }
  loading.value = true
  try {
    const user = await login(email.value, password.value, true)
    if (!user.is_staff && !user.is_admin) {
      await logout()
      error.value = 'This account is not a staff account. Property owners use the main sign-in page.'
      return
    }
    const redirect = typeof route.query.redirect === 'string' && route.query.redirect.startsWith('/admin') ? route.query.redirect : '/admin'
    router.push(redirect)
  } catch {
    error.value = 'The email/phone or password is not correct.'
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.al { min-height: 100vh; display: grid; place-items: center; padding: 24px 16px; background: #F3F5EC; font-family: 'Plus Jakarta Sans', 'Noto Sans Bengali', sans-serif; color: #16241A; }
.al-card { width: min(420px, 100%); background: #FBFCF7; border: 1px solid #D6DDCB; border-radius: 22px; padding: 32px 28px; }
.al-logo img { height: 40px; width: auto; margin-bottom: 22px; }
.al-card h1 { font-family: 'Anek Bangla', sans-serif; font-size: 1.9rem; font-weight: 800; color: #1D4A2A; }
.al-lead { color: #4A5A4E; font-size: .92rem; margin: 4px 0 20px; }
.al-lead a { color: #3F7A35; text-decoration: underline; }
form { display: flex; flex-direction: column; gap: 14px; }
.al-field { display: flex; flex-direction: column; gap: 6px; font-size: .85rem; font-weight: 600; color: #1D4A2A; }
.al-field input { min-height: 48px; padding: 10px 14px; border-radius: 10px; border: 1.5px solid #D6DDCB; background: #fff; font: inherit; font-weight: 400; font-size: 1rem; color: #16241A; width: 100%; }
.al-field input:focus { outline: none; border-color: #3F7A35; box-shadow: 0 0 0 3px rgba(63, 122, 53, .18); }
.al-pass { position: relative; display: block; }
.al-pass button { position: absolute; right: 6px; top: 50%; transform: translateY(-50%); background: none; color: #3F7A35; font-weight: 600; padding: 8px 10px; cursor: pointer; }
.al-error { color: #8A3A0F; background: #FBE9DF; border-radius: 8px; padding: 10px 12px; font-size: .9rem; }
.al-submit { min-height: 50px; border-radius: 999px; background: #1D4A2A; color: #fff; font-family: 'Anek Bangla', sans-serif; font-size: 1.1rem; font-weight: 700; cursor: pointer; }
.al-submit:hover { background: #133520; }
.al-submit:disabled { opacity: .7; cursor: wait; }
.al :focus-visible { outline: 3px solid #E2651C; outline-offset: 3px; }
</style>

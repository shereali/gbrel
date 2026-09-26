<template>
  <div class="auth">
    <div class="gb-wrap auth-grid">
      <section class="auth-card" aria-labelledby="signup-title">
        <h1 id="signup-title">প্রপার্টি মালিকের অ্যাকাউন্ট</h1>
        <p class="auth-lead">এই অ্যাকাউন্ট থেকে প্রপার্টির তথ্য ও কাগজপত্র জমা দেবেন, আর যাচাইয়ের অবস্থা দেখবেন।</p>

        <form novalidate @submit.prevent="handleSignup">
          <label class="gb-field">
            <span>আপনার নাম</span>
            <input v-model.trim="form.name" class="gb-input" type="text" autocomplete="name" required />
          </label>
          <label class="gb-field">
            <span>মোবাইল নম্বর</span>
            <input v-model.trim="form.phone" class="gb-input" type="tel" inputmode="tel" autocomplete="tel" placeholder="01XXXXXXXXX" required />
            <small>এই নম্বর দিয়েই সাইন ইন করবেন। আমাদের টিম এই নম্বরে যোগাযোগ করবে।</small>
          </label>
          <label class="gb-field">
            <span>ইমেইল <small>(ঐচ্ছিক)</small></span>
            <input v-model.trim="form.email" class="gb-input" type="email" autocomplete="email" />
          </label>
          <div class="auth-row">
            <label class="gb-field">
              <span>পাসওয়ার্ড</span>
              <input v-model="form.password" class="gb-input" type="password" autocomplete="new-password" minlength="8" required />
            </label>
            <label class="gb-field">
              <span>আবার পাসওয়ার্ড</span>
              <input v-model="form.passwordConfirmation" class="gb-input" type="password" autocomplete="new-password" required />
            </label>
          </div>
          <small class="auth-hint">পাসওয়ার্ড অন্তত ৮ অক্ষরের হতে হবে।</small>
          <p v-if="error" class="auth-error" role="alert">{{ error }}</p>
          <button type="submit" class="gb-btn gb-btn--sun auth-submit" :disabled="loading">{{ loading ? 'অ্যাকাউন্ট খোলা হচ্ছে…' : 'অ্যাকাউন্ট খুলুন' }}</button>
        </form>

        <p class="auth-switch">আগেই অ্যাকাউন্ট আছে? <NuxtLink :to="{ path: '/login', query: route.query }" class="gb-link">সাইন ইন করুন</NuxtLink></p>
      </section>

      <aside class="auth-aside">
        <h2>অ্যাকাউন্ট খোলার পর</h2>
        <ol class="auth-steps">
          <li>প্রপার্টির তথ্য, দাম আর ছবি দিন।</li>
          <li>দলিল, খতিয়ান, নামজারি, খাজনার কাগজ আপলোড করুন।</li>
          <li>শর্তে সম্মতি দিয়ে যাচাইয়ের জন্য জমা দিন।</li>
        </ol>
        <p>আপনার কাগজপত্র শুধু আমাদের যাচাই টিম দেখতে পায়, ওয়েবসাইটে কখনো প্রকাশ হয় না।</p>
      </aside>
    </div>
  </div>
</template>

<script setup lang="ts">
import { reactive, ref } from 'vue'
import { useAuth } from '~/composables/useAuth'
import { toApiPhone } from '~/utils/propertyLabels'

const route = useRoute()
const router = useRouter()
const { register } = useAuth()

const form = reactive({ name: '', phone: '', email: '', password: '', passwordConfirmation: '' })
const loading = ref(false)
const error = ref('')

const handleSignup = async () => {
  error.value = ''
  if (!form.name) { error.value = 'আপনার নাম লিখুন।'; return }
  const phone = toApiPhone(form.phone)
  if (!phone) { error.value = 'সঠিক মোবাইল নম্বর দিন, যেমন 01712345678। বিদেশের নম্বর হলে দেশের কোডসহ লিখুন।'; return }
  if (form.password.length < 8) { error.value = 'পাসওয়ার্ড অন্তত ৮ অক্ষরের হতে হবে।'; return }
  if (form.password !== form.passwordConfirmation) { error.value = 'দুটি পাসওয়ার্ড মেলেনি।'; return }

  loading.value = true
  try {
    await register({ ...form, phone })
    const redirect = typeof route.query.redirect === 'string' && route.query.redirect.startsWith('/') ? route.query.redirect : '/my-listings/new'
    router.push(redirect)
  } catch (err: any) {
    error.value = err?.message || 'অ্যাকাউন্ট খোলা যায়নি। আবার চেষ্টা করুন।'
  } finally {
    loading.value = false
  }
}

useSeoMeta({ title: 'অ্যাকাউন্ট খুলুন | গ্রাম বাংলা রিয়েল এস্টেট' })
</script>

<style scoped>
.auth { padding: clamp(40px, 7vw, 88px) 0 96px; }
.auth-grid { display: grid; grid-template-columns: minmax(0, 500px) minmax(0, 1fr); gap: clamp(32px, 6vw, 88px); align-items: start; }
.auth-card { background: var(--gb-sheet); border: 1px solid var(--gb-silt); border-radius: var(--gb-r-lg); padding: clamp(24px, 4vw, 36px); }
.auth-card h1 { font-size: clamp(1.8rem, 3.4vw, 2.6rem); font-weight: 800; font-stretch: 108%; margin-bottom: 6px; }
.auth-lead { color: var(--gb-ink-soft); margin-bottom: 22px; }
.auth-card form { display: flex; flex-direction: column; gap: 16px; }
.auth-card .gb-field small { font-size: .82rem; color: var(--gb-ink-soft); font-weight: 400; }
.auth-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
.auth-hint { font-size: .82rem; color: var(--gb-ink-soft); margin-top: -8px; }
.auth-submit { width: 100%; margin-top: 4px; }
.auth-submit:disabled { opacity: .7; cursor: wait; }
.auth-error { color: #8A3A0F; background: #FBE9DF; border-radius: 8px; padding: 10px 12px; font-size: .92rem; }
.auth-switch { margin-top: 20px; font-size: .95rem; color: var(--gb-ink-soft); }
.auth-aside { padding-top: 12px; }
.auth-aside h2 { font-size: var(--gb-t-h2); font-weight: 700; margin-bottom: 14px; }
.auth-steps { padding-left: 22px; display: flex; flex-direction: column; gap: 8px; margin-bottom: 16px; }
.auth-aside p { color: var(--gb-ink-soft); }
@media (max-width: 860px) { .auth-grid { grid-template-columns: 1fr; } }
@media (max-width: 480px) { .auth-row { grid-template-columns: 1fr; } }
</style>

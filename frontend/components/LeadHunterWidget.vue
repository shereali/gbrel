<template>
  <div>
    <div class="lw-stack">
      <button type="button" class="lw-btn lw-call" aria-label="কল ব্যাকের অনুরোধ" title="আমাকে কল করুন" @click="open = true">
        <PhoneCall :size="21" aria-hidden="true" />
      </button>
      <a v-if="whatsapp" :href="whatsappUrl" target="_blank" rel="noopener noreferrer" class="lw-btn lw-wa" aria-label="WhatsApp-এ কথা বলুন" title="WhatsApp-এ কথা বলুন">
        <svg width="26" height="26" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M17.47 14.38c-.3-.15-1.76-.87-2.03-.97-.27-.1-.47-.15-.67.15-.2.3-.77.97-.94 1.17-.17.2-.35.22-.64.07-.3-.15-1.26-.46-2.4-1.48-.89-.79-1.49-1.77-1.66-2.07-.17-.3-.02-.46.13-.61.13-.13.3-.35.45-.52.15-.17.2-.3.3-.5.1-.2.05-.37-.02-.52-.08-.15-.67-1.62-.92-2.22-.24-.58-.49-.5-.67-.51h-.57c-.2 0-.52.07-.8.37-.27.3-1.04 1.02-1.04 2.48s1.07 2.88 1.21 3.08c.15.2 2.1 3.2 5.08 4.49.71.31 1.26.49 1.7.63.71.22 1.36.19 1.87.12.57-.09 1.76-.72 2-1.41.25-.7.25-1.29.17-1.41-.07-.13-.27-.2-.57-.35zM12.05 21.5h-.01a9.4 9.4 0 0 1-4.8-1.31l-.34-.2-3.57.93.95-3.48-.22-.36a9.4 9.4 0 0 1-1.44-5.01c0-5.2 4.23-9.43 9.44-9.43 2.52 0 4.89.98 6.67 2.77a9.36 9.36 0 0 1 2.76 6.67c0 5.2-4.24 9.42-9.44 9.42zm8.02-17.45A11.27 11.27 0 0 0 12.05.75C5.8.75.71 5.84.71 12.1c0 2 .52 3.95 1.52 5.67L.62 23.25l5.61-1.47a11.3 11.3 0 0 0 5.82 1.48h.01c6.25 0 11.34-5.09 11.34-11.35 0-3.03-1.18-5.88-3.33-8.02z"/></svg>
      </a>
    </div>

    <Teleport to="body">
      <div v-if="open" ref="root" class="lw-overlay gb" @click.self="close">
        <div class="lw-card" role="dialog" aria-modal="true" aria-labelledby="lw-title">
          <button class="lw-close" aria-label="বন্ধ করুন" @click="close"><X :size="22" /></button>

          <template v-if="!done">
            <h2 id="lw-title">আপনাকে কল করব?</h2>
            <p class="lw-sub">নাম আর নম্বর দিন। অফিস সময়ের মধ্যে আমাদের টিম আপনাকে ফোন করবে।</p>
            <form class="lw-form" novalidate @submit.prevent="submit">
              <label class="gb-field">
                <span>আপনার নাম</span>
                <input v-model.trim="form.name" class="gb-input" type="text" autocomplete="name" required />
              </label>
              <label class="gb-field">
                <span>মোবাইল নম্বর</span>
                <input v-model="form.phone" class="gb-input" type="tel" inputmode="tel" autocomplete="tel" placeholder="01XXXXXXXXX বা +44…" required :aria-invalid="!!error" aria-describedby="lw-err" />
              </label>
              <label class="gb-field">
                <span>কী নিয়ে কথা বলতে চান?</span>
                <select v-model="form.interest" class="gb-select">
                  <option v-for="o in interests" :key="o" :value="o">{{ o }}</option>
                </select>
              </label>
              <p v-if="error" id="lw-err" class="lw-err" role="alert">{{ error }}</p>
              <button type="submit" class="gb-btn gb-btn--sun" :disabled="sending">{{ sending ? 'পাঠানো হচ্ছে…' : 'কল ব্যাকের অনুরোধ পাঠান' }}</button>
            </form>
          </template>

          <div v-else class="lw-done" role="status">
            <h2>অনুরোধ পাঠানো হয়েছে</h2>
            <p>ধন্যবাদ, {{ form.name }}। আমাদের টিম {{ form.phone }} নম্বরে যোগাযোগ করবে।</p>
            <button class="gb-btn gb-btn--paddy" @click="close">ঠিক আছে</button>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup lang="ts">
import { computed, onMounted, reactive, ref } from 'vue'
import { PhoneCall, X } from 'lucide-vue-next'
import { useOverlayBehavior } from '~/composables/useOverlayBehavior'
import { useSettings } from '~/composables/useSettings'
import { normalizePhone } from '~/utils/propertyLabels'

const { settings, fetchSettings } = useSettings()
onMounted(() => { fetchSettings() })

const whatsapp = computed(() => (settings.value.whatsapp_number || '').replace(/\D/g, ''))
const whatsappUrl = computed(() => `https://wa.me/${whatsapp.value}?text=${encodeURIComponent('আসসালামু আলাইকুম, আমি প্রপার্টি নিয়ে জানতে চাই।')}`)

const interests = ['জমি বা প্লট কিনতে চাই', 'জমি শেয়ার সম্পর্কে জানতে চাই', 'ফ্ল্যাট কিনতে চাই', 'বাণিজ্যিক বা রিসোর্ট প্রপার্টি', 'আমার প্রপার্টি বিক্রি করতে চাই']

const open = ref(false)
const done = ref(false)
const sending = ref(false)
const error = ref('')
const root = ref<HTMLElement | null>(null)
const form = reactive({ name: '', phone: '', interest: interests[0] })

const close = () => { open.value = false; done.value = false; error.value = '' }
useOverlayBehavior(open, close, root)

const submit = async () => {
  error.value = ''
  if (!form.name) { error.value = 'আপনার নাম লিখুন।'; return }
  const phone = normalizePhone(form.phone)
  if (!phone) { error.value = 'সঠিক মোবাইল নম্বর দিন, যেমন 01712345678। বিদেশের নম্বর হলে দেশের কোডসহ (+44…) লিখুন।'; return }
  sending.value = true
  try {
    const res = await fetch(useApiUrl('/leads'), {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
      body: JSON.stringify({ name: form.name, phone, property_title: form.interest, lead_type: 'Callback Request', message: `কল ব্যাকের অনুরোধ: ${form.interest}` })
    })
    if (!res.ok) throw new Error(String(res.status))
    done.value = true
  } catch {
    error.value = 'অনুরোধ পাঠানো যায়নি। আবার চেষ্টা করুন, অথবা সরাসরি WhatsApp-এ লিখুন।'
  } finally {
    sending.value = false
  }
}
</script>

<style scoped>
.lw-stack { position: fixed; right: 20px; bottom: calc(20px + env(safe-area-inset-bottom)); z-index: 800; display: flex; flex-direction: column; gap: 12px; }
.lw-btn { width: 54px; height: 54px; border-radius: 50%; display: grid; place-items: center; cursor: pointer; box-shadow: 0 10px 24px -10px rgba(19, 53, 32, .6); transition: transform .15s ease; }
.lw-btn:hover { transform: translateY(-2px); }
.lw-call { background: var(--gb-paper); color: var(--gb-paddy); border: 1.5px solid var(--gb-paddy); }
.lw-wa { background: #1FA855; color: #fff; }
.lw-btn:focus-visible { outline: 3px solid var(--gb-sun); outline-offset: 3px; }

.lw-overlay { position: fixed; inset: 0; z-index: 1100; background: rgba(19, 53, 32, .5); display: grid; place-items: center; padding: 16px; }
.lw-card { position: relative; width: min(440px, 100%); background: var(--gb-sheet); border-radius: var(--gb-r-lg); padding: 32px 28px 28px; max-height: 92vh; overflow-y: auto; }
.lw-card h2 { font-size: 1.7rem; font-weight: 700; font-stretch: 108%; margin-bottom: 6px; }
.lw-sub { color: var(--gb-ink-soft); margin-bottom: 20px; }
.lw-close { position: absolute; right: 12px; top: 12px; width: 44px; height: 44px; border-radius: 50%; display: grid; place-items: center; background: transparent; color: var(--gb-paddy); cursor: pointer; }
.lw-form { display: flex; flex-direction: column; gap: 14px; }
.lw-form .gb-btn { margin-top: 6px; }
.lw-form .gb-btn:disabled { opacity: .7; cursor: wait; }
.lw-err { color: #A23B16; background: #FBE9DF; border-radius: 8px; padding: 10px 12px; font-size: .92rem; }
.lw-done { display: flex; flex-direction: column; align-items: flex-start; gap: 12px; }
@media (max-width: 560px) { .lw-stack { right: 14px; } .lw-btn { width: 50px; height: 50px; } }
</style>

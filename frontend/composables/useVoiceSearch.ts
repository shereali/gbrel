import { onBeforeUnmount, onMounted, ref } from 'vue'

export type VoiceLang = 'bn-BD' | 'en-US'

const BANGLA_DIGITS: Record<string, string> = { '০': '0', '১': '1', '২': '2', '৩': '3', '৪': '4', '৫': '5', '৬': '6', '৭': '7', '৮': '8', '৯': '9' }
// Spoken digits that the browser sometimes writes out as words instead of numerals.
const DIGIT_WORDS: Record<string, string> = {
  zero: '0', oh: '0', one: '1', two: '2', three: '3', four: '4', five: '5', six: '6', seven: '7', eight: '8', nine: '9',
  'শূন্য': '0', 'জিরো': '0', 'এক': '1', 'দুই': '2', 'তিন': '3', 'চার': '4', 'পাঁচ': '5', 'সাত': '7', 'আট': '8',
  // "ছয়" and "নয়" can arrive with য় as one code point or as য + nukta.
  'ছয়': '6', 'ছয়': '6', 'নয়': '9', 'নয়': '9'
}

/** Bangla numerals → 0-9. */
export const toLatinDigits = (text: string): string => text.replace(/[০-৯]/g, d => BANGLA_DIGITS[d] ?? d)

/**
 * Turns what was heard into a search query. "zero one seven one…" or "০১৭১…" becomes "0171…", so a spoken
 * phone number matches; anything else (a name) is kept as words.
 */
export const spokenToQuery = (heard: string): string => {
  const words = toLatinDigits(heard).trim().toLowerCase().split(/\s+/).filter(Boolean)
  const asDigits = words.map(w => DIGIT_WORDS[w] ?? w)
  const joined = asDigits.join('')
  if (asDigits.length && /^[+\d-]+$/.test(joined) && joined.replace(/\D/g, '').length >= 3) return joined.replace(/[^\d+]/g, '')
  return toLatinDigits(heard).trim()
}

/** Speech-to-text for the CRM search box (Chrome / Edge / Android Chrome / Safari 14.1+). */
export const useVoiceSearch = () => {
  let SpeechRecognitionCtor: any
  // Detected after mount so the server-rendered page and the browser agree on the first render.
  const supported = ref(false)
  onMounted(() => {
    SpeechRecognitionCtor = (window as any).SpeechRecognition || (window as any).webkitSpeechRecognition
    supported.value = !!SpeechRecognitionCtor
  })
  const listening = ref(false)
  const interim = ref('')
  const error = ref('')
  let recognition: any = null

  const stop = () => {
    try { recognition?.stop() } catch { /* already stopped */ }
    listening.value = false
  }

  const start = (lang: VoiceLang, onResult: (query: string, heard: string) => void) => {
    if (!supported.value) { error.value = 'Voice search is not supported in this browser. Use Chrome.'; return }
    stop()
    error.value = ''
    interim.value = ''
    recognition = new SpeechRecognitionCtor()
    recognition.lang = lang
    recognition.interimResults = true
    recognition.maxAlternatives = 1
    recognition.continuous = false
    recognition.onresult = (event: any) => {
      let text = ''
      let isFinal = false
      for (let i = event.resultIndex; i < event.results.length; i++) {
        text += event.results[i][0].transcript
        if (event.results[i].isFinal) isFinal = true
      }
      interim.value = text
      if (isFinal) {
        onResult(spokenToQuery(text), text)
        stop()
      }
    }
    recognition.onerror = (event: any) => {
      error.value = event?.error === 'not-allowed' ? 'Allow microphone access to search by voice.' : (event?.error === 'no-speech' ? 'Did not hear anything. Try again.' : 'Voice search stopped. Try again.')
      listening.value = false
    }
    recognition.onend = () => { listening.value = false }
    listening.value = true
    recognition.start()
  }

  onBeforeUnmount(stop)

  return { supported, listening, interim, error, start, stop }
}

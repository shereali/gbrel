import { ref } from 'vue'
import { toBn } from '~/utils/propertyLabels'

// Mirrors App\Support\SiteSettings on the backend. Every value is editable in Admin → Settings.
export interface DocumentType {
  key: string
  label: string
  hint?: string
  required: boolean
}

export interface SiteSettings {
  site_name: string
  site_title: string
  contact_phone: string
  whatsapp_number: string
  contact_email: string
  office_address: string
  working_hours: string
  home_headline: string
  home_subtitle: string
  owner_commission_percent: number
  owner_terms: string
  owner_terms_version: string
  listing_document_types: DocumentType[]
  commission_rate?: string
  [key: string]: any
}

const defaultSettings: SiteSettings = {
  site_name: 'গ্রাম বাংলা রিয়েল এস্টেট লিমিটেড',
  site_title: 'গ্রাম বাংলা রিয়েল এস্টেট | জমি, প্লট ও ফ্ল্যাট — GBREL',
  contact_phone: '',
  whatsapp_number: '',
  contact_email: '',
  office_address: '',
  working_hours: '',
  home_headline: 'জমি দেখে, কাগজ বুঝে, তারপর কিনুন।',
  home_subtitle: '',
  owner_commission_percent: 2,
  owner_terms: '',
  owner_terms_version: '',
  listing_document_types: []
}

const settings = ref<SiteSettings>({ ...defaultSettings })
const isLoading = ref(false)
const isSaving = ref(false)
const hasLoaded = ref(false)
let pending: Promise<SiteSettings> | null = null

const normalize = (data: Record<string, any>): SiteSettings => ({
  ...defaultSettings,
  ...data,
  owner_commission_percent: Number(data.owner_commission_percent ?? defaultSettings.owner_commission_percent),
  listing_document_types: Array.isArray(data.listing_document_types) ? data.listing_document_types : []
})

export const useSettings = () => {
  const fetchSettings = async (force = false): Promise<SiteSettings> => {
    if (hasLoaded.value && !force) return settings.value
    if (pending && !force) return pending
    isLoading.value = true
    pending = (async () => {
      try {
        const res = await fetch(useApiUrl('/settings'))
        const body = await res.json().catch(() => null)
        if (res.ok && body?.data && typeof body.data === 'object') {
          settings.value = normalize(body.data)
          hasLoaded.value = true
        }
      } catch (err) {
        console.warn('Settings could not be loaded:', err)
      } finally {
        isLoading.value = false
        pending = null
      }
      return settings.value
    })()
    return pending
  }

  const updateSettings = async (changes: Partial<SiteSettings>) => {
    isSaving.value = true
    try {
      const res = await fetch(useApiUrl('/settings'), {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
        body: JSON.stringify(changes)
      })
      const body = await res.json().catch(() => null)
      if (!res.ok || !body?.success) {
        const first = body?.errors ? Object.values(body.errors).flat()[0] : null
        throw new Error(String(first || body?.message || 'সেটিংস সংরক্ষণ করা যায়নি।'))
      }
      settings.value = normalize(body.data)
      hasLoaded.value = true
      return settings.value
    } finally {
      isSaving.value = false
    }
  }

  // Owner terms are stored one rule per line; {commission} is replaced with the current percentage.
  const ownerTermsList = () => settings.value.owner_terms
    .split('\n')
    .map(line => line.trim())
    .filter(Boolean)
    .map(line => line.replaceAll('{commission}', toBn(settings.value.owner_commission_percent)))

  return { settings, isLoading, isSaving, fetchSettings, updateSettings, ownerTermsList }
}

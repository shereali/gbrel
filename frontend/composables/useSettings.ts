import { ref } from 'vue'

export interface SiteSettings {
  site_name: string
  site_tagline: string
  contact_email: string
  contact_phone: string
  whatsapp_number: string
  emergency_hotline: string
  office_address: string
  working_hours: string
  bank_financing_rate: number
  service_fee_pct: number
  vat_tax_pct: number
  currency: string
  currency_symbol: string
  maintenance_mode: boolean
  [key: string]: any
}

const defaultSettings: SiteSettings = {
  site_name: 'Gram Bangla Real Estate Ltd',
  site_tagline: 'Bangladesh’s Premier Real Estate Marketplace',
  contact_email: 'info@gbrel.com',
  contact_phone: '+880 1711 000000',
  whatsapp_number: '+880 1711 000000',
  emergency_hotline: '+880 1911 222333',
  office_address: 'Plot 12, Road 4, Gulshan-1, Dhaka 1212, Bangladesh',
  working_hours: 'Saturday – Thursday: 9:00 AM – 7:00 PM',
  bank_financing_rate: 8.5,
  service_fee_pct: 1.5,
  vat_tax_pct: 7.5,
  currency: 'BDT',
  currency_symbol: '৳',
  maintenance_mode: false
}

const settings = ref<SiteSettings>({ ...defaultSettings })
const isLoading = ref(false)
const isSaving = ref(false)
const hasLoaded = ref(false)

export const useSettings = () => {
  const fetchSettings = async (force = false) => {
    if (hasLoaded.value && !force) return settings.value
    isLoading.value = true
    try {
      const res = await fetch(useApiUrl('/settings'))
      if (res.ok) {
        const data = await res.json()
        if (data && typeof data === 'object') {
          // Merge with defaults
          settings.value = {
            ...defaultSettings,
            ...data,
            bank_financing_rate: Number(data.bank_financing_rate ?? defaultSettings.bank_financing_rate),
            service_fee_pct: Number(data.service_fee_pct ?? defaultSettings.service_fee_pct),
            vat_tax_pct: Number(data.vat_tax_pct ?? defaultSettings.vat_tax_pct),
            maintenance_mode: Boolean(data.maintenance_mode === true || data.maintenance_mode === '1' || data.maintenance_mode === 'true')
          }
          hasLoaded.value = true
        }
      }
    } catch (err) {
      console.error('Failed to load settings:', err)
    } finally {
      isLoading.value = false
    }
    return settings.value
  }

  const updateSettings = async (newSettings: Partial<SiteSettings>) => {
    isSaving.value = true
    try {
      const res = await fetch(useApiUrl('/settings'), {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(newSettings)
      })
      if (!res.ok) {
        throw new Error(`Failed to save settings: ${res.statusText}`)
      }
      const data = await res.json()
      if (data && data.settings) {
        settings.value = {
          ...settings.value,
          ...data.settings,
          bank_financing_rate: Number(data.settings.bank_financing_rate ?? settings.value.bank_financing_rate),
          service_fee_pct: Number(data.settings.service_fee_pct ?? settings.value.service_fee_pct),
          vat_tax_pct: Number(data.settings.vat_tax_pct ?? settings.value.vat_tax_pct)
        }
      } else {
        settings.value = { ...settings.value, ...newSettings }
      }
      return true
    } catch (err) {
      console.error('Failed to update settings:', err)
      throw err
    } finally {
      isSaving.value = false
    }
  }

  return {
    settings,
    isLoading,
    isSaving,
    fetchSettings,
    updateSettings
  }
}

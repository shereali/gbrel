import { toBn } from '~/utils/propertyLabels'

// Listings store area names in English ("Gulshan-1") because the filters and the admin use them.
// The public Bangla pages show the Bangla name; links keep the stored value so filtering still works.
const AREAS: Record<string, string> = {
  gulshan: 'গুলশান', banani: 'বনানী', baridhara: 'বারিধারা', niketan: 'নিকেতন', dhanmondi: 'ধানমন্ডি',
  uttara: 'উত্তরা', bashundhara: 'বসুন্ধরা', purbachal: 'পূর্বাচল', mirpur: 'মিরপুর', mohammadpur: 'মোহাম্মদপুর',
  motijheel: 'মতিঝিল', tejgaon: 'তেজগাঁও', badda: 'বাড্ডা', rampura: 'রামপুরা', khilkhet: 'খিলক্ষেত',
  mohakhali: 'মহাখালী', nikunja: 'নিকুঞ্জ', aftabnagar: 'আফতাবনগর', jolshiri: 'জলসিঁড়ি', savar: 'সাভার',
  keraniganj: 'কেরানীগঞ্জ', narayanganj: 'নারায়ণগঞ্জ', gazipur: 'গাজীপুর', segunbagicha: 'সেগুনবাগিচা',
  dhaka: 'ঢাকা', chattogram: 'চট্টগ্রাম', chittagong: 'চট্টগ্রাম', sylhet: 'সিলেট', coxsbazar: 'কক্সবাজার',
  rajshahi: 'রাজশাহী', khulna: 'খুলনা', cumilla: 'কুমিল্লা', comilla: 'কুমিল্লা', mymensingh: 'ময়মনসিংহ'
}

export function areaNameBn(name?: string | null): string {
  const raw = String(name || '').trim()
  if (!raw) return ''
  const m = /^(.*?)[\s-]*(\d+)$/.exec(raw)
  const base = (m ? m[1] : raw).toLowerCase().replace(/[^a-z]/g, '')
  const bn = AREAS[base]
  if (!bn) return raw
  return m ? `${bn}-${toBn(m[2])}` : bn
}

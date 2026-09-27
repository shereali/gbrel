// Google Maps links for the office. The embed needs no API key; the location text is editable in Admin → Settings.
const q = (location: string) => encodeURIComponent(location.trim())
export const mapEmbedUrl = (location: string) => `https://www.google.com/maps?q=${q(location)}&z=17&hl=bn&output=embed`
export const mapOpenUrl = (location: string) => `https://www.google.com/maps/search/?api=1&query=${q(location)}`
export const mapDirectionsUrl = (location: string) => `https://www.google.com/maps/dir/?api=1&destination=${q(location)}`

const bnDigits = '০১২৩৪৫৬৭৮৯'
const bnMonths = ['জানুয়ারি', 'ফেব্রুয়ারি', 'মার্চ', 'এপ্রিল', 'মে', 'জুন', 'জুলাই', 'আগস্ট', 'সেপ্টেম্বর', 'অক্টোবর', 'নভেম্বর', 'ডিসেম্বর']
/** 2027-06-30 → ৩০ জুন ২০২৭ */
export const bnDate = (iso?: string | null) => {
  const m = (iso || '').match(/^(\d{4})-(\d{2})-(\d{2})$/)
  if (!m) return ''
  const n = (v: string | number) => String(v).replace(/\d/g, d => bnDigits[Number(d)])
  return `${n(Number(m[3]))} ${bnMonths[Number(m[2]) - 1]} ${n(m[1])}`
}

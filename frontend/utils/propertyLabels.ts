// Bangla labels for property data shown on the public site.
export const typeLabels: Record<string, string> = {
  Plot: 'প্লট',
  Land: 'জমি',
  'Land Share': 'জমি শেয়ার',
  Flat: 'ফ্ল্যাট',
  Duplex: 'ডুপ্লেক্স',
  Penthouse: 'পেন্টহাউস',
  Hotel: 'হোটেল ও রিসোর্ট',
  Commercial: 'বাণিজ্যিক'
}
export const statusLabels: Record<string, string> = {
  Active: 'বিক্রি হচ্ছে',
  'Under Offer': 'আলোচনায় আছে',
  Sold: 'বিক্রি হয়ে গেছে',
  Delisted: 'তালিকার বাইরে',
  Draft: 'খসড়া'
}
export const listingLabels: Record<string, string> = { Sale: 'বিক্রয়', Lease: 'ভাড়া / লিজ' }
export const completionLabels: Record<string, string> = {
  Ready: 'রেডি',
  'Under Construction': 'নির্মাণাধীন',
  'Upcoming Project': 'আসন্ন প্রকল্প'
}
export const unitLabels: Record<string, string> = { Katha: 'কাঠা', Bigha: 'বিঘা', Shotok: 'শতক', Decimal: 'শতাংশ', Sqft: 'বর্গফুট' }

const bnDigits = '০১২৩৪৫৬৭৮৯'
export const toBn = (v: string | number) => String(v).replace(/\d/g, d => bnDigits[Number(d)])

export const typeLabel = (t?: string) => (t && typeLabels[t]) || t || ''
export const statusLabel = (s?: string) => (s && statusLabels[s]) || s || ''

export const areaLabel = (sqft?: number, landSize?: number, landUnit?: string) => {
  if (landSize && landUnit) return `${toBn(landSize)} ${unitLabels[landUnit] || landUnit}`
  if (sqft) return `${toBn(sqft.toLocaleString('en-IN'))} বর্গফুট`
  return ''
}

// ৳ ১.২৫ কোটি / ৳ ৮৫ লাখ
export const priceBn = (amount?: number | null) => {
  if (!amount) return ''
  if (amount >= 10000000) return `৳ ${toBn((amount / 10000000).toFixed(2).replace(/\.?0+$/, ''))} কোটি`
  if (amount >= 100000) return `৳ ${toBn((amount / 100000).toFixed(2).replace(/\.?0+$/, ''))} লাখ`
  return `৳ ${toBn(amount.toLocaleString('en-IN'))}`
}

// Normalise a phone number to the format the leads API accepts:
// +8801XXXXXXXXX for Bangladesh, or +<country><number> for abroad.
export const toApiPhone = (raw: string): string | null => {
  const bn = '০১২৩৪৫৬৭৮৯'
  let v = raw.replace(/[০-৯]/g, d => String(bn.indexOf(d))).replace(/[\s\-().]/g, '')
  if (v.startsWith('00')) v = '+' + v.slice(2)
  if (/^01[3-9]\d{8}$/.test(v)) v = '+88' + v
  if (/^8801[3-9]\d{8}$/.test(v)) v = '+' + v
  if (/^\+8801[3-9]\d{8}$/.test(v)) return v
  if (/^\+(?!880)[1-9]\d{7,14}$/.test(v)) return v
  return null
}

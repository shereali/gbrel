import { priceDisplay } from '~/utils/priceDisplay'

export const SITE_URL = 'https://gbrel.com'
export const DEFAULT_SHARE_IMAGE = `${SITE_URL}/og-default.jpg`

// One sentence for link previews and search results: title, the price as the page leads with it, and why to click.
export function propertyShareDescription(property: any): string {
  if (!property) return 'প্রপার্টির তথ্য, মূল্য ও যোগাযোগ।'
  const price = priceDisplay(property)
  const negotiable = property.buyerDetails?.negotiable === 'Yes' ? ' (আলোচনা সাপেক্ষ)' : ''
  const priceText = !price.hidden && price.amount ? `${price.per} ${price.amount}${negotiable}।` : ''
  return [`${property.title}।`, priceText, 'কাগজপত্র দেখে, সাইট ভিজিট করে সিদ্ধান্ত নিন: GBREL।'].filter(Boolean).join(' ')
}

// Share images must be absolute URLs; listing photos are stored as site paths ("/storage/…", "/img/…").
export function absoluteImage(src?: string | null): string {
  const s = String(src || '').trim()
  if (/^https:\/\//i.test(s)) return s
  if (s.startsWith('/') && !s.startsWith('//')) return `${SITE_URL}${s}`
  return DEFAULT_SHARE_IMAGE
}

// E-book "The Hidden Business of Land" — shared by pages/book.vue, pages/book-thanks.vue and components/BookDownloadModal.vue.
// The price the buyer pays is set on the server (BOOK_PRICE); keep PRICE here in step with it.
import { SITE_URL } from '~/utils/shareMeta'

export const BOOK = {
  title: 'The Hidden Business of Land',
  pages: 44,
  chapters: 12,
  author: 'মো. শের আলী খান',
  cover: '/img/book/cover.jpg',
  listPrice: 1100,
  price: 500,
  // Pixel content id, so ads can be reported per book and not mixed with property ids.
  contentId: 'book-hidden-business-of-land',
  facebookPage: 'https://www.facebook.com/grambanglarel'
}

export const BOOK_DISCOUNT_PERCENT = Math.round((1 - BOOK.price / BOOK.listPrice) * 100)

// Shared copies point back here with their own source, so shares are not counted as paid ad clicks.
export const bookShareUrl = (channel: string) => `${SITE_URL}/book?utm_source=${encodeURIComponent(channel)}&utm_medium=share&utm_campaign=land-book`

export const BOOK_SHARE_TEXT = 'জমি, ফ্ল্যাট আর ল্যান্ড শেয়ার কেনা-বেচার আগে এই বাংলা বইটা পড়ে নিন:'

export const bookShareLinks = {
  facebook: () => `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(bookShareUrl('fb_share'))}`,
  whatsapp: () => `https://wa.me/?text=${encodeURIComponent(`${BOOK_SHARE_TEXT} ${bookShareUrl('wa_share')}`)}`
}

// Optional question on the order form. Keys match BookDownloadController::REASONS.
export const BOOK_REASONS = [
  { value: 'buy', label: 'কিনব' },
  { value: 'sell', label: 'বেচব' },
  { value: 'business', label: 'ব্যবসা করব' },
  { value: 'invest_abroad', label: 'প্রবাস থেকে বিনিয়োগ' },
  { value: 'learn', label: 'জানতে চাই' }
] as const

export type BookReason = typeof BOOK_REASONS[number]['value']

// A buyer who paid on this phone sees "open your book" instead of the order form (the order link never expires).
const ORDER_KEY = 'gbrel_book_order'

export const savedBookOrder = (): string => {
  try {
    const saved = JSON.parse(localStorage.getItem(ORDER_KEY) || 'null')
    return saved && typeof saved.order === 'string' && saved.paid ? saved.order : ''
  } catch {
    return ''
  }
}

export const rememberBookOrder = (order: string, paid: boolean) => {
  try {
    localStorage.setItem(ORDER_KEY, JSON.stringify({ order, paid }))
  } catch { /* storage unavailable: the buyer can still use the order link */ }
}

// Facebook and Instagram in-app browsers often cannot save files; the page tells those readers what to do.
export const isInAppBrowser = () => typeof navigator !== 'undefined' && /FBAN|FBAV|FB_IAB|Instagram|Messenger/i.test(navigator.userAgent)

// Bangla digits with thousands separators: 1100 → "১,১০০".
export const bnMoney = (n: number) => n.toLocaleString('en-IN').replace(/\d/g, d => '০১২৩৪৫৬৭৮৯'[Number(d)])

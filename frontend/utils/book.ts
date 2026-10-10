// Free e-book "The Hidden Business of Land" — shared by pages/book.vue and components/BookDownloadModal.vue.
import { SITE_URL } from '~/utils/shareMeta'

export const BOOK = {
  title: 'The Hidden Business of Land',
  pages: 37,
  author: 'মো. শের আলী খান',
  cover: '/img/book/cover.jpg',
  // Pixel content id, so ads can be reported per book and not mixed with property ids.
  contentId: 'book-hidden-business-of-land',
  facebookPage: 'https://www.facebook.com/grambanglarel'
}

// Shared copies point back here with their own source, so shares are not counted as paid ad clicks.
export const bookShareUrl = (channel: string) => `${SITE_URL}/book?utm_source=${encodeURIComponent(channel)}&utm_medium=share&utm_campaign=land-book`

export const BOOK_SHARE_TEXT = 'জমি, ফ্ল্যাট আর ল্যান্ড শেয়ার কেনা-বেচার আগে এই ফ্রি বাংলা বইটা পড়ে নিন:'

export const bookShareLinks = {
  facebook: () => `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(bookShareUrl('fb_share'))}`,
  whatsapp: () => `https://wa.me/?text=${encodeURIComponent(`${BOOK_SHARE_TEXT} ${bookShareUrl('wa_share')}`)}`
}

// The one question asked before the download. Keys match BookDownloadController::REASONS.
export const BOOK_REASONS = [
  { value: 'buy', label: 'জমি বা ফ্ল্যাট কিনতে চাই' },
  { value: 'sell', label: 'জমি বা ফ্ল্যাট বেচতে চাই' },
  { value: 'business', label: 'জমির ব্যবসা শুরু করতে চাই' },
  { value: 'invest_abroad', label: 'প্রবাসে আছি, দেশে বিনিয়োগ করতে চাই' },
  { value: 'learn', label: 'শুধু জানতে চাই' }
] as const

export type BookReason = typeof BOOK_REASONS[number]['value']

// A reader who already got the book sees their link again instead of the form (kept for the link's lifetime).
const SAVED_KEY = 'gbrel_book_link'
const LINK_DAYS = 7

export const savedBookLink = (): string => {
  try {
    const saved = JSON.parse(localStorage.getItem(SAVED_KEY) || 'null')
    return saved && saved.until > Date.now() && typeof saved.url === 'string' ? saved.url : ''
  } catch {
    return ''
  }
}

export const rememberBookLink = (url: string) => {
  try {
    localStorage.setItem(SAVED_KEY, JSON.stringify({ url, until: Date.now() + (LINK_DAYS - 1) * 86400000 }))
  } catch { /* storage unavailable: the reader just sees the form again next time */ }
}

// Facebook and Instagram in-app browsers often cannot save files; the page tells those readers what to do.
export const isInAppBrowser = () => typeof navigator !== 'undefined' && /FBAN|FBAV|FB_IAB|Instagram|Messenger/i.test(navigator.userAgent)

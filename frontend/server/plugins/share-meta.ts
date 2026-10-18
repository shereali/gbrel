// The site renders in the browser (ssr: false), and Facebook/WhatsApp crawlers do not run JavaScript.
// So for /properties/:id the server fills in the title, description and image before sending the page shell.
import { absoluteImage, propertyShareDescription, SITE_URL } from '~/utils/shareMeta'
import { getOrganizationSchema } from '~/utils/structuredData'

const esc = (v: string) => v.replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
const REPLACED = /<title>[\s\S]*?<\/title>|<meta[^>]+(?:name="description"|property="og:[^"]*"|name="twitter:[^"]*")[^>]*>/g

// Only the fields the preview needs, in the shape the frontend's price rules expect.
function toPreviewProperty(d: any) {
  return {
    title: String(d.title || ''),
    price: Number(d.price) || 0,
    propertyType: d.property_type || '',
    landSize: Number(d.land_size) || 0,
    landUnit: d.land_unit || 'Katha',
    squareFootage: Number(d.square_footage) || 0,
    totalFloors: Number(d.total_floors) || 0,
    hidePrice: Boolean(d.hide_price),
    priceDisplayText: d.price_display_text || '',
    buyerDetails: d.buyer_details && typeof d.buyer_details === 'object' && !Array.isArray(d.buyer_details) ? d.buyer_details : {},
    image: (Array.isArray(d.images) && d.images[0]) || d.feature_image || d.video_poster || ''
  }
}

// The e-book sales page (/book) is shared on Facebook and WhatsApp a lot, so it gets its own preview.
const BOOK_TAGS = [
  `<title>The Hidden Business of Land: জমি-ফ্ল্যাট কেনা-বেচার বাংলা গাইড | গ্রাম বাংলা রিয়েল এস্টেট</title>`,
  `<meta name="description" content="জমি, ফ্ল্যাট ও ল্যান্ড শেয়ার নিরাপদে কেনা, ন্যায্য দামে বেচা আর এই ব্যবসায় আয়ের পথ নিয়ে উৎসসহ বাংলা ই-বুক। ৳১,১০০ নয়, এখন ৳৫০০।">`,
  `<meta property="og:type" content="website">`,
  `<meta property="og:site_name" content="গ্রাম বাংলা রিয়েল এস্টেট">`,
  `<meta property="og:locale" content="bn_BD">`,
  `<meta property="og:title" content="একটা ভুল কাগজে লাখ টাকা যায়: জমি-ফ্ল্যাটের বাংলা গাইড">`,
  `<meta property="og:description" content="কোন মুখী, কোন তলা, কোন কাগজ: টাকা দেওয়ার আগে পড়ে নিন। ৳১,১০০ নয়, এখন ৳৫০০।">`,
  `<meta property="og:url" content="${SITE_URL}/book">`,
  `<meta property="og:image" content="${SITE_URL}/img/book/og-book.jpg">`,
  `<meta property="og:image:width" content="1200">`,
  `<meta property="og:image:height" content="630">`,
  `<meta name="twitter:card" content="summary_large_image">`
].join('')

// In-memory cache for fast SSR tag generation & data injection (60s TTL)
const propertyCache = new Map<string, { data: any; expires: number }>()

export default defineNitroPlugin(nitroApp => {
  nitroApp.hooks.hook('render:html', async (html, { event }) => {
    // Strip speculative prefetch links from initial head to prevent saturating mobile bandwidth
    html.head = html.head.map(chunk => chunk.replace(/<link rel="prefetch"[^>]*>/g, ''))

    const path = getRequestURL(event).pathname

    // Global injection: Schema.org structured data for Google Search & AI Search engines (GEO)
    const orgSchemaJson = JSON.stringify(getOrganizationSchema()).replace(/</g, '\\u003c')
    html.head.push(`<script type="application/ld+json">${orgSchemaJson}</script>`)

    if (/^\/book\/?$/.test(path)) {
      html.head = html.head.map(chunk => chunk.replace(REPLACED, '').replace(/<meta[^>]+property="og:image:(?:width|height)"[^>]*>/g, ''))
      html.head.push(BOOK_TAGS)
      return
    }

    if (path === '/' || path === '') {
      const homeTags = [
        `<title>Gram Bangla Real Estate Limited | Gram Bangla Real Estate — GBREL</title>`,
        `<meta name="description" content="Gram Bangla Real Estate Limited (GBREL) is a premier registered real estate company in Bangladesh. Official site: www.gbrel.com. Discover verified land, plots, land shares, and apartments in Dhaka. Call: +8809658510748 | মিরপুর ডিওএইচএস, ঢাকা।">`,
        `<meta name="keywords" content="Gram Bangla Real Estate Limited, Gram Bangla Real Estate, Gram Bangla, GBREL, www.gbrel.com, gbrel, গ্রাম বাংলা রিয়েল এস্টেট লিমিটেড, গ্রাম বাংলা রিয়েল এস্টেট, গ্রাম বাংলা, Real Estate Bangladesh, Mirpur DOHS Real Estate, Plots in Dhaka, Land Share Dhaka">`,
        `<meta property="og:type" content="website">`,
        `<meta property="og:site_name" content="Gram Bangla Real Estate Limited">`,
        `<meta property="og:locale" content="bn_BD">`,
        `<meta property="og:title" content="Gram Bangla Real Estate Limited | জমি দেখে, কাগজ বুঝে, তারপর কিনুন — GBREL">`,
        `<meta property="og:description" content="Gram Bangla Real Estate Limited (GBREL) — জমি, প্লট, জমি শেয়ার ও ফ্ল্যাট খুঁজুন। দাম, কাগজপত্র ও লোকেশন দেখে নিন। Official: www.gbrel.com | ফোন: +8809658510748">`,
        `<meta property="og:url" content="${SITE_URL}/">`,
        `<meta property="og:image" content="${SITE_URL}/og-default.jpg">`,
        `<meta name="twitter:card" content="summary_large_image">`,
        `<meta name="twitter:title" content="Gram Bangla Real Estate Limited | Gram Bangla Real Estate — GBREL">`,
        `<meta name="twitter:description" content="Gram Bangla Real Estate Limited (GBREL) — জমি, প্লট, জমি শেয়ার ও ফ্ল্যাট। www.gbrel.com | ফোন: +8809658510748">`
      ].join('')
      html.head = html.head.map(chunk => chunk.replace(REPLACED, ''))
      html.head.push(homeTags)
      return
    }

    const match = /^\/properties\/(\d+)\/?$/.exec(path)
    if (!match) return
    const propId = match[1]
    const base = String(useRuntimeConfig().apiBaseServer || '').replace(/\/$/, '')
    if (!base) return

    let data: any = null
    const now = Date.now()
    const cached = propertyCache.get(propId)
    if (cached && cached.expires > now) {
      data = cached.data
    } else {
      try {
        const res: any = await $fetch(`${base}/properties/${propId}`, { timeout: 2500 })
        data = res?.success ? res.data : null
        if (data) {
          propertyCache.set(propId, { data, expires: now + 60000 })
        }
      } catch {
        return // Unknown, unpublished or backend busy: keep the site-wide tags.
      }
    }
    if (!data) return

    const p = toPreviewProperty(data)
    const title = `${p.title} | GBREL`
    const description = propertyShareDescription(p)
    const url = `${SITE_URL}/properties/${propId}`
    const heroImg = absoluteImage(p.image)

    const tags = [
      `<title>${esc(title)}</title>`,
      `<meta name="description" content="${esc(description)}">`,
      `<meta property="og:type" content="website">`,
      `<meta property="og:site_name" content="গ্রাম বাংলা রিয়েল এস্টেট">`,
      `<meta property="og:locale" content="bn_BD">`,
      `<meta property="og:title" content="${esc(p.title)}">`,
      `<meta property="og:description" content="${esc(description)}">`,
      `<meta property="og:url" content="${esc(url)}">`,
      `<meta property="og:image" content="${esc(heroImg)}">`,
      `<meta name="twitter:card" content="summary_large_image">`,
      // Preload hero image to ensure immediate LCP on mobile
      heroImg ? `<link rel="preload" as="image" href="${esc(heroImg)}" fetchpriority="high">` : '',
      // Inline initial data so client hydration needs 0 network roundtrips
      `<script id="__GBREL_INITIAL_PROPERTY__" type="application/json">${JSON.stringify(data).replace(/</g, '\\u003c')}</script>`
    ].filter(Boolean).join('')

    html.head = html.head.map(chunk => chunk.replace(REPLACED, ''))
    html.head.push(tags)
  })
})

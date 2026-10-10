// The site renders in the browser (ssr: false), and Facebook/WhatsApp crawlers do not run JavaScript.
// So for /properties/:id the server fills in the title, description and image before sending the page shell.
import { absoluteImage, propertyShareDescription, SITE_URL } from '~/utils/shareMeta'

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

// The free-book landing page (/book) is shared on Facebook and WhatsApp a lot, so it gets its own preview.
const BOOK_TAGS = [
  `<title>ফ্রি ই-বুক: The Hidden Business of Land | গ্রাম বাংলা রিয়েল এস্টেট</title>`,
  `<meta name="description" content="জমি, ফ্ল্যাট ও ল্যান্ড শেয়ার নিরাপদে কেনা, ন্যায্য দামে বেচা আর এই ব্যবসায় আয়ের পথ নিয়ে ৩৭ পাতার ফ্রি বাংলা ই-বুক।">`,
  `<meta property="og:type" content="website">`,
  `<meta property="og:site_name" content="গ্রাম বাংলা রিয়েল এস্টেট">`,
  `<meta property="og:locale" content="bn_BD">`,
  `<meta property="og:title" content="সোনার হরিণ আপনার পায়ের নিচে: ফ্রি বাংলা ই-বুক">`,
  `<meta property="og:description" content="জমি, ফ্ল্যাট ও ল্যান্ড শেয়ার কেনা-বেচার আগে পড়ে নিন। ৩৭ পাতা, সম্পূর্ণ ফ্রি।">`,
  `<meta property="og:url" content="${SITE_URL}/book">`,
  `<meta property="og:image" content="${SITE_URL}/img/book/og-book.jpg">`,
  `<meta property="og:image:width" content="1200">`,
  `<meta property="og:image:height" content="630">`,
  `<meta name="twitter:card" content="summary_large_image">`
].join('')

export default defineNitroPlugin(nitroApp => {
  nitroApp.hooks.hook('render:html', async (html, { event }) => {
    const path = getRequestURL(event).pathname
    if (/^\/book\/?$/.test(path)) {
      html.head = html.head.map(chunk => chunk.replace(REPLACED, '').replace(/<meta[^>]+property="og:image:(?:width|height)"[^>]*>/g, ''))
      html.head.push(BOOK_TAGS)
      return
    }
    const match = /^\/properties\/(\d+)\/?$/.exec(path)
    if (!match) return
    const base = String(useRuntimeConfig().apiBaseServer || '').replace(/\/$/, '')
    if (!base) return
    let data: any
    try {
      const res: any = await $fetch(`${base}/properties/${match[1]}`, { timeout: 2500 })
      data = res?.success ? res.data : null
    } catch {
      return // Unknown, unpublished or backend busy: keep the site-wide tags.
    }
    if (!data) return

    const p = toPreviewProperty(data)
    const title = `${p.title} | GBREL`
    const description = propertyShareDescription(p)
    const url = `${SITE_URL}/properties/${match[1]}`
    const tags = [
      `<title>${esc(title)}</title>`,
      `<meta name="description" content="${esc(description)}">`,
      `<meta property="og:type" content="website">`,
      `<meta property="og:site_name" content="গ্রাম বাংলা রিয়েল এস্টেট">`,
      `<meta property="og:locale" content="bn_BD">`,
      `<meta property="og:title" content="${esc(p.title)}">`,
      `<meta property="og:description" content="${esc(description)}">`,
      `<meta property="og:url" content="${esc(url)}">`,
      `<meta property="og:image" content="${esc(absoluteImage(p.image))}">`,
      `<meta name="twitter:card" content="summary_large_image">`
    ].join('')
    html.head = html.head.map(chunk => chunk.replace(REPLACED, ''))
    html.head.push(tags)
  })
})

// The site renders in the browser (ssr: false), and search/social crawlers inspect the initial server HTML.
// This plugin injects accurate route-specific <title>, <meta>, <link rel="canonical">, JSON-LD structured data,
// and semantic <noscript> content for every public route, while marking /admin and /owner as noindex.
import { absoluteImage, propertyShareDescription, SITE_URL } from '~/utils/shareMeta'
import { getOrganizationSchema } from '~/utils/structuredData'

const esc = (v: string) => v.replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
const REPLACED = /<title>[\s\S]*?<\/title>|<meta[^>]+(?:name="description"|name="keywords"|name="robots"|property="og:[^"]*"|name="twitter:[^"]*")[^>]*>|<link[^>]+rel="canonical"[^>]*>/g

const BRAND_KEYWORDS = 'Grambangla Real Estate, Grambangla Real Estate Limited, Gram Bangla Real Estate, Gram Bangla Real Estate Limited, GBREL, www.gbrel.com, gbrel.com, গ্রামবাংলা রিয়েল এস্টেট, গ্রামবাংলা রিয়েল এস্টেট লিমিটেড, গ্রাম বাংলা রিয়েল এস্টেট, Real Estate Dhaka Bangladesh, Mirpur DOHS Real Estate, Verified Plots Dhaka, Land Share Dhaka'

interface RouteSeoConfig {
  title: string
  description: string
  ogTitle?: string
  ogDescription?: string
  image?: string
  heading: string
  summary: string
}

const STATIC_ROUTE_SEO: Record<string, RouteSeoConfig> = {
  '/': {
    title: 'Grambangla Real Estate | Grambangla Real Estate Limited (GBREL)',
    description: 'Grambangla Real Estate (Grambangla Real Estate Limited — GBREL) is an RJSC-registered real estate company in Dhaka, Bangladesh (Reg: C-185751/2022, Trade License: TRAD/DSCC/026884/2022). Official website: www.gbrel.com. Verified plots, land shares, duplexes & apartments in Dhaka. Call: +8809658510748.',
    ogTitle: 'Grambangla Real Estate | জমি দেখে, কাগজ বুঝে, তারপর কিনুন — GBREL',
    ogDescription: 'Grambangla Real Estate (Grambangla Real Estate Limited, GBREL) — যাচাইকৃত জমি, প্লট, ল্যান্ড শেয়ার ও ফ্ল্যাট খুঁজুন। দাম, কাগজপত্র ও লোকেশন দেখে নিন। Official: www.gbrel.com | ফোন: +8809658510748',
    heading: 'Grambangla Real Estate (Grambangla Real Estate Limited — GBREL)',
    summary: 'Grambangla Real Estate (registered as Grambangla Real Estate Limited, RJSC Registration No. C-185751/2022, DSCC e-Trade License No. TRAD/DSCC/026884/2022) offers legally verified plots, land shares, duplexes, and apartments in Dhaka, Bangladesh. Headquartered at House 461, Road 7, Avenue 6, Mirpur DOHS, Mirpur 12, Dhaka 1216. Grambangla Real Estate Limited (gbrel.com) is an independent entity and is not affiliated with Grambangla Holdings or Grambangla Housing.'
  },
  '/properties': {
    title: 'সকল প্রপার্টি: প্লট, ল্যান্ড শেয়ার ও ফ্ল্যাট | Grambangla Real Estate (GBREL)',
    description: 'Grambangla Real Estate (Grambangla Real Estate Limited — GBREL) এর যাচাইকৃত প্লট, ল্যান্ড শেয়ার, ডুপ্লেক্স ও ফ্ল্যাটের তালিকা। দাম, লোকেশন ও দলিলপত্র দেখে সাইট ভিজিট বুক করুন।',
    heading: 'Verified Properties in Dhaka — Grambangla Real Estate',
    summary: 'Explore legally verified residential plots, commercial plots, land shares, duplexes, and ready apartments in Dhaka offered by Grambangla Real Estate Limited (GBREL).'
  },
  '/directors': {
    title: 'পরিচালনা পর্ষদ (Board of Directors) | Grambangla Real Estate Limited (GBREL)',
    description: 'Grambangla Real Estate Limited (GBREL) এর পরিচালনা পর্ষদ — প্রতিষ্ঠাতা ও ব্যবস্থাপনা পরিচালক মোঃ আবদুল কাদির তালুকদার, উপ-ব্যবস্থাপনা পরিচালক ইঞ্জি. সিয়াম তালুকদার ও পরিচালকবৃন্দ।',
    heading: 'Board of Directors — Grambangla Real Estate Limited (GBREL)',
    summary: 'Board of Directors of Grambangla Real Estate Limited (RJSC Reg: C-185751/2022): MD Abdul Quder Talukder (Founder & Managing Director), Engr. Siam Talukder (Deputy Managing Director), Engr. Sher Ali Khan (Co-Founder & CTO), Md. Abu Hanif (Director, Admin & Accounts), and Md. Rubel Hawlader (Director, Project & Transport).'
  },
  '/legal': {
    title: 'আইনি স্বচ্ছতা ও সরকারি নিবন্ধন (RJSC C-185751/2022) | Grambangla Real Estate Limited',
    description: 'Grambangla Real Estate Limited (GBREL) এর সরকারি নিবন্ধন ও আইনি তথ্য: RJSC ইনকর্পোরেশন নং C-185751/2022 এবং ঢাকা দক্ষিণ সিটি কর্পোরেশন ই-ট্রেড লাইসেন্স নং TRAD/DSCC/026884/2022।',
    heading: 'Legal Registration & Corporate Credentials — Grambangla Real Estate Limited',
    summary: 'Grambangla Real Estate Limited (GBREL) is incorporated under the Companies Act 1994 with the Registrar of Joint Stock Companies and Firms (RJSC Registration No. C-185751/2022, dated 11 December 2022) and licensed by Dhaka South City Corporation (e-Trade License No. TRAD/DSCC/026884/2022). Official website: https://www.gbrel.com. Independent from Grambangla Holdings and Grambangla Housing.'
  },
  '/contact': {
    title: 'যোগাযোগ ও অফিস ঠিকানা (মিরপুর ডিওএইচএস) | Grambangla Real Estate (GBREL)',
    description: 'Grambangla Real Estate Limited (GBREL) এর কর্পোরেট অফিস: হাউস ৪৬১, রোড ৭, অ্যাভিনিউ ৬, মিরপুর ডিওএইচএস, মিরপুর ১২, ঢাকা ১২১৬। ফোন ও হোয়াটসঅ্যাপ: +8809658510748।',
    heading: 'Contact Grambangla Real Estate Limited (GBREL)',
    summary: 'Corporate & Registered Office: House 461, Road 7, Avenue 6, Mirpur DOHS, Mirpur 12, Dhaka 1216, Bangladesh. Phone & WhatsApp: +8809658510748. Email: grambanglarealestateltd@gmail.com.'
  },
  '/advisors': {
    title: 'প্রপার্টি উপদেষ্টা টিম (Property Advisors) | Grambangla Real Estate (GBREL)',
    description: 'Grambangla Real Estate (GBREL) এর অভিজ্ঞ প্রপার্টি উপদেষ্টাদের সাথে কথা বলুন। জমি ও ফ্ল্যাটের কাগজপত্র যাচাই এবং সাইট ভিজিটে সরাসরি সহায়তা পান।',
    heading: 'Property Advisors — Grambangla Real Estate',
    summary: 'Connect with verified property advisors at Grambangla Real Estate Limited (GBREL) for site visits, title deed verification, and property consultations in Dhaka.'
  },
  '/list-property': {
    title: 'আপনার জমি বা ফ্ল্যাট বিক্রি করুন | Grambangla Real Estate (GBREL)',
    description: 'আপনার জমি, প্লট বা ফ্ল্যাট বিক্রির জন্য Grambangla Real Estate Limited (GBREL) এ জমা দিন। আমরা কাগজপত্র যাচাই করে প্রকৃত ক্রেতার কাছে পৌঁছে দিই।',
    heading: 'List Your Property with Grambangla Real Estate',
    summary: 'Submit your land, plot, or apartment for sale in Dhaka with Grambangla Real Estate Limited (GBREL).'
  },
  '/compare': {
    title: 'প্রপার্টি তুলনা করুন (Compare Properties) | Grambangla Real Estate (GBREL)',
    description: 'Grambangla Real Estate (GBREL) এর একাধিক প্লট, ল্যান্ড শেয়ার ও ফ্ল্যাটের দাম, আয়তন এবং সুবিধাসমূহ পাশাপাশি রেখে তুলনা করুন।',
    heading: 'Compare Properties — Grambangla Real Estate',
    summary: 'Compare verified plots, land shares, and apartments side by side on Grambangla Real Estate (gbrel.com).'
  },
  '/book': {
    title: 'The Hidden Business of Land: জমি-ফ্ল্যাট কেনা-বেচার বাংলা গাইড | Grambangla Real Estate',
    description: 'জমি, ফ্ল্যাট ও ল্যান্ড শেয়ার নিরাপদে কেনা, ন্যায্য দামে বেচা আর এই ব্যবসায় আয়ের পথ নিয়ে উৎসসহ বাংলা ই-বুক — Grambangla Real Estate (GBREL)। ৳১,১০০ নয়, এখন ৳৫০০।',
    ogTitle: 'একটা ভুল কাগজে লাখ টাকা যায়: জমি-ফ্ল্যাটের বাংলা গাইড | Grambangla Real Estate',
    ogDescription: 'কোন মুখী, কোন তলা, কোন কাগজ: টাকা দেওয়ার আগে পড়ে নিন। ৳১,১০০ নয়, এখন ৳৫০০।',
    image: `${SITE_URL}/img/book/og-book.jpg`,
    heading: 'The Hidden Business of Land — Grambangla Real Estate',
    summary: 'A practical Bangla guide to buying and selling land, plots, land shares, and apartments safely in Bangladesh, published by Grambangla Real Estate Limited.'
  },
  '/privacy-policy': {
    title: 'গোপনীয়তা নীতি (Privacy Policy) | Grambangla Real Estate Limited (GBREL)',
    description: 'Grambangla Real Estate Limited (GBREL) এর গোপনীয়তা নীতি এবং ব্যক্তিগত তথ্যের সুরক্ষা সম্পর্কিত বিস্তারিত তথ্য।',
    heading: 'Privacy Policy — Grambangla Real Estate Limited',
    summary: 'Privacy Policy of Grambangla Real Estate Limited (GBREL), Mirpur DOHS, Dhaka 1216.'
  },
  '/terms': {
    title: 'ব্যবহারের শর্তাবলি (Terms of Service) | Grambangla Real Estate Limited (GBREL)',
    description: 'Grambangla Real Estate Limited (GBREL) এর ওয়েবসাইট ও সেবা ব্যবহারের শর্তাবলি।',
    heading: 'Terms of Service — Grambangla Real Estate Limited',
    summary: 'Terms of Service for using https://www.gbrel.com by Grambangla Real Estate Limited (GBREL).'
  },
  '/cookie-policy': {
    title: 'কুকি নীতি (Cookie Policy) | Grambangla Real Estate Limited (GBREL)',
    description: 'Grambangla Real Estate Limited (GBREL) এর কুকি ও ব্রাউজার স্টোরেজ ব্যবহারের নীতিমালা।',
    heading: 'Cookie Policy — Grambangla Real Estate Limited',
    summary: 'Cookie Policy of Grambangla Real Estate Limited (GBREL).'
  }
}

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

function buildMetaTags(opts: {
  title: string
  description: string
  ogTitle?: string
  ogDescription?: string
  url: string
  image?: string
  robots?: string
}) {
  const img = opts.image || `${SITE_URL}/og-default.jpg`
  const ogTitle = opts.ogTitle || opts.title
  const ogDesc = opts.ogDescription || opts.description
  const robots = opts.robots || 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1'

  return [
    `<title>${esc(opts.title)}</title>`,
    `<meta name="description" content="${esc(opts.description)}">`,
    `<meta name="keywords" content="${esc(BRAND_KEYWORDS)}">`,
    `<meta name="robots" content="${esc(robots)}">`,
    `<link rel="canonical" href="${esc(opts.url)}">`,
    `<meta property="og:type" content="website">`,
    `<meta property="og:site_name" content="Grambangla Real Estate">`,
    `<meta property="og:locale" content="bn_BD">`,
    `<meta property="og:title" content="${esc(ogTitle)}">`,
    `<meta property="og:description" content="${esc(ogDesc)}">`,
    `<meta property="og:url" content="${esc(opts.url)}">`,
    `<meta property="og:image" content="${esc(img)}">`,
    `<meta property="og:image:width" content="1200">`,
    `<meta property="og:image:height" content="630">`,
    `<meta name="twitter:card" content="summary_large_image">`,
    `<meta name="twitter:title" content="${esc(ogTitle)}">`,
    `<meta name="twitter:description" content="${esc(ogDesc)}">`,
    `<meta name="twitter:image" content="${esc(img)}">`
  ].join('')
}

function buildNoscriptBlock(heading: string, summary: string, canonicalUrl: string) {
  return `<noscript><main style="max-width:860px;margin:2rem auto;padding:1rem;font-family:sans-serif;line-height:1.6;">` +
    `<h1>${esc(heading)}</h1>` +
    `<p>${esc(summary)}</p>` +
    `<section><h2>Official Brand &amp; Legal Identity</h2>` +
    `<ul>` +
    `<li><strong>Brand Name:</strong> Grambangla Real Estate (গ্রামবাংলা রিয়েল এস্টেট)</li>` +
    `<li><strong>Registered Legal Entity:</strong> Grambangla Real Estate Limited (GBREL)</li>` +
    `<li><strong>RJSC Registration No.:</strong> C-185751/2022 (Incorporated 11 December 2022)</li>` +
    `<li><strong>DSCC e-Trade License No.:</strong> TRAD/DSCC/026884/2022</li>` +
    `<li><strong>Corporate Office:</strong> House 461, Road 7, Avenue 6, Mirpur DOHS, Mirpur 12, Dhaka 1216, Bangladesh</li>` +
    `<li><strong>Phone &amp; WhatsApp:</strong> <a href="tel:+8809658510748">+8809658510748</a></li>` +
    `<li><strong>Email:</strong> <a href="mailto:grambanglarealestateltd@gmail.com">grambanglarealestateltd@gmail.com</a></li>` +
    `<li><strong>Official Website:</strong> <a href="${esc(canonicalUrl)}">${esc(canonicalUrl)}</a></li>` +
    `</ul>` +
    `<p><em>Note: Grambangla Real Estate Limited (gbrel.com) is an independent RJSC-registered company and has no affiliation with Grambangla Holdings or Grambangla Housing.</em></p>` +
    `<nav><a href="${SITE_URL}/">Home</a> | <a href="${SITE_URL}/properties">Properties</a> | <a href="${SITE_URL}/directors">Board of Directors</a> | <a href="${SITE_URL}/legal">Legal Credentials</a> | <a href="${SITE_URL}/advisors">Property Advisors</a> | <a href="${SITE_URL}/contact">Contact</a></nav>` +
    `</section></main></noscript>`
}

// In-memory cache for fast SSR tag generation & data injection (60s TTL)
const propertyCache = new Map<string, { data: any; expires: number }>()

export default defineNitroPlugin(nitroApp => {
  nitroApp.hooks.hook('render:html', async (html, { event }) => {
    // Strip speculative prefetch links from initial head to prevent saturating mobile bandwidth
    html.head = html.head.map(chunk => chunk.replace(/<link rel="prefetch"[^>]*>/g, ''))

    const rawPath = getRequestURL(event).pathname
    const cleanPath = rawPath === '/' ? '/' : rawPath.replace(/\/+$/, '')

    // Private admin/owner routes should never be indexed by search engines
    if (cleanPath.startsWith('/admin') || cleanPath.startsWith('/owner')) {
      html.head = html.head.map(chunk => chunk.replace(REPLACED, ''))
      html.head.push(
        `<title>Grambangla Real Estate — Portal</title>` +
        `<meta name="robots" content="noindex, nofollow">`
      )
      return
    }

    // Global injection: Schema.org structured data for Google Search & AI Search engines (GEO)
    const orgSchemaJson = JSON.stringify(getOrganizationSchema()).replace(/</g, '\\u003c')
    html.head.push(`<script type="application/ld+json">${orgSchemaJson}</script>`)

    // Static known routes
    const staticRoute = STATIC_ROUTE_SEO[cleanPath]
    if (staticRoute) {
      const canonicalUrl = cleanPath === '/' ? `${SITE_URL}/` : `${SITE_URL}${cleanPath}`
      html.head = html.head.map(chunk => chunk.replace(REPLACED, '').replace(/<meta[^>]+property="og:image:(?:width|height)"[^>]*>/g, ''))
      html.head.push(buildMetaTags({
        title: staticRoute.title,
        description: staticRoute.description,
        ogTitle: staticRoute.ogTitle,
        ogDescription: staticRoute.ogDescription,
        url: canonicalUrl,
        image: staticRoute.image
      }))
      if (cleanPath !== '/') {
        const breadcrumbJson = JSON.stringify({
          '@context': 'https://schema.org',
          '@type': 'BreadcrumbList',
          'itemListElement': [
            { '@type': 'ListItem', 'position': 1, 'name': 'Grambangla Real Estate', 'item': `${SITE_URL}/` },
            { '@type': 'ListItem', 'position': 2, 'name': staticRoute.heading, 'item': canonicalUrl }
          ]
        }).replace(/</g, '\\u003c')
        html.head.push(`<script type="application/ld+json">${breadcrumbJson}</script>`)
      }
      html.bodyAppend.push(buildNoscriptBlock(staticRoute.heading, staticRoute.summary, canonicalUrl))
      return
    }

    // Dynamic property detail routes: /properties/:id
    const match = /^\/properties\/(\d+)$/.exec(cleanPath)
    if (!match) {
      const fallbackUrl = `${SITE_URL}${cleanPath}`
      html.head = html.head.map(chunk => chunk.replace(/<link[^>]+rel="canonical"[^>]*>/g, ''))
      html.head.push(`<link rel="canonical" href="${esc(fallbackUrl)}">`)
      return
    }

    const propId = match[1]
    const url = `${SITE_URL}/properties/${propId}`
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
        html.head = html.head.map(chunk => chunk.replace(/<link[^>]+rel="canonical"[^>]*>/g, ''))
        html.head.push(`<link rel="canonical" href="${esc(url)}">`)
        return
      }
    }
    if (!data) {
      html.head = html.head.map(chunk => chunk.replace(/<link[^>]+rel="canonical"[^>]*>/g, ''))
      html.head.push(`<link rel="canonical" href="${esc(url)}">`)
      return
    }

    const p = toPreviewProperty(data)
    const title = `${p.title} | Grambangla Real Estate (GBREL)`
    const description = propertyShareDescription(p)
    const heroImg = absoluteImage(p.image)

    const listingSchema = JSON.stringify({
      '@context': 'https://schema.org',
      '@type': 'RealEstateListing',
      'name': p.title,
      'description': description,
      'url': url,
      'image': heroImg,
      'provider': {
        '@id': 'https://www.gbrel.com/#organization'
      }
    }).replace(/</g, '\\u003c')

    const tags = [
      buildMetaTags({
        title,
        description,
        ogTitle: `${p.title} | Grambangla Real Estate`,
        ogDescription: description,
        url,
        image: heroImg
      }),
      `<script type="application/ld+json">${listingSchema}</script>`,
      // Preload hero image to ensure immediate LCP on mobile
      heroImg ? `<link rel="preload" as="image" href="${esc(heroImg)}" fetchpriority="high">` : '',
      // Inline initial data so client hydration needs 0 network roundtrips
      `<script id="__GBREL_INITIAL_PROPERTY__" type="application/json">${JSON.stringify(data).replace(/</g, '\\u003c')}</script>`
    ].filter(Boolean).join('')

    html.head = html.head.map(chunk => chunk.replace(REPLACED, '').replace(/<meta[^>]+property="og:image:(?:width|height)"[^>]*>/g, ''))
    html.head.push(tags)
    html.bodyAppend.push(buildNoscriptBlock(p.title, description, url))
  })
})

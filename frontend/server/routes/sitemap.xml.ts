import { defineEventHandler, setHeader } from 'h3'

export default defineEventHandler(async (event) => {
  setHeader(event, 'Content-Type', 'application/xml; charset=utf-8')
  setHeader(event, 'Cache-Control', 'public, max-age=3600, s-maxage=3600')

  const baseUrl = 'https://www.gbrel.com'
  const today = new Date().toISOString().split('T')[0]

  // Primary static routes with their priorities and change frequencies
  const staticRoutes = [
    { loc: `${baseUrl}/`, changefreq: 'daily', priority: '1.0', lastmod: today },
    { loc: `${baseUrl}/properties`, changefreq: 'daily', priority: '0.9', lastmod: today },
    { loc: `${baseUrl}/properties?type=Plot`, changefreq: 'weekly', priority: '0.8', lastmod: today },
    { loc: `${baseUrl}/properties?type=Land+Share`, changefreq: 'weekly', priority: '0.8', lastmod: today },
    { loc: `${baseUrl}/properties?type=Flat`, changefreq: 'weekly', priority: '0.8', lastmod: today },
    { loc: `${baseUrl}/properties?type=Duplex`, changefreq: 'weekly', priority: '0.8', lastmod: today },
    { loc: `${baseUrl}/properties?type=Commercial`, changefreq: 'weekly', priority: '0.8', lastmod: today },
    { loc: `${baseUrl}/book`, changefreq: 'weekly', priority: '0.8', lastmod: today },
    { loc: `${baseUrl}/list-property`, changefreq: 'monthly', priority: '0.8', lastmod: today },
    { loc: `${baseUrl}/contact`, changefreq: 'monthly', priority: '0.8', lastmod: today },
    { loc: `${baseUrl}/directors`, changefreq: 'monthly', priority: '0.7', lastmod: today },
    { loc: `${baseUrl}/legal`, changefreq: 'monthly', priority: '0.7', lastmod: today },
    { loc: `${baseUrl}/advisors`, changefreq: 'weekly', priority: '0.7', lastmod: today },
    { loc: `${baseUrl}/compare`, changefreq: 'monthly', priority: '0.6', lastmod: today },
    { loc: `${baseUrl}/privacy-policy`, changefreq: 'yearly', priority: '0.3', lastmod: today },
    { loc: `${baseUrl}/terms`, changefreq: 'yearly', priority: '0.3', lastmod: today },
    { loc: `${baseUrl}/cookie-policy`, changefreq: 'yearly', priority: '0.3', lastmod: today }
  ]

  // Dynamic property routes
  const propertyRoutes: Array<{ loc: string, changefreq: string, priority: string, lastmod: string }> = []
  try {
    const config = useRuntimeConfig()
    const base = String(config.apiBaseServer || 'http://127.0.0.1:8000/api').replace(/\/$/, '')
    const res: any = await $fetch(`${base}/properties`, { timeout: 2500 }).catch(() => null)
    const list = Array.isArray(res?.data) ? res.data : []
    for (const p of list) {
      if (p.id && p.status !== 'Draft' && p.status !== 'Delisted') {
        const lastmod = p.updated_at ? p.updated_at.split('T')[0] : (p.created_at ? p.created_at.split('T')[0] : today)
        propertyRoutes.push({
          loc: `${baseUrl}/properties/${p.id}`,
          changefreq: 'weekly',
          priority: '0.8',
          lastmod
        })
      }
    }
  } catch {
    // API not reachable at build/request time
  }

  // Fallback to ensure property IDs are represented if API call was offline
  if (propertyRoutes.length === 0) {
    for (let id = 1; id <= 6; id++) {
      propertyRoutes.push({
        loc: `${baseUrl}/properties/${id}`,
        changefreq: 'weekly',
        priority: '0.8',
        lastmod: today
      })
    }
  }

  const allRoutes = [...staticRoutes, ...propertyRoutes]

  const xmlUrls = allRoutes.map(r => `  <url>
    <loc>${r.loc}</loc>
    <lastmod>${r.lastmod}</lastmod>
    <changefreq>${r.changefreq}</changefreq>
    <priority>${r.priority}</priority>
  </url>`).join('\n')

  return `<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
${xmlUrls}
</urlset>`
})

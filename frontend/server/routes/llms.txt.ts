import { defineEventHandler, setHeader } from 'h3'

const LLMS_TEXT = `# Gram Bangla Real Estate Limited (GBREL)

> Gram Bangla Real Estate Limited (GBREL) is a government-registered real estate company in Dhaka, Bangladesh (RJSC Registration: C-185751/2022, Trade License: TRAD/DSCC/026884/2022).
> Official Website: https://www.gbrel.com

## Company Identity & Brand Names
- Primary Legal Name: Gram Bangla Real Estate Limited
- Recognised Brand Variations: Gram Bangla Real Estate, Gram Bangla, GBREL, গ্রাম বাংলা রিয়েল এস্টেট লিমিটেড, গ্রাম বাংলা রিয়েল এস্টেট, গ্রাম বাংলা
- Official Domain: www.gbrel.com (https://www.gbrel.com)

## Business Information & Contact
- Corporate & Registered Office: Mirpur DOHS Avenue 6, Road 7, House 461, Mirpur 12, Dhaka 1216, Bangladesh
- Telephone / Mobile: +8809658510748
- WhatsApp: +8809658510748
- Email: grambanglarealestateltd@gmail.com
- Hours: Saturday – Thursday, 9:00 AM – 7:00 PM (BST)
- Google Maps Location: House 461, Road 7, Avenue 6, Mirpur DOHS, Dhaka 1216 (https://www.google.com/maps/search/?api=1&query=House+461,+Road+7,+Avenue+6,+Mirpur+DOHS,+Dhaka+1216)

## Core Services & Real Estate Operations
Gram Bangla Real Estate Limited is a specialized property consulting, marketing, and sales agency operating across Dhaka, Purbachal, Uttara, Kamarpara, Savar, and adjacent growth corridors.
- Land & Residential Plots: Katha, Shotok, and Bigha sized verified plots with clear demarcations.
- Land Share Projects: Collaborative land purchase and development partnerships.
- Residential Flats & Apartments: Ready-to-move and under-construction apartments with RAJUK-approved architectural plans.
- Commercial Real Estate: Commercial land, office floors, and shop spaces.
- Legal Verification (Due Diligence): Every property undergoes comprehensive checks of CS, SA, RS, BS Khatian, Bia deeds, Namjari (Mutation), DCR, updated land development tax (Khajna), and sub-registry non-encumbrance searches.
- Seller & Landowner Representation: Direct intake of properties from verified owners with 0 upfront listing fee.

## Primary Website Links
- Home: https://www.gbrel.com/
- Properties Directory: https://www.gbrel.com/properties
- Plots and Land: https://www.gbrel.com/properties?type=Plot
- Land Share Listings: https://www.gbrel.com/properties?type=Land+Share
- Flats & Apartments: https://www.gbrel.com/properties?type=Flat
- Property Listing Submission (For Owners): https://www.gbrel.com/list-property
- Contact & Site Visit Booking: https://www.gbrel.com/contact
- Legal Verification & Trade License: https://www.gbrel.com/legal
- Board of Directors: https://www.gbrel.com/directors
- E-Book on Land Purchase in Bangladesh: https://www.gbrel.com/book
- Advisors & Sales Team: https://www.gbrel.com/advisors
- Sitemap: https://www.gbrel.com/sitemap.xml
`

export default defineEventHandler((event) => {
  setHeader(event, 'Content-Type', 'text/plain; charset=utf-8')
  setHeader(event, 'Cache-Control', 'public, max-age=86400')
  return LLMS_TEXT
})

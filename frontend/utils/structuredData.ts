/**
 * Schema.org structured data generators for SEO, Local SEO, Knowledge Graph Entity Disambiguation, and AI Search / GEO.
 * Primary Brand Display Name: "Grambangla Real Estate"
 * Registered Legal Entity: "Grambangla Real Estate Limited" (GBREL)
 * Disambiguation: Strictly distinct from and unaffiliated with "Grambangla Holdings" or "Grambangla Housing".
 */
export function getOrganizationSchema() {
  return {
    '@context': 'https://schema.org',
    '@graph': [
      {
        '@type': ['RealEstateAgent', 'LocalBusiness', 'Organization'],
        '@id': 'https://www.gbrel.com/#organization',
        'name': 'Grambangla Real Estate',
        'legalName': 'Grambangla Real Estate Limited',
        'alternateName': [
          'Grambangla Real Estate',
          'Grambangla Real Estate Limited',
          'Grambangla Real Estate Ltd.',
          'Gram Bangla Real Estate',
          'Gram Bangla Real Estate Limited',
          'GBREL',
          'গ্রামবাংলা রিয়েল এস্টেট',
          'গ্রামবাংলা রিয়েল এস্টেট লিমিটেড',
          'গ্রাম বাংলা রিয়েল এস্টেট',
          'গ্রাম বাংলা রিয়েল এস্টেট লিমিটেড'
        ],
        'disambiguatingDescription': 'Grambangla Real Estate (Grambangla Real Estate Limited, GBREL, official website https://www.gbrel.com) is an independent RJSC-registered real estate company in Dhaka, Bangladesh (RJSC Registration No. C-185751/2022, DSCC e-Trade License No. TRAD/DSCC/026884/2022) headquartered at House 461, Road 7, Avenue 6, Mirpur DOHS, Mirpur 12, Dhaka 1216. Grambangla Real Estate Limited is not affiliated with, connected to, or related to Grambangla Holdings or Grambangla Housing.',
        'url': 'https://www.gbrel.com/',
        'foundingDate': '2022-12-11',
        'identifier': [
          {
            '@type': 'PropertyValue',
            'name': 'RJSC Certificate of Incorporation',
            'propertyID': 'RJSC',
            'value': 'C-185751/2022'
          },
          {
            '@type': 'PropertyValue',
            'name': 'DSCC e-Trade License',
            'propertyID': 'DSCC-Trade-License',
            'value': 'TRAD/DSCC/026884/2022'
          }
        ],
        'logo': {
          '@type': 'ImageObject',
          '@id': 'https://www.gbrel.com/#logo',
          'url': 'https://www.gbrel.com/logo.png',
          'contentUrl': 'https://www.gbrel.com/logo.png',
          'caption': 'Grambangla Real Estate (Grambangla Real Estate Limited)'
        },
        'image': 'https://www.gbrel.com/og-default.jpg',
        'email': 'grambanglarealestateltd@gmail.com',
        'telephone': '+8809658510748',
        'priceRange': '৳৳৳',
        'address': {
          '@type': 'PostalAddress',
          'streetAddress': 'House 461, Road 7, Avenue 6, Mirpur DOHS',
          'addressLocality': 'Mirpur 12, Dhaka',
          'addressRegion': 'Dhaka Division',
          'postalCode': '1216',
          'addressCountry': 'BD'
        },
        'geo': {
          '@type': 'GeoCoordinates',
          'latitude': 23.8368,
          'longitude': 90.3685
        },
        'hasMap': 'https://www.google.com/maps/search/?api=1&query=House+461,+Road+7,+Avenue+6,+Mirpur+DOHS,+Dhaka+1216',
        'openingHoursSpecification': [
          {
            '@type': 'OpeningHoursSpecification',
            'dayOfWeek': ['Saturday', 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday'],
            'opens': '09:00',
            'closes': '19:00'
          }
        ],
        'areaServed': [
          { '@type': 'City', 'name': 'Dhaka' },
          { '@type': 'AdministrativeArea', 'name': 'Dhaka Division' },
          { '@type': 'Country', 'name': 'Bangladesh' }
        ],
        'founder': {
          '@type': 'Person',
          'name': 'MD Abdul Quder Talukder',
          'jobTitle': 'Founder & Managing Director',
          'worksFor': { '@id': 'https://www.gbrel.com/#organization' }
        },
        'member': [
          {
            '@type': 'Person',
            'name': 'MD Abdul Quder Talukder',
            'jobTitle': 'Founder & Managing Director'
          },
          {
            '@type': 'Person',
            'name': 'Engr. Siam Talukder',
            'jobTitle': 'Deputy Managing Director'
          },
          {
            '@type': 'Person',
            'name': 'Engr. Sher Ali Khan',
            'jobTitle': 'Co-Founder & Chief Technology Officer (CTO)'
          },
          {
            '@type': 'Person',
            'name': 'Md. Abu Hanif',
            'jobTitle': 'Director (Admin & Accounts)'
          },
          {
            '@type': 'Person',
            'name': 'Md. Rubel Hawlader',
            'jobTitle': 'Director (Project & Transport)'
          }
        ],
        'description': 'Grambangla Real Estate (Grambangla Real Estate Limited — GBREL) is an RJSC-registered real estate company in Dhaka, Bangladesh (RJSC Reg: C-185751/2022, Trade License: TRAD/DSCC/026884/2022) offering legally verified plots, land shares, duplexes, and apartments in Dhaka.',
        'sameAs': [
          'https://www.facebook.com/grambanglarealestate',
          'https://www.gbrel.com'
        ]
      },
      {
        '@type': 'WebSite',
        '@id': 'https://www.gbrel.com/#website',
        'url': 'https://www.gbrel.com/',
        'name': 'Grambangla Real Estate',
        'alternateName': [
          'Grambangla Real Estate Limited',
          'Gram Bangla Real Estate',
          'Gram Bangla Real Estate Limited',
          'GBREL',
          'gbrel.com',
          'গ্রামবাংলা রিয়েল এস্টেট'
        ],
        'description': 'Official website of Grambangla Real Estate (Grambangla Real Estate Limited, GBREL) — Verified plots, land shares, duplexes, and apartments in Dhaka, Bangladesh.',
        'publisher': {
          '@id': 'https://www.gbrel.com/#organization'
        },
        'potentialAction': {
          '@type': 'SearchAction',
          'target': {
            '@type': 'EntryPoint',
            'urlTemplate': 'https://www.gbrel.com/properties?q={search_term_string}'
          },
          'query-input': 'required name=search_term_string'
        }
      }
    ]
  }
}

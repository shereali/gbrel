/**
 * Schema.org structured data generators for SEO, Local SEO, and AI Search / GEO
 * Target keywords: "Gram Bangla Real Estate Limited", "Gram Bangla Real Estate", "Gram Bangla", "GBREL", "www.gbrel.com"
 */
export function getOrganizationSchema() {
  return {
    '@context': 'https://schema.org',
    '@graph': [
      {
        '@type': ['RealEstateAgent', 'LocalBusiness', 'Organization'],
        '@id': 'https://www.gbrel.com/#organization',
        'name': 'Gram Bangla Real Estate Limited',
        'alternateName': [
          'Gram Bangla Real Estate',
          'Gram Bangla',
          'GBREL',
          'গ্রাম বাংলা রিয়েল এস্টেট লিমিটেড',
          'গ্রাম বাংলা রিয়েল এস্টেট',
          'গ্রাম বাংলা'
        ],
        'legalName': 'Gram Bangla Real Estate Limited',
        'url': 'https://www.gbrel.com/',
        'logo': {
          '@type': 'ImageObject',
          '@id': 'https://www.gbrel.com/#logo',
          'url': 'https://www.gbrel.com/logo.png',
          'contentUrl': 'https://www.gbrel.com/logo.png',
          'caption': 'Gram Bangla Real Estate Limited'
        },
        'image': 'https://www.gbrel.com/og-default.jpg',
        'email': 'grambanglarealestateltd@gmail.com',
        'telephone': '+8809658510748',
        'priceRange': '৳৳৳',
        'address': {
          '@type': 'PostalAddress',
          'streetAddress': 'Mirpur DOHS Avenue 6, Road 7, House 461',
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
        'description': 'Gram Bangla Real Estate Limited (GBREL) is a trusted, RJSC-registered real estate company in Dhaka, Bangladesh (RJSC: C-185751/2022, Trade License: TRAD/DSCC/026884/2022). Offering verified plots, land shares, duplexes, and flats in Dhaka with complete legal verification.',
        'sameAs': [
          'https://www.facebook.com/grambanglarealestate',
          'https://www.gbrel.com'
        ]
      },
      {
        '@type': 'WebSite',
        '@id': 'https://www.gbrel.com/#website',
        'url': 'https://www.gbrel.com/',
        'name': 'Gram Bangla Real Estate Limited',
        'alternateName': [
          'Gram Bangla Real Estate',
          'Gram Bangla',
          'GBREL',
          'www.gbrel.com'
        ],
        'description': 'Official portal of Gram Bangla Real Estate Limited — Verified land, plots, land shares, and apartments in Bangladesh.',
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

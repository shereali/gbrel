import { ref, computed } from 'vue'

export interface PropertyItem {
  id: number
  title: string
  slug: string
  tagline: string
  description: string
  address: string
  city: string
  state: string // Division/State in Bangladesh: Dhaka North, Dhaka South, Chittagong, Sylhet, Cox's Bazar, etc.
  areaName: string
  price: number
  priceUnit?: string
  buyerDetails?: Record<string, string | number | null>
  pricePrefix?: string
  listingType: 'Sale' | 'Lease' | 'Delisted'
  propertyType: 'Flat' | 'Plot' | 'Land' | 'Land Share' | 'Duplex' | 'Hotel' | 'Commercial' | 'Penthouse'
  status: 'Active' | 'Sold' | 'Delisted' | 'Under Offer' | 'Draft'
  bedrooms: number
  bathrooms: number
  balconies?: number
  squareFootage?: number
  landSize?: number
  landUnit?: 'Katha' | 'Bigha' | 'Shotok' | 'Decimal' | 'Sqft'
  garage?: number
  parking: number
  floorNumber?: number
  totalFloors?: number
  facing?: 'North' | 'South' | 'East' | 'West' | 'South-East' | 'North-East'
  completionStatus: 'Ready' | 'Under Construction' | 'Upcoming Project'
  yearBuilt?: number
  isFeatured: boolean
  isRajukApproved: boolean
  isVerified: boolean
  hasOpenHouse: boolean
  openHouseDate?: string
  lat: number
  lng: number
  images: string[]
  featureImage?: string
  gallery?: string[]
  amenities: string[]
  documentsVerified: string[]
  brochureUrl?: string
  hidePrice?: boolean
  priceDisplayText?: string
  hideAgentPhoto?: boolean
  hideAgentContact?: boolean
  hideExactAddress?: boolean
  hideFloorPlan?: boolean
  hideMortgageCalculator?: boolean
  brochuresVault?: any[]
  agentId: number
  history: Array<{
    id: number
    date: string
    event: string
    price: number
    status: string
    notes: string
  }>
  estimates: {
    marketEstimate: number
    lowEstimate: number
    highEstimate: number
    annualGrowthPct: number
    monthlyRentEstimate: number
    annualRoiPct: number
    pricePerSqftArea: number
  }
  comparables: Array<{
    id: number
    title: string
    address: string
    price: number
    sqft: number
    bedrooms: number
    bathrooms: number
    distanceKm: number
    imageUrl: string
  }>
  schools: Array<{
    name: string
    type: string
    rating: number
    distanceKm: number
    travelMins: number
    grades: string
  }>
  community: {
    neighborhood: string
    metroDistanceKm: number
    nearestMetroStation: string
    hospitalDistanceKm: number
    nearestHospital: string
    airportDistanceKm: number
    safetyRating: string
    amenitiesOverview: string
    transitOverview: string
  }
}

export interface AgentItem {
  id: number
  name: string
  title: string
  agency: string
  state: string
  city: string
  photo: string
  email: string
  phone: string
  whatsapp: string
  bio: string
  experienceYears: number
  rating: number
  reviewCount: number
  activeListingsCount: number
  specialties: string[]
}

const agentsData = ref<AgentItem[]>([
  {
    id: 1,
    name: 'Tanvir Ahmed',
    title: 'Senior Luxury Real Estate Advisor',
    agency: 'GBREL Premier Advisory',
    state: 'Dhaka North',
    city: 'Dhaka',
    photo: 'https://images.unsplash.com/photo-1560250097-0b93528c311a?q=80&w=600&auto=format&fit=crop',
    email: 'tanvir.ahmed@gbrel.com',
    phone: '+880 1819-987654',
    whatsapp: '+8801819987654',
    bio: 'Over 12 years of specialized experience in Diplomatic Zone residences, Gulshan-Banani luxury penthouses, and Purbachal high-value institutional plots. Certified RAJUK Real Estate Valuer.',
    experienceYears: 12,
    rating: 4.9,
    reviewCount: 84,
    activeListingsCount: 14,
    specialties: ['Gulshan & Banani Penthouses', 'Purbachal Sector Plots', 'NRI Investment Advisory']
  },
  {
    id: 2,
    name: 'Nusrat Jahan Chowdhury',
    title: 'Commercial & Coastal Estate Director',
    agency: 'GBREL Coastal & Commercial',
    state: 'Chittagong',
    city: 'Chittagong & Cox\'s Bazar',
    photo: 'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?q=80&w=600&auto=format&fit=crop',
    email: 'nusrat.jahan@gbrel.com',
    phone: '+880 1711-889900',
    whatsapp: '+8801711889900',
    bio: 'Specialist in Cox’s Bazar Marine Drive beachfront resorts, hotel suite fractions, and Agrabad/Khulshi prime commercial acquisitions. Fluent in English, Bengali, and Chittagonian.',
    experienceYears: 9,
    rating: 4.85,
    reviewCount: 62,
    activeListingsCount: 9,
    specialties: ['Cox\'s Bazar Hotel Suites', 'Chittagong Khulshi Duplexes', 'Industrial Port Warehouses']
  },
  {
    id: 3,
    name: 'Syed Mahbubur Rahman',
    title: 'Land Bank & Estate Consultant',
    agency: 'GBREL Agro & Institutional Lands',
    state: 'Dhaka South',
    city: 'Dhaka & Sylhet',
    photo: 'https://images.unsplash.com/photo-1519085360753-af0119f7cbe7?q=80&w=600&auto=format&fit=crop',
    email: 'mahbub.rahman@gbrel.com',
    phone: '+880 1912-778899',
    whatsapp: '+8801912778899',
    bio: 'Lead consultant for large acreage freehold lands, tea estates in Sylhet, and residential townships across Jalshiri and Keraniganj. Expert in mutation records and land registry vetting.',
    experienceYears: 15,
    rating: 5.0,
    reviewCount: 110,
    activeListingsCount: 11,
    specialties: ['Sylhet Tea Valley Resorts', 'Jalshiri Abashon Plots', 'Freehold Agro & Industrial Lands']
  }
])

const propertiesData = ref<PropertyItem[]>([
  {
    id: 1,
    title: 'Lakeview Penthouse at Gulshan-2 Diplomatic Zone',
    slug: 'lakeview-penthouse-gulshan-2',
    tagline: 'Panoramic Lakefront Skyline View with Private Terrace & Infinity Pool',
    description: 'An architectural masterpiece located in the heart of Gulshan-2 diplomatic enclave. Designed by internationally acclaimed architects, this penthouse offers an expansive 4,850 sq ft of ultra-luxurious living space with 360-degree views of Gulshan Lake. Features floor-to-ceiling soundproof acoustic glass, Italian marble flooring, smart home automation, private elevator access, 24/7 full generator backup, and 3 reserved basement parking spots.',
    address: 'Road 71, Block NW(H), Gulshan-2, Dhaka-1212',
    city: 'Dhaka',
    state: 'Dhaka North',
    areaName: 'Gulshan-2',
    price: 78000000, // ৳ 7.80 Crore
    pricePrefix: 'Fixed Price',
    listingType: 'Sale',
    propertyType: 'Penthouse',
    status: 'Active',
    bedrooms: 4,
    bathrooms: 5,
    balconies: 4,
    squareFootage: 4850,
    garage: 3,
    parking: 3,
    floorNumber: 14,
    totalFloors: 14,
    facing: 'South-East',
    completionStatus: 'Ready',
    yearBuilt: 2024,
    isFeatured: true,
    isRajukApproved: true,
    isVerified: true,
    hasOpenHouse: true,
    openHouseDate: 'Saturday, 11:00 AM - 4:00 PM',
    lat: 23.7925,
    lng: 90.4167,
    images: [
      'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?q=80&w=1600&auto=format&fit=crop',
      'https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?q=80&w=1200&auto=format&fit=crop',
      'https://images.unsplash.com/photo-1600566753376-12c8ab7fb75b?q=80&w=1200&auto=format&fit=crop',
      'https://images.unsplash.com/photo-1600585154526-990dced4db0d?q=80&w=1200&auto=format&fit=crop'
    ],
    amenities: [
      'Private High-Speed Elevator', 'Lake-Facing Infinity Pool', '24/7 Full Power Generator Backup',
      'Central VRF Air Conditioning', 'Italian Marble Flooring', 'Smart Home Automation',
      'Rooftop BBQ Lounge', '3 Reserved Basement Parking', 'Gymnasium & Steam Bath',
      '24/7 Multi-Tier CCTV Security', 'Intercom & Video Doorphone', 'Solar Backup Panels'
    ],
    documentsVerified: [
      'RAJUK Approved Building Plan (14-Storey Approval)',
      'Mutation & Updated Khajna (Tax) Receipt',
      'C/S, R/S, B/S & City Survey Khatian Cleared',
      'Freehold Clear Title with Zero Legal Encumbrance',
      'Fire Safety & Civil Aviation NOC Certified'
    ],
    agentId: 1,
    history: [
      { id: 1, date: 'Jan 2026', event: 'Listed for Sale', price: 78000000, status: 'Active', notes: 'Exclusively listed on GBREL' },
      { id: 2, date: 'Aug 2025', event: 'Construction & Finishing Completed', price: 75000000, status: 'Ready', notes: 'Interior marble & VRF installed' },
      { id: 3, date: 'Mar 2024', event: 'Pre-construction Valuation', price: 68000000, status: 'Off-Plan', notes: 'Early bird architectural launch' }
    ],
    estimates: {
      marketEstimate: 78500000,
      lowEstimate: 76000000,
      highEstimate: 82000000,
      annualGrowthPct: 12.4,
      monthlyRentEstimate: 350000, // ৳ 3.5 Lakh/month
      annualRoiPct: 5.4,
      pricePerSqftArea: 16082
    },
    comparables: [
      {
        id: 101,
        title: 'Baridhara Diplomatic Penthouse',
        address: 'Road 8, Baridhara Diplomatic Zone, Dhaka',
        price: 85000000,
        sqft: 5200,
        bedrooms: 4,
        bathrooms: 5,
        distanceKm: 1.2,
        imageUrl: 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?q=80&w=800&auto=format&fit=crop'
      },
      {
        id: 102,
        title: 'Banani Lakefront Duplex Suite',
        address: 'Road 11, Banani, Dhaka',
        price: 72000000,
        sqft: 4400,
        bedrooms: 4,
        bathrooms: 4,
        distanceKm: 1.8,
        imageUrl: 'https://images.unsplash.com/photo-1600573472592-401b489a3cdc?q=80&w=800&auto=format&fit=crop'
      }
    ],
    schools: [
      { name: 'Scholastica Senior Campus', type: 'English Medium (Cambridge)', rating: 4.9, distanceKm: 1.1, travelMins: 4, grades: 'Playgroup to A Levels' },
      { name: 'The American International School (AISD)', type: 'International Baccalaureate', rating: 5.0, distanceKm: 2.3, travelMins: 8, grades: 'K-12' },
      { name: 'North South University (NSU)', type: 'Leading Private University', rating: 4.8, distanceKm: 3.4, travelMins: 12, grades: 'Undergraduate & Graduate' }
    ],
    community: {
      neighborhood: 'Gulshan-2 Diplomatic Enclave',
      metroDistanceKm: 2.8,
      nearestMetroStation: 'MRT Line-6 Karwan Bazar / Farmgate (Feeder Connection)',
      hospitalDistanceKm: 1.5,
      nearestHospital: 'United Hospital Gulshan & Evercare Dhaka',
      airportDistanceKm: 7.5,
      safetyRating: 'Diplomatic Zone Top Tier (High Police & CCTV Patrolling)',
      amenitiesOverview: 'Surrounded by premier dining, embassies, Unimart Gulshan, upscale salons, and lakeside jogging track.',
      transitOverview: 'Direct connection to Kemal Ataturk Avenue, Gulshan Avenue, and Madani Avenue Expressway.'
    }
  },
  {
    id: 2,
    title: '10 Katha Corner Residential Plot in Purbachal Sector 17',
    slug: '10-katha-corner-plot-purbachal-sector-17',
    tagline: '100% Boundary Demarcated, 100ft Wide Road Frontage, Ready for Immediate Handover',
    description: 'A prestigious 10 Katha south-east corner plot in the prime residential hub of Purbachal New Town (Sector 17, near Lake and Commercial Boulevard). Clear boundary pillars, zero litigation, original RAJUK allotment letter with complete mutation records. Ideal for luxury single-family mansion, multi-storey residential complex, or high-yield capital investment in Dhaka’s future mega hub.',
    address: 'Road 302, Sector 17, Purbachal New Town, Dhaka',
    city: 'Dhaka',
    state: 'Dhaka North',
    areaName: 'Purbachal New Town',
    price: 36000000, // ৳ 3.60 Crore (36 Lakh/katha)
    priceUnit: 'Total (৳ 36 Lakh / Katha)',
    pricePrefix: 'Negotiable',
    listingType: 'Sale',
    propertyType: 'Plot',
    status: 'Active',
    bedrooms: 0,
    bathrooms: 0,
    landSize: 10,
    landUnit: 'Katha',
    parking: 0,
    facing: 'South-East',
    completionStatus: 'Ready',
    isFeatured: true,
    isRajukApproved: true,
    isVerified: true,
    hasOpenHouse: false,
    lat: 23.8385,
    lng: 90.5224,
    images: [
      'https://images.unsplash.com/photo-1500382017468-9049fed747ef?q=80&w=1600&auto=format&fit=crop',
      'https://images.unsplash.com/photo-1524813686514-a57563d77d61?q=80&w=1200&auto=format&fit=crop',
      'https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?q=80&w=1200&auto=format&fit=crop'
    ],
    amenities: [
      '100ft Wide Main Connecting Road', '100% Demarcated Boundary Pillars', 'Direct Lakeside Walkway Access',
      'Underground Utility Line Provisions', 'Zero Waterlogging Elevated Elevation', 'Immediate Building Plan Permission'
    ],
    documentsVerified: [
      'Original RAJUK Allotment Letter & Lease Deed',
      'Mutation Parch & Up-to-date Dakhila Tax Token',
      'C/S, R/S, B/S & Purbachal Master Plan Verified',
      'Physical Demarcation & Soil Test Certified'
    ],
    agentId: 1,
    history: [
      { id: 1, date: 'Feb 2026', event: 'Listed on GBREL', price: 36000000, status: 'Active', notes: 'Exclusive prime corner listing' },
      { id: 2, date: '2024', event: 'Sector 17 Infrastructure Handover', price: 31000000, status: 'Ready', notes: 'Paved road and utilities connected' }
    ],
    estimates: {
      marketEstimate: 37500000,
      lowEstimate: 35000000,
      highEstimate: 40000000,
      annualGrowthPct: 16.8,
      monthlyRentEstimate: 0,
      annualRoiPct: 16.8,
      pricePerSqftArea: 5000
    },
    comparables: [
      {
        id: 201,
        title: '7.5 Katha Plot in Purbachal Sector 20',
        address: 'Sector 20, Purbachal New Town',
        price: 27000000,
        sqft: 5400,
        bedrooms: 0,
        bathrooms: 0,
        distanceKm: 2.1,
        imageUrl: 'https://images.unsplash.com/photo-1500382017468-9049fed747ef?q=80&w=800&auto=format&fit=crop'
      }
    ],
    schools: [
      { name: 'Purbachal International School Campus', type: 'English Medium', rating: 4.7, distanceKm: 1.5, travelMins: 5, grades: 'Playgroup to Grade 10' },
      { name: 'Dhaka University Secondary Campus Site', type: 'Higher Education Hub', rating: 4.9, distanceKm: 3.2, travelMins: 8, grades: 'Higher Studies' }
    ],
    community: {
      neighborhood: 'Purbachal Sector 17 Modern Township',
      metroDistanceKm: 4.5,
      nearestMetroStation: 'Proposed MRT Line-5 Purbachal Station / 300ft Expressway',
      hospitalDistanceKm: 5.0,
      nearestHospital: 'Evercare Hospital Dhaka & Purbachal Specialized Medical Center',
      airportDistanceKm: 12.0,
      safetyRating: 'High - Managed by RAJUK and Purbachal Security Taskforce',
      amenitiesOverview: 'Adjacent to Purbachal Central Park, international exhibition center, and golf course.',
      transitOverview: 'Direct express access via 300 Feet Dhaka-Purbachal Expressway (8-lane Highway).'
    }
  },
  {
    id: 3,
    title: 'Sea-Facing Luxury Suite & Fractional Asset in Marine Drive',
    slug: 'sea-facing-suite-marine-drive-coxsbazar',
    tagline: 'Unobstructed Bay of Bengal View with 14% Assured Annual Rental Yield',
    description: 'Invest in the most lucrative hospitality real estate asset in Cox\'s Bazar. Located right on the world\'s longest scenic Marine Drive, this luxury sea-view suite offers an upscale getaway combined with high passive income managed by a 5-star hotel operator. Owners enjoy 30 days complimentary VIP holiday stay per year, full spa, infinity swimming pool, heli-pad, and private beach lounge access.',
    address: 'Marine Drive Road, Kolatoli-Inani Corridor, Cox\'s Bazar',
    city: 'Cox\'s Bazar',
    state: 'Chittagong',
    areaName: 'Marine Drive',
    price: 9500000, // ৳ 95 Lakh
    pricePrefix: 'High Yield Asset',
    listingType: 'Sale',
    propertyType: 'Hotel',
    status: 'Active',
    bedrooms: 1,
    bathrooms: 1,
    balconies: 1,
    squareFootage: 850,
    parking: 1,
    floorNumber: 6,
    totalFloors: 10,
    facing: 'West',
    completionStatus: 'Ready',
    yearBuilt: 2025,
    isFeatured: true,
    isRajukApproved: true,
    isVerified: true,
    hasOpenHouse: true,
    openHouseDate: 'Daily VIP Tours available with airport shuttle',
    lat: 21.4272,
    lng: 91.9800,
    images: [
      'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?q=80&w=1600&auto=format&fit=crop',
      'https://images.unsplash.com/photo-1566073771259-6a8506099945?q=80&w=1200&auto=format&fit=crop',
      'https://images.unsplash.com/photo-1520250497591-112f2f40a3f4?q=80&w=1200&auto=format&fit=crop'
    ],
    amenities: [
      'Direct Panoramic Ocean Beachfront', 'Private Sunset Balcony', 'Infinity Rooftop Swimming Pool',
      '5-Star Multi-Cuisine Restaurants', 'Heli-Pad Facility', 'Full Furnished Luxury Interior',
      '30 Days Free Owner Vacation Stay', 'Quarterly Revenue Profit Disbursement', '24/7 Concierge Service'
    ],
    documentsVerified: [
      'Ministry of Tourism & Civil Aviation NOC',
      'Environment Clearance Department (DOE) Approved',
      'Sub-Registry Sub-Deed with Clear Title Deed',
      'Quarterly Financial Audit Assurance Statement'
    ],
    agentId: 2,
    history: [
      { id: 1, date: 'Jan 2026', event: 'New Share Tranche Released', price: 9500000, status: 'Active', notes: 'High season occupancy averaging 88%' },
      { id: 2, date: '2025', event: 'Resort Grand Opening', price: 8500000, status: 'Completed', notes: 'Operated by International Hospitality Partner' }
    ],
    estimates: {
      marketEstimate: 9800000,
      lowEstimate: 9200000,
      highEstimate: 10500000,
      annualGrowthPct: 14.5,
      monthlyRentEstimate: 110000, // ৳ 1.10 Lakh avg monthly revenue share
      annualRoiPct: 14.0,
      pricePerSqftArea: 11176
    },
    comparables: [
      {
        id: 301,
        title: 'Inani Beach Luxury Villa Suite',
        address: 'Inani Beach Marine Drive, Cox\'s Bazar',
        price: 12000000,
        sqft: 1100,
        bedrooms: 2,
        bathrooms: 2,
        distanceKm: 4.5,
        imageUrl: 'https://images.unsplash.com/photo-1571896349842-33c89424de2d?q=80&w=800&auto=format&fit=crop'
      }
    ],
    schools: [
      { name: 'Cox\'s Bazar International Grammar School', type: 'English Medium', rating: 4.6, distanceKm: 4.2, travelMins: 10, grades: 'K-10' }
    ],
    community: {
      neighborhood: 'Marine Drive Tourism & Hotel Enclave',
      metroDistanceKm: 0,
      nearestMetroStation: 'Cox\'s Bazar New Iconic Railway Station (15 Mins)',
      hospitalDistanceKm: 3.5,
      nearestHospital: 'Cox\'s Bazar Sadar District Hospital & Fuad Al-Khatib Medical',
      airportDistanceKm: 5.8,
      safetyRating: 'Tourist Police & 24/7 Beach Security Patrol Enclave',
      amenitiesOverview: 'Direct walk to Himchari National Park, parasailing points, and beachfront seafood cafes.',
      transitOverview: 'Scenic Marine Drive Highway connecting Cox\'s Bazar city to Teknaf.'
    }
  },
  {
    id: 4,
    title: 'Luxury South-Facing Duplex in Dhanmondi Lake Road 8/A',
    slug: 'south-facing-duplex-dhanmondi-8a',
    tagline: 'Quiet Residential Haven with Private Terrace Garden & Solar System',
    description: 'An immaculate south-facing luxury duplex situated in the most desirable and quiet enclave of Dhanmondi (Road 8/A, steps from Dhanmondi Lake). Spanning two levels (7th & 8th floor), this residence includes 4 expansive bedrooms with en-suite walk-in closets, bespoke teak wood fittings, imported modular kitchen, servant quarters, 2 dedicated basement parking bays, and 100% backup generator.',
    address: 'Road 8/A, Dhanmondi R/A, Dhaka-1209',
    city: 'Dhaka',
    state: 'Dhaka South',
    areaName: 'Dhanmondi',
    price: 42500000, // ৳ 4.25 Crore
    pricePrefix: 'Fixed Price',
    listingType: 'Sale',
    propertyType: 'Duplex',
    status: 'Active',
    bedrooms: 4,
    bathrooms: 4,
    balconies: 3,
    squareFootage: 3200,
    garage: 2,
    parking: 2,
    floorNumber: 7,
    totalFloors: 9,
    facing: 'South',
    completionStatus: 'Ready',
    yearBuilt: 2024,
    isFeatured: false,
    isRajukApproved: true,
    isVerified: true,
    hasOpenHouse: true,
    openHouseDate: 'Friday, 3:00 PM - 6:00 PM',
    lat: 23.7465,
    lng: 90.3760,
    images: [
      'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?q=80&w=1600&auto=format&fit=crop',
      'https://images.unsplash.com/photo-1600566753190-17f0baa2a6c3?q=80&w=1200&auto=format&fit=crop',
      'https://images.unsplash.com/photo-1600210492486-724fe5c67fb0?q=80&w=1200&auto=format&fit=crop'
    ],
    amenities: [
      'Duplex Double Height Living Room', 'Lakeside Walking Distance', '24/7 Full Backup Generator',
      'Custom Teak Wood Interior', 'Imported Granite Kitchen', '2 Reserved Basement Car Parking',
      'Rooftop Community Garden', 'Dedicated Security Guard Force', 'CCTV on Every Floor'
    ],
    documentsVerified: [
      'RAJUK Approved Structural & Architectural Plan',
      'Mutation & Land Tax Certificate Up-to-Date',
      'Freehold Land Share Ratio Registered',
      'Fire Safety Department Clearance'
    ],
    agentId: 1,
    history: [
      { id: 1, date: 'Feb 2026', event: 'Listed on GBREL', price: 42500000, status: 'Active', notes: 'Prime Dhanmondi 8/A lakefront property' }
    ],
    estimates: {
      marketEstimate: 43000000,
      lowEstimate: 41000000,
      highEstimate: 45000000,
      annualGrowthPct: 9.8,
      monthlyRentEstimate: 160000,
      annualRoiPct: 4.5,
      pricePerSqftArea: 13281
    },
    comparables: [
      {
        id: 401,
        title: 'Dhanmondi Road 4 Lakeview Flat',
        address: 'Road 4, Dhanmondi, Dhaka',
        price: 38000000,
        sqft: 2850,
        bedrooms: 3,
        bathrooms: 4,
        distanceKm: 0.8,
        imageUrl: 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?q=80&w=800&auto=format&fit=crop'
      }
    ],
    schools: [
      { name: 'Mastermind School Dhanmondi', type: 'English Medium (Edexcel)', rating: 4.9, distanceKm: 0.6, travelMins: 3, grades: 'Playgroup to A Levels' },
      { name: 'Sunnydale School', type: 'English Medium', rating: 4.8, distanceKm: 0.9, travelMins: 4, grades: 'Playgroup to A Levels' },
      { name: 'Dhaka Residential Model College', type: 'National Curriculum', rating: 4.9, distanceKm: 2.2, travelMins: 8, grades: 'Class 3 to HSC' }
    ],
    community: {
      neighborhood: 'Dhanmondi Lake Enclave',
      metroDistanceKm: 2.0,
      nearestMetroStation: 'MRT Line-6 Karwan Bazar & Shahbagh Station',
      hospitalDistanceKm: 1.0,
      nearestHospital: 'Square Hospital, Labaid Specialized & Bangladesh Specialized Hospital',
      airportDistanceKm: 14.0,
      safetyRating: 'Top Ranked Dhaka Residential Area with Active Society Security',
      amenitiesOverview: 'Surrounded by Dhanmondi Lake parks, cultural hubs (Rabindra Sarobar), cafes, and top-tier healthcare.',
      transitOverview: 'Fast road access to Mirpur Road, Satmasjid Road, and Elephant Road.'
    }
  },
  {
    id: 5,
    title: '5 Katha South-Facing Commercial Plot in Bashundhara Block-M',
    slug: '5-katha-commercial-plot-bashundhara-block-m',
    tagline: 'Direct 60ft Main Avenue Road Front, 100% Mutation & Ready for Construction',
    description: 'An exceptional commercial & high-density residential plot in the rapidly booming Block-M of Bashundhara Residential Area. Located directly on a 60-foot wide central connecting road, just minutes from the Bashundhara Sports Complex and Apollo/Evercare corridor. Mutation completed, boundary demarcated with pillars, and ready for immediate building plan submission.',
    address: 'Road 22, Block M, Bashundhara R/A, Dhaka-1229',
    city: 'Dhaka',
    state: 'Dhaka North',
    areaName: 'Bashundhara R/A',
    price: 24500000, // ৳ 2.45 Crore (49 Lakh/katha)
    priceUnit: 'Total (৳ 49 Lakh / Katha)',
    pricePrefix: 'Prime Commercial',
    listingType: 'Sale',
    propertyType: 'Plot',
    status: 'Active',
    bedrooms: 0,
    bathrooms: 0,
    landSize: 5,
    landUnit: 'Katha',
    parking: 0,
    facing: 'South',
    completionStatus: 'Ready',
    isFeatured: true,
    isRajukApproved: true,
    isVerified: true,
    hasOpenHouse: false,
    lat: 23.8180,
    lng: 90.4420,
    images: [
      'https://images.unsplash.com/photo-1524813686514-a57563d77d61?q=80&w=1600&auto=format&fit=crop',
      'https://images.unsplash.com/photo-1500382017468-9049fed747ef?q=80&w=1200&auto=format&fit=crop'
    ],
    amenities: [
      '60ft Wide Commercial Avenue', 'Complete Underground Gas & Water Network',
      'Boundary Demarcated & Registered', 'Walking Distance to Commercial Centers & Mosques'
    ],
    documentsVerified: [
      'Bashundhara Official Allotment & Handover Deed',
      'Mutation Parch & Up-to-date Dakhila Khajna Paid',
      'Freehold Clear Title Vetted by Legal Counsel'
    ],
    agentId: 1,
    history: [
      { id: 1, date: 'Feb 2026', event: 'Listed on Market', price: 24500000, status: 'Active', notes: 'Prime commercial block avenue plot' }
    ],
    estimates: {
      marketEstimate: 25500000,
      lowEstimate: 23500000,
      highEstimate: 27000000,
      annualGrowthPct: 15.2,
      monthlyRentEstimate: 0,
      annualRoiPct: 15.2,
      pricePerSqftArea: 6800
    },
    comparables: [
      {
        id: 501,
        title: 'Bashundhara Block-L 5 Katha Plot',
        address: 'Block L, Bashundhara R/A',
        price: 26000000,
        sqft: 3600,
        bedrooms: 0,
        bathrooms: 0,
        distanceKm: 1.0,
        imageUrl: 'https://images.unsplash.com/photo-1500382017468-9049fed747ef?q=80&w=800&auto=format&fit=crop'
      }
    ],
    schools: [
      { name: 'International School Dhaka (ISD)', type: 'IB World School', rating: 5.0, distanceKm: 1.8, travelMins: 6, grades: 'Early Years to Grade 12' },
      { name: 'North South University (NSU)', type: 'Top Private University', rating: 4.9, distanceKm: 2.5, travelMins: 8, grades: 'Undergraduate & Masters' }
    ],
    community: {
      neighborhood: 'Bashundhara R/A Block M',
      metroDistanceKm: 3.5,
      nearestMetroStation: 'MRT Line-1 Proposed Bashundhara Station / Kuril Flyover',
      hospitalDistanceKm: 2.2,
      nearestHospital: 'Evercare Hospital Dhaka (Apollo)',
      airportDistanceKm: 8.5,
      safetyRating: 'Gated Township with Round-the-Clock Bashundhara Security Force',
      amenitiesOverview: 'Home to Bashundhara Sports Complex, Mehedi Mart, convention centers, and university campuses.',
      transitOverview: 'Fast connection to 300ft Purbachal Expressway and Kuril Flyover.'
    }
  },
  {
    id: 6,
    title: 'Boutique Eco Resort with 24 Luxury Cottages in Sreemangal',
    slug: 'boutique-eco-resort-sreemangal-tea-valley',
    tagline: '3.5 Bigha Lush Tea Garden View with Running Profitable Hospitality Business',
    description: 'An extraordinary turnkey hospitality estate situated amidst the world-famous lush rolling tea gardens of Sreemangal. Spanning 3.5 Bighas of freehold hilltop property, this resort features 24 fully furnished wooden luxury cottages, a multi-cuisine open-air restaurant, natural stream waterbody, swimming pool, organic fruit orchard, and solar backup power. Highly rated (4.8/5 on booking portals) with consistent 75%+ yearly occupancy.',
    address: 'Bhanugach Road, Sreemangal, Moulvibazar, Sylhet',
    city: 'Sylhet / Sreemangal',
    state: 'Sylhet',
    areaName: 'Sreemangal Tea Valley',
    price: 185000000, // ৳ 18.50 Crore
    pricePrefix: 'Turnkey Business',
    listingType: 'Sale',
    propertyType: 'Hotel',
    status: 'Active',
    bedrooms: 24,
    bathrooms: 26,
    landSize: 3.5,
    landUnit: 'Bigha',
    parking: 15,
    completionStatus: 'Ready',
    yearBuilt: 2023,
    isFeatured: true,
    isRajukApproved: false,
    isVerified: true,
    hasOpenHouse: true,
    openHouseDate: 'Private Investor Site Inspection by Appointment',
    lat: 24.3065,
    lng: 91.7296,
    images: [
      'https://images.unsplash.com/photo-1540555700478-4be289fbecef?q=80&w=1600&auto=format&fit=crop',
      'https://images.unsplash.com/photo-1571896349842-33c89424de2d?q=80&w=1200&auto=format&fit=crop',
      'https://images.unsplash.com/photo-1566073771259-6a8506099945?q=80&w=1200&auto=format&fit=crop'
    ],
    amenities: [
      '24 Luxury Air-Conditioned Cottages', 'Panoramic Rolling Tea Estate Views', 'Freshwater Swimming Pool',
      'Banquet & Open-Air Dining Pavilion', 'Natural Stream & Fish Pond', 'Commercial Solar Power & Generator',
      '15-Car Dedicated Parking Lot', 'Trained Staff & Operational Management Team'
    ],
    documentsVerified: [
      'Freehold DC Certified Land Record (Non-Tea-Lease Freehold)',
      'Department of Environment & Fire Safety Clearance',
      'Trade License & Tourism Hotel Registration',
      'Audited P&L Statements Available for Qualified Buyers'
    ],
    agentId: 3,
    history: [
      { id: 1, date: 'Jan 2026', event: 'Listed as Turnkey Asset', price: 185000000, status: 'Active', notes: 'Profitable resort operational since 2023' }
    ],
    estimates: {
      marketEstimate: 190000000,
      lowEstimate: 180000000,
      highEstimate: 210000000,
      annualGrowthPct: 18.0,
      monthlyRentEstimate: 1800000, // ৳ 18 Lakh average monthly gross revenue
      annualRoiPct: 15.8,
      pricePerSqftArea: 3800
    },
    comparables: [
      {
        id: 601,
        title: 'Grand Sultan Area Boutique Eco Villa',
        address: 'Radhanagar, Sreemangal',
        price: 140000000,
        sqft: 25000,
        bedrooms: 16,
        bathrooms: 18,
        distanceKm: 3.5,
        imageUrl: 'https://images.unsplash.com/photo-1540555700478-4be289fbecef?q=80&w=800&auto=format&fit=crop'
      }
    ],
    schools: [
      { name: 'Sreemangal Government College', type: 'Higher Secondary & Degree', rating: 4.5, distanceKm: 3.0, travelMins: 8, grades: 'HSC to Degree' }
    ],
    community: {
      neighborhood: 'Sreemangal Eco-Tourism Hub',
      metroDistanceKm: 0,
      nearestMetroStation: 'Sreemangal Railway Station (Direct Intercity Train to Dhaka/Chittagong)',
      hospitalDistanceKm: 4.0,
      nearestHospital: 'Sreemangal Upazila Health Complex & Moulvibazar 250-Bed Hospital',
      airportDistanceKm: 85.0,
      safetyRating: 'Serene & Safe Eco Tourism Zone with Dedicated Tourist Police',
      amenitiesOverview: 'Close to Lawachara National Park, Baikka Beel bird sanctuary, and 7-layer tea cabins.',
      transitOverview: 'Well-paved Dhaka-Sylhet Highway access.'
    }
  }
])

const isPropertiesLoading = ref(false)
const lastPropertiesSyncedAt = ref<Date | null>(null)
const isAgentsLoading = ref(false)
const lastAgentsSyncedAt = ref<Date | null>(null)

const mapDbItemToAgentItem = (item: any): AgentItem => {
  return {
    id: Number(item.id),
    name: item.name || '',
    title: item.title || 'Senior Real Estate Advisor',
    agency: item.agency || 'GBREL Premier Advisory',
    state: item.state || 'Dhaka North',
    city: item.city || 'Dhaka',
    photo: item.photo || item.avatar || 'https://images.unsplash.com/photo-1560250097-0b93528c311a?q=80&w=600&auto=format&fit=crop',
    email: item.email || `${(item.name || 'agent').toLowerCase().replace(/[^a-z0-9]/g, '')}@gbrel.com`,
    phone: item.phone || '+880 1819-000000',
    whatsapp: item.whatsapp || item.phone || '+880 1819-000000',
    bio: item.bio || 'Experienced real estate advisor with GBREL.',
    experienceYears: Number(item.experience_years ?? item.experienceYears ?? 8),
    rating: Number(item.rating ?? 4.9),
    reviewCount: Number(item.review_count ?? item.reviewCount ?? 25),
    activeListingsCount: Number(item.active_listings_count ?? item.activeListingsCount ?? 0),
    specialties: Array.isArray(item.specialties) 
      ? item.specialties 
      : (typeof item.specialties === 'string' ? JSON.parse(item.specialties || '[]') : ['Luxury Estates'])
  }
}

const mapDbItemToPropertyItem = (apiItem: any, strict = false): PropertyItem => {
  const price = Number(apiItem.price) || 0
  const areaName = apiItem.area_name || apiItem.areaName || 'Dhaka Hub'
  const sqft = Number(apiItem.square_footage) || Number(apiItem.squareFootage) || 0
  const landSize = Number(apiItem.land_size) || Number(apiItem.landSize) || 0

  return {
    id: Number(apiItem.id),
    title: apiItem.title || 'Untitled Mandate',
    slug: apiItem.slug || 'property-' + apiItem.id,
    tagline: apiItem.tagline || (strict ? '' : 'Verified Legal Ownership & Direct Handover'),
    description: apiItem.description || (strict ? '' : `Exclusive real estate mandate in ${areaName}. Verified by GBREL legal due diligence panel.`),
    address: apiItem.address || (strict ? '' : `Road 1, ${areaName}`),
    city: apiItem.city || 'Dhaka',
    state: apiItem.state || 'Dhaka North',
    areaName: areaName,
    price: price,
    priceUnit: apiItem.price_unit || undefined,
    buyerDetails: apiItem.buyer_details && typeof apiItem.buyer_details === 'object' && !Array.isArray(apiItem.buyer_details) ? apiItem.buyer_details : {},
    pricePrefix: apiItem.price_prefix || undefined,
    listingType: (apiItem.listing_type || apiItem.listingType || 'Sale') as any,
    propertyType: (apiItem.property_type || apiItem.propertyType || 'Flat') as any,
    status: (apiItem.status || 'Active') as any,
    bedrooms: Number(apiItem.bedrooms) || 0,
    bathrooms: Number(apiItem.bathrooms) || 0,
    balconies: Number(apiItem.balconies) || 0,
    squareFootage: sqft,
    landSize: landSize,
    landUnit: (apiItem.land_unit || apiItem.landUnit || 'Katha') as any,
    parking: Number(apiItem.parking) || 0,
    floorNumber: apiItem.floor_number ? Number(apiItem.floor_number) : undefined,
    totalFloors: apiItem.total_floors ? Number(apiItem.total_floors) : undefined,
    facing: (apiItem.facing || (strict ? undefined : 'South')) as any,
    completionStatus: (apiItem.completion_status || apiItem.completionStatus || (strict ? '' : 'Ready')) as any,
    yearBuilt: apiItem.year_built ? Number(apiItem.year_built) : (strict ? undefined : 2024),
    isFeatured: Boolean(apiItem.is_featured ?? apiItem.isFeatured),
    isRajukApproved: Boolean(apiItem.is_rajuk_approved ?? apiItem.isRajukApproved),
    isVerified: Boolean(apiItem.is_verified ?? apiItem.isVerified ?? true),
    hasOpenHouse: Boolean(apiItem.has_open_house ?? apiItem.hasOpenHouse),
    openHouseDate: apiItem.open_house_date || undefined,
    lat: Number(apiItem.latitude) || Number(apiItem.lat) || (strict ? 0 : 23.7925),
    lng: Number(apiItem.longitude) || Number(apiItem.lng) || (strict ? 0 : 90.4167),
    images: Array.isArray(apiItem.images) && apiItem.images.length > 0 
      ? apiItem.images 
      : (apiItem.feature_image ? [apiItem.feature_image] : (strict ? [] : ['https://images.unsplash.com/photo-1600585154340-be6161a56a0c?q=80&w=1600&auto=format&fit=crop'])),
    featureImage: apiItem.feature_image || apiItem.featureImage || (Array.isArray(apiItem.images) && apiItem.images.length > 0 ? apiItem.images[0] : (strict ? '' : 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?q=80&w=1600&auto=format&fit=crop')),
    gallery: Array.isArray(apiItem.gallery) && apiItem.gallery.length > 0
      ? apiItem.gallery 
      : (Array.isArray(apiItem.images) && apiItem.images.length > 1 ? apiItem.images.slice(1) : []),
    amenities: Array.isArray(apiItem.amenities) && apiItem.amenities.length > 0 
      ? apiItem.amenities 
      : (strict ? [] : ['24/7 Generator', 'Security CCTV', 'Elevator Access']),
    documentsVerified: Array.isArray(apiItem.documents_verified) && apiItem.documents_verified.length > 0 
      ? apiItem.documents_verified 
      : (strict ? [] : ['Clear Freehold Title Deed', 'Mutation Cleared', 'RAJUK Allotment']),
    brochureUrl: apiItem.brochure_url || apiItem.brochureUrl || undefined,
    hidePrice: Boolean(apiItem.hide_price ?? apiItem.hidePrice),
    priceDisplayText: apiItem.price_display_text || apiItem.priceDisplayText || 'Price on Application',
    hideAgentPhoto: Boolean(apiItem.hide_agent_photo ?? apiItem.hideAgentPhoto),
    hideAgentContact: Boolean(apiItem.hide_agent_contact ?? apiItem.hideAgentContact),
    hideExactAddress: Boolean(apiItem.hide_exact_address ?? apiItem.hideExactAddress),
    hideFloorPlan: Boolean(apiItem.hide_floor_plan ?? apiItem.hideFloorPlan),
    hideMortgageCalculator: Boolean(apiItem.hide_mortgage_calculator ?? apiItem.hideMortgageCalculator),
    brochuresVault: Array.isArray(apiItem.brochures_vault) ? apiItem.brochures_vault : [],
    agentId: Number(apiItem.agent_id) || Number(apiItem.agentId) || 0,
    history: Array.isArray(apiItem.history) && apiItem.history.length > 0 ? apiItem.history : [
      { id: 1, date: 'Recent', event: 'Listed on GBREL Portal', price: price, status: 'Active', notes: 'Database Synced' }
    ],
    estimates: apiItem.estimates || {
      marketEstimate: price,
      lowEstimate: price * 0.95,
      highEstimate: price * 1.05,
      annualGrowthPct: 10.5,
      monthlyRentEstimate: price * 0.004,
      annualRoiPct: 6.2,
      pricePerSqftArea: sqft > 0 ? Math.round(price / sqft) : 8500
    },
    comparables: apiItem.comparables || [],
    schools: apiItem.schools || [
      { name: 'International School Dhaka (ISD)', type: 'English Medium', rating: 4.9, distanceKm: 2.5, travelMins: 10, grades: 'Playgroup to IB' }
    ],
    community: apiItem.community || {
      neighborhood: areaName,
      metroDistanceKm: 2.0,
      nearestMetroStation: 'MRT Line-6 Station',
      hospitalDistanceKm: 2.2,
      nearestHospital: 'Evercare / United Hospital Dhaka',
      airportDistanceKm: 11.5,
      safetyRating: 'High Diplomatic Enclave Protection',
      amenitiesOverview: 'Top-tier schools, embassies, luxury shopping arcades, and international dining.',
      transitOverview: 'Direct flyover and VIP express boulevard connection.'
    }
  }
}

export const useProperties = () => {
  const properties = computed(() => propertiesData.value)
  const agents = computed(() => agentsData.value)
  const isLoading = computed(() => isPropertiesLoading.value)
  const lastSynced = computed(() => lastPropertiesSyncedAt.value)

  const featuredProperties = computed(() => 
    propertiesData.value.filter(p => p.isFeatured && p.status !== 'Draft' && p.status !== 'Delisted')
  )

  const getPropertyById = (id: number | string) => {
    const numId = Number(id)
    if (!isNaN(numId)) {
      const foundById = propertiesData.value.find(p => p.id === numId)
      if (foundById) return foundById
    }
    const strId = String(id).toLowerCase().trim()
    const foundBySlug = propertiesData.value.find(p => (p.slug || '').toLowerCase() === strId)
    if (foundBySlug) return foundBySlug
    return propertiesData.value.find(p => p.id === numId) || propertiesData.value[0]
  }

  const fetchPropertyById = async (idOrSlug: string | number, options: { strict?: boolean } = {}): Promise<PropertyItem | null> => {
    try {
      const res = await fetch(useApiUrl(`/properties/${idOrSlug}`))
      if (res.ok) {
        const json = await res.json()
        if (json && json.success && json.data) {
          const mapped = mapDbItemToPropertyItem(json.data, options.strict)
          const idx = propertiesData.value.findIndex(p => p.id === mapped.id)
          if (idx >= 0) {
            propertiesData.value[idx] = mapped
          } else {
            propertiesData.value.push(mapped)
          }
          return mapped
        }
      }
    } catch (err) {
      console.warn('Failed to fetch property by id/slug from API:', err)
    }
    // Landing pages must not display a seeded or unrelated listing after an API failure.
    return options.strict ? null : getPropertyById(idOrSlug)
  }

  const getAgentById = (id: number | string) => {
    const numId = Number(id)
    return agentsData.value.find(a => a.id === numId) || agentsData.value[0]
  }

  const getPropertiesByAgent = (agentId: number | string) => {
    const numId = Number(agentId)
    return propertiesData.value.filter(p => p.agentId === numId)
  }

  const fetchProperties = async (options: { force?: boolean; q?: string; state?: string; type?: string; status?: string } | boolean = {}) => {
    isPropertiesLoading.value = true
    const opts = typeof options === 'boolean' ? { force: options } : options
    try {
      const params = new URLSearchParams()
      if (opts.q) params.set('q', opts.q)
      if (opts.state) params.set('state', opts.state)
      if (opts.type) params.set('type', opts.type)
      if (opts.status) params.set('status', opts.status)
      const queryStr = params.toString() ? `?${params.toString()}` : ''

      const res = await fetch(useApiUrl(`/properties${queryStr}`))
      if (res.ok) {
        const json = await res.json()
        if (json && json.success && Array.isArray(json.data)) {
          if (json.data.length > 0 || opts.force || opts.q || opts.state || opts.type || opts.status) {
            propertiesData.value = json.data.map((item: any) => mapDbItemToPropertyItem(item))
          }
          lastPropertiesSyncedAt.value = new Date()
        }
      }
    } catch (err) {
      console.warn('Realtime MySQL fetch notice (using cached state):', err)
    } finally {
      isPropertiesLoading.value = false
    }
  }

  const addProperty = async (newProp: any) => {
    isPropertiesLoading.value = true
    try {
      const res = await fetch(useApiUrl('/properties'), {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(newProp)
      })

      if (!res.ok) {
        const errorJson = await res.json().catch(() => null)
        throw new Error(errorJson?.message || `MySQL Server returned status ${res.status}`)
      }

      const json = await res.json()
      if (json && json.success && json.data) {
        const createdItem = mapDbItemToPropertyItem(json.data)
        propertiesData.value.unshift(createdItem)
        lastPropertiesSyncedAt.value = new Date()
        return createdItem
      } else {
        throw new Error(json?.message || 'Failed to save property in database')
      }
    } finally {
      isPropertiesLoading.value = false
    }
  }

  const updateProperty = async (id: number, data: Partial<PropertyItem>) => {
    // Optimistic local update with backup
    const existing = propertiesData.value.find(prop => prop.id === id)
    const backup = existing ? { ...existing } : null
    if (existing) {
      Object.assign(existing, data)
    }

    try {
      const res = await fetch(useApiUrl(`/properties/${id}`), {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
      })

      if (!res.ok) {
        const errorJson = await res.json().catch(() => null)
        throw new Error(errorJson?.message || `MySQL update failed with status ${res.status}`)
      }

      const json = await res.json()
      if (json && json.success && json.data && existing) {
        const updated = mapDbItemToPropertyItem(json.data)
        Object.assign(existing, updated)
        lastPropertiesSyncedAt.value = new Date()
        return updated
      }
    } catch (err) {
      // Rollback on error
      if (existing && backup) {
        Object.assign(existing, backup)
      }
      throw err
    }
  }

  const toggleFeatureProperty = async (id: number) => {
    const existing = propertiesData.value.find(p => p.id === id)
    const original = existing ? existing.isFeatured : false
    if (existing) {
      existing.isFeatured = !original
    }

    try {
      const res = await fetch(useApiUrl(`/properties/${id}/toggle-feature`), {
        method: 'PATCH'
      })
      if (!res.ok) throw new Error('Toggle feature request failed')
      const json = await res.json()
      if (json && json.success && existing) {
        existing.isFeatured = Boolean(json.is_featured)
        lastPropertiesSyncedAt.value = new Date()
        return existing.isFeatured
      }
    } catch (err) {
      if (existing) existing.isFeatured = original
      throw err
    }
  }

  const toggleRajukProperty = async (id: number) => {
    const existing = propertiesData.value.find(p => p.id === id)
    const original = existing ? existing.isRajukApproved : false
    if (existing) {
      existing.isRajukApproved = !original
    }

    try {
      const res = await fetch(useApiUrl(`/properties/${id}/toggle-rajuk`), {
        method: 'PATCH'
      })
      if (!res.ok) throw new Error('Toggle RAJUK request failed')
      const json = await res.json()
      if (json && json.success && existing) {
        existing.isRajukApproved = Boolean(json.is_rajuk_approved)
        lastPropertiesSyncedAt.value = new Date()
        return existing.isRajukApproved
      }
    } catch (err) {
      if (existing) existing.isRajukApproved = original
      throw err
    }
  }

  const updatePropertyStatus = async (id: number, status: string) => {
    const existing = propertiesData.value.find(p => p.id === id)
    const original = existing ? existing.status : 'Active'
    if (existing) {
      existing.status = status as any
    }

    try {
      const res = await fetch(useApiUrl(`/properties/${id}/status`), {
        method: 'PATCH',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ status })
      })
      if (!res.ok) throw new Error('Update status request failed')
      const json = await res.json()
      if (json && json.success && existing) {
        existing.status = json.status || (status as any)
        lastPropertiesSyncedAt.value = new Date()
        return existing.status
      }
    } catch (err) {
      if (existing) existing.status = original
      throw err
    }
  }

  const deleteProperty = async (id: number) => {
    const index = propertiesData.value.findIndex(p => p.id === id)
    let removedItem: PropertyItem | null = null
    if (index > -1) {
      removedItem = propertiesData.value.splice(index, 1)[0]
    }

    try {
      const res = await fetch(useApiUrl(`/properties/${id}`), {
        method: 'DELETE'
      })
      if (!res.ok) {
        const errorJson = await res.json().catch(() => null)
        throw new Error(errorJson?.message || `Failed to delete from MySQL (status: ${res.status})`)
      }
      lastPropertiesSyncedAt.value = new Date()
    } catch (err) {
      // Revert if database delete failed
      if (removedItem && index > -1) {
        propertiesData.value.splice(index, 0, removedItem)
      }
      throw err
    }
  }

  const uploadImage = async (file: File): Promise<string> => {
    const formData = new FormData()
    formData.append('image', file)
    const res = await fetch(useApiUrl('/upload'), {
      method: 'POST',
      body: formData
    })
    if (!res.ok) {
      const errJson = await res.json().catch(() => null)
      throw new Error(errJson?.message || `Upload failed with status ${res.status}`)
    }
    const json = await res.json()
    if (json && json.success && json.url) {
      return json.url
    }
    throw new Error(json?.message || 'Failed to upload image')
  }

  const uploadMultipleImages = async (files: File[] | FileList): Promise<string[]> => {
    const formData = new FormData()
    for (let i = 0; i < files.length; i++) {
      formData.append('images[]', files[i])
    }
    const res = await fetch(useApiUrl('/upload'), {
      method: 'POST',
      body: formData
    })
    if (!res.ok) {
      const errJson = await res.json().catch(() => null)
      throw new Error(errJson?.message || `Upload failed with status ${res.status}`)
    }
    const json = await res.json()
    if (json && json.success && Array.isArray(json.urls)) {
      return json.urls
    }
    throw new Error(json?.message || 'Failed to upload gallery images')
  }

  const uploadBrochure = async (file: File): Promise<string> => {
    const formData = new FormData()
    formData.append('brochure', file)
    const res = await fetch(useApiUrl('/upload'), {
      method: 'POST',
      body: formData
    })
    if (!res.ok) {
      const errJson = await res.json().catch(() => null)
      throw new Error(errJson?.message || `Brochure upload failed with status ${res.status}`)
    }
    const json = await res.json()
    if (json && json.success && json.url) {
      return json.url
    }
    throw new Error(json?.message || 'Failed to upload brochure file')
  }

  const isAgentsLoadingRef = computed(() => isAgentsLoading.value)

  const fetchAgents = async (force = false) => {
    isAgentsLoading.value = true
    try {
      const res = await fetch(useApiUrl('/agents'))
      if (res.ok) {
        const json = await res.json()
        if (json && json.success && Array.isArray(json.data) && json.data.length > 0) {
          agentsData.value = json.data.map(mapDbItemToAgentItem)
          lastAgentsSyncedAt.value = new Date()
        }
      }
    } catch (err) {
      console.warn('Realtime MySQL agents fetch notice (using cache):', err)
    } finally {
      isAgentsLoading.value = false
    }
    return agentsData.value
  }

  const addAgent = async (formData: Partial<AgentItem>) => {
    const payload = {
      name: formData.name,
      title: formData.title,
      agency: formData.agency || 'GBREL Premier Advisory',
      state: formData.state || 'Dhaka North',
      city: formData.city || 'Dhaka',
      photo: formData.photo,
      email: formData.email || `${(formData.name || 'advisor').toLowerCase().replace(/[^a-z0-9]/g, '')}@gbrel.com`,
      phone: formData.phone,
      whatsapp: formData.whatsapp,
      bio: formData.bio,
      experience_years: formData.experienceYears || 8,
      rating: formData.rating || 4.9,
      review_count: formData.reviewCount || 10,
      active_listings_count: formData.activeListingsCount || 0,
      specialties: formData.specialties || ['Luxury Real Estate']
    }

    const res = await fetch(useApiUrl('/agents'), {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    })
    if (!res.ok) {
      const err = await res.json().catch(() => null)
      throw new Error(err?.message || `Failed to create advisor (status: ${res.status})`)
    }
    const json = await res.json()
    if (json && json.success && json.data) {
      const newAgent = mapDbItemToAgentItem(json.data)
      agentsData.value.push(newAgent)
      return newAgent
    }
    throw new Error('Invalid response from agents API')
  }

  const updateAgent = async (id: number, formData: Partial<AgentItem>) => {
    const payload: any = { ...formData }
    if (formData.experienceYears !== undefined) payload.experience_years = formData.experienceYears
    if (formData.reviewCount !== undefined) payload.review_count = formData.reviewCount
    if (formData.activeListingsCount !== undefined) payload.active_listings_count = formData.activeListingsCount

    const res = await fetch(useApiUrl(`/agents/${id}`), {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    })
    if (!res.ok) {
      const err = await res.json().catch(() => null)
      throw new Error(err?.message || `Failed to update advisor (status: ${res.status})`)
    }
    const json = await res.json()
    if (json && json.success && json.data) {
      const updated = mapDbItemToAgentItem(json.data)
      const index = agentsData.value.findIndex(a => a.id === id)
      if (index > -1) {
        agentsData.value[index] = updated
      }
      return updated
    }
    throw new Error('Invalid response from update agent API')
  }

  const deleteAgent = async (id: number) => {
    const index = agentsData.value.findIndex(a => a.id === id)
    let removed: AgentItem | null = null
    if (index > -1) {
      removed = agentsData.value.splice(index, 1)[0]
    }
    try {
      const res = await fetch(useApiUrl(`/agents/${id}`), {
        method: 'DELETE'
      })
      if (!res.ok) {
        throw new Error(`Failed to delete advisor: status ${res.status}`)
      }
      return true
    } catch (err) {
      if (removed && index > -1) {
        agentsData.value.splice(index, 0, removed)
      }
      throw err
    }
  }

  return {
    properties,
    agents,
    featuredProperties,
    isLoading,
    isAgentsLoading: isAgentsLoadingRef,
    lastSynced,
    getPropertyById,
    fetchPropertyById,
    getAgentById,
    getPropertiesByAgent,
    fetchProperties,
    fetchAgents,
    addProperty,
    updateProperty,
    toggleFeatureProperty,
    toggleRajukProperty,
    updatePropertyStatus,
    deleteProperty,
    addAgent,
    updateAgent,
    deleteAgent,
    uploadImage,
    uploadMultipleImages,
    uploadBrochure
  }
}

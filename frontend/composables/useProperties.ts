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
  videoUrl?: string
  videoPoster?: string
  coverMedia?: 'image' | 'video'
  hidePrice?: boolean
  priceDisplayText?: string
  hideAgentPhoto?: boolean
  hideAgentContact?: boolean
  hideExactAddress?: boolean
  hideFloorPlan?: boolean
  hideMortgageCalculator?: boolean
  brochuresVault?: any[]
  agentId: number
  ownerId?: number | null
  reviewStatus?: string | null
  publishedAt?: string | null
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
    name: 'মোঃ আবু হানিফ (Md. Abu Hanif)',
    title: 'Director & Authorized Representative',
    agency: 'GBREL Premier Advisory',
    state: 'Dhaka North',
    city: 'Dhaka',
    photo: 'https://images.unsplash.com/photo-1560250097-0b93528c311a?q=80&w=600&auto=format&fit=crop',
    email: 'abu.hanif@gbrel.com',
    phone: '+880 1348-988225',
    whatsapp: '+8801348988225',
    bio: 'Authorized representative for prime Gulshan-1 mandates, including Lake View and Gulshan-1 15 Katha estates. Specialist in high-value property conveyancing, RAJUK clearance, and escrow transactions.',
    experienceYears: 16,
    rating: 4.98,
    reviewCount: 120,
    activeListingsCount: 2,
    specialties: ['Gulshan-1 Luxury Estates', 'RAJUK Sale Permission', 'Commercial Buildings']
  },
  {
    id: 2,
    name: 'সিরাজুম মুনিরা খন্দকার (Sirajum Munira Khandakar)',
    title: 'Senior Property Acquisitions Advisor',
    agency: 'GBREL Premier Advisory',
    state: 'Dhaka North',
    city: 'Dhaka',
    photo: 'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?q=80&w=600&auto=format&fit=crop',
    email: 'sirajum.munira@gbrel.com',
    phone: '+880 1870-862751',
    whatsapp: '+8801870862751',
    bio: 'Lead representative for Road 92 Gulshan-2 paternal residential mandates. Expert in inheritance mutation vetting, title deed verification, and high-net-worth client advisory.',
    experienceYears: 11,
    rating: 4.92,
    reviewCount: 78,
    activeListingsCount: 1,
    specialties: ['Gulshan-2 Diplomatic Area', 'Paternal Inheritance Properties', 'Direct Seller Representation']
  },
  {
    id: 3,
    name: 'সিয়াম তালুকদার (Siam Talukder)',
    title: 'Commercial Land & Tower Specialist',
    agency: 'GBREL Commercial Advisory',
    state: 'Dhaka North',
    city: 'Dhaka',
    photo: 'https://images.unsplash.com/photo-1519085360753-af0119f7cbe7?q=80&w=600&auto=format&fit=crop',
    email: 'siam.talukder@gbrel.com',
    phone: '+880 1863-755006',
    whatsapp: '+8801863755006',
    bio: 'Commercial specialist representing premier Gulshan-2 multi-katha commercial plots and corner mandates. Expertise in RAJUK commercial clearance, corporate headquarters redevelopment, and bank transactions.',
    experienceYears: 14,
    rating: 4.95,
    reviewCount: 95,
    activeListingsCount: 1,
    specialties: ['Commercial Corner Plots', 'Corporate High-Rise Sites', 'Gulshan-2 Prime Avenues']
  }
])

const propertiesData = ref<PropertyItem[]>([
  {
    id: 1,
    title: 'গুলশান ১, ১৩৫/৬ — ১৫ কাঠা জমি ও ২ তলা পুরাতন দালান',
    slug: 'gulshan-1-135-6-15-katha-building',
    tagline: '১৫ কাঠা জমি ও ২ তলা পুরাতন দালান | মোট মূল্য ৳ ৯০ কোটি (আলোচনা সাপেক্ষ)',
    description: 'গুলশান ১, ১৩৫/৬ নম্বরে ১৫ কাঠা জমিতে ২ তলা পুরাতন দালান বিক্রয়ের প্রস্তাব। ৫ জন ওয়ারিশ সূত্রে মালিক, গনিউর রহমান গং বর্তমানে অপ্রত্যাহার যোগ্য পাওয়ার বলে জমির মালিক। বাংলা ১৪৩২ সনের অনলাইন খাজনা ও ২০২৩ সালের নামজারি পরিশোধ করা আছে। সার্ভিস চার্জ ও ডকুমেন্টেশন ফি জমা দেওয়া আছে। সম্পূর্ণ নিষ্কণ্টক এবং টোটাল কাগজ-পাতি আপডেট। জমি মালিকের দখলে আছে। আর্থিক লেনদেন ব্যাংকের মাধ্যমে। রাজউক থেকে বিক্রয়ের অনুমতি প্রাপ্তি ১৫ (পনের) কার্যদিবস।',
    address: 'প্লট ১৩৫/৬, গুলশান-১, ঢাকা-১২১২',
    city: 'Dhaka',
    state: 'Dhaka North',
    areaName: 'Gulshan-1',
    price: 900000000,
    priceUnit: 'মোট মূল্য ৳ ৯০ কোটি (প্রতি কাঠা ৳ ৬.০০ কোটি আলোচনা সাপেক্ষ)',
    listingType: 'Sale',
    propertyType: 'Land',
    status: 'Active',
    bedrooms: 6,
    bathrooms: 6,
    balconies: 4,
    squareFootage: 5500,
    landSize: 15.0,
    landUnit: 'Katha',
    parking: 6,
    floorNumber: 1,
    totalFloors: 2,
    facing: 'South',
    completionStatus: 'Ready',
    yearBuilt: 1998,
    isFeatured: true,
    isRajukApproved: true,
    isVerified: true,
    hasOpenHouse: true,
    openHouseDate: 'Saturday, 10:00 AM - 4:00 PM',
    lat: 23.7785,
    lng: 90.4172,
    images: [
      'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?q=80&w=1600&auto=format&fit=crop',
      'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?q=80&w=1200&auto=format&fit=crop',
      'https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?q=80&w=1200&auto=format&fit=crop',
      'https://images.unsplash.com/photo-1500382017468-9049fed747ef?q=80&w=1200&auto=format&fit=crop'
    ],
    amenities: [
      '15 Katha South-Facing Freehold Land',
      '2-Storey Existing Structure / Redevelopment Ready',
      'Wide Gulshan-1 Frontage Road Access',
      'Independent Boundary Demarcated',
      'Full Owner Physical Possession',
      'WASA Water, DESCO Electricity & Gas Connection',
      'Bank Escrow Transaction Support',
      'RAJUK Sale Permission Assistance'
    ],
    documentsVerified: [
      'মালিকানা দলিল ও ওয়ারিশান সনদপত্র (Inheritance Deed)',
      '২০২৩ সালের নামজারি ও জমাভাগ খতিয়ান (Updated Mutation)',
      'বাংলা ১৪৩২ সনের অনলাইন ভূমি উন্নয়ন কর/খাজনা রশিদ',
      'নিষ্কণ্টক মালিকানা সনদ (Clear Title Certificate)',
      'সার্ভিস চার্জ ও ডকুমেন্টেশন ফি পরিশোধ রশিদ',
      'অপ্রত্যাহারযোগ্য পাওয়ার অব অ্যাটর্নি (গনিউর রাহমান গং)'
    ],
    agentId: 1,
    buyerDetails: {
      landUse: 'Residential',
      cornerPlot: 'No',
      roadWidth: 40,
      buildingDescription: '১৫ কাঠা জমির উপর ২ তলা পুরাতন দালান/ভবন বিদ্যমান। নতুন বহুতল ভবন নির্মাণের উপযোগী।',
      utilities: 'বিদ্যুৎ, গ্যাস ও ওয়াসা পানি সংযোগ হালনাগাদ রয়েছে।',
      priceBasis: 'Total',
      negotiable: 'Yes',
      priceIncludes: '১৫ কাঠা জমি এবং বিদ্যমান ২ তলা ভবন সহ মোট মূল্য ৯০ কোটি টাকা (আলোচনা সাপেক্ষ)।',
      depositPercent: 25,
      agreementDuration: 'চুক্তির তারিখ হতে ৯০ দিন',
      paymentSchedule: 'বায়না ২৫%, অবশিষ্ট মূল্য রাজউক অনুমতি সাপেক্ষে ব্যাংক পে-অর্ডারে হস্তান্তরকালে।',
      paymentMethod: 'ব্যাংক পে-অর্ডার / একাউন্ট ট্রান্সফার',
      registrationCost: 'সরকারি বিধি মোতাবেক সকল রেজিস্ট্রেশন ফি ক্রেতা বহন করবেন।',
      buyerCosts: 'ক্রেতা নিজ দায়িত্বে রেজিস্ট্রেশন ও সংশ্লিষ্ট সরকারি ফি নির্বাহ করবেন।',
      sellerCosts: 'সার্ভিস চার্জ ও ডকুমেন্টেশন ফি পরিশোধ সম্পন্ন।',
      transferTimeline: 'রাজউক থেকে বিক্রয়ের অনুমতি প্রাপ্তির ১৫ কার্যদিবসের মধ্যে।',
      transferTrigger: 'রাজউক কর্তৃক বিক্রয় অনুমতি পত্র জারির দিন থেকে।',
      ownerCount: 5,
      ownershipSource: 'Inheritance',
      possession: 'Owner',
      bankLoan: 'None declared',
      existingAgreement: 'None declared',
      saleAuthority: 'গনিউর রাহমান গং (অপ্রত্যাহারযোগ্য পাওয়ার অব অ্যাটর্নি বলে)',
      ownershipNotes: 'ওয়ারিশ সূত্রে ৫ জন মালিক, কোনো বিরোধ নেই, সম্পূর্ণ নিষ্কণ্টক জমি।',
      mutationStatus: 'Available',
      taxPaidThrough: 'বাংলা ১৪৩২ সন',
      serviceChargeStatus: 'জমা দেওয়া আছে',
      approvalDetails: 'রেসিডেন্সিয়াল জোন হিসেবে অনুমোদিত, রাজউক থেকে বিক্রয় অনুমতি প্রক্রিয়া চলমান।',
      documentSummary: 'নিষ্কণ্টক / টোটাল কাগজ-পাতি ১০০% আপডেট।',
      sourceDate: '2026-09-15',
      updatedOn: '2026-09-15'
    },
    history: [
      { id: 1, date: 'Sep 2026', event: 'Listed on GBREL Mandate Catalog', price: 900000000, status: 'Active', notes: 'Exclusive proposal dated 15-09-2026' }
    ],
    estimates: {
      marketEstimate: 920000000,
      lowEstimate: 880000000,
      highEstimate: 950000000,
      annualGrowthPct: 14.5,
      monthlyRentEstimate: 1200000,
      annualRoiPct: 7.2,
      pricePerSqftArea: 163636
    },
    comparables: [],
    schools: [
      { name: 'Scholastica Senior Campus Gulshan', type: 'English Medium', rating: 4.9, distanceKm: 1.2, travelMins: 5, grades: 'Class 6 to A Levels' },
      { name: 'Australian International School', type: 'International', rating: 4.8, distanceKm: 1.8, travelMins: 7, grades: 'Playgroup to Year 12' }
    ],
    community: {
      neighborhood: 'Gulshan-1 Residential Circle',
      metroDistanceKm: 2.1,
      nearestMetroStation: 'Mohakhali MRT Line-6 / Hatirjheel Link',
      hospitalDistanceKm: 1.5,
      nearestHospital: 'United Hospital / Gulshan Clinic',
      airportDistanceKm: 10.5,
      safetyRating: 'Diplomatic Zone Security & CC Surveillance',
      amenitiesOverview: 'Prime access to Gulshan 1 DCC Market, Lake Park, banks, and corporate headquarters.',
      transitOverview: 'Direct connection to Gulshan Avenue and Hatirjheel Expressway.'
    }
  },
  {
    id: 2,
    title: 'গুলশান ২, রোড ৯২, প্লট ০৬ — ১৭.১৮ কাঠা জমি ও ২ তলা বিল্ডিং',
    slug: 'gulshan-2-road-92-plot-06-17-katha-building',
    tagline: '১৭.১৮ কাঠা রেসিডেন্সিয়াল জমি ও ২ তলা বিল্ডিং | প্রতি কাঠা ৭ কোটি (আলোচনা সাপেক্ষ)',
    description: 'গুলশান ২, রোড নম্বর ৯২, প্লট ০৬ এ ১৭.১৮ কাঠা জমি ও ২ তলা বিল্ডিং বিক্রয়ের প্রস্তাব। পৈতৃক সূত্রে ২ জন ভাই জমির বর্তমান মালিক। বাংলা ১৪৩২ সনের অনলাইন খাজনা পরিশোধ করা আছে, সার্ভিস চার্জ ও ডকুমেন্টেশন ফি জমা দেওয়া আছে। রেসিডেন্সিয়াল অনুমোদনপ্রাপ্ত। নিষ্কণ্টক ও টোটাল কাগজ-পাতি আপডেট কমপ্লিট। জমি ও দখল সম্পূর্ণ নিজ অধীনে। বিক্রিত মূল্যের ৩০% বায়না। আর্থিক লেনদেন ব্যাংকের মাধ্যমে। রাজউক থেকে বিক্রয়ের অনুমতি প্রাপ্তি ১৫ (পনের) কার্যদিবস। বিক্রয় অনুমতি পত্র ও সার্ভিস চার্জ বিক্রেতা বহন করবেন।',
    address: 'রোড ৯২, প্লট ০৬, গুলশান-২, ঢাকা-১২১২',
    city: 'Dhaka',
    state: 'Dhaka North',
    areaName: 'Gulshan-2',
    price: 1202600000,
    priceUnit: 'প্রতি কাঠা ৳ ৭.০০ কোটি (আলোচনা সাপেক্ষ)',
    listingType: 'Sale',
    propertyType: 'Land',
    status: 'Active',
    bedrooms: 8,
    bathrooms: 8,
    balconies: 6,
    squareFootage: 6800,
    landSize: 17.18,
    landUnit: 'Katha',
    parking: 8,
    floorNumber: 1,
    totalFloors: 2,
    facing: 'South',
    completionStatus: 'Ready',
    yearBuilt: 2005,
    isFeatured: true,
    isRajukApproved: true,
    isVerified: true,
    hasOpenHouse: true,
    openHouseDate: 'Sunday, 11:00 AM - 5:00 PM',
    lat: 23.7962,
    lng: 90.4195,
    images: [
      'https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?q=80&w=1600&auto=format&fit=crop',
      'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?q=80&w=1200&auto=format&fit=crop',
      'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?q=80&w=1200&auto=format&fit=crop',
      'https://images.unsplash.com/photo-1512917774080-9991f1c4c750?q=80&w=1200&auto=format&fit=crop'
    ],
    amenities: [
      '17.18 Katha Prime Gulshan-2 Diplomatic Vicinity',
      'Road 92 Wide Avenue Frontage',
      '2-Storey Modern Residential Structure',
      'Direct Self-Possession by 2 Brothers',
      'Zero Encumbrance or Bank Liability',
      'Gas, Electricity & Deep WASA Connection',
      'Pre-vetted Title Deeds & Survey Khatian',
      'Seller Bears RAJUK Permission & Service Charge'
    ],
    documentsVerified: [
      'পৈতৃক সূত্রের মূল মালিকানা দলিল (Paternal Title Deed)',
      'অনলাইন নামজারি ও ডিসিআর পর্চা (Mutation DCR)',
      'বাংলা ১৪৩২ সনের হালনাগাদ অনলাইন খাজনা রশিদ',
      'রাজউক অনুমোদিত আবাসিক প্লট রেকর্ড',
      'নিষ্কণ্টক মালিকানা ও দাগ খতিয়ান যাচাইকৃত'
    ],
    agentId: 2,
    buyerDetails: {
      landUse: 'Residential',
      cornerPlot: 'No',
      roadWidth: 50,
      buildingDescription: '১৭.১৮ কাঠা জমিতে ২ তলা আবাসিক ভবন। সুদৃশ্য বাগান ও ড্রাইভওয়ে সহ।',
      utilities: 'গ্যাস, থ্রি-ফেজ বিদ্যুৎ ও ওয়াসা পানির লাইন সার্বক্ষণিক চালু।',
      priceBasis: 'Per land unit',
      negotiable: 'Yes',
      priceIncludes: 'প্রতি কাঠা ৭ কোটি টাকা হিসেবে মোট ১৭.১৮ কাঠা জমি ও বিদ্যমান ২ তলা বিল্ডিং (মোট মূল্য ৳ ১২০.২৬ কোটি আলোচনা সাপেক্ষ)।',
      depositPercent: 30,
      agreementDuration: 'চুক্তির তারিখ হতে ৯০-১২০ কার্যদিবস',
      paymentSchedule: 'বায়না ৩০%, অবশিষ্ট মূল্য রাজউক বিক্রয় অনুমতি প্রাপ্তির পর রেজিস্ট্রি সম্পন্নকালে।',
      paymentMethod: 'ব্যাংক ড্রাফট / পে-অর্ডার / আরটিজিএস',
      registrationCost: 'রেজিস্ট্রেশন ফি ও সরকারি কর ক্রেতার নিয়ম অনুযায়ী।',
      buyerCosts: 'রেজিস্ট্রেশন খরচ ও কর ক্রেতা বহন করবেন।',
      sellerCosts: 'বিক্রয় অনুমতি পত্র ও সার্ভিস চার্জ বিক্রেতা বহন করবেন।',
      transferTimeline: 'রাজউক থেকে বিক্রয়ের অনুমতি প্রাপ্তির ১৫ কার্যদিবসের মধ্যে।',
      transferTrigger: 'রাজউক বিক্রয় অনুমতি পত্র হাতে পাওয়ার পর।',
      ownerCount: 2,
      ownershipSource: 'Inheritance',
      possession: 'Owner',
      bankLoan: 'None declared',
      existingAgreement: 'None declared',
      saleAuthority: 'নিজ (২ জন ভাই যৌথভাবে উপস্থিত থেকে দলিল সম্পাদন করবেন)',
      ownershipNotes: 'পৈতৃক সূত্রে ২ ভাই একক মালিক, কোনো তৃতীয় পক্ষের দাবি বা মামলা নেই।',
      mutationStatus: 'Available',
      taxPaidThrough: 'বাংলা ১৪৩২ সন',
      serviceChargeStatus: 'পরিশোধিত ও জমা আছে',
      approvalDetails: 'গুলশান-২ আবাসিক এলাকা, রাজউক বিক্রয় অনুমতি বিক্রেতা নিজ খরচে নিবেন।',
      documentSummary: 'নিষ্কণ্টক / টোটাল কাগজ-পাতি আপডেট কমপ্লিট।',
      sourceDate: '2026-09-14',
      updatedOn: '2026-09-14'
    },
    history: [
      { id: 1, date: 'Sep 2026', event: 'Listed on GBREL Mandate Catalog', price: 1202600000, status: 'Active', notes: 'Exclusive proposal dated 14-09-2026' }
    ],
    estimates: {
      marketEstimate: 1220000000,
      lowEstimate: 1180000000,
      highEstimate: 1260000000,
      annualGrowthPct: 15.2,
      monthlyRentEstimate: 1500000,
      annualRoiPct: 7.0,
      pricePerSqftArea: 176852
    },
    comparables: [],
    schools: [
      { name: 'American International School Dhaka (AISD)', type: 'American Curriculum', rating: 4.95, distanceKm: 1.5, travelMins: 6, grades: 'Pre-K to Grade 12' }
    ],
    community: {
      neighborhood: 'Gulshan-2 Diplomatic Enclave',
      metroDistanceKm: 2.8,
      nearestMetroStation: 'MRT Line-6 Banani Station',
      hospitalDistanceKm: 1.2,
      nearestHospital: 'United Hospital Gulshan-2',
      airportDistanceKm: 9.8,
      safetyRating: 'Highest Tier Security / Embassy Protected Zone',
      amenitiesOverview: 'Surrounded by top embassies, high commissions, Gulshan 2 Circle, and Lake Park.',
      transitOverview: 'Immediate access to Kemal Ataturk Avenue and Gulshan North Avenue.'
    }
  },
  {
    id: 3,
    title: 'গুলশান-২, রোড ৪৮/৪বি — ৩১ কাঠা বাণিজ্যিক কর্নার প্লট ও ২ তলা দালান',
    slug: 'gulshan-2-road-48-4b-31-katha-commercial-corner-plot',
    tagline: '৩১ কাঠা বাণিজ্যিক কর্নার প্লট ও ২ তলা দালান | প্রতি কাঠা ১৪ কোটি (আলোচনা সাপেক্ষ)',
    description: 'গুলশান-২, রোড ৪৮/৪বি তে ৩১ কাঠা বাণিজ্যিক কর্নার প্লট ও ২ তলা দালান বিক্রয়ের প্রস্তাব। ওয়ারিশ সূত্রে ৩ জন বর্তমান মালিক এবং নামজারি সম্পন্ন আছে। কমার্শিয়াল অনুমোদনপ্রাপ্ত। বাংলা ১৪৩২ সনের অনলাইন খাজনা পরিশোধিত ও সার্ভিস চার্জ জমা আছে। নিষ্কণ্টক ও টোটাল কাগজ-পাতি আপডেট কমপ্লিট। জমির দখল মালিকের। বিক্রিত মূল্যের ৩০% বায়না। আর্থিক লেনদেন ব্যাংকের মাধ্যমে। রাজউক থেকে বিক্রয়ের অনুমতি প্রাপ্তি ১৫ (পনের) কার্যদিবস। বিক্রয় অনুমতি পত্র ও সার্ভিস চার্জ বিক্রেতা বহন করবেন।',
    address: 'রোড ৪৮/৪বি, গুলশান-২, ঢাকা-১২১২',
    city: 'Dhaka',
    state: 'Dhaka North',
    areaName: 'Gulshan-2',
    price: 4340000000,
    priceUnit: 'প্রতি কাঠা ৳ ১৪.০০ কোটি (আলোচনা সাপেক্ষ)',
    listingType: 'Sale',
    propertyType: 'Commercial',
    status: 'Active',
    bedrooms: 10,
    bathrooms: 10,
    balconies: 6,
    squareFootage: 12000,
    landSize: 31.0,
    landUnit: 'Katha',
    parking: 20,
    floorNumber: 1,
    totalFloors: 2,
    facing: 'South-East',
    completionStatus: 'Ready',
    yearBuilt: 2002,
    isFeatured: true,
    isRajukApproved: true,
    isVerified: true,
    hasOpenHouse: true,
    openHouseDate: 'Monday, 2:00 PM - 6:00 PM',
    lat: 23.7915,
    lng: 90.4138,
    images: [
      'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?q=80&w=1600&auto=format&fit=crop',
      'https://images.unsplash.com/photo-1545324418-cc1a3fa10c00?q=80&w=1200&auto=format&fit=crop',
      'https://images.unsplash.com/photo-1577495508048-b635879837f1?q=80&w=1200&auto=format&fit=crop',
      'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?q=80&w=1200&auto=format&fit=crop'
    ],
    amenities: [
      '31 Katha Commercial Approved Corner Plot',
      'Double Road Corner Frontage with High Visibility',
      'Suitable for 25+ Storey Corporate Headquarters / Luxury Mixed Tower',
      'Existing 2-Storey Concrete Building',
      'High-Tension Power Substation & Commercial Utility Line',
      'Dispute-Free Inheritance Title with Updated Namjari',
      'Seller Bears RAJUK Permission & Service Charge',
      'Direct Owner Possession'
    ],
    documentsVerified: [
      'মালিকানা দলিল ও ওয়ারিশান সনদ (Inheritance Title & Succession)',
      'অনুমোদিত নামজারি ও জমাভাগ খতিয়ান (Completed Mutation)',
      'বাংলা ১৪৩২ সনের অনলাইন খাজনা পরিশোধ রশিদ',
      'কমার্শিয়াল ব্যবহারের সরকারি অনুমোদন সনদ',
      'সার্ভিস চার্জ ও ডকুমেন্টেশন ফি জমা রশিদ',
      'নিষ্কণ্টক ও নির্ভেজাল টাইটেল রিপোর্ট (Clear Title Audit)'
    ],
    agentId: 3,
    buyerDetails: {
      landUse: 'Commercial',
      cornerPlot: 'Yes',
      roadWidth: 80,
      buildingDescription: '৩১ কাঠা কর্নার প্লটে ২ তলা দালান বিদ্যমান। মাল্টি-স্টোরি কমার্শিয়াল টাওয়ার নির্মাণের জন্য আদর্শ।',
      utilities: 'বাণিজ্যিক বিদ্যুৎ, গ্যাস ও উচ্চ ক্ষমতাসম্পন্ন ওয়াসা লাইন।',
      priceBasis: 'Per land unit',
      negotiable: 'Yes',
      priceIncludes: 'প্রতি কাঠা ১৪ কোটি টাকা হিসেবে মোট ৩১ কাঠা বাণিজ্যিক কর্নার প্লট ও বিদ্যমান ২ তলা দালান (মোট মূল্য ৳ ৪৩৪.০০ কোটি আলোচনা সাপেক্ষ)।',
      depositPercent: 30,
      agreementDuration: 'চুক্তির পর ১২০ কার্যদিবস',
      paymentSchedule: 'বায়না ৩০%, অবশিষ্ট ৭০% রাজউক বিক্রয় অনুমতি সম্পন্ন হওয়ার পর রেজিস্ট্রি কালে ব্যাংকের মাধ্যমে।',
      paymentMethod: 'ব্যাংক পে-অর্ডার / আরটিজিএস',
      registrationCost: 'সরকারি কমার্শিয়াল রেজিস্ট্রেশন কর ক্রেতার নিজ দায়িত্বে।',
      buyerCosts: 'রেজিস্ট্রেশন ফি ও অন্যান্য সরকারি শুল্ক ক্রেতা বহন করবেন।',
      sellerCosts: 'বিক্রয় অনুমতি পত্র ও সার্ভিস চার্জ বিক্রেতা বহন করবেন।',
      transferTimeline: 'রাজউক থেকে বিক্রয়ের অনুমতি প্রাপ্তির ১৫ কার্যদিবসের মধ্যে।',
      transferTrigger: 'রাজউক বিক্রয় অনুমতি পত্র প্রকাশের পর।',
      ownerCount: 3,
      ownershipSource: 'Inheritance',
      possession: 'Owner',
      bankLoan: 'None declared',
      existingAgreement: 'None declared',
      saleAuthority: 'ওয়ারিশান ৩ জন মালিক যৌথভাবে',
      ownershipNotes: 'ওয়ারিশ সূত্রে নামজারি সম্পন্ন, ৩ জন মালিকের কোনো বিরোধ নেই।',
      mutationStatus: 'Available',
      taxPaidThrough: 'বাংলা ১৪৩২ সন',
      serviceChargeStatus: 'জমা দেওয়া আছে',
      approvalDetails: 'কমার্শিয়াল অনুমোদন প্রাপ্ত, রাজউক বিক্রয় অনুমতি বিক্রেতা সম্পন্ন করে দিবেন।',
      documentSummary: 'নিষ্কণ্টক / টোটাল কাগজ-পাতি আপডেট কমপ্লিট।',
      sourceDate: '2026-09-15',
      updatedOn: '2026-09-15'
    },
    history: [
      { id: 1, date: 'Sep 2026', event: 'Listed on GBREL Mandate Catalog', price: 4340000000, status: 'Active', notes: 'Commercial mandate with full corner frontage' }
    ],
    estimates: {
      marketEstimate: 4400000000,
      lowEstimate: 4250000000,
      highEstimate: 4550000000,
      annualGrowthPct: 18.0,
      monthlyRentEstimate: 6500000,
      annualRoiPct: 8.5,
      pricePerSqftArea: 361666
    },
    comparables: [],
    schools: [
      { name: 'Canadian International School Bangladesh', type: 'Canadian Curriculum', rating: 4.85, distanceKm: 1.0, travelMins: 4, grades: 'Pre-K to Grade 12' }
    ],
    community: {
      neighborhood: 'Gulshan-2 Commercial Hub',
      metroDistanceKm: 2.3,
      nearestMetroStation: 'MRT Line-6 Banani Station',
      hospitalDistanceKm: 1.4,
      nearestHospital: 'United Hospital Dhaka',
      airportDistanceKm: 9.5,
      safetyRating: 'Elite Commercial Enclave Security',
      amenitiesOverview: 'Heart of Gulshan-2 financial and diplomatic commercial corridor.',
      transitOverview: 'Wide multi-lane avenue connection to Madani Avenue and Gulshan-2 Circle.'
    }
  },
  {
    id: 4,
    title: 'লেক ভিউ — গুলশান-১ রোড ৮, বাড়ি ১০ এ ২৩ কাঠা জমিতে ৬ তলা বাণিজ্যিক ভবন',
    slug: 'lake-view-gulshan-1-road-8-23-katha-building',
    tagline: 'লেক ভিউ | ২৩ কাঠা জমিতে ৬ তলা ভবন ও ২৮টি কার পার্কিং | মূল্য ১৩৫ কোটি (আলোচনা সাপেক্ষ)',
    description: 'লেক ভিউ — বাড়ি #১০, রোড #৮, গুলশান-১, ঢাকা এ অবস্থিত ২৩ কাঠা জমির উপর ৬ তলা বিশিষ্ট সুদৃশ্য ভবন। ইউটিলিটি সরবরাহ সম্পূর্ণ আপডেটেড এবং ২৮টি কার পার্কিং সুবিধা রয়েছে। বর্তমান মালিক ২ জন (মূল মালিক মোঃ তওফিকুল ইসলাম)। জমি মালিকের সরাসরি দখলে এবং নামজারি ও খাজনা পরিশোধিত। সম্পূর্ণ নিষ্কণ্টক ও টোটাল কাগজ-পাতি আপডেট কমপ্লিট। কোনো ব্যাংক লোন নেই। বিক্রিত মূল্যের ৩০% বায়না। রাজউক থেকে বিক্রয়ের অনুমতি প্রাপ্তি ১৫ কার্যদিবস। ৩০০ টাকার নন-জুডিশিয়াল স্ট্যাম্পে হস্তান্তর চুক্তি সম্পন্ন হবে।',
    address: 'বাড়ি ১০, রোড ৮, গুলশান-১, ঢাকা-১২১২',
    city: 'Dhaka',
    state: 'Dhaka North',
    areaName: 'Gulshan-1',
    price: 1350000000,
    priceUnit: 'মোট মূল্য ৳ ১৩৫ কোটি (আলোচনা সাপেক্ষে কম হতে পারে)',
    listingType: 'Sale',
    propertyType: 'Duplex',
    status: 'Active',
    bedrooms: 16,
    bathrooms: 18,
    balconies: 12,
    squareFootage: 26000,
    landSize: 23.0,
    landUnit: 'Katha',
    parking: 28,
    floorNumber: 1,
    totalFloors: 6,
    facing: 'South',
    completionStatus: 'Ready',
    yearBuilt: 2018,
    isFeatured: true,
    isRajukApproved: true,
    isVerified: true,
    hasOpenHouse: true,
    openHouseDate: 'Saturday, 11:00 AM - 4:00 PM',
    lat: 23.7798,
    lng: 90.4148,
    images: [
      'https://images.unsplash.com/photo-1512917774080-9991f1c4c750?q=80&w=1600&auto=format&fit=crop',
      'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?q=80&w=1200&auto=format&fit=crop',
      'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?q=80&w=1200&auto=format&fit=crop',
      'https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?q=80&w=1200&auto=format&fit=crop'
    ],
    amenities: [
      '23 Katha Lakeside Prime Land in Gulshan-1',
      '6-Storey Modern Completed Architectural Edifice',
      '28 Dedicated Covered Car Parking Bays',
      'Independent 500KVA Substation & Full Generator Backup',
      'High-Speed Dual Passenger Elevators',
      'Direct Lake View and Expansive Terrace Gardens',
      'Owner Physical Possession with 24/7 Security Post',
      'Comprehensive Utility Supply 100% Updated'
    ],
    documentsVerified: [
      'মূল নিষ্কণ্টক স্বত্ব দলিল ও বণ্টননামা (Freehold Title Deed)',
      'হালনাগাদ নামজারি পর্চা ও জমাভাগ রেকর্ড (Updated Mutation)',
      'অনলাইন ভূমি উন্নয়ন কর ও খাজনা পরিশোধ রশিদ',
      'রাজউক অনুমোদিত ৬ তলা ভবনের স্থাপত্য ও কাঠামোগত নকশা',
      'ফায়ার সার্ভিস ও পরিবেশ ছাড়পত্র সনদ',
      '৩০০ টাকার নন-জুডিশিয়াল স্ট্যাম্পে কার্যকারী চুক্তিপত্র প্রস্তুত'
    ],
    agentId: 1,
    buyerDetails: {
      landUse: 'Residential',
      cornerPlot: 'No',
      roadWidth: 60,
      buildingDescription: 'লেক ভিউ: ২৩ কাঠা জমিতে ৬ তলা বিশিষ্ট আধুনিক ভবন। কার পার্কিং সংখ্যা ২৮টি।',
      utilities: 'ইউটিলিটি সরবরাহ সম্পূর্ণ আপডেট—বিদ্যুৎ সাবস্টেশন, গ্যাস সংযোগ ও গভীর নলকূপ ওয়াসা।',
      priceBasis: 'Total',
      negotiable: 'Yes',
      priceIncludes: '২৩ কাঠা জমি, ৬ তলা সম্পূর্ণ ভবন ও ২৮টি কার পার্কিং সহ মোট মূল্য ১৩৫ কোটি টাকা (আলোচনা সাপেক্ষে কম হতে পারে)।',
      depositPercent: 30,
      agreementDuration: 'চুক্তির তারিখ হতে ৯০ দিন',
      paymentSchedule: 'বায়না ৩০%, কার্যকারী পেমেন্ট শিডিউল ৩০০ টাকার নন-জুডিশিয়াল স্ট্যাম্পের মাধ্যমে চুক্তি সাপেক্ষে।',
      paymentMethod: 'ব্যাংক ড্রাফট / পে-অর্ডারের মাধ্যমে',
      registrationCost: 'ক্রেতা নিজ দায়িত্বে রেজিস্ট্রেশন সম্পন্ন করবেন।',
      buyerCosts: 'ক্রেতা নিজ দায়িত্বে রেজিস্ট্রেশন ও হস্তান্তরের খরচ করবেন।',
      sellerCosts: 'সার্ভিস চার্জ ও রাজউক অনুমতি প্রক্রিয়াকরণ।',
      transferTimeline: 'রাজউক থেকে বিক্রয়ের অনুমতি প্রাপ্তি ১৫ (পনের) কার্যদিবস।',
      transferTrigger: 'রাজউক অনুমোদন ও যৌথ চুক্তি সম্পাদনের পর।',
      ownerCount: 2,
      ownershipSource: 'Purchase',
      possession: 'Owner',
      bankLoan: 'None declared',
      existingAgreement: 'None declared',
      saleAuthority: 'মালিক মোঃ তওফিকুল ইসলাম (প্রতিনিধি: মোঃ আবু হানিফ)',
      ownershipNotes: 'মালিক ২ জন, কোনো ব্যাংক লোন বা আইনি ঝামেলা নেই, সম্পূর্ণ নিষ্কণ্টক।',
      mutationStatus: 'Available',
      taxPaidThrough: 'হালনাগাদ (নামজারি ও খাজনা পরিশোধ করা আছে)',
      serviceChargeStatus: 'আপডেট আছে',
      approvalDetails: 'রাজউক অনুমোদিত ভবন, বিক্রয়ের অনুমতি প্রাপ্তি ১৫ কার্যদিবসের মধ্যে।',
      documentSummary: 'নিষ্কণ্টক / টোটাল কাগজ-পাতি আপডেট কমপ্লিট।',
      sourceDate: '2026-09-15',
      updatedOn: '2026-09-15'
    },
    history: [
      { id: 1, date: 'Sep 2026', event: 'Listed on GBREL Mandate Catalog', price: 1350000000, status: 'Active', notes: 'Exclusive mandate for Lake View on Road 8' }
    ],
    estimates: {
      marketEstimate: 1380000000,
      lowEstimate: 1320000000,
      highEstimate: 1420000000,
      annualGrowthPct: 16.0,
      monthlyRentEstimate: 2800000,
      annualRoiPct: 7.8,
      pricePerSqftArea: 51923
    },
    comparables: [],
    schools: [
      { name: 'Scholastica Senior Campus Gulshan', type: 'English Medium', rating: 4.9, distanceKm: 1.0, travelMins: 4, grades: 'Class 6 to A Levels' }
    ],
    community: {
      neighborhood: 'Gulshan-1 Lakefront Avenue',
      metroDistanceKm: 2.0,
      nearestMetroStation: 'Mohakhali / Hatirjheel Terminal',
      hospitalDistanceKm: 1.5,
      nearestHospital: 'United Hospital / Gulshan Clinic',
      airportDistanceKm: 10.2,
      safetyRating: 'VIP Residential Security & 24/7 Patrol',
      amenitiesOverview: 'Direct lakefront frontage on Road 8, steps away from Gulshan-1 park and diplomatic clubs.',
      transitOverview: 'Direct wide-road access connecting Gulshan Avenue and Hatirjheel circular drive.'
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
    videoUrl: apiItem.video_url || apiItem.videoUrl || undefined,
    videoPoster: apiItem.video_poster || apiItem.videoPoster || undefined,
    coverMedia: (apiItem.cover_media || apiItem.coverMedia) === 'video' ? 'video' : 'image',
    hidePrice: Boolean(apiItem.hide_price ?? apiItem.hidePrice),
    priceDisplayText: apiItem.price_display_text || apiItem.priceDisplayText || 'Price on Application',
    hideAgentPhoto: Boolean(apiItem.hide_agent_photo ?? apiItem.hideAgentPhoto),
    hideAgentContact: Boolean(apiItem.hide_agent_contact ?? apiItem.hideAgentContact),
    hideExactAddress: Boolean(apiItem.hide_exact_address ?? apiItem.hideExactAddress),
    hideFloorPlan: Boolean(apiItem.hide_floor_plan ?? apiItem.hideFloorPlan),
    hideMortgageCalculator: Boolean(apiItem.hide_mortgage_calculator ?? apiItem.hideMortgageCalculator),
    brochuresVault: Array.isArray(apiItem.brochures_vault) ? apiItem.brochures_vault : [],
    agentId: Number(apiItem.agent_id) || Number(apiItem.agentId) || 0,
    ownerId: apiItem.owner_id ? Number(apiItem.owner_id) : null,
    reviewStatus: apiItem.review_status || null,
    publishedAt: apiItem.published_at || null,
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

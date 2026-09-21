<template>
  <div class="homepage-root">
    <!-- ======================================================================
         1. LUXURY HERO SECTION
         ====================================================================== -->
    <section class="hero-section">
      <div class="hero-bg-overlay"></div>
      <div class="container hero-content">
        <!-- Hero Trust Pill -->
        <div class="hero-tag animate-fade-in">
          <span class="hero-tag-dot" aria-hidden="true"></span>
          <span>RAJUK & CDA Verified Bangladesh Brokerage</span>
        </div>
        
        <h1 class="hero-title animate-fade-in-up">
          Verified Penthouses, <span>Freehold Plots</span> & Beachfront Assets
        </h1>
        
        <p class="hero-subtitle animate-fade-in-up">
          RAJUK & CDA approved apartments, corner plots in Katha/Bigha, and beachfront hotel suites across Dhaka, Chittagong, Sylhet, and Cox's Bazar.
        </p>

        <!-- Multi-Intent Real Estate Search Engine -->
        <div class="hero-search-wrapper animate-fade-in-up">
          <HeroSearch />
        </div>
      </div>
    </section>

    <!-- ======================================================================
         2. DUE DILIGENCE & MARKET IMPACT TRUST BAR
         ====================================================================== -->
    <section class="trust-bar-section">
      <div class="container">
        <div class="grid grid-4 trust-grid">
          <div class="trust-item">
            <div class="trust-value">৳ 650+ Cr</div>
            <div class="trust-title">Prime Assets Managed</div>
            <div class="trust-desc">Across Dhaka, Chittagong & Cox's Bazar</div>
          </div>

          <div class="trust-item">
            <div class="trust-value">100% Verified</div>
            <div class="trust-title">RAJUK & CDA Compliance</div>
            <div class="trust-desc">CS, RS, BS Khatians & Mutation cleared</div>
          </div>

          <div class="trust-item">
            <div class="trust-value">15 Minutes</div>
            <div class="trust-title">Direct Advisor SLA</div>
            <div class="trust-desc">Instant WhatsApp & phone callback</div>
          </div>

          <div class="trust-item">
            <div class="trust-value">0% Encumbrance</div>
            <div class="trust-title">Bank Escrow Security</div>
            <div class="trust-desc">Settlement via DBH, IDLC & BRAC Bank</div>
          </div>
        </div>
      </div>
    </section>

    <!-- ======================================================================
         3. PROPERTY ASSET CATEGORIES (BANGLADESH SPECIALIZED)
         ====================================================================== -->
    <section class="section-padded" style="background: #FFFFFF;">
      <div class="container">
        <div class="section-header">
          <div class="section-tag">Asset Classes</div>
          <h2 class="section-title">Specialized Real Estate Portfolios</h2>
          <p class="section-subtitle">
            Whether securing family residential roots or deploying commercial capital, explore targeted asset classes tailored to the Bangladeshi market.
          </p>
        </div>

        <div class="category-grid">
          <!-- 1. Flats & Penthouses - LEAD CARD -->
          <NuxtLink to="/properties?type=Flat" class="category-card category-card-lead">
            <div class="category-img-wrap">
              <img src="https://images.unsplash.com/photo-1600585154340-be6161a56a0c?q=80&w=600&auto=format&fit=crop" alt="Flats & Penthouses" />
              <span class="category-badge">Ready Flats</span>
            </div>
            <div class="category-body">
              <h3>Flats & Luxury Penthouses</h3>
              <p>Gulshan, Banani, Dhanmondi, and Uttara with lake views & rooftop amenities.</p>
              <div class="category-link">Browse Flats ({{ flatsCount }}) →</div>
            </div>
          </NuxtLink>

          <!-- 2. Plots & Lands -->
          <NuxtLink to="/properties?type=Plot" class="category-card">
            <div class="category-img-wrap">
              <img src="https://images.unsplash.com/photo-1500382017468-9049fed747ef?q=80&w=600&auto=format&fit=crop" alt="Plots & Lands" />
              <span class="category-badge">Katha / Bigha</span>
            </div>
            <div class="category-body">
              <h3>Residential Plots & Lands</h3>
              <p>Purbachal Smart City, Jalshiri, and Bashundhara freehold land with clear boundary demarcation.</p>
              <div class="category-link">Browse Plots ({{ plotsCount }}) →</div>
            </div>
          </NuxtLink>

          <!-- 3. Land Share Projects (NEW) -->
          <NuxtLink to="/properties?type=Land+Share" class="category-card" style="border-color: #DDD6FE;">
            <div class="category-img-wrap">
              <img src="https://images.unsplash.com/photo-1540555700478-4be289fbecef?q=80&w=600&auto=format&fit=crop" alt="Land Share Projects" />
              <span class="category-badge" style="background:#7C3AED; color:#FFF;">Cost-Basis Co-Ownership</span>
            </div>
            <div class="category-body">
              <h3 style="color: #6D28D9;">Land Share Projects (জমি শেয়ার)</h3>
              <p>Direct proportional land deed sub-registry with transparent actual-cost construction in Purbachal & Dhaka.</p>
              <div class="category-link" style="color: #7C3AED;">Explore Land Shares ({{ landShareCount }}) →</div>
            </div>
          </NuxtLink>

          <!-- 4. Beach Resorts & Suites -->
          <NuxtLink to="/properties?type=Hotel" class="category-card">
            <div class="category-img-wrap">
              <img src="https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?q=80&w=600&auto=format&fit=crop" alt="Resorts & Suites" />
              <span class="category-badge">14% Annual Yield</span>
            </div>
            <div class="category-body">
              <h3>Beach Resorts & Hotel Suites</h3>
              <p>Cox's Bazar Marine Drive and Inani Beach hospitality fractional suites with passive revenue.</p>
              <div class="category-link">Browse Resorts ({{ hotelCount }}) →</div>
            </div>
          </NuxtLink>

          <!-- 5. Commercial & Duplexes -->
          <NuxtLink to="/properties?type=Duplex" class="category-card">
            <div class="category-img-wrap">
              <img src="https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?q=80&w=600&auto=format&fit=crop" alt="Duplexes" />
              <span class="category-badge">Private Villas</span>
            </div>
            <div class="category-body">
              <h3>Exclusive Duplex Villas</h3>
              <p>Gated compounds in Baridhara, Bashundhara Block-I, and Sylhet tea-valley estates.</p>
              <div class="category-link">Browse Duplexes ({{ duplexCount }}) →</div>
            </div>
          </NuxtLink>
        </div>
      </div>
    </section>

    <!-- ======================================================================
         4. FEATURED EXCLUSIVE MANDATES & RESIDENCES
         ====================================================================== -->
    <section class="section-padded" style="background: #F8FAFC; border-top: 1px solid var(--color-border); border-bottom: 1px solid var(--color-border);">
      <div class="container">
        <div class="flex justify-between items-end flex-wrap gap-4" style="margin-bottom: 40px;">
          <div>
            <div class="section-tag">Handpicked Collection</div>
            <h2 class="section-title" style="margin-bottom: 0;">Featured Verified Properties</h2>
            <p class="section-subtitle" style="margin-top: 6px;">
              Ready for immediate handover with title deeds examined by our Supreme Court legal panel.
            </p>
          </div>
          <NuxtLink to="/properties" class="btn btn-outline">
            <span>Explore All {{ properties.length }} Mandates →</span>
          </NuxtLink>
        </div>

        <div class="grid grid-3">
          <PropertyCard 
            v-for="property in featuredProperties" 
            :key="property.id" 
            :property="property" 
          />
        </div>
      </div>
    </section>

    <!-- ======================================================================
         5. INTERACTIVE GEOLOCATION MAP EXPLORER
         ====================================================================== -->
    <section class="section-padded" style="background: #FFFFFF;">
      <div class="container">
        <div class="section-header">
          <div class="section-tag">Interactive Map</div>
          <h2 class="section-title">Explore Verified Locations Across Bangladesh</h2>
          <p class="section-subtitle">
            Browse verified listings pinpointed across prime Dhaka enclaves, Purbachal Express corridors, and Cox's Bazar coastline.
          </p>
        </div>

        <ClientOnly>
          <InteractiveMap :properties="properties" />
          <template #fallback>
            <div class="map-skeleton-placeholder">
              <div class="map-skeleton-pulse"></div>
              <p>Loading Interactive Property Map...</p>
            </div>
          </template>
        </ClientOnly>
      </div>
    </section>

    <!-- ======================================================================
         6. NON-RESIDENT BANGLADESHI (NRB) CONCIERGE DESK
         ====================================================================== -->
    <section class="nrb-section">
      <div class="container">
        <div class="nrb-grid">
          <div>
            <span class="badge badge-rajuk" style="margin-bottom: 14px;">GLOBAL BANGLADESHI INVESTORS</span>
            <h2 class="nrb-title">
              NRI & NRB Real Estate Concierge
            </h2>
            <p class="nrb-desc">
              Tailored advisory for expatriate Bangladeshis in the <strong>UK, USA, Canada, UAE, and Saudi Arabia</strong>. We handle foreign currency remittances, Power of Attorney legal vetting, live video walkthroughs, and airport-to-site chauffeur tours.
            </p>

            <div class="nrb-features">
              <div class="nrb-feat-item">
                <span class="nrb-check">✔</span>
                <span>Direct Bank Remittance Escrow (Bangladesh Bank Compliant)</span>
              </div>
              <div class="nrb-feat-item">
                <span class="nrb-check">✔</span>
                <span>Power of Attorney (PoA) Attestation Assistance</span>
              </div>
              <div class="nrb-feat-item">
                <span class="nrb-check">✔</span>
                <span>Airport VIP Chauffeur Pickup from Hazrat Shahjalal (DAC)</span>
              </div>
              <div class="nrb-feat-item">
                <span class="nrb-check">✔</span>
                <span>HD 4K Video Tour & Drone Boundary Survey</span>
              </div>
            </div>

            <div class="flex gap-3 flex-wrap" style="margin-top: 28px;">
              <a :href="`https://wa.me/${cleanWhatsapp}?text=Hi%20GBREL,%20I%20am%20an%20NRB%20investor%20interested%20in%20property%20acquisitions.`" target="_blank" class="btn btn-emerald btn-lg" style="background: #25D366;">
                <span>WhatsApp Expat Advisory Desk</span>
              </a>
              <NuxtLink to="/contact" class="btn btn-outline-white btn-lg">
                <span>Schedule Video Call</span>
              </NuxtLink>
            </div>
          </div>

          <div class="nrb-card">
            <h3 style="font-size: 1.25rem; font-weight: 800; color: #FFFFFF; margin-bottom: 16px;">
              Home Loan & Bank Financing Rates
            </h3>
            <p style="font-size: 0.85rem; color: #CBD5E1; margin-bottom: 20px;">
              Benchmark home loan rates from our certified banking partners:
            </p>

            <div class="bank-rate-item">
              <div class="flex justify-between items-center">
                <strong>DBH Finance</strong>
                <span class="rate-badge">{{ settings.dbhRate || '9.25%' }} p.a.</span>
              </div>
              <div class="bank-sub">Up to 85% Property Valuation • 25 Yr Tenure</div>
            </div>

            <div class="bank-rate-item">
              <div class="flex justify-between items-center">
                <strong>IDLC Finance</strong>
                <span class="rate-badge">{{ settings.idlcRate || '9.50%' }} p.a.</span>
              </div>
              <div class="bank-sub">Fast 5-day NRI Approval SLA • Zero Hidden Fee</div>
            </div>

            <div class="bank-rate-item">
              <div class="flex justify-between items-center">
                <strong>BRAC Bank</strong>
                <span class="rate-badge">{{ settings.bracRate || '9.40%' }} p.a.</span>
              </div>
              <div class="bank-sub">Special Expatriate Remittance Rebates</div>
            </div>

            <NuxtLink to="/properties" class="btn btn-sm btn-gold" style="width: 100%; margin-top: 16px;">
              Calculate EMI on Any Listing →
            </NuxtLink>
          </div>
        </div>
      </div>
    </section>

    <!-- ======================================================================
         7. SELLER & LANDOWNER ONBOARDING CALL TO ACTION
         ====================================================================== -->
    <section class="seller-cta-section">
      <div class="container">
        <div class="seller-cta-box">
          <div class="seller-cta-content">
            <span class="badge badge-featured" style="margin-bottom: 12px;">DIRECT PROPERTY OWNERS & BUILDERS</span>
            <h2 class="seller-cta-title">
              Ready to Sell Your Plot, Land or Luxury Flat?
            </h2>
            <p class="seller-cta-desc">
              List your property with Bangladesh’s most reputable brokerage. We connect you with 50,000+ pre-vetted buyers and provide legal title certification.
            </p>
            <div class="flex gap-3 flex-wrap" style="margin-top: 24px;">
              <NuxtLink to="/signup" class="btn btn-gold btn-lg">
                <span>Create Seller Account & List Property →</span>
              </NuxtLink>
              <NuxtLink to="/list-property" class="btn btn-outline-white btn-lg">
                <span>Direct Listing Form</span>
              </NuxtLink>
            </div>
          </div>
          <div class="seller-cta-stats">
            <div class="stat-bubble">
              <div class="stat-label">Average deal closing time</div>
              <div class="stat-number">28 Days</div>
            </div>
            <div class="stat-bubble">
              <div class="stat-label">Success commission, no upfront fee</div>
              <div class="stat-number">2% Only</div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup lang="ts">
import { computed, onMounted } from 'vue'
import { useProperties } from '~/composables/useProperties'
import { useSettings } from '~/composables/useSettings'
import HeroSearch from '~/components/HeroSearch.vue'
import PropertyCard from '~/components/PropertyCard.vue'
import InteractiveMap from '~/components/InteractiveMap.vue'

definePageMeta({
  layout: 'default'
})

const { properties, fetchProperties } = useProperties()
const { settings, fetchSettings } = useSettings()

onMounted(async () => {
  await Promise.all([
    fetchProperties(),
    fetchSettings()
  ])
})

const cleanWhatsapp = computed(() => {
  const num = settings.value.whatsapp_number || '8801819987654'
  return num.replace(/[^0-9]/g, '')
})

const featuredProperties = computed(() => {
  const featured = properties.value.filter(p => p.isFeatured)
  return featured.length > 0 ? featured.slice(0, 6) : properties.value.slice(0, 6)
})

const flatsCount = computed(() => properties.value.filter(p => p.propertyType === 'Flat').length)
const plotsCount = computed(() => properties.value.filter(p => p.propertyType === 'Plot' || p.propertyType === 'Land').length)
const landShareCount = computed(() => properties.value.filter(p => p.propertyType === 'Land Share').length)
const hotelCount = computed(() => properties.value.filter(p => p.propertyType === 'Hotel').length)
const duplexCount = computed(() => properties.value.filter(p => p.propertyType === 'Duplex' || p.propertyType === 'Penthouse').length)
</script>

<style scoped>
.homepage-root {
  overflow-x: hidden;
}

/* 1. Hero Section */
.hero-section {
  position: relative;
  background: #0A1128;
  background-image: radial-gradient(rgba(212, 175, 55, 0.08) 1px, transparent 0);
  background-size: 28px 28px;
  padding: 100px 0 80px;
  color: #FFFFFF;
  text-align: center;
}

.hero-content {
  max-width: 1140px;
  margin: 0 auto;
  position: relative;
  z-index: 2;
}

.hero-tag {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  background: rgba(212, 175, 55, 0.12);
  border: 1px solid rgba(212, 175, 55, 0.35);
  color: var(--color-gold-bright);
  padding: 8px 18px;
  border-radius: var(--radius-full);
  font-size: 0.8rem;
  font-weight: 700;
  letter-spacing: 0.06em;
  margin-bottom: 24px;
}

.hero-tag-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: var(--color-gold-bright);
  flex-shrink: 0;
}

.hero-title {
  font-family: var(--font-display);
  font-size: var(--text-display);
  font-weight: 800;
  line-height: 1.1;
  color: #FFFFFF;
  letter-spacing: -0.02em;
  margin-bottom: 20px;
  text-wrap: balance;
}

.hero-title span {
  color: var(--color-gold-bright);
}

.hero-subtitle {
  font-size: 1.15rem;
  color: #CBD5E1;
  max-width: 760px;
  margin: 0 auto 36px;
  line-height: 1.6;
}

.hero-search-wrapper {
  max-width: 1080px;
  margin: 0 auto 28px;
  width: 100%;
}

/* 2. Trust Bar */
.trust-bar-section {
  background: #0F172A;
  color: #FFFFFF;
  padding: 36px 0;
  border-bottom: 1px solid rgba(255, 255, 255, 0.08);
}

.trust-item {
  padding: 12px 16px;
  text-align: left;
  border-right: 1px solid rgba(255, 255, 255, 0.08);
}

.trust-item:last-child {
  border-right: none;
}

.trust-value {
  font-family: var(--font-ui);
  font-size: 1.6rem;
  font-weight: 800;
  line-height: 1.15;
  color: var(--color-gold-bright);
  font-variant-numeric: tabular-nums;
}

.trust-title {
  font-weight: 700;
  font-size: 0.92rem;
  color: #FFFFFF;
  margin-top: 6px;
}

.trust-desc {
  font-size: 0.8rem;
  color: #CBD5E1;
  margin-top: 2px;
}

/* 3. Specialized Asset Classes */
.section-padded {
  padding: 80px 0;
}

.category-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
  gap: 24px;
}

.category-card {
  background: #FFFFFF;
  border: 1px solid var(--color-border);
  border-radius: var(--radius-xl);
  overflow: hidden;
  text-decoration: none;
  display: flex;
  flex-direction: column;
  transition: all var(--transition-normal);
  box-shadow: var(--shadow-sm);
}

.category-card-lead {
  border-color: var(--color-gold);
  box-shadow: var(--shadow-md);
}

.category-img-wrap {
  position: relative;
  height: 170px;
}

.category-img-wrap img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.category-badge {
  position: absolute;
  top: 12px;
  left: 12px;
  background: rgba(10, 17, 40, 0.9);
  color: var(--color-gold-bright);
  font-size: 0.72rem;
  font-weight: 700;
  padding: 4px 10px;
  border-radius: 4px;
  text-transform: uppercase;
}

.category-body {
  padding: 20px;
  flex: 1;
  display: flex;
  flex-direction: column;
}

.category-body h3 {
  font-size: 1.15rem;
  font-weight: 800;
  color: #0A1128;
  line-height: 1.25;
  margin-bottom: 8px;
}

.category-body p {
  font-size: 0.85rem;
  color: #64748B;
  line-height: 1.5;
  margin-bottom: 14px;
  flex: 1;
}

.category-link {
  font-size: 0.85rem;
  font-weight: 700;
  color: #059669;
}

/* 6. NRB Concierge Section */
.nrb-section {
  background: #0A1128;
  color: #FFFFFF;
  padding: 80px 0;
  border-top: 1px solid rgba(255, 255, 255, 0.08);
}

.nrb-grid {
  display: grid;
  grid-template-columns: 1.2fr 0.8fr;
  gap: 48px;
  align-items: center;
}

.nrb-title {
  font-family: var(--font-display);
  font-size: var(--text-headline);
  font-weight: 800;
  color: #FFFFFF;
  line-height: 1.2;
  margin-bottom: 16px;
}

.nrb-desc {
  font-size: 1.02rem;
  color: #CBD5E1;
  line-height: 1.7;
  margin-bottom: 24px;
}

.nrb-features {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.nrb-feat-item {
  display: flex;
  align-items: center;
  gap: 10px;
  font-size: 0.92rem;
  color: #E2E8F0;
}

.nrb-check {
  color: #10B981;
  font-weight: 900;
  font-size: 1.1rem;
}

.nrb-card {
  background: #0F172A;
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: var(--radius-2xl);
  padding: 32px;
}

.bank-rate-item {
  background: rgba(255, 255, 255, 0.03);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: var(--radius-md);
  padding: 12px 16px;
  margin-bottom: 10px;
}

.rate-badge {
  background: rgba(16, 185, 129, 0.15);
  border: 1px solid rgba(16, 185, 129, 0.3);
  color: #34D399;
  font-weight: 800;
  font-size: 0.85rem;
  padding: 2px 8px;
  border-radius: 4px;
}

.bank-sub {
  font-size: 0.78rem;
  color: #CBD5E1;
  margin-top: 4px;
}

/* 7. Seller CTA */
.seller-cta-section {
  padding: 80px 0;
  background: #F8FAFC;
}

.seller-cta-box {
  background: #0A1128;
  border: 1px solid rgba(212, 175, 55, 0.25);
  border-radius: var(--radius-2xl);
  padding: 48px;
  color: #FFFFFF;
  display: grid;
  grid-template-columns: 1.3fr 0.7fr;
  gap: 36px;
  align-items: center;
}

.seller-cta-title {
  font-family: var(--font-display);
  font-size: var(--text-headline);
  font-weight: 800;
  color: #FFFFFF;
  line-height: 1.2;
  margin-bottom: 12px;
}

.seller-cta-desc {
  font-size: 1.02rem;
  color: #CBD5E1;
  line-height: 1.7;
}

.seller-cta-stats {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.stat-bubble {
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: var(--radius-xl);
  padding: 20px;
}

.stat-label {
  font-size: 0.8rem;
  color: #CBD5E1;
  margin-bottom: 6px;
}

.stat-number {
  font-family: var(--font-ui);
  font-size: 1.8rem;
  font-weight: 800;
  color: var(--color-gold-bright);
  font-variant-numeric: tabular-nums;
}

/* Responsive Media Queries */
@media (max-width: 992px) {
  .hero-title {
    font-size: 2.4rem;
  }
  .trust-grid {
    grid-template-columns: repeat(2, 1fr) !important;
    gap: 16px;
  }
  .trust-item {
    border-right: none;
    border-bottom: 1px solid rgba(255, 255, 255, 0.08);
  }
  .category-grid {
    grid-template-columns: repeat(2, 1fr) !important;
  }
  .nrb-grid {
    grid-template-columns: 1fr;
  }
  .seller-cta-box {
    grid-template-columns: 1fr;
    padding: 32px 24px;
  }
}

@media (max-width: 640px) {
  .hero-section {
    padding: 64px 0 56px;
  }
  .hero-title {
    font-size: 1.85rem;
  }
  .hero-subtitle {
    font-size: 0.98rem;
  }
  .trust-grid {
    grid-template-columns: 1fr !important;
  }
  .category-grid {
    grid-template-columns: 1fr !important;
  }
  .nrb-title {
    font-size: 1.7rem;
  }
  .seller-cta-box {
    padding: 28px 20px;
    gap: 24px;
  }
  .seller-cta-title {
    font-size: 1.6rem;
  }
}

.map-skeleton-placeholder {
  width: 100%;
  height: 520px;
  background: #0A1128;
  border-radius: var(--radius-xl);
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 16px;
  color: #CBD5E1;
  font-size: 0.95rem;
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.map-skeleton-pulse {
  width: 44px;
  height: 44px;
  border: 3px solid rgba(212, 175, 55, 0.2);
  border-top-color: var(--color-gold);
  border-radius: 50%;
  animation: mapSpin 1s linear infinite;
}

@keyframes mapSpin {
  to { transform: rotate(360deg); }
}
</style>

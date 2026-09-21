<template>
  <div class="directors-page">
    <!-- ======================================================================
         1. HERO HEADER (CLEAN & DIGNIFIED)
         ====================================================================== -->
    <header class="page-hero">
      <div class="container">
        <nav class="breadcrumb-nav" aria-label="Breadcrumb">
          <NuxtLink to="/" class="breadcrumb-link">Home</NuxtLink>
          <span class="breadcrumb-separator">/</span>
          <span class="breadcrumb-current">Board of Directors</span>
        </nav>

        <div class="hero-inner">
          <span class="hero-tag">Corporate Leadership</span>
          <h1 class="hero-heading">Board of Directors</h1>
          <p class="hero-lead">
            The leadership team at Gram Bangla Real Estate Ltd. (GBREL) brings together experienced professionals across real estate planning, civil project execution, finance, and technology to guide our land and residential developments in Bangladesh.
          </p>
        </div>
      </div>
    </header>

    <!-- ======================================================================
         2. MANAGING DIRECTOR'S STATEMENT & PROFILE
         ====================================================================== -->
    <section class="md-section">
      <div class="container">
        <div class="md-profile-card">
          <div class="md-layout">
            <!-- MD Portrait -->
            <div class="md-media-pane">
              <div class="md-photo-frame">
                <img 
                  :src="encodeURI('/img/MD-Abdul-Quder-Talukder -Managing -Director.png')" 
                  alt="MD Abdul Quder Talukder - Managing Director"
                  class="md-photo"
                />
              </div>
              <div class="md-caption">
                <div class="md-caption-name">MD Abdul Quder Talukder</div>
                <div class="md-caption-role">Founder & Managing Director</div>
              </div>
            </div>

            <!-- MD Executive Message -->
            <div class="md-content-pane">
              <div class="md-brand-lockup">
                <img src="/img/logo-full.png" alt="Gram Bangla Real Estate Limited" class="corporate-brand-logo" />
              </div>
              <div class="section-label">Executive Message</div>
              <h2 class="md-headline">Building Trust Through Verified Ownership and Long-Term Value</h2>

              <div class="md-statement">
                <p>
                  At Gram Bangla Real Estate Ltd. (GBREL), our fundamental priority is ensuring complete peace of mind for property buyers and investors. Real estate in Bangladesh represents a lifetime of savings and generational security, which is why we hold ourselves to rigorous standards in land title verification and regulatory compliance.
                </p>
                <p>
                  Every project we undertake—from prime residential sectors in Purbachal to commercial and coastal developments—undergoes thorough legal vetting across CS, SA, RS, and City Survey records before development begins. We believe that transparent documentation, scheduled project delivery, and honest communication are the cornerstones of lasting customer relationships.
                </p>
                <p>
                  Along with our Board of Directors, I invite you to explore our verified properties and work with our team with complete confidence.
                </p>
              </div>

              <div class="md-footer-row">
                <div class="md-signoff">
                  <div class="md-sign-name">MD Abdul Quder Talukder</div>
                  <div class="md-sign-title">Managing Director, Gram Bangla Real Estate Ltd.</div>
                </div>
                <div class="md-contact-touch">
                  <a href="mailto:md@gbrel.com" class="btn-clean-contact">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                      <polyline points="22,6 12,13 2,6"/>
                    </svg>
                    <span>Contact Managing Director's Office</span>
                  </a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ======================================================================
         3. BOARD OF DIRECTORS GRID
         ====================================================================== -->
    <section class="directors-section">
      <div class="container">
        <div class="section-intro">
          <span class="section-tag">Executive Council</span>
          <h2 class="section-title">Board of Directors & Executive Leadership</h2>
          <p class="section-subtitle">
            Meet the directors overseeing strategic planning, client engagement, technological innovation, finance, and civil project execution at GBREL.
          </p>
        </div>

        <div class="directors-grid">
          <div 
            v-for="director in directors" 
            :key="director.id" 
            class="director-card"
          >
            <!-- Card Image Header -->
            <div class="card-image-header">
              <div class="portrait-container">
                <img 
                  :src="director.image" 
                  :alt="director.name + ' - ' + director.designation"
                  class="director-portrait-img"
                />
              </div>
            </div>

            <!-- Card Content Body -->
            <div class="card-body">
              <div class="director-header-group">
                <span class="role-pill">{{ director.roleBadge }}</span>
                <h3 class="director-name">{{ director.name }}</h3>
                <div class="director-designation">{{ director.designation }}</div>
              </div>

              <p class="director-bio">
                {{ director.bio }}
              </p>

              <!-- Key Responsibilities -->
              <div class="responsibilities-block">
                <div class="resp-heading">Key Oversight & Responsibilities:</div>
                <ul class="resp-list">
                  <li v-for="(item, idx) in director.responsibilities" :key="idx">
                    <svg class="check-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                      <polyline points="20 6 9 17 4 12"/>
                    </svg>
                    <span>{{ item }}</span>
                  </li>
                </ul>
              </div>

              <!-- Direct Corporate Contact -->
              <div class="card-footer-action">
                <a :href="`mailto:${director.email}`" class="contact-email-link">
                  <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                    <polyline points="22,6 12,13 2,6"/>
                  </svg>
                  <span>{{ director.email }}</span>
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ======================================================================
         4. CORPORATE STANDARDS & GOVERNANCE
         ====================================================================== -->
    <section class="standards-section">
      <div class="container">
        <div class="standards-card">
          <div class="standards-logo-frame">
            <img src="/img/logo-mark.png" alt="GBREL Logo" class="standards-logo-mark" />
          </div>
          <div class="standards-content">
            <h3 class="standards-title">Gram Bangla Real Estate Limited (GBREL)</h3>
            <p class="standards-desc">
              GBREL adheres strictly to Real Estate regulatory standards in Bangladesh. Every land transaction is supported by certified mutation records, non-encumbrance verification, and transparent payment schedules to protect the interests of domestic buyers and expatriates.
            </p>
          </div>
          <div class="standards-action">
            <NuxtLink to="/contact" class="btn btn-outline-white">
              <span>Contact Head Office</span>
            </NuxtLink>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup lang="ts">
import { useHead } from '#app'

useHead({
  title: 'Board of Directors | GBREL (Gram Bangla Real Estate Ltd.)',
  meta: [
    {
      name: 'description',
      content: 'Learn about the Board of Directors and executive leadership at Gram Bangla Real Estate Ltd. (GBREL). Professional management dedicated to verified land titles, client advisory, and quality urban developments.'
    }
  ]
})

// --------------------------------------------------------------------------
// Directors Data (Realistic, Professional, Authentic)
// --------------------------------------------------------------------------
const directors = [
  {
    id: 'md-abu-hanif',
    name: 'Md. Abu Hanif',
    designation: 'Director, Administration & Accounts',
    roleBadge: 'Director, Admin & Accounts',
    image: encodeURI('/img/abu-hanif-circle.png'),
    email: 'accounts@gbrel.com',
    bio: 'Md. Abu Hanif oversees corporate administration, financial management, treasury operations, and statutory audit compliance at GBREL. He ensures all customer payment plans, client fund accounting, and vendor disbursements adhere to strict corporate financial controls and legal regulations.',
    responsibilities: [
      'Corporate financial planning, budgeting, and treasury management',
      'Client installment accounting, escrow verification, and fund protection',
      'Statutory audits, tax compliance, and corporate regulatory filings',
      'General administrative operations and organizational governance'
    ]
  },
  {
    id: 'eng-siam-talukder',
    name: 'Engr. Siam Talukder',
    designation: 'Deputy Managing Director (DMD) & Director, Strategic Planning & Client Relations',
    roleBadge: 'Deputy Managing Director',
    image: encodeURI('/img/siam-talukder-linkedin-circle.png'),
    email: 'siam.talukder@gbrel.com',
    bio: 'Engr. Siam Talukder plays a central leadership role at GBREL, directing strategic planning, project conceptualization, and client advisory. He works directly with private investors, corporate partners, and Non-Resident Bangladeshis (NRBs), presenting project masterplans, architectural viability, and long-term land valuation.',
    responsibilities: [
      'Strategic planning and masterplan project presentations for major land developments',
      'VIP and NRB client advisory, portfolio consultations, and investment structuring',
      'High-value land acquisitions, landowner partnerships, and joint-venture negotiations',
      'Corporate business development and investor relations'
    ]
  },
  {
    id: 'eng-sher-ali-khan',
    name: 'Engr. Sher Ali Khan',
    designation: 'Co-Founder, Director & Chief Technology Officer (CTO)',
    roleBadge: 'Co-Founder & CTO',
    image: encodeURI('/img/Eng Sher Ali khan Director Marketing & IT .png'),
    email: 'sherali@gbrel.com',
    bio: 'Engr. Sher Ali Khan is a Co-Founding Director and the Chief Technology Officer leading GBREL’s digital transformation and technology infrastructure. With extensive background in software engineering, he oversees the company’s PropTech discovery platforms, GIS spatial plot mapping, automated customer relationship systems, and digital investor services.',
    responsibilities: [
      'Architecting and scaling GBREL’s PropTech web, mobile, and client platforms',
      'Interactive GIS land mapping and digital plot boundary verification systems',
      'Customer acquisition infrastructure, digital marketing, and automated CRM pipelines',
      'Data security, digital title records access, and overseas buyer portal services'
    ]
  },
  {
    id: 'md-rubel-hawlader',
    name: 'Md. Rubel Hawlader',
    designation: 'Director, Project & Transport',
    roleBadge: 'Director, Project & Transport',
    image: encodeURI('/img/rubel-hawlader-circle.png'),
    email: 'projects@gbrel.com',
    bio: 'Md. Rubel Hawlader directs on-ground civil construction, earthwork operations, heavy equipment transport, and site logistics across all GBREL sectors. He manages project execution teams to ensure on-schedule boundary demarcation, earth filling, road development, and quality engineering standards.',
    responsibilities: [
      'Civil construction supervision, earth filling, and ground development works',
      'Fleet logistics and heavy excavation machinery management',
      'Site surveying, physical boundary demarcation, and corner plot layout',
      'Contractor supervision and project safety compliance'
    ]
  }
]
</script>

<style scoped>
/* ==========================================================================
   BOARD OF DIRECTORS PAGE - CLEAN, LUXURY CORPORATE AESTHETIC
   ========================================================================== */

.directors-page {
  background-color: #F8FAFC;
  min-height: 100vh;
  color: #0F172A;
}

/* 1. Page Hero */
.page-hero {
  background: #0A1128;
  color: #FFFFFF;
  padding: 44px 0 54px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.08);
}

.breadcrumb-nav {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 0.85rem;
  color: #94A3B8;
  margin-bottom: 24px;
}

.breadcrumb-link {
  color: #CBD5E1;
  text-decoration: none;
  transition: color var(--transition-fast);
}

.breadcrumb-link:hover {
  color: #D4AF37;
}

.breadcrumb-separator {
  color: #64748B;
}

.breadcrumb-current {
  color: #D4AF37;
  font-weight: 600;
}

.hero-inner {
  max-width: 820px;
}

.hero-tag {
  display: inline-block;
  font-size: 0.78rem;
  font-weight: 700;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  color: #D4AF37;
  background: rgba(212, 175, 55, 0.12);
  padding: 4px 12px;
  border-radius: 4px;
  margin-bottom: 14px;
}

.hero-heading {
  font-family: var(--font-display);
  font-size: clamp(2rem, 3.5vw, 2.8rem);
  font-weight: 800;
  color: #FFFFFF;
  line-height: 1.2;
  margin-bottom: 14px;
  letter-spacing: -0.01em;
}

.hero-lead {
  font-size: 1.05rem;
  line-height: 1.65;
  color: #CBD5E1;
}

/* 2. MD Section */
.md-section {
  padding: 48px 0 56px;
}

.md-profile-card {
  background: #FFFFFF;
  border: 1px solid var(--color-border);
  border-radius: var(--radius-xl);
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
  overflow: hidden;
  padding: 36px;
}

.md-layout {
  display: grid;
  grid-template-columns: 320px 1fr;
  gap: 40px;
  align-items: center;
}

.md-media-pane {
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
}

.md-photo-frame {
  width: 100%;
  height: 420px;
  border-radius: var(--radius-lg);
  overflow: hidden;
  background: #FFFFFF;
  border: 1px solid var(--color-border);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 8px;
}

.md-photo {
  width: 100%;
  height: 100%;
  object-fit: contain;
  object-position: center;
  display: block;
}

.md-caption {
  margin-top: 14px;
}

.md-caption-name {
  font-family: var(--font-display);
  font-size: 1.15rem;
  font-weight: 700;
  color: #0A1128;
}

.md-caption-role {
  font-size: 0.85rem;
  color: #059669;
  font-weight: 600;
  margin-top: 2px;
}

.md-content-pane {
  display: flex;
  flex-direction: column;
}

.section-label {
  font-size: 0.78rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  color: #059669;
  margin-bottom: 8px;
}

.md-headline {
  font-family: var(--font-display);
  font-size: 1.65rem;
  font-weight: 800;
  color: #0A1128;
  line-height: 1.3;
  margin-bottom: 20px;
}

.md-statement {
  display: flex;
  flex-direction: column;
  gap: 14px;
  color: #475569;
  font-size: 0.95rem;
  line-height: 1.7;
}

.md-footer-row {
  margin-top: 24px;
  padding-top: 20px;
  border-top: 1px solid var(--color-border);
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 16px;
}

.md-sign-name {
  font-weight: 700;
  color: #0A1128;
  font-size: 1rem;
}

.md-sign-title {
  font-size: 0.82rem;
  color: #64748B;
}

.btn-clean-contact {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  font-size: 0.85rem;
  font-weight: 600;
  color: #059669;
  background: #ECFDF5;
  border: 1px solid rgba(5, 150, 105, 0.2);
  padding: 8px 14px;
  border-radius: var(--radius-sm);
  text-decoration: none;
  transition: all var(--transition-fast);
}

.btn-clean-contact:hover {
  background: #059669;
  color: #FFFFFF;
}

/* 3. Directors Section */
.directors-section {
  padding: 32px 0 72px;
}

.section-intro {
  max-width: 720px;
  margin-bottom: 40px;
}

.section-tag {
  display: inline-block;
  font-size: 0.75rem;
  font-weight: 700;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  color: #059669;
  background: #ECFDF5;
  padding: 3px 10px;
  border-radius: 4px;
  margin-bottom: 10px;
}

.section-title {
  font-family: var(--font-display);
  font-size: 2rem;
  font-weight: 800;
  color: #0A1128;
  line-height: 1.2;
  margin-bottom: 10px;
}

.section-subtitle {
  color: #64748B;
  font-size: 0.98rem;
  line-height: 1.6;
}

.directors-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 32px;
}

.director-card {
  background: #FFFFFF;
  border: 1px solid var(--color-border);
  border-radius: var(--radius-xl);
  overflow: hidden;
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04);
  display: flex;
  flex-direction: column;
  transition: transform var(--transition-normal), box-shadow var(--transition-normal), border-color var(--transition-normal);
}

.director-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 12px 32px rgba(10, 17, 40, 0.08);
  border-color: rgba(212, 175, 55, 0.4);
}

/* Redesigned Card Image Header */
.card-image-header {
  position: relative;
  height: 420px;
  background: #FFFFFF;
  border-bottom: 1px solid var(--color-border);
  overflow: hidden;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 8px;
}

.portrait-container {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #FFFFFF;
}

.director-portrait-img {
  width: 100%;
  height: 100%;
  object-fit: contain;
  object-position: center;
  display: block;
  transition: transform var(--transition-smooth);
}

.director-card:hover .director-portrait-img {
  transform: scale(1.02);
}

.role-pill {
  display: inline-block;
  font-size: 0.72rem;
  font-weight: 700;
  letter-spacing: 0.05em;
  text-transform: uppercase;
  color: #0F172A;
  background: #F1F5F9;
  border: 1px solid var(--color-border);
  padding: 4px 10px;
  border-radius: var(--radius-sm);
  margin-bottom: 8px;
}

/* Card Body */
.card-body {
  padding: 28px;
  display: flex;
  flex-direction: column;
  flex: 1;
}

.director-header-group {
  margin-bottom: 16px;
  padding-bottom: 14px;
  border-bottom: 1px solid var(--color-border);
}

.director-name {
  font-family: var(--font-display);
  font-size: 1.45rem;
  font-weight: 800;
  color: #0A1128;
  line-height: 1.25;
}

.director-designation {
  font-size: 0.88rem;
  font-weight: 600;
  color: #059669;
  margin-top: 4px;
}

.director-bio {
  font-size: 0.92rem;
  line-height: 1.65;
  color: #475569;
  margin-bottom: 20px;
}

.responsibilities-block {
  background: #F8FAFC;
  border: 1px solid var(--color-border);
  border-radius: var(--radius-md);
  padding: 16px 18px;
  margin-bottom: 22px;
}

.resp-heading {
  font-size: 0.75rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  color: #64748B;
  margin-bottom: 10px;
}

.resp-list {
  list-style: none;
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.resp-list li {
  display: flex;
  align-items: flex-start;
  gap: 8px;
  font-size: 0.84rem;
  color: #334155;
  line-height: 1.45;
}

.check-icon {
  color: #059669;
  flex-shrink: 0;
  margin-top: 3px;
}

.card-footer-action {
  margin-top: auto;
  padding-top: 14px;
  border-top: 1px solid var(--color-border);
}

.contact-email-link {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  font-size: 0.85rem;
  font-weight: 600;
  color: #2563EB;
  text-decoration: none;
  transition: color var(--transition-fast);
}

.contact-email-link:hover {
  color: #1D4ED8;
  text-decoration: underline;
}

/* 4. Standards Section */
.standards-section {
  padding-bottom: 80px;
}

.standards-card {
  background: #0A1128;
  border-radius: var(--radius-xl);
  padding: 36px 44px;
  color: #FFFFFF;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 32px;
}

.standards-logo-frame {
  width: 68px;
  height: 68px;
  background: #FFFFFF;
  border-radius: var(--radius-md);
  padding: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.standards-logo-mark {
  width: 100%;
  height: 100%;
  object-fit: contain;
}

.md-brand-lockup {
  margin-bottom: 14px;
}

.corporate-brand-logo {
  height: 44px;
  max-width: 260px;
  object-fit: contain;
  display: block;
}

.standards-title {
  font-family: var(--font-display);
  font-size: 1.35rem;
  font-weight: 800;
  color: #FFFFFF;
  margin-bottom: 8px;
}

.standards-desc {
  font-size: 0.92rem;
  color: #CBD5E1;
  max-width: 680px;
  line-height: 1.6;
}

.standards-action {
  flex-shrink: 0;
}

/* ==========================================================================
   RESPONSIVE
   ========================================================================== */
@media (max-width: 1024px) {
  .md-layout {
    grid-template-columns: 280px 1fr;
    gap: 32px;
  }

  .directors-grid {
    grid-template-columns: 1fr;
    gap: 28px;
  }

  .card-image-header {
    height: 340px;
  }
}

@media (max-width: 768px) {
  .md-layout {
    grid-template-columns: 1fr;
    gap: 24px;
  }

  .md-photo-frame {
    height: 320px;
  }

  .standards-card {
    flex-direction: column;
    align-items: flex-start;
    padding: 28px;
  }
}
</style>

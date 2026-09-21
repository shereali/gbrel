<template>
  <div class="directors-page">
    <!-- Breadcrumb and Hero -->
    <header class="page-hero">
      <div class="container">
        <nav class="breadcrumb-nav" aria-label="Breadcrumb">
          <NuxtLink to="/" class="breadcrumb-link">Home</NuxtLink>
          <span class="breadcrumb-separator">/</span>
          <span class="breadcrumb-current">Board of Directors</span>
        </nav>

        <div class="hero-inner">
          <h1 class="hero-heading">Board of Directors</h1>
          <p class="hero-lead">
            The leadership team guiding project planning, land verification, finance, and operations at Gram Bangla Real Estate Limited.
          </p>
        </div>
      </div>
    </header>

    <!-- Managing Director Profile -->
    <section class="md-section">
      <div class="container">
        <div class="md-profile-card">
          <div class="md-layout">
            <!-- MD Portrait -->
            <div class="md-media-pane">
              <div class="md-photo-frame">
                <img 
                  :src="encodeURI('/img/MD-Abdul-Quder-Talukder -Managing -Director.png')" 
                  alt="MD Abdul Quder Talukder, Managing Director"
                  class="md-photo"
                />
              </div>
              <div class="md-caption">
                <div class="md-caption-name">MD Abdul Quder Talukder</div>
                <div class="md-caption-role">Founder and Managing Director</div>
              </div>
            </div>

            <!-- MD Message -->
            <div class="md-content-pane">
              <div class="md-brand-lockup">
                <img src="/img/logo-full.png" alt="Gram Bangla Real Estate Limited" class="corporate-brand-logo" />
              </div>
              <h2 class="md-headline">A Message from the Managing Director</h2>

              <div class="md-statement">
                <p>
                  At Gram Bangla Real Estate Limited, our primary aim is to assist buyers in securing genuine property with complete legal security. Investing in land or housing in Bangladesh represents years of patient saving, and our buyers deserve absolute clarity at every step of the process.
                </p>
                <p>
                  Before we take up any development, our legal associates review CS, SA, RS, and City Survey records directly through land offices to ensure every title is authentic and dispute free. We take full ownership of document verification and physical boundary protection.
                </p>
                <p>
                  Together with our directors and field engineers, we remain committed to straightforward dealings, scheduled handovers, and responsive service for clients living in Bangladesh and abroad.
                </p>
              </div>

              <div class="md-footer-row">
                <div class="md-signoff">
                  <div class="md-sign-name">MD Abdul Quder Talukder</div>
                  <div class="md-sign-title">Founder and Managing Director, Gram Bangla Real Estate Limited</div>
                </div>
                <div class="md-contact-touch">
                  <a href="mailto:md@gbrel.com" class="btn-clean-contact">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                      <polyline points="22,6 12,13 2,6"/>
                    </svg>
                    <span>Contact Managing Director Office</span>
                  </a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Board of Directors Grid -->
    <section class="directors-section">
      <div class="container">
        <div class="section-intro">
          <h2 class="section-title">Board of Directors</h2>
          <p class="section-subtitle">
            Leadership team overseeing strategic planning, client relations, technology infrastructure, accounts, and site development.
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
                  :alt="director.name + ', ' + director.designation"
                  class="director-portrait-img"
                />
              </div>
            </div>

            <!-- Card Content Body -->
            <div class="card-body">
              <div class="director-header-group">
                <h3 class="director-name">{{ director.name }}</h3>
                <div class="director-designation">{{ director.designation }}</div>
              </div>

              <p class="director-bio">
                {{ director.bio }}
              </p>

              <p class="director-bio-extra">
                {{ director.bioExtra }}
              </p>

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

    <!-- Corporate Contact Banner -->
    <section class="standards-section">
      <div class="container">
        <div class="standards-card">
          <div class="standards-logo-frame">
            <img src="/img/logo-mark.png" alt="Gram Bangla Real Estate Limited" class="standards-logo-mark" />
          </div>
          <div class="standards-content">
            <h3 class="standards-title">Gram Bangla Real Estate Limited</h3>
            <p class="standards-desc">
              For plot inquiries, project visits, or document verification, visit our corporate office or contact our team directly.
            </p>
          </div>
          <div class="standards-action">
            <NuxtLink to="/contact" class="btn btn-outline-white">
              <span>Contact Office</span>
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
  title: 'Board of Directors | Gram Bangla Real Estate Limited',
  meta: [
    {
      name: 'description',
      content: 'Board of Directors at Gram Bangla Real Estate Limited. Leadership guiding land verification, planning, finance, and civil construction in Bangladesh.'
    }
  ]
})

const directors = [
  {
    id: 'md-abu-hanif',
    name: 'Md. Abu Hanif',
    designation: 'Director, Administration and Accounts',
    image: encodeURI('/img/abu-hanif-circle.png'),
    email: 'accounts@gbrel.com',
    bio: 'Md. Abu Hanif oversees corporate administration, internal accounting, and financial management at Gram Bangla Real Estate Limited. He supervises day to day banking transactions, client payment schedules, vendor billing, and project expenditures.',
    bioExtra: 'Working closely with audit professionals and legal advisors, he ensures tax compliance and sound financial governance across all ongoing land development projects.'
  },
  {
    id: 'eng-siam-talukder',
    name: 'Engr. Siam Talukder',
    designation: 'Deputy Managing Director',
    image: encodeURI('/img/siam-talukder-linkedin-circle.png'),
    email: 'siam.talukder@gbrel.com',
    bio: 'Engr. Siam Talukder leads business planning, project presentations, and corporate client relations at Gram Bangla Real Estate Limited. He meets directly with property buyers, institutional clients, and non resident Bangladeshis to explain master layouts, road connectivity, and legal clearances.',
    bioExtra: 'He directs joint venture discussions with landowners, monitors project feasibility, and coordinates sales and marketing initiatives across current and upcoming project areas.'
  },
  {
    id: 'eng-sher-ali-khan',
    name: 'Engr. Sher Ali Khan',
    designation: 'Co Founder and Chief Technology Officer',
    image: encodeURI('/img/Eng Sher Ali khan Director Marketing & IT .png'),
    email: 'sherali@gbrel.com',
    bio: 'Engr. Sher Ali Khan is a Co Founder and the Chief Technology Officer in charge of digital systems and operational technology at Gram Bangla Real Estate Limited. He developed the company web platform, digital plot booking workflows, and internal management tools.',
    bioExtra: 'With a background in software engineering, he focuses on reliable digital services that allow clients in Bangladesh and abroad to view property documents, track project milestones, and communicate with company representatives.'
  },
  {
    id: 'md-rubel-hawlader',
    name: 'Md. Rubel Hawlader',
    designation: 'Director, Project and Transport',
    image: encodeURI('/img/rubel-hawlader-circle.png'),
    email: 'projects@gbrel.com',
    bio: 'Md. Rubel Hawlader manages heavy equipment transport, earth filling, and field operations across all project sites. He coordinates survey teams, civil contractors, and site supervisors during land development and boundary demarcations.',
    bioExtra: 'He is present on location to supervise road carpeting, equipment deployment, and safety procedures, ensuring site works progress according to schedule and technical standards.'
  }
]
</script>

<style scoped>
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

.md-brand-lockup {
  margin-bottom: 14px;
}

.corporate-brand-logo {
  height: 44px;
  max-width: 260px;
  object-fit: contain;
  display: block;
}

.md-headline {
  font-family: var(--font-display);
  font-size: 1.65rem;
  font-weight: 800;
  color: #0A1128;
  line-height: 1.3;
  margin-bottom: 18px;
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

/* Card Image Header */
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
  margin-bottom: 12px;
}

.director-bio-extra {
  font-size: 0.92rem;
  line-height: 1.65;
  color: #475569;
  margin-bottom: 20px;
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
    height: 360px;
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

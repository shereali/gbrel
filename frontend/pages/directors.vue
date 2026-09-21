<template>
  <div class="directors-page">
    <!-- ======================================================================
         1. HERO HEADER SECTION
         ====================================================================== -->
    <section class="leadership-hero">
      <div class="container">
        <!-- Breadcrumbs -->
        <nav class="breadcrumb-nav" aria-label="Breadcrumb">
          <NuxtLink to="/" class="breadcrumb-link">Home</NuxtLink>
          <span class="breadcrumb-separator">/</span>
          <span class="breadcrumb-active">Corporate Leadership & Directors</span>
        </nav>

        <div class="hero-content">
          <div class="badge-leadership-tag">
            <span class="pulse-dot"></span>
            EXECUTIVE LEADERSHIP & GOVERNANCE
          </div>
          <h1 class="hero-title">
            The Visionary Minds Driving Bangladesh's <span class="gold-gradient-text">Real Estate Evolution</span>
          </h1>
          <p class="hero-subtitle">
            Underpinned by absolute legal integrity, zero-dispute land acquisition, and cutting-edge PropTech engineering, 
            the GBREL Board of Directors steers Bangladesh's most trusted real estate enterprise.
          </p>

          <!-- Executive Key Milestones -->
          <div class="leadership-stats-strip">
            <div class="stat-pill">
              <div class="stat-num">20+</div>
              <div class="stat-label">Years Corporate Legacy</div>
            </div>
            <div class="stat-divider"></div>
            <div class="stat-pill">
              <div class="stat-num">100%</div>
              <div class="stat-label">RAJUK, CDA & Legal Deed Verified</div>
            </div>
            <div class="stat-divider"></div>
            <div class="stat-pill">
              <div class="stat-num">10,000+</div>
              <div class="stat-label">Satisfied Landowners & NRBs</div>
            </div>
            <div class="stat-divider"></div>
            <div class="stat-pill">
              <div class="stat-num">2,500+</div>
              <div class="stat-label">Acres Developed & Masterplanned</div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ======================================================================
         2. MANAGING DIRECTOR'S KEYNOTE & SPOTLIGHT
         ====================================================================== -->
    <section class="md-spotlight-section">
      <div class="container">
        <div class="md-card">
          <div class="md-grid">
            <!-- MD Portrait Column -->
            <div class="md-image-col">
              <div class="md-image-frame">
                <img 
                  :src="encodeURI('/img/MD-Abdul-Quder-Talukder -Managing -Director.png')" 
                  alt="MD Abdul Quder Talukder - Managing Director of GBREL"
                  class="md-photo"
                  loading="eager"
                />
                <div class="md-seal-badge">
                  <div class="seal-icon">★</div>
                  <div class="seal-text">FOUNDER & MD</div>
                </div>
              </div>

              <!-- Quick MD Contact & Meeting Trigger -->
              <div class="md-action-box">
                <button @click="openExecutiveModal(managingDirector)" class="btn btn-primary btn-md w-full">
                  <span>View Full Executive Dossier</span>
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                    <polyline points="12 5 19 12 12 19"></polyline>
                  </svg>
                </button>
                <button @click="openConsultationModal(managingDirector.name, managingDirector.designation)" class="btn btn-gold btn-md w-full">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                    <line x1="16" y1="2" x2="16" y2="6"></line>
                    <line x1="8" y1="2" x2="8" y2="6"></line>
                    <line x1="3" y1="10" x2="21" y2="10"></line>
                  </svg>
                  <span>Book Executive Consultation</span>
                </button>
              </div>
            </div>

            <!-- MD Narrative Column -->
            <div class="md-narrative-col">
              <div class="md-header-tag">
                <span class="role-badge">CHIEF EXECUTIVE KEYNOTE</span>
                <span class="tenure-badge">22+ Years in Land Mastery</span>
              </div>

              <h2 class="md-name">{{ managingDirector.name }}</h2>
              <div class="md-designation">{{ managingDirector.designation }}</div>
              
              <div class="md-headline-banner">
                <svg class="quote-icon" width="28" height="28" viewBox="0 0 24 24" fill="currentColor">
                  <path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h3.983v10h-9.983z"/>
                </svg>
                <div class="md-headline-text">
                  "{{ managingDirector.headline }}"
                </div>
              </div>

              <div class="md-bio-body">
                <p>
                  Welcome to <strong>Gram Bangla Real Estate Ltd. (GBREL)</strong>. When I established this company over two decades ago, Bangladesh's real estate market was marred by legal opacity, disputed land titles, and widespread buyer anxiety—particularly for hardworking expatriates living abroad.
                </p>
                <p>
                  I resolved that GBREL would operate under a distinct moral and institutional compass: <strong>Zero Legal Disputes, 100% Guaranteed Deed Succession, and Transparent Pricing</strong>. Every acre of land and square foot of residential property we bring to market is subjected to our uncompromising 7-tier legal title audit—cross-matching CS, SA, RS, and City Survey records with RAJUK and CDA approvals before a single transaction is conducted.
                </p>
                <p>
                  Today, GBREL stands as a premier real estate conglomerate in Bangladesh, delivering masterplanned residential communities in Purbachal, luxury duplexes in Gulshan & Banani, and world-class hospitality assets along Cox's Bazar Marine Drive. Together with our esteemed Board of Directors, we welcome you to experience a new standard of trust and generational investment.
                </p>
              </div>

              <!-- MD Key Pillars -->
              <div class="md-pillars-grid">
                <div class="pillar-box">
                  <div class="pillar-title">7-Tier Legal Clearance</div>
                  <div class="pillar-desc">Comprehensive CS, SA, RS, City Survey, DCR & Mutation validation.</div>
                </div>
                <div class="pillar-box">
                  <div class="pillar-title">NRB Asset Protection</div>
                  <div class="pillar-desc">Power of Attorney verification & transparent overseas repatriation escrow.</div>
                </div>
                <div class="pillar-box">
                  <div class="pillar-title">Modern Masterplanning</div>
                  <div class="pillar-desc">High elevation, planned avenues, modern drainage & green amenities.</div>
                </div>
              </div>

              <!-- Signature & Accreditation -->
              <div class="md-signature-row">
                <div>
                  <div class="signature-line">MD Abdul Quder Talukder</div>
                  <div class="signature-sub">Managing Director & Chairman of the Executive Committee</div>
                  <div class="signature-co">Gram Bangla Real Estate Ltd. (GBREL)</div>
                </div>
                <div class="compliance-seals">
                  <span class="badge badge-rajuk">RAJUK Registered</span>
                  <span class="badge badge-featured">REHAB Member</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ======================================================================
         3. BOARD OF DIRECTORS - EXECUTIVE PROFILES
         ====================================================================== -->
    <section class="board-members-section">
      <div class="container">
        <div class="section-header text-center">
          <span class="section-tag">Governing Council</span>
          <h2 class="section-title">Board of Directors & Functional Leadership</h2>
          <p class="section-desc">
            Bringing together specialized executive leadership across strategic masterplanning, client advisory, 
            cutting-edge PropTech architectures, institutional administration, and high-velocity project execution.
          </p>
        </div>

        <div class="directors-grid">
          <div 
            v-for="director in directors" 
            :key="director.id" 
            class="director-card"
          >
            <!-- Card Image Area -->
            <div class="director-image-wrapper">
              <img 
                :src="director.image" 
                :alt="director.name + ' - ' + director.designation"
                class="director-card-img"
                loading="lazy"
              />
              <div class="director-tag-floating">{{ director.departmentBadge }}</div>
              <div class="director-experience-badge">{{ director.experience }}</div>
            </div>

            <!-- Card Content -->
            <div class="director-card-body">
              <div class="director-role-tag">{{ director.designation }}</div>
              <h3 class="director-name">{{ director.name }}</h3>

              <!-- Realistic Executive Headline -->
              <div class="director-card-headline">
                "{{ director.headline }}"
              </div>

              <!-- Executive Summary -->
              <p class="director-card-bio">
                {{ director.shortBio }}
              </p>

              <!-- Key Areas of Responsibility -->
              <div class="director-responsibilities">
                <div class="resp-label">CORE GOVERNANCE PORTFOLIO:</div>
                <ul class="resp-list">
                  <li v-for="(item, idx) in director.keyOversight" :key="idx">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#10B981" stroke-width="2.5">
                      <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                    <span>{{ item }}</span>
                  </li>
                </ul>
              </div>

              <!-- Card Bottom Actions -->
              <div class="director-card-footer">
                <button 
                  @click="openExecutiveModal(director)" 
                  class="btn btn-outline btn-sm flex-1"
                >
                  <span>Detailed Profile</span>
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="16" x2="12" y2="12"></line>
                    <line x1="12" y1="8" x2="12.01" y2="8"></line>
                  </svg>
                </button>

                <button 
                  @click="openConsultationModal(director.name, director.designation)" 
                  class="btn btn-primary btn-sm"
                  title="Direct Executive Contact"
                >
                  <span>Inquire</span>
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                  </svg>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ======================================================================
         4. CORPORATE GOVERNANCE & ETHICAL COMMITMENTS
         ====================================================================== -->
    <section class="governance-pillars-section">
      <div class="container">
        <div class="section-header text-center">
          <span class="section-tag">Institutional Ethics</span>
          <h2 class="section-title">The Four Pillars of GBREL Corporate Governance</h2>
          <p class="section-desc">
            How our executive leadership maintains absolute security and value preservation for every plot, flat, and commercial holding.
          </p>
        </div>

        <div class="pillars-grid">
          <div class="governance-card">
            <div class="gov-icon gold">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
              </svg>
            </div>
            <h3 class="gov-title">Title Authenticity & Legal Security</h3>
            <p class="gov-text">
              Direct oversight by our legal and admin directorate guarantees that all properties have indisputable pedigree—free from Khas, Waqf, Vested, or forest reservation encumbrances.
            </p>
          </div>

          <div class="governance-card">
            <div class="gov-icon emerald">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
                <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
              </svg>
            </div>
            <h3 class="gov-title">Civil Engineering & Rapid Execution</h3>
            <p class="gov-text">
              Our project and transport directors deploy dedicated heavy machinery, modern soil consolidation techniques, and rigorous earthwork controls to meet delivery commitments ahead of schedule.
            </p>
          </div>

          <div class="governance-card">
            <div class="gov-icon blue">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="2" y1="12" x2="22" y2="12"></line>
                <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path>
              </svg>
            </div>
            <h3 class="gov-title">PropTech Innovation & Transparency</h3>
            <p class="gov-text">
              Pioneered by our Marketing & IT directors, client verification, digital GIS plotting, and real-time interactive asset tracking empower buyers in Bangladesh and overseas.
            </p>
          </div>

          <div class="governance-card">
            <div class="gov-icon red">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                <circle cx="9" cy="7" r="4"></circle>
                <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
              </svg>
            </div>
            <h3 class="gov-title">Fiduciary Transparency & Escrow</h3>
            <p class="gov-text">
              Every financial transaction is governed by structured escrow practices, transparent payment schedules, and zero-hidden-fee documentation certified by our administration division.
            </p>
          </div>
        </div>
      </div>
    </section>

    <!-- ======================================================================
         5. VIP EXECUTIVE CONSULTATION BANNER
         ====================================================================== -->
    <section class="executive-cta-section">
      <div class="container">
        <div class="executive-cta-card">
          <div class="cta-text-content">
            <span class="cta-tag">DIRECT EXECUTIVE ENGAGEMENT</span>
            <h2 class="cta-title">Schedule a Private Briefing with the Directorate</h2>
            <p class="cta-desc">
              Whether you are an institutional developer, an overseas Bangladeshi investor acquiring substantial land banks, 
              or a landowner interested in a joint venture, our Directors provide confidential high-level consultation.
            </p>
            <div class="cta-highlights">
              <div class="cta-item">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#10B981" stroke-width="2.5">
                  <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
                <span>Confidential HNW & NRB Asset Structuring</span>
              </div>
              <div class="cta-item">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#10B981" stroke-width="2.5">
                  <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
                <span>Direct Meeting at GBREL Gulshan Headquarters</span>
              </div>
              <div class="cta-item">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#10B981" stroke-width="2.5">
                  <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
                <span>Digital Video Conference for NRBs in UK, USA, Canada & Middle East</span>
              </div>
            </div>
          </div>
          <div class="cta-action-content">
            <button @click="openConsultationModal('GBREL Executive Directorate', 'Board of Directors')" class="btn btn-gold btn-lg w-full">
              <span>Request Executive Meeting</span>
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="5" y1="12" x2="19" y2="12"></line>
                <polyline points="12 5 19 12 12 19"></polyline>
              </svg>
            </button>
            <a 
              href="https://wa.me/8801819987654?text=Hello%20GBREL%20Executive%20Desk,%20I%20would%20like%20to%20schedule%20a%20consultation%20with%20the%20Directorate." 
              target="_blank" 
              class="btn btn-outline-white btn-lg w-full flex items-center justify-center gap-2"
              style="margin-top: 12px;"
            >
              <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.711 2.598 2.664-.698c.969.54 1.861.855 2.796.855 3.18 0 5.767-2.587 5.768-5.766 0-3.18-2.586-5.767-5.768-5.767zm7.531 5.766c-.002 4.153-3.38 7.531-7.531 7.531-.019 0-.038 0-.057 0-1.284 0-2.53-.332-3.64-.962l-4.053 1.063 1.082-3.953c-.707-1.16-1.082-2.493-1.082-3.864.002-4.153 3.38-7.531 7.531-7.531 4.153 0 7.531 3.378 7.531 7.531z"/>
              </svg>
              <span>Instant VIP WhatsApp Hotline</span>
            </a>
            <div class="hotline-notice">
              Direct Executive Desk: <strong>+880 1819-987654 / 09612-GBREL</strong>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ======================================================================
         6. DETAILED EXECUTIVE MODAL (EXPANDABLE DOSSIER)
         ====================================================================== -->
    <transition name="modal-fade">
      <div v-if="activeModalDirector" class="modal-overlay" @click.self="activeModalDirector = null">
        <div class="modal-card max-w-2xl">
          <button class="modal-close-btn" @click="activeModalDirector = null" aria-label="Close modal">✕</button>

          <div class="modal-director-header">
            <img 
              :src="activeModalDirector.image" 
              :alt="activeModalDirector.name" 
              class="modal-director-photo"
            />
            <div>
              <span class="badge badge-rajuk">{{ activeModalDirector.designation }}</span>
              <h2 class="modal-director-name">{{ activeModalDirector.name }}</h2>
              <div class="modal-director-sub">{{ activeModalDirector.academic || 'Senior Real Estate & Corporate Executive' }}</div>
            </div>
          </div>

          <div class="modal-director-body">
            <div class="modal-headline">
              "{{ activeModalDirector.headline }}"
            </div>

            <div class="modal-section-title">Professional Biography & Vision</div>
            <p class="modal-bio-text">{{ activeModalDirector.fullBio }}</p>

            <div class="modal-section-title" style="margin-top: 20px;">Governance Portfolios & Strategic Impact</div>
            <ul class="modal-impact-list">
              <li v-for="(impact, i) in activeModalDirector.achievements" :key="i">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#D4AF37" stroke-width="2.5">
                  <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
                <span>{{ impact }}</span>
              </li>
            </ul>

            <div class="modal-section-title" style="margin-top: 20px;">Direct Official Contact</div>
            <div class="modal-contact-grid">
              <div class="contact-pill">
                <span class="contact-label">Corporate Email:</span>
                <span class="contact-val">{{ activeModalDirector.email }}</span>
              </div>
              <div class="contact-pill">
                <span class="contact-label">Official Desk:</span>
                <span class="contact-val">+880 2 988-1234 (Ext: {{ activeModalDirector.ext }})</span>
              </div>
            </div>
          </div>

          <div class="modal-director-footer">
            <button 
              @click="scheduleFromModal" 
              class="btn btn-gold btn-md flex-1"
            >
              <span>Schedule Direct Meeting</span>
            </button>
            <button @click="activeModalDirector = null" class="btn btn-outline btn-md">
              <span>Close Dossier</span>
            </button>
          </div>
        </div>
      </div>
    </transition>

    <!-- ======================================================================
         7. EXECUTIVE CONSULTATION BOOKING MODAL
         ====================================================================== -->
    <transition name="modal-fade">
      <div v-if="consultationModalOpen" class="modal-overlay" @click.self="consultationModalOpen = false">
        <div class="modal-card max-w-lg">
          <button class="modal-close-btn" @click="consultationModalOpen = false" aria-label="Close modal">✕</button>
          
          <div class="text-center" style="margin-bottom: 24px;">
            <div class="badge-leadership-tag" style="margin-bottom: 8px;">EXECUTIVE INQUIRY</div>
            <h3 style="font-size: 1.4rem; font-weight: 800; color: #0A1128;">Request Consultation</h3>
            <p style="font-size: 0.88rem; color: #64748B;">
              Meeting requested with: <strong style="color: #059669;">{{ targetDirectorName }}</strong>
            </p>
          </div>

          <div v-if="consultationSent" class="alert alert-success" style="background:#ECFDF5; border:1px solid #10B981; color:#065F46; padding:16px; border-radius:10px; margin-bottom:18px; text-align:center;">
            <strong>Appointment Request Dispatched!</strong><br />
            Our Executive Secretary will review your schedule and connect with you via WhatsApp within 30 minutes.
          </div>

          <form v-else @submit.prevent="submitConsultation">
            <div class="form-group" style="margin-bottom: 14px;">
              <label class="form-label">Your Full Name</label>
              <input v-model="consultationForm.name" type="text" placeholder="e.g. Barrister / Engr. / Mr. Rahman" required class="form-input" />
            </div>

            <div class="form-group" style="margin-bottom: 14px;">
              <label class="form-label">Contact Phone / WhatsApp</label>
              <input v-model="consultationForm.phone" type="tel" placeholder="+880 17XX-XXXXXX or International No." required class="form-input" />
            </div>

            <div class="form-group" style="margin-bottom: 14px;">
              <label class="form-label">Email Address</label>
              <input v-model="consultationForm.email" type="email" placeholder="investor@example.com" required class="form-input" />
            </div>

            <div class="form-group" style="margin-bottom: 14px;">
              <label class="form-label">Meeting Modality</label>
              <select v-model="consultationForm.meetingType" class="form-select" style="width: 100%; padding: 10px 14px; border: 1px solid var(--color-border); border-radius: var(--radius-sm);">
                <option value="in_person">In-Person at GBREL Gulshan HQ (Level 14)</option>
                <option value="zoom_nrb">Virtual Video Conference (For NRB / International Investors)</option>
                <option value="site_visit">On-Site Briefing (Purbachal / Resort Projects)</option>
              </select>
            </div>

            <div class="form-group" style="margin-bottom: 20px;">
              <label class="form-label">Subject & Investment Scope</label>
              <textarea v-model="consultationForm.details" rows="3" class="form-textarea" placeholder="Briefly specify your requirement (e.g., 10-katha commercial plot, joint venture proposal, luxury duplex)..."></textarea>
            </div>

            <button type="submit" class="btn btn-emerald btn-lg w-full">
              <span>Submit Appointment Request</span>
            </button>
          </form>
        </div>
      </div>
    </transition>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useHead } from '#app'
import { useToast } from '~/composables/useToast'

useHead({
  title: 'Board of Directors & Executive Leadership | GBREL Bangladesh',
  meta: [
    {
      name: 'description',
      content: 'Meet the Managing Director and Board of Directors of Gram Bangla Real Estate Ltd. (GBREL). Visionary leadership committed to 0% legal dispute real estate, RAJUK compliance, and PropTech innovation.'
    }
  ]
})

const { add: addToast } = useToast()

// --------------------------------------------------------------------------
// Managing Director Data
// --------------------------------------------------------------------------
const managingDirector = {
  id: 'md-abdul-quder',
  name: 'MD Abdul Quder Talukder',
  designation: 'Managing Director & Founder',
  image: encodeURI('/img/MD-Abdul-Quder-Talukder -Managing -Director.png'),
  headline: 'Pioneering Legal Transparency, Ethical Land Acquisition & Generational Wealth Creation Across Bangladesh',
  departmentBadge: 'Executive Leadership',
  academic: 'Distinguished Real Estate Entrepreneur & Land Legal Strategist',
  experience: '22+ Years Leadership',
  email: 'md@gbrel.com',
  ext: '101',
  fullBio: 'MD Abdul Quder Talukder is the visionary Founder and Managing Director of Gram Bangla Real Estate Ltd. (GBREL). With more than two decades of distinguished experience in land masterplanning, commercial real estate acquisition, and urban development, Mr. Talukder has shaped GBREL into one of Bangladesh’s most reputable property enterprises. Known for his uncompromising stance on title verification, he instituted GBREL’s celebrated 7-tier legal audit protocol, ensuring that every customer investment is 100% immune from land disputes. Under his executive leadership, GBREL has successfully delivered over 100 prime land and residential developments across Dhaka, Purbachal, Chittagong, and Cox’s Bazar.',
  achievements: [
    'Spearheaded the acquisition and masterplanning of over 2,500+ acres of prime residential and commercial land banks.',
    'Formulated GBREL’s landmark 0% Land Dispute Guarantee, protecting over 10,000 domestic and expatriate families.',
    'Pioneered the integration of smart municipal infrastructure in Purbachal New Town sector zones.',
    'Recognized with multiple domestic awards for ethical property development and leadership in housing solutions.'
  ]
}

// --------------------------------------------------------------------------
// Board of Directors Data
// --------------------------------------------------------------------------
const directors = [
  {
    id: 'eng-siam-talukder',
    name: 'Engr. Siam Talukder',
    designation: 'Deputy Managing Director (DMD) & Director, Strategic Planning & Client Relations',
    departmentBadge: 'Executive Directorate / DMD',
    experience: '12+ Years Experience',
    image: encodeURI('/img/Eng Siam Talukder Director Marketing & IT.png'),
    headline: 'Architecting High-Yield Property Strategies, Masterplan Vision & VIP Client Relationship Advisory',
    shortBio: 'Key executive leader and strategic planner orchestrating GBREL’s project masterplans, VIP investor relations, and client advisory. Personally guides high-net-worth investors, NRBs, and corporate stakeholders through complex developments, explaining masterplans and structuring generational assets.',
    academic: 'Executive Leader & Strategic Masterplanner | B.Sc. in Engineering',
    email: 'siam.talukder@gbrel.com',
    ext: '102',
    keyOversight: [
      'Masterplan Strategic Planning & Project Architectural Briefings',
      'VIP & NRB Client Advisory, High-Value Acquisitions & Deal Structuring',
      'High-Stake Commercial Negotiations & Landowner Joint Ventures',
      'Cross-Border Investor Roadshows & Long-Term Corporate Growth'
    ],
    fullBio: 'Engr. Siam Talukder serves as Deputy Managing Director (DMD) & Director of Strategic Planning & Client Relations at GBREL. As one of the foundational leaders of the enterprise, Engr. Talukder orchestrates the strategic vision of GBREL, translating intricate real estate developments and urban engineering concepts into transparent, high-yield opportunities for clients. He directly commands client advisory operations, presenting project masterplans to domestic families and expatriates across the globe, ensuring every buyer gains complete clarity on land valuation, infrastructure growth, and legal security.',
    achievements: [
      'Personally advised and structured luxury land and apartment portfolios for over 1,500+ domestic and NRB clients.',
      'Spearheaded project masterplanning for GBREL’s flagship Purbachal township zones and Cox’s Bazar hospitality developments.',
      'Established strategic investment alliances with international trade delegations in Dubai, London, and North America.'
    ]
  },
  {
    id: 'eng-sher-ali-khan',
    name: 'Engr. Sher Ali Khan',
    designation: 'Co-Founder, Director & Chief Technology Officer (CTO) – PropTech & Digital Operations',
    departmentBadge: 'Co-Founder & Chief Technology Officer',
    experience: '12+ Years Experience',
    image: encodeURI('/img/Eng Sher Ali khan Director Marketing & IT .png'),
    headline: 'Pioneering Automated PropTech Platforms, GIS Spatial Intelligence & Modern Digital Real Estate Infrastructure',
    shortBio: 'Co-Founding Director and Chief Technology Officer leading GBREL’s technological empire, digital real estate innovations, and enterprise systems. Architect of Bangladesh’s premier PropTech discovery platform, integrating GIS spatial plot mapping, automated client acquisition, and global NRI investment solutions.',
    academic: 'Lead Software Architect & PropTech Visionary | B.Sc. in Computer Science & Engineering',
    email: 'cto@gbrel.com',
    ext: '103',
    keyOversight: [
      'Enterprise PropTech Architecture, Cloud Platforms & Scalable Systems',
      'Interactive GIS Spatial Plot Mapping & Boundary Coordinate Verification',
      'Automated Client Acquisition, Lead Intelligence & High-Velocity CRM',
      'NRB Digital Investment Escrow, Automated Verification & Cyber Security'
    ],
    fullBio: 'Engr. Sher Ali Khan is a Co-Founding Director and the Chief Technology Officer (CTO) of GBREL. A distinguished software architect and technology leader, Engr. Khan has pioneered the fusion of advanced computer engineering with high-yield real estate operations. He engineered GBREL’s bespoke PropTech infrastructure, automating buyer-matching algorithms, interactive GIS plot exploration, and remote escrow transparency for expatriate Bangladeshis across 25+ countries. His forward-thinking technical leadership guarantees that GBREL operates with modern digital precision, institutional data integrity, and lightning-fast customer responsiveness.',
    achievements: [
      'Architected and launched GBREL’s full-stack digital real estate ecosystem, reducing property discovery lead-times by 75%.',
      'Engineered the GIS interactive plot mapping suite, enabling instant visual verification of land elevation and boundary coordinates.',
      'Built the automated digital client acquisition engine, generating thousands of verified monthly investor inquiries worldwide.'
    ]
  },
  {
    id: 'md-abu-hanif',
    name: 'Md. Abu Hanif',
    designation: 'Director, Administration & Accounts',
    departmentBadge: 'Finance & Governance',
    experience: '18+ Years Experience',
    image: encodeURI('/img/Md-Abu-Hanif -Director Admin & Accounts.png'),
    headline: 'Upholding Institutional Audit Integrity, Fiduciary Compliance & Fiscal Governance',
    shortBio: 'Directs corporate administration, financial strategy, treasury operations, and legal escrow compliance. Ensures absolute fiscal transparency across all land acquisitions, investor payouts, and regulatory filings.',
    academic: 'Fellow Chartered Management & Corporate Governance Specialist',
    email: 'accounts@gbrel.com',
    ext: '104',
    keyOversight: [
      'Corporate Financial Planning & Treasury Operations',
      'Escrow Account Management & Client Fund Security',
      'Statutory Audit, Tax & RAJUK Financial Compliance',
      'Human Resources & Corporate Governance Policies'
    ],
    fullBio: 'Md. Abu Hanif serves as Director of Administration & Accounts at GBREL. With over 18 years of high-level financial and administrative leadership, Mr. Hanif guarantees that every commercial transaction, plot booking, and installment plan strictly adheres to Bangladesh accounting standards and corporate governance guidelines. His meticulous management of client escrow accounts ensures complete peace of mind for buyers during title deed handover and mutation.',
    achievements: [
      'Maintained a spotless statutory and tax audit record across GBREL’s multi-decade operational history.',
      'Constructed the transparent escrow framework safeguarding multi-million dollar client deposits during transfer.',
      'Streamlined vendor contracting and procurement, achieving high efficiency in project capitalization.'
    ]
  },
  {
    id: 'md-rubel-hawlader',
    name: 'Md. Rubel Hawlader',
    designation: 'Director, Project & Transport',
    departmentBadge: 'Civil Engineering & Logistics',
    experience: '16+ Years Experience',
    image: encodeURI('/img/Md RubelHawlader Director Project & Transport.png'),
    headline: 'Precision Civil Execution, Heavy Logistics & High-Velocity Infrastructure Development',
    shortBio: 'Leads on-ground civil construction, earthwork logistics, heavy equipment fleet management, and project site engineering. Ensures fast-track delivery and top-grade structural standards across all project sectors.',
    academic: 'Civil Engineering Project Leader & Heavy Logistics Director',
    email: 'projects@gbrel.com',
    ext: '105',
    keyOversight: [
      'Civil Construction, Earth Filling & Boundary Demarcation',
      'Heavy Machinery Logistics & Transport Fleet Operations',
      'Structural Engineering Quality Control & Soil Consolidation',
      'On-Site Worker Safety & Environmental Protection Standards'
    ],
    fullBio: 'Md. Rubel Hawlader is the Director of Project & Transport at GBREL. Overseeing the tangible, on-ground transformation of vast land tracts into modern residential sectors, Mr. Hawlader commands a substantial fleet of earthmoving and transport machinery. His hands-on engineering stewardship ensures that soil consolidation, boundary demarcation, road carpeting, and drainage networks are delivered with military precision and structural longevity.',
    achievements: [
      'Successfully executed on-ground development and boundary demarcation across 100+ project phases.',
      'Built GBREL’s dedicated in-house transport and heavy excavation fleet, dramatically lowering development lead times.',
      'Pioneered sustainable drainage and stormwater management systems in coastal and wetland adjacent project zones.'
    ]
  }
]

// --------------------------------------------------------------------------
// Modal State & Handlers
// --------------------------------------------------------------------------
const activeModalDirector = ref<any>(null)
const consultationModalOpen = ref(false)
const targetDirectorName = ref('')
const targetDirectorRole = ref('')
const consultationSent = ref(false)

const consultationForm = ref({
  name: '',
  phone: '',
  email: '',
  meetingType: 'in_person',
  details: ''
})

const openExecutiveModal = (director: any) => {
  activeModalDirector.value = director
}

const openConsultationModal = (name: string, role: string) => {
  targetDirectorName.value = name
  targetDirectorRole.value = role
  consultationSent.value = false
  consultationModalOpen.value = true
}

const scheduleFromModal = () => {
  if (activeModalDirector.value) {
    const d = activeModalDirector.value
    activeModalDirector.value = null
    openConsultationModal(d.name, d.designation)
  }
}

const submitConsultation = () => {
  consultationSent.value = true
  addToast({
    title: 'Consultation Request Sent',
    message: `Your briefing request with ${targetDirectorName.value} has been dispatched to the Executive Desk.`,
    type: 'success'
  })

  setTimeout(() => {
    consultationModalOpen.value = false
    consultationForm.value = {
      name: '',
      phone: '',
      email: '',
      meetingType: 'in_person',
      details: ''
    }
  }, 2500)
}
</script>

<style scoped>
/* ==========================================================================
   DIRECTORS PAGE STYLES - LUXURY REAL ESTATE PALETTE
   ========================================================================== */

.directors-page {
  background-color: #F8FAFC;
  color: var(--color-text-main);
  min-height: 100vh;
}

/* 1. Hero Section */
.leadership-hero {
  background: radial-gradient(circle at 80% 20%, rgba(212, 175, 55, 0.15) 0%, transparent 50%),
              radial-gradient(circle at 10% 80%, rgba(16, 185, 129, 0.12) 0%, transparent 50%),
              #0A1128;
  color: #FFFFFF;
  padding: 48px 0 72px;
  position: relative;
  overflow: hidden;
  border-bottom: 1px solid rgba(212, 175, 55, 0.2);
}

.breadcrumb-nav {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 0.85rem;
  margin-bottom: 28px;
  color: #94A3B8;
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

.breadcrumb-active {
  color: #D4AF37;
  font-weight: 600;
}

.hero-content {
  max-width: 920px;
}

.badge-leadership-tag {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  background: rgba(212, 175, 55, 0.14);
  border: 1px solid rgba(212, 175, 55, 0.35);
  color: #F2CD6E;
  font-size: 0.78rem;
  font-weight: 800;
  letter-spacing: 0.12em;
  padding: 6px 14px;
  border-radius: var(--radius-full);
  margin-bottom: 20px;
  text-transform: uppercase;
}

.pulse-dot {
  width: 8px;
  height: 8px;
  background: #10B981;
  border-radius: 50%;
  box-shadow: 0 0 10px #10B981;
  animation: pulse 2s infinite;
}

@keyframes pulse {
  0% { transform: scale(0.95); opacity: 0.8; }
  50% { transform: scale(1.3); opacity: 1; }
  100% { transform: scale(0.95); opacity: 0.8; }
}

.hero-title {
  font-family: var(--font-display);
  font-size: clamp(2.2rem, 4vw, 3.4rem);
  font-weight: 800;
  line-height: 1.15;
  margin-bottom: 18px;
  color: #FFFFFF;
}

.gold-gradient-text {
  background: linear-gradient(135deg, #F6E05E 0%, #D4AF37 50%, #F2CD6E 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}

.hero-subtitle {
  font-size: 1.12rem;
  line-height: 1.65;
  color: #CBD5E1;
  max-width: 780px;
  margin-bottom: 36px;
}

.leadership-stats-strip {
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  gap: 24px;
  background: rgba(255, 255, 255, 0.04);
  border: 1px solid rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(12px);
  padding: 20px 28px;
  border-radius: var(--radius-lg);
}

.stat-pill {
  flex: 1;
  min-width: 140px;
}

.stat-num {
  font-family: var(--font-display);
  font-size: 1.8rem;
  font-weight: 800;
  color: #F2CD6E;
  line-height: 1.1;
}

.stat-label {
  font-size: 0.8rem;
  color: #94A3B8;
  margin-top: 4px;
  font-weight: 500;
}

.stat-divider {
  width: 1px;
  height: 40px;
  background: rgba(255, 255, 255, 0.12);
}

/* 2. MD Spotlight Section */
.md-spotlight-section {
  padding: 64px 0;
  background: #F8FAFC;
}

.md-card {
  background: #FFFFFF;
  border-radius: var(--radius-2xl);
  border: 1px solid rgba(212, 175, 55, 0.3);
  box-shadow: 0 20px 45px -15px rgba(10, 17, 40, 0.1);
  overflow: hidden;
  padding: 44px;
  position: relative;
}

.md-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  width: 8px;
  height: 100%;
  background: linear-gradient(to bottom, #D4AF37, #10B981);
}

.md-grid {
  display: grid;
  grid-template-columns: 340px 1fr;
  gap: 48px;
  align-items: flex-start;
}

.md-image-col {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.md-image-frame {
  position: relative;
  border-radius: var(--radius-xl);
  overflow: hidden;
  border: 3px solid var(--color-gold);
  box-shadow: 0 16px 36px rgba(10, 17, 40, 0.14);
  background: radial-gradient(circle at 50% 35%, #182859 0%, #0A1128 100%);
  display: flex;
  align-items: center;
  justify-content: center;
}

.md-photo {
  width: 100%;
  height: 460px;
  object-fit: contain;
  object-position: center;
  display: block;
  padding: 8px;
  filter: drop-shadow(0 12px 24px rgba(0, 0, 0, 0.45));
  transition: transform var(--transition-normal);
}

.md-photo:hover {
  transform: scale(1.02);
}

.md-seal-badge {
  position: absolute;
  bottom: 16px;
  right: 16px;
  background: rgba(10, 17, 40, 0.9);
  backdrop-filter: blur(8px);
  border: 1px solid var(--color-gold);
  color: #F2CD6E;
  padding: 6px 12px;
  border-radius: var(--radius-sm);
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 0.75rem;
  font-weight: 800;
  letter-spacing: 0.08em;
}

.seal-icon {
  color: #D4AF37;
  font-size: 1rem;
}

.md-action-box {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.md-narrative-col {
  display: flex;
  flex-direction: column;
}

.md-header-tag {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 12px;
}

.role-badge {
  background: #ECFDF5;
  color: #065F46;
  font-size: 0.75rem;
  font-weight: 800;
  letter-spacing: 0.08em;
  padding: 4px 10px;
  border-radius: 4px;
  border: 1px solid #10B981;
}

.tenure-badge {
  font-size: 0.8rem;
  color: #64748B;
  font-weight: 600;
}

.md-name {
  font-family: var(--font-display);
  font-size: 2.3rem;
  font-weight: 800;
  color: #0A1128;
  line-height: 1.15;
  margin-bottom: 6px;
}

.md-designation {
  font-size: 1.15rem;
  color: #D4AF37;
  font-weight: 700;
  margin-bottom: 20px;
}

.md-headline-banner {
  background: #F1F5F9;
  border-left: 4px solid var(--color-gold);
  border-radius: 0 var(--radius-md) var(--radius-md) 0;
  padding: 18px 22px;
  margin-bottom: 22px;
  display: flex;
  gap: 14px;
  align-items: flex-start;
}

.quote-icon {
  color: var(--color-gold);
  flex-shrink: 0;
  margin-top: 2px;
}

.md-headline-text {
  font-size: 1.08rem;
  font-weight: 700;
  color: #0F172A;
  line-height: 1.5;
  font-style: italic;
}

.md-bio-body {
  font-size: 0.98rem;
  line-height: 1.75;
  color: #475569;
  display: flex;
  flex-direction: column;
  gap: 14px;
  margin-bottom: 28px;
}

.md-pillars-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 16px;
  margin-bottom: 30px;
}

.pillar-box {
  background: #F8FAFC;
  border: 1px solid var(--color-border);
  border-radius: var(--radius-md);
  padding: 16px;
}

.pillar-title {
  font-weight: 800;
  font-size: 0.9rem;
  color: #0A1128;
  margin-bottom: 4px;
}

.pillar-desc {
  font-size: 0.8rem;
  color: #64748B;
  line-height: 1.45;
}

.md-signature-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 16px;
  padding-top: 20px;
  border-top: 1px solid var(--color-border);
}

.signature-line {
  font-family: 'Playfair Display', Georgia, serif;
  font-size: 1.5rem;
  font-weight: 700;
  color: #0A1128;
  letter-spacing: -0.01em;
}

.signature-sub {
  font-size: 0.85rem;
  font-weight: 700;
  color: #059669;
}

.signature-co {
  font-size: 0.78rem;
  color: #64748B;
}

.compliance-seals {
  display: flex;
  align-items: center;
  gap: 8px;
}

/* 3. Board of Directors Section */
.board-members-section {
  padding: 72px 0 88px;
  background: #FFFFFF;
  border-top: 1px solid var(--color-border);
}

.section-header {
  max-width: 720px;
  margin: 0 auto 52px;
}

.section-tag {
  display: inline-block;
  font-size: 0.8rem;
  font-weight: 800;
  letter-spacing: 0.1em;
  text-transform: uppercase;
  color: #059669;
  background: #ECFDF5;
  padding: 4px 12px;
  border-radius: 4px;
  margin-bottom: 12px;
}

.section-title {
  font-family: var(--font-display);
  font-size: 2.3rem;
  font-weight: 800;
  color: #0A1128;
  line-height: 1.2;
  margin-bottom: 14px;
}

.section-desc {
  color: #64748B;
  font-size: 1.02rem;
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
  box-shadow: var(--shadow-md);
  display: flex;
  flex-direction: column;
  transition: transform var(--transition-normal), box-shadow var(--transition-normal), border-color var(--transition-normal);
}

.director-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 16px 36px rgba(10, 17, 40, 0.12);
  border-color: rgba(212, 175, 55, 0.4);
}

.director-image-wrapper {
  position: relative;
  height: 440px;
  background: radial-gradient(circle at 50% 35%, #182859 0%, #0A1128 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
  padding: 10px;
}

.director-card-img {
  width: 100%;
  height: 100%;
  object-fit: contain;
  object-position: center;
  display: block;
  filter: drop-shadow(0 12px 24px rgba(0, 0, 0, 0.45));
  transition: transform var(--transition-smooth);
}

.director-card:hover .director-card-img {
  transform: scale(1.03);
}

.director-tag-floating {
  position: absolute;
  top: 14px;
  left: 14px;
  background: rgba(10, 17, 40, 0.88);
  backdrop-filter: blur(8px);
  color: #F2CD6E;
  font-size: 0.72rem;
  font-weight: 800;
  letter-spacing: 0.08em;
  padding: 4px 10px;
  border-radius: 4px;
  border: 1px solid rgba(212, 175, 55, 0.4);
  z-index: 2;
}

.director-experience-badge {
  position: absolute;
  bottom: 14px;
  right: 14px;
  background: #059669;
  color: #FFFFFF;
  font-size: 0.75rem;
  font-weight: 700;
  padding: 4px 10px;
  border-radius: var(--radius-sm);
  box-shadow: 0 4px 10px rgba(5, 150, 105, 0.3);
  z-index: 2;
}

.director-card-body {
  padding: 28px;
  display: flex;
  flex-direction: column;
  flex: 1;
}

.director-role-tag {
  font-size: 0.8rem;
  font-weight: 800;
  color: #059669;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  margin-bottom: 6px;
}

.director-name {
  font-family: var(--font-display);
  font-size: 1.55rem;
  font-weight: 800;
  color: #0A1128;
  line-height: 1.2;
  margin-bottom: 12px;
}

.director-card-headline {
  font-size: 0.92rem;
  font-weight: 700;
  color: #1E293B;
  font-style: italic;
  line-height: 1.45;
  background: #F8FAFC;
  border-left: 3px solid #D4AF37;
  padding: 10px 14px;
  border-radius: 0 var(--radius-sm) var(--radius-sm) 0;
  margin-bottom: 14px;
}

.director-card-bio {
  font-size: 0.9rem;
  line-height: 1.6;
  color: #475569;
  margin-bottom: 18px;
  flex: 1;
}

.director-responsibilities {
  margin-bottom: 22px;
  background: #F8FAFC;
  border: 1px dashed var(--color-border);
  padding: 14px 16px;
  border-radius: var(--radius-md);
}

.resp-label {
  font-size: 0.72rem;
  font-weight: 800;
  color: #64748B;
  letter-spacing: 0.06em;
  margin-bottom: 8px;
}

.resp-list {
  list-style: none;
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.resp-list li {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 0.82rem;
  color: #334155;
  font-weight: 600;
}

.director-card-footer {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-top: auto;
  padding-top: 14px;
  border-top: 1px solid var(--color-border);
}

/* 4. Governance Pillars Section */
.governance-pillars-section {
  padding: 72px 0;
  background: #F8FAFC;
}

.pillars-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 24px;
}

.governance-card {
  background: #FFFFFF;
  border: 1px solid var(--color-border);
  border-radius: var(--radius-xl);
  padding: 28px;
  box-shadow: var(--shadow-sm);
  transition: transform var(--transition-normal), box-shadow var(--transition-normal);
}

.governance-card:hover {
  transform: translateY(-4px);
  box-shadow: var(--shadow-md);
}

.gov-icon {
  width: 48px;
  height: 48px;
  border-radius: var(--radius-md);
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 18px;
}

.gov-icon.gold { background: rgba(212, 175, 55, 0.15); color: #B7791F; }
.gov-icon.emerald { background: #ECFDF5; color: #059669; }
.gov-icon.blue { background: #EFF6FF; color: #2563EB; }
.gov-icon.red { background: #FFF1F2; color: #E11D48; }

.gov-title {
  font-size: 1.15rem;
  font-weight: 800;
  color: #0A1128;
  margin-bottom: 10px;
  line-height: 1.3;
}

.gov-text {
  font-size: 0.88rem;
  color: #64748B;
  line-height: 1.6;
}

/* 5. Executive CTA Banner */
.executive-cta-section {
  padding: 64px 0 88px;
  background: #FFFFFF;
}

.executive-cta-card {
  background: linear-gradient(135deg, #0A1128 0%, #162044 100%);
  border-radius: var(--radius-2xl);
  border: 1px solid rgba(212, 175, 55, 0.35);
  padding: 48px 56px;
  color: #FFFFFF;
  display: grid;
  grid-template-columns: 1.4fr 1fr;
  gap: 48px;
  align-items: center;
  box-shadow: 0 24px 60px -15px rgba(10, 17, 40, 0.3);
}

.cta-tag {
  color: #F2CD6E;
  font-size: 0.78rem;
  font-weight: 800;
  letter-spacing: 0.1em;
  display: block;
  margin-bottom: 12px;
}

.cta-title {
  font-family: var(--font-display);
  font-size: 2.1rem;
  font-weight: 800;
  line-height: 1.2;
  margin-bottom: 14px;
}

.cta-desc {
  font-size: 0.98rem;
  line-height: 1.65;
  color: #CBD5E1;
  margin-bottom: 24px;
}

.cta-highlights {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.cta-item {
  display: flex;
  align-items: center;
  gap: 10px;
  font-size: 0.9rem;
  color: #E2E8F0;
  font-weight: 600;
}

.hotline-notice {
  text-align: center;
  font-size: 0.78rem;
  color: #94A3B8;
  margin-top: 14px;
}

.hotline-notice strong {
  color: #10B981;
}

/* 6. Modals */
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(10, 17, 40, 0.78);
  backdrop-filter: blur(8px);
  z-index: 2000;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
}

.modal-card {
  background: #FFFFFF;
  border-radius: var(--radius-2xl);
  box-shadow: 0 25px 60px rgba(0, 0, 0, 0.3);
  width: 100%;
  max-height: 90vh;
  overflow-y: auto;
  position: relative;
  padding: 36px;
}

.modal-close-btn {
  position: absolute;
  top: 20px;
  right: 20px;
  width: 36px;
  height: 36px;
  border-radius: 50%;
  border: 1px solid var(--color-border);
  background: #F8FAFC;
  color: #64748B;
  font-size: 1.1rem;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all var(--transition-fast);
}

.modal-close-btn:hover {
  background: #EF4444;
  color: #FFFFFF;
  border-color: #EF4444;
}

.modal-director-header {
  display: flex;
  align-items: center;
  gap: 20px;
  padding-bottom: 20px;
  border-bottom: 1px solid var(--color-border);
  margin-bottom: 20px;
}

.modal-director-photo {
  width: 110px;
  height: 140px;
  border-radius: var(--radius-md);
  object-fit: contain;
  background: radial-gradient(circle at 50% 35%, #182859 0%, #0A1128 100%);
  border: 2px solid var(--color-gold);
  padding: 4px;
  filter: drop-shadow(0 6px 14px rgba(0, 0, 0, 0.3));
}

.modal-director-name {
  font-family: var(--font-display);
  font-size: 1.6rem;
  font-weight: 800;
  color: #0A1128;
  margin-top: 4px;
}

.modal-director-sub {
  font-size: 0.85rem;
  color: #64748B;
}

.modal-headline {
  background: #F8FAFC;
  border-left: 3px solid var(--color-gold);
  padding: 12px 16px;
  border-radius: 0 var(--radius-sm) var(--radius-sm) 0;
  font-weight: 700;
  font-style: italic;
  color: #0A1128;
  font-size: 0.95rem;
  margin-bottom: 20px;
}

.modal-section-title {
  font-size: 0.85rem;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  color: #059669;
  margin-bottom: 8px;
}

.modal-bio-text {
  font-size: 0.92rem;
  line-height: 1.7;
  color: #475569;
}

.modal-impact-list {
  list-style: none;
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.modal-impact-list li {
  display: flex;
  align-items: flex-start;
  gap: 10px;
  font-size: 0.88rem;
  color: #334155;
  line-height: 1.5;
}

.modal-contact-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 12px;
  margin-top: 10px;
}

.contact-pill {
  background: #F1F5F9;
  padding: 12px 14px;
  border-radius: var(--radius-sm);
  display: flex;
  flex-direction: column;
}

.contact-label {
  font-size: 0.72rem;
  color: #64748B;
  font-weight: 700;
  text-transform: uppercase;
}

.contact-val {
  font-size: 0.88rem;
  color: #0A1128;
  font-weight: 700;
  margin-top: 2px;
}

.modal-director-footer {
  display: flex;
  gap: 14px;
  margin-top: 28px;
  padding-top: 20px;
  border-top: 1px solid var(--color-border);
}

.modal-fade-enter-active,
.modal-fade-leave-active {
  transition: opacity 0.25s ease;
}

.modal-fade-enter-from,
.modal-fade-leave-to {
  opacity: 0;
}

/* ==========================================================================
   RESPONSIVE MEDIA QUERIES
   ========================================================================== */
@media (max-width: 1024px) {
  .md-grid {
    grid-template-columns: 280px 1fr;
    gap: 32px;
  }

  .directors-grid {
    grid-template-columns: 1fr;
  }

  .pillars-grid {
    grid-template-columns: repeat(2, 1fr);
  }

  .executive-cta-card {
    grid-template-columns: 1fr;
    padding: 36px;
    gap: 32px;
  }
}

@media (max-width: 768px) {
  .md-grid {
    grid-template-columns: 1fr;
    gap: 28px;
  }

  .md-photo {
    height: 320px;
  }

  .md-pillars-grid {
    grid-template-columns: 1fr;
  }

  .pillars-grid {
    grid-template-columns: 1fr;
  }

  .leadership-stats-strip {
    flex-direction: column;
    align-items: flex-start;
    gap: 16px;
  }

  .stat-divider {
    display: none;
  }

  .modal-contact-grid {
    grid-template-columns: 1fr;
  }
}
</style>

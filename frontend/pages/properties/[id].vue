<template>
  <div class="property-detail-page">
    <!-- Loading Skeleton State -->
    <div v-if="isLoadingProperty && !property" class="container text-center state-loading-box">
      <div class="luxury-loader">
        <span class="animate-spin inline-block loader-ring">◌</span>
      </div>
      <h2 class="loader-title">Loading Verified Property Mandate...</h2>
      <p class="loader-sub">Fetching live title deeds, specifications, and collateral from database.</p>
    </div>

    <!-- Error / Not Found State -->
    <div v-else-if="!property" class="container text-center state-error-box">
      <div class="error-emblem">🏛️</div>
      <h2 class="error-title">Property Mandate Not Found</h2>
      <p class="error-sub">
        This listing may have been settled, archived, or is strictly private under non-disclosure.
      </p>
      <NuxtLink to="/properties" class="btn btn-gold btn-lg">
        <span>Browse Active Verified Catalog →</span>
      </NuxtLink>
    </div>

    <!-- Live Property Page Content -->
    <div v-else class="container property-page-inner">
      <!-- Breadcrumb & Top Actions Bar -->
      <nav class="property-top-action-bar" aria-label="Breadcrumb and Actions">
        <div class="property-breadcrumb">
          <NuxtLink to="/" class="bc-link">Home</NuxtLink>
          <span class="bc-sep">/</span>
          <NuxtLink to="/properties" class="bc-link">Properties</NuxtLink>
          <span class="bc-sep">/</span>
          <span class="bc-current" :title="property.title">{{ property.title }}</span>
        </div>

        <div class="property-actions-cluster">
          <!-- Share Mandate -->
          <button 
            type="button"
            class="btn-action-glass"
            title="Share Property Mandate"
            @click="triggerShare"
          >
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
              <circle cx="18" cy="5" r="3"/>
              <circle cx="6" cy="12" r="3"/>
              <circle cx="18" cy="19" r="3"/>
              <line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/>
              <line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/>
            </svg>
            <span class="action-btn-text">Share</span>
          </button>

          <!-- Add to Compare -->
          <button 
            type="button"
            class="btn-action-glass" 
            :class="{ active: isInCompare(property.id) }"
            @click="toggleCompare(property.id)"
          >
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
              <polyline points="16 3 21 3 21 8"/>
              <line x1="4" y1="20" x2="21" y2="3"/>
              <polyline points="8 21 3 21 3 16"/>
              <line x1="20" y1="4" x2="3" y2="21"/>
            </svg>
            <span class="action-btn-text">{{ isInCompare(property.id) ? 'In Compare' : 'Compare' }}</span>
          </button>

          <!-- Save to Favorites / Wishlist -->
          <button 
            type="button"
            class="btn-action-glass"
            :class="{ active: isPropertySaved(property.id) }"
            @click="toggleSaveProperty(property.id)"
          >
            <svg width="15" height="15" viewBox="0 0 24 24" :fill="isPropertySaved(property.id) ? '#EF4444' : 'none'" :stroke="isPropertySaved(property.id) ? '#EF4444' : 'currentColor'" stroke-width="2.2">
              <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
            </svg>
            <span class="action-btn-text">{{ isPropertySaved(property.id) ? 'Saved' : 'Save' }}</span>
          </button>
        </div>
      </nav>

      <!-- Luxury Property Header (Title, Sub-Headline, Location, Price Card) -->
      <header class="property-header-hero">
        <div class="property-header-info">
          <!-- Verification Badges Row -->
          <div class="badges-row">
            <span v-if="property.isRajukApproved" class="luxury-badge badge-emerald">
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                <polyline points="20 6 9 17 4 12"/>
              </svg>
              <span>RAJUK Approved Plan</span>
            </span>

            <span v-if="property.propertyType === 'Land Share'" class="luxury-badge badge-purple">
              <span>🤝 Land Share Co-Ownership</span>
            </span>
            <span v-else class="luxury-badge badge-navy">
              <span>{{ property.propertyType }}</span>
            </span>

            <span class="luxury-badge badge-gold">
              <span>★ {{ property.listingType }}</span>
            </span>

            <span v-if="property.hasOpenHouse" class="luxury-badge badge-crimson">
              <span class="pulse-beacon"></span>
              <span>Open House Scheduled</span>
            </span>

            <span v-if="property.hidePrice" class="luxury-badge badge-amber">
              <span>🔐 Confidential Valuation (NDA)</span>
            </span>

            <a 
              v-if="property.brochureUrl || (property.brochuresVault && property.brochuresVault.length > 0)" 
              :href="property.brochureUrl || property.brochuresVault[0].file_url" 
              target="_blank" 
              class="luxury-badge badge-sky"
            >
              <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                <polyline points="14 2 14 8 20 8"/>
              </svg>
              <span>Official Brochure (PDF)</span>
            </a>

            <span class="mandate-ref-id">Ref #GBR-00{{ property.id }}</span>
          </div>

          <!-- Main Title -->
          <h1 class="property-title-text">
            {{ property.title }}
          </h1>

          <!-- Tagline Subtitle if present -->
          <p v-if="property.tagline" class="property-tagline-text">
            {{ property.tagline }}
          </p>

          <!-- Location Row with Quick Transit Anchor -->
          <div class="property-location-bar">
            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="2.2" class="location-pin-icon">
              <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
              <circle cx="12" cy="10" r="3"/>
            </svg>
            <span v-if="property.hideExactAddress" class="location-text">
              {{ property.areaName }}, {{ property.state }} (Prime Corridor • Confidential Enclave Address)
            </span>
            <span v-else class="location-text">
              {{ property.address }} ({{ property.areaName }}, {{ property.city }})
            </span>
          </div>
        </div>

        <!-- Luxury Financial Price Presentation Card -->
        <div class="property-price-card">
          <!-- Case A: Price is Hidden (Confidential Mandate) -->
          <div v-if="property.hidePrice">
            <div class="price-header-label confidential">
              <span>🔐 Confidential Mandate</span>
            </div>
            <div class="price-amount-gold">
              {{ property.priceDisplayText || 'Price on Application (POA)' }}
            </div>
            <p class="price-sub-note">
              Valuation & financial audits released exclusively upon verified NDA signature.
            </p>
            <button 
              type="button"
              class="btn btn-sm btn-gold btn-block" 
              style="margin-top: 12px; font-weight: 800;"
              @click="requestConfidentialPricing"
            >
              Request Price & NDA →
            </button>
          </div>

          <!-- Case B: Public Asking Price Display -->
          <div v-else>
            <div class="price-header-label">Official Asking Valuation</div>
            <div class="price-amount-display">
              {{ formatBDT(property.price) }}
            </div>
            
            <div v-if="property.priceUnit" class="price-rate-unit">
              {{ property.priceUnit }}
            </div>
            <div v-else-if="property.squareFootage" class="price-rate-unit">
              ৳ {{ Math.round(property.price / property.squareFootage).toLocaleString() }} / Sq. Ft.
            </div>

            <!-- Financial Trust Pill Guarantee -->
            <div class="price-guarantee-row">
              <span class="guarantee-chip">✓ Verified Freehold Title</span>
              <span class="guarantee-chip">✓ Escrow Bank Protected</span>
            </div>
          </div>
        </div>
      </header>

      <!-- HD Media Gallery with Interactive Lightbox trigger -->
      <section class="property-hero-gallery-wrap" aria-label="Property Media Gallery">
        <div class="property-hero-gallery">
          <!-- Main Left Image -->
          <div class="gallery-main-col" @click="openLightbox(0)">
            <img 
              :src="property.images[0]" 
              :alt="property.title" 
              class="gallery-main-img" 
              loading="eager"
            />
            <div class="gallery-badge-overlay">
              <span class="badge-trust-seal">🛡️ Inspected & Verified</span>
            </div>
            <div class="gallery-expand-hint">
              <span>Click to Expand HD Gallery</span>
            </div>
          </div>

          <!-- Right Sub-Images Grid -->
          <div class="gallery-sub-grid">
            <div class="sub-img-wrap" @click="openLightbox(1)">
              <img 
                v-if="property.images[1]" 
                :src="property.images[1]" 
                :alt="`${property.title} perspective`" 
                class="gallery-sub-img" 
                loading="lazy"
              />
            </div>
            
            <div class="sub-img-wrap" @click="openLightbox(2)">
              <img 
                v-if="property.images[2]" 
                :src="property.images[2]" 
                :alt="`${property.title} detail view`" 
                class="gallery-sub-img" 
                loading="lazy"
              />
              <div 
                v-if="property.images.length > 3" 
                class="gallery-more-overlay"
                @click.stop="openLightbox(0)"
              >
                <div class="more-content">
                  <span class="more-icon">📷</span>
                  <span class="more-text">+{{ property.images.length - 2 }} Photos</span>
                  <span class="more-sub">View Full Gallery</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Floating Gallery Pill Button (Mobile & Desktop) -->
        <button 
          type="button" 
          class="btn-floating-gallery-trigger"
          @click="openLightbox(0)"
        >
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
            <circle cx="8.5" cy="8.5" r="1.5"/>
            <polyline points="21 15 16 10 5 21"/>
          </svg>
          <span>View All {{ property.images.length }} Photos</span>
        </button>
      </section>

      <!-- Lightbox Modal (Full-Screen HD Presentation) -->
      <div v-if="lightboxOpen" ref="lightboxRoot" class="modal-overlay lightbox-overlay" @click.self="lightboxOpen = false">
        <div class="lightbox-stage animate-fade-in-up">
          <button @click="lightboxOpen = false" aria-label="Close photo gallery" class="lightbox-close-btn">✕</button>
          
          <div class="lightbox-media-container">
            <img 
              :src="property.images[currentLightboxIdx]" 
              class="lightbox-img" 
              :alt="`${property.title} photo ${currentLightboxIdx + 1}`" 
            />
          </div>

          <div class="lightbox-bottom-control-bar">
            <div class="lightbox-counter">
              <span>Photo <strong>{{ currentLightboxIdx + 1 }}</strong> of {{ property.images.length }}</span>
              <span class="lightbox-img-title">• {{ property.title }}</span>
            </div>

            <div class="lightbox-nav-buttons">
              <button class="btn btn-sm btn-outline-white" @click="prevPhoto" aria-label="Previous photo">‹ Previous</button>
              <button class="btn btn-sm btn-outline-white" @click="nextPhoto" aria-label="Next photo">Next ›</button>
            </div>
          </div>

          <!-- Thumbnail Strip -->
          <div class="lightbox-thumbs-strip">
            <button 
              v-for="(img, idx) in property.images" 
              :key="idx" 
              type="button"
              class="lightbox-thumb-btn"
              :class="{ active: currentLightboxIdx === idx }"
              @click="currentLightboxIdx = idx"
            >
              <img :src="img" :alt="`Thumbnail ${idx + 1}`" />
            </button>
          </div>
        </div>
      </div>

      <!-- Main Layout Grid: 6 Tabs on Left + Sticky Concierge Sidebar on Right -->
      <div class="property-detail-grid">
        <!-- Main Left Column: 6 Comprehensive Tabs -->
        <main class="property-main-content">
          <!-- 6-Tabbed Details Container -->
          <PropertyTabs :property="property" />

          <!-- Floor Plan Confidentiality Notice if hideFloorPlan is true -->
          <div 
            v-if="property.hideFloorPlan" 
            class="floorplan-restricted-card animate-fade-in"
          >
            <div class="flex items-center gap-3 mb-2">
              <span style="font-size: 1.6rem;">📐</span>
              <div>
                <h3 style="font-size: 1.15rem; font-weight: 800; color: #0F172A; margin: 0;">
                  Architectural Blueprints & Structural Layouts (Protected Mandate)
                </h3>
                <p style="color: #64748B; font-size: 0.88rem; margin-top: 4px; line-height: 1.5;">
                  Cadastral survey maps, structural calculations, and high-resolution CAD schematics are protected under copyright and provided upon verified NDA.
                </p>
              </div>
            </div>
            <button type="button" class="btn btn-sm btn-outline" style="margin-top: 10px;" @click="requestFloorPlan">
              Request Architectural Blueprints Under NDA →
            </button>
          </div>

          <!-- Institutional Trust & Due Diligence Guarantee Strip -->
          <div class="institutional-trust-strip">
            <div class="trust-pillar">
              <div class="trust-icon">🏛️</div>
              <div>
                <strong class="trust-name">Freehold Title Vetted</strong>
                <p class="trust-desc">CS, RS, SA, and BS Khatians examined by Supreme Court legal panel.</p>
              </div>
            </div>
            <div class="trust-pillar">
              <div class="trust-icon">🛡️</div>
              <div>
                <strong class="trust-name">Escrow Protected</strong>
                <p class="trust-desc">Transactions structured through scheduled commercial bank escrow.</p>
              </div>
            </div>
            <div class="trust-pillar">
              <div class="trust-icon">🤝</div>
              <div>
                <strong class="trust-name">Zero Hidden Markups</strong>
                <p class="trust-desc">Transparent valuation with direct owner/developer representation.</p>
              </div>
            </div>
            <div class="trust-pillar">
              <div class="trust-icon">📋</div>
              <div>
                <strong class="trust-name">Sub-Registry Support</strong>
                <p class="trust-desc">Complete legal deed registration and mutation assistance in Dhaka.</p>
              </div>
            </div>
          </div>
        </main>

        <!-- Right Column: Sticky Institutional Concierge Card -->
        <aside class="property-sidebar-col">
          <div class="sticky-concierge-card">
            <!-- Header Badge -->
            <div class="concierge-card-header">
              <div class="concierge-crest">🏛️</div>
              <div>
                <h3 class="concierge-title">Institutional Mandate Concierge</h3>
                <p class="concierge-sub">Direct access to verified deeds, legal counsel, and VIP advisory.</p>
              </div>
            </div>

            <!-- Primary CTAs Strip -->
            <div class="concierge-actions-strip">
              <!-- Official Brochure Download CTA -->
              <a 
                v-if="property.brochureUrl || (property.brochuresVault && property.brochuresVault.length > 0)" 
                :href="property.brochureUrl || property.brochuresVault[0].file_url" 
                target="_blank" 
                class="btn btn-gold btn-lg btn-block btn-brochure-glow"
              >
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                  <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                  <polyline points="14 2 14 8 20 8"/>
                  <line x1="12" y1="18" x2="12" y2="12"/>
                  <polyline points="9 15 12 18 15 15"/>
                </svg>
                <span>Download Official Brochure (PDF)</span>
              </a>
              <button 
                v-else 
                type="button"
                class="btn btn-outline btn-lg btn-block" 
                @click="requestBrochure"
              >
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                  <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                  <polyline points="14 2 14 8 20 8"/>
                </svg>
                <span>Request Project Brochure</span>
              </button>

              <!-- VIP Site Visit Button -->
              <button 
                type="button"
                class="btn btn-emerald btn-lg btn-block" 
                @click="scheduleModalOpen = true"
              >
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                  <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                  <line x1="16" y1="2" x2="16" y2="6"/>
                  <line x1="8" y1="2" x2="8" y2="6"/>
                  <line x1="3" y1="10" x2="21" y2="10"/>
                </svg>
                <span>Schedule VIP Site Viewing</span>
              </button>
            </div>

            <!-- Senior Advisory Officer Card -->
            <div class="advisor-profile-card">
              <div class="advisor-avatar-box">
                <div v-if="property.hideAgentPhoto" class="advisor-seal-avatar" title="GBREL Institutional Certified Advisor">
                  <span class="seal-icon">🏛️</span>
                  <span class="seal-text">GBREL</span>
                </div>
                <img 
                  v-else
                  :src="agent.photo" 
                  :alt="agent.name" 
                  class="advisor-real-photo"
                />
                <span class="advisor-active-beacon" title="Online for Consultation"></span>
              </div>

              <div class="advisor-info-box">
                <NuxtLink :to="`/agents/${agent.id}`" class="advisor-name-link">
                  <h4 class="advisor-name">{{ agent.name }}</h4>
                  <span class="advisor-verified-check" title="Verified Senior Partner">✔</span>
                </NuxtLink>
                <div class="advisor-rating-line">
                  <span class="star-gold">★</span> {{ agent.rating }} ({{ agent.reviewCount }} Reviews)
                </div>
                <div class="advisor-designation">
                  {{ property.hideAgentPhoto ? 'Institutional Mandate Advisor' : agent.title || agent.agency }}
                </div>
              </div>
            </div>

            <!-- Direct Contact Action Row (WhatsApp + Call) -->
            <div v-if="property.hideAgentContact" class="protected-hotline-note">
              <strong>Protected Mandate Hotline</strong>
              <p>Direct calls routed through GBREL Corporate Concierge to preserve owner privacy.</p>
            </div>
            <div v-else class="direct-contact-action-row">
              <a 
                :href="`https://wa.me/${agent.whatsapp.replace(/[^0-9]/g, '')}?text=Hello%20${encodeURIComponent(agent.name)},%20I%20am%20inquiring%20about:%20${encodeURIComponent(property.title)}`" 
                target="_blank" 
                class="btn-contact-whatsapp"
              >
                <span style="font-size: 1.15rem;">💬</span>
                <span>WhatsApp</span>
              </a>
              <a 
                :href="`tel:${agent.phone}`" 
                class="btn-contact-call"
              >
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                  <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
                </svg>
                <span>Call Agent</span>
              </a>
            </div>

            <!-- Qualified Investor Mandate Inquiry Form -->
            <div v-if="!inquirySubmitted" class="qualified-inquiry-box">
              <div class="inquiry-header-badge">
                <span class="badge-lock">🔒</span>
                <div>
                  <strong class="inquiry-title">Request Private Dossier & Pricing</strong>
                  <div class="inquiry-sub">Direct access to RAJUK title deeds and negotiation terms.</div>
                </div>
              </div>

              <form @submit.prevent="submitInquiry" class="inquiry-form-body">
                <!-- Prospect Name -->
                <div class="form-group mb-3">
                  <label class="form-label-sm">Full Name *</label>
                  <input v-model="inquiryForm.name" type="text" placeholder="e.g. Engr. Rafiqul Islam" required class="form-input" />
                </div>

                <!-- Phone / WhatsApp -->
                <div class="form-group mb-3">
                  <label class="form-label-sm">
                    <span>WhatsApp / Mobile Number *</span>
                    <span class="accent-text-green">(WhatsApp Preferred)</span>
                  </label>
                  <div class="input-with-icon">
                    <input v-model="inquiryForm.phone" type="tel" placeholder="+880 1711-XXXXXX" required class="form-input" />
                    <span class="input-trailing-icon">💬</span>
                  </div>
                </div>

                <!-- Email (Optional) -->
                <div class="form-group mb-3">
                  <label class="form-label-sm">
                    <span>Email Address</span>
                    <span class="hint-text">(For PDF dispatch)</span>
                  </label>
                  <input v-model="inquiryForm.email" type="email" placeholder="name@company.com" class="form-input" />
                </div>

                <!-- Capability Filter 1: Purchasing As -->
                <div class="form-group mb-3">
                  <label class="form-label-sm">I am purchasing as: *</label>
                  <select v-model="inquiryForm.buyer_category" class="form-input form-select" required>
                    <option value="Resident Business Owner / Industrialist">Resident Business Owner / Industrialist</option>
                    <option value="NRB Investor (USA / UK / Canada / Middle East)">NRB Investor (USA / UK / Canada / Middle East)</option>
                    <option value="Corporate / Institutional Fund">Corporate / Institutional Fund</option>
                    <option value="Private Family Residence Buyer">Private Family Residence Buyer</option>
                  </select>
                </div>

                <!-- Capability Filter 2: Investment Readiness & Timeline -->
                <div class="form-group mb-3">
                  <label class="form-label-sm">Purchase Readiness & Timeline: *</label>
                  <select v-model="inquiryForm.investment_readiness" class="form-input form-select" required>
                    <option value="Ready to close within 30 days (100% Cash / Self-Funded)">⚡ Ready within 30 days (100% Cash / Self-Funded)</option>
                    <option value="1 - 3 Months (Evaluating Title & Financing)">⏳ 1 - 3 Months (Evaluating Title & Financing)</option>
                    <option value="Exploring Market Pricing">🔍 Exploring Market Pricing</option>
                  </select>
                </div>

                <!-- Preferred Discussion Channel -->
                <div class="form-group mb-3">
                  <label class="form-label-sm">Preferred Discussion Channel:</label>
                  <div class="channel-pills-row">
                    <button 
                      type="button" 
                      class="channel-pill" 
                      :class="{ active: inquiryForm.preferred_contact === 'WhatsApp' }" 
                      @click="inquiryForm.preferred_contact = 'WhatsApp'"
                    >
                      💬 WhatsApp
                    </button>
                    <button 
                      type="button" 
                      class="channel-pill" 
                      :class="{ active: inquiryForm.preferred_contact === 'Phone' }" 
                      @click="inquiryForm.preferred_contact = 'Phone'"
                    >
                      📞 Phone Call
                    </button>
                    <button 
                      type="button" 
                      class="channel-pill" 
                      :class="{ active: inquiryForm.preferred_contact === 'Email' }" 
                      @click="inquiryForm.preferred_contact = 'Email'"
                    >
                      ✉️ Email
                    </button>
                  </div>
                </div>

                <!-- Specific Requirements Textarea -->
                <div class="form-group mb-4">
                  <label class="form-label-sm">Specific Inquiries or Offer Range:</label>
                  <textarea v-model="inquiryForm.message" rows="2" class="form-textarea" placeholder="Note your specific floor preferences, deed verification requests, or target closing timeline..."></textarea>
                </div>

                <!-- Submit CTA -->
                <button type="submit" class="btn btn-emerald btn-lg btn-block btn-submit-luxury" :disabled="isSubmittingInquiry">
                  <svg v-if="isSubmittingInquiry" class="animate-spin" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <circle cx="12" cy="12" r="10" stroke-opacity="0.25"/>
                    <path d="M12 2a10 10 0 0 1 10 10" stroke-linecap="round"/>
                  </svg>
                  <span>{{ isSubmittingInquiry ? 'Verifying & Dispatching...' : 'Request Private Dossier & Pricing →' }}</span>
                </button>

                <div class="inquiry-trust-note">
                  <span>🛡️ Routed directly to Senior Advisory Panel. Zero spam policy.</span>
                </div>
              </form>
            </div>

            <!-- Qualified Success Screen with Direct WhatsApp Continuation -->
            <div v-else class="text-center animate-fade-in qualified-success-box">
              <div class="success-icon-badge">✔</div>
              <strong class="success-title">Mandate Inquiry Verified!</strong>
              <p class="success-sub">
                Your dossier request has been assigned to <strong>{{ agent.name }}</strong> (Senior Luxury Advisory Partner).
              </p>

              <div class="success-action-wrap">
                <a 
                  :href="`https://wa.me/${agent.whatsapp.replace(/[^0-9]/g, '')}?text=Hello%20${encodeURIComponent(agent.name)},%20I%20just%20submitted%20a%20priority%20mandate%20inquiry%20for:%20${encodeURIComponent(property.title)}.%20My%20name%20is%20${encodeURIComponent(inquiryForm.name)}.`" 
                  target="_blank" 
                  class="btn btn-whatsapp-direct"
                >
                  <span style="font-size: 1.25rem;">💬</span>
                  <span>Continue Live on WhatsApp →</span>
                </a>
              </div>

              <button type="button" class="btn btn-sm btn-outline btn-block" @click="inquirySubmitted = false">
                <span>Submit Another Inquiry</span>
              </button>
            </div>
          </div>
        </aside>
      </div>
    </div>

    <!-- Sticky Mobile Bottom Conversion Bar (Essential for Facebook Ads Traffic) -->
    <aside v-if="property" class="mobile-sticky-lead-bar" aria-label="Mobile Contact Bar">
      <div class="sticky-inner-track">
        <div class="mobile-price-preview">
          <div class="mobile-price-val">{{ formatBDT(property.price) }}</div>
          <div class="mobile-price-type">{{ property.propertyType }}</div>
        </div>

        <a 
          :href="`https://wa.me/${agent.whatsapp.replace(/[^0-9]/g, '')}?text=Hello%20${encodeURIComponent(agent.name)},%20I%20saw%20your%20listing%20for:%20${encodeURIComponent(property.title)}%20and%20would%20like%20to%20review%20pricing%20and%20deeds.`" 
          target="_blank" 
          class="btn-whatsapp-sticky"
          aria-label="Chat on WhatsApp"
        >
          <span style="font-size: 1.2rem;">💬</span>
          <span>WhatsApp</span>
        </a>

        <button 
          type="button" 
          class="btn-gold-sticky"
          @click="quickDossierModalOpen = true"
        >
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
            <polyline points="14 2 14 8 20 8"/>
          </svg>
          <span>Price & Dossier</span>
        </button>
      </div>
    </aside>

    <!-- Quick Mobile Qualification Modal -->
    <div v-if="quickDossierModalOpen" class="modal-overlay" @click.self="quickDossierModalOpen = false">
      <div class="modal-card animate-fade-in-up mobile-dossier-card">
        <button 
          type="button" 
          class="modal-close-btn"
          @click="quickDossierModalOpen = false" 
          aria-label="Close modal"
        >
          ✕
        </button>

        <div class="modal-header-block">
          <span class="badge badge-rajuk" style="margin-bottom: 6px;">Verified Mandate</span>
          <h3 style="font-size: 1.25rem; font-weight: 800; color: #0A1128; line-height: 1.25;">
            Request Private Pricing & Dossier
          </h3>
          <p style="font-size: 0.85rem; color: #64748B; margin-top: 4px; line-height: 1.4;">
            {{ property.title }}
          </p>
        </div>

        <form @submit.prevent="submitModalInquiry">
          <div class="form-group mb-2">
            <label class="form-label-sm">Full Name *</label>
            <input v-model="inquiryForm.name" type="text" placeholder="Your Full Name" required class="form-input" />
          </div>

          <div class="form-group mb-2">
            <label class="form-label-sm">WhatsApp / Mobile Number *</label>
            <input v-model="inquiryForm.phone" type="tel" placeholder="+880 1711-XXXXXX" required class="form-input" />
          </div>

          <div class="form-group mb-2">
            <label class="form-label-sm">I am purchasing as: *</label>
            <select v-model="inquiryForm.buyer_category" class="form-input form-select" required>
              <option value="Resident Business Owner / Industrialist">Resident Business Owner / Industrialist</option>
              <option value="NRB Investor (USA / UK / Canada / Middle East)">NRB Investor (USA / UK / Canada / Middle East)</option>
              <option value="Corporate / Institutional Fund">Corporate / Institutional Fund</option>
              <option value="Private Family Residence Buyer">Private Family Residence Buyer</option>
            </select>
          </div>

          <div class="form-group mb-3">
            <label class="form-label-sm">Purchase Readiness: *</label>
            <select v-model="inquiryForm.investment_readiness" class="form-input form-select" required>
              <option value="Ready to close within 30 days (100% Cash / Self-Funded)">⚡ Ready within 30 days (100% Cash / Self-Funded)</option>
              <option value="1 - 3 Months (Evaluating Title & Financing)">⏳ 1 - 3 Months (Evaluating Title & Financing)</option>
              <option value="Exploring Market Pricing">🔍 Exploring Market Pricing</option>
            </select>
          </div>

          <button type="submit" class="btn btn-emerald btn-lg btn-block btn-submit-luxury" :disabled="isSubmittingInquiry">
            <span>{{ isSubmittingInquiry ? 'Dispatching...' : 'Get Private Dossier & Pricing →' }}</span>
          </button>
        </form>
      </div>
    </div>

    <!-- Share Mandate Modal -->
    <div v-if="shareModalOpen" class="modal-overlay" @click.self="shareModalOpen = false">
      <div class="modal-card animate-fade-in-up share-modal-card">
        <button 
          type="button" 
          class="modal-close-btn"
          @click="shareModalOpen = false" 
          aria-label="Close share dialog"
        >
          ✕
        </button>

        <h3 style="font-size: 1.25rem; font-weight: 800; color: #0A1128; margin-bottom: 6px;">
          Share Property Mandate
        </h3>
        <p style="font-size: 0.85rem; color: #64748B; margin-bottom: 20px;">
          Share this verified mandate with family, business partners, or your private investment committee.
        </p>

        <div class="share-options-grid">
          <!-- Copy Link -->
          <button type="button" class="share-option-btn" @click="copyMandateLink">
            <div class="share-icon-circle" style="background: rgba(10,17,40,0.06); color: #0A1128;">
              🔗
            </div>
            <span>Copy Direct Link</span>
          </button>

          <!-- WhatsApp Share -->
          <a 
            :href="`https://api.whatsapp.com/send?text=${encodeURIComponent('Review this luxury property on GBREL: ' + property.title + ' ' + (typeof window !== 'undefined' ? window.location.href : ''))}`" 
            target="_blank" 
            class="share-option-btn"
            style="text-decoration: none;"
          >
            <div class="share-icon-circle" style="background: #ECFDF5; color: #10B981;">
              💬
            </div>
            <span>Share to WhatsApp</span>
          </a>

          <!-- Email Share -->
          <a 
            :href="`mailto:?subject=${encodeURIComponent('GBREL Verified Mandate: ' + property.title)}&body=${encodeURIComponent('Take a look at this verified property mandate:\n\n' + property.title + '\nLocation: ' + property.areaName + ', ' + property.city + '\nPrice: ' + formatBDT(property.price) + '\n\nLink: ' + (typeof window !== 'undefined' ? window.location.href : ''))}`" 
            class="share-option-btn"
            style="text-decoration: none;"
          >
            <div class="share-icon-circle" style="background: #EFF6FF; color: #2563EB;">
              ✉️
            </div>
            <span>Email Mandate</span>
          </a>
        </div>
      </div>
    </div>

    <!-- Schedule Site Visit Modal -->
    <ScheduleModal 
      v-if="property"
      :is-open="scheduleModalOpen" 
      :property-id="property.id" 
      :property-title="property.title"
      @close="scheduleModalOpen = false" 
    />
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useProperties, type PropertyItem } from '~/composables/useProperties'
import { formatBDT } from '~/composables/useCurrency'
import { useAuth } from '~/composables/useAuth'
import { useCompare } from '~/composables/useCompare'
import PropertyTabs from '~/components/PropertyTabs.vue'
import ScheduleModal from '~/components/ScheduleModal.vue'
import { useOverlayBehavior } from '~/composables/useOverlayBehavior'
import { useApiUrl } from '~/composables/useApi'
import { useToast } from '~/composables/useToast'

const toast = useToast()
const route = useRoute()
const { getPropertyById, fetchPropertyById, getAgentById, fetchProperties } = useProperties()
const { isPropertySaved, toggleSaveProperty, user } = useAuth()
const { isInCompare, toggleCompare } = useCompare()

const isLoadingProperty = ref(true)
const dynamicProperty = ref<PropertyItem | null>(null)

// Fallback to local store or dynamic state
const property = computed<PropertyItem>(() => {
  return dynamicProperty.value || getPropertyById(route.params.id as string)
})

const agent = computed(() => {
  if (property.value?.agentId) {
    return getAgentById(property.value.agentId)
  }
  return getAgentById(1)
})

const scheduleModalOpen = ref(false)
const lightboxOpen = ref(false)
const quickDossierModalOpen = ref(false)
const shareModalOpen = ref(false)
const currentLightboxIdx = ref(0)
const inquirySubmitted = ref(false)
const lightboxRoot = ref<HTMLElement | null>(null)

const closeLightbox = () => {
  lightboxOpen.value = false
}

useOverlayBehavior(lightboxOpen, closeLightbox, lightboxRoot)

// High-Converting Qualified Buyer Form State
const inquiryForm = reactive({
  name: user.value?.name && user.value.name !== 'Guest User' && user.value.name !== 'Guest Buyer' ? user.value.name : '',
  phone: user.value?.phone || '',
  email: user.value?.email || '',
  buyer_category: 'Resident Business Owner / Industrialist',
  investment_readiness: 'Ready to close within 30 days (100% Cash / Self-Funded)',
  preferred_contact: 'WhatsApp',
  message: 'I am requesting verified RAJUK title deeds, floor plans, and pricing terms for this property mandate.'
})

const openLightbox = (idx: number) => {
  currentLightboxIdx.value = idx
  lightboxOpen.value = true
}

const prevPhoto = () => {
  if (!property.value) return
  if (currentLightboxIdx.value > 0) {
    currentLightboxIdx.value--
  } else {
    currentLightboxIdx.value = property.value.images.length - 1
  }
}

const nextPhoto = () => {
  if (!property.value) return
  if (currentLightboxIdx.value < property.value.images.length - 1) {
    currentLightboxIdx.value++
  } else {
    currentLightboxIdx.value = 0
  }
}

const isSubmittingInquiry = ref(false)

onMounted(async () => {
  isLoadingProperty.value = true
  try {
    const idOrSlug = route.params.id as string
    const loaded = await fetchPropertyById(idOrSlug)
    if (loaded) {
      dynamicProperty.value = loaded
    }
  } catch (err) {
    console.error('Failed to load property details:', err)
  } finally {
    isLoadingProperty.value = false
  }
  await fetchProperties()
})

// Unified Lead Dispatcher with UTM & Ad Tracking Auto-Capture
const submitInquiry = async () => {
  if (!property.value) return
  if (!inquiryForm.name || !inquiryForm.phone) {
    toast.error('Missing Contact Details', 'Please provide your name and WhatsApp number.')
    return
  }

  isSubmittingInquiry.value = true

  // Auto-capture Facebook and digital ad UTM parameters
  const utmSource = (route.query.utm_source as string) || 
    (process.client && document.referrer.toLowerCase().includes('facebook') ? 'facebook' : null)
  const utmMedium = (route.query.utm_medium as string) || (utmSource ? 'cpc_paid' : null)
  const utmCampaign = (route.query.utm_campaign as string) || null

  try {
    const res = await fetch(useApiUrl('/leads'), {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        property_id: property.value.id,
        name: inquiryForm.name,
        phone: inquiryForm.phone,
        email: inquiryForm.email,
        property_title: property.value.title,
        buyer_category: inquiryForm.buyer_category,
        investment_readiness: inquiryForm.investment_readiness,
        preferred_contact: inquiryForm.preferred_contact,
        message: inquiryForm.message,
        utm_source: utmSource,
        utm_medium: utmMedium,
        utm_campaign: utmCampaign
      })
    })

    const data = await res.json().catch(() => null)
    if (res.ok && data?.success) {
      inquirySubmitted.value = true
      toast.success('Inquiry Dispatched', `Directly assigned to ${agent.value.name}.`)
    } else {
      inquirySubmitted.value = true
    }
  } catch (err) {
    console.error('Inquiry dispatch error:', err)
    inquirySubmitted.value = true
  } finally {
    isSubmittingInquiry.value = false
  }
}

const submitModalInquiry = async () => {
  await submitInquiry()
  quickDossierModalOpen.value = false
}

const requestBrochure = () => {
  if (!property.value) return
  inquiryForm.message = `Hello, please dispatch the official architectural brochure, floor layout, and legal deeds for "${property.value.title}".`
  toast.info('Brochure Request', 'Please verify your contact details below to receive the private dossier.')
}

const requestConfidentialPricing = () => {
  if (!property.value) return
  inquiryForm.message = `Confidential NDA & Pricing Inquiry for "${property.value.title}". Please dispatch valuation breakdown and non-disclosure agreement.`
  toast.info('Confidential Mandate', 'Please complete the investor form to receive private valuation disclosures.')
}

const requestFloorPlan = () => {
  if (!property.value) return
  inquiryForm.message = `Request for Architectural Floor Plans & Blueprints for "${property.value.title}" under Non-Disclosure Agreement.`
  toast.info('Floor Plan Request', 'Submit request to receive confidential architectural layout.')
}

const triggerShare = async () => {
  if (process.client && navigator.share && property.value) {
    try {
      await navigator.share({
        title: property.value.title,
        text: `Review verified property mandate: ${property.value.title} on GBREL`,
        url: window.location.href
      })
      return
    } catch {
      // User dismissed native share, fallback to modal
    }
  }
  shareModalOpen.value = true
}

const copyMandateLink = async () => {
  if (process.client) {
    try {
      await navigator.clipboard.writeText(window.location.href)
      toast.success('Link Copied', 'Mandate URL copied to clipboard.')
      shareModalOpen.value = false
    } catch {
      toast.info('Mandate Link', window.location.href)
    }
  }
}
</script>

<style scoped>
/* Page Layout */
.property-detail-page {
  background: #F8FAFC;
  min-height: 100vh;
  padding: 32px 0 100px;
}

.property-page-inner {
  max-width: 1280px;
  margin: 0 auto;
  padding: 0 20px;
}

/* Skeleton & Error States */
.state-loading-box,
.state-error-box {
  padding: 100px 20px;
}

.luxury-loader {
  margin-bottom: 16px;
}

.loader-ring {
  font-size: 2.8rem;
  color: var(--color-gold);
}

.loader-title {
  font-size: 1.5rem;
  font-weight: 800;
  color: #0F172A;
  margin-bottom: 6px;
}

.loader-sub,
.error-sub {
  color: #64748B;
  font-size: 0.95rem;
  max-width: 480px;
  margin: 0 auto 24px;
  line-height: 1.5;
}

.error-emblem {
  font-size: 3.5rem;
  margin-bottom: 16px;
}

.error-title {
  font-size: 1.6rem;
  font-weight: 800;
  color: #0F172A;
  margin-bottom: 8px;
}

/* Breadcrumb & Action Bar */
.property-top-action-bar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 16px;
  margin-bottom: 24px;
}

.property-breadcrumb {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 0.88rem;
  color: #64748B;
  flex-wrap: wrap;
}

.bc-link {
  color: #64748B;
  text-decoration: none;
  transition: color 0.15s ease;
}

.bc-link:hover {
  color: #0F172A;
}

.bc-sep {
  color: #CBD5E1;
}

.bc-current {
  color: #0F172A;
  font-weight: 700;
  max-width: 420px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.property-actions-cluster {
  display: flex;
  align-items: center;
  gap: 10px;
}

.btn-action-glass {
  background: #FFFFFF;
  border: 1px solid #E2E8F0;
  border-radius: 8px;
  padding: 8px 14px;
  font-size: 0.85rem;
  font-weight: 700;
  color: #334155;
  display: inline-flex;
  align-items: center;
  gap: 6px;
  cursor: pointer;
  transition: all 0.2s ease;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
}

.btn-action-glass:hover {
  background: #F8FAFC;
  border-color: #CBD5E1;
  color: #0F172A;
  transform: translateY(-1px);
}

.btn-action-glass.active {
  background: #0A1128;
  color: #FFFFFF;
  border-color: #0A1128;
}

/* Property Header Hero */
.property-header-hero {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 24px;
  margin-bottom: 28px;
}

.property-header-info {
  flex: 1;
  min-width: 320px;
  max-width: 820px;
}

.badges-row {
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  gap: 8px;
  margin-bottom: 12px;
}

.luxury-badge {
  font-size: 0.76rem;
  font-weight: 800;
  padding: 5px 11px;
  border-radius: 6px;
  display: inline-flex;
  align-items: center;
  gap: 5px;
  letter-spacing: 0.02em;
  text-decoration: none;
}

.badge-emerald {
  background: #ECFDF5;
  color: #065F46;
  border: 1px solid #A7F3D0;
}

.badge-purple {
  background: #EDE9FE;
  color: #6D28D9;
  border: 1px solid #DDD6FE;
}

.badge-navy {
  background: #0A1128;
  color: #FFFFFF;
}

.badge-gold {
  background: rgba(212, 175, 55, 0.12);
  color: #92400E;
  border: 1px solid rgba(212, 175, 55, 0.3);
}

.badge-crimson {
  background: #FEF2F2;
  color: #991B1B;
  border: 1px solid #FECACA;
}

.badge-amber {
  background: #FEF3C7;
  color: #92400E;
  border: 1px solid #FDE68A;
}

.badge-sky {
  background: #E0F2FE;
  color: #0369A1;
  border: 1px solid #BAE6FD;
}

.mandate-ref-id {
  font-size: 0.75rem;
  color: #94A3B8;
  font-family: monospace;
  font-weight: 700;
  margin-left: 4px;
}

.pulse-beacon {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background: #DC2626;
  animation: pulse 1.8s infinite;
}

.property-title-text {
  font-size: clamp(1.65rem, 3.8vw, 2.4rem);
  font-weight: 850;
  color: #0A1128;
  line-height: 1.25;
  margin: 0 0 8px 0;
  letter-spacing: -0.02em;
}

.property-tagline-text {
  font-size: 1.05rem;
  color: #475569;
  line-height: 1.5;
  margin: 0 0 12px 0;
  font-weight: 500;
}

.property-location-bar {
  display: flex;
  align-items: center;
  gap: 6px;
  color: #64748B;
  font-size: 0.95rem;
}

.location-pin-icon {
  flex-shrink: 0;
}

/* Price Presentation Card */
.property-price-card {
  background: #FFFFFF;
  border: 1.5px solid #E2E8F0;
  border-radius: 16px;
  padding: 20px 24px;
  box-shadow: 0 4px 16px rgba(10, 17, 40, 0.05);
  min-width: 280px;
  text-align: right;
  border-top: 4px solid var(--color-gold);
}

.price-header-label {
  font-size: 0.76rem;
  color: #64748B;
  text-transform: uppercase;
  font-weight: 800;
  letter-spacing: 0.05em;
  margin-bottom: 4px;
}

.price-header-label.confidential {
  color: #B45309;
}

.price-amount-display {
  font-family: var(--font-ui);
  font-size: 2.15rem;
  font-weight: 850;
  color: #059669;
  line-height: 1.15;
  font-variant-numeric: tabular-nums;
  letter-spacing: -0.01em;
}

.price-amount-gold {
  font-family: var(--font-display);
  font-size: 1.5rem;
  font-weight: 800;
  color: var(--color-gold);
  line-height: 1.2;
}

.price-rate-unit {
  font-size: 0.85rem;
  color: #64748B;
  margin-top: 4px;
  font-weight: 600;
}

.price-sub-note {
  font-size: 0.78rem;
  color: #64748B;
  margin-top: 6px;
  line-height: 1.4;
}

.price-guarantee-row {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  gap: 8px;
  margin-top: 10px;
  flex-wrap: wrap;
}

.guarantee-chip {
  font-size: 0.72rem;
  color: #059669;
  font-weight: 700;
  background: #F0FDF4;
  padding: 2px 6px;
  border-radius: 4px;
}

/* HD Media Gallery */
.property-hero-gallery-wrap {
  position: relative;
  margin-bottom: 36px;
}

.property-hero-gallery {
  display: grid;
  grid-template-columns: 2fr 1fr;
  gap: 12px;
  height: 480px;
  border-radius: 20px;
  overflow: hidden;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
}

.gallery-main-col {
  position: relative;
  height: 100%;
  cursor: pointer;
  overflow: hidden;
}

.gallery-main-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.4s ease;
}

.gallery-main-col:hover .gallery-main-img {
  transform: scale(1.02);
}

.gallery-badge-overlay {
  position: absolute;
  top: 16px;
  left: 16px;
}

.badge-trust-seal {
  background: rgba(10, 17, 40, 0.8);
  backdrop-filter: blur(8px);
  -webkit-backdrop-filter: blur(8px);
  color: #FFFFFF;
  font-size: 0.78rem;
  font-weight: 800;
  padding: 6px 12px;
  border-radius: 20px;
  border: 1px solid rgba(255, 255, 255, 0.15);
}

.gallery-expand-hint {
  position: absolute;
  bottom: 16px;
  left: 16px;
  background: rgba(10, 17, 40, 0.7);
  backdrop-filter: blur(8px);
  color: #FFFFFF;
  font-size: 0.78rem;
  font-weight: 700;
  padding: 5px 12px;
  border-radius: 20px;
  opacity: 0;
  transition: opacity 0.25s ease;
}

.gallery-main-col:hover .gallery-expand-hint {
  opacity: 1;
}

.gallery-sub-grid {
  display: grid;
  grid-template-rows: 1fr 1fr;
  gap: 12px;
  height: 100%;
}

.sub-img-wrap {
  position: relative;
  height: 100%;
  cursor: pointer;
  overflow: hidden;
}

.gallery-sub-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.4s ease;
}

.sub-img-wrap:hover .gallery-sub-img {
  transform: scale(1.03);
}

.gallery-more-overlay {
  position: absolute;
  inset: 0;
  background: rgba(10, 17, 40, 0.75);
  backdrop-filter: blur(4px);
  -webkit-backdrop-filter: blur(4px);
  color: #FFFFFF;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: background 0.2s ease;
}

.gallery-more-overlay:hover {
  background: rgba(10, 17, 40, 0.85);
}

.more-content {
  text-align: center;
}

.more-icon {
  font-size: 1.5rem;
  display: block;
  margin-bottom: 4px;
}

.more-text {
  font-size: 1.15rem;
  font-weight: 850;
  display: block;
}

.more-sub {
  font-size: 0.78rem;
  color: #CBD5E1;
  text-transform: uppercase;
  font-weight: 700;
  letter-spacing: 0.04em;
}

.btn-floating-gallery-trigger {
  position: absolute;
  bottom: 20px;
  right: 20px;
  background: rgba(10, 17, 40, 0.85);
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
  color: #FFFFFF;
  border: 1px solid rgba(255, 255, 255, 0.25);
  border-radius: 30px;
  padding: 10px 18px;
  font-size: 0.88rem;
  font-weight: 700;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  cursor: pointer;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.3);
  transition: all 0.2s ease;
}

.btn-floating-gallery-trigger:hover {
  background: #0A1128;
  transform: translateY(-2px);
  border-color: var(--color-gold);
}

/* Lightbox Modal */
.lightbox-stage {
  max-width: 1040px;
  width: 100%;
  position: relative;
}

.lightbox-close-btn {
  position: absolute;
  top: -48px;
  right: 0;
  width: 44px;
  height: 44px;
  background: none;
  border: none;
  color: #FFFFFF;
  font-size: 1.8rem;
  cursor: pointer;
  transition: transform 0.2s;
}

.lightbox-close-btn:hover {
  transform: scale(1.15);
}

.lightbox-media-container {
  display: flex;
  align-items: center;
  justify-content: center;
  max-height: 72vh;
}

.lightbox-img {
  max-width: 100%;
  max-height: 72vh;
  object-fit: contain;
  border-radius: 12px;
  box-shadow: 0 20px 50px rgba(0, 0, 0, 0.5);
}

.lightbox-bottom-control-bar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 16px;
  color: #FFFFFF;
  flex-wrap: wrap;
  gap: 12px;
}

.lightbox-counter {
  font-size: 0.95rem;
}

.lightbox-img-title {
  color: #94A3B8;
  margin-left: 6px;
  font-size: 0.88rem;
}

.lightbox-nav-buttons {
  display: flex;
  gap: 10px;
}

.lightbox-thumbs-strip {
  display: flex;
  gap: 8px;
  overflow-x: auto;
  margin-top: 14px;
  padding-bottom: 6px;
}

.lightbox-thumb-btn {
  width: 64px;
  height: 48px;
  border-radius: 6px;
  overflow: hidden;
  border: 2px solid transparent;
  cursor: pointer;
  padding: 0;
  background: none;
  flex-shrink: 0;
  opacity: 0.6;
  transition: all 0.2s;
}

.lightbox-thumb-btn.active,
.lightbox-thumb-btn:hover {
  opacity: 1;
  border-color: var(--color-gold);
}

.lightbox-thumb-btn img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

/* Main Grid Layout */
.property-detail-grid {
  display: grid;
  grid-template-columns: minmax(0, 1fr) 380px;
  gap: 36px;
  align-items: start;
}

.property-main-content {
  min-width: 0;
}

.floorplan-restricted-card {
  margin-top: 28px;
  background: #FFFFFF;
  border: 1.5px solid #E2E8F0;
  border-radius: 16px;
  padding: 24px;
  border-left: 5px solid var(--color-gold);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
}

/* Institutional Trust Strip */
.institutional-trust-strip {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 16px;
  background: #FFFFFF;
  border: 1.5px solid #E2E8F0;
  border-radius: 16px;
  padding: 24px;
  margin-top: 32px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
}

.trust-pillar {
  display: flex;
  align-items: flex-start;
  gap: 12px;
}

.trust-icon {
  font-size: 1.6rem;
  flex-shrink: 0;
}

.trust-name {
  display: block;
  font-size: 0.95rem;
  font-weight: 800;
  color: #0F172A;
  margin-bottom: 2px;
}

.trust-desc {
  font-size: 0.82rem;
  color: #64748B;
  line-height: 1.45;
  margin: 0;
}

/* Sticky Concierge Sidebar */
.property-sidebar-col {
  position: relative;
}

.sticky-concierge-card {
  background: #FFFFFF;
  border: 1.5px solid #E2E8F0;
  border-radius: 20px;
  padding: 24px;
  box-shadow: 0 10px 30px rgba(10, 17, 40, 0.06);
  position: sticky;
  top: 96px;
  border-top: 4px solid var(--color-gold);
}

.concierge-card-header {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 20px;
  padding-bottom: 16px;
  border-bottom: 1px solid #F1F5F9;
}

.concierge-crest {
  width: 44px;
  height: 44px;
  border-radius: 10px;
  background: linear-gradient(135deg, #0A1128 0%, #1E293B 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.35rem;
  color: var(--color-gold);
  flex-shrink: 0;
  border: 1px solid rgba(212, 175, 55, 0.3);
}

.concierge-title {
  font-size: 1.05rem;
  font-weight: 800;
  color: #0A1128;
  margin: 0;
  line-height: 1.25;
}

.concierge-sub {
  font-size: 0.78rem;
  color: #64748B;
  margin: 2px 0 0 0;
  line-height: 1.35;
}

.concierge-actions-strip {
  display: flex;
  flex-direction: column;
  gap: 10px;
  margin-bottom: 20px;
}

.btn-brochure-glow {
  box-shadow: 0 4px 16px rgba(212, 175, 55, 0.3);
}

/* Advisor Profile */
.advisor-profile-card {
  display: flex;
  align-items: center;
  gap: 14px;
  padding: 14px 16px;
  background: #F8FAFC;
  border: 1px solid #E2E8F0;
  border-radius: 12px;
  margin-bottom: 16px;
}

.advisor-avatar-box {
  position: relative;
  flex-shrink: 0;
}

.advisor-seal-avatar {
  width: 54px;
  height: 54px;
  border-radius: 50%;
  background: #0A1128;
  border: 2px solid var(--color-gold);
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
}

.seal-icon {
  font-size: 1.1rem;
}

.seal-text {
  font-size: 0.55rem;
  color: var(--color-gold);
  font-weight: 800;
}

.advisor-real-photo {
  width: 54px;
  height: 54px;
  border-radius: 50%;
  object-fit: cover;
  border: 2px solid var(--color-gold);
}

.advisor-active-beacon {
  position: absolute;
  bottom: 2px;
  right: 2px;
  width: 12px;
  height: 12px;
  background: #10B981;
  border: 2px solid #FFFFFF;
  border-radius: 50%;
}

.advisor-info-box {
  flex: 1;
  min-width: 0;
}

.advisor-name-link {
  display: flex;
  align-items: center;
  gap: 4px;
  text-decoration: none;
}

.advisor-name {
  font-size: 1.02rem;
  font-weight: 800;
  color: #0F172A;
  margin: 0;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.advisor-verified-check {
  color: #059669;
  font-size: 0.8rem;
  font-weight: 800;
}

.advisor-rating-line {
  font-size: 0.8rem;
  color: #059669;
  font-weight: 700;
  margin-top: 2px;
}

.star-gold {
  color: #F59E0B;
}

.advisor-designation {
  font-size: 0.78rem;
  color: #64748B;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.protected-hotline-note {
  background: #F8FAFC;
  padding: 10px 14px;
  border-radius: 8px;
  border: 1px dashed #CBD5E1;
  text-align: center;
  font-size: 0.78rem;
  color: #64748B;
  margin-bottom: 16px;
}

.direct-contact-action-row {
  display: flex;
  gap: 8px;
  margin-bottom: 20px;
}

.btn-contact-whatsapp {
  flex: 1;
  background: #25D366;
  color: #FFFFFF;
  font-weight: 750;
  font-size: 0.88rem;
  border-radius: 8px;
  padding: 8px 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  text-decoration: none;
  box-shadow: 0 3px 10px rgba(37, 211, 102, 0.25);
  transition: all 0.2s ease;
}

.btn-contact-whatsapp:hover {
  background: #20BD5A;
  transform: translateY(-1px);
}

.btn-contact-call {
  flex: 1;
  background: #FFFFFF;
  color: #0F172A;
  border: 1.5px solid #E2E8F0;
  font-weight: 750;
  font-size: 0.88rem;
  border-radius: 8px;
  padding: 8px 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  text-decoration: none;
  transition: all 0.2s ease;
}

.btn-contact-call:hover {
  background: #F8FAFC;
  border-color: #CBD5E1;
  transform: translateY(-1px);
}

/* Qualified Form Box */
.qualified-inquiry-box {
  background: #FFFFFF;
}

.inquiry-header-badge {
  display: flex;
  align-items: center;
  gap: 10px;
  background: #F8FAFC;
  border: 1px solid #E2E8F0;
  padding: 10px 14px;
  border-radius: 10px;
  margin-bottom: 16px;
}

.badge-lock {
  font-size: 1.25rem;
}

.inquiry-title {
  font-size: 0.95rem;
  color: #0A1128;
  display: block;
  font-weight: 800;
  line-height: 1.2;
}

.inquiry-sub {
  font-size: 0.74rem;
  color: #64748B;
  margin-top: 2px;
}

.form-label-sm {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 0.78rem;
  font-weight: 700;
  color: #334155;
  margin-bottom: 5px;
}

.accent-text-green {
  color: #059669;
  font-weight: 700;
  font-size: 0.72rem;
}

.hint-text {
  color: #94A3B8;
  font-size: 0.72rem;
}

.input-with-icon {
  position: relative;
}

.input-trailing-icon {
  position: absolute;
  right: 10px;
  top: 50%;
  transform: translateY(-50%);
  font-size: 1rem;
  pointer-events: none;
}

.form-select {
  cursor: pointer;
  background-color: #FFFFFF;
}

.channel-pills-row {
  display: flex;
  gap: 6px;
}

.channel-pill {
  flex: 1;
  background: #F8FAFC;
  border: 1px solid #CBD5E1;
  border-radius: 6px;
  padding: 6px 4px;
  font-size: 0.75rem;
  font-weight: 700;
  color: #475569;
  cursor: pointer;
  transition: all 0.2s ease;
  text-align: center;
}

.channel-pill:hover {
  background: #F1F5F9;
  border-color: #94A3B8;
}

.channel-pill.active {
  background: #0A1128;
  color: #FFFFFF;
  border-color: #0A1128;
}

.btn-submit-luxury {
  font-weight: 850;
  padding: 12px 18px;
  box-shadow: 0 4px 14px rgba(16, 185, 129, 0.35);
}

.inquiry-trust-note {
  margin-top: 10px;
  text-align: center;
  font-size: 0.72rem;
  color: #64748B;
  line-height: 1.35;
}

.qualified-success-box {
  padding: 24px 18px;
  background: #ECFDF5;
  border: 1.5px solid #A7F3D0;
  border-radius: 14px;
}

.success-icon-badge {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  background: #10B981;
  color: #FFFFFF;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  margin: 0 auto 12px;
}

.success-title {
  font-size: 1.2rem;
  color: #0F172A;
  display: block;
}

.success-sub {
  font-size: 0.88rem;
  margin-top: 6px;
  color: #475569;
  line-height: 1.45;
}

.success-action-wrap {
  margin: 18px 0;
}

.btn-whatsapp-direct {
  width: 100%;
  background: #25D366;
  border: none;
  color: #FFFFFF;
  font-weight: 800;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 12px 16px;
  border-radius: 8px;
  text-decoration: none;
  box-shadow: 0 4px 14px rgba(37, 211, 102, 0.35);
}

/* Mobile Sticky Bottom Conversion Bar */
.mobile-sticky-lead-bar {
  display: none;
}

@media (max-width: 1024px) {
  .mobile-sticky-lead-bar {
    display: block;
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    background: rgba(10, 17, 40, 0.94);
    backdrop-filter: blur(16px);
    -webkit-backdrop-filter: blur(16px);
    border-top: 1px solid rgba(255, 255, 255, 0.14);
    padding: 10px 14px;
    padding-bottom: max(10px, env(safe-area-inset-bottom));
    z-index: 999;
    box-shadow: 0 -8px 24px rgba(0, 0, 0, 0.4);
  }

  .sticky-inner-track {
    display: flex;
    align-items: center;
    gap: 10px;
    max-width: 600px;
    margin: 0 auto;
  }

  .mobile-price-preview {
    flex: 1;
    min-width: 0;
  }

  .mobile-price-val {
    font-size: 1.1rem;
    font-weight: 850;
    color: #D4AF37;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    line-height: 1.2;
  }

  .mobile-price-type {
    font-size: 0.72rem;
    color: #94A3B8;
    text-transform: uppercase;
    font-weight: 700;
  }

  .btn-whatsapp-sticky {
    background: #25D366;
    color: #FFFFFF;
    border: none;
    border-radius: 8px;
    padding: 10px 14px;
    font-size: 0.88rem;
    font-weight: 800;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    text-decoration: none;
    box-shadow: 0 4px 12px rgba(37, 211, 102, 0.3);
    flex-shrink: 0;
  }

  .btn-gold-sticky {
    background: linear-gradient(135deg, #D4AF37 0%, #B89628 100%);
    color: #0A1128;
    border: none;
    border-radius: 8px;
    padding: 10px 14px;
    font-size: 0.88rem;
    font-weight: 850;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    cursor: pointer;
    box-shadow: 0 4px 12px rgba(212, 175, 55, 0.3);
    flex-shrink: 0;
  }
}

/* Modals */
.mobile-dossier-card {
  max-width: 480px;
  width: 100%;
  padding: 24px;
  position: relative;
}

.modal-header-block {
  margin-bottom: 16px;
}

.share-modal-card {
  max-width: 440px;
  width: 100%;
  padding: 28px;
  position: relative;
}

.share-options-grid {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.share-option-btn {
  display: flex;
  align-items: center;
  gap: 14px;
  padding: 12px 16px;
  background: #F8FAFC;
  border: 1.5px solid #E2E8F0;
  border-radius: 12px;
  font-size: 0.95rem;
  font-weight: 750;
  color: #0F172A;
  cursor: pointer;
  transition: all 0.2s ease;
  width: 100%;
  text-align: left;
}

.share-option-btn:hover {
  background: #FFFFFF;
  border-color: #CBD5E1;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
}

.share-icon-circle {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.2rem;
  flex-shrink: 0;
}

/* Responsive Breakpoints */
@media (max-width: 1024px) {
  .property-detail-grid {
    grid-template-columns: 1fr;
    gap: 32px;
  }

  .sticky-concierge-card {
    position: static;
    margin-top: 20px;
  }
}

@media (max-width: 768px) {
  .property-detail-page {
    padding: 20px 0 90px;
  }

  .property-hero-gallery {
    grid-template-columns: 1fr;
    height: 300px;
  }

  .gallery-sub-grid {
    display: none;
  }

  .property-price-card {
    width: 100%;
    text-align: left;
    min-width: 0;
    padding: 16px 20px;
  }

  .price-guarantee-row {
    justify-content: flex-start;
  }

  .institutional-trust-strip {
    grid-template-columns: 1fr;
    padding: 18px;
    gap: 14px;
  }

  .btn-floating-gallery-trigger {
    bottom: 12px;
    right: 12px;
    padding: 8px 14px;
    font-size: 0.8rem;
  }

  .property-top-action-bar {
    gap: 12px;
  }

  .property-actions-cluster {
    width: 100%;
    justify-content: space-between;
  }

  .btn-action-glass {
    flex: 1;
    justify-content: center;
  }
}

@media (max-width: 480px) {
  .property-hero-gallery {
    height: 240px;
    border-radius: 14px;
  }

  .sticky-concierge-card {
    padding: 18px;
    border-radius: 16px;
  }

  .direct-contact-action-row {
    flex-direction: column;
  }

  .channel-pills-row {
    flex-direction: column;
  }
}
</style>

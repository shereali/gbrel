<template>
  <footer class="app-footer">
    <div class="container">
      <div class="footer-grid">
        <!-- Col 1: Brand & Bio -->
        <div>
          <div class="flex items-center gap-3" style="margin-bottom: 16px;">
            <div class="brand-icon" style="width: 36px; height: 36px; font-size: 1.1rem;">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
              </svg>
            </div>
            <span class="brand-title" style="font-size: 1.3rem;">GBREL<span style="color: #D4AF37;">.</span>COM</span>
          </div>
          <p style="color: #CBD5E1; font-size: 0.9rem; line-height: 1.6; margin-bottom: 20px;">
            Bangladesh's premier client-hunter real estate marketplace. Connecting discerning buyers and expatriates with verified luxury residences, high-yield plots, and commercial resort assets.
          </p>
          <div class="flex items-center gap-3">
            <span class="badge badge-rajuk">RAJUK Verified</span>
            <span class="badge badge-featured">CDA Approved</span>
            <span class="badge badge-status">0% Dispute Guaranteed</span>
          </div>
        </div>

        <!-- Col 2: Prime Locations -->
        <div>
          <h4 class="footer-col-title">Prime Locations</h4>
          <ul class="footer-links-list">
            <li><NuxtLink to="/properties?area=Gulshan-2" class="footer-link">Gulshan-1 & 2 Diplomatic</NuxtLink></li>
            <li><NuxtLink to="/properties?area=Banani" class="footer-link">Banani & Road 11 Penthouses</NuxtLink></li>
            <li><NuxtLink to="/properties?area=Purbachal New Town" class="footer-link">Purbachal Smart City Plots</NuxtLink></li>
            <li><NuxtLink to="/properties?area=Bashundhara R/A" class="footer-link">Bashundhara R/A Block A-P</NuxtLink></li>
            <li><NuxtLink to="/properties?area=Dhanmondi" class="footer-link">Dhanmondi Lakefront Duplex</NuxtLink></li>
            <li><NuxtLink to="/properties?area=Marine Drive" class="footer-link">Cox’s Bazar Marine Drive Suites</NuxtLink></li>
          </ul>
        </div>

        <!-- Col 3: Property Types -->
        <div>
          <h4 class="footer-col-title">Categories</h4>
          <ul class="footer-links-list">
            <li><NuxtLink to="/properties?type=Flat" class="footer-link">Luxury Flats & Duplexes</NuxtLink></li>
            <li><NuxtLink to="/properties?type=Plot" class="footer-link">Residential & Corner Plots</NuxtLink></li>
            <li><NuxtLink to="/properties?type=Hotel" class="footer-link">Beach Resorts & Hotel Suites</NuxtLink></li>
            <li><NuxtLink to="/properties?type=Land" class="footer-link">Industrial & Agro Lands</NuxtLink></li>
            <li><NuxtLink to="/properties?type=Commercial" class="footer-link">Grade-A Corporate Offices</NuxtLink></li>
            <li><NuxtLink to="/list-property" class="footer-link" style="color: #D4AF37; font-weight: 700;">Sell Your Property</NuxtLink></li>
          </ul>
        </div>

        <!-- Col 4: Corporate Offices & Hotline -->
        <div>
          <h4 class="footer-col-title">Corporate Headquarters</h4>
          <p style="color: #CBD5E1; font-size: 0.88rem; margin-bottom: 12px;">
            <strong>Dhaka HQ:</strong> Level 14, GBREL Tower, Kemal Ataturk Avenue, Gulshan-2, Dhaka-1212.
          </p>
          <p style="color: #CBD5E1; font-size: 0.88rem; margin-bottom: 16px;">
            <strong>Chittagong Office:</strong> Agrabad Commercial Area, Chittagong.
          </p>
          <div style="background: rgba(255, 255, 255, 0.05); padding: 14px; border-radius: var(--radius-md); border: 1px solid rgba(255, 255, 255, 0.1);">
            <div style="font-size: 0.78rem; color: #CBD5E1; text-transform: uppercase; letter-spacing: 0.05em;">24/7 Client Advisory Hotline</div>
            <div style="font-family: var(--font-ui); font-size: 1.2rem; font-weight: 800; color: #10B981; margin-top: 4px; font-variant-numeric: tabular-nums;">
              +880 1819-987654 / 09612-GBREL
            </div>
          </div>
        </div>
      </div>

      <!-- Bottom bar -->
      <div class="footer-bottom-bar">
        <div>
          © {{ new Date().getFullYear() }} GBREL (Gram Bangla Real Estate Ltd.). All rights reserved. Real Estate Regulatory Authority (RAJUK/CDA) Compliant.
        </div>
        <div class="flex items-center gap-4">
          <NuxtLink to="/directors" class="footer-link" style="color: #D4AF37; font-weight: 700;">Board of Directors</NuxtLink>
          <span>•</span>
          <NuxtLink to="#" class="footer-link">Privacy Policy</NuxtLink>
          <span>•</span>
          <NuxtLink to="#" class="footer-link">Terms of Escrow</NuxtLink>
          <span>•</span>
          <NuxtLink to="#" class="footer-link">Legal Verification Process</NuxtLink>
        </div>
      </div>
    </div>
  </footer>
</template>

<template>
  <div class="signup-page animate-fade-in">
    <div class="container" style="padding: 60px 24px; max-width: 1100px;">
      <div class="signup-grid">
        <!-- Left Column: Value Proposition for Property Owners -->
        <div class="signup-pitch">
          <div class="brand-tag">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
              <path d="M12 2l8 4v6c0 5-3.5 8.5-8 10-4.5-1.5-8-5-8-10V6l8-4z"/>
              <path d="M9 12l2 2 4-4"/>
            </svg>
            <span>GBREL VERIFIED SELLER NETWORK</span>
          </div>

          <h1 class="pitch-title">
            List Your Land, Plot or Flat with Bangladesh's Premier Brokerage
          </h1>

          <p class="pitch-desc">
            Connect directly with over 50,000+ verified High-Net-Worth Individuals (HNWI) and Non-Resident Bangladeshi (NRB) investors across the UK, USA, Canada, and the Middle East.
          </p>

          <div class="benefits-list">
            <div class="benefit-item">
              <div class="benefit-icon">✔</div>
              <div>
                <strong>Zero Upfront Listing Fee</strong>
                <p>No listing charges. We only earn a standard 2% advisory commission once your transaction is closed.</p>
              </div>
            </div>

            <div class="benefit-item">
              <div class="benefit-icon">✔</div>
              <div>
                <strong>Supreme Court Legal Title Verification</strong>
                <p>We review your CS, RS, BS Khatians, Mutation receipts, and RAJUK approvals to grant the trusted badge.</p>
              </div>
            </div>

            <div class="benefit-item">
              <div class="benefit-icon">✔</div>
              <div>
                <strong>Guaranteed Bank Escrow Settlement</strong>
                <p>100% financial security via partner banks (BRAC Bank, DBH Finance, IDLC, City Bank).</p>
              </div>
            </div>

            <div class="benefit-item">
              <div class="benefit-icon">✔</div>
              <div>
                <strong>Chauffeur Guided VIP Viewings</strong>
                <p>We manage buyer site inspections with our senior regional advisors and dedicated vehicles.</p>
              </div>
            </div>
          </div>

          <!-- Trust Badges -->
          <div class="trust-pill-row">
            <span class="trust-pill">RAJUK & CDA Compliant</span>
            <span class="trust-pill">100% Mutation Verified</span>
            <span class="trust-pill">৳ 650+ Cr Transacted</span>
          </div>
        </div>

        <!-- Right Column: Registration Form -->
        <div class="signup-card">
          <div class="card-header">
            <h2 style="font-size: 1.5rem; font-weight: 800; color: #0F172A;">Create Seller Account</h2>
            <p style="font-size: 0.88rem; color: #64748B; margin-top: 4px;">
              Register to list your land, plot, flat, or commercial asset
            </p>
          </div>

          <form @submit.prevent="handleSignup">
            <!-- Account Category Selection -->
            <div class="form-group" style="margin-bottom: 18px;">
              <label class="form-label">I am registering as a:</label>
              <div class="seller-type-selector">
                <label 
                  class="type-option" 
                  :class="{ active: form.sellerType === 'Landowner' }"
                >
                  <input type="radio" v-model="form.sellerType" value="Landowner" style="display:none;" />
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true" style="margin: 0 auto 4px;">
                    <path d="M12 21s-6-5.2-6-10a6 6 0 0 1 12 0c0 4.8-6 10-6 10z"/>
                    <circle cx="12" cy="11" r="2"/>
                  </svg>
                  <span>Land / Plot Owner</span>
                </label>

                <label 
                  class="type-option" 
                  :class="{ active: form.sellerType === 'Apartment Owner' }"
                >
                  <input type="radio" v-model="form.sellerType" value="Apartment Owner" style="display:none;" />
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true" style="margin: 0 auto 4px;">
                    <path d="M3 21h18M5 21V7l7-4 7 4v14M9 21v-6h6v6"/>
                  </svg>
                  <span>Flat / Duplex Owner</span>
                </label>

                <label 
                  class="type-option" 
                  :class="{ active: form.sellerType === 'Developer' }"
                >
                  <input type="radio" v-model="form.sellerType" value="Developer" style="display:none;" />
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true" style="margin: 0 auto 4px;">
                    <path d="M2 20h20M4 20V8l8-4 8 4v12M9 20v-4h6v4"/>
                  </svg>
                  <span>Real Estate Builder</span>
                </label>
              </div>
            </div>

            <!-- Full Name -->
            <div class="form-group" style="margin-bottom: 14px;">
              <label class="form-label">Full Name (As per NID / Passport)</label>
              <input 
                v-model="form.name" 
                type="text" 
                required 
                placeholder="e.g. Kazi Rashedul Islam" 
                class="form-input" 
              />
            </div>

            <!-- Phone & WhatsApp -->
            <div class="grid grid-2" style="gap: 12px; margin-bottom: 14px;">
              <div class="form-group">
                <label class="form-label">Mobile Number</label>
                <input 
                  v-model="form.phone" 
                  type="tel" 
                  required 
                  placeholder="+880 1711-XXXXXX" 
                  class="form-input" 
                />
              </div>

              <div class="form-group">
                <label class="form-label">WhatsApp Number</label>
                <input 
                  v-model="form.whatsapp" 
                  type="tel" 
                  placeholder="+880 1819-XXXXXX" 
                  class="form-input" 
                />
              </div>
            </div>

            <!-- Email Address -->
            <div class="form-group" style="margin-bottom: 14px;">
              <label class="form-label">Email Address</label>
              <input 
                v-model="form.email" 
                type="email" 
                required 
                placeholder="name@example.com" 
                class="form-input" 
              />
            </div>

            <!-- Password -->
            <div class="form-group" style="margin-bottom: 18px;">
              <label class="form-label">Create Password</label>
              <input 
                v-model="form.password" 
                type="password" 
                required 
                placeholder="Minimum 8 characters" 
                class="form-input" 
              />
            </div>

            <!-- Location / City -->
            <div class="form-group" style="margin-bottom: 22px;">
              <label class="form-label">Primary Property Division</label>
              <select v-model="form.division" class="form-select">
                <option value="Dhaka North">Dhaka North (Gulshan, Banani, Purbachal, Uttara)</option>
                <option value="Dhaka South">Dhaka South (Dhanmondi, Jalshiri, Keraniganj)</option>
                <option value="Chittagong">Chittagong & Cox's Bazar</option>
                <option value="Sylhet">Sylhet & Sreemangal</option>
              </select>
            </div>

            <button type="submit" class="btn btn-gold btn-lg" style="width: 100%; margin-bottom: 16px;">
              <span>Register & Proceed to Submit Property →</span>
            </button>

            <div style="text-align: center; font-size: 0.85rem; color: #64748B;">
              Already registered? 
              <NuxtLink to="/login" style="color: #059669; font-weight: 700; text-decoration: none;">
                Sign In to Your Account
              </NuxtLink>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { reactive } from 'vue'
import { useRouter } from 'vue-router'
import { useAuth } from '~/composables/useAuth'

definePageMeta({
  layout: 'default'
})

const router = useRouter()
const { login } = useAuth()

const form = reactive({
  sellerType: 'Landowner',
  name: '',
  phone: '',
  whatsapp: '',
  email: '',
  password: '',
  division: 'Dhaka North'
})

const handleSignup = async () => {
  // Trigger auth login state
  await login(form.email || 'seller@gbrel.com', form.password)
  // Direct forward to listing submission page
  router.push('/list-property?registered=true')
}
</script>

<style scoped>
.signup-page {
  background: #F8FAFC;
  min-height: calc(100vh - 80px);
}

.signup-grid {
  display: grid;
  grid-template-columns: 1.1fr 1fr;
  gap: 48px;
  align-items: center;
}

.brand-tag {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  background: rgba(212, 175, 55, 0.12);
  border: 1px solid rgba(212, 175, 55, 0.3);
  color: #B45309;
  padding: 6px 14px;
  border-radius: var(--radius-full);
  font-size: 0.78rem;
  font-weight: 800;
  letter-spacing: 0.06em;
  margin-bottom: 16px;
}

.pitch-title {
  font-family: var(--font-display);
  font-size: var(--text-headline);
  font-weight: 800;
  color: #0A1128;
  line-height: 1.2;
  margin-bottom: 16px;
  text-wrap: balance;
}

.pitch-desc {
  font-size: 1.05rem;
  color: #475569;
  line-height: 1.6;
  margin-bottom: 28px;
}

.benefits-list {
  display: flex;
  flex-direction: column;
  gap: 18px;
  margin-bottom: 32px;
}

.benefit-item {
  display: flex;
  gap: 14px;
  align-items: flex-start;
}

.benefit-icon {
  width: 28px;
  height: 28px;
  border-radius: 50%;
  background: #10B981;
  color: #FFFFFF;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.85rem;
  font-weight: 900;
  flex-shrink: 0;
  margin-top: 2px;
}

.benefit-item strong {
  color: #0F172A;
  font-size: 0.98rem;
  display: block;
}

.benefit-item p {
  color: #64748B;
  font-size: 0.88rem;
  line-height: 1.4;
  margin-top: 2px;
}

.trust-pill-row {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
}

.trust-pill {
  background: #FFFFFF;
  border: 1px solid var(--color-border);
  padding: 6px 12px;
  border-radius: var(--radius-md);
  font-size: 0.82rem;
  font-weight: 700;
  color: #334155;
}

.signup-card {
  background: #FFFFFF;
  border: 1px solid var(--color-border);
  border-radius: var(--radius-2xl);
  padding: 36px;
  box-shadow: var(--shadow-xl);
}

.card-header {
  margin-bottom: 24px;
  padding-bottom: 16px;
  border-bottom: 1px solid var(--color-border);
}

.seller-type-selector {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 8px;
}

.type-option {
  padding: 10px 12px;
  border: 1px solid var(--color-border);
  border-radius: var(--radius-md);
  cursor: pointer;
  text-align: center;
  font-size: 0.82rem;
  font-weight: 700;
  color: #475569;
  transition: all var(--transition-fast);
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 2px;
}

.type-option:hover {
  border-color: #059669;
}

.type-option.active {
  background: #ECFDF5;
  border-color: #10B981;
  color: #065F46;
}

@media (max-width: 992px) {
  .signup-grid {
    grid-template-columns: 1fr;
    gap: 32px;
  }
}

@media (max-width: 400px) {
  .seller-type-selector {
    grid-template-columns: 1fr;
  }
  .signup-card {
    padding: 28px 20px;
  }
}
</style>

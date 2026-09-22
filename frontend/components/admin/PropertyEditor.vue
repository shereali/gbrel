<template>
  <div class="property-editor-container animate-fade-in">
    <!-- STICKY TOPBAR & ACTIONS -->
    <div class="property-editor-topbar">
      <div class="flex items-center gap-3">
        <button type="button" class="btn btn-sm btn-outline-white" @click="handleGoBack">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <line x1="19" y1="12" x2="5" y2="12"/>
            <polyline points="12 19 5 12 12 5"/>
          </svg>
          <span>Inventory</span>
        </button>

        <div>
          <div style="font-size:0.75rem; color:var(--admin-text-muted); display:flex; align-items:center; gap:6px;">
            <span>Properties</span>
            <span>›</span>
            <span>{{ isEditing ? `Edit Listing #${propId}` : 'Create New Listing' }}</span>
          </div>
          <h2 style="font-size:1.1rem; font-weight:800; color:var(--admin-text-primary); margin:0; line-height:1.2;">
            {{ propForm.title ? propForm.title : (isEditing ? 'Edit Property' : 'New Property Listing') }}
          </h2>
        </div>
      </div>

      <div class="flex items-center gap-3 flex-wrap">
        <!-- Status Indicator Pill -->
        <div class="badge" :style="statusBadgeStyle">
          <span class="badge-dot" :class="{ pulse: propForm.status === 'Active' }"></span>
          <span>Status: <strong>{{ propForm.status }}</strong></span>
        </div>

        <button type="button" class="btn btn-sm btn-outline-white" :disabled="isSaving" @click="handleGoBack">
          Cancel
        </button>

        <button 
          type="button" 
          class="btn btn-sm btn-outline-white" 
          :disabled="isSaving" 
          @click="saveProperty('Draft')"
          title="Save as a private draft without publishing live"
        >
          <span>💾 Save Draft</span>
        </button>

        <button 
          type="button" 
          class="btn btn-sm btn-emerald" 
          :disabled="isSaving" 
          @click="saveProperty('Active')"
          style="display:inline-flex; align-items:center; gap:6px;"
          title="Publish listing live to the public portal"
        >
          <span v-if="isSaving" class="animate-spin">◌</span>
          <span>{{ isSaving ? 'Saving...' : (isEditing ? 'Save & Publish Live' : '🚀 Publish Property') }}</span>
        </button>
      </div>
    </div>

    <!-- MAIN TWO-COLUMN FORM LAYOUT -->
    <div class="property-editor-grid">
      <!-- LEFT COLUMN: FORM SECTION CARDS -->
      <div class="property-editor-main-col">

        <!-- SECTION 1: BASIC DETAILS & CATEGORY -->
        <div id="sec-basic" class="property-editor-card">
          <div class="property-card-header">
            <div class="property-card-title">
              <span class="property-card-icon">1</span>
              <span>Basic Identity & Category</span>
            </div>
            <span v-if="propForm.title && propForm.propertyType" style="color:#10B981; font-size:0.8rem; font-weight:700;">✔ Completed</span>
          </div>

          <div class="form-group" style="margin-bottom:14px;">
            <label class="form-label" style="display:flex; justify-content:space-between;">
              <span>Property Title</span>
              <span style="font-size:0.75rem; color:var(--admin-text-muted);">{{ propForm.title.length }}/120 characters</span>
            </label>
            <input 
              v-model="propForm.title" 
              type="text" 
              maxlength="120"
              placeholder="e.g. 10 Katha Corner Plot at Purbachal Sector 17 or 3,500 Sqft Lakefront Penthouse" 
              class="form-input" 
              style="font-size:0.95rem; font-weight:600;"
            />
          </div>

          <div class="grid grid-3" style="gap:14px;">
            <!-- Category (Dynamic) -->
            <div class="form-group">
              <label class="form-label" style="display:flex; justify-content:space-between; align-items:center;">
                <span>Category</span>
                <button type="button" class="inline-quick-add-btn" @click="promptAddOption('categories', 'Category')">+ Add</button>
              </label>
              <select v-model="propForm.propertyType" class="form-select">
                <option v-for="cat in categories" :key="cat" :value="cat">{{ cat }}</option>
              </select>
            </div>

            <!-- Transaction Type (Dynamic) -->
            <div class="form-group">
              <label class="form-label" style="display:flex; justify-content:space-between; align-items:center;">
                <span>Transaction Type</span>
                <button type="button" class="inline-quick-add-btn" @click="promptAddOption('transaction_types', 'Transaction Type')">+ Add</button>
              </label>
              <select v-model="propForm.listingType" class="form-select">
                <option v-for="t in transactionTypes" :key="t" :value="t">{{ t }}</option>
              </select>
            </div>

            <!-- Property Lifecycle Status (Dynamic) -->
            <div class="form-group">
              <label class="form-label" style="display:flex; justify-content:space-between; align-items:center;">
                <span>Lifecycle Status</span>
                <button type="button" class="inline-quick-add-btn" @click="promptAddOption('statuses', 'Status')">+ Add</button>
              </label>
              <select v-model="propForm.status" class="form-select">
                <option v-for="s in statuses" :key="s" :value="s">
                  {{ s }} {{ s === 'Draft' ? '(Private Draft)' : (s === 'Active' ? '(Live on Portal)' : '') }}
                </option>
              </select>
            </div>
          </div>
        </div>

        <!-- SECTION 2: LOCATION & REGIONAL MAPPING -->
        <div id="sec-location" class="property-editor-card">
          <div class="property-card-header">
            <div class="property-card-title">
              <span class="property-card-icon">2</span>
              <span>Location & Regional Mapping</span>
            </div>
            <span v-if="propForm.state && propForm.areaName" style="color:#10B981; font-size:0.8rem; font-weight:700;">✔ Completed</span>
          </div>

          <div class="grid grid-2" style="gap:14px; margin-bottom:14px;">
            <!-- Division / Region (Dynamic) -->
            <div class="form-group">
              <label class="form-label" style="display:flex; justify-content:space-between; align-items:center;">
                <span>Division / Region</span>
                <button type="button" class="inline-quick-add-btn" @click="promptAddOption('divisions', 'Division / Region')">+ Add</button>
              </label>
              <select v-model="propForm.state" class="form-select">
                <option v-for="div in divisions" :key="div" :value="div">{{ div }}</option>
              </select>
            </div>

            <div class="form-group">
              <label class="form-label">Neighborhood / Area Name</label>
              <input v-model="propForm.areaName" type="text" placeholder="e.g. Gulshan-2, Banani DOHS, Purbachal Sector 17" class="form-input" />
            </div>
          </div>

          <div class="form-group">
            <label class="form-label">Full Street Address & Landmark</label>
            <input v-model="propForm.address" type="text" placeholder="e.g. Road 44, Block C, Gulshan-2 (Near Lake Park)" class="form-input" />
          </div>
        </div>

        <!-- SECTION 3: PRICING & VALUATION -->
        <div id="sec-pricing" class="property-editor-card">
          <div class="property-card-header">
            <div class="property-card-title">
              <span class="property-card-icon">3</span>
              <span>Pricing & Valuation</span>
            </div>
            <span v-if="propForm.hidePrice || propForm.price > 0" style="color:#10B981; font-size:0.8rem; font-weight:700;">✔ Completed</span>
          </div>

          <div class="grid grid-2" style="gap:16px; align-items:flex-start;">
            <div class="form-group">
              <label class="form-label">Asking Price (BDT ৳)</label>
              <input 
                v-model.number="propForm.price" 
                type="number" 
                step="50000" 
                min="0"
                class="form-input" 
                style="font-family:var(--font-ui); font-size:1.15rem; font-weight:800; color:#10B981;"
              />
              <div style="font-size:0.82rem; color:var(--color-gold); margin-top:6px; font-weight:600;">
                Live Preview: {{ formatBDT(propForm.price) }}
              </div>
            </div>

            <div style="background:rgba(255,255,255,0.02); padding:14px; border-radius:var(--radius-md); border:1px solid var(--admin-border-subtle);">
              <label class="flex items-center gap-2" style="cursor:pointer; margin-bottom:8px;">
                <input v-model="propForm.hidePrice" type="checkbox" style="width:16px; height:16px; accent-color:var(--color-gold);" />
                <span style="font-weight:700; font-size:0.88rem; color:var(--admin-text-primary);">Hide Exact Price (Price on Application)</span>
              </label>
              <p style="font-size:0.75rem; color:var(--admin-text-muted); margin:0 0 8px 0; line-height:1.4;">
                Replaces numerical price with a confidential badge on public pages to safeguard seller discretion.
              </p>
              <input 
                v-if="propForm.hidePrice" 
                v-model="propForm.priceDisplayText" 
                type="text" 
                placeholder="Display text (e.g. Price on Application)" 
                class="form-input" 
                style="font-size:0.82rem; padding:6px 10px;"
              />
            </div>
          </div>
        </div>

        <!-- SECTION 4: ARCHITECTURAL DIMENSIONS & SPECS -->
        <div id="sec-specs" class="property-editor-card">
          <div class="property-card-header">
            <div class="property-card-title">
              <span class="property-card-icon">4</span>
              <span>Dimensions & Specifications</span>
            </div>
            <span v-if="propForm.squareFootage > 0 || propForm.landSize > 0" style="color:#10B981; font-size:0.8rem; font-weight:700;">✔ Completed</span>
          </div>

          <div class="grid grid-4" style="gap:14px; margin-bottom:14px;">
            <div class="form-group">
              <label class="form-label">Square Footage (Sq. Ft.)</label>
              <input v-model.number="propForm.squareFootage" type="number" placeholder="2400" class="form-input" />
            </div>

            <div class="form-group">
              <label class="form-label">Land Size</label>
              <input v-model.number="propForm.landSize" type="number" step="0.5" placeholder="5" class="form-input" />
            </div>

            <!-- Land Unit (Dynamic) -->
            <div class="form-group">
              <label class="form-label" style="display:flex; justify-content:space-between; align-items:center;">
                <span>Land Unit</span>
                <button type="button" class="inline-quick-add-btn" @click="promptAddOption('land_units', 'Land Unit')">+ Add</button>
              </label>
              <select v-model="propForm.landUnit" class="form-select">
                <option v-for="u in landUnits" :key="u" :value="u">{{ u }}</option>
              </select>
            </div>

            <div class="form-group">
              <label class="form-label">Beds / Baths</label>
              <div class="flex gap-2">
                <input v-model.number="propForm.bedrooms" type="number" min="0" placeholder="Beds" class="form-input" />
                <input v-model.number="propForm.bathrooms" type="number" min="0" placeholder="Baths" class="form-input" />
              </div>
            </div>
          </div>

          <div class="grid grid-3" style="gap:14px;">
            <div class="form-group">
              <label class="form-label">Facing Direction</label>
              <select v-model="propForm.facing" class="form-select">
                <option value="South">South Facing</option>
                <option value="North">North Facing</option>
                <option value="East">East Facing</option>
                <option value="West">West Facing</option>
                <option value="South-East">South-East Facing</option>
                <option value="North-East">North-East Facing</option>
              </select>
            </div>

            <div class="form-group">
              <label class="form-label">Parking Spaces</label>
              <input v-model.number="propForm.parking" type="number" min="0" placeholder="2" class="form-input" />
            </div>

            <div class="form-group">
              <label class="form-label">Completion Status</label>
              <select v-model="propForm.completionStatus" class="form-select">
                <option value="Ready">Ready for Handover</option>
                <option value="Under Construction">Under Construction</option>
                <option value="Upcoming Project">Upcoming Project</option>
              </select>
            </div>
          </div>
        </div>

        <!-- SECTION 5: HIGHLIGHTS & DESCRIPTION -->
        <div id="sec-details" class="property-editor-card">
          <div class="property-card-header">
            <div class="property-card-title">
              <span class="property-card-icon">5</span>
              <span>Highlights & Editorial Narrative</span>
            </div>
            <span v-if="propForm.tagline || propForm.description" style="color:#10B981; font-size:0.8rem; font-weight:700;">✔ Completed</span>
          </div>

          <div class="form-group" style="margin-bottom:14px;">
            <label class="form-label">Primary Headline / Key Selling Point</label>
            <input v-model="propForm.tagline" type="text" placeholder="e.g. Panoramic Lakefront Skyline View with Private Sky Garden & 3 Car Parking" class="form-input" />
          </div>

          <div class="form-group">
            <label class="form-label">Comprehensive Editorial Description</label>
            <textarea 
              v-model="propForm.description" 
              rows="4" 
              placeholder="Detail the layout architectural highlights, fittings, floor materials, elevator specs, security systems, mutation status, and neighborhood advantages..." 
              class="form-input" 
              style="resize:vertical; line-height:1.5;"
            ></textarea>
          </div>
        </div>

        <!-- SECTION 6: PHOTOS & MEDIA SHOWCASE -->
        <div id="sec-media" class="property-editor-card">
          <div class="property-card-header">
            <div class="property-card-title">
              <span class="property-card-icon">6</span>
              <span>Photos & Media Showcase</span>
              <span class="badge" style="background:rgba(212,175,55,0.15); color:var(--color-gold); font-size:0.75rem; margin-left:8px;">
                {{ (propForm.featureImage ? 1 : 0) + propForm.gallery.length }} Photos Attached
              </span>
            </div>
            <span v-if="propForm.featureImage" style="color:#10B981; font-size:0.8rem; font-weight:700;">✔ Cover Set</span>
          </div>

          <!-- Primary Hero Cover Image -->
          <div style="background:rgba(255,255,255,0.02); padding:16px; border-radius:var(--radius-md); border:1px solid var(--admin-border-subtle); margin-bottom:16px;">
            <div class="flex items-center justify-between" style="margin-bottom:10px;">
              <span style="font-weight:700; color:#10B981; font-size:0.9rem;">★ Primary Featured Cover Image</span>
              <button type="button" class="btn btn-sm btn-outline-white" style="font-size:0.75rem; padding:4px 10px;" @click="applySampleCover">
                ✨ Random Preset Cover
              </button>
            </div>

            <div class="grid grid-2" style="gap:16px; align-items:center;">
              <div class="feature-img-preview-box">
                <img :src="propForm.featureImage || 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?q=80&w=1600&auto=format&fit=crop'" alt="Cover Preview" />
                <div class="feature-img-badge">
                  <span>★ Featured Hero Cover</span>
                </div>
              </div>

              <div class="flex flex-col gap-3">
                <input v-model="propForm.featureImage" type="url" placeholder="Paste image URL (https://...)" class="form-input" style="font-size:0.85rem;" />
                <input ref="featureFileInput" type="file" accept="image/*" style="display:none;" @change="onFeatureFileSelected" />
                <div class="flex items-center gap-2">
                  <button type="button" class="btn btn-sm btn-emerald flex-1" :disabled="isUploadingFeature" @click="triggerFeatureUpload">
                    <span v-if="isUploadingFeature" class="animate-spin mr-1">◌</span>
                    <span>{{ isUploadingFeature ? 'Uploading...' : '📁 Upload Local Cover Photo' }}</span>
                  </button>
                  <button v-if="propForm.featureImage" type="button" class="btn btn-sm btn-outline-white" @click="propForm.featureImage = ''">
                    Clear
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Gallery Photos -->
          <div style="background:rgba(255,255,255,0.02); padding:16px; border-radius:var(--radius-md); border:1px solid var(--admin-border-subtle);">
            <div class="flex items-center justify-between flex-wrap gap-2" style="margin-bottom:12px;">
              <label class="form-label" style="font-weight:700; color:var(--color-gold); margin-bottom:0;">
                🖼 Gallery Photos ({{ propForm.gallery.length }} shots)
              </label>
              <input ref="galleryFileInput" type="file" multiple accept="image/*" style="display:none;" @change="onGalleryFilesSelected" />
              <div class="flex items-center gap-2">
                <button type="button" class="btn btn-sm btn-outline-white" style="font-size:0.75rem; padding:4px 10px;" @click="applySampleGalleryPack">
                  Load Luxury Pack
                </button>
                <button type="button" class="btn btn-sm btn-emerald" :disabled="isUploadingGallery" @click="triggerGalleryUpload" style="font-size:0.75rem; padding:4px 12px;">
                  <span v-if="isUploadingGallery" class="animate-spin mr-1">◌</span>
                  <span>Upload Photos</span>
                </button>
              </div>
            </div>

            <div class="flex gap-2" style="margin-bottom:12px;">
              <input v-model="newGalleryUrl" type="url" placeholder="Paste gallery image URL and hit Enter..." class="form-input" style="flex:1; font-size:0.85rem;" @keydown.enter.prevent="addGalleryUrl" />
              <button type="button" class="btn btn-sm btn-outline-white" @click="addGalleryUrl">Add URL</button>
            </div>

            <div v-if="propForm.gallery.length > 0" class="gallery-grid">
              <div v-for="(imgUrl, idx) in propForm.gallery" :key="idx" class="gallery-item-card">
                <img :src="imgUrl" :alt="'Gallery ' + (idx + 1)" loading="lazy" />
                <div class="gallery-item-overlay">
                  <div class="flex justify-between items-center">
                    <span style="font-size:0.7rem; color:#FFF; font-weight:700; background:rgba(0,0,0,0.6); padding:2px 6px; border-radius:3px;">#{{ idx + 1 }}</span>
                    <button type="button" class="gallery-btn-action danger" @click="removeGalleryItem(idx)" title="Remove photo">✕</button>
                  </div>
                  <button type="button" class="gallery-btn-action star" @click="makeCover(idx)" title="Set as primary featured cover">★ Set as Cover</button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- SECTION 7: PROJECT BROCHURE & DOCUMENTS -->
        <div id="sec-brochure" class="property-editor-card">
          <div class="property-card-header">
            <div class="property-card-title">
              <span class="property-card-icon">7</span>
              <span>Project Brochure & Documents</span>
            </div>
            <span v-if="propForm.brochureUrl" style="color:#10B981; font-size:0.8rem; font-weight:700;">✔ Attached</span>
          </div>

          <p style="font-size:0.82rem; color:var(--admin-text-muted); margin-bottom:12px;">
            Attach an official architectural floorplan, sales brochure, or project deck for potential investors to download.
          </p>

          <input ref="brochureFileInput" type="file" accept=".pdf,.doc,.docx" style="display:none;" @change="onBrochureFileSelected" />
          <div class="flex items-center gap-2">
            <button type="button" class="btn btn-sm btn-emerald" :disabled="isUploadingBrochure" @click="triggerBrochureUpload">
              <span v-if="isUploadingBrochure" class="animate-spin mr-1">◌</span>
              <span>{{ isUploadingBrochure ? 'Uploading...' : '📁 Upload PDF Brochure' }}</span>
            </button>
            <input v-model="propForm.brochureUrl" type="url" placeholder="Or paste direct brochure URL (e.g. /storage/... or https://...)" class="form-input" style="flex:1; font-size:0.85rem;" />
            <button v-if="propForm.brochureUrl" type="button" class="btn btn-sm btn-outline-white" @click="propForm.brochureUrl = ''">Clear</button>
          </div>
        </div>

        <!-- SECTION 8: LEGAL CLEARANCES & HOMEPAGE FEATURE -->
        <div id="sec-legal" class="property-editor-card">
          <div class="property-card-header">
            <div class="property-card-title">
              <span class="property-card-icon">8</span>
              <span>Legal Clearances & Verification</span>
            </div>
            <span v-if="propForm.isRajukApproved" style="color:#10B981; font-size:0.8rem; font-weight:700;">✔ Verified</span>
          </div>

          <div class="flex items-center gap-8 flex-wrap">
            <label class="flex items-center gap-2" style="cursor:pointer; font-size:0.9rem; color:var(--admin-text-primary);">
              <input v-model="propForm.isRajukApproved" type="checkbox" style="width:18px; height:18px; accent-color:#10B981;" />
              <span>RAJUK / CDA Approved Building Plan Verified</span>
            </label>
            <label class="flex items-center gap-2" style="cursor:pointer; font-size:0.9rem; color:var(--admin-text-primary);">
              <input v-model="propForm.isFeatured" type="checkbox" style="width:18px; height:18px; accent-color:#D4AF37;" />
              <span>Feature on Live Homepage Showcase (Star Badge)</span>
            </label>
          </div>
        </div>

        <!-- SECTION 9: PRIVACY & CONFIDENTIALITY CONTROLS -->
        <div id="sec-privacy" class="property-editor-card" style="border-color:rgba(212,175,55,0.25);">
          <div class="property-card-header">
            <div class="property-card-title">
              <span class="property-card-icon">9</span>
              <span>Client Privacy & Confidentiality Controls</span>
            </div>
            <span style="font-size:0.75rem; color:var(--color-gold);">VIP Seller Protections</span>
          </div>

          <p style="font-size:0.82rem; color:var(--admin-text-muted); margin-bottom:14px;">
            Confidential listings protect seller identity and VIP addresses from unsolicited direct approaches.
          </p>

          <div class="grid grid-2" style="gap:12px;">
            <label class="flex items-center gap-2" style="cursor:pointer; font-size:0.85rem; color:var(--admin-text-primary);">
              <input v-model="propForm.hideAgentPhoto" type="checkbox" style="width:16px; height:16px; accent-color:#D4AF37;" />
              <span>🛡️ Hide Advisor Photo (Display Official GBREL Crest)</span>
            </label>
            <label class="flex items-center gap-2" style="cursor:pointer; font-size:0.85rem; color:var(--admin-text-primary);">
              <input v-model="propForm.hideExactAddress" type="checkbox" style="width:16px; height:16px; accent-color:#38BDF8;" />
              <span>📍 Hide Exact Street / Plot Address (Show Area Only)</span>
            </label>
            <label class="flex items-center gap-2" style="cursor:pointer; font-size:0.85rem; color:var(--admin-text-primary);">
              <input v-model="propForm.hideFloorPlan" type="checkbox" style="width:16px; height:16px; accent-color:#A855F7;" />
              <span>📐 Gate Floor Plans (Signed NDA Required to View)</span>
            </label>
            <label class="flex items-center gap-2" style="cursor:pointer; font-size:0.85rem; color:var(--admin-text-primary);">
              <input v-model="propForm.hideAgentContact" type="checkbox" style="width:16px; height:16px; accent-color:#10B981;" />
              <span>🔒 Route Inquiries through Corporate Concierge</span>
            </label>
          </div>
        </div>

      </div>

      <!-- RIGHT COLUMN: FIXED COMPLETION GUIDE & QUICK NAVIGATOR -->
      <div class="property-guide-column-wrap">
        <aside class="property-guide-panel">
          <div class="guide-floating-badge-row">
            <div class="guide-floating-badge">
              <span class="guide-pulse-dot"></span>
              <span>COMPLETION GUIDE</span>
            </div>
            <span class="badge" style="background:rgba(212,175,55,0.15); color:var(--color-gold); font-size:0.75rem; font-weight:700;">
              {{ completedStepsCount }}/{{ totalStepsCount }} Done
            </span>
          </div>

          <!-- Readiness Progress Bar -->
          <div>
            <div class="flex justify-between items-center" style="font-size:0.78rem; color:var(--admin-text-muted); margin-bottom:6px;">
              <span>Listing Readiness Score</span>
              <strong :style="{ color: completionPercentage === 100 ? '#10B981' : 'var(--color-gold)' }">{{ completionPercentage }}%</strong>
            </div>
            <div style="background:rgba(255,255,255,0.08); height:8px; border-radius:4px; overflow:hidden;">
              <div 
                :style="{ 
                  width: completionPercentage + '%', 
                  background: completionPercentage === 100 ? '#10B981' : 'linear-gradient(90deg, #D4AF37, #10B981)',
                  height: '100%',
                  transition: 'width 0.3s ease'
                }"
              ></div>
            </div>
          </div>

          <!-- Clickable Guideline Items with Active Section Tracking -->
          <div class="guide-steps-list">
            <div 
              v-for="(item, idx) in guideItems" 
              :key="item.id"
              class="guide-step-item"
              :class="{ 'is-done': item.isCompleted, 'is-active-step': activeSectionId === item.id }"
              @click="scrollToSection(item.id)"
              :title="'Click to jump to ' + item.title"
            >
              <div class="guide-check-circle" :class="item.isCompleted ? 'done' : 'pending'">
                <span v-if="item.isCompleted">✔</span>
                <span v-else>{{ idx + 1 }}</span>
              </div>
              <div style="flex:1; min-width:0;">
                <div class="flex items-center justify-between gap-1">
                  <div style="font-size:0.82rem; font-weight:700; color:var(--admin-text-primary); line-height:1.2;">
                    {{ item.title }}
                  </div>
                  <span v-if="activeSectionId === item.id" style="font-size:0.65rem; color:var(--color-gold); font-weight:800; text-transform:uppercase; letter-spacing:0.04em;">
                    Viewing
                  </span>
                </div>
                <div style="font-size:0.72rem; color:var(--admin-text-muted); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; margin-top:2px;">
                  {{ item.sub }}
                </div>
              </div>
              <span style="font-size:0.75rem; color:var(--admin-text-muted);">›</span>
            </div>
          </div>

          <!-- Dynamic Recommendation Tip Box -->
          <div style="background:rgba(212,175,55,0.08); border:1px solid rgba(212,175,55,0.25); border-radius:8px; padding:12px;">
            <div style="font-size:0.76rem; font-weight:700; color:var(--color-gold); margin-bottom:4px; display:flex; align-items:center; gap:4px;">
              <span>💡 Recommendation:</span>
            </div>
            <p style="font-size:0.78rem; color:var(--admin-text-secondary); line-height:1.45; margin:0;">
              {{ activeGuideTip }}
            </p>
          </div>

          <!-- Quick Save Actions Inside Guide -->
          <div class="flex flex-col gap-2 pt-2">
            <button 
              type="button" 
              class="btn btn-sm btn-outline-white" 
              style="width:100%; font-size:0.82rem; padding:8px;" 
              :disabled="isSaving" 
              @click="saveProperty('Draft')"
            >
              <span>💾 Save Current Draft</span>
            </button>
            <button 
              type="button" 
              class="btn btn-sm btn-emerald" 
              style="width:100%; font-size:0.82rem; padding:8px;" 
              :disabled="isSaving" 
              @click="saveProperty('Active')"
            >
              <span>🚀 Publish Live Listing</span>
            </button>
          </div>
        </aside>
      </div>
    </div>

    <!-- MOBILE FLOATING GUIDE TRIGGER (<= 1080px) -->
    <button 
      type="button" 
      class="mobile-floating-guide-btn" 
      @click="mobileGuideOpen = true"
      title="Open Property Completion Guide"
    >
      <span>📋 Guide ({{ completedStepsCount }}/{{ totalStepsCount }})</span>
      <span style="background:rgba(0,0,0,0.25); padding:2px 6px; border-radius:10px; font-size:0.75rem;">
        {{ completionPercentage }}%
      </span>
    </button>

    <!-- MOBILE GUIDE BOTTOM SHEET -->
    <div v-if="mobileGuideOpen" class="mobile-guide-overlay" @click.self="mobileGuideOpen = false">
      <div class="mobile-guide-sheet">
        <div class="flex items-center justify-between pb-3 mb-3" style="border-bottom:1px solid var(--admin-border-subtle);">
          <div class="guide-floating-badge">
            <span class="guide-pulse-dot"></span>
            <span>COMPLETION GUIDE</span>
          </div>
          <button class="btn btn-sm btn-outline-white" @click="mobileGuideOpen = false">✕ Close</button>
        </div>

        <div class="mb-3">
          <div class="flex justify-between items-center" style="font-size:0.78rem; color:var(--admin-text-muted); margin-bottom:6px;">
            <span>Listing Readiness Score</span>
            <strong :style="{ color: completionPercentage === 100 ? '#10B981' : 'var(--color-gold)' }">{{ completionPercentage }}%</strong>
          </div>
          <div style="background:rgba(255,255,255,0.08); height:8px; border-radius:4px; overflow:hidden;">
            <div 
              :style="{ 
                width: completionPercentage + '%', 
                background: completionPercentage === 100 ? '#10B981' : 'linear-gradient(90deg, #D4AF37, #10B981)',
                height: '100%',
                transition: 'width 0.3s ease'
              }"
            ></div>
          </div>
        </div>

        <div class="guide-steps-list mb-4" style="max-height:48vh;">
          <div 
            v-for="(item, idx) in guideItems" 
            :key="item.id"
            class="guide-step-item"
            :class="{ 'is-done': item.isCompleted, 'is-active-step': activeSectionId === item.id }"
            @click="scrollToSection(item.id)"
          >
            <div class="guide-check-circle" :class="item.isCompleted ? 'done' : 'pending'">
              <span v-if="item.isCompleted">✔</span>
              <span v-else>{{ idx + 1 }}</span>
            </div>
            <div style="flex:1; min-width:0;">
              <div class="flex items-center justify-between gap-1">
                <div style="font-size:0.85rem; font-weight:700; color:var(--admin-text-primary);">{{ item.title }}</div>
                <span v-if="activeSectionId === item.id" style="font-size:0.65rem; color:var(--color-gold); font-weight:800; text-transform:uppercase;">
                  Viewing
                </span>
              </div>
              <div style="font-size:0.75rem; color:var(--admin-text-muted);">{{ item.sub }}</div>
            </div>
            <span style="font-size:0.85rem; color:var(--admin-text-muted);">›</span>
          </div>
        </div>

        <div class="flex gap-2">
          <button type="button" class="btn btn-sm btn-outline-white flex-1" @click="saveProperty('Draft')">Save Draft</button>
          <button type="button" class="btn btn-sm btn-emerald flex-1" @click="saveProperty('Active')">Publish Live</button>
        </div>
      </div>
    </div>

    <!-- QUICK MODAL TO ADD CUSTOM DYNAMIC OPTION -->
    <div v-if="customOptionModal.isOpen" class="admin-modal-overlay" @click.self="customOptionModal.isOpen = false">
      <div class="admin-modal-card animate-fade-in-up" style="max-width:420px;">
        <div class="admin-modal-header">
          <h3 class="admin-modal-title">Add Custom {{ customOptionModal.label }}</h3>
          <button class="admin-modal-close" @click="customOptionModal.isOpen = false">✕</button>
        </div>
        <div class="admin-modal-body">
          <p style="font-size:0.85rem; color:var(--admin-text-muted); margin-bottom:12px;">
            Enter the name of the new {{ customOptionModal.label }}. This will be dynamically saved to the database and instantly selectable.
          </p>
          <div class="form-group">
            <label class="form-label">New {{ customOptionModal.label }} Name</label>
            <input 
              v-model="customOptionModal.value" 
              type="text" 
              placeholder="e.g. Waterfront Villa, Eco Tourism Plot, etc." 
              class="form-input" 
              @keydown.enter.prevent="submitCustomOption"
              autofocus
            />
          </div>
        </div>
        <div class="admin-modal-footer">
          <button type="button" class="btn btn-sm btn-outline-white" @click="customOptionModal.isOpen = false">Cancel</button>
          <button type="button" class="btn btn-sm btn-emerald" @click="submitCustomOption">Save & Select</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, computed, onMounted, onUnmounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useProperties, type PropertyItem } from '~/composables/useProperties'
import { usePropertyOptions } from '~/composables/usePropertyOptions'
import { formatBDT, formatArea } from '~/composables/useCurrency'
import { useToast } from '~/composables/useToast'

const props = defineProps<{
  propertyId?: number | string
}>()

const router = useRouter()
const toast = useToast()

const activeSectionId = ref('sec-basic')
const mobileGuideOpen = ref(false)

const { 
  addProperty, 
  updateProperty, 
  fetchPropertyById, 
  uploadImage, 
  uploadMultipleImages, 
  uploadBrochure 
} = useProperties()

const { 
  categories, 
  divisions, 
  transactionTypes, 
  statuses, 
  landUnits, 
  fetchOptions, 
  addCustomOption 
} = usePropertyOptions()

const isSaving = ref(false)
const isUploadingFeature = ref(false)
const isUploadingGallery = ref(false)
const isUploadingBrochure = ref(false)
const newGalleryUrl = ref('')

const featureFileInput = ref<HTMLInputElement | null>(null)
const galleryFileInput = ref<HTMLInputElement | null>(null)
const brochureFileInput = ref<HTMLInputElement | null>(null)

const route = useRoute()
const isEditing = computed(() => Boolean(props.propertyId || route.params.id || route.query.id))
const propId = computed(() => Number(props.propertyId || route.params.id || route.query.id) || null)

const propForm = reactive({
  title: '',
  tagline: '',
  description: '',
  propertyType: 'Flat',
  status: 'Draft',
  listingType: 'Sale',
  state: 'Dhaka North',
  areaName: '',
  address: '',
  price: 35000000,
  bedrooms: 3,
  bathrooms: 3,
  parking: 1,
  squareFootage: 2400,
  landSize: 0,
  landUnit: 'Katha',
  facing: 'South',
  completionStatus: 'Ready',
  isRajukApproved: true,
  isFeatured: false,
  featureImage: 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?q=80&w=1600&auto=format&fit=crop',
  gallery: [] as string[],
  brochureUrl: '',
  hidePrice: false,
  priceDisplayText: 'Price on Application',
  hideAgentPhoto: false,
  hideAgentContact: false,
  hideExactAddress: false,
  hideFloorPlan: false,
  hideMortgageCalculator: false,
  agentId: 1
})

const SAMPLE_COVERS = [
  'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?q=80&w=1600&auto=format&fit=crop',
  'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?q=80&w=1600&auto=format&fit=crop',
  'https://images.unsplash.com/photo-1500382017468-9049fed747ef?q=80&w=1600&auto=format&fit=crop',
  'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?q=80&w=1600&auto=format&fit=crop',
  'https://images.unsplash.com/photo-1540555700478-4be289fbecef?q=80&w=1600&auto=format&fit=crop',
  'https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?q=80&w=1600&auto=format&fit=crop'
]

const SAMPLE_GALLERY_PACK = [
  'https://images.unsplash.com/photo-1600566753376-12c8ab7fb75b?q=80&w=1200&auto=format&fit=crop',
  'https://images.unsplash.com/photo-1600566753190-17f0baa2a6c3?q=80&w=1200&auto=format&fit=crop',
  'https://images.unsplash.com/photo-1600210492486-724fe5c67fb0?q=80&w=1200&auto=format&fit=crop',
  'https://images.unsplash.com/photo-1571896349842-33c89424de2d?q=80&w=1200&auto=format&fit=crop'
]

// Modal state for adding custom option
const customOptionModal = reactive({
  isOpen: false,
  key: 'categories' as 'categories' | 'divisions' | 'transaction_types' | 'statuses' | 'land_units',
  label: 'Category',
  value: ''
})

const promptAddOption = (key: 'categories' | 'divisions' | 'transaction_types' | 'statuses' | 'land_units', label: string) => {
  customOptionModal.key = key
  customOptionModal.label = label
  customOptionModal.value = ''
  customOptionModal.isOpen = true
}

const submitCustomOption = async () => {
  const val = customOptionModal.value.trim()
  if (!val) {
    toast.warning('Name Required', 'Please enter a name for the option.')
    return
  }

  try {
    await addCustomOption(customOptionModal.key, val)
    if (customOptionModal.key === 'categories') propForm.propertyType = val
    if (customOptionModal.key === 'divisions') propForm.state = val
    if (customOptionModal.key === 'transaction_types') propForm.listingType = val as any
    if (customOptionModal.key === 'statuses') propForm.status = val as any
    if (customOptionModal.key === 'land_units') propForm.landUnit = val as any

    toast.success('Option Saved', `Added "${val}" to ${customOptionModal.label}.`)
    customOptionModal.isOpen = false
  } catch (err: any) {
    toast.error('Error', err.message || 'Could not save option.')
  }
}

// Status styling
const statusBadgeStyle = computed(() => {
  switch (propForm.status) {
    case 'Active':
      return { background: 'rgba(16,185,129,0.15)', color: '#10B981', border: '1px solid rgba(16,185,129,0.3)' }
    case 'Draft':
      return { background: 'rgba(212,175,55,0.15)', color: 'var(--color-gold)', border: '1px solid rgba(212,175,55,0.3)' }
    case 'Under Offer':
      return { background: 'rgba(56,189,248,0.15)', color: '#38BDF8', border: '1px solid rgba(56,189,248,0.3)' }
    case 'Sold':
      return { background: 'rgba(168,85,247,0.15)', color: '#A855F7', border: '1px solid rgba(168,85,247,0.3)' }
    default:
      return { background: 'rgba(255,255,255,0.06)', color: 'var(--admin-text-muted)', border: '1px solid rgba(255,255,255,0.1)' }
  }
})

// Guideline items
const guideItems = computed(() => [
  {
    id: 'sec-basic',
    title: '1. Basic Identity & Category',
    sub: propForm.title ? `${propForm.title.slice(0, 24)}... (${propForm.propertyType})` : 'Listing title & category needed',
    isCompleted: Boolean(propForm.title?.trim() && propForm.propertyType)
  },
  {
    id: 'sec-location',
    title: '2. Location & Regional Mapping',
    sub: propForm.areaName ? `${propForm.areaName}, ${propForm.state}` : 'Area & neighborhood needed',
    isCompleted: Boolean(propForm.areaName?.trim() && propForm.state)
  },
  {
    id: 'sec-pricing',
    title: '3. Pricing & Valuation',
    sub: propForm.hidePrice ? 'Confidential (POA)' : (propForm.price > 0 ? formatBDT(propForm.price) : 'Asking price needed'),
    isCompleted: Boolean(propForm.hidePrice || propForm.price > 0)
  },
  {
    id: 'sec-specs',
    title: '4. Dimensions & Specifications',
    sub: (propForm.squareFootage > 0 || propForm.landSize > 0) ? `${formatArea(propForm.squareFootage, propForm.landSize, propForm.landUnit)} • ${propForm.bedrooms} Beds` : 'Sqft or Land size needed',
    isCompleted: Boolean(propForm.squareFootage > 0 || propForm.landSize > 0)
  },
  {
    id: 'sec-details',
    title: '5. Highlights & Editorial Story',
    sub: propForm.description ? 'Description provided' : (propForm.tagline ? 'Tagline entered' : 'Add selling description'),
    isCompleted: Boolean(propForm.tagline?.trim() || propForm.description?.trim())
  },
  {
    id: 'sec-media',
    title: '6. Photos & Media Showcase',
    sub: propForm.featureImage ? `${(propForm.featureImage ? 1 : 0) + propForm.gallery.length} visual asset(s)` : 'Hero cover photo required',
    isCompleted: Boolean(propForm.featureImage?.trim())
  },
  {
    id: 'sec-brochure',
    title: '7. Brochure PDF Attachment',
    sub: propForm.brochureUrl ? 'PDF Brochure attached' : 'Optional sales deck PDF',
    isCompleted: Boolean(propForm.brochureUrl?.trim())
  },
  {
    id: 'sec-legal',
    title: '8. Legal Clearances & Badges',
    sub: propForm.isRajukApproved ? 'RAJUK/CDA Verified' : 'Compliance checklist review',
    isCompleted: Boolean(propForm.isRajukApproved)
  },
  {
    id: 'sec-privacy',
    title: '9. Privacy & Confidentiality',
    sub: (propForm.hideAgentPhoto || propForm.hideExactAddress || propForm.hideFloorPlan || propForm.hidePrice) ? 'Privacy protections enabled' : 'Standard public exposure',
    isCompleted: true
  }
])

const totalStepsCount = computed(() => guideItems.value.length)
const completedStepsCount = computed(() => guideItems.value.filter(i => i.isCompleted).length)
const completionPercentage = computed(() => {
  if (totalStepsCount.value === 0) return 0
  return Math.round((completedStepsCount.value / totalStepsCount.value) * 100)
})

const activeGuideTip = computed(() => {
  if (!propForm.title?.trim()) return 'Add a descriptive headline (e.g., "Grand 3,500 Sqft Duplex Penthouse in Gulshan-2").'
  if (!propForm.areaName?.trim()) return 'Enter the neighborhood or landmark to assist map matching.'
  if (!propForm.featureImage?.trim()) return 'Upload a high-resolution hero cover photo or choose a preset to entice luxury buyers.'
  if (!propForm.brochureUrl?.trim()) return 'Upload an architectural floorplan or sales brochure PDF for investor downloads.'
  if (completionPercentage.value === 100) return 'All essential details are filled! The listing is ready for public publication.'
  return 'Review specifications, dimensions, and legal compliance before publishing live.'
})

const scrollToSection = (sectionId: string) => {
  activeSectionId.value = sectionId
  const el = document.getElementById(sectionId)
  if (el) {
    const yOffset = -146
    const y = el.getBoundingClientRect().top + window.pageYOffset + yOffset
    window.scrollTo({ top: y, behavior: 'smooth' })
    el.classList.add('section-highlight-pulse')
    setTimeout(() => {
      el.classList.remove('section-highlight-pulse')
    }, 1500)
  }
  mobileGuideOpen.value = false
}

let sectionObserver: IntersectionObserver | null = null

const setupScrollSpy = () => {
  if (typeof window === 'undefined' || !('IntersectionObserver' in window)) return

  sectionObserver = new IntersectionObserver((entries) => {
    for (const entry of entries) {
      if (entry.isIntersecting) {
        activeSectionId.value = entry.target.id
      }
    }
  }, {
    rootMargin: '-130px 0px -40% 0px',
    threshold: 0.1
  })

  const sectionIds = [
    'sec-basic', 'sec-location', 'sec-pricing', 'sec-specs', 
    'sec-details', 'sec-media', 'sec-brochure', 'sec-legal', 'sec-privacy'
  ]
  sectionIds.forEach(id => {
    const el = document.getElementById(id)
    if (el) sectionObserver?.observe(el)
  })
}

// Media upload helpers
const triggerFeatureUpload = () => featureFileInput.value?.click()
const triggerGalleryUpload = () => galleryFileInput.value?.click()
const triggerBrochureUpload = () => brochureFileInput.value?.click()

const onFeatureFileSelected = async (e: Event) => {
  const target = e.target as HTMLInputElement
  if (!target.files || target.files.length === 0) return
  isUploadingFeature.value = true
  try {
    const url = await uploadImage(target.files[0])
    propForm.featureImage = url
    toast.success('Cover Uploaded', 'Hero cover image saved.')
  } catch (err: any) {
    toast.error('Upload Error', err.message || 'Could not upload cover image.')
  } finally {
    isUploadingFeature.value = false
    target.value = ''
  }
}

const onGalleryFilesSelected = async (e: Event) => {
  const target = e.target as HTMLInputElement
  if (!target.files || target.files.length === 0) return
  isUploadingGallery.value = true
  try {
    const urls = await uploadMultipleImages(target.files)
    propForm.gallery.push(...urls)
    toast.success('Gallery Updated', `${urls.length} photo(s) added to gallery.`)
  } catch (err: any) {
    toast.error('Upload Error', err.message || 'Could not upload gallery images.')
  } finally {
    isUploadingGallery.value = false
    target.value = ''
  }
}

const onBrochureFileSelected = async (e: Event) => {
  const target = e.target as HTMLInputElement
  if (!target.files || target.files.length === 0) return
  isUploadingBrochure.value = true
  try {
    const url = await uploadBrochure(target.files[0])
    propForm.brochureUrl = url
    toast.success('Brochure Uploaded', 'Document saved successfully.')
  } catch (err: any) {
    toast.error('Upload Error', err.message || 'Could not upload brochure.')
  } finally {
    isUploadingBrochure.value = false
    target.value = ''
  }
}

const addGalleryUrl = () => {
  const val = newGalleryUrl.value.trim()
  if (!val) return
  if (!propForm.gallery.includes(val)) {
    propForm.gallery.push(val)
    toast.info('Photo Added', 'Image appended to gallery.')
  }
  newGalleryUrl.value = ''
}

const removeGalleryItem = (index: number) => {
  propForm.gallery.splice(index, 1)
}

const makeCover = (index: number) => {
  const selected = propForm.gallery[index]
  const currentCover = propForm.featureImage
  propForm.featureImage = selected
  if (currentCover && currentCover !== selected) {
    propForm.gallery[index] = currentCover
  } else {
    propForm.gallery.splice(index, 1)
  }
  toast.success('Cover Changed', 'Selected photo set as primary cover.')
}

const applySampleCover = () => {
  const random = SAMPLE_COVERS[Math.floor(Math.random() * SAMPLE_COVERS.length)]
  propForm.featureImage = random
}

const applySampleGalleryPack = () => {
  for (const s of SAMPLE_GALLERY_PACK) {
    if (!propForm.gallery.includes(s) && s !== propForm.featureImage) {
      propForm.gallery.push(s)
    }
  }
  toast.info('Sample Pack Loaded', 'Attached luxury architectural photo pack.')
}

const handleGoBack = () => {
  router.push('/admin/properties')
}

// Load data on mount
onMounted(async () => {
  await fetchOptions()

  if (isEditing.value && propId.value) {
    try {
      const item = await fetchPropertyById(propId.value)
      if (item) {
        propForm.title = item.title
        propForm.tagline = item.tagline || ''
        propForm.description = item.description || ''
        propForm.propertyType = item.propertyType
        propForm.status = item.status || 'Active'
        propForm.listingType = item.listingType || 'Sale'
        propForm.state = item.state || 'Dhaka North'
        propForm.areaName = item.areaName || ''
        propForm.address = item.address || ''
        propForm.price = item.price || 0
        propForm.bedrooms = item.bedrooms || 0
        propForm.bathrooms = item.bathrooms || 0
        propForm.parking = item.parking || 1
        propForm.squareFootage = item.squareFootage || 0
        propForm.landSize = item.landSize || 0
        propForm.landUnit = item.landUnit || 'Katha'
        propForm.isRajukApproved = item.isRajukApproved
        propForm.isFeatured = item.isFeatured
        propForm.featureImage = item.featureImage || item.images?.[0] || ''
        propForm.gallery = item.gallery && item.gallery.length > 0 ? [...item.gallery] : (item.images?.slice(1) || [])
        propForm.brochureUrl = item.brochureUrl || ''
        propForm.hidePrice = Boolean(item.hidePrice)
        propForm.priceDisplayText = item.priceDisplayText || 'Price on Application'
        propForm.hideAgentPhoto = Boolean(item.hideAgentPhoto)
        propForm.hideAgentContact = Boolean(item.hideAgentContact)
        propForm.hideExactAddress = Boolean(item.hideExactAddress)
        propForm.hideFloorPlan = Boolean(item.hideFloorPlan)
        propForm.hideMortgageCalculator = Boolean(item.hideMortgageCalculator)
      }
    } catch (err: any) {
      toast.error('Load Failed', err.message || 'Could not load property details.')
    }
  } else {
    propForm.status = 'Draft'
  }

  setTimeout(() => {
    setupScrollSpy()
  }, 300)
})

onUnmounted(() => {
  sectionObserver?.disconnect()
})

// Save & Publish
const saveProperty = async (targetStatus?: 'Draft' | 'Active') => {
  if (targetStatus) {
    propForm.status = targetStatus
  }

  if (propForm.status === 'Draft') {
    if (!propForm.title.trim()) propForm.title = 'Untitled Draft Property'
    if (!propForm.areaName.trim()) propForm.areaName = 'Unspecified Location'
    if (!propForm.address.trim()) propForm.address = 'Draft Address'
  } else {
    if (!propForm.title.trim()) {
      toast.warning('Title Required', 'Please enter a property title before publishing.')
      scrollToSection('sec-basic')
      return
    }
    if (!propForm.areaName.trim()) {
      toast.warning('Location Required', 'Please provide the area or neighborhood name.')
      scrollToSection('sec-location')
      return
    }
  }

  isSaving.value = true
  try {
    const featureCover = propForm.featureImage.trim()
    const validGallery = propForm.gallery.map(g => g.trim()).filter(Boolean)
    const allImages = [featureCover, ...validGallery].filter(Boolean)

    if (isEditing.value && propId.value) {
      await updateProperty(propId.value, {
        title: propForm.title,
        tagline: propForm.tagline,
        description: propForm.description,
        propertyType: propForm.propertyType as any,
        status: propForm.status as any,
        listingType: propForm.listingType as any,
        state: propForm.state,
        areaName: propForm.areaName,
        address: propForm.address,
        price: propForm.price,
        bedrooms: propForm.bedrooms,
        bathrooms: propForm.bathrooms,
        parking: propForm.parking,
        squareFootage: propForm.squareFootage,
        landSize: propForm.landSize,
        landUnit: propForm.landUnit as any,
        isRajukApproved: propForm.isRajukApproved,
        isFeatured: propForm.isFeatured,
        featureImage: featureCover,
        gallery: validGallery,
        images: allImages,
        brochureUrl: propForm.brochureUrl.trim() || undefined,
        hidePrice: propForm.hidePrice,
        priceDisplayText: propForm.priceDisplayText,
        hideAgentPhoto: propForm.hideAgentPhoto,
        hideAgentContact: propForm.hideAgentContact,
        hideExactAddress: propForm.hideExactAddress,
        hideFloorPlan: propForm.hideFloorPlan,
        hideMortgageCalculator: propForm.hideMortgageCalculator
      })

      if (propForm.status === 'Draft') {
        toast.success('💾 Draft Saved', `Property draft #${propId.value} saved.`)
      } else {
        toast.success('Property Published', `Listing "${propForm.title}" published live.`)
      }
    } else {
      const created = await addProperty({
        ...propForm,
        status: propForm.status,
        featureImage: featureCover,
        gallery: validGallery,
        images: allImages,
        brochureUrl: propForm.brochureUrl.trim() || undefined
      })

      if (propForm.status === 'Draft') {
        toast.success('💾 Draft Saved', `New property saved as Draft #${created?.id || ''}.`)
      } else {
        toast.success('Property Published', `Listing #${created?.id || ''} published live.`)
      }
    }

    router.push('/admin/properties')
  } catch (err: any) {
    toast.error('Save Failed', err.message || 'Unable to save property.')
  } finally {
    isSaving.value = false
  }
}
</script>

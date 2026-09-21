<template>
  <div class="admin-page animate-fade-in">
    <!-- Top Header & Live Sync Status -->
    <div class="admin-header-row">
      <div>
        <div class="flex items-center gap-3 flex-wrap">
          <h1 class="page-title">Property & Land Catalog (CRUD Engine)</h1>
          <span 
            class="badge" 
            :style="{
              background: isLoading ? 'rgba(234, 179, 8, 0.15)' : 'rgba(16, 185, 129, 0.15)',
              color: isLoading ? '#EAB308' : '#10B981',
              border: isLoading ? '1px solid rgba(234, 179, 8, 0.3)' : '1px solid rgba(16, 185, 129, 0.3)',
              padding: '4px 10px',
              borderRadius: '9999px',
              fontSize: '0.78rem',
              fontWeight: '700',
              display: 'inline-flex',
              alignItems: 'center',
              gap: '6px'
            }"
          >
            <span :style="{ width: '8px', height: '8px', borderRadius: '50%', background: isLoading ? '#EAB308' : '#10B981', display: 'inline-block' }"></span>
            {{ isLoading ? 'Syncing with MySQL...' : '🟢 MySQL Production Live' }}
          </span>
        </div>
        <p class="page-subtitle">
          Realtime bidirectional synchronization with MySQL database • {{ properties.length }} live mandates recorded
          <span v-if="lastSyncedFormatted" style="color:var(--admin-text-muted); margin-left:6px;">(Last Synced: {{ lastSyncedFormatted }})</span>
        </p>
      </div>
      <div class="admin-header-actions flex items-center gap-3">
        <button 
          class="btn btn-outline-white" 
          :disabled="isLoading"
          @click="refreshData"
          title="Force fresh sync from MySQL database"
          style="display:inline-flex; align-items:center; gap:6px;"
        >
          <svg :class="{ 'animate-spin': isLoading }" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <polyline points="23 4 23 10 17 10"/>
            <path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/>
          </svg>
          <span>{{ isLoading ? 'Syncing...' : '↻ Refresh MySQL' }}</span>
        </button>
        <button class="btn btn-emerald" @click="openAddPropertyModal">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <line x1="12" y1="5" x2="12" y2="19"/>
            <line x1="5" y1="12" x2="19" y2="12"/>
          </svg>
          <span>+ Create New Mandate</span>
        </button>
      </div>
    </div>

    <!-- Search, Filter & Summary Bar -->
    <div class="panel-card" style="padding:16px 20px; margin-bottom:20px;">
      <div class="flex justify-between items-center flex-wrap gap-4">
        <div class="flex items-center gap-3 flex-wrap flex-1">
          <!-- Search Filter -->
          <label class="sr-only" for="inventory-search">Search property inventory</label>
          <div style="position:relative; width:100%; max-width:280px;">
            <input 
              id="inventory-search"
              v-model="inventorySearch" 
              type="text" 
              placeholder="Search title, address, area..." 
              class="form-input" 
              style="width:100%; padding-right:28px;" 
            />
            <button 
              v-if="inventorySearch" 
              @click="inventorySearch = ''" 
              style="position:absolute; right:8px; top:50%; transform:translateY(-50%); background:none; border:none; color:var(--admin-text-muted); cursor:pointer; font-size:12px;"
              title="Clear search"
            >✕</button>
          </div>

          <!-- Category Filter -->
          <select v-model="inventoryTypeFilter" class="form-select" style="width: auto;">
            <option value="">All Categories</option>
            <option value="Land Share">Land Share (Co-Ownership)</option>
            <option value="Flat">Flats & Apartments</option>
            <option value="Plot">Residential Plots (Katha)</option>
            <option value="Land">Freehold Lands (Bigha)</option>
            <option value="Hotel">Hotel & Beach Resorts</option>
            <option value="Duplex">Duplexes & Penthouses</option>
            <option value="Commercial">Commercial Assets</option>
          </select>

          <!-- Division Filter -->
          <select v-model="inventoryDivisionFilter" class="form-select" style="width: auto;">
            <option value="">All Divisions</option>
            <option value="Dhaka North">Dhaka North</option>
            <option value="Dhaka South">Dhaka South</option>
            <option value="Chittagong">Chittagong & Cox's Bazar</option>
            <option value="Sylhet">Sylhet</option>
          </select>

          <!-- Status Filter -->
          <select v-model="inventoryStatusFilter" class="form-select" style="width: auto;">
            <option value="">All Statuses</option>
            <option value="Active">Active</option>
            <option value="Under Offer">Under Offer</option>
            <option value="Sold">Sold</option>
            <option value="Delisted">Delisted</option>
          </select>

          <!-- Reset Filter Button -->
          <button 
            v-if="inventorySearch || inventoryTypeFilter || inventoryDivisionFilter || inventoryStatusFilter"
            class="btn btn-sm btn-outline-white" 
            @click="resetFilters"
            style="font-size:0.8rem; padding:6px 10px;"
          >
            Clear Filters
          </button>
        </div>

        <div class="flex items-center gap-2">
          <span style="font-size:0.85rem; color:var(--admin-text-muted); font-weight:600;">
            Showing {{ filteredProperties.length }} of {{ properties.length }} items
          </span>
        </div>
      </div>
    </div>

    <!-- Property Table -->
    <div class="panel-card" style="padding:0; overflow:hidden;">
      <div class="table-responsive">
        <table class="admin-table">
          <thead>
            <tr>
              <th>Property & Database ID</th>
              <th>Category & Dimensions</th>
              <th>Asking Price (BDT)</th>
              <th>Legal Clearance</th>
              <th>Featured</th>
              <th>Status</th>
              <th style="text-align:right;">Actions</th>
            </tr>
          </thead>
          <tbody>
            <!-- Loading State -->
            <tr v-if="isLoading && properties.length === 0">
              <td colspan="7" style="text-align:center; padding:48px 20px;">
                <div class="flex flex-col items-center justify-center gap-3">
                  <svg class="animate-spin" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#10B981" stroke-width="2.5">
                    <polyline points="23 4 23 10 17 10"/>
                    <path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/>
                  </svg>
                  <p style="color:var(--admin-text-primary); font-size:0.95rem; font-weight:600;">Fetching luxury mandates from MySQL production database...</p>
                </div>
              </td>
            </tr>

            <!-- Empty State -->
            <tr v-else-if="filteredProperties.length === 0">
              <td colspan="7" style="text-align:center; padding:48px 20px;">
                <div class="flex flex-col items-center justify-center gap-2">
                  <div style="font-size:2rem;">🏛</div>
                  <p style="color:var(--admin-text-primary); font-size:1rem; font-weight:600;">No properties match your current filters</p>
                  <p style="color:var(--admin-text-muted); font-size:0.85rem;">Try broadening your search or click below to register a new property mandate in MySQL.</p>
                  <button class="btn btn-emerald btn-sm" style="margin-top:8px;" @click="openAddPropertyModal">
                    + Create New Mandate
                  </button>
                </div>
              </td>
            </tr>

            <!-- Data Rows -->
            <tr v-for="prop in filteredProperties" :key="prop.id">
              <td>
                <div class="flex items-center gap-3">
                  <div style="position:relative; flex-shrink:0;">
                    <img 
                      :src="prop.featureImage || prop.images[0] || 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?q=80&w=200&auto=format&fit=crop'" 
                      class="table-thumb" 
                      alt="thumb" 
                      loading="lazy"
                    />
                    <span 
                      v-if="prop.images && prop.images.length > 1" 
                      style="position:absolute; bottom:2px; right:2px; background:rgba(10,17,40,0.85); color:#F1F5F9; font-size:0.68rem; font-weight:700; padding:1px 4px; border-radius:3px; border:1px solid rgba(255,255,255,0.2);"
                      title="Gallery photos attached"
                    >
                      📷 {{ prop.images.length }}
                    </span>
                  </div>
                  <div>
                    <strong style="color:var(--admin-text-primary); display:block; max-width:280px; line-height:1.3; font-size:0.92rem;">
                      {{ prop.title }}
                    </strong>
                    <div class="flex items-center gap-2 flex-wrap" style="margin-top:2px;">
                      <span style="font-size:0.78rem; color:var(--admin-text-muted);">
                        MySQL ID: #{{ prop.id }} • {{ prop.areaName }}, {{ prop.city }}
                      </span>
                      <a 
                        v-if="prop.brochureUrl" 
                        :href="prop.brochureUrl" 
                        target="_blank" 
                        style="font-size:0.7rem; font-weight:700; color:#38BDF8; background:rgba(56,189,248,0.15); border:1px solid rgba(56,189,248,0.3); padding:1px 6px; border-radius:3px; text-decoration:none; display:inline-flex; align-items:center; gap:3px;"
                        title="Download attached brochure PDF"
                      >
                        📄 PDF Brochure
                      </a>
                    </div>
                  </div>
                </div>
              </td>

              <td>
                <span class="badge badge-status" style="font-size:0.75rem;">{{ prop.propertyType }}</span>
                <div style="font-size:0.8rem; color:var(--admin-text-muted); margin-top:4px;">
                  {{ formatArea(prop.squareFootage, prop.landSize, prop.landUnit) }}
                </div>
              </td>

              <td style="font-family:var(--font-ui); font-size:1.05rem; font-weight:800; color:#10B981; font-variant-numeric:tabular-nums;">
                {{ formatBDT(prop.price) }}
              </td>

              <td>
                <button 
                  class="badge-admin" 
                  :class="prop.isRajukApproved ? 'active' : 'pending'"
                  :disabled="togglingRajukId === prop.id"
                  style="cursor:pointer; transition:all 0.2s;"
                  @click="handleToggleRajuk(prop.id)"
                  title="Click to toggle RAJUK Approved status in MySQL"
                >
                  <span v-if="togglingRajukId === prop.id" class="animate-spin" style="display:inline-block; margin-right:4px;">◌</span>
                  {{ prop.isRajukApproved ? '✔ RAJUK Pass' : 'Pending Audit' }}
                </button>
              </td>

              <td>
                <button 
                  class="star-toggle-btn" 
                  :class="{ active: prop.isFeatured }"
                  :disabled="togglingFeatureId === prop.id"
                  @click="handleToggleFeature(prop.id)"
                  :title="prop.isFeatured ? 'Featured on Homepage (Click to unfeature)' : 'Click to feature on Homepage in MySQL'"
                >
                  <span v-if="togglingFeatureId === prop.id" class="animate-spin" style="display:inline-block; font-size:0.8rem;">◌</span>
                  <span v-else>★</span>
                </button>
              </td>

              <td>
                <select 
                  :value="prop.status" 
                  :disabled="updatingStatusId === prop.id"
                  @change="handleStatusChange(prop.id, $event)"
                  class="status-inline-select"
                  title="Change status in MySQL database"
                >
                  <option value="Active">Active</option>
                  <option value="Under Offer">Under Offer</option>
                  <option value="Sold">Sold</option>
                  <option value="Delisted">Delisted</option>
                </select>
              </td>

              <td style="text-align:right;">
                <div class="action-btn-group">
                  <NuxtLink :to="`/properties/${prop.id}`" class="action-btn" target="_blank" title="Preview on live site" aria-label="Preview on live site">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                      <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                      <circle cx="12" cy="12" r="3"/>
                    </svg>
                  </NuxtLink>
                  <button class="action-btn edit" @click="openEditPropertyModal(prop)" title="Edit details" aria-label="Edit details">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                      <path d="M17 3a2.83 2.83 0 0 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"/>
                    </svg>
                  </button>
                  <button class="action-btn delete" @click="promptDelete(prop)" title="Delete listing" aria-label="Delete listing">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                      <polyline points="3 6 5 6 21 6"/>
                      <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                    </svg>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- ======================================================================
         MODAL 1: ADD / EDIT PROPERTY MANDATE (WITH FEATURE IMAGE & GALLERY)
         ====================================================================== -->
    <div v-if="showPropModal" ref="propModalRoot" class="admin-modal-overlay" @click.self="closePropModal">
      <div class="admin-modal-card wide animate-fade-in-up" style="max-width:780px;">
        <div class="admin-modal-header">
          <div>
            <h3 class="admin-modal-title">
              {{ editingPropId ? 'Edit Property Mandate (MySQL #' + editingPropId + ')' : 'Create New Property Mandate' }}
            </h3>
            <p class="panel-sub">Configure asset specifications, pricing, legal clearance, feature cover, and media gallery</p>
          </div>
          <button class="admin-modal-close" @click="closePropModal" aria-label="Close modal">✕</button>
        </div>

        <form @submit.prevent="handleSaveProperty" style="display:flex; flex-direction:column; flex:1; overflow:hidden;">
          <div class="admin-modal-body" style="max-height:74vh; overflow-y:auto; padding:20px;">
            <!-- Row 1: Title & Category -->
            <div class="grid grid-2" style="gap:14px; margin-bottom:14px;">
              <div class="form-group">
                <label class="form-label">Property Title *</label>
                <input v-model="propForm.title" type="text" required placeholder="e.g. 10 Katha Corner Plot at Purbachal Sector 17" class="form-input" />
              </div>
              <div class="form-group">
                <label class="form-label">Category *</label>
                <select v-model="propForm.propertyType" class="form-select">
                  <option value="Land Share">Land Share (Co-Ownership Project)</option>
                  <option value="Flat">Flat / Luxury Apartment</option>
                  <option value="Plot">Residential Plot (Katha)</option>
                  <option value="Land">Freehold Land (Bigha)</option>
                  <option value="Hotel">Hotel & Beach Resort</option>
                  <option value="Duplex">Duplex & Penthouse</option>
                  <option value="Commercial">Commercial / Corporate Office</option>
                </select>
              </div>
            </div>

            <!-- Row 2: Location & Price -->
            <div class="grid grid-3" style="gap:14px; margin-bottom:14px;">
              <div class="form-group">
                <label class="form-label">Division / Region *</label>
                <select v-model="propForm.state" class="form-select">
                  <option value="Dhaka North">Dhaka North</option>
                  <option value="Dhaka South">Dhaka South</option>
                  <option value="Chittagong">Chittagong & Cox's Bazar</option>
                  <option value="Sylhet">Sylhet</option>
                </select>
              </div>
              <div class="form-group">
                <label class="form-label">Area Name / Hub *</label>
                <input v-model="propForm.areaName" type="text" required placeholder="e.g. Gulshan-2, Purbachal" class="form-input" />
              </div>
              <div class="form-group">
                <label class="form-label">Asking Price (BDT Taka) *</label>
                <input v-model.number="propForm.price" type="number" required placeholder="35000000" class="form-input" />
                <div v-if="propForm.price" style="font-size:0.75rem; color:#10B981; margin-top:3px; font-weight:700;">
                  Formatted: {{ formatBDT(propForm.price) }}
                </div>
              </div>
            </div>

            <!-- Row 3: Dimensions & Specs -->
            <div class="grid grid-4" style="gap:12px; margin-bottom:14px;">
              <div class="form-group">
                <label class="form-label">Sq. Footage</label>
                <input v-model.number="propForm.squareFootage" type="number" placeholder="2400" class="form-input" />
              </div>
              <div class="form-group">
                <label class="form-label">Land Size</label>
                <input v-model.number="propForm.landSize" type="number" step="0.5" placeholder="5" class="form-input" />
              </div>
              <div class="form-group">
                <label class="form-label">Land Unit</label>
                <select v-model="propForm.landUnit" class="form-select">
                  <option value="Katha">Katha</option>
                  <option value="Bigha">Bigha</option>
                  <option value="Shotok">Shotok / Decimal</option>
                  <option value="Sqft">Sq. Ft.</option>
                </select>
              </div>
              <div class="form-group">
                <label class="form-label">Beds / Baths</label>
                <div class="flex gap-2">
                  <input v-model.number="propForm.bedrooms" type="number" placeholder="Beds" class="form-input" />
                  <input v-model.number="propForm.bathrooms" type="number" placeholder="Baths" class="form-input" />
                </div>
              </div>
            </div>

            <!-- Row 4: Street Address -->
            <div class="grid grid-1" style="gap:14px; margin-bottom:14px;">
              <div class="form-group">
                <label class="form-label">Street Address & Landmark</label>
                <input v-model="propForm.address" type="text" placeholder="Road, Block, Sector, Landmark" class="form-input" />
              </div>
            </div>

            <!-- Row 5: Tagline & Description -->
            <div class="grid grid-1" style="gap:14px; margin-bottom:16px;">
              <div class="form-group">
                <label class="form-label">Tagline / Key Selling Point</label>
                <input v-model="propForm.tagline" type="text" placeholder="e.g. Panoramic Lakefront Skyline View with Private Terrace" class="form-input" />
              </div>
              <div class="form-group">
                <label class="form-label">Comprehensive Description</label>
                <textarea v-model="propForm.description" rows="2" placeholder="Provide full details regarding the property layout, accessibility, and legal documentation..." class="form-input" style="resize:vertical;"></textarea>
              </div>
            </div>

            <!-- ========================================================== -->
            <!-- MEDIA & VISUAL ASSETS: FEATURE IMAGE & GALLERY MANAGER -->
            <!-- ========================================================== -->
            <div class="media-manager-card">
              <div class="flex items-center justify-between flex-wrap gap-2" style="margin-bottom:12px;">
                <div>
                  <h4 style="font-size:0.95rem; font-weight:800; color:var(--admin-text-primary); display:flex; align-items:center; gap:8px;">
                    <span>📸 Media Assets & Visual Showcase</span>
                    <span class="badge" style="background:rgba(212,175,55,0.15); color:var(--color-gold); font-size:0.75rem;">
                      Total: {{ (propForm.featureImage ? 1 : 0) + propForm.gallery.length }} Photos
                    </span>
                  </h4>
                  <p style="font-size:0.78rem; color:var(--admin-text-muted); margin-top:2px;">
                    Set your Primary Cover / Featured Image and upload or link multiple HD Gallery Photos for client showcase.
                  </p>
                </div>
                <div class="flex items-center gap-2">
                  <button 
                    type="button" 
                    class="btn btn-sm btn-outline-white" 
                    style="font-size:0.75rem; padding:4px 8px;"
                    @click="applySampleGalleryPack"
                    title="Insert 3 sample luxury architectural photos"
                  >
                    + Sample Gallery Pack
                  </button>
                </div>
              </div>

              <!-- PART A: PRIMARY FEATURED COVER IMAGE -->
              <div style="background:var(--admin-bg-surface); padding:14px; border-radius:var(--radius-md); border:1px solid var(--admin-border-subtle); margin-bottom:14px;">
                <label class="form-label" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
                  <span style="font-weight:700; color:#10B981; display:flex; align-items:center; gap:6px;">
                    <span>★ Primary Featured Cover Image</span>
                    <span style="font-size:0.75rem; color:var(--admin-text-muted); font-weight:normal;">(Appears on Homepage, Listing Cards & Detail Header)</span>
                  </span>
                  <button 
                    type="button" 
                    class="btn btn-sm btn-outline-white" 
                    style="font-size:0.72rem; padding:3px 8px;"
                    @click="applySampleCover"
                  >
                    ✨ Random Cover Preset
                  </button>
                </label>

                <div class="grid grid-2" style="gap:14px; align-items:center;">
                  <!-- Live Preview Box -->
                  <div class="feature-img-preview-box">
                    <img 
                      :src="propForm.featureImage || 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?q=80&w=1600&auto=format&fit=crop'" 
                      alt="Featured Cover Preview" 
                    />
                    <div class="feature-img-badge">
                      <span>★ Featured Cover Photo</span>
                    </div>
                  </div>

                  <!-- URL Input & Upload Controls -->
                  <div class="flex flex-col gap-3">
                    <div>
                      <label style="font-size:0.8rem; color:var(--admin-text-muted); display:block; margin-bottom:4px;">Feature Image URL</label>
                      <input 
                        v-model="propForm.featureImage" 
                        type="url" 
                        placeholder="https://images.unsplash.com/..." 
                        class="form-input" 
                        style="width:100%; font-size:0.85rem;" 
                      />
                    </div>

                    <!-- Hidden File Input for Feature Image -->
                    <input 
                      ref="featureFileInput" 
                      type="file" 
                      accept="image/*" 
                      style="display:none;" 
                      @change="onFeatureFileSelected" 
                    />

                    <div class="flex items-center gap-2">
                      <button 
                        type="button" 
                        class="btn btn-sm btn-emerald flex-1" 
                        :disabled="isUploadingFeature" 
                        @click="triggerFeatureUpload"
                        style="font-size:0.82rem; justify-content:center;"
                      >
                        <span v-if="isUploadingFeature" class="animate-spin" style="display:inline-block; margin-right:4px;">◌</span>
                        <span>{{ isUploadingFeature ? 'Uploading Cover...' : '📁 Upload Cover File' }}</span>
                      </button>

                      <button 
                        v-if="propForm.featureImage"
                        type="button" 
                        class="btn btn-sm btn-outline-white" 
                        @click="propForm.featureImage = ''" 
                        title="Clear cover image"
                        style="font-size:0.8rem; padding:6px 10px;"
                      >
                        Clear
                      </button>
                    </div>
                    <p style="font-size:0.75rem; color:var(--admin-text-muted);">
                      Supports direct web image URLs or local files (.jpg, .png, .webp).
                    </p>
                  </div>
                </div>
              </div>

              <!-- PART B: PROPERTY GALLERY IMAGES -->
              <div style="background:var(--admin-bg-surface); padding:14px; border-radius:var(--radius-md); border:1px solid var(--admin-border-subtle);">
                <div class="flex items-center justify-between flex-wrap gap-2" style="margin-bottom:10px;">
                  <label class="form-label" style="font-weight:700; color:var(--color-gold); margin-bottom:0; display:flex; align-items:center; gap:6px;">
                    <span>🖼 Photo Gallery ({{ propForm.gallery.length }} Additional Shots)</span>
                    <span style="font-size:0.75rem; color:var(--admin-text-muted); font-weight:normal;">(Interior, Bedroom, Kitchen, Master Plan, Aerial)</span>
                  </label>

                  <!-- Hidden Multi-File Input for Gallery -->
                  <input 
                    ref="galleryFileInput" 
                    type="file" 
                    multiple 
                    accept="image/*" 
                    style="display:none;" 
                    @change="onGalleryFilesSelected" 
                  />

                  <button 
                    type="button" 
                    class="btn btn-sm btn-outline-white" 
                    :disabled="isUploadingGallery"
                    @click="triggerGalleryUpload"
                    style="font-size:0.78rem; padding:4px 10px; display:inline-flex; align-items:center; gap:6px;"
                  >
                    <span v-if="isUploadingGallery" class="animate-spin">◌</span>
                    <span>{{ isUploadingGallery ? 'Uploading Photos...' : '📁 Upload Gallery Photos (Multi)' }}</span>
                  </button>
                </div>

                <!-- Add Image by URL Bar -->
                <div class="flex items-center gap-2" style="margin-bottom:12px;">
                  <input 
                    v-model="newGalleryUrl" 
                    type="url" 
                    placeholder="Paste gallery image URL (e.g. https://...)" 
                    class="form-input" 
                    style="flex:1; font-size:0.85rem;" 
                    @keydown.enter.prevent="addGalleryUrl"
                  />
                  <button 
                    type="button" 
                    class="btn btn-sm btn-emerald" 
                    @click="addGalleryUrl"
                    style="font-size:0.82rem; white-space:nowrap;"
                  >
                    + Add to Gallery
                  </button>
                </div>

                <!-- Gallery Thumbnails Grid -->
                <div v-if="propForm.gallery.length > 0" class="gallery-grid">
                  <div 
                    v-for="(imgUrl, idx) in propForm.gallery" 
                    :key="idx" 
                    class="gallery-item-card"
                  >
                    <img :src="imgUrl" :alt="'Gallery photo ' + (idx + 1)" loading="lazy" />
                    <div class="gallery-item-overlay">
                      <div class="flex justify-between items-center">
                        <span style="font-size:0.68rem; color:#FFF; font-weight:700; background:rgba(0,0,0,0.6); padding:2px 5px; border-radius:3px;">
                          #{{ idx + 1 }}
                        </span>
                        <button 
                          type="button" 
                          class="gallery-btn-action danger" 
                          @click="removeGalleryItem(idx)" 
                          title="Remove from gallery"
                        >
                          ✕
                        </button>
                      </div>

                      <div class="flex justify-center" style="margin-top:auto;">
                        <button 
                          type="button" 
                          class="gallery-btn-action star" 
                          @click="makeCover(idx)" 
                          title="Set this image as primary featured cover"
                          style="width:100%; text-align:center; font-weight:700;"
                        >
                          ★ Make Cover
                        </button>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Empty Gallery State -->
                <div v-else style="padding:20px; text-align:center; background:var(--admin-bg-surface-secondary); border-radius:var(--radius-sm); border:1px dashed var(--admin-border-subtle);">
                  <p style="font-size:0.82rem; color:var(--admin-text-muted); margin:0;">
                    No additional gallery photos added yet. Use the upload button or URL bar above to enrich this mandate.
                  </p>
                </div>
              </div>

              <!-- PART C: OFFICIAL PROJECT BROCHURE (PDF / DOC) -->
              <div style="background:var(--admin-bg-surface); padding:14px; border-radius:var(--radius-md); border:1px solid var(--admin-border-subtle); margin-top:14px;">
                <div class="flex items-center justify-between flex-wrap gap-2" style="margin-bottom:8px;">
                  <label class="form-label" style="font-weight:700; color:#38BDF8; margin-bottom:0; display:flex; align-items:center; gap:6px;">
                    <span>📑 Official Project Brochure (PDF / DOC)</span>
                    <span style="font-size:0.75rem; color:var(--admin-text-muted); font-weight:normal;">(Downloadable on Public Listing & Detail Page)</span>
                  </label>

                  <!-- Hidden File Input for Brochure -->
                  <input 
                    ref="brochureFileInput" 
                    type="file" 
                    accept=".pdf,.doc,.docx,application/pdf" 
                    style="display:none;" 
                    @change="onBrochureFileSelected" 
                  />

                  <button 
                    type="button" 
                    class="btn btn-sm btn-outline-white" 
                    :disabled="isUploadingBrochure"
                    @click="triggerBrochureUpload"
                    style="font-size:0.78rem; padding:4px 10px; display:inline-flex; align-items:center; gap:6px;"
                  >
                    <span v-if="isUploadingBrochure" class="animate-spin">◌</span>
                    <span>{{ isUploadingBrochure ? 'Uploading PDF...' : '📁 Upload Brochure File' }}</span>
                  </button>
                </div>

                <div v-if="propForm.brochureUrl" class="flex items-center justify-between gap-3" style="background:rgba(56, 189, 248, 0.08); border:1px solid rgba(56, 189, 248, 0.3); border-radius:var(--radius-sm); padding:10px 14px; margin-bottom:8px;">
                  <div class="flex items-center gap-2" style="overflow:hidden;">
                    <span style="background:#EF4444; color:#FFF; font-size:0.7rem; font-weight:800; padding:2px 6px; border-radius:3px; flex-shrink:0;">PDF</span>
                    <a :href="propForm.brochureUrl" target="_blank" style="color:#38BDF8; font-size:0.85rem; font-weight:600; text-decoration:underline; text-overflow:ellipsis; overflow:hidden; white-space:nowrap;">
                      {{ propForm.brochureUrl.split('/').pop() || 'Attached Project Brochure' }}
                    </a>
                  </div>
                  <div class="flex items-center gap-2 flex-shrink-0">
                    <a :href="propForm.brochureUrl" target="_blank" class="btn btn-sm btn-outline-white" style="font-size:0.75rem; padding:3px 8px;">Preview</a>
                    <button type="button" class="btn btn-sm btn-outline-white" style="font-size:0.75rem; padding:3px 8px; color:#EF4444;" @click="propForm.brochureUrl = ''">Clear</button>
                  </div>
                </div>

                <input 
                  v-model="propForm.brochureUrl" 
                  type="url" 
                  placeholder="Or paste direct brochure URL (e.g. /storage/brochures/... or https://...)" 
                  class="form-input" 
                  style="font-size:0.82rem; width:100%;" 
                />
              </div>
            </div>

            <!-- Row 7: Flags -->
            <div class="flex items-center gap-6 flex-wrap" style="padding:12px 16px; border-radius:8px; border:1px solid var(--admin-border-subtle);">
              <label class="flex items-center gap-2" style="cursor:pointer; font-size:0.88rem;">
                <input v-model="propForm.isRajukApproved" type="checkbox" style="width:16px; height:16px; accent-color:#10B981;" />
                <span>RAJUK / CDA Approved Plan Verified</span>
              </label>
              <label class="flex items-center gap-2" style="cursor:pointer; font-size:0.88rem;">
                <input v-model="propForm.isFeatured" type="checkbox" style="width:16px; height:16px; accent-color:#D4AF37;" />
                <span>Feature on Live Homepage Showcase</span>
              </label>
            </div>
          </div>

          <div class="admin-modal-footer">
            <button type="button" class="btn btn-sm btn-outline-white" :disabled="isSaving" @click="closePropModal">Cancel</button>
            <button type="submit" class="btn btn-sm btn-emerald" :disabled="isSaving" style="display:inline-flex; align-items:center; gap:6px;">
              <span v-if="isSaving" class="animate-spin">◌</span>
              <span>{{ isSaving ? (editingPropId ? 'Updating MySQL...' : 'Creating in MySQL...') : (editingPropId ? 'Save Changes' : 'Publish Property Mandate') }}</span>
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- ======================================================================
         MODAL 2: DELETE CONFIRMATION MODAL
         ====================================================================== -->
    <div v-if="deleteModalTarget" class="admin-modal-overlay" @click.self="deleteModalTarget = null">
      <div class="admin-modal-card animate-fade-in-up" style="max-width:440px;">
        <div class="admin-modal-header" style="background:#1E1622; border-bottom:1px solid rgba(239,68,68,0.2);">
          <h3 class="admin-modal-title" style="color:#F87171; display:flex; align-items:center; gap:8px;">
            <span>⚠ Confirm Deletion from MySQL</span>
          </h3>
          <button class="admin-modal-close" @click="deleteModalTarget = null">✕</button>
        </div>
        <div class="admin-modal-body">
          <p style="color:var(--admin-text-primary); font-size:0.92rem; line-height:1.5;">
            Are you sure you want to permanently delete <strong>"{{ deleteModalTarget.title }}"</strong> (MySQL ID #{{ deleteModalTarget.id }})?
          </p>
          <p style="color:var(--admin-text-muted); font-size:0.82rem; margin-top:8px;">
            This will permanently erase the listing from the MySQL database and delist it from the live portal.
          </p>
        </div>
        <div class="admin-modal-footer">
          <button class="btn btn-sm btn-outline-white" :disabled="isDeleting" @click="deleteModalTarget = null">Cancel</button>
          <button class="btn btn-sm" :disabled="isDeleting" style="background:#EF4444; color:#FFF; display:inline-flex; align-items:center; gap:6px;" @click="executeDelete">
            <span v-if="isDeleting" class="animate-spin">◌</span>
            <span>{{ isDeleting ? 'Deleting from MySQL...' : 'Permanently Delete' }}</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, computed, onMounted } from 'vue'
import { useProperties, type PropertyItem } from '~/composables/useProperties'
import { formatBDT, formatArea } from '~/composables/useCurrency'
import { useOverlayBehavior } from '~/composables/useOverlayBehavior'
import { useToast } from '~/composables/useToast'

definePageMeta({
  layout: 'admin'
})

const { 
  properties, 
  isLoading, 
  lastSynced, 
  fetchProperties, 
  addProperty, 
  updateProperty, 
  toggleFeatureProperty, 
  toggleRajukProperty, 
  updatePropertyStatus, 
  deleteProperty,
  uploadImage,
  uploadMultipleImages,
  uploadBrochure
} = useProperties()

const toast = useToast()

const inventorySearch = ref('')
const inventoryTypeFilter = ref('')
const inventoryDivisionFilter = ref('')
const inventoryStatusFilter = ref('')

const showPropModal = ref(false)
const editingPropId = ref<number | null>(null)
const propModalRoot = ref<HTMLElement | null>(null)
const deleteModalTarget = ref<PropertyItem | null>(null)

const isSaving = ref(false)
const isDeleting = ref(false)
const isUploadingFeature = ref(false)
const isUploadingGallery = ref(false)
const isUploadingBrochure = ref(false)
const newGalleryUrl = ref('')

const featureFileInput = ref<HTMLInputElement | null>(null)
const galleryFileInput = ref<HTMLInputElement | null>(null)
const brochureFileInput = ref<HTMLInputElement | null>(null)

const togglingRajukId = ref<number | null>(null)
const togglingFeatureId = ref<number | null>(null)
const updatingStatusId = ref<number | null>(null)

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

const closePropModal = () => {
  if (isSaving.value) return
  showPropModal.value = false
  editingPropId.value = null
  newGalleryUrl.value = ''
}

useOverlayBehavior(showPropModal, closePropModal, propModalRoot)

const lastSyncedFormatted = computed(() => {
  if (!lastSynced.value) return ''
  const date = new Date(lastSynced.value)
  return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' })
})

const propForm = reactive({
  title: '',
  tagline: '',
  description: '',
  propertyType: 'Flat' as any,
  state: 'Dhaka North',
  areaName: 'Gulshan-2',
  address: 'Kemal Ataturk Avenue, Dhaka',
  price: 35000000,
  listingType: 'Sale' as const,
  bedrooms: 3,
  bathrooms: 3,
  squareFootage: 2400,
  landSize: 0,
  landUnit: 'Katha' as const,
  facing: 'South' as const,
  isRajukApproved: true,
  isFeatured: false,
  featureImage: 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?q=80&w=1600&auto=format&fit=crop',
  gallery: [] as string[],
  brochureUrl: '',
  agentId: 1
})

const filteredProperties = computed(() => {
  return properties.value.filter(p => {
    if (inventoryTypeFilter.value && p.propertyType !== inventoryTypeFilter.value) return false
    if (inventoryDivisionFilter.value && p.state !== inventoryDivisionFilter.value) return false
    if (inventoryStatusFilter.value && p.status !== inventoryStatusFilter.value) return false
    if (inventorySearch.value) {
      const q = inventorySearch.value.toLowerCase().trim()
      return (
        p.title.toLowerCase().includes(q) || 
        p.areaName.toLowerCase().includes(q) || 
        p.address.toLowerCase().includes(q) || 
        String(p.id).includes(q)
      )
    }
    return true
  })
})

const resetFilters = () => {
  inventorySearch.value = ''
  inventoryTypeFilter.value = ''
  inventoryDivisionFilter.value = ''
  inventoryStatusFilter.value = ''
}

const refreshData = async () => {
  try {
    await fetchProperties(true)
    toast.success('MySQL Synced', `Updated catalog (${properties.value.length} properties loaded).`)
  } catch (err: any) {
    toast.error('Sync Error', err.message || 'Unable to refresh from MySQL.')
  }
}

onMounted(async () => {
  await fetchProperties()
})

const openAddPropertyModal = () => {
  editingPropId.value = null
  propForm.title = ''
  propForm.tagline = ''
  propForm.description = ''
  propForm.propertyType = 'Flat'
  propForm.state = 'Dhaka North'
  propForm.areaName = ''
  propForm.address = ''
  propForm.price = 35000000
  propForm.bedrooms = 3
  propForm.bathrooms = 3
  propForm.squareFootage = 2400
  propForm.landSize = 0
  propForm.isRajukApproved = true
  propForm.isFeatured = false
  propForm.featureImage = 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?q=80&w=1600&auto=format&fit=crop'
  propForm.gallery = [
    'https://images.unsplash.com/photo-1600566753376-12c8ab7fb75b?q=80&w=1200&auto=format&fit=crop',
    'https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?q=80&w=1200&auto=format&fit=crop'
  ]
  propForm.brochureUrl = ''
  newGalleryUrl.value = ''
  showPropModal.value = true
}

const openEditPropertyModal = (p: PropertyItem) => {
  editingPropId.value = p.id
  propForm.title = p.title
  propForm.tagline = p.tagline || ''
  propForm.description = p.description || ''
  propForm.propertyType = p.propertyType as any
  propForm.state = p.state
  propForm.areaName = p.areaName
  propForm.address = p.address
  propForm.price = p.price
  propForm.bedrooms = p.bedrooms
  propForm.bathrooms = p.bathrooms
  propForm.squareFootage = p.squareFootage || 2000
  propForm.landSize = p.landSize || 0
  propForm.landUnit = p.landUnit || 'Katha'
  propForm.isRajukApproved = p.isRajukApproved
  propForm.isFeatured = p.isFeatured

  // Map Feature Image and Gallery
  propForm.featureImage = p.featureImage || p.images?.[0] || 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?q=80&w=1600&auto=format&fit=crop'
  propForm.gallery = p.gallery && p.gallery.length > 0 
    ? [...p.gallery] 
    : (p.images && p.images.length > 1 ? p.images.slice(1) : [])
  propForm.brochureUrl = p.brochureUrl || ''
  newGalleryUrl.value = ''
  showPropModal.value = true
}

// Media Action Helpers
const triggerFeatureUpload = () => {
  featureFileInput.value?.click()
}

const triggerGalleryUpload = () => {
  galleryFileInput.value?.click()
}

const onFeatureFileSelected = async (e: Event) => {
  const target = e.target as HTMLInputElement
  if (!target.files || target.files.length === 0) return
  const file = target.files[0]
  isUploadingFeature.value = true
  try {
    const uploadedUrl = await uploadImage(file)
    propForm.featureImage = uploadedUrl
    toast.success('Cover Uploaded', 'Feature cover photo uploaded successfully.')
  } catch (err: any) {
    toast.error('Upload Failed', err.message || 'Could not upload cover image.')
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
    const uploadedUrls = await uploadMultipleImages(target.files)
    propForm.gallery.push(...uploadedUrls)
    toast.success('Gallery Updated', `${uploadedUrls.length} photo(s) added to gallery.`)
  } catch (err: any) {
    toast.error('Upload Failed', err.message || 'Could not upload gallery images.')
  } finally {
    isUploadingGallery.value = false
    target.value = ''
  }
}

const triggerBrochureUpload = () => {
  brochureFileInput.value?.click()
}

const onBrochureFileSelected = async (e: Event) => {
  const target = e.target as HTMLInputElement
  if (!target.files || target.files.length === 0) return
  isUploadingBrochure.value = true
  try {
    const uploadedUrl = await uploadBrochure(target.files[0])
    propForm.brochureUrl = uploadedUrl
    toast.success('Brochure Uploaded', 'Project brochure file saved successfully.')
  } catch (err: any) {
    toast.error('Upload Failed', err.message || 'Could not upload brochure file.')
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
    toast.info('Gallery Photo Added', 'New image appended to gallery.')
  }
  newGalleryUrl.value = ''
}

const removeGalleryItem = (index: number) => {
  propForm.gallery.splice(index, 1)
}

const makeCover = (index: number) => {
  const selectedGalleryImg = propForm.gallery[index]
  const currentCover = propForm.featureImage
  propForm.featureImage = selectedGalleryImg
  if (currentCover && currentCover !== selectedGalleryImg) {
    propForm.gallery[index] = currentCover
  } else {
    propForm.gallery.splice(index, 1)
  }
  toast.success('Cover Changed', 'Selected photo is now the primary featured cover.')
}

const applySampleCover = () => {
  const random = SAMPLE_COVERS[Math.floor(Math.random() * SAMPLE_COVERS.length)]
  propForm.featureImage = random
}

const applySampleGalleryPack = () => {
  for (const sample of SAMPLE_GALLERY_PACK) {
    if (!propForm.gallery.includes(sample) && sample !== propForm.featureImage) {
      propForm.gallery.push(sample)
    }
  }
  toast.info('Sample Pack Loaded', 'Attached luxury architectural gallery photos.')
}

const handleSaveProperty = async () => {
  isSaving.value = true
  try {
    const featureCover = propForm.featureImage.trim()
    const validGallery = propForm.gallery.map(g => g.trim()).filter(Boolean)
    const allImages = [featureCover, ...validGallery].filter(Boolean)

    if (editingPropId.value) {
      await updateProperty(editingPropId.value, {
        title: propForm.title,
        tagline: propForm.tagline,
        description: propForm.description,
        propertyType: propForm.propertyType,
        state: propForm.state,
        areaName: propForm.areaName,
        address: propForm.address,
        price: propForm.price,
        bedrooms: propForm.bedrooms,
        bathrooms: propForm.bathrooms,
        squareFootage: propForm.squareFootage,
        landSize: propForm.landSize,
        landUnit: propForm.landUnit,
        isRajukApproved: propForm.isRajukApproved,
        isFeatured: propForm.isFeatured,
        featureImage: featureCover,
        gallery: validGallery,
        images: allImages,
        brochureUrl: propForm.brochureUrl.trim() || undefined
      })
      toast.success('MySQL Updated', `Successfully updated "${propForm.title}" with ${allImages.length} photo(s).`)
    } else {
      const created = await addProperty({
        ...propForm,
        featureImage: featureCover,
        gallery: validGallery,
        images: allImages,
        brochureUrl: propForm.brochureUrl.trim() || undefined
      })
      toast.success('MySQL Created', `New mandate #${created?.id || ''} saved with ${allImages.length} photo(s)!`)
    }
    showPropModal.value = false
    editingPropId.value = null
  } catch (err: any) {
    toast.error('MySQL Save Failed', err.message || 'Unable to save property mandate.')
  } finally {
    isSaving.value = false
  }
}

const promptDelete = (prop: PropertyItem) => {
  deleteModalTarget.value = prop
}

const executeDelete = async () => {
  if (!deleteModalTarget.value) return
  isDeleting.value = true
  const target = deleteModalTarget.value
  try {
    await deleteProperty(target.id)
    toast.info('MySQL Deleted', `Mandate #${target.id} "${target.title}" permanently removed.`)
    deleteModalTarget.value = null
  } catch (err: any) {
    toast.error('Delete Failed', err.message || 'Unable to delete listing from MySQL.')
  } finally {
    isDeleting.value = false
  }
}

const handleToggleRajuk = async (id: number) => {
  togglingRajukId.value = id
  try {
    const newState = await toggleRajukProperty(id)
    toast.success('RAJUK Updated', `Mandate #${id} RAJUK status set to ${newState ? 'Verified Pass' : 'Pending Audit'}.`)
  } catch (err: any) {
    toast.error('Update Failed', err.message || 'Could not update RAJUK status.')
  } finally {
    togglingRajukId.value = null
  }
}

const handleToggleFeature = async (id: number) => {
  togglingFeatureId.value = id
  try {
    const newState = await toggleFeatureProperty(id)
    toast.success('Feature Toggled', `Mandate #${id} homepage showcase is now ${newState ? 'Active' : 'Disabled'}.`)
  } catch (err: any) {
    toast.error('Update Failed', err.message || 'Could not toggle featured status.')
  } finally {
    togglingFeatureId.value = null
  }
}

const handleStatusChange = async (id: number, event: Event) => {
  updatingStatusId.value = id
  const target = event.target as HTMLSelectElement
  const newStatus = target.value
  try {
    await updatePropertyStatus(id, newStatus)
    toast.success('Status Changed', `Mandate #${id} status changed to "${newStatus}" in MySQL.`)
  } catch (err: any) {
    toast.error('Update Failed', err.message || 'Could not change status.')
  } finally {
    updatingStatusId.value = null
  }
}
</script>

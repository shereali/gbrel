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
            {{ isLoading ? 'Syncing...' : '🟢 Live & Synced' }}
          </span>
        </div>
        <p class="page-subtitle">
          Manage real estate inventory, prices, legal verification, and brochures • {{ properties.length }} active properties
          <span v-if="lastSyncedFormatted" style="color:var(--admin-text-muted); margin-left:6px;">(Updated: {{ lastSyncedFormatted }})</span>
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
        <NuxtLink to="/admin/brochures" class="btn btn-outline-white" style="display:inline-flex; align-items:center; gap:6px;">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
            <polyline points="14 2 14 8 20 8"/>
            <line x1="16" y1="13" x2="8" y2="13"/>
            <line x1="16" y1="17" x2="8" y2="17"/>
          </svg>
          <span>📑 Brochure Vault</span>
        </NuxtLink>
        <button class="btn btn-emerald" @click="openAddPropertyModal">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <line x1="12" y1="5" x2="12" y2="19"/>
            <line x1="5" y1="12" x2="19" y2="12"/>
          </svg>
          <span>Add Property</span>
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
            <option value="Active">Active (Live)</option>
            <option value="Draft">Draft</option>
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
                  <p style="color:var(--admin-text-primary); font-size:0.95rem; font-weight:600;">Loading properties...</p>
                </div>
              </td>
            </tr>

            <!-- Empty State -->
            <tr v-else-if="filteredProperties.length === 0">
              <td colspan="7" style="text-align:center; padding:48px 20px;">
                <div class="flex flex-col items-center justify-center gap-2">
                  <div style="font-size:2rem;">🏛</div>
                  <p style="color:var(--admin-text-primary); font-size:1rem; font-weight:600;">No properties match your current filters</p>
                  <p style="color:var(--admin-text-muted); font-size:0.85rem;">Try broadening your search or click below to add a new property listing.</p>
                  <button class="btn btn-emerald btn-sm" style="margin-top:8px;" @click="openAddPropertyModal">
                    Add Property
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
                        ID: #{{ prop.id }} • {{ prop.areaName }}, {{ prop.city }}
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
                  :style="prop.status === 'Draft' ? 'border-color: #F59E0B; color: #FCD34D;' : (prop.status === 'Active' ? 'border-color: #10B981;' : '')"
                  title="Change property status"
                >
                  <option value="Active">Active (Live)</option>
                  <option value="Draft">Draft (Private)</option>
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
         MODAL 1: ADD / EDIT PROPERTY WITH INTERACTIVE COMPLETION GUIDE
         ====================================================================== -->
    <div v-if="showPropModal" ref="propModalRoot" class="admin-modal-overlay" @click.self="closePropModal">
      <div class="admin-modal-card wide animate-fade-in-up" style="max-width:1040px;">
        <div class="admin-modal-header">
          <div class="flex items-center gap-3 flex-wrap">
            <div>
              <h3 class="admin-modal-title">
                {{ editingPropId ? 'Edit Property (#' + editingPropId + ')' : 'Add New Property' }}
              </h3>
              <p class="panel-sub">Configure specifications, media, and follow the step-by-step guideline</p>
            </div>
            <span 
              class="badge-admin" 
              :class="propForm.status === 'Active' ? 'active' : (propForm.status === 'Draft' ? 'pending' : 'neutral')"
              style="font-size:0.75rem;"
            >
              <span style="font-size:0.7rem;">●</span> {{ propForm.status || 'Draft' }} Mode
            </span>
          </div>
          <div class="flex items-center gap-2">
            <button 
              type="button" 
              class="btn btn-sm" 
              :class="showGuideSidebar ? 'btn-gold' : 'btn-outline-white'" 
              @click="showGuideSidebar = !showGuideSidebar"
              style="font-size:0.78rem; padding:4px 10px;"
              title="Toggle Property Checklist Guide"
            >
              📋 {{ showGuideSidebar ? 'Hide Guide' : 'Show Guide' }} ({{ completedStepsCount }}/{{ totalStepsCount }})
            </button>
            <button class="admin-modal-close" @click="closePropModal" aria-label="Close modal">✕</button>
          </div>
        </div>

        <form @submit.prevent="handleSaveProperty('Active')" style="display:flex; flex-direction:column; flex:1; overflow:hidden;">
          <div class="admin-modal-body" style="max-height:74vh; overflow-y:auto; padding:20px;">
            <div class="property-modal-flex-layout">
              
              <!-- LEFT COLUMN: PROPERTY FORM SECTIONS -->
              <div class="property-modal-form-col">
                
                <!-- Section 1: Basic Details & Category -->
                <div id="sec-basic" class="property-form-section">
                  <div class="flex items-center justify-between mb-3">
                    <h4 style="font-size:0.92rem; font-weight:700; color:var(--color-gold); margin:0; display:flex; align-items:center; gap:6px;">
                      <span>1. Basic Details & Category</span>
                    </h4>
                    <span v-if="propForm.title && propForm.propertyType" style="color:#10B981; font-size:0.78rem; font-weight:700;">✔ Completed</span>
                  </div>

                  <div class="grid grid-2" style="gap:14px; margin-bottom:12px;">
                    <div class="form-group">
                      <label class="form-label">Property Title</label>
                      <input v-model="propForm.title" type="text" placeholder="e.g. 10 Katha Corner Plot at Purbachal Sector 17" class="form-input" />
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

                  <div class="grid grid-2" style="gap:14px;">
                    <div class="form-group">
                      <label class="form-label">Transaction Type</label>
                      <select v-model="propForm.listingType" class="form-select">
                        <option value="Sale">For Sale</option>
                        <option value="Lease">For Lease / Rent</option>
                      </select>
                    </div>
                    <div class="form-group">
                      <label class="form-label">Property Lifecycle Status</label>
                      <select v-model="propForm.status" class="form-select">
                        <option value="Draft">Draft (Save privately, not live)</option>
                        <option value="Active">Active (Publish live to website)</option>
                        <option value="Under Offer">Under Offer</option>
                        <option value="Sold">Sold</option>
                        <option value="Delisted">Delisted</option>
                      </select>
                    </div>
                  </div>
                </div>

                <!-- Section 2: Location & Region -->
                <div id="sec-location" class="property-form-section">
                  <div class="flex items-center justify-between mb-3">
                    <h4 style="font-size:0.92rem; font-weight:700; color:var(--color-gold); margin:0; display:flex; align-items:center; gap:6px;">
                      <span>2. Location & Area</span>
                    </h4>
                    <span v-if="propForm.state && propForm.areaName" style="color:#10B981; font-size:0.78rem; font-weight:700;">✔ Completed</span>
                  </div>

                  <div class="grid grid-2" style="gap:14px; margin-bottom:12px;">
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
                      <input v-model="propForm.areaName" type="text" required placeholder="e.g. Gulshan-2, Banani, Purbachal" class="form-input" />
                    </div>
                  </div>

                  <div class="form-group">
                    <label class="form-label">Street Address & Landmark</label>
                    <input v-model="propForm.address" type="text" placeholder="Road, Block, Sector, Landmark" class="form-input" />
                  </div>
                </div>

                <!-- Section 3: Pricing & Valuation -->
                <div id="sec-pricing" class="property-form-section">
                  <div class="flex items-center justify-between mb-3">
                    <h4 style="font-size:0.92rem; font-weight:700; color:var(--color-gold); margin:0; display:flex; align-items:center; gap:6px;">
                      <span>3. Pricing & Valuation</span>
                    </h4>
                    <span v-if="propForm.price > 0 || propForm.hidePrice" style="color:#10B981; font-size:0.78rem; font-weight:700;">✔ Completed</span>
                  </div>

                  <div class="grid grid-2" style="gap:14px;">
                    <div class="form-group">
                      <label class="form-label">Asking Price (BDT Taka) *</label>
                      <input v-model.number="propForm.price" type="number" placeholder="35000000" class="form-input" />
                      <div v-if="propForm.price" style="font-size:0.75rem; color:#10B981; margin-top:3px; font-weight:700;">
                        Preview: {{ formatBDT(propForm.price) }}
                      </div>
                    </div>
                    <div class="form-group">
                      <label class="form-label">Price Visibility Option</label>
                      <div style="background:rgba(255,255,255,0.03); padding:8px 10px; border-radius:6px; border:1px solid var(--admin-border-subtle); margin-top:2px;">
                        <label class="flex items-center gap-2" style="cursor:pointer; font-size:0.82rem; color:#FFF;">
                          <input v-model="propForm.hidePrice" type="checkbox" style="width:15px; height:15px; accent-color:var(--color-gold);" />
                          <span>🔐 Hide Price (Display Price on Application / POA)</span>
                        </label>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Section 4: Dimensions & Specifications -->
                <div id="sec-specs" class="property-form-section">
                  <div class="flex items-center justify-between mb-3">
                    <h4 style="font-size:0.92rem; font-weight:700; color:var(--color-gold); margin:0; display:flex; align-items:center; gap:6px;">
                      <span>4. Dimensions & Specifications</span>
                    </h4>
                    <span v-if="propForm.squareFootage > 0 || propForm.landSize > 0" style="color:#10B981; font-size:0.78rem; font-weight:700;">✔ Completed</span>
                  </div>

                  <div class="grid grid-4" style="gap:12px;">
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
                </div>

                <!-- Section 5: Highlights & Overview -->
                <div id="sec-details" class="property-form-section">
                  <div class="flex items-center justify-between mb-3">
                    <h4 style="font-size:0.92rem; font-weight:700; color:var(--color-gold); margin:0; display:flex; align-items:center; gap:6px;">
                      <span>5. Highlights & Description</span>
                    </h4>
                    <span v-if="propForm.tagline || propForm.description" style="color:#10B981; font-size:0.78rem; font-weight:700;">✔ Completed</span>
                  </div>

                  <div class="form-group" style="margin-bottom:12px;">
                    <label class="form-label">Tagline / Key Selling Point</label>
                    <input v-model="propForm.tagline" type="text" placeholder="e.g. Panoramic Lakefront Skyline View with Private Terrace" class="form-input" />
                  </div>
                  <div class="form-group">
                    <label class="form-label">Comprehensive Description</label>
                    <textarea v-model="propForm.description" rows="2" placeholder="Provide full details regarding property layout, access, and neighborhood highlights..." class="form-input" style="resize:vertical;"></textarea>
                  </div>
                </div>

                <!-- Section 6: Media & Visual Assets -->
                <div id="sec-media" class="property-form-section">
                  <div class="flex items-center justify-between mb-3">
                    <h4 style="font-size:0.92rem; font-weight:700; color:var(--color-gold); margin:0; display:flex; align-items:center; gap:6px;">
                      <span>6. Photos & Media Showcase</span>
                      <span class="badge" style="background:rgba(212,175,55,0.15); color:var(--color-gold); font-size:0.75rem;">
                        {{ (propForm.featureImage ? 1 : 0) + propForm.gallery.length }} Photos
                      </span>
                    </h4>
                    <span v-if="propForm.featureImage" style="color:#10B981; font-size:0.78rem; font-weight:700;">✔ Cover Set</span>
                  </div>

                  <!-- Primary Featured Cover Image -->
                  <div style="background:rgba(255,255,255,0.02); padding:14px; border-radius:var(--radius-md); border:1px solid var(--admin-border-subtle); margin-bottom:14px;">
                    <label class="form-label" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
                      <span style="font-weight:700; color:#10B981;">★ Primary Featured Cover Image</span>
                      <button type="button" class="btn btn-sm btn-outline-white" style="font-size:0.72rem; padding:3px 8px;" @click="applySampleCover">
                        ✨ Random Cover Preset
                      </button>
                    </label>

                    <div class="grid grid-2" style="gap:14px; align-items:center;">
                      <div class="feature-img-preview-box">
                        <img :src="propForm.featureImage || 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?q=80&w=1600&auto=format&fit=crop'" alt="Cover Preview" />
                        <div class="feature-img-badge">
                          <span>★ Featured Cover Photo</span>
                        </div>
                      </div>

                      <div class="flex flex-col gap-2">
                        <input v-model="propForm.featureImage" type="url" placeholder="Paste image URL (https://...)" class="form-input" style="width:100%; font-size:0.85rem;" />
                        <input ref="featureFileInput" type="file" accept="image/*" style="display:none;" @change="onFeatureFileSelected" />
                        <div class="flex items-center gap-2">
                          <button type="button" class="btn btn-sm btn-emerald flex-1" :disabled="isUploadingFeature" @click="triggerFeatureUpload" style="font-size:0.8rem;">
                            <span v-if="isUploadingFeature" class="animate-spin mr-1">◌</span>
                            <span>{{ isUploadingFeature ? 'Uploading...' : '📁 Upload Cover File' }}</span>
                          </button>
                          <button v-if="propForm.featureImage" type="button" class="btn btn-sm btn-outline-white" @click="propForm.featureImage = ''" style="font-size:0.8rem; padding:6px 10px;">
                            Clear
                          </button>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- Gallery Images -->
                  <div style="background:rgba(255,255,255,0.02); padding:14px; border-radius:var(--radius-md); border:1px solid var(--admin-border-subtle);">
                    <div class="flex items-center justify-between flex-wrap gap-2" style="margin-bottom:10px;">
                      <label class="form-label" style="font-weight:700; color:var(--color-gold); margin-bottom:0;">
                        🖼 Gallery Photos ({{ propForm.gallery.length }} shots)
                      </label>
                      <input ref="galleryFileInput" type="file" multiple accept="image/*" style="display:none;" @change="onGalleryFilesSelected" />
                      <div class="flex items-center gap-2">
                        <button type="button" class="btn btn-sm btn-outline-white" style="font-size:0.75rem; padding:3px 8px;" @click="applySampleGalleryPack">
                          Sample Pack
                        </button>
                        <button type="button" class="btn btn-sm btn-emerald" :disabled="isUploadingGallery" @click="triggerGalleryUpload" style="font-size:0.75rem; padding:3px 8px;">
                          <span v-if="isUploadingGallery" class="animate-spin mr-1">◌</span>
                          <span>Upload Photos</span>
                        </button>
                      </div>
                    </div>

                    <div class="flex gap-2" style="margin-bottom:10px;">
                      <input v-model="newGalleryUrl" type="url" placeholder="Paste gallery image URL..." class="form-input" style="flex:1; font-size:0.82rem;" @keydown.enter.prevent="addGalleryUrl" />
                      <button type="button" class="btn btn-sm btn-outline-white" @click="addGalleryUrl" style="font-size:0.8rem;">Add</button>
                    </div>

                    <div v-if="propForm.gallery.length > 0" class="gallery-grid">
                      <div v-for="(imgUrl, idx) in propForm.gallery" :key="idx" class="gallery-item-card">
                        <img :src="imgUrl" :alt="'Gallery ' + (idx + 1)" loading="lazy" />
                        <div class="gallery-item-overlay">
                          <div class="flex justify-between items-center">
                            <span style="font-size:0.68rem; color:#FFF; font-weight:700; background:rgba(0,0,0,0.6); padding:2px 5px; border-radius:3px;">#{{ idx + 1 }}</span>
                            <button type="button" class="gallery-btn-action danger" @click="removeGalleryItem(idx)">✕</button>
                          </div>
                          <button type="button" class="gallery-btn-action star" @click="makeCover(idx)">★ Set as Cover</button>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Section 7: Project Brochure -->
                <div id="sec-brochure" class="property-form-section">
                  <div class="flex items-center justify-between mb-3">
                    <h4 style="font-size:0.92rem; font-weight:700; color:var(--color-gold); margin:0; display:flex; align-items:center; gap:6px;">
                      <span>7. Project Brochure & Documents</span>
                    </h4>
                    <span v-if="propForm.brochureUrl" style="color:#10B981; font-size:0.78rem; font-weight:700;">✔ Attached</span>
                  </div>

                  <input ref="brochureFileInput" type="file" accept=".pdf,.doc,.docx" style="display:none;" @change="onBrochureFileSelected" />
                  <div class="flex items-center gap-2 mb-2">
                    <button type="button" class="btn btn-sm btn-emerald" :disabled="isUploadingBrochure" @click="triggerBrochureUpload" style="font-size:0.8rem;">
                      <span v-if="isUploadingBrochure" class="animate-spin mr-1">◌</span>
                      <span>{{ isUploadingBrochure ? 'Uploading...' : 'Upload PDF Brochure' }}</span>
                    </button>
                    <input v-model="propForm.brochureUrl" type="url" placeholder="Or paste direct brochure URL (e.g. /storage/... or https://...)" class="form-input" style="flex:1; font-size:0.82rem;" />
                    <button v-if="propForm.brochureUrl" type="button" class="btn btn-sm btn-outline-white" @click="propForm.brochureUrl = ''" style="font-size:0.75rem;">Clear</button>
                  </div>
                </div>

                <!-- Section 8: Legal Clearances -->
                <div id="sec-legal" class="property-form-section">
                  <div class="flex items-center justify-between mb-3">
                    <h4 style="font-size:0.92rem; font-weight:700; color:var(--color-gold); margin:0; display:flex; align-items:center; gap:6px;">
                      <span>8. Legal & Verification Badges</span>
                    </h4>
                    <span v-if="propForm.isRajukApproved" style="color:#10B981; font-size:0.78rem; font-weight:700;">✔ Verified</span>
                  </div>

                  <div class="flex items-center gap-6 flex-wrap">
                    <label class="flex items-center gap-2" style="cursor:pointer; font-size:0.85rem;">
                      <input v-model="propForm.isRajukApproved" type="checkbox" style="width:16px; height:16px; accent-color:#10B981;" />
                      <span>RAJUK / CDA Approved Plan Verified</span>
                    </label>
                    <label class="flex items-center gap-2" style="cursor:pointer; font-size:0.85rem;">
                      <input v-model="propForm.isFeatured" type="checkbox" style="width:16px; height:16px; accent-color:#D4AF37;" />
                      <span>Feature on Live Homepage Showcase</span>
                    </label>
                  </div>
                </div>

                <!-- Section 9: Privacy Controls -->
                <div id="sec-privacy" class="property-form-section" style="border-color:rgba(212,175,55,0.25);">
                  <div class="flex items-center justify-between mb-3">
                    <h4 style="font-size:0.92rem; font-weight:700; color:var(--color-gold); margin:0; display:flex; align-items:center; gap:6px;">
                      <span>9. Privacy & Confidentiality Controls</span>
                    </h4>
                    <span style="font-size:0.75rem; color:var(--admin-text-muted);">Confidential client protections</span>
                  </div>

                  <div class="grid grid-2" style="gap:10px;">
                    <label class="flex items-center gap-2" style="cursor:pointer; font-size:0.82rem; color:#FFF;">
                      <input v-model="propForm.hideAgentPhoto" type="checkbox" style="width:15px; height:15px; accent-color:#D4AF37;" />
                      <span>🛡️ Hide Advisor Photo (Show Official Crest)</span>
                    </label>
                    <label class="flex items-center gap-2" style="cursor:pointer; font-size:0.82rem; color:#FFF;">
                      <input v-model="propForm.hideExactAddress" type="checkbox" style="width:15px; height:15px; accent-color:#38BDF8;" />
                      <span>📍 Hide Exact Street / Plot Address</span>
                    </label>
                    <label class="flex items-center gap-2" style="cursor:pointer; font-size:0.82rem; color:#FFF;">
                      <input v-model="propForm.hideFloorPlan" type="checkbox" style="width:15px; height:15px; accent-color:#A855F7;" />
                      <span>📐 Gate Floor Plans (NDA Required)</span>
                    </label>
                    <label class="flex items-center gap-2" style="cursor:pointer; font-size:0.82rem; color:#FFF;">
                      <input v-model="propForm.hideAgentContact" type="checkbox" style="width:15px; height:15px; accent-color:#10B981;" />
                      <span>🔒 Route Inquiries through Corporate Concierge</span>
                    </label>
                  </div>
                </div>

              </div>

              <!-- RIGHT COLUMN: INTERACTIVE GUIDELINE & CHECKLIST NOTE -->
              <aside v-if="showGuideSidebar" class="property-guide-sidebar">
                <div class="flex items-center justify-between">
                  <div style="font-size:0.88rem; font-weight:800; color:#FFF; display:flex; align-items:center; gap:6px;">
                    <span>📋 Property Guide</span>
                  </div>
                  <span class="badge" style="background:rgba(212,175,55,0.15); color:var(--color-gold); font-size:0.7rem; font-weight:700;">
                    {{ completedStepsCount }}/{{ totalStepsCount }} Done
                  </span>
                </div>

                <!-- Readiness Progress Bar -->
                <div>
                  <div class="flex justify-between items-center" style="font-size:0.75rem; color:var(--admin-text-muted); margin-bottom:4px;">
                    <span>Readiness Meter</span>
                    <strong :style="{ color: completionPercentage === 100 ? '#10B981' : 'var(--color-gold)' }">{{ completionPercentage }}%</strong>
                  </div>
                  <div style="background:rgba(255,255,255,0.08); height:6px; border-radius:3px; overflow:hidden;">
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

                <!-- Clickable Guideline Items -->
                <div class="space-y-1" style="max-height:360px; overflow-y:auto; padding-right:2px;">
                  <div 
                    v-for="(item, idx) in guideItems" 
                    :key="item.id"
                    class="guide-step-item"
                    :class="{ 'is-done': item.isCompleted }"
                    @click="scrollToSection(item.id)"
                    :title="'Click to jump to ' + item.title"
                  >
                    <div class="guide-check-circle" :class="item.isCompleted ? 'done' : 'pending'">
                      <span v-if="item.isCompleted">✔</span>
                      <span v-else>{{ idx + 1 }}</span>
                    </div>
                    <div style="flex:1; min-width:0;">
                      <div style="font-size:0.8rem; font-weight:700; color:#FFF; line-height:1.2;">
                        {{ item.title }}
                      </div>
                      <div style="font-size:0.72rem; color:var(--admin-text-muted); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; margin-top:2px;">
                        {{ item.sub }}
                      </div>
                    </div>
                    <span style="font-size:0.75rem; color:var(--admin-text-muted);">›</span>
                  </div>
                </div>

                <!-- Dynamic Step Tip -->
                <div style="background:rgba(212,175,55,0.08); border:1px solid rgba(212,175,55,0.2); border-radius:8px; padding:10px 12px;">
                  <div style="font-size:0.74rem; font-weight:700; color:var(--color-gold); margin-bottom:3px; display:flex; align-items:center; gap:4px;">
                    <span>💡 Recommendation:</span>
                  </div>
                  <p style="font-size:0.76rem; color:#E2E8F0; line-height:1.4; margin:0;">
                    {{ activeGuideTip }}
                  </p>
                </div>

                <!-- Quick Save Action Inside Guide -->
                <button 
                  type="button" 
                  class="btn btn-sm btn-outline-white" 
                  style="width:100%; font-size:0.8rem; padding:7px;" 
                  :disabled="isSaving" 
                  @click="handleSaveProperty('Draft')"
                >
                  <span>💾 Save Current Draft</span>
                </button>
              </aside>

            </div>
          </div>

          <div class="admin-modal-footer">
            <div class="flex items-center gap-2 mr-auto" style="font-size:0.82rem; color:var(--admin-text-muted);">
              <span>Readiness: <strong>{{ completionPercentage }}%</strong> ({{ completedStepsCount }}/{{ totalStepsCount }} completed)</span>
            </div>
            <button type="button" class="btn btn-sm btn-outline-white" :disabled="isSaving" @click="closePropModal">Cancel</button>
            <button 
              type="button" 
              class="btn btn-sm btn-outline-white" 
              :disabled="isSaving"
              @click="handleSaveProperty('Draft')"
              title="Save as Draft without publishing to public visitors"
            >
              <span>💾 Save as Draft</span>
            </button>
            <button 
              type="button" 
              class="btn btn-sm btn-emerald" 
              :disabled="isSaving" 
              @click="handleSaveProperty('Active')"
              style="display:inline-flex; align-items:center; gap:6px;"
              title="Publish property live on public website"
            >
              <span v-if="isSaving" class="animate-spin">◌</span>
              <span>{{ isSaving ? 'Saving...' : (editingPropId ? 'Save & Publish Live' : 'Publish Property') }}</span>
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

const showGuideSidebar = ref(true)
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
  status: 'Draft' as 'Active' | 'Sold' | 'Delisted' | 'Under Offer' | 'Draft',
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
  hidePrice: false,
  priceDisplayText: 'Price on Application',
  hideAgentPhoto: false,
  hideAgentContact: false,
  hideExactAddress: false,
  hideFloorPlan: false,
  hideMortgageCalculator: false,
  agentId: 1
})

// ============================================================================
// PROPERTY COMPLETION GUIDE & SECTION NAVIGATION
// ============================================================================
const guideItems = computed(() => [
  {
    id: 'sec-basic',
    title: '1. Basic Identity & Status',
    sub: propForm.title ? `${propForm.title.slice(0, 26)}... (${propForm.status})` : 'Listing title & status required',
    isCompleted: Boolean(propForm.title?.trim() && propForm.propertyType)
  },
  {
    id: 'sec-location',
    title: '2. Location & Address',
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
    title: '4. Dimensions & Specs',
    sub: (propForm.squareFootage > 0 || propForm.landSize > 0) ? `${formatArea(propForm.squareFootage, propForm.landSize, propForm.landUnit)} • ${propForm.bedrooms} Beds` : 'Sqft or Land size needed',
    isCompleted: Boolean(propForm.squareFootage > 0 || propForm.landSize > 0)
  },
  {
    id: 'sec-details',
    title: '5. Highlights & Description',
    sub: propForm.description ? 'Description provided' : (propForm.tagline ? 'Tagline entered' : 'Add selling description'),
    isCompleted: Boolean(propForm.tagline?.trim() || propForm.description?.trim())
  },
  {
    id: 'sec-media',
    title: '6. Photos & Media Showcase',
    sub: propForm.featureImage ? `${(propForm.featureImage ? 1 : 0) + propForm.gallery.length} visual asset(s)` : 'Feature cover photo required',
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
    title: '8. Legal Clearances',
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
  const el = document.getElementById(sectionId)
  if (el) {
    el.scrollIntoView({ behavior: 'smooth', block: 'center' })
    el.classList.add('section-highlight-pulse')
    setTimeout(() => {
      el.classList.remove('section-highlight-pulse')
    }, 1500)
  }
}

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
  propForm.status = 'Draft'
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
  propForm.hidePrice = false
  propForm.priceDisplayText = 'Price on Application'
  propForm.hideAgentPhoto = false
  propForm.hideAgentContact = false
  propForm.hideExactAddress = false
  propForm.hideFloorPlan = false
  propForm.hideMortgageCalculator = false
  newGalleryUrl.value = ''
  showPropModal.value = true
}

const openEditPropertyModal = (p: PropertyItem) => {
  editingPropId.value = p.id
  propForm.title = p.title
  propForm.tagline = p.tagline || ''
  propForm.description = p.description || ''
  propForm.propertyType = p.propertyType as any
  propForm.status = p.status || 'Active'
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
  propForm.hidePrice = Boolean(p.hidePrice)
  propForm.priceDisplayText = p.priceDisplayText || 'Price on Application'
  propForm.hideAgentPhoto = Boolean(p.hideAgentPhoto)
  propForm.hideAgentContact = Boolean(p.hideAgentContact)
  propForm.hideExactAddress = Boolean(p.hideExactAddress)
  propForm.hideFloorPlan = Boolean(p.hideFloorPlan)
  propForm.hideMortgageCalculator = Boolean(p.hideMortgageCalculator)
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

const handleSaveProperty = async (targetStatus?: 'Draft' | 'Active') => {
  // If targetStatus is explicitly given, override propForm.status
  if (targetStatus) {
    propForm.status = targetStatus
  }

  // Soft handling for Draft saving so users can save partial progress anytime
  if (propForm.status === 'Draft') {
    if (!propForm.title.trim()) {
      propForm.title = 'Untitled Draft Property'
    }
    if (!propForm.areaName.trim()) {
      propForm.areaName = 'Unspecified Location'
    }
    if (!propForm.address.trim()) {
      propForm.address = 'Draft Address'
    }
  } else {
    // Stricter validation for Publishing Live
    if (!propForm.title.trim()) {
      toast.warning('Title Required', 'Please provide a property title before publishing.')
      scrollToSection('sec-basic')
      return
    }
    if (!propForm.areaName.trim()) {
      toast.warning('Location Required', 'Please enter an area name before publishing.')
      scrollToSection('sec-location')
      return
    }
  }

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
        status: propForm.status,
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
        toast.success('💾 Draft Saved', `Property draft #${editingPropId.value} saved.`)
      } else {
        toast.success('Property Published', `Successfully updated and published "${propForm.title}".`)
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
        toast.success('💾 Draft Saved', `New property saved as Draft #${created?.id || ''}. You can complete it anytime.`)
      } else {
        toast.success('Property Published', `Property #${created?.id || ''} published successfully on live portal.`)
      }
    }
    showPropModal.value = false
    editingPropId.value = null
  } catch (err: any) {
    toast.error('Save Failed', err.message || 'Unable to save property.')
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
    toast.info('Property Removed', `Property #${target.id} "${target.title}" was deleted.`)
    deleteModalTarget.value = null
  } catch (err: any) {
    toast.error('Delete Failed', err.message || 'Unable to delete property.')
  } finally {
    isDeleting.value = false
  }
}

const handleToggleRajuk = async (id: number) => {
  togglingRajukId.value = id
  try {
    const newState = await toggleRajukProperty(id)
    toast.success('Verification Updated', `Property #${id} RAJUK status set to ${newState ? 'Verified Pass' : 'Pending Audit'}.`)
  } catch (err: any) {
    toast.error('Update Failed', err.message || 'Could not update verification status.')
  } finally {
    togglingRajukId.value = null
  }
}

const handleToggleFeature = async (id: number) => {
  togglingFeatureId.value = id
  try {
    const newState = await toggleFeatureProperty(id)
    toast.success('Feature Toggled', `Property #${id} homepage showcase is now ${newState ? 'Active' : 'Disabled'}.`)
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
    toast.success('Status Changed', `Property #${id} status changed to "${newStatus}".`)
  } catch (err: any) {
    toast.error('Update Failed', err.message || 'Could not change status.')
  } finally {
    updatingStatusId.value = null
  }
}
</script>

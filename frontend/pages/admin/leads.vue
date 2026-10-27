<template>
  <div ref="pageRoot" class="admin-page animate-fade-in ld">
    <div class="admin-header-row ld-header" :class="{ 'is-hidden-mobile': detailOpen }">
      <div>
        <h1 class="page-title">Buyer Inquiries & Leads</h1>
        <p class="page-subtitle ld-subtitle">Open a lead to see everything the buyer told us, then call or message them.</p>
      </div>
      <div class="admin-header-actions">
        <button
          type="button"
          class="ld-hl-btn ld-refresh-btn"
          :class="{ 'is-refreshing': isRefreshing }"
          title="Refresh leads list"
          @click="handleManualRefresh"
        >
          <RotateCcw :size="14" :class="{ 'spin-anim': isRefreshing }" aria-hidden="true" />
          <span>Refresh</span>
        </button>
        <button type="button" class="ld-hl-btn" title="Choose which form answers to highlight" @click="openHighlights()">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
          <span>Highlights</span>
        </button>
        <button class="btn btn-emerald ld-add" @click="showAddLeadModal = true">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
            <line x1="12" y1="5" x2="12" y2="19"/>
            <line x1="5" y1="12" x2="19" y2="12"/>
          </svg>
          <span>Log New Inquiry</span>
        </button>
      </div>
    </div>

    <!-- Toolbar: search, status tabs, focus and sort (inline on laptop/desktop, responsive on phones) -->
    <div class="ld-toolbar" :class="{ 'is-hidden-mobile': detailOpen }">
      <div class="ld-toolbar-row">
        <div class="ld-searchrow">
          <label class="ld-search">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="7"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input v-model.trim="search" type="search" inputmode="search" placeholder="Search name, phone or property" aria-label="Search leads" />
            <template v-if="voice.supported.value">
              <button type="button" class="ld-lang" :title="voiceLang === 'bn-BD' ? 'Listening in Bangla. Tap for English.' : 'Listening in English. Tap for Bangla.'" @click="toggleVoiceLang">{{ voiceLang === 'bn-BD' ? 'বাং' : 'EN' }}</button>
              <button type="button" class="ld-mic" :class="{ on: voice.listening.value }" :aria-pressed="voice.listening.value" :aria-label="voice.listening.value ? 'Stop voice search' : 'Search by voice'" @click="toggleVoice">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="9" y="2" width="6" height="12" rx="3"/><path d="M5 10a7 7 0 0 0 14 0"/><line x1="12" y1="17" x2="12" y2="22"/></svg>
              </button>
            </template>
          </label>
        </div>

        <!-- All select filter elements: inline on laptop/desktop, 3-col grid on mobile -->
        <div v-if="view === 'active'" class="ld-filters">
          <CrmFilterDropdown
            v-model="projectFilter"
            :options="projectDropdownOptions"
            :icon="FolderKanban"
            default-value="all"
            placeholder="All Projects"
            empty-text="No projects"
            align="left"
            aria-label="Filter by project"
          />

          <CrmFilterDropdown
            v-model="statusFilter"
            :options="statusDropdownOptions"
            :icon="CircleDot"
            default-value="All"
            placeholder="All Status"
            align="left"
            aria-label="Filter by status"
          />

          <CrmFilterDropdown
            v-if="staff.length && isLeadAdmin"
            v-model="staffFilter"
            :options="staffDropdownOptions"
            :icon="Users"
            default-value="all"
            placeholder="All Team"
            align="right"
            aria-label="Filter by assigned staff"
          />

          <CrmFilterDropdown
            v-model="quick"
            :options="quickDropdownOptions"
            :icon="Layers"
            default-value="all"
            placeholder="All leads"
            align="left"
            aria-label="Quick views"
          />

          <CrmFilterDropdown
            v-model="answerFilter"
            :options="answerDropdownOptions"
            :icon="MessageSquareQuote"
            default-value=""
            placeholder="All answers"
            empty-text="No form answers yet"
            align="left"
            aria-label="Filter by form answer"
          />

          <CrmFilterDropdown
            v-model="sortBy"
            :options="sortDropdownOptions"
            :icon="ArrowUpDown"
            default-value="newest"
            placeholder="Sort by"
            align="right"
            aria-label="Sort by"
          />

          <CrmFilterDropdown
            v-model="focus"
            :options="buyerFocusOptions"
            :icon="Target"
            default-value="All"
            placeholder="All buyers"
            align="left"
            aria-label="Show buyer type"
          />

          <button
            type="button"
            class="ld-activity-filter-btn"
            :class="{ 'is-active': quick === 'activity' }"
            title="Filter by current activity (status updates, notes, recent interactions)"
            aria-label="Filter by current activity"
            @click="toggleActivityFilter"
          >
            <Activity :size="14" aria-hidden="true" />
            <span>Current Activity</span>
            <span v-if="currentActivityCount" class="ld-act-count">{{ currentActivityCount }}</span>
          </button>

          <button
            v-if="hasFilters"
            type="button"
            class="ld-reset-btn"
            title="Reset all filters to default"
            aria-label="Reset all filters"
            @click="clearFilters"
          >
            <RotateCcw :size="13" aria-hidden="true" />
            <span>Reset</span>
          </button>
        </div>
      </div>

      <p v-if="voice.listening.value || voice.error.value" class="ld-voice-status" role="status">
        <template v-if="voice.listening.value">Listening… {{ voice.interim.value || (voiceLang === 'bn-BD' ? 'নাম বা নম্বর বলুন' : 'Say a name or number') }}</template>
        <template v-else>{{ voice.error.value }}</template>
      </p>

      <div v-if="activeStaffInfo && isLeadAdmin" class="ld-staff-banner">
        <div class="ld-staff-banner-info">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
          <span>Assigned to: <strong>{{ activeStaffInfo.name }}</strong> ({{ activeStaffInfo.role || 'Staff' }}) &middot; {{ activeStaffInfo.count }} leads</span>
        </div>
        <button type="button" class="ld-staff-banner-clear" @click="staffFilter = 'all'">Show all &times;</button>
      </div>
      <div v-else-if="view === 'trash' && isLeadAdmin" class="ld-trash-banner">
        <div class="ld-trash-banner-info">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
          <span>Showing deleted inquiries (Trash). Restore one to bring it back to active pipeline.</span>
        </div>
        <button type="button" class="ld-staff-banner-clear" @click="changeView('active')">Back to active leads &times;</button>
      </div>
    </div>

    <div v-if="isLoading" class="ld-empty" role="status">Loading leads…</div>

    <div v-else-if="filteredLeads.length === 0" class="ld-empty">
      <strong>{{ view === 'trash' ? 'Trash is empty' : (hasFilters ? 'No leads match these filters' : 'No inquiries yet') }}</strong>
      <p v-if="hasFilters">Clear the search or choose "All" to see every lead.</p>
      <p v-else-if="view === 'trash'">Deleted inquiries show up here and can be restored.</p>
      <p v-else>New buyer inquiries from the website appear here. You can also log a phone inquiry yourself.</p>
      <button v-if="hasFilters" type="button" class="btn btn-sm btn-outline-white" @click="clearFilters">Clear filters</button>
      <button v-else-if="view === 'active'" type="button" class="btn btn-sm btn-emerald" @click="showAddLeadModal = true">Log New Inquiry</button>
    </div>

    <div v-else class="ld-split" :class="{ 'is-detail': detailOpen }">
      <!-- Lead list with multi-select and bulk staff assignment -->
      <div class="ld-list-wrap">
        <div v-if="view === 'active' && isLeadAdmin" class="ld-list-header">
          <label class="ld-select-all" title="Select all visible leads">
            <input
              type="checkbox"
              class="ld-checkbox"
              :checked="allFilteredSelected && filteredLeads.length > 0"
              :indeterminate.prop="someFilteredSelected"
              @change="toggleSelectAll"
            />
            <span class="ld-select-all-text">
              {{ selectedLeadIds.size ? `${selectedLeadIds.size} selected` : 'Select all' }}
            </span>
          </label>

          <div v-if="selectedLeadIds.size" class="ld-bulk-bar">
            <select v-model="bulkAssignTarget" class="ld-select ld-bulk-select" aria-label="Transfer selected leads to">
              <option value="" disabled>Transfer to staff...</option>
              <option value="unassigned">Unassigned</option>
              <option v-for="s in staff" :key="s.id" :value="s.id">{{ s.name }} ({{ s.role || 'Staff' }})</option>
            </select>
            <button
              type="button"
              class="btn btn-sm btn-emerald ld-bulk-btn"
              :disabled="!bulkAssignTarget || isBulkAssigning"
              @click="executeBulkAssign"
            >
              {{ isBulkAssigning ? 'Transferring…' : 'Transfer' }}
            </button>
            <button type="button" class="ld-bulk-cancel" title="Clear selection" @click="clearSelection">&times;</button>
          </div>
        </div>

        <ul class="ld-list" aria-label="Leads">
          <li v-for="lead in filteredLeads" :key="lead.id" class="ld-item" :class="{ active: selected?.id === lead.id, checked: selectedLeadIds.has(lead.id) }" :data-tier="lead.tier || 'none'">
            <label v-if="view === 'active' && isLeadAdmin" class="ld-check-col" :title="`Select ${lead.name}`" @click.stop>
              <input
                type="checkbox"
                class="ld-checkbox"
                :checked="selectedLeadIds.has(lead.id)"
                @change="toggleSelectLead(lead.id)"
              />
            </label>
            <button type="button" class="ld-row" :aria-current="selected?.id === lead.id ? 'true' : undefined" @click="openLead(lead)">
              <span class="ld-main">
                <span class="ld-name">
                  <span v-if="isFresh(lead)" class="ld-new" title="Not contacted yet"></span>
                  <span class="ld-name-text" :title="lead.name || 'Direct Buyer'">{{ lead.name || 'Direct Buyer' }}</span>
                </span>
                <span class="ld-badges-row">
                  <span class="ld-stage" :data-stage="stageKey(lead.stage)">{{ lead.stage }}</span>
                  <span v-if="assignedName(lead)" class="ld-assigned-tag" :title="`Assigned to ${assignedName(lead)}`">
                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    <span class="ld-assigned-text">{{ assignedName(lead) }}</span>
                  </span>
                </span>
                <span v-if="highlightsFor(lead).length" class="ld-hl-row">
                  <span v-for="h in highlightsFor(lead).slice(0, 2)" :key="h.label" class="ld-hl-chip" :title="h.label + ': ' + h.value">{{ h.value }}</span>
                </span>
                <span v-if="flagFor(lead)" class="ld-flag" :data-tone="flagFor(lead)!.tone">{{ flagFor(lead)!.text }}</span>
                <span v-else-if="lastContactText(lead)" class="ld-flag" data-tone="muted">{{ lastContactText(lead) }}</span>
                <span v-else class="ld-prop">{{ lead.property }}</span>
                <span v-if="shortNote(lead)" class="ld-note-line" :title="shortNote(lead)">{{ shortNote(lead) }}</span>
              </span>
              <span class="ld-side">
                <span class="ld-tier" :data-tier="lead.tier || 'none'">{{ tierLabel(lead.tier) }}</span>
                <time :datetime="lead.created_at" :title="fullDate(lead.created_at)">{{ ago(lead.created_at) }}</time>
              </span>
            </button>
            <a v-if="lead.phone && !lead.deleted_at" :href="`tel:${lead.phone}`" class="ld-row-call" :aria-label="`Call ${lead.name}`" :title="`Call ${lead.phone}`">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.8 19.8 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.12 4.18 2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.96.36 1.9.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.91.34 1.85.57 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
            </a>
          </li>
        </ul>
      </div>

      <!-- Lead detail -->
      <article v-if="selected" class="ld-detail" :aria-label="`Details for ${selected.name}`">
        <div class="ld-backbar">
          <button type="button" class="ld-back" @click="detailOpen = false">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><polyline points="15 18 9 12 15 6"/></svg>
            <span>All leads</span>
          </button>
          <span class="ld-backbar-name">{{ selected.name }}</span>
        </div>

        <header class="ld-head">
          <div class="ld-head-top">
            <h2>{{ selected.name }}</h2>
            <span class="ld-tier ld-tier--big" :data-tier="selected.tier || 'none'">{{ tierLabel(selected.tier) }}<template v-if="selected.score"> · {{ selected.score }}</template></span>
          </div>
          <div class="ld-phone">
            <strong dir="ltr">{{ selected.phone }}</strong>
            <button type="button" class="ld-copy" :title="'Copy ' + selected.phone" @click="copyPhone(selected.phone)">Copy</button>
          </div>
          <p class="ld-received">
            Received {{ fullDate(selected.created_at) }} ({{ ago(selected.created_at) }})
            <template v-if="selected.deleted_at"> · <span class="ld-deleted">Deleted</span></template>
          </p>
        </header>

        <section v-if="highlightsFor(selected).length || activeVisit" class="ld-hl-card" :class="{ 'with-visit': !!activeVisit && highlightsFor(selected).length }" aria-label="Highlighted answers and site visit">
          <button
            v-if="highlightsFor(selected).length"
            type="button"
            class="ld-hl-edit-corner"
            title="Change highlighted fields"
            @click="openHighlights(formKeyOf(selected))"
          >
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
            <span>Change</span>
          </button>

          <div v-if="highlightsFor(selected).length" class="ld-hl-left">
            <div v-for="h in highlightsFor(selected)" :key="h.label" class="ld-hl-item">
              <span class="ld-hl-label">{{ h.label }}:</span>
              <strong class="ld-hl-value">{{ h.value }}</strong>
            </div>
          </div>

          <div v-if="activeVisit" class="ld-hl-visit-side">
            <div class="ld-hl-visit-badge-row">
              <span class="ld-hl-visit-badge" :data-status="activeVisit.status">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                <span>{{ activeVisit.visit_type || 'Site Visit' }}: {{ activeVisit.status }}</span>
              </span>
              <button type="button" class="ld-hl-visit-link" @click="tab = 'visits'">
                View all ({{ selectedVisits.length }}) &rarr;
              </button>
            </div>

            <div class="ld-hl-visit-details">
              <div class="ld-hl-visit-time">
                <strong dir="ltr">{{ formatHumanVisit(activeVisit.date, activeVisit.time) }}</strong>
              </div>
              <p v-if="activeVisit.property_title" class="ld-hl-visit-meta">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg>
                <span>{{ activeVisit.property_title }}</span>
              </p>
              <p v-if="activeVisit.assigned_agent" class="ld-hl-visit-meta">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                <span>Guide: {{ activeVisit.assigned_agent }}</span>
              </p>
              <p v-if="activeVisit.vip_pickup" class="ld-hl-visit-meta">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
                <span>VIP Pickup: {{ activeVisit.pickup_location || 'Buyer location' }}</span>
              </p>
              <p v-if="activeVisit.outcome_notes" class="ld-hl-visit-outcome">
                <strong>Outcome:</strong> {{ activeVisit.outcome_notes }}
              </p>
            </div>
          </div>
        </section>

        <div v-if="!selected.deleted_at" class="ld-actions">
          <a v-if="selected.phone" :href="`tel:${selected.phone}`" class="ld-act ld-act--call">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.8 19.8 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.12 4.18 2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.96.36 1.9.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.91.34 1.85.57 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
            Call
          </a>
          <a v-if="selected.phone" :href="whatsappLink(selected)" target="_blank" rel="noopener noreferrer" class="ld-act ld-act--wa">WhatsApp</a>
          <NuxtLink v-if="selected.visitor_id" :to="`/admin/lead-journey/${selected.id}`" class="ld-act" title="Pages visited, ad source and survey steps">Website journey</NuxtLink>
        </div>

        <div v-if="!selected.deleted_at" class="ld-status">
          <span id="ld-status-label" class="ld-label">Status</span>
          <div class="ld-seg" role="group" aria-labelledby="ld-status-label">
            <button v-for="stage in stages" :key="stage" type="button" :class="{ on: selected.stage === stage }" :aria-pressed="selected.stage === stage" @click="setStage(selected, stage)">{{ stage }}</button>
          </div>
          <span v-if="!stages.includes(selected.stage)" class="ld-muted">Current status: {{ selected.stage }}</span>
        </div>
        <p v-if="selected.stage === 'Lost' && selected.lost_reason" class="ld-lost">Lost: {{ selected.lost_reason }}</p>

        <div v-if="!selected.deleted_at" class="ld-work">
          <div class="ld-work-col ld-work-col--assign">
            <div class="ld-work-header">
              <span class="ld-work-label">Assigned to</span>
            </div>
            <div class="ld-work-control">
              <select v-if="isLeadAdmin" :value="selected.assigned_to ?? ''" class="ld-select ld-work-select" @change="assignLead(selected, $event)">
                <option value="">Unassigned</option>
                <option v-for="s in staff" :key="s.id" :value="s.id">{{ s.name }}</option>
              </select>
              <div v-else class="ld-assigned-readonly">
                <span>{{ assignedName(selected) || 'Unassigned' }}</span>
              </div>
            </div>
          </div>
          <div class="ld-work-col ld-work-col--follow">
            <div class="ld-work-header">
              <span id="ld-follow-label" class="ld-work-label">Next follow-up</span>
              <span class="ld-follow-now" :class="followState(selected)">({{ followText(selected) }})</span>
            </div>
            <div class="ld-work-control ld-chips" role="group" aria-labelledby="ld-follow-label">
              <button type="button" @click="setFollowUp(selected, inDays(1))">Tomorrow</button>
              <button type="button" @click="setFollowUp(selected, inDays(3))">In 3 days</button>
              <button type="button" @click="setFollowUp(selected, inDays(7))">Next week</button>
              <label class="ld-pick" title="Pick date and time">
                <span class="ld-sr">Pick a date and time</span>
                <input v-model="pickedFollowUp" type="datetime-local" @change="pickFollowUp(selected)" />
              </label>
              <button v-if="selected.next_follow_up_at" type="button" class="ld-clear" @click="setFollowUp(selected, null)">Clear</button>
            </div>
          </div>
        </div>

        <div v-if="!selected.deleted_at" class="ld-tabs-detail" role="tablist" aria-label="Lead sections">
          <button v-for="t in detailTabs" :key="t.key" type="button" role="tab" :aria-selected="tab === t.key" :class="{ on: tab === t.key }" @click="tab = t.key">
            {{ t.label }}<span v-if="t.count" class="ld-count">{{ t.count }}</span>
          </button>
        </div>

        <LeadActivityPanel
          v-if="!selected.deleted_at && tab === 'activity'"
          :lead-id="selected.id" :received-at="selected.created_at" :activities="crmDetail.activities" :loading="crmLoading"
          @changed="refreshAll"
        />
        <LeadVisitsPanel
          v-if="!selected.deleted_at && tab === 'visits'"
          :lead-id="selected.id" :visits="crmDetail.visits" :staff="staff" :assigned-to="selected.assigned_to ?? null"
          :suggest-video="selected.parsed?.lives === 'Abroad (NRB)'"
          @changed="refreshAll"
        />

        <section v-if="formAnswers(selected).length" v-show="selected.deleted_at || tab === 'overview'" class="ld-section ld-answers">
          <h3>
            Form answers
            <span v-if="selected.meta_form_name" class="ld-answers-form">{{ selected.meta_form_name }}</span>
          </h3>
          <ol class="ld-qa">
            <li v-for="(qa, i) in formAnswers(selected)" :key="i" :class="{ hl: qa.highlighted }">
              <span class="ld-q"><span class="ld-qn" aria-hidden="true">{{ i + 1 }}</span>{{ qa.question }}</span>
              <strong class="ld-a">{{ qa.answer }}</strong>
            </li>
          </ol>
        </section>

        <section v-for="section in detailSections" v-show="selected.deleted_at || tab === 'overview'" :key="section.title" class="ld-section">
          <h3>{{ section.title }}</h3>
          <dl>
            <div v-for="row in section.rows" :key="row.label">
              <dt>{{ row.label }}</dt>
              <dd>
                <NuxtLink v-if="row.to" :to="row.to">{{ row.value }}</NuxtLink>
                <template v-else>{{ row.value }}</template>
              </dd>
            </div>
          </dl>
        </section>

        <section v-if="selected.note" v-show="selected.deleted_at || tab === 'overview'" class="ld-section">
          <h3>Notes</h3>
          <p class="ld-note">{{ selected.note }}</p>
        </section>

        <footer v-if="isLeadAdmin" class="ld-foot">
          <button v-if="selected.deleted_at" type="button" class="btn btn-sm btn-emerald" @click="restoreLead(selected)">Restore lead</button>
          <button v-else type="button" class="ld-delete" @click="promptDeleteLead(selected)">Delete this inquiry</button>
        </footer>
      </article>
    </div>

    <!-- Phones: add a lead with one thumb -->
    <button v-if="!detailOpen && view === 'active'" type="button" class="ld-fab" aria-label="Log new inquiry" @click="showAddLeadModal = true">
      <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
    </button>

    <HighlightFieldsModal
      v-if="highlightModal.open && modalHighlightForms.length"
      :forms="modalHighlightForms" :selected="highlights.selected" :initial-form="highlightModal.form" :saving="highlightModal.saving"
      @close="highlightModal.open = false" @save="saveHighlights"
    />

    <!-- MODAL: WHY WAS THE LEAD LOST? -->
    <div v-if="lostTarget" class="admin-modal-overlay" @click.self="lostTarget = null">
      <div class="admin-modal-card animate-fade-in-up" style="max-width: 460px;">
        <div class="admin-modal-header">
          <h3 class="admin-modal-title">Mark {{ lostTarget.name }} as lost</h3>
          <button class="admin-modal-close" aria-label="Close" @click="lostTarget = null">✕</button>
        </div>
        <form @submit.prevent="confirmLost">
          <div class="admin-modal-body">
            <label class="form-label" for="lost-reason">Why did we lose this lead?</label>
            <select id="lost-reason" v-model="lostChoice" class="form-select">
              <option v-for="r in lostReasons" :key="r" :value="r">{{ r }}</option>
            </select>
            <input v-if="lostChoice === 'Other'" v-model.trim="lostOther" type="text" class="form-input" style="margin-top: 10px;" placeholder="Write the reason" maxlength="150" aria-label="Other reason" />
            <p style="font-size: 0.82rem; color: var(--admin-text-muted); margin-top: 10px;">You can move the lead back to another status any time.</p>
          </div>
          <div class="admin-modal-footer">
            <button type="button" class="btn btn-sm btn-outline-white" @click="lostTarget = null">Cancel</button>
            <button type="submit" class="btn btn-sm" style="background:#EF4444; color:#FFF;" :disabled="lostChoice === 'Other' && !lostOther">Mark as lost</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Log a buyer the team spoke to (phone, Messenger, walk-in…) -->
    <LogInquiryModal
      v-if="showAddLeadModal"
      :staff="staff"
      :leads="leadsList"
      :me-id="me?.id ?? null"
      @close="showAddLeadModal = false"
      @open="openFromInquiry"
      @saved="onInquirySaved"
    />

    <!-- ======================================================================
         MODAL: CONFIRM SOFT DELETE LEAD
         ====================================================================== -->
    <div v-if="deleteLeadTarget" class="admin-modal-overlay" @click.self="deleteLeadTarget = null">
      <div class="admin-modal-card animate-fade-in-up" style="max-width:440px;">
        <div class="admin-modal-header">
          <h3 class="admin-modal-title" style="color: #F87171; display:flex; align-items:center; gap:8px;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
              <polyline points="3 6 5 6 21 6"></polyline>
              <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
            </svg>
            Delete Lead Inquiry
          </h3>
          <button class="admin-modal-close" aria-label="Close" @click="deleteLeadTarget = null">✕</button>
        </div>
        <div class="admin-modal-body" style="padding: 20px;">
          <p style="color: var(--admin-text-secondary); font-size: 0.92rem; line-height: 1.5; margin-bottom: 14px;">
            Are you sure you want to soft delete the inquiry from <strong style="color: #FFF;">{{ deleteLeadTarget.name }}</strong>?
          </p>
          <div style="background: var(--admin-bg-surface-alt); border: 1px solid var(--admin-border-subtle); padding: 12px 14px; border-radius: var(--radius-sm); font-size: 0.85rem; color: #8E9B8F;">
            <div>Phone: <strong style="color: #FFF;">{{ deleteLeadTarget.phone }}</strong></div>
            <div v-if="deleteLeadTarget.property" style="margin-top:4px;">Property: <strong style="color: #10B981;">{{ deleteLeadTarget.property }}</strong></div>
          </div>
          <p style="color: #9CA3AF; font-size: 0.8rem; margin-top: 12px; line-height: 1.4;">
            This will soft-delete the lead from active pipeline views while keeping it safely recoverable in the Trash.
          </p>
        </div>
        <div class="admin-modal-footer">
          <button type="button" class="btn btn-sm btn-outline-white" :disabled="isDeleting" @click="deleteLeadTarget = null">Cancel</button>
          <button
            type="button"
            class="btn btn-sm"
            :disabled="isDeleting"
            style="background:#EF4444; color:#FFF; display:inline-flex; align-items:center; gap:6px;"
            @click="executeDeleteLead"
          >
            <span>{{ isDeleting ? 'Deleting...' : 'Confirm Soft Delete' }}</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, computed, watch, onMounted, onBeforeUnmount, nextTick } from 'vue'
import { useToast } from '~/composables/useToast'
import { useApiUrl } from '~/composables/useApi'
import { buildQuestions } from '~/utils/leadSurvey'
import LeadActivityPanel from '~/components/admin/crm/LeadActivityPanel.vue'
import LeadVisitsPanel from '~/components/admin/crm/LeadVisitsPanel.vue'
import HighlightFieldsModal from '~/components/admin/crm/HighlightFieldsModal.vue'
import LogInquiryModal from '~/components/admin/crm/LogInquiryModal.vue'
import CrmFilterDropdown from '~/components/admin/crm/CrmFilterDropdown.vue'
import { Activity, ArrowUpDown, CircleDot, FolderKanban, Layers, MessageSquareQuote, RotateCcw, Target, Users } from 'lucide-vue-next'
import {
  LEAD_STAGES, dhakaIso, endOfDhakaDay, formatDay, formatHumanVisit, formatTime, formatWhen, inDays, parseApiDate, useLeadCrm,
  type LeadActivity, type LeadHighlights, type LeadVisit, type StaffMember, type HighlightForm
} from '~/composables/useLeadCrm'
import { toLatinDigits, useVoiceSearch, type VoiceLang } from '~/composables/useVoiceSearch'

definePageMeta({
  layout: 'admin'
})

const toast = useToast()
const { token, user: me, isSuperAdmin } = useAuth()
const isLeadAdmin = computed(() => isSuperAdmin.value || me.value?.role === 'admin' || (me.value?.permissions || []).includes('*'))

const view = ref<'active' | 'trash'>('active')
const projectFilter = ref('all')
const statusFilter = ref('All')
const staffFilter = ref<number | 'all' | 'unassigned'>('all')
const focus = ref('All')
const sortBy = ref<'newest' | 'hottest'>('newest')
const search = ref('')
const showAddLeadModal = ref(false)
const isLoading = ref(false)
const detailOpen = ref(false)
const selectedId = ref<number | null>(null)

const selectedLeadIds = reactive(new Set<number>())
const bulkAssignTarget = ref<string>('')
const isBulkAssigning = ref(false)

const deleteLeadTarget = ref<any | null>(null)
const isDeleting = ref(false)

const leadsList = ref<any[]>([])
const stages: readonly string[] = LEAD_STAGES

/* ---------- Reading what the website stored ---------- */

// Survey answers are stored in English; show the same Bangla wording the buyer saw.
const answerLabels = new Map<string, string>()
;[...buildQuestions(false), ...buildQuestions(true)].forEach(q => q.options.forEach(o => answerLabels.set(o.value, o.label)))
const say = (value?: string | null) => (value ? answerLabels.get(value) || value : '')

const ctaLabels: Record<string, string> = {
  inline_first_question: 'First question on the property page',
  sidebar_first_question: 'First question in the side panel'
}

// The survey saves its extra answers as "Key: value" lines in `message`; manual leads save plain text.
const readMessage = (message?: string | null) => {
  const out: Record<string, string> = {}
  const notes: string[] = []
  for (const raw of String(message || '').split('\n')) {
    const line = raw.trim()
    if (!line) continue
    const m = line.match(/^([^:]{2,40}):\s*(.+)$/)
    const key = m?.[1]
    if (key === 'Lead score') out.score = m![2]
    else if (key === 'Payment plan') out.payment = m![2]
    else if (key === 'Lives') out.lives = m![2]
    else if (key?.startsWith('Preferred time')) { if (m![2] !== 'Not specified') out.time = m![2] }
    else if (key === 'Survey') out.survey = m![2]
    else if (key === 'Price shown') out.price = m![2]
    else if (key === 'CTA') out.cta = m![2]
    else if (key === 'Landing page') out.landing = m![2]
    else if (key === 'Contact consent' || key === 'Ad content' || key === 'Ad term') continue
    else if (line === 'Came from a Facebook ad click') out.fbclick = '1'
    else notes.push(line)
  }
  const score = out.score?.match(/^(HOT|WARM|COLD)\s*\((\d+)\s*\/\s*(\d+)\)/)
  return {
    parsed: out,
    tier: (score?.[1] as 'HOT' | 'WARM' | 'COLD' | undefined) || null,
    score: score ? `${score[2]}/${score[3]}` : '',
    note: notes.join('\n')
  }
}

const normalizeStage = (value?: string | null) => {
  const s = String(value || 'New').trim()
  if (s.toLowerCase() === 'site visit completed' || s.toLowerCase() === 'site_visit_completed') return 'Visited'
  const known = LEAD_STAGES.find(stage => stage.toLowerCase() === s.toLowerCase())
  return known || s.charAt(0).toUpperCase() + s.slice(1).toLowerCase()
}

// quiet: refresh in the background without hiding the open lead behind a loading message.
const fetchLeads = async (onlyTrashed = false, quiet = false) => {
  if (!quiet) isLoading.value = true
  try {
    const url = onlyTrashed ? useApiUrl('/leads?only_trashed=1') : useApiUrl('/leads')
    const res = await fetch(url, { headers: { Authorization: `Bearer ${token.value}` } })
    if (!res.ok) throw new Error('Lead access failed')
    const json = await res.json()
    if (json && json.success && Array.isArray(json.data)) {
      leadsList.value = json.data.map((l: any) => {
        const read = readMessage(l.message)
        return {
          ...l,
          ...read,
          name: l.name || 'Direct Buyer',
          note: withoutFormAnswers(read.note, l.meta_answers),
          property: l.property_title || l.property || 'Direct Inquiry',
          type: l.buyer_category || l.buyer_type || l.lead_type || l.type || 'Direct Buyer',
          stage: normalizeStage(l.status || l.stage)
        }
      })
    } else {
      leadsList.value = []
    }
  } catch (err) {
    console.error('Failed to fetch leads:', err)
    toast.error('Could not load inquiries', 'Please check your session and lead access permissions.')
    leadsList.value = []
  } finally {
    isLoading.value = false
  }
}

const changeView = async (next: 'active' | 'trash') => {
  view.value = next
  statusFilter.value = 'All'
  staffFilter.value = 'all'
  quick.value = next === 'trash' ? 'trash' : 'all'
  clearSelection()
  detailOpen.value = false
  await fetchLeads(next === 'trash')
}

// Phones: the search bar and the lead's back bar stick just under the admin top bar, whatever its height.
const pageRoot = ref<HTMLElement | null>(null)
const measureTopbar = () => {
  const bar = document.querySelector('.admin-topbar') as HTMLElement | null
  pageRoot.value?.style.setProperty('--ld-sticky-top', `${bar?.offsetHeight ?? 0}px`)
}
onMounted(() => { measureTopbar(); window.addEventListener('resize', measureTopbar, { passive: true }) })
onBeforeUnmount(() => window.removeEventListener('resize', measureTopbar))

onMounted(async () => {
  voiceLang.value = readVoiceLang()
  await Promise.all([fetchLeads(), loadOverview(), loadHighlights()])
  // /admin/leads?lead=12 (linked from the Site Viewings page) opens that lead.
  const wanted = Number(route.query.lead)
  if (wanted && leadsList.value.some(l => l.id === wanted)) {
    selectedId.value = wanted
    detailOpen.value = true
  }
})

/* ---------- Filtering, sorting, selection ---------- */

const matchesFocus = (l: any) => {
  if (focus.value === 'All') return true
  if (focus.value === 'Ready within 3 months') {
    return ['Within 30 days', '1–3 months'].includes(l.investment_readiness) && l.budget_range === 'Listed price fits budget'
  }
  return (l.type || '').includes(focus.value)
}
// "01785 665175", "+8801785…" and "০১৭৮৫…" all find the same buyer: phone numbers are compared digit by digit.
const digitsOf = (value: unknown) => toLatinDigits(String(value || '')).replace(/\D/g, '')
const matchesSearch = (l: any) => {
  const q = toLatinDigits(search.value).toLowerCase()
  if (!q) return true
  const qDigits = q.replace(/\D/g, '')
  if (qDigits.length >= 3 && /^[\d\s+()-]+$/.test(q)) {
    const phone = digitsOf(l.phone)
    return phone.includes(qDigits) || phone.includes(qDigits.replace(/^0/, ''))
  }
  const words = q.split(/\s+/).filter(Boolean)
  const haystack = [l.name, l.phone, l.property, l.email, l.utm_campaign].map(v => String(v || '').toLowerCase()).join(' ')
  return words.every(w => haystack.includes(w))
}

/* ---------- Voice search ---------- */

const voice = useVoiceSearch()
const readVoiceLang = (): VoiceLang => {
  try { return localStorage.getItem('gbrel_voice_lang') === 'bn-BD' ? 'bn-BD' : 'en-US' } catch { return 'en-US' }
}
const voiceLang = ref<VoiceLang>('en-US')
const toggleVoiceLang = () => {
  voiceLang.value = voiceLang.value === 'bn-BD' ? 'en-US' : 'bn-BD'
  try { localStorage.setItem('gbrel_voice_lang', voiceLang.value) } catch { /* private mode */ }
}
const toggleVoice = () => {
  if (voice.listening.value) { voice.stop(); return }
  voice.start(voiceLang.value, query => {
    search.value = query
    // One match: open it straight away, the way a person would after searching.
    nextTick(() => {
      if (filteredLeads.value.length === 1) openLead(filteredLeads.value[0])
      else if (!filteredLeads.value.length) toast.info('No match', `Nothing found for "${query}".`)
    })
  })
}

/* ---------- Highlighted answers ---------- */

const highlights = ref<LeadHighlights>({ forms: [], selected: {} })
const highlightModal = reactive<{ open: boolean; form: string | null; saving: boolean }>({ open: false, form: null, saving: false })
const showFilters = ref(false)

const websiteFieldValue: Record<string, (l: any) => string> = {
  'Best time to call': l => l.callback_time || l.parsed?.time || '',
  'Wants to buy': l => say(l.investment_readiness),
  Budget: l => say(l.budget_range),
  'Plans to use it for': l => say(l.buyer_category),
  Prefers: l => l.preferred_contact || '',
  Email: l => l.email || ''
}
/** Every question of a Meta lead form with the buyer's answer, in the order of the form. */
const formAnswers = (l: any): { question: string; answer: string; highlighted: boolean }[] => {
  if (!l || !Array.isArray(l.meta_answers)) return []
  const chosen = highlights.value.selected[formKeyOf(l)] || []
  return l.meta_answers
    .filter((a: any) => a?.question && a?.answer)
    .map((a: any) => ({
      question: String(a.question),
      answer: String(a.answer),
      highlighted: chosen.some((c: string) => c.trim() === String(a.question).trim())
    }))
}
// The answers are also saved as "Question: answer" lines in the message; they now have their own section, so keep them out of Notes.
const withoutFormAnswers = (note: string, answers: unknown): string => {
  if (!note || !Array.isArray(answers)) return note
  const lines = new Set(answers.map((a: any) => `${a?.question}: ${a?.answer}`.trim()))
  return note.split('\n').filter(line => !lines.has(line.trim())).join('\n').trim()
}

const formKeyOf = (l: any): string => {
  if (l?.meta_form_id) return String(l.meta_form_id)
  if (l?.meta_form_name && highlights.value.selected[l.meta_form_name]) return l.meta_form_name
  const prop = l?.property_title || l?.property
  if (prop && highlights.value.selected[`website:${prop}`]) {
    return `website:${prop}`
  }
  return 'website'
}
const highlightsFor = (l: any): { label: string; value: string }[] => {
  if (!l) return []
  const key = formKeyOf(l)
  const chosen = highlights.value.selected[key] || (key.startsWith('website:') ? highlights.value.selected['website'] : []) || []
  if (!chosen.length) return []
  if (!l.meta_form_id && (!Array.isArray(l.meta_answers) || l.meta_answers.length === 0)) {
    return chosen.map(label => ({ label, value: websiteFieldValue[label]?.(l) || '' })).filter(h => h.value)
  }
  const answers: { question: string; answer: string }[] = Array.isArray(l.meta_answers) ? l.meta_answers : []
  return chosen
    .map(label => {
      const match = answers.find(a => String(a?.question || '').trim() === label.trim())
      return { label, value: match?.answer || '' }
    })
    .filter(h => h.value)
}

const modalHighlightForms = computed<HighlightForm[]>(() => {
  const metaMap = new Map<string, { key: string; name: string; fields: Set<string>; count: number }>()

  // Seed from backend highlights.forms
  for (const f of highlights.value.forms) {
    if (f.key !== 'website' && !f.key.startsWith('website:')) {
      metaMap.set(f.key, {
        key: f.key,
        name: f.name,
        fields: new Set(f.fields || []),
        count: (f as any).count || 0
      })
    }
  }

  // Merge from leadsList (exact same source as projectDropdownOptions!)
  for (const l of leadsList.value) {
    if (l.meta_form_id) {
      const key = String(l.meta_form_id)
      const existing = metaMap.get(key)
      const name = l.meta_form_name || l.property_title || existing?.name || `Form ${key}`
      if (!existing) {
        metaMap.set(key, {
          key,
          name,
          fields: new Set<string>(),
          count: 1
        })
      } else {
        existing.count++
        if (name && (existing.name.startsWith('Form ') || !existing.name)) {
          existing.name = name
        }
      }

      // Collect questions from meta_answers so they can be highlighted
      const entry = metaMap.get(key)!
      if (Array.isArray(l.meta_answers)) {
        for (const a of l.meta_answers) {
          if (a?.question && typeof a.question === 'string' && a.question.trim()) {
            entry.fields.add(a.question.trim())
          }
        }
      }
    }
  }

  // Sort Meta forms by count descending (exact same order and counts as All Projects)
  const metaList: HighlightForm[] = [...metaMap.values()]
    .sort((a, b) => b.count - a.count)
    .map(m => ({
      key: m.key,
      name: m.name,
      fields: Array.from(m.fields),
      count: m.count,
      source: 'meta' as const
    }))

  // Website forms below Meta
  const websiteForms: HighlightForm[] = []
  const baseWeb = highlights.value.forms.find(f => f.key === 'website')
  const baseWebFields = baseWeb?.fields?.length ? baseWeb.fields : [
    'Best time to call', 'Wants to buy', 'Budget', 'Plans to use it for', 'Prefers', 'Email'
  ]
  const totalWebCount = leadsList.value.filter(l => !l.meta_form_id).length

  websiteForms.push({
    key: 'website',
    name: 'All Website Inquiries',
    fields: baseWebFields,
    count: totalWebCount,
    source: 'website' as const
  })

  const webProjectsMap = new Map<string, { count: number }>()
  for (const l of leadsList.value) {
    if (!l.meta_form_id) {
      const p = l.property_title || l.property || 'General Inquiry'
      const cur = webProjectsMap.get(p) || { count: 0 }
      cur.count++
      webProjectsMap.set(p, cur)
    }
  }
  for (const f of highlights.value.forms) {
    if (f.key.startsWith('website:')) {
      const p = f.key.slice(8)
      if (!webProjectsMap.has(p)) {
        webProjectsMap.set(p, { count: 0 })
      }
    }
  }

  const sortedWebProjects = [...webProjectsMap.entries()].sort((a, b) => b[1].count - a[1].count)
  for (const [proj, info] of sortedWebProjects) {
    const existingF = highlights.value.forms.find(f => f.key === `website:${proj}`)
    websiteForms.push({
      key: `website:${proj}`,
      name: proj,
      fields: existingF?.fields?.length ? existingF.fields : baseWebFields,
      count: info.count,
      source: 'website' as const
    })
  }

  return [...metaList, ...websiteForms]
})

async function loadHighlights() {
  try {
    highlights.value = await crm.highlights()
  } catch {
    // Leads still show without highlights.
  }
}
const openHighlights = async (form?: string) => {
  if (!highlights.value.forms.length) await loadHighlights()
  highlightModal.form = form || (projectFilter.value !== 'all' ? projectFilter.value : null)
  highlightModal.open = true
}
const saveHighlights = async (form: string, fields: string[]) => {
  highlightModal.saving = true
  try {
    const saved = await crm.saveHighlights(form, fields)
    highlights.value.selected = { ...highlights.value.selected, [saved.form]: saved.fields }
    highlightModal.open = false
    toast.success('Highlights saved', fields.length ? `${fields.length} field${fields.length > 1 ? 's' : ''} will show first.` : 'Nothing is highlighted for this form now.')
  } catch (err: any) {
    toast.error('Could not save', err?.message || 'Only team members who can manage leads can change this.')
  } finally {
    highlightModal.saving = false
  }
}

/* ---------- Filter by Project (Meta forms & Website properties) ---------- */

const matchesProject = (l: any): boolean => {
  if (!projectFilter.value || projectFilter.value === 'all') return true
  if (projectFilter.value === 'website' || projectFilter.value === 'website:all') {
    return !l.meta_form_id
  }
  if (projectFilter.value.startsWith('website:')) {
    const target = projectFilter.value.slice(8)
    return !l.meta_form_id && ((l.property_title || '') === target || (l.property || '') === target)
  }
  return String(l.meta_form_id || '') === projectFilter.value ||
         (l.meta_form_name && l.meta_form_name === projectFilter.value) ||
         (l.property_title && l.property_title === projectFilter.value)
}

const projectDropdownOptions = computed(() => {
  const allCount = leadsList.value.filter(l => matchesFocus(l) && matchesSearch(l) && matchesQuick(l)).length

  // Meta forms
  const metaFormMap = new Map<string, { key: string; name: string }>()
  for (const f of highlights.value.forms) {
    if (f.key !== 'website' && !f.key.startsWith('website:')) {
      metaFormMap.set(f.key, { key: f.key, name: f.name })
    }
  }
  for (const l of leadsList.value) {
    if (l.meta_form_id && !metaFormMap.has(String(l.meta_form_id))) {
      metaFormMap.set(String(l.meta_form_id), {
        key: String(l.meta_form_id),
        name: l.meta_form_name || l.property_title || `Form ${l.meta_form_id}`
      })
    }
  }

  // Website projects
  const websiteProjectCounts = new Map<string, number>()
  let totalWebsiteCount = 0
  for (const l of leadsList.value) {
    if (!l.meta_form_id) {
      totalWebsiteCount++
      const p = l.property_title || l.property || 'General Inquiry'
      websiteProjectCounts.set(p, (websiteProjectCounts.get(p) || 0) + 1)
    }
  }
  for (const f of highlights.value.forms) {
    if (f.key.startsWith('website:')) {
      const proj = f.key.slice(8)
      if (!websiteProjectCounts.has(proj)) {
        websiteProjectCounts.set(proj, 0)
      }
    }
  }

  const items: any[] = [
    { value: 'all', label: 'All Projects', count: allCount }
  ]

  if (metaFormMap.size > 0) {
    const metaOptions = [...metaFormMap.values()].map(mf => {
      const count = leadsList.value.filter(l => String(l.meta_form_id) === mf.key).length
      return {
        value: mf.key,
        label: mf.name,
        count
      }
    }).sort((a, b) => b.count - a.count)

    items.push({
      label: 'Meta Ad Campaigns',
      options: metaOptions
    })
  }

  if (totalWebsiteCount > 0 || websiteProjectCounts.size > 0) {
    const webOptions: any[] = [
      { value: 'website:all', label: 'All Website Inquiries', count: totalWebsiteCount }
    ]

    const sortedProjects = [...websiteProjectCounts.entries()].sort((a, b) => b[1] - a[1])
    for (const [proj, count] of sortedProjects) {
      webOptions.push({
        value: `website:${proj}`,
        label: proj,
        count
      })
    }

    items.push({
      label: 'Website Projects',
      options: webOptions
    })
  }

  return items
})

watch(projectFilter, () => {
  answerFilter.value = ''
  if (selected.value && !matchesProject(selected.value)) {
    selectedId.value = null
    detailOpen.value = false
  }
})

/* ---------- Filter by a highlighted form answer, e.g. "যত দ্রুত সম্ভব (আজই)" ---------- */

const answerFilter = ref('')
const ANSWER_SEP = '\u0001'
const allAnswersFor = (l: any): { label: string; value: string }[] => {
  const hl = highlightsFor(l)
  if (hl.length) return hl
  if (Array.isArray(l.meta_answers)) {
    return l.meta_answers
      .filter((a: any) => a?.question && a?.answer)
      .map((a: any) => ({ label: String(a.question), value: String(a.answer) }))
  }
  return []
}
const answerOptions = computed(() => {
  const groups = new Map<string, Map<string, number>>()
  const scopedLeads = leadsList.value.filter(l => matchesProject(l))
  for (const l of scopedLeads) {
    for (const h of allAnswersFor(l)) {
      const answers = groups.get(h.label) || new Map<string, number>()
      answers.set(h.value, (answers.get(h.value) || 0) + 1)
      groups.set(h.label, answers)
    }
  }
  return [...groups.entries()].map(([label, answers]) => ({
    label,
    options: [...answers.entries()]
      .sort((a, b) => b[1] - a[1])
      .map(([text, count]) => ({ value: label + ANSWER_SEP + text, text, count }))
  }))
})
const matchesAnswer = (l: any) => {
  if (!answerFilter.value) return true
  const [label, value] = answerFilter.value.split(ANSWER_SEP)
  return allAnswersFor(l).some(h => h.label === label && h.value === value)
}
// A saved choice that no longer exists (e.g. highlights changed) must not hide every lead.
watch(answerOptions, groups => {
  if (answerFilter.value && !groups.some(g => g.options.some(o => o.value === answerFilter.value))) answerFilter.value = ''
})

const baseLeads = computed(() => leadsList.value.filter(l => matchesProject(l) && matchesFocus(l) && matchesSearch(l) && matchesQuick(l) && matchesAnswer(l)))

const statusTabs = computed(() => [
  { value: 'All', label: 'All', count: baseLeads.value.length },
  ...stages.map(s => ({ value: s, label: s, count: baseLeads.value.filter(l => l.stage === s).length }))
])
const statusDropdownOptions = computed(() => [
  { value: 'All', label: 'All Status', count: baseLeads.value.length },
  ...stages.map(s => ({ value: s, label: s, count: baseLeads.value.filter(l => l.stage === s).length }))
])

const staffTabs = computed(() => {
  const map = new Map<number, number>()
  for (const l of baseLeads.value) {
    if (l.assigned_to) {
      map.set(l.assigned_to, (map.get(l.assigned_to) || 0) + 1)
    }
  }
  return staff.value.map(s => ({
    id: s.id,
    name: s.name,
    role: s.role,
    count: map.get(s.id) || 0
  }))
})

const unassignedCount = computed(() => baseLeads.value.filter(l => !l.assigned_to).length)

const staffDropdownOptions = computed(() => [
  { value: 'all', label: 'All Team', count: baseLeads.value.length },
  { value: 'unassigned', label: 'Unassigned', count: unassignedCount.value },
  ...staffTabs.value.map(s => ({
    value: s.id,
    label: s.name,
    count: s.count,
    subtext: s.role || 'Staff'
  }))
])

const answerDropdownOptions = computed(() => {
  const list: any[] = [
    { value: '', label: 'All answers' }
  ]
  for (const group of answerOptions.value) {
    list.push({
      label: group.label,
      options: group.options.map(opt => ({
        value: opt.value,
        label: opt.text,
        count: opt.count
      }))
    })
  }
  return list
})

const sortDropdownOptions = [
  { value: 'newest', label: 'Newest first' },
  { value: 'activity', label: 'Current Activity' },
  { value: 'hottest', label: 'Hottest first' }
]

const buyerFocusOptions = [
  { value: 'All', label: 'All buyers' },
  { value: 'Ready within 3 months', label: 'Ready within 3 months' },
  { value: 'Own / family use', label: 'Own / family use' },
  { value: 'Investment', label: 'Investment' }
]

const activeStaffInfo = computed(() => {
  if (typeof staffFilter.value !== 'number') return null
  return staffTabs.value.find(s => s.id === staffFilter.value) || null
})

const assignedName = (l: any): string => {
  if (!l?.assigned_to) return ''
  return staff.value.find(s => s.id === l.assigned_to)?.name || ''
}

const allFilteredSelected = computed(() => {
  if (!filteredLeads.value.length) return false
  return filteredLeads.value.every(l => selectedLeadIds.has(l.id))
})

const someFilteredSelected = computed(() => {
  if (!filteredLeads.value.length) return false
  const count = filteredLeads.value.filter(l => selectedLeadIds.has(l.id)).length
  return count > 0 && count < filteredLeads.value.length
})

const toggleSelectLead = (id: number) => {
  if (selectedLeadIds.has(id)) selectedLeadIds.delete(id)
  else selectedLeadIds.add(id)
}

const toggleSelectAll = () => {
  if (allFilteredSelected.value) {
    filteredLeads.value.forEach(l => selectedLeadIds.delete(l.id))
  } else {
    filteredLeads.value.forEach(l => selectedLeadIds.add(l.id))
  }
}

const clearSelection = () => {
  selectedLeadIds.clear()
  bulkAssignTarget.value = ''
}

const executeBulkAssign = async () => {
  if (!selectedLeadIds.size || !bulkAssignTarget.value) return
  isBulkAssigning.value = true
  try {
    const targetId = bulkAssignTarget.value === 'unassigned' ? null : Number(bulkAssignTarget.value)
    const ids = Array.from(selectedLeadIds)
    await crm.bulkAssign(ids, targetId)
    const targetName = targetId ? staff.value.find(s => s.id === targetId)?.name || 'staff' : 'Unassigned'
    toast.success('Leads transferred', `${ids.length} lead${ids.length > 1 ? 's' : ''} transferred to ${targetName}.`)
    clearSelection()
    await refreshAll()
  } catch (err: any) {
    toast.error('Could not transfer leads', err?.message || 'Try again.')
  } finally {
    isBulkAssigning.value = false
  }
}

const tierRank: Record<string, number> = { HOT: 0, WARM: 1, COLD: 2 }
const filteredLeads = computed(() => {
  let list = view.value === 'trash' || statusFilter.value === 'All'
    ? [...baseLeads.value]
    : baseLeads.value.filter(l => l.stage === statusFilter.value || l.id === selectedId.value)

  if (view.value === 'active') {
    if (staffFilter.value === 'unassigned') {
      list = list.filter(l => !l.assigned_to)
    } else if (typeof staffFilter.value === 'number') {
      list = list.filter(l => l.assigned_to === staffFilter.value)
    }
  }

  if (quick.value === 'activity') list.sort((a, b) => activityTimeMs(b) - activityTimeMs(a))
  else if (quick.value === 'followups') list.sort((a, b) => (followMs(a) ?? 0) - (followMs(b) ?? 0))
  else if (quick.value === 'visits') list.sort((a, b) => visitKey(a).localeCompare(visitKey(b)))
  else if (sortBy.value === 'activity') {
    list.sort((a, b) => activityTimeMs(b) - activityTimeMs(a))
  } else if (sortBy.value === 'hottest') {
    list.sort((a, b) => (tierRank[a.tier] ?? 3) - (tierRank[b.tier] ?? 3) || String(b.created_at).localeCompare(String(a.created_at)))
  }
  return list
})

const hasFilters = computed(() => !!search.value || focus.value !== 'All' || !!answerFilter.value || projectFilter.value !== 'all' || (view.value === 'active' && (statusFilter.value !== 'All' || quick.value !== 'all' || staffFilter.value !== 'all')))
const clearFilters = () => { search.value = ''; focus.value = 'All'; answerFilter.value = ''; projectFilter.value = 'all'; statusFilter.value = 'All'; quick.value = 'all'; staffFilter.value = 'all' }

/* ---------- CRM: owner, follow-up, site visits and activity ---------- */

const crm = useLeadCrm()
const route = useRoute()

const staff = ref<StaffMember[]>([])
const upcomingVisits = ref<LeadVisit[]>([])
const crmDetail = ref<{ activities: LeadActivity[]; visits: LeadVisit[] }>({ activities: [], visits: [] })
const crmLoading = ref(false)
const tab = ref<'overview' | 'activity' | 'visits'>('overview')
const quick = ref<'all' | 'activity' | 'followups' | 'visits' | 'unassigned' | 'mine' | 'trash'>('all')
watch(quick, val => {
  if (val === 'trash' && view.value !== 'trash') {
    changeView('trash')
  } else if (val !== 'trash' && view.value === 'trash') {
    changeView('active')
  }
})
const pickedFollowUp = ref('')
const lostTarget = ref<any | null>(null)
const lostReasons = ['Bought elsewhere', 'Price too high', 'Not interested any more', 'Could not reach the buyer', 'Other']
const lostChoice = ref(lostReasons[0])
const lostOther = ref('')

const isClosed = (l: any) => ['Converted', 'Lost'].includes(l.stage)
const followMs = (l: any): number | null => (l.next_follow_up_at ? parseApiDate(l.next_follow_up_at).getTime() : null)
const followState = (l: any): 'none' | 'overdue' | 'today' | 'later' => {
  const t = followMs(l)
  if (t === null) return 'none'
  if (t < Date.now()) return 'overdue'
  return t <= endOfDhakaDay() ? 'today' : 'later'
}
const followText = (l: any) => (followState(l) === 'none' ? 'Not set' : (followState(l) === 'overdue' ? 'Overdue, was ' : '') + formatWhen(l.next_follow_up_at))

// First open visit per lead (the overview is already sorted by date and time).
const nextVisit = computed(() => {
  const map = new Map<number, LeadVisit>()
  for (const v of upcomingVisits.value) if (!map.has(v.lead_id)) map.set(v.lead_id, v)
  return map
})
const visitKey = (l: any) => { const v = nextVisit.value.get(l.id); return v ? v.date + (v.time || '') : '9999' }

const flagFor = (l: any): { tone: string; text: string } | null => {
  if (isClosed(l) || l.deleted_at) return null
  const state = followState(l)
  if (state === 'overdue') return { tone: 'bad', text: 'Follow-up overdue' }
  if (state === 'today') return { tone: 'warn', text: 'Follow up today' }
  const v = nextVisit.value.get(l.id)
  if (v) return { tone: 'info', text: v.visit_type + ' ' + formatDay(v.date) + (v.time ? ', ' + formatTime(v.time) : '') }
  if (state === 'later') return { tone: 'muted', text: 'Follow up ' + formatWhen(l.next_follow_up_at) }
  return null
}

/** CSS hook for the coloured stage badge beside the name ("Site Visit" → "site-visit"). */
const stageKey = (stage?: string) => String(stage || 'New').toLowerCase().replace(/\s+/g, '-')

// Only real contact with the buyer goes under the name. System entries (form arrived, stage or owner changed)
// are skipped: when the lead arrived is already shown on the right.
const CONTACT_LABELS: Record<string, string> = { call: 'Call', whatsapp: 'WhatsApp', email: 'Email', note: 'Note' }
const NOTE_TYPES = ['note', 'call', 'whatsapp', 'email', 'manual']
/** Shown when there is no visit or follow-up flag: the last contact, e.g. "Call · No answer · 2h ago", else the stage. */
const lastContactText = (l: any): string => {
  const a = l.last_activity || l.recent_activity
  if (a && CONTACT_LABELS[a.type]) return [CONTACT_LABELS[a.type], a.outcome, a.at ? ago(a.at) : ''].filter(Boolean).join(' · ')
  if (a && a.type === 'stage') return 'Status: ' + (a.meta?.to || l.stage) + (a.at ? ' · ' + ago(a.at) : '')
  return l.stage && l.stage !== 'New' ? 'Status: ' + l.stage : ''
}
/** One short line of the latest note (or call note) so the team sees the context without opening the lead. */
const shortNote = (l: any): string => {
  const a = l.last_activity || l.recent_activity
  const body = (a && NOTE_TYPES.includes(a.type) ? a.body : '') || String(l.note || '').split('\n').find((line: string) => line.trim()) || ''
  const text = body.replace(/\s+/g, ' ').trim()
  return text.length > 90 ? text.slice(0, 89) + '…' : text
}

function hasCurrentActivity(l: any): boolean {
  if (!l || l.deleted_at) return false
  if (l.recent_activity || l.last_activity) return true
  if (l.stage && l.stage !== 'New') return true
  if (l.note && String(l.note).trim().length > 0) return true
  if (l.last_contacted_at || l.next_follow_up_at) return true
  if (l.updated_at && l.created_at) {
    const updated = new Date(l.updated_at).getTime()
    const created = new Date(l.created_at).getTime()
    if (updated - created > 60000) return true
  }
  return false
}

function activityTimeMs(l: any): number {
  if (!l) return 0
  const iso = l.recent_activity?.at || l.last_activity?.at || l.last_contacted_at || l.updated_at || l.created_at
  if (!iso) return 0
  const t = new Date(iso).getTime()
  return isNaN(t) ? 0 : t
}

const currentActivityCount = computed(() => openLeads.value.filter(hasCurrentActivity).length)

const toggleActivityFilter = () => {
  quick.value = quick.value === 'activity' ? 'all' : 'activity'
}

const openLeads = computed(() => leadsList.value.filter(l => !isClosed(l)))
const quickViews = computed(() => {
  const base = [
    { key: 'all' as const, label: isLeadAdmin.value ? 'All leads' : 'My leads', count: leadsList.value.length, alert: false },
    { key: 'activity' as const, label: 'Current Activity', count: currentActivityCount.value, alert: false },
    { key: 'followups' as const, label: 'Needs follow-up', count: openLeads.value.filter(l => ['overdue', 'today'].includes(followState(l))).length, alert: true },
    { key: 'visits' as const, label: 'Visits coming up', count: openLeads.value.filter(l => nextVisit.value.has(l.id)).length, alert: false },
  ]
  if (isLeadAdmin.value) {
    base.push(
      { key: 'unassigned' as const, label: 'Unassigned', count: openLeads.value.filter(l => !l.assigned_to).length, alert: false },
      { key: 'mine' as const, label: 'Assigned to me', count: openLeads.value.filter(l => l.assigned_to && l.assigned_to === me.value?.id).length, alert: false },
      { key: 'trash' as const, label: 'Trash (Deleted)', count: view.value === 'trash' ? leadsList.value.length : 0, alert: false }
    )
  }
  return base
})
const quickDropdownOptions = computed(() =>
  quickViews.value.map(q => ({
    value: q.key,
    label: q.label,
    count: q.count
  }))
)
function matchesQuick(l: any) {
  if (view.value === 'trash') return true
  switch (quick.value) {
    case 'activity': return !isClosed(l) && hasCurrentActivity(l)
    case 'followups': return !isClosed(l) && ['overdue', 'today'].includes(followState(l))
    case 'visits': return !isClosed(l) && nextVisit.value.has(l.id)
    case 'unassigned': return !isClosed(l) && !l.assigned_to
    case 'mine': return !!l.assigned_to && l.assigned_to === me.value?.id
    case 'trash': return true
    default: return true
  }
}

const detailTabs = computed(() => [
  { key: 'overview' as const, label: 'Overview', count: 0 },
  { key: 'activity' as const, label: 'Activity', count: crmDetail.value.activities.length },
  { key: 'visits' as const, label: 'Site visits', count: crmDetail.value.visits.filter(v => v.status === 'Confirmed').length }
])

async function loadOverview() {
  try {
    const data = await crm.overview()
    staff.value = data.staff
    upcomingVisits.value = data.visits
  } catch {
    // The list still works without owners and visit flags.
  }
}
async function loadDetail() {
  const id = selectedId.value
  if (!id || view.value === 'trash') { crmDetail.value = { activities: [], visits: [] }; return }
  crmLoading.value = true
  try {
    const data = await crm.detail(id)
    if (selectedId.value === id) crmDetail.value = data
  } catch {
    crmDetail.value = { activities: [], visits: [] }
  } finally {
    crmLoading.value = false
  }
}
const refreshAll = () => Promise.all([fetchLeads(view.value === 'trash', true), loadOverview(), loadDetail()])

const isRefreshing = ref(false)
const handleManualRefresh = async () => {
  if (isRefreshing.value) return
  isRefreshing.value = true
  try {
    await refreshAll()
    toast.success('Leads refreshed', 'Showing the latest buyer inquiries.')
  } catch {
    toast.error('Could not refresh', 'Check your network connection.')
  } finally {
    setTimeout(() => { isRefreshing.value = false }, 500)
  }
}

let autoRefreshTimer: ReturnType<typeof setInterval> | null = null
onMounted(() => {
  autoRefreshTimer = setInterval(() => {
    refreshAll()
  }, 120000)
})
onBeforeUnmount(() => {
  if (autoRefreshTimer) {
    clearInterval(autoRefreshTimer)
    autoRefreshTimer = null
  }
})

watch(selectedId, () => {
  tab.value = 'overview'
  pickedFollowUp.value = ''
  crmDetail.value = { activities: [], visits: [] }
  loadDetail()
})

// Copies the fields the CRM can change from the server's answer onto the lead shown in the list.
const syncLead = (lead: any, data: any) => {
  if (!lead || !data) return lead
  const preservedName = lead.name || data.name || 'Direct Buyer'
  return Object.assign(lead, {
    name: preservedName,
    status: data.status,
    stage: normalizeStage(data.status),
    lost_reason: data.lost_reason,
    assigned_to: data.assigned_to,
    next_follow_up_at: data.next_follow_up_at,
    last_contacted_at: data.last_contacted_at
  })
}

const assignLead = async (lead: any, event: Event) => {
  const select = event.target as HTMLSelectElement
  const previous = lead.assigned_to ?? ''
  try {
    syncLead(lead, await crm.assign(lead.id, select.value ? Number(select.value) : null))
    toast.info('Owner updated', lead.name + (lead.assigned_to ? ' is assigned.' : ' is unassigned.'))
    loadDetail()
  } catch (err: any) {
    select.value = String(previous)
    toast.error('Could not assign', err?.message || 'Try again.')
  }
}

const setFollowUp = async (lead: any, at: string | null) => {
  try {
    syncLead(lead, await crm.followUp(lead.id, at))
    toast.success(at ? 'Follow-up set' : 'Follow-up cleared', at ? followText(lead) : '')
    loadDetail()
  } catch (err: any) {
    toast.error('Could not save', err?.message || 'Try again.')
  }
}
const pickFollowUp = (lead: any) => {
  if (!pickedFollowUp.value) return
  const [date, time] = pickedFollowUp.value.split('T')
  pickedFollowUp.value = ''
  return setFollowUp(lead, dhakaIso(date, time))
}

// Stays open after a status change even if the active tab no longer lists the lead.
const selected = computed(() => leadsList.value.find(l => l.id === selectedId.value) || null)

const selectedVisits = computed<LeadVisit[]>(() => {
  if (!selected.value) return []
  const fromDetail = crmDetail.value.visits || []
  if (fromDetail.length) return fromDetail
  return upcomingVisits.value.filter(v => v.lead_id === selected.value.id)
})

const activeVisit = computed<LeadVisit | null>(() => {
  const list = selectedVisits.value
  if (!list.length) return null
  return list.find(v => v.status === 'Confirmed') || list.find(v => v.status === 'Completed') || list[0] || null
})

// Keep a lead open on wide screens; on phones the list stays first until a lead is tapped.
watch(filteredLeads, list => {
  if (!list.length) { selectedId.value = null; return }
  if (!leadsList.value.some(l => l.id === selectedId.value)) {
    selectedId.value = list[0].id
    detailOpen.value = false
  }
}, { immediate: true })

// Opening a different view (active/trash) or clearing the search starts from the first lead again.
watch([view, search, focus], () => {
  if (filteredLeads.value.length && !filteredLeads.value.some(l => l.id === selectedId.value)) {
    selectedId.value = filteredLeads.value[0].id
    detailOpen.value = false
  }
})

const openLead = (lead: any) => {
  selectedId.value = lead.id
  detailOpen.value = true
  // On phones the lead replaces the list, so start reading from its top.
  if (window.innerWidth <= 960) window.scrollTo({ top: 0, behavior: 'instant' })
}

/* ---------- Display helpers ---------- */

const dhaka = 'Asia/Dhaka'
// The API sends some timestamps as "2026-10-01 00:06:51" (UTC, no zone), which a browser would read as local time.
const toDate = (value: string) => new Date(/^\d{4}-\d\d-\d\d \d/.test(value) ? value.replace(' ', 'T') + 'Z' : value)
const fullDate = (iso?: string) => iso
  ? toDate(iso).toLocaleString('en-GB', { timeZone: dhaka, day: 'numeric', month: 'short', hour: 'numeric', minute: '2-digit', hour12: true })
  : ''
const ago = (iso?: string) => {
  if (!iso) return ''
  const minutes = Math.max(0, Math.round((Date.now() - toDate(iso).getTime()) / 60000))
  if (minutes < 1) return 'just now'
  if (minutes < 60) return `${minutes}m ago`
  if (minutes < 1440) return `${Math.floor(minutes / 60)}h ago`
  if (minutes < 10080) return `${Math.floor(minutes / 1440)}d ago`
  return toDate(iso).toLocaleDateString('en-GB', { timeZone: dhaka, day: 'numeric', month: 'short' })
}
const tierLabel = (tier?: string | null) => (tier ? tier.charAt(0) + tier.slice(1).toLowerCase() : 'Unscored')
const isFresh = (l: any) => l.stage === 'New' && !l.deleted_at

const whatsappLink = (l: any) => {
  const text = `আসসালামু আলাইকুম ${l.name}, গ্রাম বাংলা রিয়েল এস্টেট থেকে বলছি। "${l.property}" নিয়ে আপনার আগ্রহের বিষয়ে কথা বলতে চাই। এখন কি কথা বলা সম্ভব?`
  return `https://wa.me/${String(l.phone || '').replace(/[^0-9]/g, '')}?text=${encodeURIComponent(text)}`
}

const copyPhone = async (phone: string) => {
  try {
    await navigator.clipboard.writeText(phone)
    toast.success('Copied', `${phone} copied to the clipboard.`)
  } catch {
    toast.error('Could not copy', 'Select the number and copy it by hand.')
  }
}

// "partial (2 of 5 questions)" → plain words for the team. Buyers who left after the contact step have not answered everything.
const surveyText = (raw?: string) => {
  const m = String(raw || '').match(/^(complete|partial) \((\d+) of (\d+)/)
  if (!m) return ''
  return m[1] === 'complete' ? `All ${m[3]} questions answered` : `Answered ${m[2]} of ${m[3]} questions. Ask the rest on the call.`
}

type Row = { label: string; value: string; to?: string }
const detailSections = computed(() => {
  const l = selected.value
  if (!l) return []
  const surveyed = !!l.form_version
  const formAd = l.meta_lead_id ? (l.meta_platform === 'instagram' ? 'Instagram lead form' : 'Facebook lead form') : ''
  const source = formAd || (l.utm_source === 'crm_manual'
    ? `Logged by the team · ${l.utm_medium || 'manual'}`
    : (l.utm_source === 'facebook' || l.parsed?.fbclick ? 'Facebook ad' : (l.utm_source || 'Website, no ad')))
  const sections: { title: string; rows: Row[] }[] = [
    {
      title: 'What the buyer told us',
      rows: [
        surveyed ? { label: 'Plans to use it for', value: say(l.buyer_category) } : { label: 'Buyer type', value: l.lead_type || '' },
        { label: 'Wants to buy', value: say(l.investment_readiness) },
        { label: 'Budget', value: say(l.budget_range) },
        { label: 'Payment', value: say(l.parsed?.payment) },
        { label: 'Lives', value: say(l.parsed?.lives) },
        { label: 'Form answers', value: surveyText(l.parsed?.survey) }
      ]
    },
    {
      title: 'How to reach them',
      rows: [
        { label: 'Prefers', value: l.preferred_contact || '' },
        { label: 'Best time', value: l.callback_time || l.parsed?.time || '' },
        { label: 'Suggested next step', value: l.next_step || '' },
        { label: 'Permission to contact', value: l.contact_consented_at ? `Given on ${fullDate(l.contact_consented_at)}` : (surveyed ? 'Not recorded' : '') }
      ]
    },
    {
      title: 'Property',
      rows: [
        { label: 'Interested in', value: l.property, to: l.property_id ? `/properties/${l.property_id}` : undefined },
        { label: 'Price shown to buyer', value: l.parsed?.price || '' }
      ]
    },
    {
      title: 'Where they came from',
      rows: [
        { label: 'Source', value: source },
        { label: 'Campaign', value: l.utm_campaign || '' },
        { label: 'Ad set', value: l.utm_term || '' },
        { label: 'Ad', value: l.utm_content || '' },
        { label: 'Lead form', value: l.meta_form_name || '' },
        { label: 'Opened from', value: ctaLabels[l.parsed?.cta] || l.parsed?.cta || '' }
      ]
    }
  ]
  return sections
    .map(s => ({ ...s, rows: s.rows.filter(r => r.value) }))
    .filter(s => s.rows.length)
})

/* ---------- Actions ---------- */

const openFromInquiry = async (id: number) => {
  showAddLeadModal.value = false
  if (view.value === 'trash') { view.value = 'active'; await fetchLeads(false) }
  clearFilters()
  const lead = leadsList.value.find(l => l.id === id)
  if (lead) openLead(lead)
}

const onInquirySaved = async (lead: any) => {
  showAddLeadModal.value = false
  toast.success('Lead saved', `${lead.name} is in the CRM.`)
  view.value = 'active'
  await fetchLeads(false)
  clearFilters()
  const fresh = leadsList.value.find(l => l.id === lead.id)
  if (fresh) openLead(fresh)
}

const setStage = async (lead: any, newStage: string) => {
  if (lead.stage === newStage) return
  if (newStage === 'Lost') {
    lostChoice.value = lostReasons[0]
    lostOther.value = ''
    lostTarget.value = lead
    return
  }
  await applyStage(lead, newStage)
}

const applyStage = async (lead: any, stage: string, reason?: string) => {
  const previous = lead.stage
  const previousName = lead.name
  lead.stage = stage
  try {
    const updated = await crm.setStage(lead.id, stage, reason)
    syncLead(lead, updated)
    if (!lead.name && previousName) {
      lead.name = previousName
    }
    toast.info('Status updated', (lead.name || 'Lead') + ' is now "' + stage + '".')
    loadDetail()
  } catch (err: any) {
    lead.stage = previous
    toast.error('Update Failed', err?.message || 'Could not update lead stage.')
  }
}

const confirmLost = async () => {
  const lead = lostTarget.value
  if (!lead) return
  const reason = lostChoice.value === 'Other' ? lostOther.value : lostChoice.value
  lostTarget.value = null
  await applyStage(lead, 'Lost', reason)
}

const promptDeleteLead = (lead: any) => {
  deleteLeadTarget.value = lead
}

const executeDeleteLead = async () => {
  if (!deleteLeadTarget.value) return
  const target = deleteLeadTarget.value
  isDeleting.value = true
  try {
    const res = await fetch(useApiUrl(`/leads/${target.id}`), {
      method: 'DELETE',
      headers: {
        Authorization: `Bearer ${token.value}`
      }
    })
    if (!res.ok) {
      const errData = await res.json().catch(() => ({}))
      throw new Error(errData?.message || 'Failed to delete lead from server')
    }

    leadsList.value = leadsList.value.filter(l => l.id !== target.id)
    detailOpen.value = false
    toast.info('Lead Removed', `Lead inquiry from ${target.name} has been moved to the Trash.`)
    deleteLeadTarget.value = null
  } catch (err: any) {
    console.error('Delete lead error:', err)
    toast.error('Deletion Failed', err?.message || 'Could not delete lead inquiry.')
  } finally {
    isDeleting.value = false
  }
}

const restoreLead = async (lead: any) => {
  try {
    const res = await fetch(useApiUrl(`/leads/${lead.id}/restore`), {
      method: 'POST',
      headers: {
        Authorization: `Bearer ${token.value}`
      }
    })
    if (!res.ok) {
      const errData = await res.json().catch(() => ({}))
      throw new Error(errData?.message || 'Failed to restore lead')
    }

    leadsList.value = leadsList.value.filter(l => l.id !== lead.id)
    detailOpen.value = false
    toast.success('Lead Restored', `Lead inquiry from ${lead.name} has been restored to active pipeline.`)
  } catch (err: any) {
    console.error('Restore lead error:', err)
    toast.error('Restore Failed', err?.message || 'Could not restore lead inquiry.')
  }
}
</script>

<style scoped>
.ld { color: var(--admin-text-primary); --ld-danger: #F87171; --ld-hot: #F97316; --ld-warm: #FBBF24; --ld-cold: #7DA0C4; }
.admin-theme-light .ld { --ld-danger: #B91C1C; --ld-hot: #C2410C; --ld-warm: #A16207; --ld-cold: #3B6A99; }

/* Header & Toolbar */
.ld-header { margin-bottom: 12px; }
.ld-subtitle { margin-top: 2px; font-size: 0.86rem; }
.ld-toolbar { display: flex; flex-direction: column; gap: 8px; margin-bottom: 10px; }
.ld-toolbar-row { display: flex; align-items: center; gap: 8px; width: 100%; }
.ld-searchrow { flex: 0 1 240px; min-width: 170px; }
.ld-search { position: relative; flex: 1 1 auto; min-width: 0; display: flex; align-items: center; }
.ld-search svg { position: absolute; left: 12px; color: var(--admin-text-muted); pointer-events: none; }
.ld-search input { width: 100%; min-height: 38px; padding: 6px 12px 6px 36px; border-radius: 10px; border: 1px solid var(--admin-border-subtle); background: var(--admin-bg-surface); color: inherit; font: inherit; font-size: 0.86rem; }
.ld-search input:focus { outline: 2px solid var(--admin-text-gold); outline-offset: 1px; border-color: transparent; }
.ld-filters { display: flex; align-items: center; gap: 6px; flex: 1 1 auto; min-width: 0; }
.ld-filters :deep(.cf-dropdown) { flex: 1 1 0; min-width: 105px; max-width: 175px; }
.ld-count { margin-left: 4px; color: var(--admin-text-muted); font-weight: 500; }
.ld-trash-note { margin: 0; font-size: 0.88rem; color: var(--admin-text-secondary); }
.ld-trash-banner { display: flex; align-items: center; justify-content: space-between; gap: 8px; padding: 6px 12px; border-radius: 8px; background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.25); font-size: 0.82rem; color: var(--admin-text-primary); }
.ld-trash-banner-info { display: flex; align-items: center; gap: 6px; }
.ld-trash-banner-info svg { color: var(--ld-danger); flex: none; }
.ld button:focus-visible, .ld a:focus-visible, .ld select:focus-visible { outline: 2px solid var(--admin-text-gold); outline-offset: 2px; }

/* Flags on list rows */
.ld-flag { font-size: 0.76rem; font-weight: 700; color: var(--admin-text-muted); }
.ld-flag[data-tone='bad'] { color: var(--ld-danger); }
.ld-flag[data-tone='warn'] { color: var(--ld-warm); }
.ld-flag[data-tone='info'] { color: #2563EB; }

/* Owner, follow-up and sections inside a lead */
.ld-lost { margin: 10px 0 0; font-size: 0.88rem; color: var(--ld-danger); font-weight: 600; }
.ld-work { display: flex; flex-wrap: wrap; align-items: flex-start; gap: 16px 24px; margin-top: 18px; padding: 14px 0 0; border-top: 1px solid var(--admin-border-subtle); }
.ld-work-col { display: flex; flex-direction: column; gap: 6px; min-width: 0; }
.ld-work-col--assign { flex: 0 1 200px; min-width: 150px; }
.ld-work-col--follow { flex: 1 1 280px; min-width: 220px; }
.ld-work-header { display: flex; align-items: center; gap: 8px; min-height: 22px; }
.ld-work-label { font-size: 0.82rem; font-weight: 600; color: var(--admin-text-secondary); }
.ld-assigned-readonly { min-height: 38px; width: 100%; display: flex; align-items: center; padding: 6px 12px; border-radius: 10px; border: 1px solid var(--admin-border-subtle); background: var(--admin-bg-surface-alt); font-size: 0.88rem; font-weight: 600; color: var(--admin-text-primary); }
.ld-follow-now { margin: 0; font-size: 0.82rem; font-weight: 700; }
.ld-follow-now.overdue { color: var(--ld-danger); }
.ld-follow-now.today { color: var(--ld-warm); }
.ld-follow-now.none { color: var(--admin-text-muted); font-weight: 500; }
.ld-work-control { min-height: 38px; display: flex; align-items: center; }
.ld-work-select { width: 100%; min-height: 38px; font-size: 0.86rem; }
.ld-chips { display: flex; flex-wrap: wrap; align-items: center; gap: 6px; }
.ld-chips button { min-height: 34px; padding: 2px 12px; border-radius: 999px; border: 1px solid var(--admin-border-hover); background: transparent; color: var(--admin-text-secondary); font: inherit; font-size: 0.82rem; font-weight: 600; cursor: pointer; }
.ld-chips button:hover { background: var(--admin-chip-bg); }
.ld-chips .ld-clear { border-color: transparent; color: var(--admin-text-muted); text-decoration: underline; text-underline-offset: 3px; }
.ld-pick input { min-height: 34px; padding: 2px 8px; border-radius: 8px; border: 1px solid var(--admin-border-hover); background: var(--admin-bg-surface); color: inherit; font: inherit; font-size: 0.82rem; }
.ld-sr { position: absolute; width: 1px; height: 1px; overflow: hidden; clip: rect(0 0 0 0); white-space: nowrap; }
.ld-tabs-detail { display: flex; gap: 2px; margin: 24px 0 18px; border-bottom: 1px solid var(--admin-border-subtle); overflow-x: auto; }
.ld-tabs-detail button { display: inline-flex; align-items: center; gap: 6px; min-height: 44px; padding: 8px 16px; border: 0; border-bottom: 3px solid transparent; background: transparent; color: var(--admin-text-secondary); font: inherit; font-size: 0.92rem; font-weight: 600; cursor: pointer; white-space: nowrap; }
.ld-tabs-detail button:hover { color: var(--admin-text-primary); }
.ld-tabs-detail button.on { color: var(--admin-text-primary); border-bottom-color: var(--admin-text-gold); }
.ld-detail .ld-section:first-of-type { margin-top: 0; padding-top: 0; border-top: 0; }

/* Empty / loading */
.ld-empty { text-align: center; padding: 56px 24px; color: var(--admin-text-muted); border: 1px dashed var(--admin-border-hover); border-radius: 14px; }
.ld-empty strong { display: block; color: var(--admin-text-primary); font-size: 1.05rem; margin-bottom: 6px; }
.ld-empty p { max-width: 420px; margin: 0 auto 16px; font-size: 0.9rem; line-height: 1.55; }

/* Split layout */
.ld-split { display: grid; grid-template-columns: minmax(300px, 380px) 1fr; border: 1px solid var(--admin-border-subtle); border-radius: 14px; background: var(--admin-bg-surface); overflow: hidden; align-items: start; }
.ld-list { list-style: none; margin: 0; padding: 0; max-height: calc(100vh - 290px); min-height: 360px; overflow-y: auto; border-right: 1px solid var(--admin-border-subtle); }
.ld-item { display: flex; align-items: stretch; border-bottom: 1px solid var(--admin-border-subtle); }
.ld-item:hover { background: var(--admin-table-row-hover); }
.ld-item.active { background: color-mix(in srgb, var(--admin-text-emerald) 12%, transparent); }
.ld-row { position: relative; flex: 1; min-width: 0; display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; padding: 12px 8px 12px 18px; border: 0; background: transparent; color: inherit; font: inherit; text-align: left; cursor: pointer; }
.ld-row::before { content: ''; position: absolute; left: 0; top: 0; bottom: 0; width: 4px; background: var(--tier, transparent); }
.ld-row-call { flex: none; display: flex; align-items: center; justify-content: center; width: 52px; color: #C2530F; border-left: 1px solid var(--admin-border-subtle); }
.ld-row-call:hover { background: color-mix(in srgb, #C2530F 12%, transparent); }
.admin-theme-light .ld-row-call { color: #A8460C; }

/* Highlighted answers & site visit info */
.ld-hl-row { display: flex; flex-wrap: wrap; gap: 4px; margin-top: 2px; }
.ld-hl-chip { max-width: 100%; padding: 2px 8px; border-radius: 6px; background: rgba(251, 191, 36, 0.16); border: 1px solid rgba(251, 191, 36, 0.45); color: var(--admin-text-primary); font-size: 0.76rem; font-weight: 600; line-height: 1.5; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.ld-hl-card { position: relative; display: grid; gap: 8px; margin-top: 12px; padding: 10px 14px; border-radius: 10px; background: rgba(251, 191, 36, 0.08); border: 1px solid rgba(251, 191, 36, 0.35); border-left-width: 3px; }
.ld-hl-card.with-visit { grid-template-columns: minmax(0, 1fr) minmax(0, 1.15fr); gap: 14px; align-items: start; }
.ld-hl-left { display: flex; flex-direction: column; gap: 5px; padding-right: 70px; }
.ld-hl-visit-side { display: flex; flex-direction: column; gap: 8px; padding: 10px 12px; border-radius: 10px; background: var(--admin-bg-surface); border: 1px solid var(--admin-border-subtle); box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05); }
.ld-hl-card:not(.with-visit) .ld-hl-visit-side { background: transparent; border: 0; padding: 0; box-shadow: none; }
.ld-hl-visit-badge-row { display: flex; align-items: center; justify-content: space-between; gap: 8px; }
.ld-hl-visit-badge { display: inline-flex; align-items: center; gap: 6px; padding: 2px 9px; border-radius: 999px; font-size: 0.74rem; font-weight: 700; background: rgba(14, 165, 233, 0.15); color: #0284C7; }
.ld-hl-visit-badge[data-status='Confirmed'] { background: rgba(16, 185, 129, 0.16); color: #059669; }
.ld-hl-visit-badge[data-status='Completed'] { background: rgba(13, 148, 136, 0.18); color: #0D9488; }
.ld-hl-visit-badge[data-status='Cancelled'], .ld-hl-visit-badge[data-status='No-show'] { background: rgba(239, 68, 68, 0.15); color: #DC2626; }
.ld-hl-visit-badge[data-status='Rescheduled'] { background: rgba(245, 158, 11, 0.18); color: #B45309; }
.ld-hl-visit-link { border: 0; background: transparent; color: var(--admin-text-emerald); font: inherit; font-size: 0.76rem; font-weight: 600; cursor: pointer; text-decoration: underline; text-underline-offset: 2px; }
.ld-hl-visit-link:hover { opacity: 0.85; }
.ld-hl-visit-details { display: flex; flex-direction: column; gap: 4px; }
.ld-hl-visit-time { font-size: 0.92rem; color: var(--admin-text-primary); }
.ld-hl-visit-meta { margin: 0; display: flex; align-items: center; gap: 6px; font-size: 0.8rem; color: var(--admin-text-secondary); }
.ld-hl-visit-meta svg { flex: none; opacity: 0.8; }
.ld-hl-visit-outcome { margin: 3px 0 0; padding-top: 4px; border-top: 1px dashed var(--admin-border-subtle); font-size: 0.8rem; color: var(--admin-text-secondary); line-height: 1.35; }
.ld-hl-item { display: flex; flex-wrap: wrap; align-items: baseline; gap: 4px 8px; }
.ld-hl-label { font-size: 0.74rem; color: var(--admin-text-secondary); line-height: 1.35; }
.ld-hl-value { font-size: 0.88rem; font-weight: 700; line-height: 1.35; color: var(--admin-text-primary); overflow-wrap: anywhere; }
.ld-hl-edit-corner { position: absolute; top: 8px; right: 10px; display: inline-flex; align-items: center; gap: 4px; padding: 2px 7px; border-radius: 6px; border: 1px solid rgba(251, 191, 36, 0.4); background: rgba(251, 191, 36, 0.12); color: var(--admin-text-primary); font: inherit; font-size: 0.72rem; font-weight: 600; cursor: pointer; transition: all 0.15s ease; }
.ld-hl-edit-corner:hover { background: rgba(251, 191, 36, 0.25); }
/* Form answers: question on top, the buyer's answer under it, one after another */
.ld-answers h3 { display: flex; flex-wrap: wrap; align-items: baseline; gap: 4px 10px; }
.ld-answers-form { font-size: 0.78rem; font-weight: 500; color: var(--admin-text-muted); }
.ld-qa { list-style: none; margin: 4px 0 0; padding: 0; display: grid; gap: 8px; }
.ld-qa li { display: flex; flex-direction: column; gap: 4px; padding: 12px 14px; border-radius: 10px; background: var(--admin-bg-surface-alt); border: 1px solid var(--admin-border-subtle); }
.ld-qa li.hl { background: rgba(251, 191, 36, 0.1); border-color: rgba(251, 191, 36, 0.45); border-left: 4px solid #D9A21B; }
.ld-q { display: flex; align-items: flex-start; gap: 8px; font-size: 0.84rem; line-height: 1.45; color: var(--admin-text-secondary); }
.ld-qn { flex: none; display: inline-flex; align-items: center; justify-content: center; min-width: 20px; height: 20px; border-radius: 50%; background: var(--admin-chip-bg); color: var(--admin-text-secondary); font-size: 0.72rem; font-weight: 700; }
.ld-a { padding-left: 28px; font-size: 1rem; line-height: 1.45; overflow-wrap: anywhere; }
@media (min-width: 1280px) {
  .ld-qa li { display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); align-items: baseline; gap: 4px 16px; }
  .ld-a { padding-left: 0; }
}
.ld-hl-btn { display: inline-flex; align-items: center; gap: 6px; min-height: 40px; padding: 6px 14px; border-radius: 10px; border: 1px solid rgba(251, 191, 36, 0.5); background: transparent; color: var(--admin-text-primary); font: inherit; font-size: 0.88rem; font-weight: 600; cursor: pointer; }
.ld-hl-btn svg { color: #D9A21B; }
.ld-hl-btn:hover { background: rgba(251, 191, 36, 0.12); }
.ld-refresh-btn { border-color: var(--admin-border-subtle); color: var(--admin-text-secondary); }
.ld-refresh-btn svg { color: var(--admin-text-secondary); }
.ld-refresh-btn:hover { background: var(--admin-chip-bg); color: var(--admin-text-primary); border-color: var(--admin-border-hover); }
.ld-refresh-btn:hover svg { color: var(--admin-text-primary); }
.ld-refresh-btn.is-refreshing { opacity: 0.8; pointer-events: none; }
@keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
.spin-anim { animation: spin 0.75s linear infinite; }
.ld-activity-filter-btn { display: inline-flex; align-items: center; justify-content: center; gap: 6px; min-height: 40px; padding: 6px 12px; border-radius: 10px; border: 1px solid var(--admin-border-subtle); background: var(--admin-bg-surface); color: var(--admin-text-secondary); font: inherit; font-size: 0.83rem; font-weight: 600; cursor: pointer; white-space: nowrap; transition: all 0.15s cubic-bezier(0.16, 1, 0.3, 1); }
.ld-activity-filter-btn:hover { background: var(--admin-chip-bg); color: var(--admin-text-primary); border-color: var(--admin-border-hover); }
.ld-activity-filter-btn.is-active { background: rgba(14, 165, 233, 0.14); border-color: #0284C7; color: #0284C7; font-weight: 700; }
.admin-theme-light .ld-activity-filter-btn.is-active { background: rgba(2, 132, 199, 0.1); border-color: #0284C7; color: #0369A1; }
.ld-act-count { display: inline-flex; align-items: center; justify-content: center; min-width: 18px; height: 18px; padding: 0 5px; border-radius: 999px; font-size: 0.7rem; font-weight: 700; background: rgba(14, 165, 233, 0.2); color: #0284C7; }
.ld-activity-filter-btn.is-active .ld-act-count { background: #0284C7; color: #fff; }
.ld-reset-btn { display: inline-flex; align-items: center; justify-content: center; gap: 6px; min-height: 40px; padding: 6px 12px; border-radius: 10px; border: 1px solid rgba(239, 68, 68, 0.45); background: rgba(239, 68, 68, 0.1); color: #EF4444; font: inherit; font-size: 0.83rem; font-weight: 700; cursor: pointer; white-space: nowrap; transition: all 0.15s cubic-bezier(0.16, 1, 0.3, 1); }
.ld-reset-btn:hover { background: rgba(239, 68, 68, 0.2); border-color: rgba(239, 68, 68, 0.7); color: #DC2626; }
.admin-header-actions { display: flex; flex-wrap: wrap; gap: 8px; }

/* Search, voice and filters */
.ld-searchrow { display: flex; gap: 8px; flex: 1 1 320px; max-width: 460px; }
.ld-search input::-webkit-search-cancel-button { cursor: pointer; }
.ld-search:has(.ld-mic) input { padding-right: 92px; }
.ld-lang, .ld-mic { position: absolute; display: flex; align-items: center; justify-content: center; border: 0; font: inherit; cursor: pointer; }
.ld-lang { right: 48px; min-width: 38px; height: 30px; padding: 0 6px; border-radius: 8px; background: var(--admin-chip-bg); color: var(--admin-text-secondary); font-size: 0.74rem; font-weight: 700; }
.ld-mic { right: 4px; width: 40px; height: 34px; border-radius: 8px; background: transparent; color: var(--admin-text-secondary); }
.ld-mic:hover { background: var(--admin-chip-bg); color: var(--admin-text-primary); }
.ld-mic.on { background: #DC2626; color: #fff; animation: ld-pulse 1.2s ease-in-out infinite; }
@keyframes ld-pulse { 0%, 100% { box-shadow: 0 0 0 0 rgba(220, 38, 38, 0.5); } 50% { box-shadow: 0 0 0 8px rgba(220, 38, 38, 0); } }
@media (prefers-reduced-motion: reduce) { .ld-mic.on { animation: none; } }
.ld-voice-status { flex-basis: 100%; margin: -4px 0 0; font-size: 0.84rem; color: var(--admin-text-secondary); }
.ld-filter-btn { display: none; flex: none; align-items: center; justify-content: center; width: 44px; min-height: 44px; border-radius: 10px; border: 1px solid var(--admin-border-subtle); background: var(--admin-bg-surface); color: var(--admin-text-secondary); cursor: pointer; }
.ld-filter-btn.on { border-color: var(--admin-text-gold); color: var(--admin-text-primary); }
.ld-fab { display: none; }
.ld-backbar-name { display: none; }
.ld-main { min-width: 0; display: flex; flex-direction: column; gap: 3px; }
.ld-name { display: flex; align-items: center; gap: 8px; min-width: 0; font-weight: 700; font-size: 0.98rem; color: var(--admin-text-primary); line-height: 1.35; }
.ld-name-text { min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.ld-badges-row { display: flex; align-items: center; flex-wrap: wrap; gap: 6px; margin: 2px 0 3px; min-width: 0; max-width: 100%; }
/* Stage badge beside the name */
.ld-stage { flex: none; white-space: nowrap; padding: 1px 8px; border-radius: 999px; font-size: 0.7rem; font-weight: 700; line-height: 1.6; letter-spacing: 0.01em; background: var(--admin-chip-bg); color: var(--admin-text-secondary); }
.ld-stage[data-stage='new'] { background: rgba(16, 185, 129, 0.15); color: #059669; }
.ld-stage[data-stage='contacted'] { background: rgba(59, 130, 246, 0.15); color: #2563EB; }
.ld-stage[data-stage='qualified'] { background: rgba(139, 92, 246, 0.16); color: #7C3AED; }
.ld-stage[data-stage='site-visit'] { background: rgba(14, 165, 233, 0.16); color: #0284C7; }
.ld-stage[data-stage='visited'] { background: rgba(13, 148, 136, 0.18); color: #0D9488; }
.ld-stage[data-stage='negotiation'] { background: rgba(245, 158, 11, 0.18); color: #B45309; }
.ld-stage[data-stage='converted'] { background: #059669; color: #fff; }
.ld-stage[data-stage='lost'] { background: rgba(239, 68, 68, 0.15); color: #DC2626; }
.ld-assigned-tag { display: inline-flex; align-items: center; gap: 4px; padding: 1px 7px; border-radius: 6px; font-size: 0.68rem; font-weight: 600; background: var(--admin-chip-bg); color: var(--admin-text-secondary); border: 1px solid var(--admin-border-subtle); flex: 0 1 auto; min-width: 0; max-width: none; white-space: nowrap; }
.ld-assigned-tag svg { opacity: 0.7; flex: none; }
.ld-assigned-text { min-width: 0; white-space: nowrap; }

/* Staff banner */
.ld-staff-banner { display: flex; align-items: center; justify-content: space-between; gap: 8px; padding: 6px 12px; border-radius: 8px; background: rgba(11, 122, 88, 0.1); border: 1px solid rgba(11, 122, 88, 0.25); font-size: 0.82rem; color: var(--admin-text-primary); }
.ld-staff-banner-info { display: flex; align-items: center; gap: 6px; }
.ld-staff-banner-clear { border: 0; background: transparent; color: var(--admin-text-emerald); font: inherit; font-size: 0.78rem; font-weight: 600; cursor: pointer; text-decoration: underline; }

/* List wrapper, selection & bulk actions */
.ld-list-wrap { display: flex; flex-direction: column; min-width: 0; }
.ld-list-header { display: flex; align-items: center; justify-content: space-between; gap: 8px; padding: 10px 14px; background: var(--admin-bg-surface-alt); border-bottom: 1px solid var(--admin-border-subtle); min-height: 44px; }
.ld-select-all { display: inline-flex; align-items: center; gap: 8px; font-size: 0.82rem; font-weight: 600; color: var(--admin-text-secondary); cursor: pointer; user-select: none; }
.ld-bulk-bar { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
.ld-bulk-select { font-size: 0.82rem; padding: 4px 8px; min-height: 32px; border-radius: 8px; }
.ld-bulk-btn { min-height: 32px; padding: 4px 12px; font-size: 0.82rem; font-weight: 700; border-radius: 8px; }
.ld-bulk-cancel { border: 0; background: transparent; color: var(--admin-text-muted); font-size: 1rem; cursor: pointer; padding: 2px 6px; }
.ld-bulk-cancel:hover { color: var(--admin-text-primary); }
.ld-check-col { display: flex; align-items: center; justify-content: center; width: 44px; padding-left: 12px; cursor: pointer; flex: none; }
.ld-checkbox { width: 17px; height: 17px; accent-color: #0B7A58; cursor: pointer; border-radius: 4px; }
.ld-item.checked { background: rgba(16, 185, 129, 0.08); }
.ld-note-line { font-size: 0.78rem; font-style: italic; color: var(--admin-text-muted); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.ld-new { flex: none; width: 8px; height: 8px; border-radius: 50%; background: var(--admin-text-emerald); }
.ld-prop { font-size: 0.82rem; color: var(--admin-text-muted); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.ld-side { flex: none; display: flex; flex-direction: column; align-items: flex-end; gap: 4px; font-size: 0.76rem; color: var(--admin-text-muted); padding-top: 1px; }
[data-tier='HOT'] { --tier: var(--ld-hot); }
[data-tier='WARM'] { --tier: var(--ld-warm); }
[data-tier='COLD'] { --tier: var(--ld-cold); }
.ld-tier { display: inline-flex; align-items: center; gap: 5px; font-size: 0.76rem; font-weight: 700; color: var(--tier, var(--admin-text-muted)); line-height: 1.35; white-space: nowrap; }
.ld-tier::before { content: ''; width: 7px; height: 7px; border-radius: 50%; background: var(--tier, var(--admin-text-muted)); opacity: 0.9; }
.ld-tier--big { font-size: 0.88rem; padding: 4px 12px; border-radius: 999px; background: color-mix(in srgb, var(--tier, #8E9B8F) 16%, transparent); }

/* Detail */
.ld-detail { padding: 24px 28px 28px; min-width: 0; max-height: calc(100vh - 290px); min-height: 360px; overflow-y: auto; }
.ld-back { display: none; align-items: center; gap: 4px; min-height: 44px; margin: -8px 0 8px -8px; padding: 8px; border: 0; background: transparent; color: var(--admin-text-secondary); font: inherit; font-weight: 600; cursor: pointer; }
.ld-head-top { display: flex; align-items: center; flex-wrap: wrap; gap: 10px 14px; }
.ld-head h2 { margin: 0; font-size: 1.5rem; font-weight: 800; line-height: 1.2; }
.ld-phone { display: flex; align-items: center; gap: 10px; margin-top: 8px; font-size: 1.1rem; }
.ld-copy { min-height: 32px; padding: 2px 12px; border-radius: 8px; border: 1px solid var(--admin-border-hover); background: transparent; color: var(--admin-text-secondary); font: inherit; font-size: 0.8rem; font-weight: 600; cursor: pointer; }
.ld-copy:hover { background: var(--admin-chip-bg); color: var(--admin-text-primary); }
.ld-received { margin: 6px 0 0; font-size: 0.84rem; color: var(--admin-text-muted); }
.ld-deleted { color: var(--ld-danger); font-weight: 700; }

.ld-actions { display: flex; flex-wrap: wrap; gap: 10px; margin: 20px 0 0; }
.ld-act { display: inline-flex; align-items: center; justify-content: center; gap: 8px; min-height: 44px; padding: 8px 20px; border-radius: 10px; border: 1px solid var(--admin-border-hover); color: var(--admin-text-primary); font-weight: 700; font-size: 0.92rem; text-decoration: none; }
.ld-act:hover { background: var(--admin-chip-bg); }
.ld-act--call, .ld-act--wa { color: #fff; }
.ld-act--call { background: #C2530F; border-color: #C2530F; }
.ld-act--call:hover { background: #A8460C; }
.ld-act--wa { background: #178A45; border-color: #178A45; }
.ld-act--wa:hover { background: #126F37; }

.ld-status { display: flex; flex-wrap: wrap; align-items: center; gap: 10px 14px; margin-top: 20px; }
.ld-label { font-size: 0.84rem; font-weight: 600; color: var(--admin-text-secondary); }
.ld-seg { display: inline-flex; flex-wrap: wrap; border: 1px solid var(--admin-border-hover); border-radius: 10px; overflow: hidden; }
.ld-seg button { min-height: 40px; padding: 6px 16px; border: 0; border-right: 1px solid var(--admin-border-subtle); background: transparent; color: var(--admin-text-secondary); font: inherit; font-size: 0.86rem; font-weight: 600; cursor: pointer; }
.ld-seg button:last-child { border-right: 0; }
.ld-seg button:hover { background: var(--admin-chip-bg); }
.ld-seg button.on { background: #0B7A58; color: #fff; }
.ld-muted { font-size: 0.82rem; color: var(--admin-text-muted); }

.ld-section { margin-top: 26px; padding-top: 18px; border-top: 1px solid var(--admin-border-subtle); }
.ld-section h3 { margin: 0 0 8px; font-size: 0.95rem; font-weight: 700; color: var(--admin-text-secondary); }
.ld-section dl { margin: 0; }
.ld-section dl > div { display: grid; grid-template-columns: 170px 1fr; gap: 12px; padding: 7px 0; font-size: 0.92rem; line-height: 1.5; }
.ld-section dt { color: var(--admin-text-muted); }
.ld-section dd { margin: 0; overflow-wrap: anywhere; }
.ld-section dd a { color: var(--admin-text-emerald); }
.ld-note { margin: 0; padding: 12px 14px; border-radius: 10px; background: var(--admin-bg-surface-alt); border: 1px solid var(--admin-border-subtle); font-size: 0.9rem; line-height: 1.6; white-space: pre-line; overflow-wrap: anywhere; }

.ld-foot { margin-top: 30px; padding-top: 16px; border-top: 1px solid var(--admin-border-subtle); }
.ld-delete { min-height: 40px; padding: 4px 0; border: 0; background: transparent; color: var(--ld-danger); font: inherit; font-size: 0.86rem; font-weight: 600; text-decoration: underline; text-underline-offset: 3px; cursor: pointer; }

/* Phones and narrow tablets: the list first, the lead opens full screen */
@media (max-width: 960px) {
  .is-hidden-mobile { display: none !important; }
  .ld-split { display: block; border-radius: 12px; }
  .ld-list { max-height: none; border-right: 0; }
  .ld-detail { display: none; }
  .ld-split.is-detail { border: 0; background: transparent; overflow: visible; }
  .ld-split.is-detail .ld-list { display: none; }
  .ld-split.is-detail .ld-detail { display: block; max-height: none; min-height: 0; padding: 0 2px calc(96px + env(safe-area-inset-bottom)); overflow: visible; }
  .ld-back { display: inline-flex; margin: 0; }

  /* Search stays in reach while scrolling the list */
  .ld-toolbar { position: sticky; top: var(--ld-sticky-top, 0px); z-index: 30; margin: 0 -16px 8px; padding: 8px 16px; gap: 6px; background: var(--admin-bg-base, var(--admin-bg-surface)); border-bottom: 1px solid var(--admin-border-subtle); }
  .ld-toolbar-row { flex-direction: column; align-items: stretch; gap: 6px; }
  .ld-searchrow { flex: none; width: 100%; max-width: none; }
  .ld-filters { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 5px; width: 100%; }
  .ld-filters :deep(.cf-dropdown) { width: 100%; max-width: none; min-width: 0; }
  .ld-activity-filter-btn, .ld-reset-btn { min-height: 38px; padding: 4px 8px; font-size: 0.78rem; width: 100%; }
  .ld-filter-btn { display: none !important; }
  .ld-hl-card.with-visit { grid-template-columns: 1fr; gap: 12px; }
  .ld-hl-card.with-visit .ld-hl-visit-side { border-top: 1px solid rgba(251, 191, 36, 0.35); }
  .ld-list-header { display: none !important; }
  .ld-check-col { display: none !important; }
  .ld-row { padding: 12px 6px 12px 6px; }
  .ld-name { font-size: 1rem; }
  .ld-row-call { width: 56px; }

  /* Lead screen: a bar to go back, the key answers first, call buttons always under the thumb */
  .ld-backbar { position: sticky; top: var(--ld-sticky-top, 0px); z-index: 30; display: flex; align-items: center; gap: 8px; margin: 0 -16px 10px; padding: 4px 12px; background: var(--admin-bg-base, var(--admin-bg-surface)); border-bottom: 1px solid var(--admin-border-subtle); }
  .ld-backbar-name { display: block; min-width: 0; font-weight: 700; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
  .ld-head h2 { font-size: 1.3rem; }
  .ld-actions { position: fixed; left: 0; right: 0; bottom: 0; z-index: 60; margin: 0; padding: 10px 12px calc(10px + env(safe-area-inset-bottom)); gap: 8px; background: var(--admin-bg-surface); border-top: 1px solid var(--admin-border-subtle); box-shadow: 0 -6px 20px rgba(0, 0, 0, 0.18); }
  .ld-act { flex: 1 1 0; min-height: 48px; padding: 8px 10px; font-size: 0.95rem; }
  .ld-status { margin-top: 16px; }
  .ld-seg { display: flex; flex-wrap: nowrap; overflow-x: auto; max-width: 100%; scrollbar-width: none; }
  .ld-seg::-webkit-scrollbar { display: none; }
  .ld-seg button { flex: none; white-space: nowrap; min-height: 44px; }
  .ld-work { display: flex; flex-wrap: wrap; align-items: flex-start; gap: 12px 14px; margin-top: 14px; padding-top: 12px; }
  .ld-work-col--assign { flex: 1 1 140px; }
  .ld-work-col--follow { flex: 2 1 200px; }
  .ld-tabs-detail { position: sticky; top: calc(var(--ld-sticky-top, 0px) + 53px); z-index: 20; margin: 18px -16px 14px; padding: 0 8px; background: var(--admin-bg-base, var(--admin-bg-surface)); }
  .ld-tabs-detail button { flex: 1 1 0; justify-content: center; padding: 8px 10px; }
  .ld-section { margin-top: 18px; padding-top: 14px; }
  .ld-section dl > div { grid-template-columns: 1fr; gap: 0; padding: 7px 0; }
  .ld-section dt { font-size: 0.8rem; }

  /* Add a lead: round button above the bottom edge */
  .ld-fab { position: fixed; right: 16px; bottom: calc(20px + env(safe-area-inset-bottom)); z-index: 55; display: flex; align-items: center; justify-content: center; width: 58px; height: 58px; border: 0; border-radius: 50%; background: #0B7A58; color: #fff; box-shadow: 0 8px 22px rgba(0, 0, 0, 0.3); cursor: pointer; }
  .ld-list { padding-bottom: 84px; }
}

@media (max-width: 640px) {
  .ld-subtitle { display: none; }
  .ld-header { align-items: center; gap: 8px; margin-bottom: 8px; }
  .ld-header .page-title { font-size: 1.25rem; }
  .ld-add { display: none !important; }
  .ld-hl-btn span { display: none; }
  .ld-hl-btn { width: 44px; min-height: 44px; justify-content: center; padding: 0; }
}
</style>

<template>
  <div class="admin-modal-overlay" @click.self="emit('close')">
    <div class="admin-modal-card animate-fade-in-up hf" role="dialog" aria-modal="true" aria-labelledby="hf-title">
      <div class="admin-modal-header">
        <h3 id="hf-title" class="admin-modal-title">Highlight form answers</h3>
        <button class="admin-modal-close" aria-label="Close" @click="emit('close')">✕</button>
      </div>
      <div class="admin-modal-body">
        <p class="hf-help">Choose the answers the team should see first. They show on top of every lead from that project or form and under the name in the list.</p>

        <!-- Custom Project / Lead form Selector -->
        <span id="hf-form-label" class="form-label">Project / Lead form</span>
        <div ref="formDropdownRoot" class="hf-dd form-dd">
          <button
            type="button"
            class="hf-dd-btn"
            :class="{ 'is-active': formDropdownOpen }"
            aria-haspopup="listbox"
            :aria-expanded="formDropdownOpen"
            aria-labelledby="hf-form-label"
            @click="toggleFormDropdown"
          >
            <div class="hf-selected-val">
              <span class="hf-form-icon" aria-hidden="true">
                <!-- Globe icon for website -->
                <svg v-if="currentFormIsWebsite" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                  <circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>
                </svg>
                <!-- Campaign / layers icon for Meta -->
                <svg v-else width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                  <polygon points="12 2 2 7 12 12 22 7 12 2"/><polyline points="2 17 12 22 22 17"/><polyline points="2 12 12 17 22 12"/>
                </svg>
              </span>
              <span :title="currentFormName">{{ currentFormName }}</span>
            </div>
            <svg class="hf-chevron" :class="{ 'is-open': formDropdownOpen }" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
              <polyline points="6 9 12 15 18 9"/>
            </svg>
          </button>

          <transition name="hf-pop">
            <div v-if="formDropdownOpen" class="hf-dd-list form-menu" role="listbox" aria-labelledby="hf-form-label">
              <!-- Website Group -->
              <div v-if="websiteForms.length" class="hf-group">
                <div class="hf-group-title">
                  <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                  <span>Website Inquiries & Projects</span>
                </div>
                <button
                  v-for="f in websiteForms"
                  :key="f.key"
                  type="button"
                  class="hf-opt-btn"
                  :class="{ 'is-selected': formKey === f.key }"
                  role="option"
                  :aria-selected="formKey === f.key"
                  @click="selectForm(f.key)"
                >
                  <div class="hf-opt-left">
                    <svg v-if="formKey === f.key" class="hf-check" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                    <span v-else class="hf-check-spacer" aria-hidden="true" />
                    <span class="hf-opt-text" :title="f.name">{{ f.name }}</span>
                  </div>
                </button>
              </div>

              <!-- Meta Group -->
              <div v-if="metaForms.length" class="hf-group">
                <div class="hf-group-title">
                  <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 2 7 12 12 22 7 12 2"/><polyline points="2 17 12 22 22 17"/><polyline points="2 12 12 17 22 12"/></svg>
                  <span>Meta Lead Forms / Campaigns</span>
                </div>
                <button
                  v-for="f in metaForms"
                  :key="f.key"
                  type="button"
                  class="hf-opt-btn"
                  :class="{ 'is-selected': formKey === f.key }"
                  role="option"
                  :aria-selected="formKey === f.key"
                  @click="selectForm(f.key)"
                >
                  <div class="hf-opt-left">
                    <svg v-if="formKey === f.key" class="hf-check" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                    <span v-else class="hf-check-spacer" aria-hidden="true" />
                    <span class="hf-opt-text" :title="f.name">{{ f.name }}</span>
                  </div>
                </button>
              </div>
            </div>
          </transition>
        </div>

        <span id="hf-fields-label" class="form-label hf-label">Fields to highlight</span>
        <div ref="dropdownRoot" class="hf-dd fields-dd">
          <button
            type="button" class="hf-dd-btn" :class="{ 'is-active': dropdownOpen }" aria-haspopup="listbox" :aria-expanded="dropdownOpen" aria-labelledby="hf-fields-label"
            :disabled="!fields.length" @click="toggleFieldsDropdown"
          >
            <span>{{ summary }}</span>
            <svg class="hf-chevron" :class="{ 'is-open': dropdownOpen }" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
              <polyline points="6 9 12 15 18 9"/>
            </svg>
          </button>
          <transition name="hf-pop">
            <ul v-if="dropdownOpen" class="hf-dd-list" role="listbox" aria-multiselectable="true" aria-labelledby="hf-fields-label">
              <li v-for="field in fields" :key="field" role="option" :aria-selected="picked.includes(field)">
                <label class="hf-opt">
                  <input type="checkbox" :value="field" :checked="picked.includes(field)" @change="toggle(field)" />
                  <span>{{ field }}</span>
                </label>
              </li>
            </ul>
          </transition>
        </div>
        <p v-if="!fields.length" class="hf-empty">No answers from this project or form yet. Its fields appear here after the first lead arrives.</p>

        <div v-if="picked.length" class="hf-chips" aria-label="Highlighted fields">
          <button v-for="field in picked" :key="field" type="button" class="hf-chip" :title="'Remove ' + field" @click="toggle(field)">
            {{ field }} <span aria-hidden="true">✕</span>
          </button>
        </div>
      </div>
      <div class="admin-modal-footer">
        <button type="button" class="btn btn-sm btn-outline-white" @click="emit('close')">Cancel</button>
        <button type="button" class="btn btn-sm btn-emerald" :disabled="saving || !formKey" @click="save">{{ saving ? 'Saving…' : 'Save' }}</button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue'
import type { HighlightForm } from '~/composables/useLeadCrm'

const props = defineProps<{ forms: HighlightForm[]; selected: Record<string, string[]>; initialForm?: string | null; saving: boolean }>()
const emit = defineEmits<{ close: []; save: [form: string, fields: string[]] }>()

const formKey = ref(
  props.initialForm && props.forms.some(f => f.key === props.initialForm)
    ? props.initialForm
    : (props.forms.find(f => f.key !== 'website')?.key || props.forms[0]?.key || '')
)
const picked = ref<string[]>([])
const dropdownOpen = ref(false)
const dropdownRoot = ref<HTMLElement | null>(null)

const formDropdownOpen = ref(false)
const formDropdownRoot = ref<HTMLElement | null>(null)

const websiteForms = computed(() => props.forms.filter(f => f.key === 'website' || f.key.startsWith('website:')))
const metaForms = computed(() => props.forms.filter(f => f.key !== 'website' && !f.key.startsWith('website:')))

const currentForm = computed(() => props.forms.find(f => f.key === formKey.value))
const currentFormName = computed(() => currentForm.value?.name || 'Select project or form')
const currentFormIsWebsite = computed(() => formKey.value === 'website' || formKey.value.startsWith('website:'))

const fields = computed(() => currentForm.value?.fields || [])
const summary = computed(() => {
  if (!fields.value.length) return 'No fields yet'
  if (!picked.value.length) return 'Choose fields…'
  return picked.value.length === 1 ? picked.value[0] : `${picked.value.length} fields selected`
})

watch(formKey, key => {
  picked.value = [...(props.selected[key] || [])]
  dropdownOpen.value = false
}, { immediate: true })

const toggleFormDropdown = () => {
  formDropdownOpen.value = !formDropdownOpen.value
  if (formDropdownOpen.value) dropdownOpen.value = false
}

const toggleFieldsDropdown = () => {
  dropdownOpen.value = !dropdownOpen.value
  if (dropdownOpen.value) formDropdownOpen.value = false
}

const selectForm = (key: string) => {
  formKey.value = key
  formDropdownOpen.value = false
}

const toggle = (field: string) => {
  picked.value = picked.value.includes(field) ? picked.value.filter(f => f !== field) : [...picked.value, field]
}
const save = () => emit('save', formKey.value, picked.value)

const closeOnOutside = (event: MouseEvent) => {
  const target = event.target as Node
  if (dropdownOpen.value && dropdownRoot.value && !dropdownRoot.value.contains(target)) {
    dropdownOpen.value = false
  }
  if (formDropdownOpen.value && formDropdownRoot.value && !formDropdownRoot.value.contains(target)) {
    formDropdownOpen.value = false
  }
}

const handleKeyDown = (event: KeyboardEvent) => {
  if (event.key === 'Escape') {
    if (formDropdownOpen.value) formDropdownOpen.value = false
    else if (dropdownOpen.value) dropdownOpen.value = false
  }
}

onMounted(() => {
  document.addEventListener('click', closeOnOutside)
  window.addEventListener('keydown', handleKeyDown)
})

onBeforeUnmount(() => {
  document.removeEventListener('click', closeOnOutside)
  window.removeEventListener('keydown', handleKeyDown)
})
</script>

<style scoped>
.hf { max-width: 520px; }
.hf-help { margin: 0 0 14px; font-size: 0.88rem; line-height: 1.5; color: var(--admin-text-secondary); }
.hf-label { display: block; margin-top: 14px; }

.hf-dd { position: relative; }
.form-dd { z-index: 25; margin-top: 4px; }
.fields-dd { z-index: 15; }

.hf-dd-btn {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 10px;
  width: 100%;
  min-height: 42px;
  padding: 8px 12px;
  border-radius: 10px;
  border: 1px solid var(--admin-border-hover);
  background: var(--admin-bg-surface);
  color: inherit;
  font: inherit;
  font-size: 0.88rem;
  font-weight: 500;
  text-align: left;
  cursor: pointer;
  outline: none;
  transition: all 0.15s cubic-bezier(0.16, 1, 0.3, 1);
}

.hf-dd-btn:hover:not(:disabled) {
  border-color: var(--admin-border-hover);
  background: var(--admin-bg-surface-alt);
}

.hf-dd-btn.is-active {
  border-color: var(--admin-text-emerald, #0B7A58);
  box-shadow: 0 0 0 2px rgba(11, 122, 88, 0.2);
}

.hf-selected-val {
  display: flex;
  align-items: center;
  gap: 9px;
  min-width: 0;
  flex: 1 1 auto;
}

.hf-selected-val span:last-child {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.hf-form-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  flex: none;
  color: var(--admin-text-emerald, #0B7A58);
  opacity: 0.9;
}

.hf-chevron {
  flex: none;
  opacity: 0.65;
  transition: transform 0.2s cubic-bezier(0.16, 1, 0.3, 1);
}

.hf-chevron.is-open {
  transform: rotate(180deg);
  opacity: 1;
  color: var(--admin-text-emerald, #0B7A58);
}

.hf-dd-btn span { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.hf-dd-btn:disabled { opacity: 0.6; cursor: default; }

.hf-dd-list {
  position: absolute;
  left: 0;
  right: 0;
  top: calc(100% + 5px);
  max-height: 280px;
  overflow-y: auto;
  margin: 0;
  padding: 6px;
  list-style: none;
  border-radius: 12px;
  border: 1px solid var(--admin-border-hover);
  background: var(--admin-bg-surface);
  box-shadow: 0 16px 36px -4px rgba(0, 0, 0, 0.35), 0 6px 14px rgba(0, 0, 0, 0.18);
  scrollbar-width: thin;
}

.hf-dd-list::-webkit-scrollbar { width: 5px; }
.hf-dd-list::-webkit-scrollbar-thumb { background: var(--admin-border-hover); border-radius: 10px; }

/* Custom Groups inside Form Dropdown */
.hf-group {
  margin-bottom: 6px;
  padding-bottom: 4px;
  border-bottom: 1px dashed var(--admin-border-subtle);
}

.hf-group:last-child {
  margin-bottom: 0;
  padding-bottom: 0;
  border-bottom: 0;
}

.hf-group-title {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 6px 10px 4px;
  font-size: 0.7rem;
  font-weight: 700;
  letter-spacing: 0.04em;
  text-transform: uppercase;
  color: var(--admin-text-muted);
}

.hf-opt-btn {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 8px;
  width: 100%;
  min-height: 38px;
  padding: 7px 10px;
  border-radius: 8px;
  border: 0;
  background: transparent;
  color: var(--admin-text-primary);
  font: inherit;
  font-size: 0.84rem;
  font-weight: 500;
  text-align: left;
  cursor: pointer;
  transition: background 0.12s ease;
}

.hf-opt-btn:hover {
  background: var(--admin-table-row-hover, rgba(255, 255, 255, 0.06));
}

.hf-opt-btn.is-selected {
  background: rgba(11, 122, 88, 0.14);
  color: var(--admin-text-emerald, #0B7A58);
  font-weight: 700;
}

.hf-opt-left {
  display: flex;
  align-items: center;
  gap: 8px;
  min-width: 0;
  flex: 1 1 auto;
}

.hf-check {
  flex: none;
  color: var(--admin-text-emerald, #0B7A58);
}

.hf-check-spacer {
  flex: none;
  width: 14px;
  height: 14px;
}

.hf-opt-text {
  min-width: 0;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.hf-opt {
  display: flex;
  align-items: flex-start;
  gap: 10px;
  min-height: 40px;
  padding: 8px 10px;
  border-radius: 8px;
  font-size: 0.88rem;
  line-height: 1.4;
  cursor: pointer;
}

.hf-opt:hover {
  background: var(--admin-chip-bg);
}

.hf-opt input {
  flex: none;
  width: 18px;
  height: 18px;
  margin-top: 1px;
  accent-color: #0B7A58;
}

.hf-empty {
  margin: 8px 0 0;
  font-size: 0.84rem;
  color: var(--admin-text-muted);
}

.hf-chips {
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
  margin-top: 12px;
}

.hf-chip {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  max-width: 100%;
  min-height: 32px;
  padding: 4px 10px;
  border-radius: 999px;
  border: 1px solid rgba(251, 191, 36, 0.5);
  background: rgba(251, 191, 36, 0.14);
  color: inherit;
  font: inherit;
  font-size: 0.8rem;
  text-align: left;
  cursor: pointer;
}

.hf-chip span { opacity: 0.7; }

/* Transitions */
.hf-pop-enter-active,
.hf-pop-leave-active {
  transition: opacity 0.15s cubic-bezier(0.16, 1, 0.3, 1), transform 0.15s cubic-bezier(0.16, 1, 0.3, 1);
}

.hf-pop-enter-from,
.hf-pop-leave-to {
  opacity: 0;
  transform: translateY(-4px) scale(0.98);
}
</style>

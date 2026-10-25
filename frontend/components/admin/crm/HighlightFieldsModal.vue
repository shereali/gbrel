<template>
  <div class="admin-modal-overlay" @click.self="emit('close')">
    <div class="admin-modal-card animate-fade-in-up hf" role="dialog" aria-modal="true" aria-labelledby="hf-title">
      <div class="admin-modal-header">
        <h3 id="hf-title" class="admin-modal-title">Highlight form answers</h3>
        <button class="admin-modal-close" aria-label="Close" @click="emit('close')">✕</button>
      </div>
      <div class="admin-modal-body">
        <p class="hf-help">Choose the answers the team should see first. They show on top of every lead from that form and under the name in the list.</p>

        <label class="form-label" for="hf-form">Lead form</label>
        <select id="hf-form" v-model="formKey" class="form-select">
          <option v-for="f in forms" :key="f.key" :value="f.key">{{ f.name }}</option>
        </select>

        <span id="hf-fields-label" class="form-label hf-label">Fields to highlight</span>
        <div ref="dropdownRoot" class="hf-dd">
          <button
            type="button" class="hf-dd-btn" aria-haspopup="listbox" :aria-expanded="dropdownOpen" aria-labelledby="hf-fields-label"
            :disabled="!fields.length" @click="dropdownOpen = !dropdownOpen"
          >
            <span>{{ summary }}</span>
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg>
          </button>
          <ul v-if="dropdownOpen" class="hf-dd-list" role="listbox" aria-multiselectable="true" aria-labelledby="hf-fields-label">
            <li v-for="field in fields" :key="field" role="option" :aria-selected="picked.includes(field)">
              <label class="hf-opt">
                <input type="checkbox" :value="field" :checked="picked.includes(field)" @change="toggle(field)" />
                <span>{{ field }}</span>
              </label>
            </li>
          </ul>
        </div>
        <p v-if="!fields.length" class="hf-empty">No answers from this form yet. Its fields appear here after the first lead arrives.</p>

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

const formKey = ref(props.initialForm && props.forms.some(f => f.key === props.initialForm) ? props.initialForm : (props.forms.find(f => f.key !== 'website')?.key || props.forms[0]?.key || ''))
const picked = ref<string[]>([])
const dropdownOpen = ref(false)
const dropdownRoot = ref<HTMLElement | null>(null)

const fields = computed(() => props.forms.find(f => f.key === formKey.value)?.fields || [])
const summary = computed(() => {
  if (!fields.value.length) return 'No fields yet'
  if (!picked.value.length) return 'Choose fields…'
  return picked.value.length === 1 ? picked.value[0] : `${picked.value.length} fields selected`
})

watch(formKey, key => { picked.value = [...(props.selected[key] || [])]; dropdownOpen.value = false }, { immediate: true })

const toggle = (field: string) => {
  picked.value = picked.value.includes(field) ? picked.value.filter(f => f !== field) : [...picked.value, field]
}
const save = () => emit('save', formKey.value, picked.value)

const closeOnOutside = (event: MouseEvent) => {
  if (dropdownOpen.value && dropdownRoot.value && !dropdownRoot.value.contains(event.target as Node)) dropdownOpen.value = false
}
onMounted(() => document.addEventListener('click', closeOnOutside))
onBeforeUnmount(() => document.removeEventListener('click', closeOnOutside))
</script>

<style scoped>
.hf { max-width: 520px; }
.hf-help { margin: 0 0 14px; font-size: 0.88rem; line-height: 1.5; color: var(--admin-text-secondary); }
.hf-label { display: block; margin-top: 14px; }
.hf-dd { position: relative; }
.hf-dd-btn { display: flex; align-items: center; justify-content: space-between; gap: 10px; width: 100%; min-height: 44px; padding: 8px 12px; border-radius: 10px; border: 1px solid var(--admin-border-hover); background: var(--admin-bg-surface); color: inherit; font: inherit; font-size: 0.9rem; text-align: left; cursor: pointer; }
.hf-dd-btn span { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.hf-dd-btn:disabled { opacity: 0.6; cursor: default; }
.hf-dd-list { position: absolute; z-index: 5; left: 0; right: 0; top: calc(100% + 4px); max-height: 280px; overflow-y: auto; margin: 0; padding: 6px; list-style: none; border-radius: 10px; border: 1px solid var(--admin-border-hover); background: var(--admin-bg-surface); box-shadow: 0 12px 30px rgba(0, 0, 0, 0.25); }
.hf-opt { display: flex; align-items: flex-start; gap: 10px; min-height: 44px; padding: 10px; border-radius: 8px; font-size: 0.9rem; line-height: 1.4; cursor: pointer; }
.hf-opt:hover { background: var(--admin-chip-bg); }
.hf-opt input { flex: none; width: 18px; height: 18px; margin-top: 1px; accent-color: #0B7A58; }
.hf-empty { margin: 8px 0 0; font-size: 0.84rem; color: var(--admin-text-muted); }
.hf-chips { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 12px; }
.hf-chip { display: inline-flex; align-items: center; gap: 6px; max-width: 100%; min-height: 32px; padding: 4px 10px; border-radius: 999px; border: 1px solid rgba(251, 191, 36, 0.5); background: rgba(251, 191, 36, 0.14); color: inherit; font: inherit; font-size: 0.8rem; text-align: left; cursor: pointer; }
.hf-chip span { opacity: 0.7; }
</style>

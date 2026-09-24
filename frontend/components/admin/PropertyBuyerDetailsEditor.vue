<template>
  <section id="sec-buyer-details" class="buyer-details-editor">
    <h3 style="color:var(--admin-text-primary)">Buyer information / ক্রেতার প্রয়োজনীয় তথ্য</h3>
    <p>All entered information is public. Leave unknown values empty. Use the assigned advisor for contact; do not enter personal numbers, signatures or identity documents here.</p>
    <p>Price basis uses the asking price above. Total price is calculated only when the area and basis are known. Claims are displayed as seller-provided information.</p>
    <details v-for="(group, index) in buyerDetailGroups" :key="group.key" :open="index === 0">
      <summary>{{ group.title }} <span>{{ filled(group) }}/{{ group.fields.length }} entered</span></summary>
      <div class="buyer-field-grid">
        <div v-for="field in group.fields.filter(f => f.key !== 'priceBasis')" :key="field.key" :class="{ wide: field.type === 'textarea' }">
          <label :for="`detail-${field.key}`">{{ field.label }} <small>{{ field.bn }}</small></label>
          <select v-if="field.type === 'select'" :id="`detail-${field.key}`" :value="modelValue[field.key] ?? ''" @change="set(field.key, $event)">
            <option value="">Not provided / জানা নেই</option><option v-for="option in field.options" :key="option[0]" :value="option[0]">{{ option[0] }} · {{ option[1] }}</option>
          </select>
          <textarea v-else-if="field.type === 'textarea'" :id="`detail-${field.key}`" :value="modelValue[field.key] ?? ''" rows="3" maxlength="2000" @input="set(field.key, $event)" />
          <input v-else :id="`detail-${field.key}`" :value="modelValue[field.key] ?? ''" :type="field.type" :min="field.type === 'number' ? 0 : undefined" :max="field.max" :step="field.integer ? 1 : 'any'" maxlength="500" @input="set(field.key, $event, field.type === 'number')" />
        </div>
      </div>
    </details>
    <div class="buyer-preview"><strong>Public price preview</strong><p v-if="property.hidePrice">Price hidden. Payment and cost details will also be hidden.</p><p v-else-if="total">{{ formatBDT(total) }} total asking price · additional costs shown separately</p><p v-else>Choose a price basis and provide the matching area to show a total. Per-share prices stay per share.</p></div>
  </section>
</template>
<script setup lang="ts">
import { buyerDetailGroups, hasDetail, totalAskingPrice } from '~/utils/buyerDetails.mjs'
import { formatBDT } from '~/composables/useCurrency'
const props = defineProps<{ modelValue: Record<string, any>, property: any }>()
const emit = defineEmits(['update:modelValue'])
const set = (key: string, event: Event, numeric = false) => { const value = (event.target as HTMLInputElement).value; emit('update:modelValue', { ...props.modelValue, [key]: numeric && value !== '' ? Number(value) : value }) }
const filled = (group: any) => group.fields.filter((f: any) => hasDetail(props.modelValue[f.key])).length
const total = computed(() => totalAskingPrice({ ...props.property, buyerDetails: props.modelValue }))
</script>
<style scoped>
.buyer-details-editor{padding:24px;border:1px solid var(--admin-border-subtle);border-radius:16px;margin-bottom:24px;min-width:0}.buyer-details-editor>p{color:var(--admin-text-muted);font-size:13px;line-height:1.7}.buyer-details-editor details{border-top:1px solid var(--admin-border-subtle);padding:16px 0}.buyer-details-editor summary{cursor:pointer;font-weight:700;min-height:44px;line-height:1.6}.buyer-details-editor summary span{font-size:12px;color:var(--admin-text-muted);display:block;font-weight:400}.buyer-field-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:18px}.wide{grid-column:1/-1}label{display:block;font-size:13px;font-weight:600;margin-bottom:8px}label small{display:block;font-weight:400;color:var(--admin-text-muted)}input,select,textarea{box-sizing:border-box;width:100%;min-width:0;min-height:44px;border:1px solid var(--admin-border-subtle);border-radius:8px;background:var(--admin-bg-main,#111827);color:var(--admin-text-primary,#fff);padding:10px;font:inherit;font-size:16px}input:focus-visible,select:focus-visible,textarea:focus-visible,summary:focus-visible{outline:2px solid #10b981;outline-offset:3px}.buyer-preview{padding:16px;border-radius:12px;background:rgba(16,185,129,.08);font-size:14px}.buyer-preview p{margin-bottom:0}@media(max-width:640px){.buyer-field-grid{grid-template-columns:1fr}.buyer-details-editor{padding:16px}}
</style>

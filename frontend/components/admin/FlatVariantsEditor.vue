<template>
  <div class="flat-variants-editor-card">
    <div class="editor-header">
      <div>
        <div class="flex items-center gap-2">
          <span class="editor-badge">Multi-Unit</span>
          <h4 style="margin:0; font-size:1.05rem; font-weight:700; color:var(--admin-text-primary);">
            Flat Configurations & Unit Types / বিভিন্ন সাইজের ফ্ল্যাট ভ্যারিয়েন্ট
          </h4>
        </div>
        <p style="margin:4px 0 0; font-size:0.8rem; color:var(--admin-text-muted);">
          একটি বিল্ডিংয়ে ৪ বা ততোধিক সাইজের ফ্ল্যাট থাকলে এখানে প্রতিটি সাইজ, বেড/বাথ, তলা এবং ফ্লোর প্ল্যান যুক্ত করুন।
        </p>
      </div>

      <button 
        type="button" 
        class="btn btn-sm btn-emerald" 
        @click="addVariant"
      >
        <span>+ Add Flat Size / সাইজ যোগ করুন</span>
      </button>
    </div>

    <!-- Empty state -->
    <div v-if="!variants.length" class="empty-variants-state">
      <p style="margin:0 0 10px; font-size:0.88rem; color:var(--admin-text-muted);">
        বর্তমানে কোনো ফ্ল্যাট ভ্যারিয়েন্ট যোগ করা নেই। যদি এই বিল্ডিংয়ে একাধিক সাইজের ফ্ল্যাট থাকে, নিচে ক্লিক করুন।
      </p>
      <button type="button" class="btn btn-sm btn-outline-white" @click="addVariant">
        + প্রথম ফ্ল্যাট সাইজ যোগ করুন (e.g. Type A)
      </button>
    </div>

    <!-- Variants List -->
    <div v-else class="variants-repeater-list">
      <div 
        v-for="(item, index) in variants" 
        :key="index"
        class="variant-item-card"
      >
        <div class="variant-item-top">
          <div class="flex items-center gap-2">
            <span class="item-number">#{{ index + 1 }}</span>
            <strong style="color:var(--admin-text-primary); font-size:0.95rem;">
              {{ item.name || `Unit Type ${index + 1}` }}
            </strong>
            <span v-if="item.sqft" class="badge-sqft">{{ item.sqft }} Sqft</span>
            <span v-if="item.price" class="badge-price">৳ {{ Number(item.price).toLocaleString('en-IN') }}</span>
          </div>

          <div class="flex items-center gap-2">
            <button 
              type="button" 
              class="icon-btn-secondary" 
              title="Duplicate this variant"
              @click="duplicateVariant(index)"
            >
              📋 কপি
            </button>
            <button 
              type="button" 
              class="icon-btn-danger" 
              title="Delete variant"
              @click="removeVariant(index)"
            >
              ✕ মুছুন
            </button>
          </div>
        </div>

        <div class="variant-form-grid">
          <!-- Row 1: Name, Sqft, Price, Price/sqft -->
          <div class="form-group">
            <label class="form-label">Type / নাম <small>(e.g. Type A, Unit-B)</small></label>
            <input 
              v-model="item.name" 
              type="text" 
              placeholder="e.g. Type A (১২৫০ বর্গফুট)" 
              class="form-input" 
              @input="update"
            />
          </div>

          <div class="form-group">
            <label class="form-label">Square Footage (বর্গফুট)</label>
            <input 
              v-model.number="item.sqft" 
              type="number" 
              min="0"
              placeholder="1250" 
              class="form-input" 
              @input="update"
            />
          </div>

          <div class="form-group">
            <label class="form-label">Asking Price (মোট মূল্য BDT)</label>
            <input 
              v-model.number="item.price" 
              type="number" 
              min="0"
              placeholder="11500000" 
              class="form-input" 
              @input="update"
            />
          </div>

          <div class="form-group">
            <label class="form-label">Rate / Sqft (ঐচ্ছিক)</label>
            <input 
              v-model.number="item.price_per_sqft" 
              type="number" 
              min="0"
              placeholder="9200" 
              class="form-input" 
              @input="update"
            />
          </div>

          <!-- Row 2: Beds, Baths, Balconies, Facing -->
          <div class="form-group">
            <label class="form-label">Bedrooms (বেড)</label>
            <input 
              v-model.number="item.bedrooms" 
              type="number" 
              min="0"
              placeholder="3" 
              class="form-input" 
              @input="update"
            />
          </div>

          <div class="form-group">
            <label class="form-label">Bathrooms (বাথ)</label>
            <input 
              v-model.number="item.bathrooms" 
              type="number" 
              min="0"
              placeholder="3" 
              class="form-input" 
              @input="update"
            />
          </div>

          <div class="form-group">
            <label class="form-label">Balconies (বারান্দা)</label>
            <input 
              v-model.number="item.balconies" 
              type="number" 
              min="0"
              placeholder="2" 
              class="form-input" 
              @input="update"
            />
          </div>

          <div class="form-group">
            <label class="form-label">Facing Direction (অভিমুখ)</label>
            <select v-model="item.facing" class="form-select" @change="update">
              <option value="">Not selected</option>
              <option value="South">South Facing</option>
              <option value="North">North Facing</option>
              <option value="East">East Facing</option>
              <option value="West">West Facing</option>
              <option value="South-East">South-East Facing</option>
              <option value="North-East">North-East Facing</option>
            </select>
          </div>

          <!-- Row 3: Available Floors, Floor Plan Image -->
          <div class="form-group" style="grid-column: span 2;">
            <label class="form-label">Available Floors / ইউনিট অবস্থান <small>(e.g. ৩য়, ৫ম ও ৭ম তলা)</small></label>
            <input 
              v-model="item.floor_available" 
              type="text" 
              placeholder="e.g. ৩য়, ৫ম ও ৭ম তলা (মোট ৩টি উপলব্ধ)" 
              class="form-input" 
              @input="update"
            />
          </div>

          <div class="form-group" style="grid-column: span 2;">
            <label class="form-label">Floor Plan Image URL <small>(নির্দিষ্ট ফ্লোর প্ল্যান ছবির লিংক)</small></label>
            <div class="flex gap-2">
              <input 
                v-model="item.floor_plan_image" 
                type="text" 
                placeholder="e.g. /img/properties/dhaur-kamarpara-1250-sqft-floorplan.jpg" 
                class="form-input" 
                @input="update"
              />
              <img 
                v-if="item.floor_plan_image" 
                :src="item.floor_plan_image" 
                alt="thumb" 
                style="width:42px; height:42px; object-fit:cover; border-radius:6px; border:1px solid var(--admin-border-subtle);" 
              />
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'

export interface FlatVariantItem {
  name: string
  sqft?: number | null
  bedrooms?: number | null
  bathrooms?: number | null
  balconies?: number | null
  price?: number | null
  price_per_sqft?: number | null
  floor_available?: string
  facing?: string
  floor_plan_image?: string
  is_available?: boolean
}

const props = defineProps<{
  modelValue?: FlatVariantItem[]
}>()

const emit = defineEmits<{
  (e: 'update:modelValue', value: FlatVariantItem[]): void
}>()

const variants = computed<FlatVariantItem[]>({
  get: () => Array.isArray(props.modelValue) ? props.modelValue : [],
  set: (val) => emit('update:modelValue', val)
})

const update = () => {
  emit('update:modelValue', [...variants.value])
}

const addVariant = () => {
  const currentCount = variants.value.length
  const labels = ['Type A', 'Type B', 'Type C', 'Type D', 'Type E', 'Type F']
  const nextName = labels[currentCount] || `Unit Type ${currentCount + 1}`
  const newItem: FlatVariantItem = {
    name: nextName,
    sqft: 1200 + currentCount * 200,
    bedrooms: 3,
    bathrooms: 3,
    balconies: 2,
    price: null,
    price_per_sqft: null,
    floor_available: '',
    facing: 'South',
    floor_plan_image: '',
    is_available: true
  }
  emit('update:modelValue', [...variants.value, newItem])
}

const duplicateVariant = (index: number) => {
  const source = variants.value[index]
  if (!source) return
  const cloned: FlatVariantItem = {
    ...JSON.parse(JSON.stringify(source)),
    name: `${source.name || 'Unit'} (Copy)`
  }
  const next = [...variants.value]
  next.splice(index + 1, 0, cloned)
  emit('update:modelValue', next)
}

const removeVariant = (index: number) => {
  const next = variants.value.filter((_, idx) => idx !== index)
  emit('update:modelValue', next)
}
</script>

<style scoped>
.flat-variants-editor-card {
  padding: 20px;
  background: rgba(255, 255, 255, 0.02);
  border: 1px solid var(--admin-border-subtle);
  border-radius: var(--radius-md, 12px);
  margin-top: 18px;
}
.editor-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 16px;
  margin-bottom: 16px;
}
.editor-badge {
  background: rgba(16, 185, 129, 0.15);
  color: #10b981;
  font-size: 0.72rem;
  font-weight: 700;
  padding: 2px 8px;
  border-radius: 999px;
  text-transform: uppercase;
}
.empty-variants-state {
  padding: 24px;
  border: 1.5px dashed var(--admin-border-subtle);
  border-radius: 10px;
  text-align: center;
  background: rgba(0, 0, 0, 0.1);
}
.variants-repeater-list {
  display: flex;
  flex-direction: column;
  gap: 16px;
}
.variant-item-card {
  background: var(--admin-bg-surface, #1e293b);
  border: 1px solid var(--admin-border-subtle, #334155);
  border-radius: 10px;
  padding: 16px;
}
.variant-item-top {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 12px;
  border-bottom: 1px solid var(--admin-border-subtle, #334155);
  padding-bottom: 12px;
  margin-bottom: 14px;
}
.item-number {
  background: rgba(255, 255, 255, 0.08);
  color: var(--admin-text-muted);
  font-size: 0.75rem;
  font-weight: 700;
  padding: 2px 8px;
  border-radius: 6px;
}
.badge-sqft {
  background: rgba(59, 130, 246, 0.15);
  color: #60a5fa;
  font-size: 0.75rem;
  font-weight: 600;
  padding: 2px 8px;
  border-radius: 4px;
}
.badge-price {
  background: rgba(212, 175, 55, 0.15);
  color: var(--color-gold, #f59e0b);
  font-size: 0.75rem;
  font-weight: 600;
  padding: 2px 8px;
  border-radius: 4px;
}
.icon-btn-secondary {
  background: rgba(255, 255, 255, 0.06);
  border: 1px solid var(--admin-border-subtle, #334155);
  color: var(--admin-text-primary, #fff);
  padding: 4px 8px;
  border-radius: 6px;
  font-size: 0.75rem;
  cursor: pointer;
}
.icon-btn-secondary:hover {
  background: rgba(255, 255, 255, 0.12);
}
.icon-btn-danger {
  background: rgba(239, 68, 68, 0.12);
  border: 1px solid rgba(239, 68, 68, 0.3);
  color: #f87171;
  padding: 4px 8px;
  border-radius: 6px;
  font-size: 0.75rem;
  cursor: pointer;
}
.icon-btn-danger:hover {
  background: rgba(239, 68, 68, 0.25);
}
.variant-form-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 12px;
}
@media (max-width: 900px) {
  .variant-form-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}
@media (max-width: 600px) {
  .variant-form-grid {
    grid-template-columns: 1fr;
  }
}
</style>

<template>
  <div class="ow">
    <div class="gb-wrap">
      <nav class="ow-crumb" aria-label="Breadcrumb"><NuxtLink to="/my-listings">আমার প্রপার্টি</NuxtLink><span aria-hidden="true">/</span><span>{{ listingId ? (form.title || 'প্রপার্টি') : 'নতুন প্রপার্টি' }}</span></nav>

      <div v-if="loading" class="ow-loading" role="status">তথ্য আসছে…</div>

      <div v-else class="ow-layout">
        <!-- Step rail: a real sequence, so it is numbered -->
        <aside class="ow-rail" aria-label="ধাপ">
          <ol>
            <li v-for="(s, i) in steps" :key="s.key" :class="{ now: step === i, done: stepDone(i) }" :style="{ '--band': bandColors[i] }">
              <button type="button" :disabled="!canVisit(i)" :aria-current="step === i ? 'step' : undefined" @click="goTo(i)">
                <span class="ow-n">{{ toBn(i + 1) }}</span>
                <span class="ow-step-text"><strong>{{ s.title }}</strong><small>{{ s.hint }}</small></span>
              </button>
            </li>
          </ol>
        </aside>

        <section class="ow-main">
          <div v-if="notice" class="ow-notice" :class="notice.tone" role="status">
            <strong>{{ notice.title }}</strong>
            <p>{{ notice.text }}</p>
          </div>

          <!-- 1. Property -->
          <form v-show="step === 0" class="ow-panel" novalidate @submit.prevent="saveAndNext">
            <h1 class="ow-h">প্রপার্টির তথ্য</h1>
            <p class="ow-lead">ক্রেতা যা দেখবেন আর আমাদের যাচাই টিম যা মিলিয়ে দেখবে।</p>
            <ChoiceChips v-model="form.property_type" name="type" label="প্রপার্টির ধরন" :options="propertyTypeOptions" cards :disabled="readOnly" />
            <label class="gb-field"><span>একটি ছোট শিরোনাম</span><input v-model.trim="form.title" class="gb-input" :placeholder="titleSuggestion || 'যেমন: বসুন্ধরায় ৫ কাঠার দক্ষিণমুখী প্লট'" :disabled="readOnly" /></label>
            <div class="ow-grid3">
              <label class="gb-field"><span>বিভাগ</span><select v-model="form.state" class="gb-select" :disabled="readOnly"><option value="">বেছে নিন</option><option v-for="o in divisionOptions" :key="o.value" :value="o.value">{{ o.label }}</option></select></label>
              <label class="gb-field"><span>শহর / জেলা</span><input v-model.trim="form.city" class="gb-input" placeholder="যেমন: ঢাকা" :disabled="readOnly" /></label>
              <label class="gb-field"><span>এলাকা</span><input v-model.trim="form.area_name" class="gb-input" placeholder="যেমন: বসুন্ধরা আ/এ" :disabled="readOnly" /></label>
            </div>
            <label class="gb-field"><span>ওয়েবসাইটে দেখানোর মতো ঠিকানা</span><input v-model.trim="form.address" class="gb-input" placeholder="যেমন: রোড ৮, ব্লক ডি, বসুন্ধরা" :disabled="readOnly" /><small>বাড়ি বা প্লট নম্বর এখানে দেবেন না।</small></label>
            <label class="gb-field"><span>পূর্ণ ঠিকানা (শুধু যাচাই টিম দেখবে)</span><input v-model.trim="details.fullAddress" class="gb-input" placeholder="প্লট/বাড়ি নম্বর, রোড, ব্লক, এলাকা" :disabled="readOnly" /></label>
            <div class="ow-grid3">
              <label class="gb-field"><span>মৌজা</span><input v-model.trim="details.mouza" class="gb-input" :disabled="readOnly" /></label>
              <label class="gb-field"><span>দাগ নম্বর</span><input v-model.trim="details.dagNumbers" class="gb-input" :disabled="readOnly" /></label>
              <label class="gb-field"><span>খতিয়ান নম্বর</span><input v-model.trim="details.khatianNumbers" class="gb-input" :disabled="readOnly" /></label>
            </div>
            <div class="ow-grid3">
              <label class="gb-field"><span>জমির পরিমাণ</span><input v-model.number="form.land_size" class="gb-input" type="number" min="0" step="0.01" inputmode="decimal" :disabled="readOnly" /></label>
              <label class="gb-field"><span>একক</span><select v-model="form.land_unit" class="gb-select" :disabled="readOnly"><option v-for="o in landUnitOptions" :key="o.value" :value="o.value">{{ o.label }}</option></select></label>
              <label class="gb-field"><span>সামনের রাস্তা (ফুট)</span><input v-model.number="details.roadWidth" class="gb-input" type="number" min="0" inputmode="numeric" :disabled="readOnly" /></label>
            </div>
            <div v-if="rooms" class="ow-grid3">
              <label class="gb-field"><span>আয়তন (বর্গফুট)</span><input v-model.number="form.square_footage" class="gb-input" type="number" min="0" inputmode="numeric" :disabled="readOnly" /></label>
              <label class="gb-field"><span>বেডরুম</span><input v-model.number="form.bedrooms" class="gb-input" type="number" min="0" inputmode="numeric" :disabled="readOnly" /></label>
              <label class="gb-field"><span>বাথরুম</span><input v-model.number="form.bathrooms" class="gb-input" type="number" min="0" inputmode="numeric" :disabled="readOnly" /></label>
            </div>
            <div class="ow-grid3">
              <label class="gb-field"><span>ভবনের তলা (থাকলে)</span><input v-model.number="form.total_floors" class="gb-input" type="number" min="0" inputmode="numeric" :disabled="readOnly" /></label>
              <label class="gb-field"><span>অভিমুখ</span><select v-model="form.facing" class="gb-select" :disabled="readOnly"><option :value="null">জানি না</option><option v-for="o in facingOptions" :key="o.value" :value="o.value">{{ o.label }}</option></select></label>
              <label class="gb-field"><span>অবস্থা</span><select v-model="form.completion_status" class="gb-select" :disabled="readOnly"><option v-for="o in completionOptions" :key="o.value" :value="o.value">{{ o.label }}</option></select></label>
            </div>
            <div class="ow-grid2">
              <ChoiceChips v-model="details.landUse" name="landUse" label="জমির ব্যবহার" :options="landUseOptions" :disabled="readOnly" />
              <ChoiceChips v-model="details.cornerPlot" name="corner" label="কর্নার প্লট?" :options="yesNo" :disabled="readOnly" />
            </div>
            <label class="gb-field"><span>বর্তমান স্থাপনা</span><input v-model.trim="details.buildingDescription" class="gb-input" placeholder="যেমন: ২ তলা পুরাতন ভবন, টিনশেড, খালি জমি" :disabled="readOnly" /></label>
            <label class="gb-field"><span>প্রপার্টি সম্পর্কে আরও কিছু</span><textarea v-model="form.description" class="gb-textarea" rows="4" placeholder="রাস্তা, আশপাশ, সুবিধা — ক্রেতার যা জানা দরকার" :disabled="readOnly" /></label>
            <StepFooter />
          </form>

          <!-- 2. Ownership -->
          <form v-show="step === 1" class="ow-panel" novalidate @submit.prevent="saveAndNext">
            <h2 class="ow-h">মালিকানা</h2>
            <p class="ow-lead">এই উত্তরগুলো শুধু আমাদের যাচাই টিম দেখে। সঠিক উত্তর দিলে যাচাই দ্রুত হয়।</p>
            <ChoiceChips v-model="details.submitterRole" name="role" label="আপনি কে?" :options="submitterRoleOptions" :disabled="readOnly" />
            <div class="ow-grid2">
              <label class="gb-field"><span>মোট মালিক কতজন?</span><input v-model.number="details.ownerCount" class="gb-input" type="number" min="1" inputmode="numeric" :disabled="readOnly" /></label>
              <ChoiceChips v-model="details.allOwnersAgree" name="agree" label="সব মালিক বিক্রিতে রাজি?" :options="allOwnersAgreeOptions" :disabled="readOnly" />
            </div>
            <ChoiceChips v-model="details.ownershipSource" name="source" label="কীভাবে মালিক হয়েছেন?" :options="ownershipSourceOptions" :disabled="readOnly" />
            <ChoiceChips v-model="details.possession" name="possession" label="এখন কার দখলে?" :options="possessionOptions" :disabled="readOnly" />
            <div class="ow-grid2">
              <ChoiceChips v-model="details.mutationStatus" name="mutation" label="নামজারি হয়েছে?" :options="mutationOptions" :disabled="readOnly" />
              <label class="gb-field"><span>খাজনা কোন সাল পর্যন্ত পরিশোধিত?</span><input v-model.trim="details.taxPaidThrough" class="gb-input" placeholder="যেমন: ১৪৩২ বাংলা" :disabled="readOnly" /></label>
            </div>
            <div class="ow-grid2">
              <ChoiceChips v-model="details.bankLoan" name="loan" label="ব্যাংক ঋণ বা বন্ধক আছে?" :options="noneExistsOptions" :disabled="readOnly" />
              <ChoiceChips v-model="details.existingAgreement" name="bayna" label="অন্য কারও সঙ্গে বায়না বা চুক্তি আছে?" :options="noneExistsOptions" :disabled="readOnly" />
            </div>
            <ChoiceChips v-model="details.disputeOrCase" name="dispute" label="জমি নিয়ে কোনো মামলা বা বিরোধ আছে?" :options="[{ value: 'No', label: 'না' }, { value: 'Yes', label: 'হ্যাঁ' }]" :disabled="readOnly" />
            <label v-if="details.disputeOrCase === 'Yes'" class="gb-field"><span>সংক্ষেপে লিখুন</span><textarea v-model="details.disputeNote" class="gb-textarea" rows="3" :disabled="readOnly" /></label>
            <StepFooter />
          </form>

          <!-- 3. Price -->
          <form v-show="step === 2" class="ow-panel" novalidate @submit.prevent="saveAndNext">
            <h2 class="ow-h">দাম ও সময়</h2>
            <p class="ow-lead">আপনার প্রত্যাশিত দাম জানান। ওয়েবসাইটে কী দাম দেখানো হবে, তা চুক্তির সময় আপনার সঙ্গে ঠিক করা হবে।</p>
            <ChoiceChips v-model="details.priceBasis" name="basis" label="দাম কীভাবে বলছেন?" :options="priceBasisOptions" :disabled="readOnly" />
            <label class="gb-field ow-price">
              <span>প্রত্যাশিত দাম (টাকা){{ details.priceBasis === 'Per land unit' ? `, প্রতি ${labelOf(landUnitOptions, form.land_unit)}` : details.priceBasis === 'Per sqft' ? ', প্রতি বর্গফুট' : '' }}</span>
              <input v-model.number="details.expectedPrice" class="gb-input" type="number" min="0" step="1000" inputmode="numeric" placeholder="যেমন: 7000000" :disabled="readOnly" />
              <small v-if="details.expectedPrice">{{ priceBn(details.expectedPrice) }}<template v-if="totalPrice"> · মোট প্রায় {{ priceBn(totalPrice) }}</template></small>
            </label>
            <div class="ow-grid2">
              <ChoiceChips v-model="details.negotiable" name="neg" label="দাম আলোচনা সাপেক্ষ?" :options="yesNo" :disabled="readOnly" />
              <ChoiceChips v-model="details.sellTimeline" name="timeline" label="কত দিনের মধ্যে বিক্রি করতে চান?" :options="sellTimelineOptions" :disabled="readOnly" />
            </div>
            <label class="gb-field"><span>কখন ফোন করলে সুবিধা?</span><input v-model.trim="details.bestTimeToCall" class="gb-input" placeholder="যেমন: সন্ধ্যা ৬টার পর" :disabled="readOnly" /></label>
            <label class="gb-field"><span>আমাদের জন্য অন্য কিছু</span><textarea v-model="details.notes" class="gb-textarea" rows="3" :disabled="readOnly" /></label>
            <StepFooter />
          </form>

          <!-- 4. Documents -->
          <section v-show="step === 3" class="ow-panel">
            <h2 class="ow-h">কাগজপত্র</h2>
            <p class="ow-lead">PDF বা পরিষ্কার ছবি (JPG, PNG), প্রতিটি ১৫ MB পর্যন্ত। একই ধরনের একাধিক কাগজ থাকলে আলাদা আলাদা আপলোড করুন।</p>
            <ul class="ow-docs">
              <li v-for="type in documentTypes" :key="type.key" :class="{ need: type.required && !docsOf(type.key).length }">
                <div class="ow-doc-head">
                  <div>
                    <strong>{{ type.label }}</strong>
                    <span>{{ type.required ? 'অবশ্যই লাগবে' : 'প্রযোজ্য হলে' }}<template v-if="type.hint"> · {{ type.hint }}</template></span>
                  </div>
                  <label v-if="!readOnly" class="gb-btn gb-btn--line gb-btn--sm ow-upload" :class="{ busy: uploading === type.key }">
                    <input type="file" accept=".pdf,image/jpeg,image/png,image/webp" :disabled="uploading !== ''" @change="onDocument(type.key, $event)" />
                    {{ uploading === type.key ? 'আপলোড হচ্ছে…' : 'আপলোড' }}
                  </label>
                </div>
                <ul v-if="docsOf(type.key).length" class="ow-files">
                  <li v-for="doc in docsOf(type.key)" :key="doc.id">
                    <button type="button" class="ow-file-name" @click="openDoc(doc.id)">{{ doc.original_name }}</button>
                    <span class="ow-doc-status" :class="doc.status">{{ docStatusLabels[doc.status] }}</span>
                    <button v-if="doc.status !== 'verified' && !readOnly" type="button" class="ow-remove" :aria-label="`${doc.original_name} মুছুন`" @click="removeDoc(doc.id)">মুছুন</button>
                    <p v-if="doc.review_note" class="ow-doc-note">{{ doc.review_note }}</p>
                  </li>
                </ul>
              </li>
            </ul>
            <StepFooter />
          </section>

          <!-- 5. Photos -->
          <section v-show="step === 4" class="ow-panel">
            <h2 class="ow-h">ছবি</h2>
            <p class="ow-lead">সামনে থেকে, রাস্তা, চারপাশ আর ভেতরের ছবি দিন। প্রথম ছবিটি প্রধান ছবি হিসেবে দেখানো হবে। পরে আমাদের টিম পেশাদার ছবিও তুলতে পারে।</p>
            <div class="ow-photos">
              <figure v-for="(url, i) in form.images" :key="url">
                <img :src="url" :alt="`ছবি ${toBn(i + 1)}`" />
                <figcaption>
                  <span v-if="i === 0">প্রধান ছবি</span>
                  <button v-else-if="!readOnly" type="button" @click="makeCover(i)">প্রধান করুন</button>
                  <button v-if="!readOnly" type="button" @click="removePhoto(i)">সরান</button>
                </figcaption>
              </figure>
              <label v-if="!readOnly && form.images.length < 20" class="ow-add-photo" :class="{ busy: uploadingPhotos }">
                <input type="file" accept="image/jpeg,image/png,image/webp" multiple :disabled="uploadingPhotos" @change="onPhotos" />
                <span>{{ uploadingPhotos ? 'আপলোড হচ্ছে…' : 'ছবি যোগ করুন' }}</span>
              </label>
            </div>
            <StepFooter />
          </section>

          <!-- 6. Review & submit -->
          <section v-show="step === 5" class="ow-panel">
            <h2 class="ow-h">দেখে নিন ও জমা দিন</h2>
            <dl class="ow-summary">
              <div><dt>প্রপার্টি</dt><dd>{{ labelOf(propertyTypeOptions, form.property_type) || '—' }}<template v-if="form.land_size">, {{ toBn(form.land_size) }} {{ labelOf(landUnitOptions, form.land_unit) }}</template></dd></div>
              <div><dt>লোকেশন</dt><dd>{{ [form.area_name, form.city].filter(Boolean).join(', ') || '—' }}</dd></div>
              <div><dt>প্রত্যাশিত দাম</dt><dd>{{ details.expectedPrice ? priceBn(details.expectedPrice) : '—' }} {{ details.priceBasis === 'Per land unit' ? `প্রতি ${labelOf(landUnitOptions, form.land_unit)}` : details.priceBasis === 'Per sqft' ? 'প্রতি বর্গফুট' : '' }}</dd></div>
              <div><dt>কাগজপত্র</dt><dd>{{ toBn(listing?.documents.length || 0) }}টি আপলোড হয়েছে</dd></div>
              <div><dt>ছবি</dt><dd>{{ toBn(form.images.length) }}টি</dd></div>
            </dl>

            <div v-if="missingItems.length" class="ow-missing" role="alert">
              <strong>জমা দেওয়ার আগে পূরণ করুন</strong>
              <ul><li v-for="item in missingItems" :key="item.text"><button type="button" class="gb-link" @click="goTo(item.step)">{{ item.text }}</button></li></ul>
            </div>
            <div v-if="listing?.missing_required_documents.length" class="ow-soft">
              <strong>এই কাগজগুলো এখনো আপলোড হয়নি</strong>
              <p>এখন জমা দিতে পারেন, তবে যাচাই শেষ করতে এগুলো লাগবে: {{ listing.missing_required_documents.map(d => d.label).join(', ') }}।</p>
            </div>

            <template v-if="canSubmit">
              <h3 class="ow-sub">শর্তাবলি</h3>
              <ol class="ow-terms"><li v-for="term in ownerTermsList()" :key="term">{{ term }}</li></ol>
              <label class="ow-agree"><input v-model="agree" type="checkbox" /> <span>আমি শর্তগুলো পড়েছি এবং সম্মতি দিচ্ছি।</span></label>
              <p v-if="error" class="ow-error" role="alert">{{ error }}</p>
              <button type="button" class="gb-btn gb-btn--sun ow-submit" :disabled="submitting || !agree || missingItems.length > 0" @click="submitListing">{{ submitting ? 'জমা হচ্ছে…' : 'যাচাইয়ের জন্য জমা দিন' }}</button>
            </template>
            <p v-else-if="['approved', 'update_submitted'].includes(status)" class="ow-muted">পরিবর্তন সংরক্ষণ করলে আমাদের টিম দেখে নিয়ে তবেই ওয়েবসাইটে দেখাবে।</p>
            <div class="ow-foot"><button type="button" class="gb-link" @click="goTo(step - 1)">আগের ধাপ</button></div>
          </section>
        </section>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, defineComponent, h, onMounted, reactive, ref } from 'vue'
import ChoiceChips from '~/components/owner/ChoiceChips.vue'
import { useOwnerListings, type OwnerListing } from '~/composables/useOwnerListings'
import { useSettings } from '~/composables/useSettings'
import { useToast } from '~/composables/useToast'
import { priceBn, toBn } from '~/utils/propertyLabels'
import {
  allOwnersAgreeOptions, completionOptions, divisionOptions, facingOptions, hasRooms, labelOf, landUnitOptions, landUseOptions,
  mutationOptions, noneExistsOptions, ownershipSourceOptions, possessionOptions, priceBasisOptions, propertyTypeOptions,
  sellTimelineOptions, submitterRoleOptions, yesNo
} from '~/utils/ownerListingOptions'

const route = useRoute()
const router = useRouter()
const toast = useToast()
const api = useOwnerListings()
const { settings, fetchSettings, ownerTermsList } = useSettings()

const listingId = ref<number | null>(route.params.id === 'new' ? null : Number(route.params.id))
const listing = ref<OwnerListing | null>(null)
const loading = ref(true)
const saving = ref(false)
const submitting = ref(false)
const uploading = ref('')
const uploadingPhotos = ref(false)
const agree = ref(false)
const error = ref('')
const step = ref(0)
const bandColors = ['#B9D08F', '#9DBE73', '#7FA85A', '#5C924A', '#3A7234', '#1D4A2A']

const steps = [
  { key: 'property', title: 'প্রপার্টি', hint: 'ধরন, লোকেশন, আয়তন' },
  { key: 'ownership', title: 'মালিকানা', hint: 'মালিক, দখল, ঋণ' },
  { key: 'price', title: 'দাম', hint: 'প্রত্যাশিত দাম ও সময়' },
  { key: 'documents', title: 'কাগজপত্র', hint: 'দলিল, খতিয়ান, খাজনা' },
  { key: 'photos', title: 'ছবি', hint: 'যত পরিষ্কার তত ভালো' },
  { key: 'submit', title: 'জমা', hint: 'দেখে নিন ও সম্মতি দিন' }
]

const form = reactive({
  title: '', property_type: '' as string, state: '', city: '', area_name: '', address: '',
  land_size: null as number | null, land_unit: 'Katha', square_footage: null as number | null, total_floors: null as number | null,
  bedrooms: null as number | null, bathrooms: null as number | null, parking: null as number | null,
  facing: null as string | null, completion_status: 'Ready', description: '', images: [] as string[]
})
const details = reactive<Record<string, any>>({})

const rooms = computed(() => hasRooms(form.property_type))
const documentTypes = computed(() => settings.value.listing_document_types)
const docStatusLabels: Record<string, string> = { pending: 'যাচাই বাকি', verified: 'যাচাই হয়েছে', rejected: 'গ্রহণ হয়নি' }
const docsOf = (key: string) => (listing.value?.documents || []).filter(d => d.document_type === key)
const titleSuggestion = computed(() => {
  const type = labelOf(propertyTypeOptions, form.property_type)
  if (!type || !form.area_name) return ''
  return `${form.area_name}-এ ${form.land_size ? `${toBn(form.land_size)} ${labelOf(landUnitOptions, form.land_unit)}ের ` : ''}${type}`
})
const totalPrice = computed(() => (details.priceBasis === 'Per land unit' && form.land_size ? details.expectedPrice * form.land_size : details.priceBasis === 'Per sqft' && form.square_footage ? details.expectedPrice * form.square_footage : 0))

const status = computed(() => listing.value?.review_status || 'draft')
const readOnly = computed(() => ['in_review', 'rejected'].includes(status.value))
const canSubmit = computed(() => ['draft', 'changes_requested'].includes(status.value))

const notice = computed(() => {
  const l = listing.value
  if (!l) return null
  if (l.review_status === 'changes_requested') return { tone: 'warn', title: 'আমাদের টিম কিছু তথ্য চেয়েছে', text: l.review_note || 'প্রয়োজনীয় তথ্য বা কাগজ যোগ করে আবার জমা দিন।' }
  if (l.review_status === 'in_review') return { tone: 'info', title: 'যাচাই চলছে', text: 'এই সময় তথ্য বদলানো যাবে না। কিছু বদলাতে হলে আমাদের টিমকে ফোন করুন।' }
  if (l.review_status === 'submitted') return { tone: 'info', title: 'জমা হয়েছে', text: 'যাচাই শুরু হওয়ার আগে পর্যন্ত আপনি তথ্য ঠিক করতে পারবেন।' }
  if (l.review_status === 'rejected') return { tone: 'warn', title: 'এই প্রপার্টি নেওয়া সম্ভব হয়নি', text: l.review_note || '' }
  if (l.review_status === 'update_submitted') return { tone: 'info', title: 'আপনার পরিবর্তন যাচাই হচ্ছে', text: 'অনুমোদনের পর ওয়েবসাইটে দেখানো হবে।' }
  if (l.review_status === 'approved') return { tone: 'ok', title: l.is_live ? 'প্রপার্টিটি ওয়েবসাইটে প্রকাশিত' : 'অনুমোদিত', text: 'কিছু বদলালে আমাদের টিম দেখে নিয়ে তবেই ওয়েবসাইটে দেখাবে।' }
  return null
})

const missingItems = computed(() => {
  const items: Array<{ step: number; text: string }> = []
  if (!form.property_type) items.push({ step: 0, text: 'প্রপার্টির ধরন' })
  if (!form.title) items.push({ step: 0, text: 'শিরোনাম' })
  if (!form.city || !form.area_name) items.push({ step: 0, text: 'শহর ও এলাকা' })
  if (!details.fullAddress) items.push({ step: 0, text: 'পূর্ণ ঠিকানা' })
  const ownership = ['submitterRole', 'ownerCount', 'allOwnersAgree', 'ownershipSource', 'possession', 'bankLoan', 'existingAgreement', 'mutationStatus', 'disputeOrCase']
  if (ownership.some(k => details[k] === undefined || details[k] === null || details[k] === '')) items.push({ step: 1, text: 'মালিকানার সব প্রশ্ন' })
  if (!details.expectedPrice || !details.priceBasis) items.push({ step: 2, text: 'প্রত্যাশিত দাম' })
  return items
})

const stepDone = (i: number) => {
  if (!listing.value) return false
  if (i === 0) return !!(form.property_type && form.title && form.area_name && details.fullAddress)
  if (i === 1) return !missingItems.value.some(m => m.step === 1)
  if (i === 2) return !!details.expectedPrice
  if (i === 3) return !!listing.value.documents.length && !listing.value.missing_required_documents.length
  if (i === 4) return form.images.length > 0
  return listing.value.review_status !== 'draft'
}
const canVisit = (i: number) => i === 0 || listingId.value !== null

const applyListing = (l: OwnerListing) => {
  listing.value = l
  const pending = l.owner_pending_changes || {}
  const source: Record<string, any> = { ...l, ...pending }
  Object.keys(form).forEach(key => {
    const value = source[key]
    if (key === 'images') form.images = Array.isArray(value) ? [...value] : []
    else if (value !== undefined && value !== null) (form as any)[key] = value
  })
  if (form.title === 'নতুন প্রপার্টি') form.title = ''
  Object.assign(details, l.owner_details || {}, pending.owner_details || {})
}

const payload = () => ({
  ...form,
  title: form.title || titleSuggestion.value || null,
  owner_details: Object.fromEntries(Object.entries(details).filter(([, v]) => v !== '' && v !== undefined))
})

const save = async (): Promise<boolean> => {
  if (readOnly.value) return true
  saving.value = true
  error.value = ''
  try {
    const saved = listingId.value ? await api.update(listingId.value, payload()) : await api.create(payload())
    if (!listingId.value) {
      listingId.value = saved.id
      // Update the address without remounting the page, so the owner stays on the step they are filling.
      window.history.replaceState(window.history.state, '', `/my-listings/${saved.id}`)
    }
    applyListing(saved)
    return true
  } catch (err: any) {
    error.value = err?.message || 'সংরক্ষণ করা যায়নি।'
    toast.error('সংরক্ষণ হয়নি', error.value)
    return false
  } finally {
    saving.value = false
  }
}

const goTo = async (i: number) => {
  if (i < 0 || i >= steps.length) return
  if (i > 0 && !listingId.value && !(await save())) return
  step.value = i
  if (import.meta.client) window.scrollTo({ top: 0, behavior: 'smooth' })
}
const saveAndNext = async () => { if (await save()) goTo(step.value + 1) }

const onDocument = async (type: string, event: Event) => {
  const input = event.target as HTMLInputElement
  const file = input.files?.[0]
  input.value = ''
  if (!file || !listingId.value) return
  uploading.value = type
  try {
    await api.uploadDocument(listingId.value, type, file)
    applyListing(await api.get(listingId.value))
    toast.success('আপলোড হয়েছে', file.name)
  } catch (err: any) {
    toast.error('আপলোড হয়নি', err?.message || '')
  } finally {
    uploading.value = ''
  }
}
const removeDoc = async (documentId: number) => {
  if (!listingId.value) return
  try {
    await api.deleteDocument(listingId.value, documentId)
    applyListing(await api.get(listingId.value))
  } catch (err: any) {
    toast.error('মুছে ফেলা যায়নি', err?.message || '')
  }
}
const openDoc = async (documentId: number) => {
  try { await api.openDocument(documentId) } catch (err: any) { toast.error('ফাইল খোলা যায়নি', err?.message || '') }
}

const onPhotos = async (event: Event) => {
  const input = event.target as HTMLInputElement
  const files = input.files ? Array.from(input.files).slice(0, 20 - form.images.length) : []
  input.value = ''
  if (!files.length) return
  uploadingPhotos.value = true
  try {
    form.images.push(...(await api.uploadPhotos(files)))
    await save()
  } catch (err: any) {
    toast.error('ছবি আপলোড হয়নি', err?.message || '')
  } finally {
    uploadingPhotos.value = false
  }
}
const removePhoto = async (i: number) => { form.images.splice(i, 1); await save() }
const makeCover = async (i: number) => { const [img] = form.images.splice(i, 1); form.images.unshift(img); await save() }

const submitListing = async () => {
  if (!listingId.value) return
  if (!(await save())) return
  submitting.value = true
  error.value = ''
  try {
    await fetchSettings(true)
    applyListing(await api.submit(listingId.value, settings.value.owner_terms_version))
    toast.success('জমা হয়েছে', 'যাচাইয়ের অবস্থা আপনার অ্যাকাউন্টে দেখতে পাবেন।')
    router.push('/my-listings')
  } catch (err: any) {
    error.value = err?.message || 'জমা দেওয়া যায়নি।'
  } finally {
    submitting.value = false
  }
}

// Shared footer for steps 1–5.
const StepFooter = defineComponent(() => () => h('div', { class: 'ow-foot' }, [
  step.value > 0 ? h('button', { type: 'button', class: 'gb-link', onClick: () => goTo(step.value - 1) }, 'আগের ধাপ') : h('span'),
  readOnly.value
    ? h('button', { type: 'button', class: 'gb-btn gb-btn--paddy', onClick: () => goTo(step.value + 1) }, 'পরের ধাপ')
    : h('button', { type: step.value <= 2 ? 'submit' : 'button', class: 'gb-btn gb-btn--paddy', disabled: saving.value, onClick: step.value > 2 ? saveAndNext : undefined }, saving.value ? 'সংরক্ষণ হচ্ছে…' : 'সংরক্ষণ করে এগিয়ে যান')
]))

onMounted(async () => {
  await fetchSettings()
  if (listingId.value) {
    try {
      applyListing(await api.get(listingId.value))
      if (listing.value?.review_status === 'draft' && listing.value.owner_details?.expectedPrice) step.value = 3
    } catch {
      toast.error('প্রপার্টি পাওয়া যায়নি', '')
      router.replace('/my-listings')
    }
  }
  loading.value = false
})

useSeoMeta({ title: 'প্রপার্টির তথ্য | গ্রাম বাংলা রিয়েল এস্টেট' })
</script>

<style scoped>
.ow { padding: 28px 0 96px; }
.ow-crumb { display: flex; gap: 8px; font-size: .88rem; color: var(--gb-ink-soft); margin-bottom: 18px; }
.ow-crumb a { color: var(--gb-leaf); }
.ow-loading { padding: 80px 0; text-align: center; color: var(--gb-ink-soft); }
.ow-layout { display: grid; grid-template-columns: 260px minmax(0, 1fr); gap: clamp(24px, 4vw, 56px); align-items: start; }

.ow-rail { position: sticky; top: 100px; }
.ow-rail ol { list-style: none; display: flex; flex-direction: column; }
.ow-rail li { position: relative; }
.ow-rail li::before { content: ''; position: absolute; left: 21px; top: 46px; bottom: -2px; width: 2px; background: var(--gb-silt); }
.ow-rail li.done::before { background: var(--band); }
.ow-rail li:last-child::before { display: none; }
.ow-rail button { display: flex; align-items: flex-start; gap: 12px; width: 100%; padding: 6px 0 16px; background: transparent; text-align: left; cursor: pointer; color: var(--gb-ink); }
.ow-rail button:disabled { cursor: not-allowed; opacity: .55; }
.ow-n { flex-shrink: 0; display: grid; place-items: center; width: 44px; height: 44px; border-radius: 50%; border: 2px solid var(--gb-silt); background: var(--gb-paper); font-family: var(--gb-display); font-weight: 700; font-size: 1.2rem; color: var(--gb-ink-soft); }
.ow-rail li.done .ow-n { background: var(--band); border-color: var(--band); color: var(--gb-paddy); }
.ow-rail li.done:nth-child(n+4) .ow-n { color: #fff; }
.ow-rail li.now .ow-n { border-color: var(--gb-sun); box-shadow: 0 0 0 3px rgba(226, 101, 28, .2); color: var(--gb-paddy); }
.ow-step-text { display: flex; flex-direction: column; padding-top: 2px; }
.ow-step-text strong { font-family: var(--gb-display); font-size: 1.08rem; color: var(--gb-paddy); }
.ow-step-text small { font-size: .8rem; color: var(--gb-ink-soft); }

.ow-panel { display: flex; flex-direction: column; gap: 18px; background: var(--gb-sheet); border: 1px solid var(--gb-silt); border-radius: var(--gb-r-lg); padding: clamp(20px, 3.5vw, 36px); }
.ow-h { font-size: clamp(1.6rem, 3vw, 2.2rem); font-weight: 800; font-stretch: 108%; }
.ow-lead { color: var(--gb-ink-soft); margin-top: -10px; }
.ow-sub { font-size: 1.2rem; font-weight: 700; margin-top: 8px; }
.ow-grid2 { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px; }
.ow-grid3 { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 16px; }
.ow-panel .gb-field small { font-size: .82rem; color: var(--gb-ink-soft); font-weight: 400; }
.ow-price input { font-family: var(--gb-display); font-size: 1.3rem; font-weight: 600; }
.ow-price small { font-family: var(--gb-display); font-size: 1rem !important; color: var(--gb-paddy) !important; font-weight: 600 !important; }

.ow-notice { border-radius: var(--gb-r); padding: 14px 18px; margin-bottom: 16px; border: 1.5px solid; }
.ow-notice.warn { background: #FBEBDD; border-color: #EFB98F; }
.ow-notice.info { background: #EEF3E6; border-color: var(--gb-silt); }
.ow-notice.ok { background: #E3F0D8; border-color: var(--gb-shoot); }
.ow-notice strong { font-family: var(--gb-display); font-size: 1.1rem; color: var(--gb-paddy); }
.ow-notice p { font-size: .95rem; white-space: pre-line; }

.ow-foot { display: flex; justify-content: space-between; align-items: center; gap: 12px; padding-top: 8px; border-top: 1px dashed var(--gb-silt); margin-top: 6px; }

.ow-docs { list-style: none; border-top: 2px solid var(--gb-paddy); }
.ow-docs > li { padding: 14px 0; border-bottom: 1px solid var(--gb-silt); }
.ow-doc-head { display: flex; justify-content: space-between; align-items: center; gap: 16px; }
.ow-doc-head strong { display: block; font-family: var(--gb-display); font-size: 1.05rem; color: var(--gb-paddy); }
.ow-doc-head span { font-size: .84rem; color: var(--gb-ink-soft); }
.ow-docs > li.need .ow-doc-head span { color: var(--gb-sun-deep); }
.ow-upload { position: relative; flex-shrink: 0; cursor: pointer; }
.ow-upload input { position: absolute; inset: 0; opacity: 0; cursor: pointer; }
.ow-upload:focus-within { outline: 3px solid var(--gb-sun); outline-offset: 2px; }
.ow-upload.busy { opacity: .7; }
.ow-files { list-style: none; margin-top: 10px; display: flex; flex-direction: column; gap: 6px; }
.ow-files li { display: flex; flex-wrap: wrap; align-items: center; gap: 10px; background: #fff; border: 1px solid var(--gb-silt); border-radius: 8px; padding: 8px 12px; }
.ow-file-name { background: none; color: var(--gb-leaf); text-decoration: underline; cursor: pointer; text-align: left; flex: 1; min-width: 0; overflow-wrap: anywhere; font-size: .92rem; }
.ow-doc-status { font-size: .78rem; padding: 2px 10px; border-radius: 999px; background: #EEF1E6; color: var(--gb-ink-soft); }
.ow-doc-status.verified { background: #DDEFCF; color: var(--gb-paddy); }
.ow-doc-status.rejected { background: #FBE3D4; color: #8A3A0F; }
.ow-remove { background: none; color: #A23B16; cursor: pointer; font-size: .85rem; }
.ow-doc-note { width: 100%; font-size: .85rem; color: #8A3A0F; }

.ow-photos { display: grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: 12px; }
.ow-photos figure { margin: 0; border-radius: 12px; overflow: hidden; border: 1px solid var(--gb-silt); background: #fff; }
.ow-photos img { width: 100%; aspect-ratio: 4 / 3; object-fit: cover; }
.ow-photos figcaption { display: flex; justify-content: space-between; gap: 6px; padding: 6px 10px; font-size: .82rem; }
.ow-photos figcaption span { color: var(--gb-paddy); font-weight: 600; }
.ow-photos figcaption button { background: none; color: var(--gb-leaf); cursor: pointer; font-size: .82rem; }
.ow-add-photo { position: relative; display: grid; place-items: center; aspect-ratio: 4 / 3; border: 2px dashed var(--gb-silt); border-radius: 12px; color: var(--gb-paddy); font-family: var(--gb-display); font-weight: 600; cursor: pointer; background: #fff; }
.ow-add-photo:hover { border-color: var(--gb-leaf); }
.ow-add-photo input { position: absolute; inset: 0; opacity: 0; cursor: pointer; }
.ow-add-photo:focus-within { outline: 3px solid var(--gb-sun); outline-offset: 2px; }

.ow-summary { border-top: 2px solid var(--gb-paddy); }
.ow-summary div { display: flex; justify-content: space-between; gap: 16px; padding: 10px 0; border-bottom: 1px solid var(--gb-silt); }
.ow-summary dt { color: var(--gb-ink-soft); }
.ow-summary dd { font-weight: 600; text-align: right; }
.ow-missing { background: #FBEBDD; border: 1.5px solid #EFB98F; border-radius: var(--gb-r); padding: 14px 18px; }
.ow-missing ul { margin: 6px 0 0 20px; }
.ow-soft { background: #EEF3E6; border-radius: var(--gb-r); padding: 14px 18px; }
.ow-soft p { font-size: .92rem; color: var(--gb-ink-soft); }
.ow-terms { padding-left: 22px; display: flex; flex-direction: column; gap: 8px; }
.ow-terms li { line-height: 1.75; }
.ow-agree { display: flex; align-items: flex-start; gap: 10px; font-weight: 600; cursor: pointer; }
.ow-agree input { width: 20px; height: 20px; margin-top: 4px; accent-color: var(--gb-paddy); }
.ow-submit { align-self: flex-start; }
.ow-submit:disabled { opacity: .55; cursor: not-allowed; }
.ow-error { color: #8A3A0F; background: #FBE9DF; border-radius: 8px; padding: 10px 12px; }
.ow-muted { color: var(--gb-ink-soft); }

@media (max-width: 900px) {
  .ow-layout { grid-template-columns: 1fr; }
  .ow-rail { position: static; overflow-x: auto; margin: 0 -20px; padding: 0 20px; }
  .ow-rail ol { flex-direction: row; gap: 4px; }
  .ow-rail li::before { display: none; }
  .ow-rail button { flex-direction: column; align-items: center; padding: 0 6px 6px; min-width: 64px; }
  .ow-step-text small { display: none; }
  .ow-step-text strong { font-size: .85rem; white-space: nowrap; }
  .ow-grid3 { grid-template-columns: 1fr 1fr; }
}
@media (max-width: 560px) {
  .ow-grid2, .ow-grid3 { grid-template-columns: 1fr; }
  .ow-doc-head { flex-direction: column; align-items: flex-start; }
}
</style>

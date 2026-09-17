<template>
  <div style="background: #F8FAFC; min-height: 100vh; padding: 40px 0 80px;">
    <div class="container">
      <!-- Header -->
      <div style="margin-bottom: 36px; text-align: center; max-width: 680px; margin-left: auto; margin-right: auto;">
        <span class="section-tag">Senior Real Estate Advisors</span>
        <h1 style="font-size: 2.3rem; font-weight: 800; color: #0A1128; margin-bottom: 12px;">
          Find Verified Property Specialists by Region
        </h1>
        <p style="color: #64748B; font-size: 1rem;">
          Our licensed brokers and legal valuation advisors specialize in Dhaka Diplomatic Enclaves, Purbachal plots, Chittagong commercial, and Cox's Bazar resorts.
        </p>

        <!-- Division Filter Tabs -->
        <div class="flex justify-center gap-2 flex-wrap" style="margin-top: 24px;">
          <button 
            v-for="region in regions" 
            :key="region"
            class="btn btn-sm"
            :class="selectedRegion === region ? 'btn-primary' : 'btn-outline'"
            @click="selectedRegion = region"
          >
            {{ region === '' ? 'All Bangladesh Regions' : region }}
          </button>
        </div>
      </div>

      <!-- Agent Cards Grid -->
      <div class="grid grid-3">
        <div 
          v-for="agent in filteredAgents" 
          :key="agent.id" 
          class="card animate-fade-in-up"
          style="padding: 24px; display: flex; flex-direction: column;"
        >
          <div class="flex items-center gap-4" style="margin-bottom: 16px;">
            <img :src="agent.photo" :alt="agent.name" style="width: 72px; height: 72px; border-radius: 50%; object-fit: cover; border: 2px solid var(--color-gold);" />
            <div>
              <span class="badge badge-rajuk" style="margin-bottom: 4px;">{{ agent.state }}</span>
              <h3 style="font-size: 1.25rem; font-weight: 800; color: #0F172A; line-height: 1.2;">{{ agent.name }}</h3>
              <div style="font-size: 0.85rem; color: #64748B;">{{ agent.title }}</div>
            </div>
          </div>

          <p style="color: #475569; font-size: 0.9rem; line-height: 1.6; margin-bottom: 18px; flex: 1;">
            {{ agent.bio }}
          </p>

          <!-- Specialties -->
          <div style="margin-bottom: 18px;">
            <div style="font-size: 0.75rem; color: #64748B; text-transform: uppercase; font-weight: 700; margin-bottom: 6px;">Specialties:</div>
            <div class="flex flex-wrap gap-1">
              <span v-for="(spec, idx) in agent.specialties" :key="idx" style="font-size: 0.78rem; background: #F1F5F9; color: #334155; padding: 3px 8px; border-radius: 4px; font-weight: 600;">
                {{ spec }}
              </span>
            </div>
          </div>

          <!-- Stats Row -->
          <div class="flex items-center justify-between" style="padding: 12px 0; border-top: 1px dashed var(--color-border); border-bottom: 1px dashed var(--color-border); margin-bottom: 20px; font-size: 0.85rem;">
            <div>
              <span style="color:#64748B;">Experience:</span> <strong>{{ agent.experienceYears }}+ Years</strong>
            </div>
            <div>
              <span style="color:#64748B;">Rating:</span> <strong style="color: #F59E0B;"><span aria-hidden="true">★</span> {{ agent.rating }}</strong> ({{ agent.reviewCount }})
            </div>
            <div>
              <span style="color:#64748B;">Listings:</span> <strong style="color: #059669;">{{ agent.activeListingsCount }} Active</strong>
            </div>
          </div>

          <!-- Action Buttons -->
          <div class="flex items-center gap-2">
            <NuxtLink :to="`/agents/${agent.id}`" class="btn btn-primary btn-sm flex-1">
              <span>View Portfolio</span>
            </NuxtLink>
            <a 
              :href="`https://wa.me/${agent.whatsapp.replace(/[^0-9]/g, '')}?text=Hello%20${encodeURIComponent(agent.name)},%20I%20would%20like%20real%20estate%20advisory%20assistance.`"
              target="_blank"
              class="btn-card-whatsapp"
              style="width: 36px; height: 36px; border-radius: 8px;"
              title="Chat on WhatsApp"
            >
              <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.711 2.598 2.664-.698c.969.54 1.861.855 2.796.855 3.18 0 5.767-2.587 5.768-5.766 0-3.18-2.586-5.767-5.768-5.767zm7.531 5.766c-.002 4.153-3.38 7.531-7.531 7.531-.019 0-.038 0-.057 0-1.284 0-2.53-.332-3.64-.962l-4.053 1.063 1.082-3.953c-.707-1.16-1.082-2.493-1.082-3.864.002-4.153 3.38-7.531 7.531-7.531 4.153 0 7.531 3.378 7.531 7.531z"/>
              </svg>
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useProperties } from '~/composables/useProperties'

const { agents, fetchAgents } = useProperties()

onMounted(async () => {
  await fetchAgents()
})

const regions = ['', 'Dhaka North', 'Dhaka South', 'Chittagong', 'Sylhet']
const selectedRegion = ref('')

const filteredAgents = computed(() => {
  if (!selectedRegion.value) return agents.value
  return agents.value.filter(a => a.state === selectedRegion.value)
})
</script>

<template>
  <div style="background: #F8FAFC; min-height: 100vh; padding: 40px 0 80px;">
    <div class="container">
      <!-- Breadcrumb -->
      <div class="flex items-center gap-2" style="font-size: 0.85rem; color: #64748B; margin-bottom: 24px;">
        <NuxtLink to="/" style="color: #64748B;">Home</NuxtLink>
        <span>/</span>
        <NuxtLink to="/agents" style="color: #64748B;">Agents</NuxtLink>
        <span>/</span>
        <span style="color: #0F172A; font-weight: 600;">{{ agent.name }}</span>
      </div>

      <!-- Agent Profile Banner Card -->
      <div style="background: #FFFFFF; border: 1px solid var(--color-border); border-radius: var(--radius-xl); padding: 36px; margin-bottom: 48px; box-shadow: var(--shadow-md);">
        <div style="display: grid; grid-template-columns: 140px 1fr auto; gap: 32px; align-items: center;" class="agent-banner-grid">
          <!-- Photo -->
          <img :src="agent.photo" :alt="agent.name" style="width: 140px; height: 140px; border-radius: var(--radius-lg); object-fit: cover; border: 3px solid var(--color-gold); box-shadow: var(--shadow-md);" />

          <!-- Details -->
          <div>
            <div class="flex items-center gap-2" style="margin-bottom: 6px;">
              <span class="badge badge-rajuk">{{ agent.state }}</span>
              <span class="badge badge-featured">Licensed Specialist</span>
            </div>
            <h1 style="font-size: 2rem; font-weight: 800; color: #0A1128; line-height: 1.2; margin-bottom: 4px;">{{ agent.name }}</h1>
            <div style="font-size: 1.05rem; color: #059669; font-weight: 700; margin-bottom: 8px;">{{ agent.title }} • {{ agent.agency }}</div>
            <p style="color: #475569; font-size: 0.95rem; line-height: 1.6; max-width: 680px;">{{ agent.bio }}</p>

            <div class="flex items-center gap-6 flex-wrap" style="margin-top: 16px; font-size: 0.9rem;">
              <div>Experience: <strong>{{ agent.experienceYears }}+ Years</strong></div>
              <div>Rating: <strong style="color: #F59E0B;"><span aria-hidden="true">★</span> {{ agent.rating }} / 5.0</strong> ({{ agent.reviewCount }} verified reviews)</div>
              <div>Phone: <strong>{{ agent.phone }}</strong></div>
            </div>
          </div>

          <!-- Actions -->
          <div class="flex flex-col gap-3">
            <a 
              :href="`https://wa.me/${agent.whatsapp.replace(/[^0-9]/g, '')}?text=Hello%20${encodeURIComponent(agent.name)},%20I%20am%20seeking%20property%20consultation.`"
              target="_blank"
              class="btn btn-emerald"
              style="background:#25D366; font-weight:700;"
            >
              <span>Chat on WhatsApp</span>
            </a>
            <a :href="`tel:${agent.phone}`" class="btn btn-primary">
              <span>Direct Phone Call</span>
            </a>
            <a :href="`mailto:${agent.email}`" class="btn btn-outline">
              <span>Send Email</span>
            </a>
          </div>
        </div>
      </div>

      <!-- Agent's Active Listings Portfolio -->
      <div>
        <div class="flex items-center justify-between" style="margin-bottom: 24px;">
          <div>
            <h2 style="font-size: 1.75rem; font-weight: 800; color: #0A1128;">Active Listings Represented by {{ agent.name }}</h2>
            <p style="color: #64748B; font-size: 0.95rem;">Exclusive direct mandates and verified properties</p>
          </div>
          <span class="badge badge-status" style="font-size: 0.9rem; padding: 6px 14px;">{{ agentListings.length }} Active Mandates</span>
        </div>

        <div v-if="agentListings.length > 0" class="grid grid-3">
          <PropertyCard 
            v-for="prop in agentListings" 
            :key="prop.id" 
            :property="prop" 
          />
        </div>

        <div v-else class="text-center" style="background:#FFF; padding:40px; border-radius:var(--radius-lg); border:1px solid var(--color-border);">
          <p style="color: #64748B;">No listings currently active under this agent profile.</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useProperties } from '~/composables/useProperties'
import PropertyCard from '~/components/PropertyCard.vue'

const route = useRoute()
const { getAgentById, getPropertiesByAgent, fetchAgents, fetchProperties } = useProperties()

onMounted(async () => {
  await Promise.all([
    fetchAgents(),
    fetchProperties()
  ])
})

const agent = computed(() => getAgentById(route.params.id as string))
const agentListings = computed(() => getPropertiesByAgent(agent.value.id))
</script>

<style scoped>
@media (max-width: 992px) {
  .agent-banner-grid {
    grid-template-columns: 1fr !important;
    text-align: center;
    justify-items: center;
  }
}
</style>

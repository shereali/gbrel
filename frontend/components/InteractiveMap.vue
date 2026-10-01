<template>
  <div class="map-wrapper">
    <!-- Map Canvas Container -->
    <div ref="mapContainer" class="leaflet-map-container"></div>
    
    <!-- Floating Map Controls & Counter -->
    <div class="map-overlay-badge">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#10B981" stroke-width="2.5">
        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
        <circle cx="12" cy="10" r="3"/>
      </svg>
      <span><strong>{{ properties.length }}</strong> Geo-Verified Listings on Live Map</span>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, watch, onBeforeUnmount } from 'vue'
import type { PropertyItem } from '~/composables/useProperties'
import { formatBDT } from '~/composables/useCurrency'

const props = defineProps<{
  properties: PropertyItem[]
  centerLat?: number
  centerLng?: number
  zoom?: number
}>()

const mapContainer = ref<HTMLElement | null>(null)
let mapInstance: any = null
let markersLayer: any = null

const initMap = async () => {
  if (typeof window === 'undefined' || !mapContainer.value) return

  // Dynamically import leaflet to prevent SSR issues
  // The map stylesheet is only needed here, so it loads with the map instead of blocking every page.
  await import('leaflet/dist/leaflet.css')
  const L = (await import('leaflet')).default

  // Center on Dhaka or provided coords
  const initialLat = props.centerLat || 23.7925
  const initialLng = props.centerLng || 90.4167
  const initialZoom = props.zoom || 11

  mapInstance = L.map(mapContainer.value, {
    center: [initialLat, initialLng],
    zoom: initialZoom,
    scrollWheelZoom: false
  })

  // OpenStreetMap Tile Layer
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; OpenStreetMap contributors'
  }).addTo(mapInstance)

  markersLayer = L.layerGroup().addTo(mapInstance)
  renderMarkers(L)
}

const renderMarkers = (L: any) => {
  if (!markersLayer || !mapInstance) return
  markersLayer.clearLayers()

  const bounds: any[] = []

  props.properties.forEach(prop => {
    const lat = prop.lat ?? (prop as any).latitude
    const lng = prop.lng ?? (prop as any).longitude

    if (lat && lng) {
      bounds.push([lat, lng])

      // Custom HTML Marker with Price Tag
      const priceFormatted = formatBDT(prop.price)
      const customIcon = L.divIcon({
        className: 'custom-leaflet-marker',
        html: `
          <div style="
            background: #0A1128;
            color: #FFFFFF;
            padding: 5px 10px;
            border-radius: 20px;
            border: 2px solid #D4AF37;
            font-size: 11px;
            font-weight: 800;
            white-space: nowrap;
            box-shadow: 0 4px 14px rgba(0,0,0,0.4);
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 4px;
            transform: translate(-50%, -50%);
          ">
            <span style="width:6px; height:6px; border-radius:50%; background:#10B981;"></span>
            ${priceFormatted}
          </div>
        `,
        iconSize: [80, 30],
        iconAnchor: [40, 15]
      })

      const marker = L.marker([lat, lng], { icon: customIcon })

      const firstImage = (prop.images && prop.images.length > 0)
        ? prop.images[0]
        : 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?q=80&w=600&auto=format&fit=crop'

      // Popup Content Card on Click/Hover
      const popupHtml = `
        <div style="width: 220px; font-family: 'Plus Jakarta Sans', sans-serif;">
          <img src="${firstImage}" style="width: 100%; height: 110px; object-fit: cover; border-radius: 8px; margin-bottom: 8px;" />
          <div style="font-weight: 700; font-size: 13px; color: #0F172A; line-height: 1.3; margin-bottom: 4px;">${prop.title}</div>
          <div style="font-size: 11px; color: #64748B; margin-bottom: 6px;">${prop.areaName}, ${prop.city}</div>
          <div style="font-size: 14px; font-weight: 800; color: #059669; margin-bottom: 8px;">${priceFormatted}</div>
          <a href="/properties/${prop.id}" style="
            display: block;
            text-align: center;
            background: #0A1128;
            color: #FFFFFF;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 700;
            text-decoration: none;
          ">View Details</a>
        </div>
      `

      marker.bindPopup(popupHtml)
      marker.addTo(markersLayer)
    }
  })

  if (bounds.length > 1 && mapInstance) {
    try {
      mapInstance.fitBounds(bounds, { padding: [40, 40], maxZoom: 14 })
    } catch (e) {
      // ignore
    }
  }
}

onMounted(() => {
  initMap()
})

watch(() => props.properties, async () => {
  if (typeof window !== 'undefined') {
    const L = (await import('leaflet')).default
    renderMarkers(L)
  }
}, { deep: true })

onBeforeUnmount(() => {
  if (mapInstance) {
    mapInstance.remove()
  }
})
</script>

<style scoped>
.map-overlay-badge {
  position: absolute;
  top: 16px;
  right: 16px;
  z-index: 1000;
  background: #0A1128;
  color: #FFFFFF;
  padding: 8px 16px;
  border-radius: var(--radius-full);
  font-size: 0.82rem;
  display: flex;
  align-items: center;
  gap: 8px;
  border: 1px solid rgba(255, 255, 255, 0.2);
  box-shadow: 0 4px 14px rgba(0, 0, 0, 0.25);
}
</style>

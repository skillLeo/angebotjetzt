<script setup lang="ts">
import { onBeforeUnmount, onMounted, ref } from 'vue';
import type { Map as LeafletMap } from 'leaflet';

const props = defineProps<{
    plz: string;
    ort: string;
}>();

const mapEl = ref<HTMLElement | null>(null);
let map: LeafletMap | null = null;

// Approximate coordinates by first PLZ digit (Leitzone) — enough to place the request region.
const zones: Record<string, [number, number]> = {
    '0': [51.05, 13.74], '1': [52.52, 13.4], '2': [53.55, 9.99], '3': [52.37, 9.73],
    '4': [51.51, 7.47], '5': [50.94, 6.96], '6': [50.11, 8.68], '7': [48.78, 9.18],
    '8': [48.14, 11.58], '9': [49.45, 11.08],
};

onMounted(async () => {
    const L = await import('leaflet');
    await import('leaflet/dist/leaflet.css');
    if (!mapEl.value) return;

    const coords = zones[props.plz.charAt(0)] ?? [51.16, 10.45];
    map = L.map(mapEl.value, { center: coords, zoom: 10, scrollWheelZoom: false });
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '© OpenStreetMap' }).addTo(map);

    const icon = L.divIcon({
        className: '',
        html: '<span style="display:block;width:22px;height:22px;background:#3EAE2B;border:3px solid #fff;border-radius:50% 50% 50% 0;rotate:45deg;box-shadow:0 4px 10px rgba(11,37,69,0.3);"></span>',
        iconSize: [22, 22],
        iconAnchor: [11, 20],
    });
    L.marker(coords, { icon }).addTo(map).bindPopup(`${props.plz} ${props.ort}`);
});

onBeforeUnmount(() => {
    map?.remove();
    map = null;
});
</script>

<template>
    <div ref="mapEl" class="h-56 w-full rounded-card border border-ink-100" role="img" :aria-label="`Karte: ${plz} ${ort}`" />
</template>

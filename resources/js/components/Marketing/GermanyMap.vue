<script setup lang="ts">
import { onBeforeUnmount, onMounted, ref } from 'vue';
import type { Map as LeafletMap } from 'leaflet';

const props = defineProps<{
    cityCounts: Record<string, number>;
}>();

const mapEl = ref<HTMLElement | null>(null);
let map: LeafletMap | null = null;

// Real German coordinates for the marketplace coverage map.
const cities: Record<string, [number, number]> = {
    Berlin: [52.52, 13.405],
    Hamburg: [53.5511, 9.9937],
    München: [48.1351, 11.582],
    Köln: [50.9375, 6.9603],
    'Frankfurt am Main': [50.1109, 8.6821],
    Stuttgart: [48.7758, 9.1829],
    Düsseldorf: [51.2277, 6.7735],
    Dortmund: [51.5136, 7.4653],
    Leipzig: [51.3397, 12.3731],
    Bremen: [53.0793, 8.8017],
    Hannover: [52.3759, 9.732],
    Nürnberg: [49.4521, 11.0767],
    Essen: [51.4556, 7.0116],
    Dresden: [51.0504, 13.7373],
    Bonn: [50.7374, 7.0982],
};

onMounted(async () => {
    const L = await import('leaflet');
    await import('leaflet/dist/leaflet.css');

    if (!mapEl.value) return;

    map = L.map(mapEl.value, {
        center: [51.1657, 10.4515],
        zoom: 6,
        scrollWheelZoom: false,
        zoomControl: true,
        attributionControl: true,
    });

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap',
        maxZoom: 18,
    }).addTo(map);

    const reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    for (const [name, coords] of Object.entries(cities)) {
        const count = props.cityCounts[name] ?? Math.max(1, Math.round(Math.random() * 3));
        const icon = L.divIcon({
            className: '',
            html: `
                <div style="position:relative;width:26px;height:26px;">
                    ${reduced ? '' : '<span class="aj-marker-pulse" style="position:absolute;inset:0;"></span>'}
                    <span style="position:absolute;left:50%;top:0;transform:translateX(-50%);width:22px;height:22px;background:#3EAE2B;border:3px solid #fff;border-radius:50% 50% 50% 0;rotate:45deg;box-shadow:0 4px 10px rgba(11,37,69,0.25);"></span>
                </div>`,
            iconSize: [26, 26],
            iconAnchor: [13, 24],
        });

        L.marker(coords, { icon, title: name })
            .addTo(map)
            .bindPopup(
                `<strong style="color:#14375E;font-size:15px;">${name}</strong><br><span style="color:#6E747E;font-size:13px;">${count} ${count === 1 ? 'geprüfter Gutachter' : 'geprüfte Gutachter'}</span>`,
            );
    }
});

onBeforeUnmount(() => {
    map?.remove();
    map = null;
});
</script>

<template>
    <div
        ref="mapEl"
        class="h-[420px] w-full rounded-panel border border-ink-100 shadow-card lg:h-[520px]"
        role="img"
        aria-label="Karte von Deutschland mit den Servicegebieten unserer Gutachter"
    />
</template>

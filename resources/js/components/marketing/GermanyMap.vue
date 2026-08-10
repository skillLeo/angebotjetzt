<script setup lang="ts">
import { onBeforeUnmount, onMounted, ref } from 'vue';
import type { Map as LeafletMap } from 'leaflet';

const props = defineProps<{
    cityCounts: Record<string, number>;
}>();

const mapEl = ref<HTMLElement | null>(null);
let map: LeafletMap | null = null;

// Real German coordinates for the marketplace coverage map — spread across
// all 16 states so the map reads as nationwide even while real coverage is
// still concentrated in a handful of cities (see the real/demo split below).
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
    Wiesbaden: [50.0782, 8.2398],
    Saarbrücken: [49.2354, 6.9969],
    Kiel: [54.3233, 10.1228],
    Rostock: [54.0887, 12.1401],
    Magdeburg: [52.1205, 11.6276],
    Erfurt: [50.9848, 11.0299],
    Potsdam: [52.3906, 13.0645],
    Schwerin: [53.6355, 11.4012],
    Mainz: [49.9929, 8.2473],
    Kassel: [51.3127, 9.4797],
    Karlsruhe: [49.0069, 8.4037],
    Mannheim: [49.4875, 8.466],
    Augsburg: [48.3705, 10.8978],
    Münster: [51.9607, 7.6261],
    Bielefeld: [52.0302, 8.5325],
    Wuppertal: [51.2562, 7.1508],
    Bochum: [51.4818, 7.2162],
    Freiburg: [47.999, 7.8421],
    Regensburg: [49.0134, 12.1016],
    Chemnitz: [50.8278, 12.9214],
    Halle: [51.4825, 11.9699],
    Braunschweig: [52.2689, 10.5268],
    Aachen: [50.7753, 6.0839],
    Lübeck: [53.8655, 10.6866],
    Trier: [49.7596, 6.6441],
    Flensburg: [54.7937, 9.4318],
};

type CityMarker = { name: string; coords: [number, number]; count: number; isReal: boolean };

// Only cities with an actual matching count from the backend (real active
// inspectors) are shown as genuine coverage. Everything else is explicitly
// flagged as demo/not-yet-covered — never a fabricated provider count.
function buildMarkers(cityCounts: Record<string, number>): CityMarker[] {
    return Object.entries(cities).map(([name, coords]) => {
        const count = cityCounts[name] ?? 0;

        return { name, coords, count, isReal: count > 0 };
    });
}

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

    for (const { name, coords, count, isReal } of buildMarkers(props.cityCounts)) {
        // Real coverage: solid green pin with the actual verified count.
        // Not-yet-covered: muted outline pin, no count, honest "coming soon"
        // copy — must never look like a genuine verified location.
        const color = isReal ? '#3EAE2B' : '#B8C0CC';
        const icon = L.divIcon({
            className: '',
            html: `
                <div style="position:relative;width:26px;height:26px;">
                    ${reduced || !isReal ? '' : '<span class="aj-marker-pulse" style="position:absolute;inset:0;"></span>'}
                    <span style="position:absolute;left:50%;top:0;transform:translateX(-50%);width:22px;height:22px;background:${color};border:3px solid #fff;border-radius:50% 50% 50% 0;rotate:45deg;box-shadow:0 4px 10px rgba(11,37,69,0.25);"></span>
                </div>`,
            iconSize: [26, 26],
            iconAnchor: [13, 24],
        });

        const popup = isReal
            ? `<strong style="color:#14375E;font-size:15px;">${name}</strong><br><span style="color:#6E747E;font-size:13px;">${count} ${count === 1 ? 'geprüfter Anbieter' : 'geprüfte Anbieter'}</span>`
            : `<strong style="color:#14375E;font-size:15px;">${name}</strong><br><span style="color:#6E747E;font-size:13px;">Bald auch hier verfügbar</span>`;

        L.marker(coords, { icon, title: name }).addTo(map).bindPopup(popup);
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
        class="isolate relative z-0 h-[420px] w-full rounded-panel border border-ink-100 shadow-card lg:h-[520px]"
        role="img"
        aria-label="Karte von Deutschland mit den Servicegebieten unserer Anbieter"
    />
</template>

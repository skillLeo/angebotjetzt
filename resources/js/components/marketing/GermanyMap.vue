<script setup lang="ts">
import { onBeforeUnmount, onMounted, ref } from 'vue';
import type { Map as LeafletMap } from 'leaflet';

type CoverageCity = { name: string; lat: number; lng: number; covered: boolean; count: number };

const props = defineProps<{
    coverage: CoverageCity[];
}>();

const mapEl = ref<HTMLElement | null>(null);
let map: LeafletMap | null = null;

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

    for (const { name, lat, lng, covered, count } of props.coverage) {
        const color = covered ? '#3EAE2B' : '#B8C0CC';
        const icon = L.divIcon({
            className: '',
            html: `
                <div style="position:relative;width:26px;height:26px;">
                    ${reduced || !covered ? '' : '<span class="aj-marker-pulse" style="position:absolute;inset:0;"></span>'}
                    <span style="position:absolute;left:50%;top:0;transform:translateX(-50%);width:22px;height:22px;background:${color};border:3px solid #fff;border-radius:50% 50% 50% 0;rotate:45deg;box-shadow:0 4px 10px rgba(11,37,69,0.25);"></span>
                </div>`,
            iconSize: [26, 26],
            iconAnchor: [13, 24],
        });

        // A city can be marked covered before any provider lists it as their
        // home city, so only state a provider count when there really is one.
        let detail = 'Bald auch hier verfügbar';
        if (covered) {
            detail = count > 0
                ? `${count} ${count === 1 ? 'geprüfter Anbieter' : 'geprüfte Anbieter'}`
                : 'Servicegebiet';
        }

        L.marker([lat, lng], { icon, title: name })
            .addTo(map)
            .bindPopup(
                `<strong style="color:#14375E;font-size:15px;">${name}</strong><br><span style="color:#6E747E;font-size:13px;">${detail}</span>`
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
        class="isolate relative z-0 h-[420px] w-full rounded-panel border border-ink-100 shadow-card lg:h-[520px]"
        role="img"
        aria-label="Karte von Deutschland mit den Servicegebieten unserer Anbieter"
    />
</template>

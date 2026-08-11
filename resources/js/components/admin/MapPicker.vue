<script setup lang="ts">
import { onBeforeUnmount, onMounted, ref, watch } from 'vue';
import type { Map as LeafletMap, Marker } from 'leaflet';

type PickerLocation = { id: number; name: string; latitude: number; longitude: number; isCovered: boolean; providers: number };

const props = defineProps<{
    locations: PickerLocation[];
    picked: { lat: number; lng: number } | null;
}>();

const emit = defineEmits<{ pick: [{ lat: number; lng: number }] }>();

const mapEl = ref<HTMLElement | null>(null);
let map: LeafletMap | null = null;
let L: typeof import('leaflet') | null = null;
let pickedMarker: Marker | null = null;
const existing: Marker[] = [];

function dot(color: string) {
    return L!.divIcon({
        className: '',
        html: `<span style="display:block;width:16px;height:16px;background:${color};border:3px solid #fff;border-radius:50%;box-shadow:0 2px 6px rgba(11,37,69,0.35);"></span>`,
        iconSize: [16, 16],
        iconAnchor: [8, 8],
    });
}

function drawExisting() {
    if (!map || !L) return;

    for (const marker of existing.splice(0)) {
        marker.remove();
    }

    for (const loc of props.locations) {
        const green = loc.isCovered || loc.providers > 0;
        existing.push(
            L.marker([loc.latitude, loc.longitude], {
                icon: dot(green ? '#3EAE2B' : '#B8C0CC'),
                title: loc.name,
                interactive: false,
            }).addTo(map),
        );
    }
}

function drawPicked() {
    if (!map || !L) return;

    pickedMarker?.remove();
    pickedMarker = null;

    if (!props.picked) return;

    pickedMarker = L.marker([props.picked.lat, props.picked.lng], {
        icon: L.divIcon({
            className: '',
            html: `<span style="display:block;width:22px;height:22px;background:#14375E;border:3px solid #fff;border-radius:50% 50% 50% 0;rotate:45deg;box-shadow:0 4px 10px rgba(11,37,69,0.4);"></span>`,
            iconSize: [22, 22],
            iconAnchor: [11, 20],
        }),
    }).addTo(map);
}

onMounted(async () => {
    L = await import('leaflet');
    await import('leaflet/dist/leaflet.css');

    if (!mapEl.value) return;

    map = L.map(mapEl.value, {
        center: [51.1657, 10.4515],
        zoom: 5,
        scrollWheelZoom: false,
    });

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap',
        maxZoom: 18,
    }).addTo(map);

    map.on('click', (e: { latlng: { lat: number; lng: number } }) => {
        emit('pick', { lat: Number(e.latlng.lat.toFixed(6)), lng: Number(e.latlng.lng.toFixed(6)) });
    });

    drawExisting();
    drawPicked();
});

watch(() => props.locations, drawExisting, { deep: true });
watch(() => props.picked, drawPicked, { deep: true });

onBeforeUnmount(() => {
    map?.remove();
    map = null;
});
</script>

<template>
    <div>
        <div ref="mapEl" class="h-[360px] w-full overflow-hidden rounded-card border border-ink-300" />
        <p class="mt-2 text-xs text-ink-500">
            Klicken Sie auf die Karte, um einen Standort genau dort zu setzen. Grün = wird auf der Startseite angezeigt.
        </p>
    </div>
</template>

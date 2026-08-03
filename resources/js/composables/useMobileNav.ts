import { ref } from 'vue';

// Shared across PublicHeader (which owns the toggle) and PublicLayout (which
// needs to know when to get out of the way — e.g. hiding the cookie banner
// so it doesn't sit on top of the open mobile menu, obscuring most of it).
export const mobileNavOpen = ref(false);

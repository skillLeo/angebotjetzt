import { computed, inject, provide, type ComputedRef, type InjectionKey } from 'vue';

/** A resolved key → text map as handed over by the server. */
export type SiteContentMap = Record<string, string>;

const SITE_CONTENT: InjectionKey<ComputedRef<SiteContentMap>> = Symbol('siteContent');

/**
 * Publishes the page's content map to every component below it, and returns
 * a reader for the publishing component's own copy.
 *
 * Takes a getter rather than the map itself so the value stays reactive
 * across Inertia navigations, when the page props are replaced wholesale.
 *
 * The reader has to be returned rather than obtained from useSiteContent():
 * inject() resolves against the parent chain, so a component never sees its
 * own provide() and would silently read nothing but fallbacks.
 */
export function provideSiteContent(source: () => SiteContentMap | undefined): (key: string, fallback: string) => string {
    const map = computed<SiteContentMap>(() => source() ?? {});

    provide(SITE_CONTENT, map);

    return (key: string, fallback: string): string => map.value[key] ?? fallback;
}

/**
 * Reads admin-editable copy.
 *
 *   const content = useSiteContent();
 *   content('home.hero.cta', 'Angebote erhalten')
 *
 * The second argument is the string that used to be hardcoded here. It is
 * used whenever the key is absent — an un-seeded database, or a component
 * rendered on a page that does not publish a content map — so the site never
 * renders a blank where text used to be. An empty stored value is honoured as
 * an intentional edit, not treated as missing.
 */
export function useSiteContent(): (key: string, fallback: string) => string {
    const map = inject(
        SITE_CONTENT,
        computed<SiteContentMap>(() => ({})),
    );

    return (key: string, fallback: string): string => map.value[key] ?? fallback;
}

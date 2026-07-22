const euroFormatter = new Intl.NumberFormat('de-DE', {
    style: 'currency',
    currency: 'EUR',
});

/** Format integer cents as "1.249,00 €". */
export function formatEuro(cents: number | null | undefined): string {
    if (cents === null || cents === undefined) return '–';
    return euroFormatter.format(cents / 100);
}

/** Format integer cents without decimals, e.g. "249 €". */
export function formatEuroShort(cents: number | null | undefined): string {
    if (cents === null || cents === undefined) return '–';
    return new Intl.NumberFormat('de-DE', {
        style: 'currency',
        currency: 'EUR',
        maximumFractionDigits: 0,
    }).format(cents / 100);
}

export function formatNumber(value: number): string {
    return new Intl.NumberFormat('de-DE').format(value);
}

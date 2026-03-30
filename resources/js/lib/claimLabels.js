export function formatClaimMoney(value) {
    if (value == null || value === '') {
        return '—';
    }
    return new Intl.NumberFormat('tr-TR', { style: 'currency', currency: 'TRY' }).format(Number(value));
}

export function formatClaimDateTime(iso) {
    if (!iso) {
        return '—';
    }
    return iso.replace('T', ' ').slice(0, 16);
}

export function insightBadgeClass(type) {
    switch (type) {
        case 'warning':
            return 'bg-amber-100 text-amber-900 ring-amber-600/20 dark:bg-amber-950/50 dark:text-amber-200';
        case 'stale':
            return 'bg-rose-100 text-rose-900 ring-rose-600/20 dark:bg-rose-950/50 dark:text-rose-200';
        case 'payment':
            return 'bg-sky-100 text-sky-900 ring-sky-600/20 dark:bg-sky-950/50 dark:text-sky-200';
        case 'finance':
            return 'bg-violet-100 text-violet-900 ring-violet-600/20 dark:bg-violet-950/50 dark:text-violet-200';
        default:
            return 'bg-slate-100 text-slate-700 ring-slate-500/20 dark:bg-slate-800 dark:text-slate-200';
    }
}

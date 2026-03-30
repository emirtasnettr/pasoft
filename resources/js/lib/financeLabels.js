export function formatMoneyTr(value) {
    if (value == null || value === '') {
        return '—';
    }
    return new Intl.NumberFormat('tr-TR', { style: 'currency', currency: 'TRY' }).format(Number(value));
}

export function formatDateTimeShort(iso) {
    if (!iso) {
        return '—';
    }
    return iso.replace('T', ' ').slice(0, 16);
}

export const PAYMENT_METHOD_LABELS = {
    cash: 'Nakit',
    card: 'Kredi kartı',
    transfer: 'Havale',
};

export function paymentMethodLabel(method) {
    return PAYMENT_METHOD_LABELS[method] ?? method;
}

/** Türkiye için wa.me numarası (basit normalize) */
export function whatsappHref(phone) {
    if (!phone) {
        return null;
    }
    let d = String(phone).replace(/\D/g, '');
    if (d.startsWith('0')) {
        d = '90' + d.slice(1);
    } else if (!d.startsWith('90') && d.length >= 10) {
        d = '90' + d;
    }
    return `https://wa.me/${d}`;
}

export function overdueAlertClass(severity) {
    if (severity === '7') {
        return 'border-rose-400 bg-rose-50 text-rose-950 ring-1 ring-rose-200 dark:border-rose-500/50 dark:bg-rose-950/40 dark:text-rose-50 dark:ring-rose-500/30';
    }
    return 'border-rose-300 bg-rose-50/95 text-rose-900 ring-1 ring-rose-200 dark:border-rose-500/40 dark:bg-rose-950/30 dark:text-rose-100 dark:ring-rose-500/25';
}

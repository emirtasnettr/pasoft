export const SEGMENT_LABELS = {
    vip: 'VIP',
    active: 'Aktif',
    passive: 'Pasif',
    potential: 'Potansiyel',
};

export function segmentBadgeClass(segment) {
    const map = {
        vip: 'bg-amber-100 text-amber-900 ring-amber-600/15 dark:bg-amber-950/50 dark:text-amber-200',
        active: 'bg-emerald-100 text-emerald-800 ring-emerald-600/15 dark:bg-emerald-950/50 dark:text-emerald-200',
        passive: 'bg-slate-100 text-slate-700 ring-slate-500/15 dark:bg-slate-800 dark:text-slate-200',
        potential: 'bg-indigo-100 text-indigo-800 ring-indigo-600/15 dark:bg-indigo-950/50 dark:text-indigo-200',
    };
    return map[segment] ?? map.potential;
}

export const PAYMENT_STATUS_LABELS = {
    pending: 'Bekliyor',
    partial: 'Kısmi',
    collected: 'Tahsil',
};

export const RENEWAL_STATUS_LABELS = {
    active: 'Aktif',
    pending_renewal: 'Yenileme bekliyor',
    renewed: 'Yenilendi',
    not_renewed: 'Yenilenmedi',
};

export function activityCustomerLabel(action) {
    const map = {
        created: 'Müşteri oluşturuldu',
        updated: 'Güncellendi',
        note_added: 'Not eklendi',
        document_added: 'Evrak yüklendi',
        document_removed: 'Evrak silindi',
    };
    return map[action] ?? action;
}

export function formatMoneyTr(n) {
    if (n == null || Number.isNaN(Number(n))) {
        return '—';
    }
    return `₺${Number(n).toLocaleString('tr-TR', { minimumFractionDigits: 0, maximumFractionDigits: 2 })}`;
}

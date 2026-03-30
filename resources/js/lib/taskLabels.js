export const TYPE_LABELS = {
    call: 'Arama',
    visit: 'Ziyaret',
    proposal: 'Teklif',
    follow_up: 'Takip',
    other: 'Diğer',
};

export const PRIORITY_LABELS = {
    low: 'Düşük',
    medium: 'Orta',
    high: 'Yüksek',
};

export const STATUS_LABELS = {
    pending: 'Bekliyor',
    in_progress: 'Devam ediyor',
    done: 'Tamamlandı',
    cancelled: 'İptal',
    overdue: 'Gecikti',
};

export function displayStatus(row) {
    if (row.status === 'done') {
        return 'done';
    }
    if (row.status === 'cancelled') {
        return 'cancelled';
    }
    if (row.is_overdue) {
        return 'overdue';
    }
    return row.status;
}

export function statusBadgeClass(key) {
    const map = {
        pending:
            'bg-slate-100 text-slate-700 ring-slate-500/10 dark:bg-slate-800 dark:text-slate-200',
        in_progress:
            'bg-sky-100 text-sky-800 ring-sky-600/15 dark:bg-sky-950/60 dark:text-sky-200',
        done: 'bg-emerald-100 text-emerald-800 ring-emerald-600/15 dark:bg-emerald-950/50 dark:text-emerald-200',
        cancelled:
            'bg-slate-100 text-slate-500 ring-slate-500/10 dark:bg-slate-800 dark:text-slate-400',
        overdue: 'bg-rose-100 text-rose-800 ring-rose-600/20 dark:bg-rose-950/50 dark:text-rose-200',
    };
    return map[key] ?? map.pending;
}

export function priorityBadgeClass(p) {
    const map = {
        low: 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300',
        medium: 'bg-amber-100 text-amber-900 dark:bg-amber-950/50 dark:text-amber-200',
        high: 'bg-rose-100 text-rose-900 dark:bg-rose-950/50 dark:text-rose-200',
    };
    return map[p] ?? map.medium;
}

export function typeBadgeClass() {
    return 'bg-violet-100 text-violet-800 dark:bg-violet-950/50 dark:text-violet-200';
}

export function activityLabel(action) {
    const map = {
        created: 'Görev oluşturuldu',
        status_changed: 'Durum değişti',
        completed: 'Tamamlandı',
        assigned_changed: 'Atanan kişi değişti',
        note_added: 'Not eklendi',
        file_added: 'Dosya yüklendi',
    };
    return map[action] ?? action;
}

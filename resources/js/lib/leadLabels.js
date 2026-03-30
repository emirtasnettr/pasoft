export const STAGE_LABELS = {
    new: 'Yeni',
    contacted: 'İletişim',
    proposal: 'Teklif',
    meeting: 'Görüşme',
    won: 'Kazanıldı',
    lost: 'Kaybedildi',
};

export const STAGE_ORDER = ['new', 'contacted', 'proposal', 'meeting', 'won', 'lost'];

export function stageBadgeClass(stage) {
    const map = {
        new: 'bg-slate-100 text-slate-700 ring-slate-500/15 dark:bg-slate-800 dark:text-slate-200',
        contacted: 'bg-sky-100 text-sky-800 ring-sky-600/15 dark:bg-sky-950/50 dark:text-sky-200',
        proposal: 'bg-violet-100 text-violet-800 ring-violet-600/15 dark:bg-violet-950/50 dark:text-violet-200',
        meeting: 'bg-indigo-100 text-indigo-800 ring-indigo-600/15 dark:bg-indigo-950/50 dark:text-indigo-200',
        won: 'bg-emerald-100 text-emerald-800 ring-emerald-600/15 dark:bg-emerald-950/50 dark:text-emerald-200',
        lost: 'bg-rose-100 text-rose-800 ring-rose-600/15 dark:bg-rose-950/50 dark:text-rose-200',
    };
    return map[stage] ?? map.new;
}

export const TEMP_LABELS = {
    hot: 'Sıcak',
    warm: 'Orta',
    cold: 'Soğuk',
};

export const TEMP_EMOJI = {
    hot: '🔥',
    warm: '🌤️',
    cold: '❄️',
};

export function tempBadgeClass(t) {
    const map = {
        hot: 'bg-orange-100 text-orange-900 dark:bg-orange-950/50 dark:text-orange-200',
        warm: 'bg-amber-100 text-amber-900 dark:bg-amber-950/50 dark:text-amber-200',
        cold: 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300',
    };
    return map[t] ?? map.warm;
}

export function activityLeadLabel(action) {
    const map = {
        created: 'Lead oluşturuldu',
        updated: 'Güncellendi',
        stage_changed: 'Aşama değişti',
        note_added: 'Not eklendi',
        called: 'Temas / arama',
    };
    return map[action] ?? action;
}

export function digitsPhone(phone) {
    if (!phone) {
        return '';
    }
    return String(phone).replace(/\D/g, '');
}

export function waLink(phone) {
    const d = digitsPhone(phone);
    if (!d) {
        return '#';
    }
    return `https://wa.me/${d.startsWith('0') ? '90' + d.slice(1) : d}`;
}

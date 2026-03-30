import { formatMoneyTr } from '@/lib/customerLabels';

export { formatMoneyTr };

export const URGENCY_LABELS = {
    active: 'Aktif',
    warn_7: '7 gün kaldı',
    warn_3: '3 gün kaldı',
    critical_1: '1 gün kaldı',
    expired: 'Süresi doldu',
    unknown: 'Tarih yok',
};

export function activityPolicyLabel(action) {
    const map = {
        created: 'Poliçe oluşturuldu',
        updated: 'Güncellendi',
        note_added: 'Not eklendi',
        document_added: 'Evrak yüklendi',
        document_removed: 'Evrak silindi',
        renewal_updated: 'Yenileme durumu değişti',
        renewal_started: 'Yenileme başlatıldı',
        reminder_sent: 'Hatırlatma kaydedildi',
    };
    return map[action] ?? action;
}

export function daysLeftLabel(row) {
    if (row.urgency === 'expired') {
        return 'Süresi doldu';
    }
    if (row.days_left == null) {
        return '—';
    }
    return `${row.days_left} gün`;
}

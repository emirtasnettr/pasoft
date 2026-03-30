import {
    BriefcaseIcon,
    HeartIcon,
    HomeModernIcon,
    RectangleStackIcon,
    ShieldCheckIcon,
    SparklesIcon,
    TruckIcon,
} from '@heroicons/vue/24/outline';

const MAP = {
    truck: TruckIcon,
    home: HomeModernIcon,
    heart: HeartIcon,
    briefcase: BriefcaseIcon,
    'shield-check': ShieldCheckIcon,
    'rectangle-stack': RectangleStackIcon,
    sparkles: SparklesIcon,
};

export function policyTypeOutlineIcon(slug) {
    return MAP[slug] || RectangleStackIcon;
}

export const POLICY_TYPE_ICON_OPTIONS = [
    { value: 'rectangle-stack', label: 'Genel' },
    { value: 'truck', label: 'Araç' },
    { value: 'home', label: 'Konut' },
    { value: 'heart', label: 'Sağlık' },
    { value: 'briefcase', label: 'İş / sorumluluk' },
    { value: 'shield-check', label: 'Koruma' },
    { value: 'sparkles', label: 'Özel' },
];

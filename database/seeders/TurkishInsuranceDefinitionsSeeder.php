<?php

namespace Database\Seeders;

use App\Models\InsuranceCompany;
use App\Models\PolicyType;
use Illuminate\Database\Seeder;

/**
 * Türkiye pazarında yaygın poliçe türleri ve faaliyet gösteren sigorta şirketleri.
 * Kodlar ve slug'lar sistem içinde benzersiz tanımlayıcıdır; resmi kısaltmalarla uyumlu olacak şekilde seçilmiştir.
 */
class TurkishInsuranceDefinitionsSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->policyTypes() as $row) {
            PolicyType::query()->updateOrCreate(
                ['slug' => $row['slug']],
                $row + ['is_active' => true],
            );
        }

        foreach ($this->insuranceCompanies() as $row) {
            InsuranceCompany::query()->updateOrCreate(
                ['code' => $row['code']],
                $row + [
                    'is_active' => true,
                    'api_enabled' => false,
                    'quote_integration_enabled' => false,
                ],
            );
        }
    }

    /**
     * @return list<array{name: string, slug: string, description: string|null, category: string|null, color: string, icon: string, renewal_reminder_days: int}>
     */
    private function policyTypes(): array
    {
        return [
            // Araç
            [
                'name' => 'Zorunlu Trafik Sigortası',
                'slug' => 'zorunlu-trafik',
                'description' => 'Motorlu araçlar için zorunlu mali sorumluluk (trafik) sigortası.',
                'category' => 'arac',
                'color' => '#0ea5e9',
                'icon' => 'truck',
                'renewal_reminder_days' => 30,
            ],
            [
                'name' => 'Kasko Sigortası',
                'slug' => 'kasko',
                'description' => 'Araç hasar ve hırsızlık teminatları (kasko).',
                'category' => 'arac',
                'color' => '#6366f1',
                'icon' => 'truck',
                'renewal_reminder_days' => 30,
            ],
            [
                'name' => 'İhtiyari Mali Mesuliyet (İMM)',
                'slug' => 'imm',
                'description' => 'Trafik poliçesi limitlerini aşan zararlar için ek teminat.',
                'category' => 'arac',
                'color' => '#8b5cf6',
                'icon' => 'truck',
                'renewal_reminder_days' => 30,
            ],
            [
                'name' => 'Yeşil Kart Sigortası',
                'slug' => 'yesil-kart',
                'description' => 'Yurt dışında araç kullanımı için uluslararası sorumluluk sigortası.',
                'category' => 'arac',
                'color' => '#059669',
                'icon' => 'truck',
                'renewal_reminder_days' => 45,
            ],
            // Sağlık
            [
                'name' => 'Tamamlayıcı Sağlık Sigortası (TSS)',
                'slug' => 'tss',
                'description' => 'SGK anlaşmalı özel hastanelerde ek teminat.',
                'category' => 'saglik',
                'color' => '#ec4899',
                'icon' => 'heart',
                'renewal_reminder_days' => 30,
            ],
            [
                'name' => 'Özel Sağlık Sigortası (ÖSS)',
                'slug' => 'oss',
                'description' => 'Ayakta ve yatarak tedavi, check-up ve ilave teminatlar.',
                'category' => 'saglik',
                'color' => '#db2777',
                'icon' => 'heart',
                'renewal_reminder_days' => 30,
            ],
            [
                'name' => 'Yabancı Sağlık Sigortası',
                'slug' => 'yabanci-saglik',
                'description' => 'İkamet, vize ve yurt dışı seyahatler için sağlık teminatı.',
                'category' => 'saglik',
                'color' => '#f472b6',
                'icon' => 'heart',
                'renewal_reminder_days' => 30,
            ],
            [
                'name' => 'Diş Sigortası',
                'slug' => 'dis-sigortasi',
                'description' => 'Ayakta diş tedavileri ve protez teminatları.',
                'category' => 'saglik',
                'color' => '#14b8a6',
                'icon' => 'heart',
                'renewal_reminder_days' => 30,
            ],
            // Konut & işyeri
            [
                'name' => 'DASK (Zorun Deprem)',
                'slug' => 'dask',
                'description' => 'Konutlar için zorunlu deprem sigortası.',
                'category' => 'konut',
                'color' => '#f59e0b',
                'icon' => 'home',
                'renewal_reminder_days' => 30,
            ],
            [
                'name' => 'Konut Sigortası',
                'slug' => 'konut',
                'description' => 'Yangın, su baskını, hırsızlık ve ek teminatlar.',
                'category' => 'konut',
                'color' => '#eab308',
                'icon' => 'home',
                'renewal_reminder_days' => 30,
            ],
            [
                'name' => 'İşyeri Sigortası',
                'slug' => 'isyeri',
                'description' => 'Ofis, dükkan ve üretim tesisleri için yangın ve ek riskler.',
                'category' => 'is',
                'color' => '#ca8a04',
                'icon' => 'briefcase',
                'renewal_reminder_days' => 30,
            ],
            [
                'name' => 'İnşaat All Risk',
                'slug' => 'insaat-all-risk',
                'description' => 'Şantiye ve inşaat süreçleri için all risk teminatı.',
                'category' => 'is',
                'color' => '#78716c',
                'icon' => 'briefcase',
                'renewal_reminder_days' => 30,
            ],
            // Hayat & birikim
            [
                'name' => 'Hayat Sigortası',
                'slug' => 'hayat',
                'description' => 'Vefat ve yaşam teminatlı hayat ürünleri.',
                'category' => 'hayat',
                'color' => '#7c3aed',
                'icon' => 'shield-check',
                'renewal_reminder_days' => 30,
            ],
            [
                'name' => 'Ferdi Kaza Sigortası',
                'slug' => 'ferdi-kaza',
                'description' => 'Kaza sonucu maluliyet ve vefat teminatları.',
                'category' => 'hayat',
                'color' => '#6d28d9',
                'icon' => 'shield-check',
                'renewal_reminder_days' => 30,
            ],
            [
                'name' => 'Tehlikeli Hastalıklar Sigortası',
                'slug' => 'tehlikeli-hastaliklar',
                'description' => 'Kritik hastalık teşhisi ve tedavi destek teminatı.',
                'category' => 'hayat',
                'color' => '#a855f7',
                'icon' => 'heart',
                'renewal_reminder_days' => 30,
            ],
            [
                'name' => 'Kredi Hayat Sigortası',
                'slug' => 'kredi-hayat',
                'description' => 'Kredi borçlusu vefaat/maluliyet ile kredi kapanış teminatı.',
                'category' => 'hayat',
                'color' => '#9333ea',
                'icon' => 'shield-check',
                'renewal_reminder_days' => 30,
            ],
            // Seyahat & nakliyat
            [
                'name' => 'Seyahat Sağlık Sigortası',
                'slug' => 'seyahat-saglik',
                'description' => 'Yurt içi ve yurt dışı seyahatlerde sağlık ve bagaj teminatı.',
                'category' => 'seyahat',
                'color' => '#06b6d4',
                'icon' => 'sparkles',
                'renewal_reminder_days' => 14,
            ],
            [
                'name' => 'Nakliyat (Emtia) Sigortası',
                'slug' => 'nakliyat',
                'description' => 'Karayolu, deniz ve hava taşımacılığında emtia riskleri.',
                'category' => 'nakliyat',
                'color' => '#0891b2',
                'icon' => 'truck',
                'renewal_reminder_days' => 30,
            ],
            [
                'name' => 'Tekne Sigortası',
                'slug' => 'tekne',
                'description' => 'Yat ve tekne gövde/makine ve üçüncü şahıs sorumluluk.',
                'category' => 'diger',
                'color' => '#0284c7',
                'icon' => 'sparkles',
                'renewal_reminder_days' => 30,
            ],
            // Tarım & özel branş
            [
                'name' => 'Tarım Sigortası',
                'slug' => 'tarim',
                'description' => 'Bitkisel ürün, sera ve hayvancılık teminatları.',
                'category' => 'tarim',
                'color' => '#16a34a',
                'icon' => 'rectangle-stack',
                'renewal_reminder_days' => 30,
            ],
            [
                'name' => 'Mühendislik Sigortası',
                'slug' => 'muhendislik',
                'description' => 'Makine montaj, deneme ve garanti süreçleri.',
                'category' => 'is',
                'color' => '#64748b',
                'icon' => 'briefcase',
                'renewal_reminder_days' => 30,
            ],
            [
                'name' => 'Makine Kırılması Sigortası',
                'slug' => 'makine-kirilmasi',
                'description' => 'İşletme makinelerinde ani ve beklenmedik hasarlar.',
                'category' => 'is',
                'color' => '#475569',
                'icon' => 'briefcase',
                'renewal_reminder_days' => 30,
            ],
            // Sorumluluk & siber
            [
                'name' => 'İşveren Mali Mesuliyet',
                'slug' => 'isveren-mali-mesuliyet',
                'description' => 'İş kazası ve meslek hastalığından doğan hukuki sorumluluk.',
                'category' => 'is',
                'color' => '#dc2626',
                'icon' => 'briefcase',
                'renewal_reminder_days' => 30,
            ],
            [
                'name' => 'Profesyonel Sorumluluk (PL)',
                'slug' => 'profesyonel-sorumluluk',
                'description' => 'Mesleki hata ve ihmallerden doğan zararlar.',
                'category' => 'is',
                'color' => '#b91c1c',
                'icon' => 'briefcase',
                'renewal_reminder_days' => 30,
            ],
            [
                'name' => 'Siber Risk Sigortası',
                'slug' => 'siber-risk',
                'description' => 'Siber saldırı, veri ihlali ve iş kesintisi teminatları.',
                'category' => 'is',
                'color' => '#4f46e5',
                'icon' => 'shield-check',
                'renewal_reminder_days' => 30,
            ],
            [
                'name' => 'Kefalet Sigortası',
                'slug' => 'kefalet',
                'description' => 'İhale ve sözleşme teminatları için kefalet.',
                'category' => 'is',
                'color' => '#0f766e',
                'icon' => 'briefcase',
                'renewal_reminder_days' => 30,
            ],
            [
                'name' => 'Hırsızlık Sigortası',
                'slug' => 'hirsizlik',
                'description' => 'Para, değerli eşya ve stok hırsızlığı teminatı.',
                'category' => 'is',
                'color' => '#be123c',
                'icon' => 'shield-check',
                'renewal_reminder_days' => 30,
            ],
            [
                'name' => 'Evcil Hayvan Sigortası',
                'slug' => 'evcil-hayvan',
                'description' => 'Veteriner tedavi ve sorumluluk teminatları.',
                'category' => 'diger',
                'color' => '#d97706',
                'icon' => 'sparkles',
                'renewal_reminder_days' => 30,
            ],
            [
                'name' => 'Eğitim Sigortası',
                'slug' => 'egitim',
                'description' => 'Öğrenci ve eğitim kurumu özel teminatları.',
                'category' => 'diger',
                'color' => '#2563eb',
                'icon' => 'sparkles',
                'renewal_reminder_days' => 30,
            ],
            [
                'name' => 'Emtia (Depo) Sigortası',
                'slug' => 'emtia-depo',
                'description' => 'Depolanan mallar için yangın, su ve hırsızlık riskleri.',
                'category' => 'is',
                'color' => '#57534e',
                'icon' => 'rectangle-stack',
                'renewal_reminder_days' => 30,
            ],
            [
                'name' => 'Hukuksal Koruma',
                'slug' => 'hukuksal-koruma',
                'description' => 'Dava ve avukatlık masrafları için hukuki koruma.',
                'category' => 'diger',
                'color' => '#1e40af',
                'icon' => 'shield-check',
                'renewal_reminder_days' => 30,
            ],
        ];
    }

    /**
     * Türkiye’de faaliyet gösteren ana sigorta şirketleri (ticari unvan özeti).
     *
     * @return list<array{name: string, code: string}>
     */
    private function insuranceCompanies(): array
    {
        return [
            ['name' => 'Türkiye Sigorta A.Ş.', 'code' => 'TURKIYE'],
            ['name' => 'Allianz Sigorta A.Ş.', 'code' => 'ALLIANZ'],
            ['name' => 'Anadolu Sigorta A.Ş.', 'code' => 'ANADOLU'],
            ['name' => 'Aksigorta A.Ş.', 'code' => 'AKSIGORTA'],
            ['name' => 'AXA Sigorta A.Ş.', 'code' => 'AXA'],
            ['name' => 'Groupama Sigorta A.Ş.', 'code' => 'GROUPAMA'],
            ['name' => 'HDI Sigorta A.Ş.', 'code' => 'HDI'],
            ['name' => 'Mapfre Sigorta A.Ş.', 'code' => 'MAPFRE'],
            ['name' => 'Sompo Sigorta A.Ş.', 'code' => 'SOMPO'],
            ['name' => 'Türk Nippon Sigorta A.Ş.', 'code' => 'NIPPON'],
            ['name' => 'Zurich Sigorta A.Ş.', 'code' => 'ZURICH'],
            ['name' => 'Quick Sigorta A.Ş.', 'code' => 'QUICK'],
            ['name' => 'Neova Sigorta A.Ş.', 'code' => 'NEOVA'],
            ['name' => 'Ray Sigorta A.Ş.', 'code' => 'RAY'],
            ['name' => 'Şeker Sigorta A.Ş.', 'code' => 'SEKER'],
            ['name' => 'Generali Sigorta A.Ş.', 'code' => 'GENERALI'],
            ['name' => 'Unico Sigorta A.Ş.', 'code' => 'UNICO'],
            ['name' => 'Magdeburger Sigorta A.Ş.', 'code' => 'MAGDEBURGER'],
            ['name' => 'Hepiyi Sigorta A.Ş.', 'code' => 'HEPIYI'],
            ['name' => 'Eureko Sigorta A.Ş.', 'code' => 'EUREKO'],
            ['name' => 'Gulf Sigorta A.Ş.', 'code' => 'GULF'],
            ['name' => 'Demir Sigorta A.Ş.', 'code' => 'DEMIR'],
            ['name' => 'Doğa Sigorta A.Ş.', 'code' => 'DOGA'],
            ['name' => 'Orient Sigorta A.Ş.', 'code' => 'ORIENT'],
            ['name' => 'Koru Sigorta A.Ş.', 'code' => 'KORU'],
            ['name' => 'Bereket Sigorta A.Ş.', 'code' => 'BEREKET'],
            ['name' => 'Ankara Sigorta A.Ş.', 'code' => 'ANKARA'],
            ['name' => 'Ethica Sigorta A.Ş.', 'code' => 'ETHICA'],
            ['name' => 'Atlas Mutuel Sigorta A.Ş.', 'code' => 'ATLAS'],
            ['name' => 'Prive Sigorta A.Ş.', 'code' => 'PRIVE'],
        ];
    }
}

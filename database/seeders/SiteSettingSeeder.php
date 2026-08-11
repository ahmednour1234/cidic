<?php

namespace Database\Seeders;

use App\Models\SiteSetting;
use Illuminate\Database\Seeder;

class SiteSettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // group => [key => [value, type, label]]
            'general' => [
                'company_name_ar' => ['سدك للإستقدام', 'text', 'اسم الشركة (عربي)'],
                'company_name_en' => ['CIDIC RECRUITMENT', 'text', 'اسم الشركة (إنجليزي)'],
                'company_description' => [
                    'نوفر خدمات الاستقدام، الإيجار الشهري، ونقل الخدمات باحترافية وسرعة وفق الأنظمة المعتمدة في المملكة العربية السعودية.',
                    'textarea',
                    'وصف الشركة',
                ],
                'license_number' => ['1234567890', 'text', 'رقم الترخيص'],
                'logo' => [null, 'image', 'شعار الموقع'],
                'favicon' => [null, 'image', 'أيقونة المتصفح'],
                'footer_text' => ['© ' . date('Y') . ' سدك للإستقدام. جميع الحقوق محفوظة.', 'text', 'نص التذييل'],
            ],
            'contact' => [
                'phone' => ['0112345678', 'text', 'رقم الهاتف'],
                'whatsapp' => ['0501234567', 'text', 'رقم الواتساب'],
                'email' => ['info@cidic.sa', 'email', 'البريد الإلكتروني'],
                'address' => ['الرياض، المملكة العربية السعودية', 'text', 'العنوان'],
                'google_map_url' => [null, 'url', 'رابط خرائط جوجل'],
                'whatsapp_default_message' => [
                    'السلام عليكم، أرغب بالاستفسار عن خدمات الاستقدام.',
                    'textarea',
                    'رسالة الواتساب الافتراضية',
                ],
            ],
            'social' => [
                'facebook' => [null, 'url', 'فيسبوك'],
                'instagram' => [null, 'url', 'انستقرام'],
                'twitter' => [null, 'url', 'إكس (تويتر)'],
                'tiktok' => [null, 'url', 'تيك توك'],
                'snapchat' => [null, 'url', 'سناب شات'],
            ],
            'seo' => [
                'meta_title' => ['سدك للإستقدام | حلول موثوقة لاستقدام العمالة المنزلية', 'text', 'عنوان الميتا'],
                'meta_description' => [
                    'نوفر خدمات استقدام العمالة المنزلية، الإيجار الشهري، ونقل الخدمات في المملكة العربية السعودية وفق الأنظمة المعتمدة.',
                    'textarea',
                    'وصف الميتا',
                ],
                'og_image' => [null, 'image', 'صورة المشاركة'],
            ],
            'homepage' => [
                // Newline splits the headline; the second line is highlighted in the hero.
                'hero_title' => ["حلول موثوقة لاستقدام\nالعمالة المنزلية في السعودية", 'textarea', 'عنوان الواجهة'],
                'hero_subtitle' => [
                    'نوفر خدمات الاستقدام، الإيجار الشهري، ونقل الخدمات باحترافية وسرعة وفق الأنظمة المعتمدة.',
                    'textarea',
                    'وصف الواجهة',
                ],
                'hero_image' => [null, 'image', 'صورة الواجهة (صورة واحدة)'],
                // Optional layered hero: background scene + cut-out subject (PNG
                // with transparency). When both are set they replace hero_image.
                'hero_scene_image' => [null, 'image', 'صورة الخلفية (الواجهة المركبة)'],
                'hero_subject_image' => [null, 'image', 'صورة العاملة المقصوصة (PNG شفاف)'],
            ],
        ];

        $sort = 0;

        foreach ($settings as $group => $items) {
            foreach ($items as $key => [$value, $type, $label]) {
                SiteSetting::updateOrCreate(
                    ['key' => $key],
                    [
                        'value' => $value,
                        'type' => $type,
                        'group' => $group,
                        'label' => $label,
                        'sort_order' => $sort++,
                    ],
                );
            }
        }
    }
}

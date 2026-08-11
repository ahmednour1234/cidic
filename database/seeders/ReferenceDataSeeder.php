<?php

namespace Database\Seeders;

use App\Models\CandidateCategory;
use App\Models\Faq;
use App\Models\HowItWorks;
use App\Models\Nationality;
use App\Models\Service;
use App\Models\Testimonial;
use App\Models\WhyChooseUs;
use Illuminate\Database\Seeder;

class ReferenceDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedNationalities();
        $this->seedCategories();
        $this->seedServices();
        $this->seedHowItWorks();
        $this->seedWhyChooseUs();
        $this->seedFaqs();
        $this->seedTestimonials();
    }

    protected function seedNationalities(): void
    {
        $rows = [
            ['الفلبين', 'Philippines', 'philippines', 'PH'],
            ['بنغلاديش', 'Bangladesh', 'bangladesh', 'BD'],
            ['سريلانكا', 'Sri Lanka', 'sri-lanka', 'LK'],
            ['كينيا', 'Kenya', 'kenya', 'KE'],
            ['إثيوبيا', 'Ethiopia', 'ethiopia', 'ET'],
        ];

        foreach ($rows as $i => [$ar, $en, $slug, $code]) {
            Nationality::updateOrCreate(
                ['slug' => $slug],
                [
                    'name_ar' => $ar,
                    'name_en' => $en,
                    'country_code' => $code,
                    'description' => "عمالة منزلية مدربة من {$ar} وفق الأنظمة المعتمدة.",
                    'is_active' => true,
                    'sort_order' => $i,
                ],
            );
        }
    }

    protected function seedCategories(): void
    {
        $rows = [
            ['عاملة منزلية', 'Housemaid', 'housemaid'],
            ['مربية أطفال', 'Nanny', 'nanny'],
            ['مقدمة رعاية', 'Caregiver', 'caregiver'],
            ['رعاية كبار السن', 'Elderly Care', 'elderly-care'],
            ['طباخة', 'Cook', 'cook'],
            ['عاملة نظافة', 'Cleaner', 'cleaner'],
        ];

        foreach ($rows as $i => [$ar, $en, $slug]) {
            CandidateCategory::updateOrCreate(
                ['slug' => $slug],
                [
                    'name_ar' => $ar,
                    'name_en' => $en,
                    'is_active' => true,
                    'sort_order' => $i,
                ],
            );
        }
    }

    protected function seedServices(): void
    {
        $rows = [
            [
                'الاستقدام',
                'recruitment',
                '👥',
                'استقدام العمالة المنزلية من مختلف الجنسيات وفق الأنظمة المعتمدة.',
                'نقدم خدمة الاستقدام الكاملة بدءاً من اختيار العاملة المناسبة ومروراً بإنهاء كافة الإجراءات النظامية والتأشيرات، وصولاً إلى استلام العاملة في المملكة مع ضمان الالتزام بجميع اشتراطات وزارة الموارد البشرية.',
            ],
            [
                'الإيجار الشهري',
                'monthly-rental',
                '📅',
                'توفير عمالة منزلية بعقود شهرية مرنة تناسب احتياجك.',
                'خدمة الإيجار الشهري تتيح لك الاستفادة من خدمات العمالة المنزلية لفترة محددة دون الالتزام بعقد استقدام طويل الأجل، مع إمكانية التجديد أو الإنهاء وفق حاجتك.',
            ],
            [
                'نقل الخدمات',
                'transfer-services',
                '✈️',
                'إتمام إجراءات نقل خدمات العمالة بسرعة واحترافية.',
                'نتولى إنهاء كافة إجراءات نقل الخدمات من الكفيل السابق إليك بشكل نظامي وسريع، مع متابعة كاملة للمعاملة حتى إتمامها.',
            ],
        ];

        foreach ($rows as $i => [$title, $slug, $icon, $short, $description]) {
            Service::updateOrCreate(
                ['slug' => $slug],
                [
                    'title' => $title,
                    'icon' => $icon,
                    'short_description' => $short,
                    'description' => $description,
                    'is_active' => true,
                    'sort_order' => $i,
                ],
            );
        }
    }

    protected function seedHowItWorks(): void
    {
        $rows = [
            ['تواصل معنا', '🎧', 'تواصل معنا عبر الهاتف أو الواتساب أو من خلال نموذج الطلب في الموقع.'],
            ['تحديد الطلب', '📋', 'نساعدك في تحديد الجنسية والمهنة المناسبة لاحتياجات أسرتك.'],
            ['إجراءات الاستقدام', '📄', 'نتولى إنهاء كافة الإجراءات النظامية والتأشيرات نيابةً عنك.'],
            ['الوصول والمتابعة', '🤝', 'استلام العاملة ومتابعة مستمرة لضمان رضاك عن الخدمة.'],
        ];

        foreach ($rows as $i => [$title, $icon, $description]) {
            HowItWorks::updateOrCreate(
                ['title' => $title],
                [
                    'icon' => $icon,
                    'description' => $description,
                    'is_active' => true,
                    'sort_order' => $i,
                ],
            );
        }
    }

    protected function seedWhyChooseUs(): void
    {
        $rows = [
            ['سرعة الإجراءات', '⚡', 'إنجاز المعاملات في أقصر وقت ممكن مع متابعة دقيقة لكل مرحلة.'],
            ['عقود موثقة', '🛡️', 'جميع العقود موثقة ونظامية وفق أنظمة وزارة الموارد البشرية.'],
            ['متابعة مستمرة', '🔄', 'فريق مختص يتابع معك قبل وبعد وصول العاملة.'],
            ['خدمة عملاء', '🎧', 'خدمة عملاء متاحة للرد على استفساراتك وحل أي إشكال.'],
        ];

        foreach ($rows as $i => [$title, $icon, $description]) {
            WhyChooseUs::updateOrCreate(
                ['title' => $title],
                [
                    'icon' => $icon,
                    'description' => $description,
                    'is_active' => true,
                    'sort_order' => $i,
                ],
            );
        }
    }

    protected function seedFaqs(): void
    {
        $rows = [
            ['كم تستغرق مدة الاستقدام؟', 'تختلف المدة حسب الجنسية وإجراءات السفارة، وغالباً تتراوح بين 30 و90 يوماً من تاريخ اكتمال المستندات.'],
            ['ما هي المستندات المطلوبة؟', 'يلزم توفر هوية وطنية سارية، وتحديث بيانات الأسرة، وإصدار تأشيرة الاستقدام عبر منصة مساند.'],
            ['هل يمكن استبدال العاملة؟', 'نعم، وفق شروط العقد والفترة التجريبية المحددة نظاماً في منصة مساند.'],
            ['ما الفرق بين الاستقدام والإيجار الشهري؟', 'الاستقدام عقد طويل الأجل تكون فيه العاملة على كفالتك، أما الإيجار الشهري فهو تعاقد مؤقت تبقى فيه العاملة على كفالة المكتب.'],
            ['كيف يتم الدفع؟', 'يتم الدفع وفق دفعات موضحة في العقد، ويمكنك الاستفسار عن التفاصيل بالتواصل معنا.'],
        ];

        foreach ($rows as $i => [$question, $answer]) {
            Faq::updateOrCreate(
                ['question' => $question],
                [
                    'answer' => $answer,
                    'is_active' => true,
                    'sort_order' => $i,
                ],
            );
        }
    }

    protected function seedTestimonials(): void
    {
        $rows = [
            ['أحمد العتيبي', 'الرياض', 'تعاملت مع المكتب في استقدام عاملة منزلية وكانت الإجراءات سريعة ومنظمة. أشكر فريق العمل على المتابعة المستمرة.', 5],
            ['نورة السالم', 'جدة', 'خدمة ممتازة ومصداقية عالية. العاملة وصلت في الوقت المحدد وكانت مدربة بشكل جيد.', 5],
            ['خالد الدوسري', 'الدمام', 'استفدت من خدمة الإيجار الشهري وكانت مناسبة جداً لظروفي. أنصح بالتعامل معهم.', 4],
        ];

        foreach ($rows as $i => [$name, $city, $review, $rating]) {
            Testimonial::updateOrCreate(
                ['name' => $name],
                [
                    'city' => $city,
                    'review' => $review,
                    'rating' => $rating,
                    'is_active' => true,
                    'sort_order' => $i,
                ],
            );
        }
    }
}

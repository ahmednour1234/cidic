<?php

namespace App\Http\Controllers;

use App\Models\Candidate;
use App\Models\Faq;
use App\Models\HowItWorks;
use App\Models\Nationality;
use App\Models\Service;
use App\Models\Testimonial;
use App\Models\WhyChooseUs;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        return view('pages.home', [
            'services' => Service::query()->active()->ordered()->get(),
            'nationalities' => Nationality::query()
                ->active()
                ->ordered()
                ->withCount(['candidates' => fn ($q) => $q->active()->available()])
                ->get(),
            // Featured first, then any other available profile, to always fill the row.
            'candidates' => Candidate::query()
                ->with(['nationality:id,name_ar,slug,flag', 'category:id,name_ar,slug'])
                ->active()
                ->available()
                ->orderByDesc('featured')
                ->orderBy('sort_order')
                ->orderByDesc('created_at')
                ->limit(4)
                ->get(),
            'howItWorks' => HowItWorks::query()->active()->ordered()->get(),
            'whyChooseUs' => WhyChooseUs::query()->active()->ordered()->get(),
            'testimonials' => Testimonial::query()->active()->ordered()->get(),
            'faqs' => Faq::query()->active()->ordered()->limit(6)->get(),
        ]);
    }
}

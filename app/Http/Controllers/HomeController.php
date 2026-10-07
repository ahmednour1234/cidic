<?php

namespace App\Http\Controllers;

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
            // Counts the CV stock the public catalogue actually serves, and
            // lists only nationalities that have something to show - an entry
            // reading "0 سيرة متاحة" is just a dead end for the visitor.
            'nationalities' => Nationality::query()
                ->active()
                ->ordered()
                ->whereHas('workers', fn ($q) => $q->publiclyVisible())
                ->withCount(['workers as candidates_count' => fn ($q) => $q->publiclyVisible()])
                ->get(),
            // The homepage no longer lists CVs; the catalogue lives at /cvs.
            'howItWorks' => HowItWorks::query()->active()->ordered()->get(),
            'whyChooseUs' => WhyChooseUs::query()->active()->ordered()->get(),
            'testimonials' => Testimonial::query()->active()->ordered()->get(),
            'faqs' => Faq::query()->active()->ordered()->limit(6)->get(),
        ]);
    }
}

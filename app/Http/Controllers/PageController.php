<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use App\Models\HowItWorks;
use App\Models\Page;
use App\Models\WhyChooseUs;
use Illuminate\View\View;

class PageController extends Controller
{
    public function about(): View
    {
        return view('pages.about', [
            'page' => $this->page('about', 'من نحن'),
            'whyChooseUs' => WhyChooseUs::query()->active()->ordered()->get(),
            'howItWorks' => HowItWorks::query()->active()->ordered()->get(),
        ]);
    }

    public function faq(): View
    {
        return view('pages.faq', [
            'faqs' => Faq::query()->active()->ordered()->get(),
        ]);
    }

    public function privacyPolicy(): View
    {
        return view('pages.legal', [
            'page' => $this->page('privacy-policy', 'سياسة الخصوصية'),
        ]);
    }

    public function terms(): View
    {
        return view('pages.legal', [
            'page' => $this->page('terms', 'الشروط والأحكام'),
        ]);
    }

    /**
     * Fetch an editable CMS page, falling back to an empty shell so the route
     * still renders if an admin deletes the record.
     */
    protected function page(string $slug, string $fallbackTitle): Page
    {
        return Page::query()->active()->where('slug', $slug)->first()
            ?? new Page(['title' => $fallbackTitle, 'content' => null, 'slug' => $slug]);
    }
}

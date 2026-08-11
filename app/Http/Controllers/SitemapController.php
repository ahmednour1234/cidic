<?php

namespace App\Http\Controllers;

use App\Models\Candidate;
use App\Models\Service;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $urls = [
            ['loc' => route('home'), 'priority' => '1.0', 'changefreq' => 'daily'],
            ['loc' => route('services.index'), 'priority' => '0.8', 'changefreq' => 'weekly'],
            ['loc' => route('nationalities.index'), 'priority' => '0.8', 'changefreq' => 'weekly'],
            ['loc' => route('candidates.index'), 'priority' => '0.9', 'changefreq' => 'daily'],
            ['loc' => route('about'), 'priority' => '0.6', 'changefreq' => 'monthly'],
            ['loc' => route('faq'), 'priority' => '0.6', 'changefreq' => 'monthly'],
            ['loc' => route('contact.create'), 'priority' => '0.6', 'changefreq' => 'monthly'],
            ['loc' => route('recruitment-requests.create'), 'priority' => '0.7', 'changefreq' => 'monthly'],
            ['loc' => route('privacy-policy'), 'priority' => '0.3', 'changefreq' => 'yearly'],
            ['loc' => route('terms'), 'priority' => '0.3', 'changefreq' => 'yearly'],
        ];

        foreach (Service::query()->active()->ordered()->get() as $service) {
            $urls[] = [
                'loc' => route('services.show', $service),
                'lastmod' => $service->updated_at?->toAtomString(),
                'priority' => '0.7',
                'changefreq' => 'monthly',
            ];
        }

        foreach (Candidate::query()->active()->available()->latest()->limit(1000)->get() as $candidate) {
            $urls[] = [
                'loc' => route('candidates.show', $candidate),
                'lastmod' => $candidate->updated_at?->toAtomString(),
                'priority' => '0.8',
                'changefreq' => 'weekly',
            ];
        }

        return response()
            ->view('sitemap', ['urls' => $urls])
            ->header('Content-Type', 'application/xml');
    }
}

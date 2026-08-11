<?php

namespace App\Http\Controllers;

use App\Models\Nationality;
use Illuminate\View\View;

class NationalityController extends Controller
{
    public function index(): View
    {
        return view('pages.nationalities', [
            'nationalities' => Nationality::query()
                ->active()
                ->ordered()
                ->withCount(['candidates' => fn ($q) => $q->active()->available()])
                ->get(),
        ]);
    }
}

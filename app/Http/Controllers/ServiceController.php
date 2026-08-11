<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ServiceController extends Controller
{
    public function index(): View
    {
        return view('pages.services', [
            'services' => Service::query()->active()->ordered()->get(),
        ]);
    }

    public function show(Service $service): View
    {
        if (! $service->is_active) {
            throw new NotFoundHttpException();
        }

        return view('pages.service-show', [
            'service' => $service,
            'services' => Service::query()
                ->active()
                ->ordered()
                ->where('id', '!=', $service->id)
                ->get(),
        ]);
    }
}

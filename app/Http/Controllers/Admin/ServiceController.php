<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Services\ActivityLogger;
use App\Services\FileUploadService;
use App\Support\ArabicSlug;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ServiceController extends Controller
{
    public function __construct(
        protected FileUploadService $files,
        protected ActivityLogger $activity,
    ) {}

    public function index(): View
    {
        return view('admin.services.index', [
            'services' => Service::query()->ordered()->paginate(20),
        ]);
    }

    public function create(): View
    {
        return view('admin.services.create', ['service' => new Service()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['slug'] = $this->uniqueSlug($data['slug'] ?? null, $data['title']);

        if ($image = $request->file('image')) {
            $data['image'] = $this->files->store($image, 'services');
        }

        $service = Service::create($data);
        $this->activity->log('service.created', $service);

        return redirect()->route('admin.services.index')->with('success', 'تمت إضافة الخدمة بنجاح.');
    }

    public function edit(Service $service): View
    {
        return view('admin.services.edit', ['service' => $service]);
    }

    public function update(Request $request, Service $service): RedirectResponse
    {
        $data = $this->validated($request, $service);
        $data['slug'] = $this->uniqueSlug($data['slug'] ?? null, $data['title'], $service->id);

        if ($image = $request->file('image')) {
            $data['image'] = $this->files->replace($image, 'services', $service->image);
        }

        $service->update($data);
        $this->activity->log('service.updated', $service);

        return redirect()->route('admin.services.index')->with('success', 'تم تحديث الخدمة بنجاح.');
    }

    public function destroy(Service $service): RedirectResponse
    {
        $service->delete();
        $this->activity->log('service.deleted', $service);

        return redirect()->route('admin.services.index')->with('success', 'تم حذف الخدمة.');
    }

    /**
     * @return array<string, mixed>
     */
    protected function validated(Request $request, ?Service $service = null): array
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'slug' => [
                'nullable', 'string', 'max:150', 'alpha_dash',
                Rule::unique('services', 'slug')->ignore($service?->id),
            ],
            'short_description' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string', 'max:10000'],
            'icon' => ['nullable', 'string', 'max:100'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:65535'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
        ], [], [
            'title' => 'العنوان',
            'slug' => 'الرابط',
            'short_description' => 'الوصف المختصر',
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        unset($validated['image']);

        return $validated;
    }

    protected function uniqueSlug(?string $slug, string $title, ?int $ignoreId = null): string
    {
        $base = filled($slug) ? ArabicSlug::make($slug) : ArabicSlug::make($title);
        $candidate = $base;
        $suffix = 1;

        while (Service::withTrashed()
            ->where('slug', $candidate)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->exists()
        ) {
            $candidate = $base . '-' . (++$suffix);
        }

        return $candidate;
    }
}

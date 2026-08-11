<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Nationality;
use App\Services\ActivityLogger;
use App\Services\FileUploadService;
use App\Support\ArabicSlug;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class NationalityController extends Controller
{
    public function __construct(
        protected FileUploadService $files,
        protected ActivityLogger $activity,
    ) {}

    public function index(): View
    {
        return view('admin.nationalities.index', [
            'nationalities' => Nationality::query()
                ->withCount('candidates')
                ->ordered()
                ->paginate(20),
        ]);
    }

    public function create(): View
    {
        return view('admin.nationalities.create', ['nationality' => new Nationality()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['slug'] = $this->uniqueSlug($data['slug'] ?? null, $data['name_en'] ?? $data['name_ar']);

        if ($flag = $request->file('flag')) {
            $data['flag'] = $this->files->store($flag, 'nationalities');
        }

        $nationality = Nationality::create($data);
        $this->activity->log('nationality.created', $nationality);

        return redirect()->route('admin.nationalities.index')->with('success', 'تمت إضافة الجنسية بنجاح.');
    }

    public function edit(Nationality $nationality): View
    {
        return view('admin.nationalities.edit', ['nationality' => $nationality]);
    }

    public function update(Request $request, Nationality $nationality): RedirectResponse
    {
        $data = $this->validated($request, $nationality);
        $data['slug'] = $this->uniqueSlug(
            $data['slug'] ?? null,
            $data['name_en'] ?? $data['name_ar'],
            $nationality->id,
        );

        if ($flag = $request->file('flag')) {
            $data['flag'] = $this->files->replace($flag, 'nationalities', $nationality->flag);
        }

        $nationality->update($data);
        $this->activity->log('nationality.updated', $nationality);

        return redirect()->route('admin.nationalities.index')->with('success', 'تم تحديث الجنسية بنجاح.');
    }

    public function destroy(Nationality $nationality): RedirectResponse
    {
        // Candidates reference nationalities with restrictOnDelete; block instead of erroring.
        if ($nationality->candidates()->exists()) {
            return back()->with('error', 'لا يمكن حذف الجنسية لارتباطها بسير ذاتية.');
        }

        $nationality->delete();
        $this->activity->log('nationality.deleted', $nationality);

        return redirect()->route('admin.nationalities.index')->with('success', 'تم حذف الجنسية.');
    }

    /**
     * @return array<string, mixed>
     */
    protected function validated(Request $request, ?Nationality $nationality = null): array
    {
        $validated = $request->validate([
            'name_ar' => ['required', 'string', 'max:150'],
            'name_en' => ['nullable', 'string', 'max:150'],
            'slug' => [
                'nullable', 'string', 'max:150', 'alpha_dash',
                Rule::unique('nationalities', 'slug')->ignore($nationality?->id),
            ],
            'country_code' => ['nullable', 'string', 'max:8'],
            'flag' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:2048'],
            'description' => ['nullable', 'string', 'max:2000'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:65535'],
        ], [], [
            'name_ar' => 'الاسم بالعربية',
            'name_en' => 'الاسم بالإنجليزية',
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        unset($validated['flag']);

        return $validated;
    }

    protected function uniqueSlug(?string $slug, string $name, ?int $ignoreId = null): string
    {
        $base = ArabicSlug::make(filled($slug) ? $slug : $name);
        $candidate = $base;
        $suffix = 1;

        while (Nationality::withTrashed()
            ->where('slug', $candidate)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->exists()
        ) {
            $candidate = $base . '-' . (++$suffix);
        }

        return $candidate;
    }
}

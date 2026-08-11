<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CandidateCategory;
use App\Services\ActivityLogger;
use App\Support\ArabicSlug;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CandidateCategoryController extends Controller
{
    public function __construct(protected ActivityLogger $activity) {}

    public function index(): View
    {
        return view('admin.categories.index', [
            'categories' => CandidateCategory::query()
                ->withCount('candidates')
                ->ordered()
                ->paginate(20),
        ]);
    }

    public function create(): View
    {
        return view('admin.categories.create', ['category' => new CandidateCategory()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['slug'] = $this->uniqueSlug($data['slug'] ?? null, $data['name_en'] ?? $data['name_ar']);

        $category = CandidateCategory::create($data);
        $this->activity->log('category.created', $category);

        return redirect()->route('admin.categories.index')->with('success', 'تمت إضافة التصنيف بنجاح.');
    }

    public function edit(CandidateCategory $category): View
    {
        return view('admin.categories.edit', ['category' => $category]);
    }

    public function update(Request $request, CandidateCategory $category): RedirectResponse
    {
        $data = $this->validated($request, $category);
        $data['slug'] = $this->uniqueSlug(
            $data['slug'] ?? null,
            $data['name_en'] ?? $data['name_ar'],
            $category->id,
        );

        $category->update($data);
        $this->activity->log('category.updated', $category);

        return redirect()->route('admin.categories.index')->with('success', 'تم تحديث التصنيف بنجاح.');
    }

    public function destroy(CandidateCategory $category): RedirectResponse
    {
        if ($category->candidates()->exists()) {
            return back()->with('error', 'لا يمكن حذف التصنيف لارتباطه بسير ذاتية.');
        }

        $category->delete();
        $this->activity->log('category.deleted', $category);

        return redirect()->route('admin.categories.index')->with('success', 'تم حذف التصنيف.');
    }

    /**
     * @return array<string, mixed>
     */
    protected function validated(Request $request, ?CandidateCategory $category = null): array
    {
        $validated = $request->validate([
            'name_ar' => ['required', 'string', 'max:150'],
            'name_en' => ['nullable', 'string', 'max:150'],
            'slug' => [
                'nullable', 'string', 'max:150', 'alpha_dash',
                Rule::unique('candidate_categories', 'slug')->ignore($category?->id),
            ],
            'icon' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:2000'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:65535'],
        ], [], [
            'name_ar' => 'الاسم بالعربية',
            'name_en' => 'الاسم بالإنجليزية',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        return $validated;
    }

    protected function uniqueSlug(?string $slug, string $name, ?int $ignoreId = null): string
    {
        $base = ArabicSlug::make(filled($slug) ? $slug : $name);
        $candidate = $base;
        $suffix = 1;

        while (CandidateCategory::withTrashed()
            ->where('slug', $candidate)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->exists()
        ) {
            $candidate = $base . '-' . (++$suffix);
        }

        return $candidate;
    }
}

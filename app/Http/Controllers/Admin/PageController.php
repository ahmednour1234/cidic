<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Support\ArabicSlug;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PageController extends Controller
{
    public function index(): View
    {
        return view('admin.pages.index', [
            'pages' => Page::query()->orderBy('title')->paginate(20),
        ]);
    }

    public function create(): View
    {
        return view('admin.pages.create', ['page' => new Page()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['slug'] = ArabicSlug::make($data['slug'] ?: $data['title']);

        Page::create($data);

        return redirect()->route('admin.pages.index')->with('success', 'تمت إضافة الصفحة بنجاح.');
    }

    public function edit(Page $page): View
    {
        return view('admin.pages.edit', ['page' => $page]);
    }

    public function update(Request $request, Page $page): RedirectResponse
    {
        $data = $this->validated($request, $page);
        $data['slug'] = ArabicSlug::make($data['slug'] ?: $data['title']);

        $page->update($data);

        return redirect()->route('admin.pages.index')->with('success', 'تم تحديث الصفحة بنجاح.');
    }

    public function destroy(Page $page): RedirectResponse
    {
        $page->delete();

        return redirect()->route('admin.pages.index')->with('success', 'تم حذف الصفحة.');
    }

    /**
     * @return array<string, mixed>
     */
    protected function validated(Request $request, ?Page $page = null): array
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'slug' => [
                'nullable', 'string', 'max:200', 'alpha_dash',
                Rule::unique('pages', 'slug')->ignore($page?->id),
            ],
            // Trusted admin HTML; rendered with {!! !!} in the public view.
            'content' => ['nullable', 'string', 'max:100000'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'is_active' => ['nullable', 'boolean'],
        ], [], [
            'title' => 'العنوان',
            'content' => 'المحتوى',
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $validated['slug'] = $validated['slug'] ?? null;

        return $validated;
    }
}

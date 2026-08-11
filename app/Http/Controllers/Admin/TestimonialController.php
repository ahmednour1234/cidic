<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Testimonial;
use App\Services\FileUploadService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TestimonialController extends Controller
{
    public function __construct(protected FileUploadService $files) {}

    public function index(): View
    {
        return view('admin.testimonials.index', [
            'records' => Testimonial::query()->ordered()->paginate(20),
        ]);
    }

    public function create(): View
    {
        return view('admin.testimonials.create', ['record' => new Testimonial()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        if ($avatar = $request->file('avatar')) {
            $data['avatar'] = $this->files->store($avatar, 'testimonials');
        }

        Testimonial::create($data);

        return redirect()->route('admin.testimonials.index')->with('success', 'تمت إضافة الرأي بنجاح.');
    }

    public function edit(Testimonial $testimonial): View
    {
        return view('admin.testimonials.edit', ['record' => $testimonial]);
    }

    public function update(Request $request, Testimonial $testimonial): RedirectResponse
    {
        $data = $this->validated($request);

        if ($avatar = $request->file('avatar')) {
            $data['avatar'] = $this->files->replace($avatar, 'testimonials', $testimonial->avatar);
        }

        $testimonial->update($data);

        return redirect()->route('admin.testimonials.index')->with('success', 'تم تحديث الرأي بنجاح.');
    }

    public function destroy(Testimonial $testimonial): RedirectResponse
    {
        // Hard delete (no soft deletes on this model): remove the avatar too.
        $this->files->delete($testimonial->avatar);
        $testimonial->delete();

        return redirect()->route('admin.testimonials.index')->with('success', 'تم حذف الرأي.');
    }

    /**
     * @return array<string, mixed>
     */
    protected function validated(Request $request): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'city' => ['nullable', 'string', 'max:128'],
            'review' => ['required', 'string', 'max:2000'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:65535'],
        ], [], [
            'name' => 'الاسم',
            'review' => 'الرأي',
            'rating' => 'التقييم',
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        unset($validated['avatar']);

        return $validated;
    }
}

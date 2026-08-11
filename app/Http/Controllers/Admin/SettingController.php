<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use App\Services\ActivityLogger;
use App\Services\FileUploadService;
use App\Services\SettingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingController extends Controller
{
    /** Groups rendered as tabs, in order. */
    protected const GROUPS = [
        'general' => 'عام',
        'contact' => 'التواصل',
        'social' => 'التواصل الاجتماعي',
        'seo' => 'تحسين الظهور',
        'homepage' => 'الصفحة الرئيسية',
    ];

    public function __construct(
        protected SettingService $settings,
        protected FileUploadService $files,
        protected ActivityLogger $activity,
    ) {}

    public function edit(): View
    {
        $grouped = SiteSetting::query()
            ->orderBy('sort_order')
            ->get()
            ->groupBy('group');

        return view('admin.settings.edit', [
            'groups' => self::GROUPS,
            'grouped' => $grouped,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $definitions = SiteSetting::query()->get()->keyBy('key');

        $validated = $request->validate([
            'settings' => ['array'],
            'settings.*' => ['nullable', 'string', 'max:5000'],
            'files' => ['array'],
            'files.*' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,svg,ico', 'max:4096'],
        ]);

        $values = [];

        foreach ($validated['settings'] ?? [] as $key => $value) {
            // Ignore keys that are not registered settings.
            if (! $definitions->has($key)) {
                continue;
            }

            $values[$key] = $value;
        }

        // Image settings arrive separately so an untouched field keeps its value.
        foreach ($request->file('files', []) as $key => $file) {
            if (! $definitions->has($key) || ! $file) {
                continue;
            }

            $values[$key] = $this->files->replace(
                $file,
                'settings',
                $definitions[$key]->value,
            );
        }

        foreach ($values as $key => $value) {
            $definition = $definitions[$key];
            $definition->update(['value' => $value]);
        }

        // Model events flush the cache, but flush explicitly for the memoised map.
        $this->settings->flush();
        $this->activity->log('settings.updated', null, ['keys' => array_keys($values)]);

        return back()->with('success', 'تم حفظ الإعدادات بنجاح.');
    }
}

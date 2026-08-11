<?php

namespace App\Services;

use App\Models\SiteSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class SettingService
{
    /** In-request memo so repeated setting() calls never re-hit the cache store. */
    protected ?array $loaded = null;

    /**
     * All settings as a key => value map, cached forever and busted on write.
     */
    public function all(): array
    {
        if ($this->loaded !== null) {
            return $this->loaded;
        }

        $this->loaded = Cache::rememberForever(SiteSetting::CACHE_KEY, function () {
            // Guards early boot (e.g. migrate on a fresh database) before the table exists.
            if (! Schema::hasTable('site_settings')) {
                return [];
            }

            return SiteSetting::query()->pluck('value', 'key')->all();
        });

        return $this->loaded;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $value = $this->all()[$key] ?? null;

        return ($value === null || $value === '') ? $default : $value;
    }

    /**
     * Create or update a single setting and refresh the cache.
     */
    public function set(string $key, mixed $value, string $type = 'text', string $group = 'general'): void
    {
        SiteSetting::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'type' => $type, 'group' => $group],
        );

        $this->flush();
    }

    /**
     * Persist many settings at once, then flush once.
     *
     * @param  array<string, mixed>  $values
     */
    public function setMany(array $values, string $group = 'general'): void
    {
        foreach ($values as $key => $value) {
            SiteSetting::updateOrCreate(
                ['key' => $key],
                ['value' => $value, 'group' => $group],
            );
        }

        $this->flush();
    }

    /** @return array<string, SiteSetting> */
    public function group(string $group): array
    {
        return SiteSetting::query()
            ->where('group', $group)
            ->orderBy('sort_order')
            ->get()
            ->keyBy('key')
            ->all();
    }

    public function flush(): void
    {
        $this->loaded = null;
        Cache::forget(SiteSetting::CACHE_KEY);
    }
}

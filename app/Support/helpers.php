<?php

use App\Services\SettingService;

if (! function_exists('setting')) {
    /**
     * Read a site setting. Values are cached, so this is safe to call repeatedly
     * from Blade without incurring a query each time.
     */
    function setting(?string $key = null, mixed $default = null): mixed
    {
        $service = app(SettingService::class);

        if ($key === null) {
            return $service;
        }

        return $service->get($key, $default);
    }
}

if (! function_exists('whatsapp_url')) {
    /**
     * Build a wa.me link from the configured WhatsApp number.
     * Non-digit characters are stripped, and a Saudi country code is assumed
     * when the number is stored in local 05xxxxxxxx form.
     */
    function whatsapp_url(?string $message = null, ?string $number = null): string
    {
        $number = $number ?: (string) setting('whatsapp', '');
        $digits = preg_replace('/\D+/', '', $number) ?? '';

        if ($digits === '') {
            return '#';
        }

        if (str_starts_with($digits, '00')) {
            $digits = substr($digits, 2);
        } elseif (str_starts_with($digits, '0')) {
            $digits = '966' . substr($digits, 1);
        }

        $url = 'https://wa.me/' . $digits;

        if (filled($message)) {
            $url .= '?text=' . rawurlencode($message);
        }

        return $url;
    }
}

if (! function_exists('tel_url')) {
    /** Build a tel: link from an arbitrary stored phone string. */
    function tel_url(?string $number = null): string
    {
        $number = $number ?: (string) setting('phone', '');
        $clean = preg_replace('/[^\d+]/', '', $number) ?? '';

        return $clean === '' ? '#' : 'tel:' . $clean;
    }
}

if (! function_exists('setting_image')) {
    /**
     * Resolve a setting that stores a file path on the public disk to a URL.
     *
     * Storage URLs are root-relative so they work on any host/port. Pass
     * $absolute for contexts that require a full URL (OpenGraph, sitemaps).
     */
    function setting_image(string $key, ?string $default = null, bool $absolute = false): ?string
    {
        $path = setting($key);

        if (blank($path)) {
            return $default;
        }

        if (str_starts_with((string) $path, 'http://') || str_starts_with((string) $path, 'https://')) {
            return (string) $path;
        }

        $url = Illuminate\Support\Facades\Storage::url((string) $path);

        return $absolute ? url($url) : $url;
    }
}

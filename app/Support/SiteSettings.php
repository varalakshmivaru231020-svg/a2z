<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

/**
 * Settings edited in the admin panel (Site settings). They are layered over the defaults in
 * config/site.php on every request (see ApplySiteSettings), so every existing
 * config('site.phone') / config('site.email') / … in the views is dynamic without further changes.
 */
class SiteSettings
{
    private const CACHE_KEY = 'site_settings';

    /** config/site.php values as shipped, so a cleared setting really falls back to them. */
    private static ?array $defaults = null;

    /** @return array<string, ?string> */
    public static function all(): array
    {
        try {
            return Cache::rememberForever(self::CACHE_KEY, fn () => Setting::query()->pluck('value', 'name')->all());
        } catch (\Throwable) {
            return []; // table not migrated yet (fresh install, tests) - fall back to config defaults
        }
    }

    public static function get(string $name, ?string $default = null): ?string
    {
        $value = self::all()[$name] ?? null;

        return filled($value) ? $value : $default;
    }

    /** @param array<string, ?string> $values */
    public static function save(array $values): void
    {
        foreach ($values as $name => $value) {
            Setting::updateOrCreate(['name' => $name], ['value' => $value]);
        }

        Cache::forget(self::CACHE_KEY);
    }

    /** Overlay the saved settings on config('site.*'). */
    public static function apply(): void
    {
        self::$defaults ??= collect(['phone', 'phone_link', 'whatsapp', 'email', 'email_secondary', 'tagline', 'slogan', 'footer_text', 'offices', 'announcement'])
            ->mapWithKeys(fn ($key) => ["site.$key" => config("site.$key")])
            ->all();

        $config = self::$defaults;
        $saved = self::all();

        if (filled($saved['phone'] ?? null)) {
            $digits = preg_replace('/\D+/', '', $saved['phone']);
            if (strlen($digits) === 10) {
                $digits = '91' . $digits; // a bare Indian mobile number
            }

            $config['site.phone'] = $saved['phone'];
            $config['site.phone_link'] = '+' . $digits;
            $config['site.whatsapp'] = $digits;
        }

        foreach (['email', 'tagline', 'footer_text'] as $name) {
            if (filled($saved[$name] ?? null)) {
                $config["site.$name"] = $saved[$name];
            }
        }

        // The slogan and the second email may be cleared on purpose, so an empty saved value means "show none".
        if (array_key_exists('slogan', $saved)) {
            $config['site.slogan'] = (string) $saved['slogan'];
        }

        if (array_key_exists('email_secondary', $saved)) {
            $config['site.email_secondary'] = (string) $saved['email_secondary'];
        }

        // Announcement bar: blank text falls back to the default, the two switches are saved as '1' / '0'.
        $announcement = $config['site.announcement'];
        if (filled($saved['announcement_text'] ?? null)) {
            $announcement['text'] = $saved['announcement_text'];
        }
        foreach (['enabled' => 'announcement_enabled', 'home_only' => 'announcement_home_only'] as $key => $name) {
            if (array_key_exists($name, $saved)) {
                $announcement[$key] = $saved[$name] === '1';
            }
        }
        $config['site.announcement'] = $announcement;

        if (filled($saved['offices'] ?? null) && is_array($offices = json_decode($saved['offices'], true)) && $offices) {
            $config['site.offices'] = array_values($offices);
        }

        config($config);
    }

    /**
     * Header or footer logo: the uploaded one if there is one, otherwise the built-in file.
     *
     * @return array{url: string, width: int, height: int, custom: bool, badge: bool}
     */
    public static function logo(string $which): array
    {
        $header = $which === 'header';
        [$default, $width, $height] = $header ? ['img/logo.png', 72, 76] : ['img/logo-footer.png', 170, 179];

        $path = self::get("{$which}_logo");
        $disk = Storage::disk('uploads');

        if (! $path || ! $disk->exists($path)) {
            return ['url' => Assets::url($default), 'width' => $width, 'height' => $height, 'custom' => false, 'badge' => false];
        }

        [$w, $h] = @getimagesize($disk->path($path)) ?: [0, 0];

        if ($w > 0 && $h > 0) {
            if ($header) {
                $height = 76;
                $width = min(260, (int) round($w * $height / $h));
            } else {
                $width = 170;
                $height = (int) round($h * $width / $w);
            }
        }

        return [
            'url' => asset('uploads/' . $path) . '?v=' . $disk->lastModified($path),
            'width' => $width,
            'height' => $height,
            'custom' => true,
            // A logo with dark lettering needs a light backing on the dark footer (an admin choice).
            'badge' => ! $header && self::get('footer_logo_badge') === '1',
        ];
    }
}

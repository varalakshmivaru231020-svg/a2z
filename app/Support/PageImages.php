<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

/**
 * The photos on the Home and About pages that the admin can replace (Admin → Page images).
 * Each slot falls back to the built-in file in public/img until something is uploaded.
 */
class PageImages
{
    /**
     * @var array<string, array{page: string, label: string, default: string, width: int, height: int, max: int, hint: string}>
     */
    public const SLOTS = [
        'home_hero' => [
            'page' => 'Home page',
            'label' => 'Top image (beside the headline)',
            'default' => 'img/office.jpg',
            'width' => 991, 'height' => 551, 'max' => 1600,
            'hint' => 'Landscape photo, about 1000 × 550 or larger.',
        ],
        'home_why' => [
            'page' => 'Home page',
            'label' => '“Why AKS Global Maintenance” image',
            'default' => 'img/crew.jpg',
            'width' => 599, 'height' => 681, 'max' => 1200,
            'hint' => 'Portrait or square photo works best, about 600 × 680 or larger.',
        ],
        'about_profile' => [
            'page' => 'About us page',
            'label' => 'Company profile image (next to “Our journey”)',
            'default' => 'img/office.jpg',
            'width' => 991, 'height' => 551, 'max' => 1600,
            'hint' => 'Landscape photo, about 1000 × 550 or larger.',
        ],
        'about_vision' => [
            'page' => 'About us page',
            'label' => 'Vision & mission background',
            'default' => 'img/office.jpg',
            'width' => 991, 'height' => 551, 'max' => 1920,
            'hint' => 'Wide photo, about 1600 × 900 or larger. It sits under a dark blue overlay so the text stays readable.',
        ],
        'about_crew' => [
            'page' => 'About us page',
            'label' => 'Crew image (next to “Our crew”)',
            'default' => 'img/crew.jpg',
            'width' => 599, 'height' => 681, 'max' => 1200,
            'hint' => 'Portrait or square photo works best, about 600 × 680 or larger.',
        ],
    ];

    public static function setting(string $slot): string
    {
        return "image_{$slot}";
    }

    /** @return ?string path on the uploads disk of the uploaded image, if there is a usable one */
    public static function path(string $slot): ?string
    {
        $path = SiteSettings::get(self::setting($slot));

        return $path && Storage::disk('uploads')->exists($path) ? $path : null;
    }

    /**
     * The uploaded image for a slot, otherwise the built-in one.
     *
     * @return array{url: string, width: int, height: int, custom: bool}
     */
    public static function get(string $slot): array
    {
        $def = self::SLOTS[$slot];
        $path = self::path($slot);

        if (! $path) {
            return ['url' => Assets::url($def['default']), 'width' => $def['width'], 'height' => $def['height'], 'custom' => false];
        }

        $disk = Storage::disk('uploads');
        [$w, $h] = @getimagesize($disk->path($path)) ?: [$def['width'], $def['height']];

        return [
            'url' => asset('uploads/' . $path) . '?v=' . $disk->lastModified($path),
            'width' => $w,
            'height' => $h,
            'custom' => true,
        ];
    }
}

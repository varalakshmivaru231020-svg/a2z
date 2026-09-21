<?php

namespace App\Support;

use App\Models\SeoPage;
use Illuminate\Support\Str;

/**
 * Per-page SEO data: <title>, meta description, canonical, robots, Open Graph
 * / Twitter tags and JSON-LD structured data. Controllers build one of these
 * and pass it to the view as $seo; partials/seo.blade.php renders it.
 */
class Seo
{
    public string $title;
    public string $description = '';
    public string $canonical;
    public string $image;
    public ?string $imageAlt = null;
    public string $type = 'website';
    public bool $index = true;

    /** Banner photo for the page hero (uploaded in the admin), if any. */
    public ?string $banner = null;

    /** @var array<int, array{0: string, 1: ?string}> */
    private array $crumbs = [];

    /** @var array<int, array<string, mixed>> */
    private array $schemas = [];

    public function __construct()
    {
        $this->image = Assets::url(config('seo.default_image'));
    }

    /**
     * SEO for one of the static pages in config('seo.pages'). Values saved in
     * the admin (seo_pages table) win over the config defaults.
     */
    public static function forPage(string $key): static
    {
        $defaults = config("seo.pages.$key", []);
        $saved = SeoPage::query()->where('page_key', $key)->first();

        $seo = new static();
        $seo->title = filled($saved?->meta_title) ? $saved->meta_title : ($defaults['title'] ?? config('site.name'));
        $seo->description = filled($saved?->meta_description) ? $saved->meta_description : ($defaults['description'] ?? '');
        $seo->canonical = url($defaults['path'] ?? '/');
        $seo->banner = $saved?->bannerUrl();

        if ($key !== 'home') {
            $seo->crumb('Home', url('/'));
            $seo->crumb($defaults['label'] ?? $seo->title, $seo->canonical);
        }

        return $seo;
    }

    /** SEO for a record-level page (service, job, …). */
    public static function make(string $title, string $description, string $canonical): static
    {
        $seo = new static();
        $seo->title = $title;
        $seo->description = $description;
        $seo->canonical = $canonical;

        return $seo;
    }

    /** Borrow another page's banner (service pages use the Services banner, job pages the Recruitment one). */
    public function bannerFrom(string $pageKey): static
    {
        $this->banner ??= SeoPage::query()->where('page_key', $pageKey)->first()?->bannerUrl();

        return $this;
    }

    public function noindex(): static
    {
        $this->index = false;

        return $this;
    }

    public function image(?string $url, ?string $alt = null): static
    {
        if ($url) {
            $this->image = $url;
            $this->imageAlt = $alt;
        }

        return $this;
    }

    public function type(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function crumb(string $name, ?string $url = null): static
    {
        $this->crumbs[] = [$name, $url];

        return $this;
    }

    /** Add a structured-data node (rendered as its own JSON-LD block). */
    public function schema(array $node): static
    {
        $this->schemas[] = $node + ['@context' => 'https://schema.org'];

        return $this;
    }

    public function fullTitle(): string
    {
        $brand = config('seo.brand');

        return Str::contains($this->title, $brand)
            ? $this->title
            : $this->title . config('seo.title_suffix');
    }

    public function metaDescription(): string
    {
        $text = trim(preg_replace('/\s+/', ' ', strip_tags($this->description)));

        return Str::limit($text, 160, '…');
    }

    public function robots(): string
    {
        return $this->index
            ? 'index, follow, max-image-preview:large, max-snippet:-1'
            : 'noindex, nofollow';
    }

    public function breadcrumbs(): array
    {
        return $this->crumbs;
    }

    /** Every JSON-LD block for this page, breadcrumbs included. */
    public function jsonLd(): array
    {
        $blocks = $this->schemas;

        if (count($this->crumbs) > 1) {
            $items = [];
            foreach ($this->crumbs as $i => [$name, $url]) {
                $item = ['@type' => 'ListItem', 'position' => $i + 1, 'name' => $name];
                if ($url) {
                    $item['item'] = $url;
                }
                $items[] = $item;
            }

            $blocks[] = [
                '@context' => 'https://schema.org',
                '@type' => 'BreadcrumbList',
                'itemListElement' => $items,
            ];
        }

        return $blocks;
    }

    /* ------------------------------------------------------------------ *
     * Reusable schema.org nodes
     * ------------------------------------------------------------------ */

    public static function organization(): array
    {
        $offices = collect(config('site.offices'));

        return [
            '@type' => ['Organization', 'ProfessionalService'],
            '@id' => url('/') . '#organization',
            'name' => config('site.name'),
            'alternateName' => config('site.brand'),
            'slogan' => config('site.tagline'),
            'url' => url('/'),
            'logo' => SiteSettings::logo('header')['url'],
            'image' => asset('img/office.jpg'),
            'telephone' => config('site.phone_link'),
            'email' => config('site.email'),
            'foundingDate' => (string) config('site.founded'),
            'areaServed' => [
                ['@type' => 'City', 'name' => 'Bangalore'],
                ['@type' => 'City', 'name' => 'Kochi'],
            ],
            'address' => self::postalAddress($offices->first()),
            'location' => $offices->map(fn ($o) => [
                '@type' => 'Place',
                'name' => $o['label'],
                'address' => self::postalAddress($o),
            ])->all(),
            'contactPoint' => [
                '@type' => 'ContactPoint',
                'telephone' => config('site.phone_link'),
                'email' => config('site.email'),
                'contactType' => 'customer service',
                'areaServed' => 'IN',
                'availableLanguage' => ['en', 'hi', 'kn', 'ml'],
            ],
        ];
    }

    public static function postalAddress(array $office): array
    {
        return [
            '@type' => 'PostalAddress',
            'streetAddress' => $office['street'],
            'addressLocality' => $office['locality'],
            'addressRegion' => $office['region'],
            'postalCode' => $office['postal'],
            'addressCountry' => 'IN',
        ];
    }

    /** Compact reference to the organization, for nodes that point back to it. */
    public static function organizationRef(): array
    {
        return [
            '@type' => 'Organization',
            '@id' => url('/') . '#organization',
            'name' => config('site.name'),
            'url' => url('/'),
            'logo' => SiteSettings::logo('header')['url'],
        ];
    }
}

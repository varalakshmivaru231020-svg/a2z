<?php

namespace App\Http\Controllers;

use App\Models\GalleryItem;
use App\Models\Service;
use App\Support\Seo;
use Illuminate\View\View;

class PageController extends Controller
{
    public function home(): View
    {
        $services = Service::active()->ordered()->get();

        // Featured services first; if nothing is flagged, fall back to the first few.
        $featured = $services->where('is_featured', true)->take(8);
        if ($featured->isEmpty()) {
            $featured = $services->take(8);
        }

        $seo = Seo::forPage('home')
            ->schema(Seo::organization())
            ->schema([
                '@type' => 'WebSite',
                '@id' => url('/') . '#website',
                'url' => url('/'),
                'name' => config('site.name'),
                'inLanguage' => 'en-IN',
                'publisher' => ['@id' => url('/') . '#organization'],
            ]);

        return view('pages.home', [
            'seo' => $seo,
            'services' => $services,
            'featured' => $featured,
            'categories' => config('site.service_categories'),
            'gallery' => GalleryItem::active()->ordered()->limit(6)->get(),
        ]);
    }

    public function about(): View
    {
        $seo = Seo::forPage('about')->schema([
            '@type' => 'AboutPage',
            'name' => 'About ' . config('site.name'),
            'url' => url('/about'),
            'about' => ['@id' => url('/') . '#organization'],
            'isPartOf' => ['@id' => url('/') . '#website'],
        ]);

        return view('pages.about', ['seo' => $seo]);
    }
}

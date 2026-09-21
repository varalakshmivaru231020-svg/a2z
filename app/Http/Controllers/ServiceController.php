<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Support\Seo;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ServiceController extends Controller
{
    public function index(Request $request): View
    {
        $services = Service::active()->ordered()->get();

        // /services?category=facility shows just that category. Anything unknown (or empty) falls back to "All".
        $category = (string) $request->query('category');
        $active = array_key_exists($category, config('site.service_categories')) && $services->contains('category', $category)
            ? $category
            : null;

        $seo = Seo::forPage('services')->schema([
            '@type' => 'CollectionPage',
            'name' => 'Our Services',
            'url' => route('services.index'),
            'isPartOf' => ['@id' => url('/') . '#website'],
            'mainEntity' => [
                '@type' => 'ItemList',
                'itemListElement' => $services->values()->map(fn (Service $s, int $i) => [
                    '@type' => 'ListItem',
                    'position' => $i + 1,
                    'name' => $s->title,
                    'url' => $s->url(),
                ])->all(),
            ],
        ]);

        return view('services.index', [
            'seo' => $seo,
            // toBase(): an Eloquent collection's except() filters by model key, which we don't want here.
            'grouped' => $services->groupBy('category')->toBase(),
            'categories' => config('site.service_categories'),
            'active' => $active,
            'total' => $services->count(),
        ]);
    }

    public function show(string $slug): View
    {
        $service = Service::active()->where('slug', $slug)->firstOrFail();

        $related = Service::active()
            ->where('category', $service->category)
            ->whereKeyNot($service->id)
            ->ordered()
            ->limit(3)
            ->get();

        $seo = Seo::make(
            $service->meta_title ?: $service->title . ' in Bangalore',
            $service->meta_description ?: $service->summary,
            $service->url(),
        )
            ->image($service->imageUrl(), $service->title)
            ->bannerFrom('services')
            ->crumb('Home', url('/'))
            ->crumb('Services', route('services.index'))
            ->crumb($service->title, $service->url())
            ->schema(array_filter([
                '@type' => 'Service',
                'name' => $service->title,
                'description' => $service->summary,
                'serviceType' => $service->categoryName(),
                'url' => $service->url(),
                'image' => $service->imageUrl(),
                'provider' => Seo::organizationRef(),
                'areaServed' => [
                    ['@type' => 'City', 'name' => 'Bangalore'],
                    ['@type' => 'City', 'name' => 'Kochi'],
                ],
            ]));

        return view('services.show', [
            'seo' => $seo,
            'service' => $service,
            'related' => $related,
        ]);
    }
}

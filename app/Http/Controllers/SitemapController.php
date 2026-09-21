<?php

namespace App\Http\Controllers;

use App\Models\GalleryItem;
use App\Models\JobOpening;
use App\Models\Service;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function sitemap(): Response
    {
        $services = Service::active()->ordered()->get(['slug', 'updated_at']);
        $jobs = JobOpening::open()->get(['slug', 'updated_at']);

        $latest = fn ($models) => $models->max('updated_at');
        $galleryUpdated = GalleryItem::active()->max('updated_at');

        // lastmod is only given where we actually know it; a made-up date is worse than none.
        $urls = [
            ['loc' => url('/'), 'lastmod' => collect([$latest($services), $latest($jobs)])->filter()->max()],
            ['loc' => url('/about')],
            ['loc' => route('services.index'), 'lastmod' => $latest($services)],
            ['loc' => route('recruitment.index'), 'lastmod' => $latest($jobs)],
            ['loc' => route('gallery'), 'lastmod' => $galleryUpdated ? \Illuminate\Support\Carbon::parse($galleryUpdated) : null],
            ['loc' => route('contact')],
        ];

        foreach ($services as $service) {
            $urls[] = ['loc' => route('services.show', $service->slug), 'lastmod' => $service->updated_at];
        }

        foreach ($jobs as $job) {
            $urls[] = ['loc' => route('recruitment.show', $job->slug), 'lastmod' => $job->updated_at];
        }

        return response()
            ->view('sitemap', ['urls' => $urls])
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    public function robots(): Response
    {
        $body = implode("\n", [
            'User-agent: *',
            'Disallow: /admin',
            'Allow: /',
            '',
            'Sitemap: ' . route('sitemap'),
            '',
        ]);

        return response($body, 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }
}

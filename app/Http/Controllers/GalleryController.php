<?php

namespace App\Http\Controllers;

use App\Models\GalleryItem;
use App\Support\Seo;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GalleryController extends Controller
{
    private const PER_PAGE = 24;

    public function index(Request $request): View
    {
        $items = GalleryItem::active()->ordered()->paginate(self::PER_PAGE)->withQueryString();

        $seo = Seo::forPage('gallery');

        // Each paginated page is its own canonical URL with its own title.
        if ($items->currentPage() > 1) {
            $seo->canonical = route('gallery', ['page' => $items->currentPage()]);
            $seo->title .= ' – Page ' . $items->currentPage();
        }

        $seo->schema([
            '@type' => 'ImageGallery',
            'name' => 'Gallery – ' . config('site.name'),
            'url' => $seo->canonical,
            'isPartOf' => ['@id' => url('/') . '#website'],
        ]);

        return view('pages.gallery', ['seo' => $seo, 'items' => $items]);
    }
}

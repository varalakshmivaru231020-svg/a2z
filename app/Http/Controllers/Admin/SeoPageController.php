<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SeoPage;
use App\Support\ImageUploader;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

/** Banner photo + search-engine title/description for each static page. */
class SeoPageController extends Controller
{
    public function __construct(private ImageUploader $images)
    {
    }

    public function edit(): View
    {
        return view('admin.seo', [
            'pages' => config('seo.pages'),
            'saved' => SeoPage::all()->keyBy('page_key'),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'pages' => ['required', 'array'],
            'pages.*.meta_title' => ['nullable', 'string', 'max:70'],
            'pages.*.meta_description' => ['nullable', 'string', 'max:200'],
            'banners.*' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
        ], [
            'banners.*.image' => 'The banner must be an image.',
            'banners.*.mimes' => 'Banners must be JPG, PNG or WebP.',
            'banners.*.max' => 'Each banner must be 8 MB or smaller.',
        ]);

        $bannerErrors = [];

        foreach (array_keys(config('seo.pages')) as $key) {
            $input = $request->input("pages.$key", []);
            $page = SeoPage::firstOrNew(['page_key' => $key]);

            // Blank = fall back to the default in config/seo.php.
            $page->meta_title = filled($input['meta_title'] ?? null) ? trim($input['meta_title']) : null;
            $page->meta_description = filled($input['meta_description'] ?? null) ? trim($input['meta_description']) : null;

            // The home page has its own hero; the other five pages get an uploadable banner.
            if ($key !== 'home') {
                if ($request->boolean("remove_banner.$key") && $page->banner) {
                    $this->images->delete($page->banner);
                    $page->banner = null;
                }

                if ($file = $request->file("banners.$key")) {
                    try {
                        $new = $this->images->store($file, 'banners', 1920)['path'];
                        $this->images->delete($page->banner);
                        $page->banner = $new;
                    } catch (RuntimeException $e) {
                        $bannerErrors["banners.$key"] = $e->getMessage();
                    }
                }
            }

            $page->save();
        }

        if ($bannerErrors) {
            return back()->withErrors($bannerErrors);
        }

        return back()->with('status', 'Banners and SEO settings saved.');
    }
}

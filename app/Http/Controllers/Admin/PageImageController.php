<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\ImageUploader;
use App\Support\PageImages;
use App\Support\SiteSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

/** Replace the photos on the Home and About pages. */
class PageImageController extends Controller
{
    public function __construct(private ImageUploader $images)
    {
    }

    public function edit(): View
    {
        return view('admin.images', [
            'groups' => collect(PageImages::SLOTS)
                ->map(fn ($def, $slot) => $def + ['slot' => $slot, 'image' => PageImages::get($slot)])
                ->groupBy('page'),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'images.*' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
        ], [
            'images.*.image' => 'This must be an image.',
            'images.*.mimes' => 'Images must be JPG, PNG or WebP.',
            'images.*.max' => 'Each image must be 8 MB or smaller.',
        ]);

        $values = [];
        $added = [];
        $replaced = [];

        foreach (PageImages::SLOTS as $slot => $def) {
            $name = PageImages::setting($slot);
            $old = SiteSettings::get($name);

            if ($request->boolean("remove_image.$slot") && $old) {
                $replaced[] = $old;
                $values[$name] = '';
            }

            if ($file = $request->file("images.$slot")) {
                try {
                    $values[$name] = $added[] = $this->images->store($file, 'pages', $def['max'])['path'];
                    $replaced[] = $old;
                } catch (RuntimeException $e) {
                    $this->images->delete(...$added); // nothing is saved unless every upload worked

                    return back()->withErrors(["images.$slot" => $e->getMessage()]);
                }
            }
        }

        if ($values) {
            SiteSettings::save($values);
            $this->images->delete(...$replaced);
        }

        return back()->with('status', $values ? 'Page images saved.' : 'No changes — choose an image to upload first.');
    }
}

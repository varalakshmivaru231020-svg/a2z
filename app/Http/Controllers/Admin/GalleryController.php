<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GalleryItem;
use App\Support\ImageUploader;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

class GalleryController extends Controller
{
    public function __construct(private ImageUploader $images)
    {
    }

    public function index(): View
    {
        return view('admin.gallery.index', [
            'items' => GalleryItem::ordered()->paginate(30),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'images' => ['required', 'array', 'min:1', 'max:20'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
        ], [
            'images.required' => 'Choose at least one photo to upload.',
            'images.*.image' => 'Each file must be an image.',
            'images.*.mimes' => 'Photos must be JPG, PNG or WebP.',
            'images.*.max' => 'Each photo must be 8 MB or smaller.',
        ]);

        $added = 0;
        $failed = [];

        foreach ($request->file('images') as $file) {
            try {
                $stored = $this->images->store($file, 'gallery', 1600, 640);
                GalleryItem::create(['path' => $stored['path'], 'thumb_path' => $stored['thumb']]);
                $added++;
            } catch (RuntimeException $e) {
                $failed[] = $file->getClientOriginalName();
            }
        }

        $message = $added . ' photo' . ($added === 1 ? '' : 's') . ' uploaded.';

        if ($failed) {
            return back()->with('status', $message)->withErrors(['images' => 'Could not process: ' . implode(', ', $failed)]);
        }

        return back()->with('status', $message);
    }

    public function update(Request $request, GalleryItem $item): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['nullable', 'string', 'max:150'],
            'caption' => ['nullable', 'string', 'max:300'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
        ]);

        $item->update([
            'title' => $data['title'] ?? null,
            'caption' => $data['caption'] ?? null,
            'sort_order' => (int) ($data['sort_order'] ?? 0),
            'is_active' => $request->boolean('is_active'),
        ]);

        return back()->with('status', 'Photo updated.');
    }

    public function destroy(GalleryItem $item): RedirectResponse
    {
        $item->delete(); // also removes the files

        return back()->with('status', 'Photo deleted.');
    }
}

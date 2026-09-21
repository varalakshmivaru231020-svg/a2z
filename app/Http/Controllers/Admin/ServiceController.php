<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Support\Icons;
use App\Support\ImageUploader;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use RuntimeException;

class ServiceController extends Controller
{
    public function __construct(private ImageUploader $images)
    {
    }

    public function index(Request $request): View
    {
        $services = Service::query()
            ->when($request->query('category'), fn ($q, $c) => $q->where('category', $c))
            ->orderBy('category')->orderBy('sort_order')->orderBy('title')
            ->get();

        return view('admin.services.index', [
            'services' => $services,
            'category' => $request->query('category'),
        ]);
    }

    public function create(): View
    {
        return view('admin.services.form', ['service' => new Service([
            'icon' => 'briefcase', 'is_active' => true, 'sort_order' => 0,
        ])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate($this->rules());

        $service = new Service();
        if ($error = $this->fill($service, $request, $data)) {
            return back()->withInput()->withErrors(['image' => $error]);
        }
        $service->save();

        return redirect()->route('admin.services.index')->with('status', "Service “{$service->title}” added.");
    }

    public function edit(Service $service): View
    {
        return view('admin.services.form', ['service' => $service]);
    }

    public function update(Request $request, Service $service): RedirectResponse
    {
        $data = $request->validate($this->rules($service));

        if ($error = $this->fill($service, $request, $data)) {
            return back()->withInput()->withErrors(['image' => $error]);
        }
        $service->save();

        return redirect()->route('admin.services.index')->with('status', "Service “{$service->title}” updated.");
    }

    public function destroy(Service $service): RedirectResponse
    {
        $service->delete();

        return redirect()->route('admin.services.index')->with('status', "Service “{$service->title}” deleted.");
    }

    private function rules(?Service $service = null): array
    {
        return [
            'category' => ['required', Rule::in(array_keys(config('site.service_categories')))],
            'title' => ['required', 'string', 'max:150'],
            'slug' => ['nullable', 'string', 'max:100', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique('services', 'slug')->ignore($service)],
            'summary' => ['required', 'string', 'max:300'],
            'description' => ['nullable', 'string', 'max:10000'],
            'features' => ['nullable', 'string', 'max:3000'],
            'icon' => ['required', Rule::in(array_keys(Icons::SERVICE_CHOICES))],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'meta_title' => ['nullable', 'string', 'max:70'],
            'meta_description' => ['nullable', 'string', 'max:200'],
        ];
    }

    /** Copies validated input onto the model. Returns an error message if the image is unusable. */
    private function fill(Service $service, Request $request, array $data): ?string
    {
        $service->fill([
            'category' => $data['category'],
            'title' => $data['title'],
            'summary' => $data['summary'],
            'description' => $data['description'] ?? null,
            'icon' => $data['icon'],
            'sort_order' => (int) ($data['sort_order'] ?? 0),
            'is_active' => $request->boolean('is_active'),
            'is_featured' => $request->boolean('is_featured'),
            'meta_title' => $data['meta_title'] ?? null,
            'meta_description' => $data['meta_description'] ?? null,
            'features' => $this->lines($data['features'] ?? ''),
            'slug' => filled($data['slug'] ?? null)
                ? $data['slug']
                : ($service->slug ?: Service::uniqueSlug($data['title'])),
        ]);

        $oldImage = $service->image;

        if ($request->boolean('remove_image')) {
            $service->image = null;
        }

        if ($file = $request->file('image')) {
            try {
                $service->image = $this->images->store($file, 'services', 1400)['path'];
            } catch (RuntimeException $e) {
                return $e->getMessage();
            }
        }

        if ($oldImage && $oldImage !== $service->image) {
            $this->images->delete($oldImage);
        }

        return null;
    }

    /** One item per line, blanks dropped. */
    private function lines(string $text): array
    {
        return array_slice(array_values(array_filter(array_map('trim', preg_split('/\R/', $text)))), 0, 20);
    }
}

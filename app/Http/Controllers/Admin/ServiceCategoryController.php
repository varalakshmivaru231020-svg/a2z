<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ServiceCategory;
use App\Support\Icons;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ServiceCategoryController extends Controller
{
    public function index(): View
    {
        return view('admin.services.categories.index', [
            'categories' => ServiceCategory::query()->orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.services.categories.form', [
            'category' => new ServiceCategory(['icon' => 'briefcase', 'sort_order' => 0]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate($this->rules());

        $category = ServiceCategory::create([
            'key' => ServiceCategory::uniqueKey($data['name']),
            'name' => $data['name'],
            'blurb' => $data['blurb'] ?? null,
            'icon' => $data['icon'],
            'sort_order' => (int) ($data['sort_order'] ?? 0),
        ]);

        return redirect()->route('admin.services.categories.index')->with('status', "Category “{$category->name}” added.");
    }

    public function edit(ServiceCategory $category): View
    {
        return view('admin.services.categories.form', ['category' => $category]);
    }

    public function update(Request $request, ServiceCategory $category): RedirectResponse
    {
        $data = $request->validate($this->rules());

        $category->update([
            'name' => $data['name'],
            'blurb' => $data['blurb'] ?? null,
            'icon' => $data['icon'],
            'sort_order' => (int) ($data['sort_order'] ?? 0),
        ]);

        return redirect()->route('admin.services.categories.index')->with('status', "Category “{$category->name}” updated.");
    }

    public function destroy(ServiceCategory $category): RedirectResponse
    {
        if ($count = $category->serviceCount()) {
            return back()->withErrors(['category' => "“{$category->name}” still has {$count} service(s). Move or delete them first."]);
        }

        $category->delete();

        return redirect()->route('admin.services.categories.index')->with('status', "Category “{$category->name}” deleted.");
    }

    private function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:80'],
            'blurb' => ['nullable', 'string', 'max:300'],
            'icon' => ['required', Rule::in(array_keys(Icons::SERVICE_CHOICES))],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
        ];
    }
}

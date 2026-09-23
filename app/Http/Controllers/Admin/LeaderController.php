<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Leader;
use App\Support\ImageUploader;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

class LeaderController extends Controller
{
    public function __construct(private ImageUploader $images)
    {
    }

    public function index(): View
    {
        return view('admin.leadership.index', [
            'leaders' => Leader::query()->ordered()->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.leadership.form', ['leader' => new Leader([
            'is_active' => true, 'sort_order' => 0,
        ])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate($this->rules());

        $leader = new Leader();
        if ($error = $this->fill($leader, $request, $data)) {
            return back()->withInput()->withErrors(['photo' => $error]);
        }
        $leader->save();

        return redirect()->route('admin.leadership.index')->with('status', "“{$leader->name}” added.");
    }

    public function edit(Leader $leader): View
    {
        return view('admin.leadership.form', ['leader' => $leader]);
    }

    public function update(Request $request, Leader $leader): RedirectResponse
    {
        $data = $request->validate($this->rules());

        if ($error = $this->fill($leader, $request, $data)) {
            return back()->withInput()->withErrors(['photo' => $error]);
        }
        $leader->save();

        return redirect()->route('admin.leadership.index')->with('status', "“{$leader->name}” updated.");
    }

    public function destroy(Leader $leader): RedirectResponse
    {
        $leader->delete();

        return redirect()->route('admin.leadership.index')->with('status', "“{$leader->name}” deleted.");
    }

    private function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'role' => ['required', 'string', 'max:150'],
            'bio' => ['required', 'string', 'max:2000'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
        ];
    }

    /** Copies validated input onto the model. Returns an error message if the photo is unusable. */
    private function fill(Leader $leader, Request $request, array $data): ?string
    {
        $leader->fill([
            'name' => $data['name'],
            'role' => $data['role'],
            'bio' => $data['bio'],
            'sort_order' => (int) ($data['sort_order'] ?? 0),
            'is_active' => $request->boolean('is_active'),
        ]);

        $oldPhoto = $leader->photo;

        if ($request->boolean('remove_photo')) {
            $leader->photo = null;
        }

        if ($file = $request->file('photo')) {
            try {
                $leader->photo = $this->images->store($file, 'leadership', 1200)['path'];
            } catch (RuntimeException $e) {
                return $e->getMessage();
            }
        }

        if ($oldPhoto && $oldPhoto !== $leader->photo) {
            $this->images->delete($oldPhoto);
        }

        return null;
    }
}

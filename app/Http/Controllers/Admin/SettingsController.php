<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\ImageUploader;
use App\Support\SiteSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use RuntimeException;

/** Site settings: contact details, tagline, announcement bar, footer text, office addresses and the header / footer logos. */
class SettingsController extends Controller
{
    private const OFFICE_SLOTS = 4;

    public function __construct(private ImageUploader $images)
    {
    }

    public function edit(): View
    {
        return view('admin.settings', [
            // config('site.offices') already carries any saved changes (ApplySiteSettings middleware).
            'offices' => collect(config('site.offices'))->pad(self::OFFICE_SLOTS, [])->values()->all(),
            'headerLogo' => SiteSettings::logo('header'),
            'footerLogo' => SiteSettings::logo('footer'),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        // A slot that is partly filled must have its label, street and locality.
        $slot = fn (string $except) => 'required_with:' . collect(['label', 'street', 'locality', 'region', 'postal', 'map'])
            ->reject(fn ($f) => $f === $except)->map(fn ($f) => "offices.*.$f")->implode(',');

        $data = $request->validate([
            'phone' => ['required', 'string', 'regex:/^[0-9+\-\s()]{7,20}$/'],
            'email' => ['required', 'email:rfc', 'max:150'],
            'email_secondary' => ['nullable', 'email:rfc', 'max:150', 'different:email'],
            'tagline' => ['required', 'string', 'max:120'],
            'slogan' => ['nullable', 'string', 'max:160'],
            'footer_text' => ['nullable', 'string', 'max:400'],
            'announcement_text' => ['nullable', 'string', 'max:160', 'required_if_accepted:announcement_enabled'],
            'header_logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:4096'],
            'footer_logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:4096'],
            'offices' => ['required', 'array'],
            'offices.0.label' => ['required', 'string', 'max:80'],
            'offices.0.street' => ['required', 'string', 'max:200'],
            'offices.0.locality' => ['required', 'string', 'max:80'],
            'offices.*.label' => ['nullable', 'string', 'max:80', $slot('label')],
            'offices.*.street' => ['nullable', 'string', 'max:200', $slot('street')],
            'offices.*.locality' => ['nullable', 'string', 'max:80', $slot('locality')],
            'offices.*.region' => ['nullable', 'string', 'max:80'],
            'offices.*.postal' => ['nullable', 'string', 'max:12'],
            'offices.*.map' => ['nullable', 'string', 'max:200'],
        ], [
            'phone.regex' => 'Please enter a valid phone number.',
            'email_secondary.different' => 'The second email should be different from the main one — or leave it blank.',
            'announcement_text.required_if_accepted' => 'Enter the announcement text, or untick “Show the announcement bar”.',
            'offices.*.label.required_with' => 'Give this office a name (e.g. “Branch Office · Kochi”).',
            'offices.*.street.required_with' => 'Enter the street address for this office.',
            'offices.*.locality.required_with' => 'Enter the city for this office.',
            'header_logo.*' => 'The header logo must be a PNG, JPG or WebP image up to 4 MB.',
            'footer_logo.*' => 'The footer logo must be a PNG, JPG or WebP image up to 4 MB.',
        ]);

        $values = [
            'phone' => $data['phone'],
            'email' => $data['email'],
            'email_secondary' => $data['email_secondary'] ?? '', // blank = not shown
            'tagline' => $data['tagline'],
            'slogan' => $data['slogan'] ?? '',           // blank hides the slogan
            'footer_text' => $data['footer_text'] ?? '', // blank = built-in text
            'announcement_enabled' => $request->boolean('announcement_enabled') ? '1' : '0',
            'announcement_text' => $data['announcement_text'] ?? '', // blank = built-in text
            'announcement_home_only' => $request->boolean('announcement_home_only') ? '1' : '0',
            'offices' => json_encode($this->offices($data['offices']), JSON_UNESCAPED_UNICODE),
            'footer_logo_badge' => $request->boolean('footer_logo_badge') ? '1' : '0',
        ];

        foreach (['header_logo', 'footer_logo'] as $field) {
            $old = SiteSettings::get($field);

            if ($request->boolean("remove_$field") && $old) {
                $this->images->delete($old);
                $values[$field] = '';
            }

            if ($file = $request->file($field)) {
                try {
                    $values[$field] = $this->images->store($file, 'branding', 700)['path'];
                    $this->images->delete($old);
                } catch (RuntimeException $e) {
                    return back()->withInput()->withErrors([$field => $e->getMessage()]);
                }
            }
        }

        SiteSettings::save($values);

        return back()->with('status', 'Site settings saved.');
    }

    /** Turns the submitted office slots into the list the site uses, dropping empty slots. */
    private function offices(array $slots): array
    {
        $offices = [];

        foreach ($slots as $i => $slot) {
            $label = trim($slot['label'] ?? '');
            $street = trim($slot['street'] ?? '');
            $locality = trim($slot['locality'] ?? '');

            if ($label === '' && $street === '' && $locality === '') {
                continue;
            }

            $postal = trim($slot['postal'] ?? '');
            $map = trim($slot['map'] ?? '');

            $offices[] = [
                'key' => Str::slug($label) ?: "office-$i",
                'label' => $label,
                'street' => $street,
                'locality' => $locality,
                'region' => trim($slot['region'] ?? ''),
                'postal' => $postal,
                // What the Google map searches for; defaults to the address itself.
                'map' => $map !== '' ? $map : trim("$street, $locality $postal"),
            ];
        }

        return $offices;
    }
}

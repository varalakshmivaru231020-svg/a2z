<?php

namespace App\Http\Controllers;

use App\Models\Enquiry;
use App\Models\Service;
use App\Support\Seo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContactController extends Controller
{
    public function show(Request $request): View
    {
        $seo = Seo::forPage('contact')->schema([
            '@type' => 'ContactPage',
            'name' => 'Contact ' . config('site.name'),
            'url' => route('contact'),
            'isPartOf' => ['@id' => url('/') . '#website'],
            'about' => ['@id' => url('/') . '#organization'],
        ])->schema(Seo::organization());

        // /contact?service=plumbing pre-selects that service in the enquiry form.
        $subject = null;
        if ($slug = $request->query('service')) {
            $subject = Service::active()->where('slug', $slug)->value('title');
        }

        return view('pages.contact', [
            'seo' => $seo,
            'offices' => config('site.offices'),
            'prefillSubject' => $subject,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        // Honeypot: real visitors never see or fill this field.
        if (filled($request->input('website'))) {
            return $this->sent();
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email:rfc', 'max:150'],
            'phone' => ['required', 'string', 'regex:/^[0-9+\-\s()]{7,20}$/'],
            'subject' => ['nullable', 'string', 'max:150'],
            'message' => ['required', 'string', 'min:10', 'max:3000'],
        ], [
            'phone.regex' => 'Please enter a valid phone number.',
            'message.min' => 'Please tell us a little more about what you need (at least 10 characters).',
        ]);

        Enquiry::create($data + ['ip' => $request->ip()]);

        return $this->sent();
    }

    private function sent(): RedirectResponse
    {
        return back()->with('enquiry_sent', true)->withFragment('enquiry');
    }
}

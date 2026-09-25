@extends('layouts.admin')

@section('title', 'Site settings')

@section('content')
    <form method="post" enctype="multipart/form-data" action="{{ route('admin.settings.update') }}">
        @csrf @method('put')

        <div class="form-cols">
            <div>
                <section class="card">
                    <h2>Contact details</h2>
                    <p class="muted">Shown on the Contact page and in the footer, and used behind every “Call now”, WhatsApp and email button.</p>
                    <div class="form-grid">
                        <x-admin.field name="phone" label="Phone number" :value="config('site.phone')" required maxlength="20"
                            hint="Also used for WhatsApp. Include the country code, e.g. +91 98765 43210 (a plain 10-digit number is treated as Indian)." />
                        <x-admin.field name="email" label="Email address" type="email" :value="config('site.email')" required maxlength="150" />
                        <x-admin.field name="email_secondary" label="Second email address (optional)" type="email" :value="config('site.email_secondary')" maxlength="150"
                            hint="Shown under the main email in the footer and on the Contact page. Leave blank to show only one." />
                    </div>
                </section>

                <section class="card">
                    <h2>Announcement bar</h2>
                    <p class="muted">The thin dark strip at the very top of the website. Change the message any time, or switch it off.</p>
                    <div class="form-grid">
                        <x-admin.check class="full" name="announcement_enabled" label="Show the announcement bar" :checked="config('site.announcement.enabled')" />
                        <x-admin.field class="full" name="announcement_text" label="Announcement text" :value="config('site.announcement.text')" maxlength="160"
                            hint="One short line, up to 160 characters. Leave blank to use the built-in message." />
                        <x-admin.check class="full" name="announcement_home_only" label="Show it on the home page only" :checked="config('site.announcement.home_only')"
                            hint="Untick to show the bar on every page. It is hidden on phones to save screen space." />
                    </div>
                </section>

                <section class="card">
                    <h2>Tagline &amp; footer text</h2>
                    <div class="form-grid">
                        <x-admin.field class="full" name="tagline" label="Tagline" :value="config('site.tagline')" required maxlength="120"
                            hint="Shown in the footer and at the start of the home page introduction." />
                        <x-admin.field class="full" name="slogan" label="Slogan (optional)" :value="config('site.slogan')" maxlength="160"
                            hint="A small italic line in the footer and on the About page. Leave blank to hide it." />
                        <x-admin.field class="full" name="footer_text" label="Footer description" type="textarea" rows="3" :value="config('site.footer_text')" maxlength="400"
                            hint="The short paragraph under the footer logo. Leave blank to use the built-in text." />
                    </div>
                </section>

                <section class="card">
                    <h2>Office addresses</h2>
                    <p class="muted">The Contact page shows one card per office and the footer lists them; the first office is on the map first. Leave a block empty to skip it.</p>

                    @foreach ($offices as $i => $office)
                        <fieldset class="office-fields">
                            <legend>Office {{ $i + 1 }}{{ $i === 0 ? ' (required)' : '' }}</legend>
                            <div class="form-grid">
                                @foreach ([
                                    ['label', 'Office name', true, 'e.g. Branch Office · Kochi'],
                                    ['street', 'Street address', true, ''],
                                    ['locality', 'City', false, ''],
                                    ['region', 'State', false, ''],
                                    ['postal', 'PIN code', false, ''],
                                    ['map', 'Map search text (optional)', false, 'Defaults to the address'],
                                ] as [$field, $label, $full, $placeholder])
                                    <div class="fld {{ $full ? 'full' : '' }} @error("offices.$i.$field") has-error @enderror">
                                        <label for="o-{{ $i }}-{{ $field }}">{{ $label }}</label>
                                        <input id="o-{{ $i }}-{{ $field }}" name="offices[{{ $i }}][{{ $field }}]" type="text"
                                               value="{{ old("offices.$i.$field", $office[$field] ?? '') }}" placeholder="{{ $placeholder }}" maxlength="200">
                                        @error("offices.$i.$field")<p class="fld__error">{{ $message }}</p>@enderror
                                    </div>
                                @endforeach
                            </div>
                        </fieldset>
                    @endforeach
                </section>
            </div>

            <div>
                <div class="card">
                    <h2>Save changes</h2>
                    <p class="muted">Changes appear on the website straight away.</p>
                    <button class="btn btn--primary btn--block" type="submit">Save settings</button>
                </div>

                <section class="card">
                    <h2>Header logo</h2>
                    <div class="logo-preview"><img src="{{ $headerLogo['url'] }}" alt="Current header logo"></div>
                    @if ($headerLogo['custom'])
                        <x-admin.check name="remove_header_logo" label="Remove my logo (go back to the built-in one)" :checked="false" />
                    @endif
                    <div class="fld @error('header_logo') has-error @enderror">
                        <label for="f-header_logo">{{ $headerLogo['custom'] ? 'Replace header logo' : 'Upload a header logo' }}</label>
                        <input id="f-header_logo" name="header_logo" type="file" accept="image/png,image/jpeg,image/webp">
                        <p class="fld__hint">PNG (transparent works best), JPG or WebP · up to 4 MB. Shown on a white bar, so use a logo that reads on white.</p>
                        @error('header_logo')<p class="fld__error">{{ $message }}</p>@enderror
                    </div>
                </section>

                <section class="card">
                    <h2>Footer logo</h2>
                    <div class="logo-preview {{ $footerLogo['badge'] ? '' : 'logo-preview--dark' }}">
                        <img src="{{ $footerLogo['url'] }}" alt="Current footer logo" @if ($footerLogo['badge']) class="is-badge" @endif>
                    </div>
                    @if ($footerLogo['custom'])
                        <x-admin.check name="remove_footer_logo" label="Remove my logo (go back to the built-in one)" :checked="false" />
                    @endif
                    <div class="fld @error('footer_logo') has-error @enderror">
                        <label for="f-footer_logo">{{ $footerLogo['custom'] ? 'Replace footer logo' : 'Upload a footer logo' }}</label>
                        <input id="f-footer_logo" name="footer_logo" type="file" accept="image/png,image/jpeg,image/webp">
                        <p class="fld__hint">PNG (transparent works best), JPG or WebP · up to 4 MB. The footer is dark blue.</p>
                        @error('footer_logo')<p class="fld__error">{{ $message }}</p>@enderror
                    </div>
                    <x-admin.check name="footer_logo_badge" label="Show my footer logo on a white badge" :checked="$footerLogo['badge']"
                        hint="Tick this if your logo has dark lettering — on the dark footer it would be hard to read. Applies to an uploaded logo only." />
                </section>
            </div>
        </div>
    </form>
@endsection

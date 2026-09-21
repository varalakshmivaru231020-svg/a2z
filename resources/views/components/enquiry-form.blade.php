@props(['prefill' => null, 'id' => 'enquiry'])
{{-- $serviceOptions is supplied by a view composer (AppServiceProvider) --}}
<form id="{{ $id }}" class="form card" method="post" action="{{ route('contact.store') }}" data-form>
    @csrf

    @if (session('enquiry_sent'))
        <div class="alert alert--success" role="status">
            <x-icon name="check-circle" :size="22" />
            <div><strong>Thank you — your enquiry has been sent.</strong> Our team will call or email you shortly.</div>
        </div>
    @endif

    {{-- Honeypot: hidden from people, irresistible to bots --}}
    <div class="hp" aria-hidden="true">
        <label>Leave this field empty <input type="text" name="website" tabindex="-1" autocomplete="off"></label>
    </div>

    <div class="form__grid">
        <div class="field @error('name') has-error @enderror">
            <label for="{{ $id }}-name">Full name <span class="req">*</span></label>
            <input id="{{ $id }}-name" name="name" type="text" value="{{ old('name') }}" autocomplete="name" maxlength="120" required>
            @error('name') <p class="field__error">{{ $message }}</p> @enderror
        </div>

        <div class="field @error('phone') has-error @enderror">
            <label for="{{ $id }}-phone">Phone <span class="req">*</span></label>
            <input id="{{ $id }}-phone" name="phone" type="tel" inputmode="tel" value="{{ old('phone') }}" autocomplete="tel" maxlength="20" pattern="[0-9+\-\s()]{7,20}" required>
            @error('phone') <p class="field__error">{{ $message }}</p> @enderror
        </div>

        <div class="field field--full @error('email') has-error @enderror">
            <label for="{{ $id }}-email">Email <span class="req">*</span></label>
            <input id="{{ $id }}-email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" maxlength="150" required>
            @error('email') <p class="field__error">{{ $message }}</p> @enderror
        </div>

        <div class="field field--full @error('subject') has-error @enderror">
            <label for="{{ $id }}-subject">I'm interested in</label>
            <select id="{{ $id }}-subject" name="subject">
                <option value="">General enquiry</option>
                @foreach ($serviceOptions as $title)
                    <option value="{{ $title }}" @selected(old('subject', $prefill) === $title)>{{ $title }}</option>
                @endforeach
            </select>
            @error('subject') <p class="field__error">{{ $message }}</p> @enderror
        </div>

        <div class="field field--full @error('message') has-error @enderror">
            <label for="{{ $id }}-message">How can we help? <span class="req">*</span></label>
            <textarea id="{{ $id }}-message" name="message" rows="4" maxlength="3000" required>{{ old('message') }}</textarea>
            @error('message') <p class="field__error">{{ $message }}</p> @enderror
        </div>
    </div>

    <button class="btn btn--primary btn--lg btn--block" type="submit" data-submit>Send enquiry</button>
    <p class="form__note">We use your details only to respond to your enquiry.</p>
</form>

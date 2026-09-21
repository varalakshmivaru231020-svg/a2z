@props(['title' => 'Tell us what you need', 'text' => 'Call us or send a quick enquiry — our team will get back to you.'])
<section class="cta-band">
    <div class="container">
        <div class="cta-box">
            <h2>{{ $title }}</h2>
            <p>{{ $text }}</p>
            <div class="cta-box__actions">
                <a class="btn btn--glass btn--lg" href="tel:{{ config('site.phone_link') }}"><x-icon name="phone" :size="20" /> Call now</a>
                <a class="btn btn--white btn--lg" href="{{ route('contact') }}#enquiry">Send an enquiry <x-icon name="arrow-right" :size="20" /></a>
            </div>
        </div>
    </div>
</section>

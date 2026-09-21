@extends('layouts.site')

@section('content')
    <x-page-hero :title="$job->title" :lead="$job->summary" eyebrow="Job opening" :seo="$seo">
        <ul class="meta meta--light">
            <li><x-icon name="map-pin" :size="16" /> {{ $job->location }}</li>
            <li><x-icon name="briefcase" :size="16" /> {{ $job->typeLabel() }}</li>
            @if ($job->experience)<li><x-icon name="award" :size="16" /> {{ $job->experience }}</li>@endif
            @if ($job->department)<li><x-icon name="building" :size="16" /> {{ $job->department }}</li>@endif
        </ul>
    </x-page-hero>

    <section class="section">
        <div class="container detail">
            <article class="detail__main">
                @if (! $isOpen)
                    <div class="alert alert--warning" role="status">
                        <x-icon name="alert" :size="22" />
                        <div><strong>This position is closed.</strong> It is no longer accepting applications. <a href="{{ route('recruitment.index') }}">See current openings</a>.</div>
                    </div>
                @endif

                <h2>About the role</h2>
                @foreach ($job->paragraphs() as $paragraph)
                    <p>{!! nl2br(e($paragraph)) !!}</p>
                @endforeach

                @if ($responsibilities = $job->lines('responsibilities'))
                    <h2>Responsibilities</h2>
                    <ul class="check-list">
                        @foreach ($responsibilities as $line)
                            <li><x-icon name="check-circle" :size="22" /> <span>{{ $line }}</span></li>
                        @endforeach
                    </ul>
                @endif

                @if ($requirements = $job->lines('requirements'))
                    <h2>Requirements</h2>
                    <ul class="check-list">
                        @foreach ($requirements as $line)
                            <li><x-icon name="check-circle" :size="22" /> <span>{{ $line }}</span></li>
                        @endforeach
                    </ul>
                @endif
            </article>

            <aside class="detail__side">
                <div class="card card--pad side-card">
                    <h2>Job details</h2>
                    <dl class="facts">
                        <div><dt>Location</dt><dd>{{ $job->location }}</dd></div>
                        <div><dt>Type</dt><dd>{{ $job->typeLabel() }}</dd></div>
                        @if ($job->experience)<div><dt>Experience</dt><dd>{{ $job->experience }}</dd></div>@endif
                        @if ($job->salary)<div><dt>Salary</dt><dd>{{ $job->salary }}</dd></div>@endif
                        <div><dt>Vacancies</dt><dd>{{ $job->vacancies }}</dd></div>
                        @if ($job->published_at)<div><dt>Posted</dt><dd>{{ $job->published_at->format('j M Y') }}</dd></div>@endif
                        @if ($job->closing_date)<div><dt>Apply by</dt><dd>{{ $job->closing_date->format('j M Y') }}</dd></div>@endif
                    </dl>
                </div>

                @if ($isOpen)
                    <form id="apply" class="form card card--pad" method="post" action="{{ route('recruitment.apply', $job->slug) }}" enctype="multipart/form-data" data-form>
                        @csrf
                        <h2>Apply for this job</h2>

                        @if (session('applied'))
                            <div class="alert alert--success" role="status">
                                <x-icon name="check-circle" :size="22" />
                                <div><strong>Application received — thank you!</strong> Our team will review your details and contact you if your profile matches.</div>
                            </div>
                        @endif
                        @if (session('error'))
                            <div class="alert alert--error" role="alert"><x-icon name="alert" :size="22" /><div>{{ session('error') }}</div></div>
                        @endif

                        <div class="hp" aria-hidden="true">
                            <label>Leave this field empty <input type="text" name="website" tabindex="-1" autocomplete="off"></label>
                        </div>

                        <div class="field @error('name') has-error @enderror">
                            <label for="a-name">Full name <span class="req">*</span></label>
                            <input id="a-name" name="name" type="text" value="{{ old('name') }}" autocomplete="name" maxlength="120" required>
                            @error('name') <p class="field__error">{{ $message }}</p> @enderror
                        </div>

                        <div class="field @error('email') has-error @enderror">
                            <label for="a-email">Email <span class="req">*</span></label>
                            <input id="a-email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" maxlength="150" required>
                            @error('email') <p class="field__error">{{ $message }}</p> @enderror
                        </div>

                        <div class="field @error('phone') has-error @enderror">
                            <label for="a-phone">Phone <span class="req">*</span></label>
                            <input id="a-phone" name="phone" type="tel" inputmode="tel" value="{{ old('phone') }}" autocomplete="tel" maxlength="20" pattern="[0-9+\-\s()]{7,20}" required>
                            @error('phone') <p class="field__error">{{ $message }}</p> @enderror
                        </div>

                        <div class="field @error('current_location') has-error @enderror">
                            <label for="a-location">Current location</label>
                            <input id="a-location" name="current_location" type="text" value="{{ old('current_location') }}" maxlength="120" placeholder="e.g. Horamavu, Bangalore">
                            @error('current_location') <p class="field__error">{{ $message }}</p> @enderror
                        </div>

                        <div class="field @error('experience') has-error @enderror">
                            <label for="a-exp">Total experience</label>
                            <input id="a-exp" name="experience" type="text" value="{{ old('experience') }}" maxlength="60" placeholder="e.g. 3 years, or Fresher">
                            @error('experience') <p class="field__error">{{ $message }}</p> @enderror
                        </div>

                        <div class="field @error('cover_note') has-error @enderror">
                            <label for="a-note">Anything you'd like us to know</label>
                            <textarea id="a-note" name="cover_note" rows="3" maxlength="2000">{{ old('cover_note') }}</textarea>
                            @error('cover_note') <p class="field__error">{{ $message }}</p> @enderror
                        </div>

                        <div class="field @error('resume') has-error @enderror">
                            <label for="a-resume">Resume <span class="req">*</span></label>
                            <input id="a-resume" name="resume" type="file" accept=".pdf,.doc,.docx,application/pdf" required>
                            <p class="field__hint">PDF, DOC or DOCX · up to 5 MB</p>
                            @error('resume') <p class="field__error">{{ $message }}</p> @enderror
                        </div>

                        <button class="btn btn--primary btn--lg btn--block" type="submit" data-submit>Submit application</button>
                        <p class="form__note">Your details and resume are used only to consider you for a role at {{ config('site.brand') }}.</p>
                    </form>
                @else
                    <div class="card card--pad side-card">
                        <h2>Interested in working with us?</h2>
                        <p>Other roles may be open. Browse current openings or get in touch.</p>
                        <a class="btn btn--primary btn--block" href="{{ route('recruitment.index') }}">See current openings</a>
                        <a class="btn btn--outline btn--block" href="{{ route('contact') }}">Contact us</a>
                    </div>
                @endif
            </aside>
        </div>
    </section>
@endsection

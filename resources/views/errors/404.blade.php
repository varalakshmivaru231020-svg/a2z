@extends('layouts.site')

@section('content')
    <section class="section">
        <div class="container">
            <div class="empty-state">
                <p class="error-code">404</p>
                <h1>We couldn't find that page</h1>
                <p>The page may have moved, or the link may be out of date. Try one of these instead.</p>
                <div class="empty-state__actions">
                    <a class="btn btn--primary" href="{{ route('home') }}">Go to the home page</a>
                    <a class="btn btn--outline" href="{{ route('services.index') }}">Our services</a>
                    <a class="btn btn--outline" href="{{ route('contact') }}">Contact us</a>
                </div>
            </div>
        </div>
    </section>
@endsection

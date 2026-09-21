@extends('layouts.admin')

@section('title', 'Enquiry from ' . $enquiry->name)

@section('actions')
    <a class="btn btn--outline" href="{{ route('admin.enquiries.index') }}">Back to enquiries</a>
@endsection

@section('content')
    <div class="form-cols">
        <div class="card">
            <h2>Message</h2>
            <div class="prose">{{ $enquiry->message }}</div>
        </div>

        <div>
            <div class="card">
                <h2>Contact details</h2>
                <dl class="details">
                    <dt>Name</dt><dd>{{ $enquiry->name }}</dd>
                    <dt>Email</dt><dd><a href="mailto:{{ $enquiry->email }}">{{ $enquiry->email }}</a></dd>
                    <dt>Phone</dt><dd><a href="tel:{{ preg_replace('/[^0-9+]/', '', $enquiry->phone) }}">{{ $enquiry->phone }}</a></dd>
                    <dt>Interested in</dt><dd>{{ $enquiry->subject ?: 'General enquiry' }}</dd>
                    <dt>Received</dt><dd>{{ $enquiry->created_at->format('j M Y, g:i a') }}</dd>
                </dl>
                <div class="form-actions" style="margin-top:16px">
                    <a class="btn btn--primary" href="mailto:{{ $enquiry->email }}?subject={{ rawurlencode('Re: your enquiry to ' . config('site.brand')) }}"><x-icon name="mail" :size="18" /> Reply by email</a>
                    <a class="btn btn--outline" href="tel:{{ preg_replace('/[^0-9+]/', '', $enquiry->phone) }}"><x-icon name="phone" :size="18" /> Call</a>
                </div>
            </div>

            <form class="card" method="post" action="{{ route('admin.enquiries.destroy', $enquiry) }}" data-confirm="Delete this enquiry?">
                @csrf @method('delete')
                <button class="btn btn--danger btn--block" type="submit">Delete enquiry</button>
            </form>
        </div>
    </div>
@endsection

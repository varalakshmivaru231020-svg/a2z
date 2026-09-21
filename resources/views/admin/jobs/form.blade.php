@extends('layouts.admin')

@php($isEdit = $job->exists)
@section('title', $isEdit ? 'Edit job opening' : 'Post a job')

@section('actions')
    <a class="btn btn--outline" href="{{ route('admin.jobs.index') }}">Back to jobs</a>
@endsection

@section('content')
    <form method="post" action="{{ $isEdit ? route('admin.jobs.update', $job) : route('admin.jobs.store') }}">
        @csrf
        @if ($isEdit) @method('put') @endif

        <div class="form-cols">
            <div class="card">
                <div class="form-grid">
                    <x-admin.field class="full" name="title" label="Job title" :value="$job->title" required maxlength="150" placeholder="e.g. Housekeeping Supervisor" />
                    <x-admin.field name="department" label="Department / category" :value="$job->department" maxlength="80" placeholder="e.g. Facility Management" />
                    <x-admin.field name="location" label="Location" :value="$job->location" required maxlength="120" />

                    <x-admin.field name="employment_type" label="Employment type" type="select" required>
                        @foreach (config('site.employment_types') as $key => $label)
                            <option value="{{ $key }}" @selected(old('employment_type', $job->employment_type) === $key)>{{ $label }}</option>
                        @endforeach
                    </x-admin.field>
                    <x-admin.field name="vacancies" label="Vacancies" type="number" :value="$job->vacancies ?? 1" min="1" max="999" required />

                    <x-admin.field name="experience" label="Experience" :value="$job->experience" maxlength="80" placeholder="e.g. 2–4 years" />
                    <x-admin.field name="salary" label="Salary (optional)" :value="$job->salary" maxlength="80" placeholder="e.g. ₹18,000 – ₹25,000 per month" />

                    <x-admin.field class="full" name="summary" label="Short summary" type="textarea" rows="2" :value="$job->summary" required maxlength="300" hint="One or two lines shown in the job list and in search results." />
                    <x-admin.field class="full" name="description" label="About the role" type="textarea" rows="7" :value="$job->description" required hint="Separate paragraphs with a blank line." />
                    <x-admin.field class="full" name="responsibilities" label="Responsibilities" type="textarea" rows="5" :value="$job->responsibilities" hint="One per line." />
                    <x-admin.field class="full" name="requirements" label="Requirements" type="textarea" rows="5" :value="$job->requirements" hint="One per line." />
                </div>

                <h2 class="section-title">Search engine listing (SEO)</h2>
                <div class="form-grid">
                    <x-admin.field class="full" name="meta_title" label="Meta title" :value="$job->meta_title" maxlength="70" counter="60" hint="Leave blank to use “Job title – Job in Location”." />
                    <x-admin.field class="full" name="meta_description" label="Meta description" type="textarea" rows="2" :value="$job->meta_description" maxlength="200" counter="160" hint="Leave blank to use the short summary." />
                    <x-admin.field class="full" name="slug" label="URL slug" :value="$job->slug" maxlength="100" hint="Leave blank to generate from the title." />
                </div>
            </div>

            <div>
                <div class="card">
                    <h2>Publishing</h2>
                    <x-admin.field name="status" label="Status" type="select" required>
                        <option value="open" @selected(old('status', $job->status) === 'open')>Open — accepting applications</option>
                        <option value="closed" @selected(old('status', $job->status) === 'closed')>Closed</option>
                    </x-admin.field>
                    <x-admin.field name="closing_date" label="Closing date (optional)" type="date" :value="$job->closing_date?->format('Y-m-d')" hint="The job closes automatically after this date." />

                    <div class="form-actions">
                        <button class="btn btn--primary" type="submit">{{ $isEdit ? 'Save changes' : 'Post job' }}</button>
                        <a class="btn btn--outline" href="{{ route('admin.jobs.index') }}">Cancel</a>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection

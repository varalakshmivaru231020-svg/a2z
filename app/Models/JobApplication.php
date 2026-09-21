<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class JobApplication extends Model
{
    public const STATUSES = [
        'new' => 'New',
        'reviewed' => 'Reviewed',
        'shortlisted' => 'Shortlisted',
        'rejected' => 'Rejected',
        'hired' => 'Hired',
    ];

    protected $fillable = [
        'job_opening_id', 'job_title', 'name', 'email', 'phone', 'current_location', 'experience',
        'cover_note', 'resume_path', 'resume_name', 'status', 'admin_notes', 'ip',
    ];

    protected static function booted(): void
    {
        // Resumes are personal data: never leave the file behind once the record is gone.
        static::deleting(fn (JobApplication $application) => Storage::disk('local')->delete($application->resume_path));
    }

    public function job(): BelongsTo
    {
        return $this->belongsTo(JobOpening::class, 'job_opening_id');
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst($this->status);
    }

    /** Friendly file name for the resume download, e.g. "asha-rao-housekeeping-supervisor.pdf". */
    public function resumeDownloadName(): string
    {
        $ext = pathinfo($this->resume_path, PATHINFO_EXTENSION);

        return Str::slug($this->name . ' ' . $this->job_title) . ($ext ? ".$ext" : '');
    }
}

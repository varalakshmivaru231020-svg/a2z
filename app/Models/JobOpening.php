<?php

namespace App\Models;

use App\Models\Concerns\HasUniqueSlug;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JobOpening extends Model
{
    use HasUniqueSlug;

    public const OPEN = 'open';
    public const CLOSED = 'closed';

    protected $fillable = [
        'title', 'slug', 'department', 'location', 'employment_type', 'experience', 'salary',
        'vacancies', 'summary', 'description', 'responsibilities', 'requirements',
        'closing_date', 'status', 'published_at', 'closed_at', 'meta_title', 'meta_description',
    ];

    protected function casts(): array
    {
        return [
            'closing_date' => 'date',
            'published_at' => 'datetime',
            'closed_at' => 'datetime',
            'vacancies' => 'integer',
        ];
    }

    public function applications(): HasMany
    {
        return $this->hasMany(JobApplication::class);
    }

    /** Open for applications: marked open and not past its closing date. */
    public function scopeOpen(Builder $query): Builder
    {
        return $query->where('status', self::OPEN)
            ->where(fn (Builder $q) => $q->whereNull('closing_date')->orWhereDate('closing_date', '>=', today()));
    }

    public function isOpen(): bool
    {
        return $this->status === self::OPEN
            && ($this->closing_date === null || ! $this->closing_date->isPast() || $this->closing_date->isToday());
    }

    public function url(): string
    {
        return route('recruitment.show', $this->slug);
    }

    public function typeLabel(): string
    {
        return config("site.employment_types.{$this->employment_type}", 'Full-time');
    }

    /** schema.org employmentType value. */
    public function schemaEmploymentType(): string
    {
        return match ($this->employment_type) {
            'part_time' => 'PART_TIME',
            'contract' => 'CONTRACTOR',
            'temporary' => 'TEMPORARY',
            default => 'FULL_TIME',
        };
    }

    public function paragraphs(): array
    {
        return preg_split('/\R{2,}/', trim((string) $this->description), -1, PREG_SPLIT_NO_EMPTY) ?: [];
    }

    /** A multi-line text column as a clean list of bullet points. */
    public function lines(string $column): array
    {
        return array_values(array_filter(array_map(
            fn ($line) => trim(ltrim(trim($line), "-•*· \t")),
            preg_split('/\R/', (string) $this->{$column}),
        )));
    }
}

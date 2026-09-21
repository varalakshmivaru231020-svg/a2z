<?php

namespace App\Models;

use App\Models\Concerns\HasUniqueSlug;
use App\Support\ImageUploader;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasUniqueSlug;

    protected $fillable = [
        'category', 'title', 'slug', 'summary', 'description', 'icon', 'image', 'features',
        'is_active', 'is_featured', 'sort_order', 'meta_title', 'meta_description',
    ];

    protected function casts(): array
    {
        return [
            'features' => 'array',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::deleting(fn (Service $service) => app(ImageUploader::class)->delete($service->image));
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('title');
    }

    public function url(): string
    {
        return route('services.show', $this->slug);
    }

    public function imageUrl(): ?string
    {
        return $this->image ? asset('uploads/' . $this->image) : null;
    }

    public function categoryName(): string
    {
        return config("site.service_categories.{$this->category}.name", ucfirst($this->category));
    }

    /** Long description split into paragraphs on blank lines. */
    public function paragraphs(): array
    {
        return preg_split('/\R{2,}/', trim((string) $this->description), -1, PREG_SPLIT_NO_EMPTY) ?: [];
    }
}

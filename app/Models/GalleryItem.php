<?php

namespace App\Models;

use App\Support\ImageUploader;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class GalleryItem extends Model
{
    protected $fillable = ['title', 'caption', 'path', 'thumb_path', 'is_active', 'sort_order'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    protected static function booted(): void
    {
        static::deleting(fn (GalleryItem $item) => app(ImageUploader::class)->delete($item->path, $item->thumb_path));
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /** Manual order first, then newest. */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderByDesc('id');
    }

    public function url(): string
    {
        return asset('uploads/' . $this->path);
    }

    public function thumbUrl(): string
    {
        return asset('uploads/' . ($this->thumb_path ?: $this->path));
    }

    /** Alt text: the title, else the caption, else a generic description. */
    public function alt(): string
    {
        return $this->title ?: ($this->caption ?: config('site.brand') . ' gallery photo');
    }
}

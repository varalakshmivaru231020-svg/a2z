<?php

namespace App\Models;

use App\Support\ImageUploader;
use Illuminate\Database\Eloquent\Model;

class SeoPage extends Model
{
    protected $fillable = ['page_key', 'meta_title', 'meta_description', 'banner'];

    protected static function booted(): void
    {
        static::deleting(fn (SeoPage $page) => app(ImageUploader::class)->delete($page->banner));
    }

    public function bannerUrl(): ?string
    {
        return filled($this->banner) ? asset('uploads/' . $this->banner) : null;
    }
}

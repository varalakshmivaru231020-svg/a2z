<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ServiceCategory extends Model
{
    protected $fillable = ['key', 'name', 'blurb', 'icon', 'sort_order'];

    private static ?array $map = null;

    protected static function booted(): void
    {
        static::saved(fn () => self::$map = null);
        static::deleted(fn () => self::$map = null);
    }

    /** key => ['name', 'blurb', 'icon'], in display order. */
    public static function map(): array
    {
        return self::$map ??= static::query()
            ->orderBy('sort_order')->orderBy('name')
            ->get()
            ->mapWithKeys(fn (self $c) => [$c->key => [
                'name' => $c->name,
                'blurb' => (string) $c->blurb,
                'icon' => $c->icon,
            ]])
            ->all();
    }

    public static function uniqueKey(string $name): string
    {
        $base = Str::limit(Str::slug($name), 30, '') ?: 'category';
        $key = $base;
        $n = 2;

        while (static::query()->where('key', $key)->exists()) {
            $key = $base . '-' . $n++;
        }

        return $key;
    }

    public function serviceCount(): int
    {
        return Service::query()->where('category', $this->key)->count();
    }
}

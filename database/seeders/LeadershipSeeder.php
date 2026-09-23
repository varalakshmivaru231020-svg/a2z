<?php

namespace Database\Seeders;

use App\Models\Leader;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

/**
 * The leadership team carried over from the company brochure (see config/site.php).
 * The owner can edit every entry from the admin panel afterwards.
 * Safe to re-run: leaders are matched by name and only created if missing.
 */
class LeadershipSeeder extends Seeder
{
    public function run(): void
    {
        $order = 0;

        foreach (config('site.leadership', []) as $person) {
            $order += 10;

            if (Leader::where('name', $person['name'])->exists()) {
                continue;
            }

            $photo = null;
            if (! empty($person['photo'])) {
                $source = public_path($person['photo']);
                if (is_file($source)) {
                    $photo = 'leadership/' . basename($person['photo']);
                    Storage::disk('uploads')->put($photo, file_get_contents($source));
                }
            }

            Leader::create([
                'name' => $person['name'],
                'role' => $person['role'],
                'bio' => $person['bio'],
                'photo' => $photo,
                'is_active' => true,
                'sort_order' => $order,
            ]);
        }
    }
}

<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AdminUserSeeder extends Seeder
{
    /**
     * Creates the admin login from ADMIN_EMAIL / ADMIN_PASSWORD in .env.
     * - No password set: a random one is generated and printed once.
     * - Account already exists and no password set: left untouched.
     * - Password set: the account is created, or its password is reset to it.
     */
    public function run(): void
    {
        $email = config('site.admin.email');
        $password = config('site.admin.password');

        if (blank($email)) {
            $this->command?->warn('ADMIN_EMAIL is not set in .env — no admin user was created.');

            return;
        }

        $existing = User::where('email', $email)->first();

        if ($existing && blank($password)) {
            $this->command?->info("Admin {$email} already exists — password unchanged.");

            return;
        }

        $generated = blank($password);
        $password = $generated ? Str::password(16, symbols: false) : $password;

        User::updateOrCreate(
            ['email' => $email],
            ['name' => config('site.admin.name'), 'password' => $password],
        );

        $this->command?->info("Admin user ready: {$email}");
        if ($generated) {
            $this->command?->warn("Generated password (shown once): {$password}");
        }
    }
}

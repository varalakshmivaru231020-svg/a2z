<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Storage;

abstract class TestCase extends BaseTestCase
{
    /** Uploads and resumes go to throw-away fake disks, never to public/uploads or storage/app/private. */
    protected function fakeDisks(): void
    {
        Storage::fake('uploads');
        Storage::fake('local');
    }

    protected function actingAsAdmin(): User
    {
        $admin = User::factory()->create();
        $this->actingAs($admin);

        return $admin;
    }
}

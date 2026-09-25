<?php

namespace Tests\Feature;

use App\Models\Leader;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LeadershipTest extends TestCase
{
    use RefreshDatabase;

    private function leader(array $override = []): Leader
    {
        return Leader::create($override + ['name' => 'Mr. Ashok', 'role' => 'Operations Manager', 'bio' => 'Runs the field teams.', 'is_active' => true, 'sort_order' => 30]);
    }

    public function test_leadership_screens_need_a_login(): void
    {
        $leader = $this->leader();

        $this->get('/admin/leadership')->assertRedirect('/admin/login');
        $this->get("/admin/leadership/{$leader->id}/edit")->assertRedirect('/admin/login');
        $this->put("/admin/leadership/{$leader->id}", [])->assertRedirect('/admin/login');
        $this->delete("/admin/leadership/{$leader->id}")->assertRedirect('/admin/login');
        $this->assertDatabaseCount('leaders', 1);
    }

    public function test_the_edit_form_is_filled_with_the_selected_person(): void
    {
        $this->actingAsAdmin();
        $leader = $this->leader(['name' => 'Mr. Noor Hussain', 'role' => 'Business Developer']);

        $this->get("/admin/leadership/{$leader->id}/edit")->assertOk()
            ->assertSee('Mr. Noor Hussain')->assertSee('Business Developer')
            ->assertSee("/admin/leadership/{$leader->id}", false);
    }

    public function test_editing_a_person_updates_them_instead_of_adding_a_duplicate(): void
    {
        $this->actingAsAdmin();
        $leader = $this->leader();

        $this->put("/admin/leadership/{$leader->id}", ['name' => 'Mr. Ashok Kumar', 'role' => 'Director', 'bio' => 'New bio.', 'sort_order' => 5, 'is_active' => '1'])
            ->assertRedirect('/admin/leadership')->assertSessionHas('status', '“Mr. Ashok Kumar” updated.');

        $this->assertDatabaseCount('leaders', 1);
        $this->assertSame('Director', $leader->fresh()->role);
    }

    public function test_deleting_a_person_removes_them_and_their_photo(): void
    {
        $this->fakeDisks();
        $this->actingAsAdmin();

        $this->post('/admin/leadership', ['name' => 'Mrs. Shamali Prasad', 'role' => 'General Manager', 'bio' => 'Leads operations.', 'sort_order' => 20, 'is_active' => '1',
            'photo' => UploadedFile::fake()->image('p.jpg', 600, 700)])->assertSessionHasNoErrors();
        $leader = Leader::firstOrFail();
        Storage::disk('uploads')->assertExists($leader->photo);
        $other = $this->leader(['name' => 'Mr. Prasad Ashok']);

        $this->delete("/admin/leadership/{$leader->id}")
            ->assertRedirect('/admin/leadership')->assertSessionHas('status', '“Mrs. Shamali Prasad” deleted.');

        $this->assertModelMissing($leader);
        $this->assertModelExists($other);
        $this->assertSame([], Storage::disk('uploads')->allFiles('leadership'));
        // (The confirmation banner still names the person, so look for their row's links instead.)
        $this->get('/admin/leadership')->assertOk()
            ->assertDontSee("/admin/leadership/{$leader->id}/edit", false)
            ->assertSee("/admin/leadership/{$other->id}/edit", false);
    }
}

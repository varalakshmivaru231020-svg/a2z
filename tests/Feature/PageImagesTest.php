<?php

namespace Tests\Feature;

use App\Support\PageImages;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PageImagesTest extends TestCase
{
    use RefreshDatabase;

    private function save(array $files = [], array $remove = [])
    {
        return $this->put('/admin/images', ['images' => $files, 'remove_image' => $remove]);
    }

    public function test_the_screen_needs_a_login_and_lists_every_slot(): void
    {
        $this->get('/admin/images')->assertRedirect('/admin/login');
        $this->put('/admin/images', [])->assertRedirect('/admin/login');

        $this->actingAsAdmin();
        $screen = $this->get('/admin/images')->assertOk()->assertSee('Home page')->assertSee('About us page')->getContent();

        foreach (array_keys(PageImages::SLOTS) as $slot) {
            $this->assertSame(1, substr_count($screen, "name=\"images[$slot]\""), $slot);
        }
    }

    public function test_pages_use_the_built_in_photos_until_something_is_uploaded(): void
    {
        $this->get('/')->assertOk()->assertSee('img/office.jpg', false)->assertSee('img/crew.jpg', false)->assertDontSee('/uploads/pages/', false);
        $this->get('/about')->assertOk()->assertSee('img/office.jpg', false)->assertDontSee('/uploads/pages/', false)->assertDontSee('--vm-image', false);
    }

    public function test_uploaded_images_replace_the_right_pictures_on_the_home_and_about_pages(): void
    {
        $this->fakeDisks();
        $this->actingAsAdmin();

        $this->save([
            'home_hero' => UploadedFile::fake()->image('hero.jpg', 1800, 900),
            'about_crew' => UploadedFile::fake()->image('crew.png', 800, 900),
            'about_vision' => UploadedFile::fake()->image('vision.jpg', 2400, 1200),
        ])->assertSessionHasNoErrors();

        $this->assertCount(3, Storage::disk('uploads')->allFiles('pages'));

        $home = $this->get('/')->getContent();
        $this->assertMatchesRegularExpression('#<img src="[^"]*/uploads/pages/[^"]+\?v=\d+"[^>]*fetchpriority="high"#', $home, 'home hero');
        $this->assertStringContainsString('img/crew.jpg', $home, 'the untouched home slot keeps its built-in photo');

        $about = $this->get('/about')->getContent();
        $this->assertMatchesRegularExpression('#<img src="[^"]*/uploads/pages/[^"]+\?v=\d+" alt="Illustration of a facility team collaborating[^>]*width="800" height="900"#', $about, 'about crew keeps its proportions');
        $this->assertMatchesRegularExpression('#class="vm-section" style="--vm-image: url\(\'[^\']*/uploads/pages/[^\']+\'\)"#', $about, 'vision background');
        $this->assertStringContainsString('img/office.jpg', $about, 'the untouched profile slot keeps its built-in photo');

        [$width] = getimagesizefromstring(Storage::disk('uploads')->get(PageImages::path('about_vision')));
        $this->assertLessThanOrEqual(1920, $width, 'large images are downsized');
    }

    public function test_replacing_and_removing_an_image_cleans_up_the_files(): void
    {
        $this->fakeDisks();
        $this->actingAsAdmin();

        $this->save(['home_why' => UploadedFile::fake()->image('one.jpg', 600, 700)]);
        $first = PageImages::path('home_why');
        Storage::disk('uploads')->assertExists($first);

        $this->save(['home_why' => UploadedFile::fake()->image('two.jpg', 600, 700)]);
        $second = PageImages::path('home_why');
        $this->assertNotSame($first, $second);
        Storage::disk('uploads')->assertMissing($first);
        Storage::disk('uploads')->assertExists($second);

        $this->save([], ['home_why' => '1'])->assertSessionHasNoErrors();
        $this->assertNull(PageImages::path('home_why'));
        $this->assertSame([], Storage::disk('uploads')->allFiles('pages'));
        $this->get('/')->assertSee('img/crew.jpg', false)->assertDontSee('/uploads/pages/', false);
    }

    public function test_the_cta_banner_photo_appears_on_every_page_with_the_band_and_is_plain_until_uploaded(): void
    {
        $this->fakeDisks();
        $this->actingAsAdmin();

        foreach (['/about', '/services', '/gallery', '/recruitment'] as $url) {
            $this->get($url)->assertOk()->assertDontSee('--cta-image', false)->assertDontSee('cta-box has-image', false);
        }

        $this->save(['cta_banner' => UploadedFile::fake()->image('cta.jpg', 2400, 800)])->assertSessionHasNoErrors();
        $path = PageImages::path('cta_banner');
        Storage::disk('uploads')->assertExists($path);

        foreach (['/about', '/services', '/gallery', '/recruitment'] as $url) {
            $this->assertMatchesRegularExpression(
                '#class="cta-box has-image" style="--cta-image: url\(\'[^\']*/uploads/pages/[^\']+\'\)"#',
                $this->get($url)->assertOk()->getContent(),
                $url,
            );
        }

        $this->get('/admin/images')->assertOk()->assertSee('Your uploaded image.');

        $this->save([], ['cta_banner' => '1']);
        Storage::disk('uploads')->assertMissing($path);
        $this->get('/about')->assertDontSee('--cta-image', false);
    }

    public function test_bad_files_are_refused_and_nothing_is_saved(): void
    {
        $this->fakeDisks();
        $this->actingAsAdmin();

        $this->save(['home_hero' => UploadedFile::fake()->create('photo.pdf', 20, 'application/pdf')])->assertSessionHasErrors('images.home_hero');
        $this->save(['home_hero' => UploadedFile::fake()->createWithContent('p.svg', '<svg xmlns="http://www.w3.org/2000/svg"><script>1</script></svg>')])->assertSessionHasErrors('images.home_hero');
        $this->save(['home_hero' => UploadedFile::fake()->image('huge.jpg', 100, 100)->size(9000)])->assertSessionHasErrors('images.home_hero');

        $this->assertNull(PageImages::path('home_hero'));
        $this->assertSame([], Storage::disk('uploads')->allFiles('pages'));
    }
}

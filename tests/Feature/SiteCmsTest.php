<?php

namespace Tests\Feature;

use App\Models\Media;
use App\Models\Site;
use App\Models\Template;
use App\Models\User;
use Database\Seeders\TemplateSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SiteCmsTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Template $template;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(TemplateSeeder::class);
        $this->template = Template::where('type', 'product')->firstOrFail();
        $this->user = User::factory()->create();
    }

    private function makeSite(array $overrides = []): Site
    {
        return Site::create(array_merge([
            'user_id' => $this->user->id,
            'template_id' => $this->template->id,
            'slug' => 'my-landing',
            'content' => $this->template->default_content,
            'published' => true,
        ], $overrides));
    }

    public function test_public_show_renders_vietnamese_content(): void
    {
        $site = $this->makeSite();
        $viTitle = $this->template->default_content['hero']['title']['vi'];

        $response = $this->get('/vi/site/'.$site->slug);

        $response->assertStatus(200);
        $response->assertSee($viTitle, false);
    }

    public function test_public_show_renders_english_content(): void
    {
        $site = $this->makeSite();
        $enTitle = $this->template->default_content['hero']['title']['en'];

        $response = $this->get('/en/site/'.$site->slug);

        $response->assertStatus(200);
        $response->assertSee($enTitle, false);
    }

    public function test_public_show_returns_404_for_unpublished_site(): void
    {
        $site = $this->makeSite(['published' => false]);

        $this->get('/vi/site/'.$site->slug)->assertStatus(404);
    }

    public function test_public_show_returns_404_for_unsupported_locale(): void
    {
        $site = $this->makeSite();

        $this->get('/fr/site/'.$site->slug)->assertStatus(404);
    }

    public function test_admin_create_clones_default_content(): void
    {
        $response = $this->actingAs($this->user)->post('/admin/sites', [
            'template_id' => $this->template->id,
            'slug' => 'fresh-site',
        ]);

        $site = Site::where('slug', 'fresh-site')->firstOrFail();

        $response->assertRedirect(route('admin.sites.edit', $site));
        $this->assertFalse($site->published);
        $this->assertSame(
            $this->template->default_content,
            $site->content,
            'new site content must deep-equal template default_content'
        );
        $this->assertSame($this->user->id, $site->user_id);
    }

    public function test_admin_update_rehydrates_dot_notation_into_nested_json(): void
    {
        $site = $this->makeSite(['published' => false]);

        $this->actingAs($this->user)->put('/admin/sites/'.$site->id, [
            'content' => [
                'hero.title' => ['en' => 'Custom English', 'vi' => 'Tiếng Việt Custom'],
                'hero.cta_url' => '#custom',
            ],
            'published' => '1',
        ])->assertRedirect(route('admin.sites.edit', $site));

        $fresh = $site->fresh();
        $this->assertSame('Custom English', $fresh->content['hero']['title']['en']);
        $this->assertSame('Tiếng Việt Custom', $fresh->content['hero']['title']['vi']);
        $this->assertSame('#custom', $fresh->content['hero']['cta_url']);
        $this->assertTrue($fresh->published);
    }

    public function test_admin_preview_renders_regardless_of_published(): void
    {
        $site = $this->makeSite(['published' => false]);

        $this->actingAs($this->user)
            ->get('/admin/sites/'.$site->id.'/preview')
            ->assertStatus(200);
    }

    public function test_admin_update_preserves_indexed_list_round_trip(): void
    {
        $site = $this->makeSite(['published' => false]);
        $originalItems = $site->content['features']['items'];
        $this->assertCount(3, $originalItems, 'sanity: seed should have 3 features');

        // Simulate what the edit form actually sends for an indexed list:
        // a JSON-encoded string, not a PHP array.
        $this->actingAs($this->user)->put('/admin/sites/'.$site->id, [
            'content' => [
                'features.items' => json_encode($originalItems, JSON_UNESCAPED_UNICODE),
                'hero.title' => $site->content['hero']['title'],
            ],
            'published' => '1',
        ])->assertRedirect(route('admin.sites.edit', $site));

        $fresh = $site->fresh();
        $this->assertIsArray($fresh->content['features']['items']);
        $this->assertCount(3, $fresh->content['features']['items']);
        $this->assertSame($originalItems, $fresh->content['features']['items']);

        // Preview must render without throwing on the foreach.
        // App default locale is 'vi', so assert the Vietnamese title is visible.
        $this->actingAs($this->user)
            ->get('/admin/sites/'.$site->id.'/preview')
            ->assertStatus(200)
            ->assertSee($originalItems[0]['title']['vi'], false);
    }

    public function test_admin_edit_rejects_non_owner(): void
    {
        $site = $this->makeSite();
        $otherUser = User::factory()->create();

        $this->actingAs($otherUser)
            ->get('/admin/sites/'.$site->id.'/edit')
            ->assertStatus(403);
    }

    public function test_image_upload_stores_file_and_returns_url(): void
    {
        Storage::fake('public');
        $site = $this->makeSite();

        $response = $this->actingAs($this->user)->post(
            '/admin/sites/'.$site->id.'/images',
            ['image' => UploadedFile::fake()->image('banner.jpg', 800, 600)]
        );

        $response->assertStatus(200);
        $response->assertJsonStructure(['id', 'url']);

        $media = Media::where('site_id', $site->id)->firstOrFail();
        Storage::disk('public')->assertExists($media->path);
    }
}

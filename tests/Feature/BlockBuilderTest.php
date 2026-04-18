<?php

namespace Tests\Feature;

use App\Models\Site;
use App\Models\Template;
use App\Models\Theme;
use App\Models\User;
use App\Services\SettingService;
use Database\Seeders\CatalogSeeder;
use Database\Seeders\SettingsSeeder;
use Database\Seeders\TemplateSeeder;
use Database\Seeders\ThemeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BlockBuilderTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(TemplateSeeder::class);
        $this->seed(CatalogSeeder::class);
        $this->seed(ThemeSeeder::class);
        $this->seed(SettingsSeeder::class);
        SettingService::flush();
        $this->user = User::factory()->create();
    }

    private function makeSiteWithBlocks(array $blocks = [], bool $published = true): Site
    {
        return Site::create([
            'user_id' => $this->user->id,
            'template_id' => Template::first()->id,
            'slug' => 'block-test-' . uniqid(),
            'content' => Template::first()->default_content,
            'blocks' => $blocks ?: [
                ['id' => 'b1', 'type' => 'text', 'order' => 0, 'content' => [
                    'heading' => ['vi' => 'Tiêu đề test', 'en' => 'Test heading'],
                    'body' => ['vi' => 'Nội dung test', 'en' => 'Test body'],
                ]],
                ['id' => 'b2', 'type' => 'cta', 'order' => 1, 'content' => [
                    'title' => ['vi' => 'Hành động', 'en' => 'Take Action'],
                    'description' => ['vi' => 'Mô tả', 'en' => 'Description'],
                    'button_label' => ['vi' => 'Click', 'en' => 'Click'],
                    'button_url' => '#',
                ]],
            ],
            'published' => $published,
        ]);
    }

    // ── Block rendering ──

    public function test_site_with_blocks_renders_from_blocks(): void
    {
        $site = $this->makeSiteWithBlocks();

        $this->get("/vi/site/{$site->slug}")
            ->assertStatus(200)
            ->assertSee('Tiêu đề test', false)
            ->assertSee('Hành động', false);
    }

    public function test_site_without_blocks_falls_back_to_template(): void
    {
        $site = Site::create([
            'user_id' => $this->user->id,
            'template_id' => Template::first()->id,
            'slug' => 'legacy-test',
            'content' => Template::first()->default_content,
            'blocks' => null,
            'published' => true,
        ]);

        $response = $this->get("/vi/site/{$site->slug}");
        $response->assertStatus(200);
        // Should render from template view, not blocks
    }

    // ── Block editor ──

    public function test_block_editor_loads_with_blocks_json(): void
    {
        $site = $this->makeSiteWithBlocks();

        $this->actingAs($this->user)
            ->get("/admin/sites/{$site->id}/editor")
            ->assertStatus(200)
            ->assertSee('blockEditor', false)
            ->assertSee('Sortable', false);
    }

    public function test_block_editor_saves_blocks_json(): void
    {
        $site = $this->makeSiteWithBlocks();
        $newBlocks = [
            ['id' => 'new1', 'type' => 'spacer', 'order' => 0, 'content' => ['height' => 100]],
        ];

        $this->actingAs($this->user)
            ->putJson("/admin/sites/{$site->id}/blocks", ['blocks' => $newBlocks])
            ->assertJson(['success' => true]);

        $fresh = $site->fresh();
        $this->assertCount(1, $fresh->blocks);
        $this->assertSame('spacer', $fresh->blocks[0]['type']);
        $this->assertSame(100, $fresh->blocks[0]['content']['height']);
    }

    public function test_block_editor_upload_image(): void
    {
        Storage::fake('public');
        $site = $this->makeSiteWithBlocks();

        $this->actingAs($this->user)
            ->post("/admin/sites/{$site->id}/blocks/upload", [
                'image' => UploadedFile::fake()->image('block.jpg', 800, 600),
            ])
            ->assertStatus(200)
            ->assertJsonStructure(['url']);
    }

    // ── Products block ──

    public function test_products_block_renders_catalog_products(): void
    {
        $category = \App\Models\Category::first();
        $site = $this->makeSiteWithBlocks([
            ['id' => 'p1', 'type' => 'products', 'order' => 0, 'content' => [
                'heading' => ['vi' => 'Sản phẩm', 'en' => 'Products'],
                'category_id' => $category->id,
                'count' => 2,
            ]],
        ]);

        $this->get("/vi/site/{$site->slug}")
            ->assertStatus(200)
            ->assertSee('Sản phẩm', false);
    }

    // ── Theme clone ──

    public function test_theme_clone_on_site_create(): void
    {
        $theme = Theme::where('slug', 'landing-product')->first();

        $this->actingAs($this->user)
            ->post('/admin/sites', [
                'theme_id' => $theme->id,
                'slug' => 'theme-clone-test',
            ])
            ->assertRedirect();

        $site = Site::where('slug', 'theme-clone-test')->first();
        $this->assertNotNull($site);
        $this->assertNotNull($site->blocks);
        $this->assertSame($theme->id, $site->theme_id);
        $this->assertCount(count($theme->blocks_preset), $site->blocks);
    }

    // ── Settings ──

    public function test_settings_crud(): void
    {
        $this->actingAs($this->user)
            ->get('/admin/settings')
            ->assertStatus(200);

        $this->actingAs($this->user)
            ->post('/admin/settings', [
                'site_name' => ['vi' => 'Tên mới', 'en' => 'New Name'],
                'email' => 'new@example.com',
            ])
            ->assertRedirect(route('admin.settings.edit'));

        SettingService::flush();
        $this->assertSame('new@example.com', SettingService::get('email'));
        $this->assertSame('Tên mới', data_get(SettingService::get('site_name'), 'vi'));
    }

    public function test_setting_service_get_set(): void
    {
        SettingService::set('test_key', 'test_value');
        SettingService::flush();

        $this->assertSame('test_value', SettingService::get('test_key'));
        $this->assertNull(SettingService::get('nonexistent'));
        $this->assertSame('default', SettingService::get('nonexistent', 'default'));
    }
}

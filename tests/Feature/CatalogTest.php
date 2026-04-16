<?php

namespace Tests\Feature;

use App\Models\Attribute;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Database\Seeders\CatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CatalogTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CatalogSeeder::class);
        $this->user = User::factory()->create();
    }

    // ── Admin Category ──

    public function test_admin_category_create(): void
    {
        $this->actingAs($this->user)->post('/admin/categories', [
            'name' => ['vi' => 'Phụ kiện', 'en' => 'Accessories'],
            'sort_order' => 5,
            'status' => 'active',
        ])->assertRedirect(route('admin.categories.index'));

        $this->assertDatabaseHas('categories', ['slug' => 'phu-kien']);
    }

    public function test_admin_category_update(): void
    {
        $cat = Category::where('slug', 'ao-thun')->firstOrFail();

        $this->actingAs($this->user)->put('/admin/categories/' . $cat->id, [
            'name' => ['vi' => 'Áo thun Updated', 'en' => 'T-shirt Updated'],
            'sort_order' => 1,
            'status' => 'active',
        ])->assertRedirect(route('admin.categories.index'));

        $this->assertSame('Áo thun Updated', $cat->fresh()->name['vi']);
    }

    public function test_admin_category_delete_blocked_when_has_products(): void
    {
        $cat = Category::where('slug', 'ao-thun')->firstOrFail();

        $this->actingAs($this->user)
            ->delete('/admin/categories/' . $cat->id)
            ->assertRedirect(route('admin.categories.index'));

        $this->assertDatabaseHas('categories', ['id' => $cat->id]);
    }

    // ── Admin Attribute ──

    public function test_admin_attribute_create_with_values(): void
    {
        $this->actingAs($this->user)->post('/admin/attributes', [
            'name' => ['vi' => 'Chất liệu', 'en' => 'Material'],
            'type' => 'select',
            'values' => [
                ['vi' => 'Cotton', 'en' => 'Cotton', 'sort_order' => 1],
                ['vi' => 'Polyester', 'en' => 'Polyester', 'sort_order' => 2],
            ],
        ])->assertRedirect(route('admin.attributes.index'));

        $attr = Attribute::where('name->vi', 'Chất liệu')->first();
        $this->assertNotNull($attr);
        $this->assertSame('Chất liệu', $attr->name['vi']);
        $this->assertCount(2, $attr->values);
    }

    public function test_admin_attribute_delete_blocked_when_used(): void
    {
        $attr = Attribute::first();

        $this->actingAs($this->user)
            ->delete('/admin/attributes/' . $attr->id)
            ->assertRedirect(route('admin.attributes.index'));

        $this->assertDatabaseHas('attributes', ['id' => $attr->id]);
    }

    // ── Admin Product ──

    public function test_admin_product_create(): void
    {
        $cat = Category::first();

        $this->actingAs($this->user)->post('/admin/products', [
            'name' => ['vi' => 'Sản phẩm mới', 'en' => 'New Product'],
            'category_id' => $cat->id,
            'base_price' => 99000,
            'status' => 'draft',
        ])->assertRedirect();

        $this->assertDatabaseHas('products', ['slug' => 'san-pham-moi']);
    }

    public function test_admin_product_generate_variants(): void
    {
        $product = Product::where('slug', 'ao-thun-co-v')->firstOrFail();
        $attrs = Attribute::all();
        $product->attributes()->sync($attrs->pluck('id'));

        // Delete existing variants to test clean generation
        $product->variants()->delete();
        $this->assertSame(0, $product->variants()->count());

        $this->actingAs($this->user)
            ->post('/admin/products/' . $product->id . '/variants/generate')
            ->assertRedirect();

        $sizeAttr = Attribute::where('name->vi', 'Kích thước')->first();
        $colorAttr = Attribute::where('name->vi', 'Màu sắc')->first();
        $expected = $sizeAttr->values()->count() * $colorAttr->values()->count();

        $this->assertSame($expected, $product->variants()->count());
    }

    public function test_admin_product_upload_image(): void
    {
        Storage::fake('public');
        $product = Product::first();

        $this->actingAs($this->user)->post(
            '/admin/products/' . $product->id . '/images',
            ['image' => UploadedFile::fake()->image('test.jpg', 400, 300)]
        )->assertStatus(200)->assertJsonStructure(['id', 'url']);

        $this->assertDatabaseHas('product_images', ['product_id' => $product->id]);
    }

    // ── Public Listing ──

    public function test_public_listing_returns_200(): void
    {
        $this->get('/vi/products')->assertStatus(200);
    }

    public function test_public_listing_shows_active_products(): void
    {
        $product = Product::active()->first();

        $this->get('/vi/products')
            ->assertStatus(200)
            ->assertSee($product->name['vi'], false);
    }

    public function test_public_category_filter(): void
    {
        $cat = Category::where('slug', 'ao-thun')->firstOrFail();

        $this->get('/vi/category/ao-thun')
            ->assertStatus(200)
            ->assertSee($cat->name['vi'], false);
    }

    public function test_public_category_404_for_invalid_slug(): void
    {
        $this->get('/vi/category/nonexistent')->assertStatus(404);
    }

    // ── Public Detail ──

    public function test_public_detail_returns_200(): void
    {
        $product = Product::active()->first();

        $this->get('/vi/product/' . $product->slug)
            ->assertStatus(200)
            ->assertSee($product->name['vi'], false);
    }

    public function test_public_detail_404_for_draft_product(): void
    {
        $product = Product::first();
        $product->update(['status' => 'draft']);

        $this->get('/vi/product/' . $product->slug)->assertStatus(404);
    }

    // ── API Variant Price ──

    public function test_api_variant_price_returns_correct_data(): void
    {
        $variant = ProductVariant::with('attributeValues')->active()->first();
        $valueIds = $variant->attributeValues->pluck('id')->all();

        $params = implode('&', array_map(fn ($id) => "values[]=$id", $valueIds));

        $this->get("/api/product/{$variant->product_id}/variant-price?$params")
            ->assertStatus(200)
            ->assertJsonStructure(['variant_id', 'sku', 'raw_price', 'price', 'stock', 'in_stock']);
    }

    public function test_api_variant_price_404_for_invalid_combo(): void
    {
        $product = Product::first();

        $this->get("/api/product/{$product->id}/variant-price?values[]=99999")
            ->assertStatus(404);
    }

    public function test_variant_price_fallback_to_base_price(): void
    {
        $variant = ProductVariant::active()->whereNull('price')->first();

        if (!$variant) {
            $this->markTestSkipped('No variant with null price in seed data');
        }

        $effectivePrice = $variant->getEffectivePrice();

        $this->assertSame((float) $variant->product->base_price, $effectivePrice);
    }
}

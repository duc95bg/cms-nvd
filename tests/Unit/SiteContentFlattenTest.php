<?php

namespace Tests\Unit;

use App\Support\FlattenContent;
use PHPUnit\Framework\TestCase;

class SiteContentFlattenTest extends TestCase
{
    private array $locales = ['en', 'vi'];

    public function test_scalar_at_root_returns_the_key(): void
    {
        $result = FlattenContent::flatten(['title' => 'hello'], $this->locales);

        $this->assertSame(['title' => 'hello'], $result);
    }

    public function test_translatable_leaf_is_preserved_as_single_entry(): void
    {
        $data = [
            'hero' => [
                'title' => ['en' => 'x', 'vi' => 'y'],
            ],
        ];

        $result = FlattenContent::flatten($data, $this->locales);

        $this->assertSame(['hero.title' => ['en' => 'x', 'vi' => 'y']], $result);
    }

    public function test_nested_group_with_mixed_leaves_flattens_recursively(): void
    {
        $data = [
            'hero' => [
                'title' => ['en' => 'x', 'vi' => 'y'],
                'cta_url' => '#features',
            ],
        ];

        $result = FlattenContent::flatten($data, $this->locales);

        $this->assertSame([
            'hero.title' => ['en' => 'x', 'vi' => 'y'],
            'hero.cta_url' => '#features',
        ], $result);
    }

    public function test_indexed_list_is_preserved_as_leaf(): void
    {
        $data = [
            'features' => [
                'items' => [
                    ['title' => ['en' => 'A', 'vi' => 'a']],
                    ['title' => ['en' => 'B', 'vi' => 'b']],
                ],
            ],
        ];

        $result = FlattenContent::flatten($data, $this->locales);

        $this->assertArrayHasKey('features.items', $result);
        $this->assertCount(2, $result['features.items']);
        $this->assertSame(['en' => 'A', 'vi' => 'a'], $result['features.items'][0]['title']);
    }
}

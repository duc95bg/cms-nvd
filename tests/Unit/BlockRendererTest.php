<?php

namespace Tests\Unit;

use App\Services\BlockRenderer;
use Tests\TestCase;

class BlockRendererTest extends TestCase
{
    public function test_renders_known_block_types(): void
    {
        $blocks = [
            ['id' => 'b1', 'type' => 'spacer', 'order' => 0, 'content' => ['height' => 32]],
            ['id' => 'b2', 'type' => 'cta', 'order' => 1, 'content' => [
                'title' => ['vi' => 'CTA Title VI', 'en' => 'CTA Title EN'],
                'description' => ['vi' => 'Desc', 'en' => 'Desc'],
                'button_label' => ['vi' => 'Click', 'en' => 'Click'],
                'button_url' => '#test',
            ]],
        ];

        $html = BlockRenderer::render($blocks);

        $this->assertStringContainsString('height: 32px', $html);
        // Block renders based on current locale; assert either vi or en title present
        $this->assertTrue(
            str_contains($html, 'CTA Title VI') || str_contains($html, 'CTA Title EN'),
            'CTA block should render title in current locale'
        );
    }

    public function test_skips_unknown_block_type(): void
    {
        $blocks = [
            ['id' => 'b1', 'type' => 'nonexistent_type', 'order' => 0, 'content' => []],
            ['id' => 'b2', 'type' => 'spacer', 'order' => 1, 'content' => ['height' => 10]],
        ];

        $html = BlockRenderer::render($blocks);

        $this->assertStringContainsString('height: 10px', $html);
        $this->assertStringNotContainsString('nonexistent_type', $html);
    }

    public function test_renders_empty_blocks_array(): void
    {
        $html = BlockRenderer::render([]);

        $this->assertSame('', $html);
    }
}

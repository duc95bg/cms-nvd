<?php

namespace App\Services;

class BlockRenderer
{
    /**
     * Render an ordered array of blocks into HTML.
     * Each block: {id, type, order, content}.
     * Renders blocks/{type}.blade.php partial for each, skipping unknown types.
     */
    public static function render(array $blocks): string
    {
        $html = '';

        foreach ($blocks as $block) {
            $type = $block['type'] ?? null;

            if (!$type) {
                continue;
            }

            $view = "blocks.{$type}";

            if (!view()->exists($view)) {
                continue;
            }

            $html .= view($view, ['block' => $block['content'] ?? []])->render();
        }

        return $html;
    }
}

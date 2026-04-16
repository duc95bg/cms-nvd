<?php

namespace Tests\Unit;

use App\Traits\HasLocalizedContent;
use Tests\TestCase;

class HasLocalizedContentTest extends TestCase
{
    private function makeModel(array $attributes): object
    {
        return new class($attributes) {
            use HasLocalizedContent;

            public array $name;

            public function __construct(array $attrs)
            {
                $this->name = $attrs['name'] ?? [];
            }
        };
    }

    public function test_returns_correct_locale(): void
    {
        $model = $this->makeModel(['name' => ['en' => 'Hello', 'vi' => 'Xin chào']]);

        // HasLocalizedContent::t() calls app()->getLocale() which isn't available
        // in a pure unit test. Test the explicit locale parameter instead.
        $this->assertSame('Hello', $model->t('name', 'en'));
        $this->assertSame('Xin chào', $model->t('name', 'vi'));
    }

    public function test_fallback_to_default_string(): void
    {
        $model = $this->makeModel(['name' => []]);

        $this->assertSame('fallback', $model->t('name', 'en', 'fallback'));
    }

    public function test_returns_empty_string_when_no_match_and_no_default(): void
    {
        $model = $this->makeModel(['name' => ['vi' => 'Xin chào']]);

        // 'ja' not present, no fallback locale available in pure unit test
        $this->assertSame('', $model->t('name', 'ja'));
    }
}

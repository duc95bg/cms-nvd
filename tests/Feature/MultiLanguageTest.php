<?php

namespace Tests\Feature;

use Tests\TestCase;

class MultiLanguageTest extends TestCase
{
    public function test_english_home_renders_english_welcome(): void
    {
        $response = $this->get('/en');

        $response->assertStatus(200);
        $response->assertSee('Welcome to CMS');
    }

    public function test_vietnamese_home_renders_vietnamese_welcome(): void
    {
        $response = $this->get('/vi');

        $response->assertStatus(200);
        $response->assertSee('Chào mừng đến với CMS', false);
    }

    public function test_session_locale_persists_across_requests(): void
    {
        $this->get('/en')->assertStatus(200);

        $this->get('/')->assertRedirect('/en');
    }

    public function test_default_locale_is_vietnamese_when_no_session(): void
    {
        $response = $this->get('/');

        $response->assertRedirect('/vi');
    }
}

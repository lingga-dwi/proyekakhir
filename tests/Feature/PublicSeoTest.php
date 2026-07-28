<?php

namespace Tests\Feature;

use Tests\TestCase;

class PublicSeoTest extends TestCase
{
    public function test_public_homepage_contains_core_seo_metadata(): void
    {
        $response = $this->get(route('home'));

        $response
            ->assertOk()
            ->assertSee('name="description"', false)
            ->assertSee('rel="canonical"', false)
            ->assertSee('property="og:title"', false)
            ->assertSee('Bawa ukuran, denah, atau foto ruang Anda.')
            ->assertSee('https://wa.me/6282186888824', false);
    }

    public function test_sitemap_is_available_as_xml(): void
    {
        $response = $this->get(route('sitemap'));

        $response
            ->assertOk()
            ->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
            ->assertSee('<urlset', false)
            ->assertSee(route('katalog'), false);
    }

    public function test_about_page_is_publicly_available(): void
    {
        $this->get(route('about'))
            ->assertOk()
            ->assertSee('Tentang Daiku')
            ->assertSee('Pekanbaru');
    }

    public function test_consultation_page_offers_web_form_and_whatsapp_paths(): void
    {
        $this->get(route('konsultasi.index'))
            ->assertOk()
            ->assertSee('Daftar untuk Konsultasi')
            ->assertSee('https://wa.me/6285805908809', false)
            ->assertSee('Chat WhatsApp');
    }
}

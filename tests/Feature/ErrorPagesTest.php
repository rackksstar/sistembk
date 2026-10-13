<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ErrorPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_not_found_request_displays_custom_404_page(): void
    {
        $this->get('/halaman-yang-tidak-ada')
            ->assertNotFound()
            ->assertSee('404')
            ->assertSee('Halaman Tidak Ditemukan')
            ->assertSee('Kembali')
            ->assertSee('Ke Halaman Masuk');
    }

    public function test_common_error_pages_render_with_navigation(): void
    {
        foreach (['400', '401', '405', '419', '429', '500', '502', '503', '504'] as $status) {
            $this->view("errors.{$status}")
                ->assertSee($status)
                ->assertSee('Kembali')
                ->assertSee('Ke Halaman Masuk');
        }
    }
}

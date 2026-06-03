<?php

namespace Tests\Feature\Phase6;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class Phase6IntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_rute_modul_tim_masih_ada_bersama_tryout(): void
    {
        $this->assertTrue(Route::has('guru.rpls.index'));
        $this->assertTrue(Route::has('siswa.instruments.index'));
        $this->assertTrue(Route::has('guru.tryout.index'));
        $this->assertTrue(Route::has('siswa.tryout.index'));
    }
}

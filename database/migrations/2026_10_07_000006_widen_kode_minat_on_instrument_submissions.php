<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('instrument_submissions', function (Blueprint $table) {
            // Kode kategori max 5 × top-3 = 15; sediakan ruang aman.
            $table->string('kode_minat', 20)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('instrument_submissions', function (Blueprint $table) {
            $table->string('kode_minat', 10)->nullable()->change();
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sosiometry_instruments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sekolah_id')->nullable();
            $table->unsignedBigInteger('kelas_id')->nullable();
            $table->boolean('is_active')->default(false);
            $table->timestamps();

            $table->index('sekolah_id');
            $table->index('kelas_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sosiometry_instruments');
    }
};

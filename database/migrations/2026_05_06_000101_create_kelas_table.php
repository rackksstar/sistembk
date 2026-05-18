<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kelas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sekolah_id')->constrained('sekolahs')->cascadeOnDelete();
            $table->string('nama');
            $table->string('jenjang', 40)->nullable();
            $table->string('tingkatan', 40)->nullable();
            $table->timestamps();

            $table->unique(['sekolah_id', 'nama']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kelas');
    }
};


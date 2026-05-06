<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guru_bks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('sekolah_id')->nullable()->constrained('sekolahs')->nullOnDelete();
            $table->string('nip', 40)->nullable();
            $table->string('jabatan', 120)->nullable();
            $table->string('bidang_studi', 120)->nullable();
            $table->timestamps();

            $table->unique('user_id');
            $table->unique(['sekolah_id', 'nip']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guru_bks');
    }
};


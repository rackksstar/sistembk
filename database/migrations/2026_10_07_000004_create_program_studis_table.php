<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('program_studis', function (Blueprint $table) {
            $table->id();
            $table->string('institusi', 150)->default('Politeknik Caltex Riau');
            $table->string('nama', 150);
            $table->string('jenjang_pendidikan', 10);
            $table->string('jurusan', 150)->nullable();
            $table->text('deskripsi')->nullable();
            $table->text('prospek_karier')->nullable();
            $table->string('website_url')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['institusi', 'nama', 'jenjang_pendidikan'], 'program_studis_unique');
        });

        Schema::create('interest_category_program_studi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('interest_category_id')->constrained('interest_categories')->cascadeOnDelete();
            $table->foreignId('program_studi_id')->constrained('program_studis')->cascadeOnDelete();
            $table->unsignedTinyInteger('relevansi')->default(2);
            $table->timestamps();

            $table->unique(['interest_category_id', 'program_studi_id'], 'ic_ps_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('interest_category_program_studi');
        Schema::dropIfExists('program_studis');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sociometry_questions', function (Blueprint $table) {
            $table->id();
            $table->text('pertanyaan');
            $table->string('jenis'); // positif | negatif
            $table->string('aspek'); // pertemanan | organisasi | belajar | karier
            $table->unsignedInteger('urutan')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['jenis', 'aspek']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sociometry_questions');
    }
};

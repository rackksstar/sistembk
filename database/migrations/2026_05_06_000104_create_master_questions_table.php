<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('master_questions', function (Blueprint $table) {
            $table->id();
            $table->string('kategori', 20)->index();
            $table->text('teks_pertanyaan');
            $table->string('tipe_input', 20)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('master_questions');
    }
};


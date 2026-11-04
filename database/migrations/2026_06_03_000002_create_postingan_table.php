<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('postingan', function (Blueprint $table) {
            $table->id();

            $table->foreignId('post_category_id')
                ->constrained('post_categories')
                ->cascadeOnDelete();

            $table->string('judul');
            $table->string('slug')->unique();
            $table->text('isi');
            $table->string('gambar_path')->nullable();
            $table->string('status', 20)->default('draft');

            $table->timestamps();

            $table->index(['status', 'created_at'], 'idx_postingan_publik');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('postingan');
    }
};

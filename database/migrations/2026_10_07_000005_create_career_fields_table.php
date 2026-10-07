<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('career_fields', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 150);
            $table->text('deskripsi')->nullable();
            $table->json('contoh_pekerjaan')->nullable();
            $table->unsignedTinyInteger('job_zone')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('career_field_interest_category', function (Blueprint $table) {
            $table->id();
            $table->foreignId('interest_category_id')->constrained('interest_categories')->cascadeOnDelete();
            $table->foreignId('career_field_id')->constrained('career_fields')->cascadeOnDelete();
            $table->unsignedTinyInteger('relevansi')->default(2);
            $table->timestamps();

            $table->unique(['interest_category_id', 'career_field_id'], 'cf_ic_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('career_field_interest_category');
        Schema::dropIfExists('career_fields');
    }
};

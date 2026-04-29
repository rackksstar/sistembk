<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guidance_classes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('guidance_class_student', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guidance_class_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['guidance_class_id', 'student_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guidance_class_student');
        Schema::dropIfExists('guidance_classes');
    }
};

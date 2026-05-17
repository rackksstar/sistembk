<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rpls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->string('type');
            $table->date('service_date')->nullable();
            $table->string('target')->nullable();
            $table->text('tujuan');
            $table->text('materi');
            $table->text('metode');
            $table->text('evaluasi');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rpls');
    }
};

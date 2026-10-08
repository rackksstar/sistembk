<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('group_consultation_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('rpl_id')->constrained('rpls')->cascadeOnDelete();
            $table->foreignId('class_id')->nullable()->constrained('classes')->nullOnDelete();
            $table->string('title');
            $table->date('service_date');
            $table->unsignedSmallInteger('duration_minutes')->nullable();
            $table->string('location')->nullable();
            $table->string('case_category')->nullable()->index();
            $table->text('result');
            $table->text('evaluation');
            $table->text('follow_up')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('group_consultation_reports');
    }
};

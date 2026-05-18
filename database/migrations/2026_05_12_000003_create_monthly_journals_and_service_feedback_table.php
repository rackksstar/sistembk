<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('monthly_journals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedTinyInteger('month');
            $table->unsignedSmallInteger('year');
            $table->string('title');
            $table->unsignedInteger('individual_services')->default(0);
            $table->unsignedInteger('group_services')->default(0);
            $table->unsignedInteger('classical_services')->default(0);
            $table->text('summary');
            $table->text('evaluation')->nullable();
            $table->text('follow_up')->nullable();
            $table->timestamps();
            $table->unique(['teacher_id', 'month', 'year']);
        });

        Schema::create('service_feedback', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('consultation_request_id')->nullable()->constrained()->nullOnDelete();
            $table->string('service_type');
            $table->unsignedTinyInteger('rating');
            $table->text('message');
            $table->text('suggestion')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_feedback');
        Schema::dropIfExists('monthly_journals');
    }
};

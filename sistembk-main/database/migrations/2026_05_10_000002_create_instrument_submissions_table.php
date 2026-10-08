<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('instrument_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->string('category');
            $table->unsignedInteger('total_score')->default(0);
            $table->string('result_label')->nullable();
            $table->text('result_description')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();
        });

        Schema::create('instrument_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('instrument_submission_id')->constrained()->cascadeOnDelete();
            $table->foreignId('instrument_question_id')->constrained()->cascadeOnDelete();
            $table->string('answer_label');
            $table->unsignedInteger('score')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('instrument_answers');
        Schema::dropIfExists('instrument_submissions');
    }
};

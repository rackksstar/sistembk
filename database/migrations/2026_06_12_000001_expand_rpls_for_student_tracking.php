<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rpls', function (Blueprint $table) {
            $table->foreignId('class_id')->nullable()->after('teacher_id')->constrained('classes')->nullOnDelete();
            $table->foreignId('student_id')->nullable()->after('class_id')->constrained('users')->nullOnDelete();
            $table->string('status')->default('aktif')->after('type');
            $table->unsignedTinyInteger('semester')->nullable()->after('status');
            $table->unsignedSmallInteger('year')->nullable()->after('semester');
        });

        Schema::create('rpl_student', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rpl_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['rpl_id', 'student_id']);
        });

        Schema::table('consultation_requests', function (Blueprint $table) {
            $table->foreignId('rpl_id')->nullable()->after('counselor_id')->constrained('rpls')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('consultation_requests', function (Blueprint $table) {
            $table->dropConstrainedForeignId('rpl_id');
        });

        Schema::dropIfExists('rpl_student');

        Schema::table('rpls', function (Blueprint $table) {
            $table->dropConstrainedForeignId('student_id');
            $table->dropConstrainedForeignId('class_id');
            $table->dropColumn(['status', 'semester', 'year']);
        });
    }
};

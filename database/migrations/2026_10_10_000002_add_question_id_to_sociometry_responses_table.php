<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sociometry_responses', function (Blueprint $table) {
            $table->foreignId('sociometry_question_id')
                ->nullable()
                ->after('relation_type')
                ->constrained('sociometry_questions')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('sociometry_responses', function (Blueprint $table) {
            $table->dropConstrainedForeignId('sociometry_question_id');
        });
    }
};

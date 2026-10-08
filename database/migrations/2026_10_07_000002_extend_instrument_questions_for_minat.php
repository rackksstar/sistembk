<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('instrument_questions', function (Blueprint $table) {
            $table->foreignId('interest_category_id')
                ->nullable()
                ->after('category')
                ->constrained('interest_categories')
                ->restrictOnDelete();
            $table->string('jenjang_target', 10)->default('semua')->after('interest_category_id');
            $table->unsignedTinyInteger('bobot')->default(1)->after('jenjang_target');
            $table->softDeletes();
            $table->index(['category', 'is_active', 'interest_category_id'], 'instrument_questions_minat_idx');
        });
    }

    public function down(): void
    {
        Schema::table('instrument_questions', function (Blueprint $table) {
            $table->dropIndex('instrument_questions_minat_idx');
            $table->dropConstrainedForeignId('interest_category_id');
            $table->dropColumn(['jenjang_target', 'bobot', 'deleted_at']);
        });
    }
};

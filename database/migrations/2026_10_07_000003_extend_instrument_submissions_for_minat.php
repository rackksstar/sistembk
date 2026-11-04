<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('instrument_submissions', function (Blueprint $table) {
            $table->string('jenjang', 10)->nullable()->after('category');
            $table->string('kode_minat', 10)->nullable()->after('jenjang');
            $table->json('category_scores')->nullable()->after('kode_minat');
            $table->foreignId('dominant_interest_id')
                ->nullable()
                ->after('category_scores')
                ->constrained('interest_categories')
                ->nullOnDelete();
            $table->foreignId('secondary_interest_id')
                ->nullable()
                ->after('dominant_interest_id')
                ->constrained('interest_categories')
                ->nullOnDelete();
            $table->boolean('is_tied')->default(false)->after('secondary_interest_id');
        });
    }

    public function down(): void
    {
        Schema::table('instrument_submissions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('dominant_interest_id');
            $table->dropConstrainedForeignId('secondary_interest_id');
            $table->dropColumn(['jenjang', 'kode_minat', 'category_scores', 'is_tied']);
        });
    }
};

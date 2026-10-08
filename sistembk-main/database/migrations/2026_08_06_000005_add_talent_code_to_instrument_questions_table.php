<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('instrument_questions', function (Blueprint $table) {
            $table->char('talent_code', 1)->nullable()->after('section');
        });
    }

    public function down(): void
    {
        Schema::table('instrument_questions', function (Blueprint $table) {
            $table->dropColumn('talent_code');
        });
    }
};

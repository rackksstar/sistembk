<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('instrument_submissions', function (Blueprint $table) {
            $table->decimal('percentage', 5, 2)->default(0)->after('total_score');
        });
    }

    public function down(): void
    {
        Schema::table('instrument_submissions', function (Blueprint $table) {
            $table->dropColumn('percentage');
        });
    }
};

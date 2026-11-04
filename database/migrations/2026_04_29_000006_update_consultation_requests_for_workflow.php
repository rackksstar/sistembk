<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('consultation_requests', function (Blueprint $table) {
            $table->date('consultation_date')->nullable()->after('preferred_time');
            $table->time('consultation_time')->nullable()->after('consultation_date');
            $table->text('result')->nullable()->after('notes');
            $table->text('evaluation')->nullable()->after('result');
        });

        DB::table('consultation_requests')->where('status', 'menunggu')->update(['status' => 'pending']);
        DB::table('consultation_requests')->where('status', 'dijadwalkan')->update(['status' => 'disetujui']);
    }

    public function down(): void
    {
        Schema::table('consultation_requests', function (Blueprint $table) {
            $table->dropColumn(['consultation_date', 'consultation_time', 'result', 'evaluation']);
        });

        DB::table('consultation_requests')->where('status', 'pending')->update(['status' => 'menunggu']);
        DB::table('consultation_requests')->where('status', 'disetujui')->update(['status' => 'dijadwalkan']);
    }
};

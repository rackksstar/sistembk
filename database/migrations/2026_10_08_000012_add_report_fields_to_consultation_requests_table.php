<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Kolom pelaksanaan/pelaporan laporan konseling: pertemuan, durasi, tempat,
 * pendekatan & teknik, semester, dan tahun pelajaran. Semua nullable supaya
 * pengajuan lama tetap valid, lalu diisi guru saat mengisi laporan.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('consultation_requests', function (Blueprint $table) {
            $table->unsignedSmallInteger('meeting_number')->nullable()->after('notes');
            $table->unsignedSmallInteger('duration_minutes')->nullable()->after('meeting_number');
            $table->string('location')->nullable()->after('duration_minutes');
            $table->text('approach_technique')->nullable()->after('location');
            $table->unsignedTinyInteger('semester')->nullable()->after('approach_technique');
            $table->unsignedSmallInteger('year')->nullable()->after('semester');
        });

        // Laporan lama: semester/tahun disimpulkan dari tanggal konseling,
        // sisanya dibiarkan null (diisi ulang oleh guru saat edit laporan).
        $rows = DB::table('consultation_requests')
            ->select('id', 'consultation_date')
            ->whereNull('semester')
            ->whereNotNull('consultation_date')
            ->get();

        foreach ($rows as $row) {
            $date = \Illuminate\Support\Carbon::parse($row->consultation_date);

            DB::table('consultation_requests')->where('id', $row->id)->update([
                'semester' => $date->month >= 7 ? 1 : 2,
                'year' => $date->year,
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('consultation_requests', function (Blueprint $table) {
            $table->dropColumn([
                'meeting_number',
                'duration_minutes',
                'location',
                'approach_technique',
                'semester',
                'year',
            ]);
        });
    }
};

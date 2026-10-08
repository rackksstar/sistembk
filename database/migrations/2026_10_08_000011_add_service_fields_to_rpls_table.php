<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Kolom pelaksanaan RPL — dipakai di form guru dan dokumen cetak resmi
 * (pertemuan ke berapa, durasi, tempat, topik permasalahan, media).
 * Khusus untuk RPL kelompok field ini wajib diisi (dicek di RplController).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rpls', function (Blueprint $table) {
            $table->unsignedSmallInteger('meeting_number')->nullable()->after('service_date');
            $table->unsignedSmallInteger('duration_minutes')->nullable()->after('meeting_number');
            $table->string('topik_permasalahan')->nullable()->after('duration_minutes');
            $table->string('location')->nullable()->after('topik_permasalahan');
            $table->text('media')->nullable()->after('location');
        });

        // RPL kelompok lama belum punya topik — diisi dari judulnya supaya
        // daftar & cetak tetap tampil wajar.
        \Illuminate\Support\Facades\DB::table('rpls')
            ->where('type', 'kelompok')
            ->whereNull('topik_permasalahan')
            ->update(['topik_permasalahan' => \Illuminate\Support\Facades\DB::raw('title')]);
    }

    public function down(): void
    {
        Schema::table('rpls', function (Blueprint $table) {
            $table->dropColumn([
                'meeting_number',
                'duration_minutes',
                'topik_permasalahan',
                'location',
                'media',
            ]);
        });
    }
};

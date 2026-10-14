<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('master_questions', function (Blueprint $table) {
            if (! Schema::hasColumn('master_questions', 'opsi')) {
                $table->json('opsi')->nullable()->after('tipe_input');
            }
            if (! Schema::hasColumn('master_questions', 'kunci_jawaban')) {
                $table->string('kunci_jawaban', 255)->nullable()->after('opsi');
            }
        });

        Schema::table('try_outs', function (Blueprint $table) {
            if (! Schema::hasColumn('try_outs', 'hasil_dipublikasikan')) {
                $table->boolean('hasil_dipublikasikan')->default(false)->after('status');
            }
            if (! Schema::hasColumn('try_outs', 'hasil_dipublikasikan_at')) {
                $table->timestamp('hasil_dipublikasikan_at')->nullable()->after('hasil_dipublikasikan');
            }
        });

        Schema::table('try_out_detail', function (Blueprint $table) {
            if (! Schema::hasColumn('try_out_detail', 'skor_per_soal')) {
                $table->json('skor_per_soal')->nullable()->after('jawaban');
            }
        });
    }

    public function down(): void
    {
        Schema::table('try_out_detail', function (Blueprint $table) {
            if (Schema::hasColumn('try_out_detail', 'skor_per_soal')) {
                $table->dropColumn('skor_per_soal');
            }
        });

        Schema::table('try_outs', function (Blueprint $table) {
            if (Schema::hasColumn('try_outs', 'hasil_dipublikasikan_at')) {
                $table->dropColumn('hasil_dipublikasikan_at');
            }
            if (Schema::hasColumn('try_outs', 'hasil_dipublikasikan')) {
                $table->dropColumn('hasil_dipublikasikan');
            }
        });

        Schema::table('master_questions', function (Blueprint $table) {
            if (Schema::hasColumn('master_questions', 'kunci_jawaban')) {
                $table->dropColumn('kunci_jawaban');
            }
            if (Schema::hasColumn('master_questions', 'opsi')) {
                $table->dropColumn('opsi');
            }
        });
    }
};

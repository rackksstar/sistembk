<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sekolahs', function (Blueprint $table) {
            if (! Schema::hasColumn('sekolahs', 'npsn')) {
                $table->string('npsn', 20)->nullable()->unique()->after('nama');
            }

            if (! Schema::hasColumn('sekolahs', 'alamat')) {
                $table->text('alamat')->nullable()->after('npsn');
            }

            if (! Schema::hasColumn('sekolahs', 'logo_path')) {
                $table->string('logo_path')->nullable()->after('alamat');
            }

            if (! Schema::hasColumn('sekolahs', 'is_mou')) {
                $table->boolean('is_mou')->default(false)->index()->after('logo_path');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'username')) {
                $table->string('username')->nullable()->unique()->after('name');
            }
        });

        Schema::table('guru_bks', function (Blueprint $table) {
            if (! Schema::hasColumn('guru_bks', 'no_hp')) {
                $table->string('no_hp', 30)->nullable()->unique()->after('sekolah_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('guru_bks', function (Blueprint $table) {
            if (Schema::hasColumn('guru_bks', 'no_hp')) {
                $table->dropColumn('no_hp');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'username')) {
                $table->dropColumn('username');
            }
        });

        Schema::table('sekolahs', function (Blueprint $table) {
            foreach (['is_mou', 'logo_path', 'alamat', 'npsn'] as $column) {
                if (Schema::hasColumn('sekolahs', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};

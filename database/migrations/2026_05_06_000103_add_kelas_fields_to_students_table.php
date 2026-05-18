<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->foreignId('kelas_id')->nullable()->after('user_id')->constrained('kelas')->nullOnDelete();
            $table->string('jenis_kelamin', 20)->nullable()->after('birth_date');
            $table->text('alamat')->nullable()->after('jenis_kelamin');
            $table->string('status_biodata', 40)->default('belum_lengkap')->index()->after('alamat');
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropConstrainedForeignId('kelas_id');
            $table->dropColumn(['jenis_kelamin', 'alamat', 'status_biodata']);
        });
    }
};


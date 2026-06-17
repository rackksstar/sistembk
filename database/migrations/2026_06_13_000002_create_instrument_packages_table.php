<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('instrument_packages', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nama paket: "Asesmen Minat Bakat Kelas X"
            $table->text('description')->nullable();
            $table->enum('category', [
                'minat_bakat',
                'gaya_belajar',
                'kepribadian',
                'sosiometri',
                'angket_masalah',
                'try_out',
            ]); // Kategori asesmen
            $table->string('analysis_type')->default('basic'); // basic, detailed, custom
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index('category');
            $table->index('is_active');
        });

        // Tabel pivot untuk menghubungkan paket dengan pertanyaan
        Schema::create('instrument_package_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('instrument_package_id')->constrained('instrument_packages')->cascadeOnDelete();
            $table->foreignId('instrument_question_id')->constrained('instrument_questions')->cascadeOnDelete();
            $table->integer('order')->default(0); // Urutan pertanyaan dalam paket
            $table->timestamps();
            
            $table->unique(['instrument_package_id', 'instrument_question_id'], 'pkg_q_unique');
            $table->index('order');
        });

        // Tambah kolom instrument_package_id ke instrument_submissions
        Schema::table('instrument_submissions', function (Blueprint $table) {
            $table->foreignId('instrument_package_id')->nullable()->constrained('instrument_packages')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('instrument_submissions', function (Blueprint $table) {
            $table->dropForeignKeyIfExists('instrument_submissions_instrument_package_id_foreign');
            $table->dropColumn('instrument_package_id');
        });

        Schema::dropIfExists('instrument_package_questions');
        Schema::dropIfExists('instrument_packages');
    }
};

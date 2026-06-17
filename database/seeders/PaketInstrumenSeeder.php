<?php

namespace Database\Seeders;

use App\Models\PaketInstrumen;
use App\Models\PertanyaanInstrumen;
use App\Models\User;
use Illuminate\Database\Seeder;

class PaketInstrumenSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Cari guru BK pertama
        $guruBk = User::where('role', 'guru')->first();
        if (!$guruBk) {
            return; // Tidak ada guru BK, skip
        }

        // Ambil pertanyaan dari database
        $questions = PertanyaanInstrumen::where('is_active', true)->get();

        // 1. Paket Minat Bakat
        $minatBakat = PaketInstrumen::create([
            'name' => 'Asesmen Minat Bakat Kelas X',
            'description' => 'Paket asesmen untuk mengidentifikasi minat dan bakat siswa kelas X',
            'category' => 'minat_bakat',
            'analysis_type' => 'detailed',
            'created_by' => $guruBk->id,
            'is_active' => true,
        ]);

        // Tambahkan pertanyaan ke paket (jika ada)
        $minatBakatQuestions = $questions
            ->where('category', 'minat_bakat')
            ->take(10)
            ->pluck('id')
            ->toArray();
        
        foreach ($minatBakatQuestions as $idx => $questionId) {
            $minatBakat->questions()->attach($questionId, ['order' => $idx + 1]);
        }

        // 2. Paket Gaya Belajar
        $gayaBelajar = PaketInstrumen::create([
            'name' => 'Asesmen Gaya Belajar Kelas X',
            'description' => 'Paket asesmen untuk menentukan gaya belajar siswa (visual, audio, kinestetik)',
            'category' => 'gaya_belajar',
            'analysis_type' => 'detailed',
            'created_by' => $guruBk->id,
            'is_active' => true,
        ]);

        $gayaBelajarQuestions = $questions
            ->where('category', 'gaya_belajar')
            ->take(10)
            ->pluck('id')
            ->toArray();
        
        foreach ($gayaBelajarQuestions as $idx => $questionId) {
            $gayaBelajar->questions()->attach($questionId, ['order' => $idx + 1]);
        }

        // 3. Paket Kepribadian
        $kepribadian = PaketInstrumen::create([
            'name' => 'Asesmen Kepribadian Kelas X',
            'description' => 'Paket asesmen untuk profiling kepribadian siswa',
            'category' => 'kepribadian',
            'analysis_type' => 'detailed',
            'created_by' => $guruBk->id,
            'is_active' => true,
        ]);

        $kepribadianQuestions = $questions
            ->where('category', 'kepribadian')
            ->take(10)
            ->pluck('id')
            ->toArray();
        
        foreach ($kepribadianQuestions as $idx => $questionId) {
            $kepribadian->questions()->attach($questionId, ['order' => $idx + 1]);
        }

        // 4. Paket Karier
        $karier = PaketInstrumen::create([
            'name' => 'Asesmen Karier Kelas X',
            'description' => 'Paket asesmen untuk membantu siswa merencanakan pilihan karier atau studi lanjut.',
            'category' => 'karier',
            'analysis_type' => 'detailed',
            'created_by' => $guruBk->id,
            'is_active' => true,
        ]);

        $karierQuestions = $questions
            ->where('category', 'karier')
            ->take(10)
            ->pluck('id')
            ->toArray();
        
        foreach ($karierQuestions as $idx => $questionId) {
            $karier->questions()->attach($questionId, ['order' => $idx + 1]);
        }

        // 5. Paket Akademik
        $akademik = PaketInstrumen::create([
            'name' => 'Asesmen Akademik Kelas X',
            'description' => 'Paket asesmen untuk menilai kesiapan belajar dan strategi akademik siswa.',
            'category' => 'akademik',
            'analysis_type' => 'detailed',
            'created_by' => $guruBk->id,
            'is_active' => true,
        ]);

        $akademikQuestions = $questions
            ->where('category', 'akademik')
            ->take(10)
            ->pluck('id')
            ->toArray();
        
        foreach ($akademikQuestions as $idx => $questionId) {
            $akademik->questions()->attach($questionId, ['order' => $idx + 1]);
        }

        // 6. Paket Sosiometri
        $sosiometri = PaketInstrumen::create([
            'name' => 'Asesmen Sosiometri Kelas X',
            'description' => 'Paket asesmen untuk memahami dinamika hubungan sosial di kelas',
            'category' => 'sosiometri',
            'analysis_type' => 'basic',
            'created_by' => $guruBk->id,
            'is_active' => true,
        ]);

        $sosiometriQuestions = $questions
            ->where('category', 'sosiometri')
            ->take(10)
            ->pluck('id')
            ->toArray();
        
        foreach ($sosiometriQuestions as $idx => $questionId) {
            $sosiometri->questions()->attach($questionId, ['order' => $idx + 1]);
        }

        // 5. Paket Angket Masalah
        $angketMasalah = PaketInstrumen::create([
            'name' => 'Angket Masalah Kelas X',
            'description' => 'Paket untuk mengidentifikasi masalah/tantangan yang dihadapi siswa',
            'category' => 'angket_masalah',
            'analysis_type' => 'basic',
            'created_by' => $guruBk->id,
            'is_active' => true,
        ]);

        $angketMasalahQuestions = $questions
            ->where('category', 'angket_masalah')
            ->take(10)
            ->pluck('id')
            ->toArray();
        
        foreach ($angketMasalahQuestions as $idx => $questionId) {
            $angketMasalah->questions()->attach($questionId, ['order' => $idx + 1]);
        }
    }
}

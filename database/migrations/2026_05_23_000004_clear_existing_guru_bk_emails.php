<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')
            ->where('role', 'guru')
            ->update([
                'email' => null,
                'email_verified_at' => null,
            ]);
    }

    public function down(): void
    {
        DB::table('users')
            ->where('role', 'guru')
            ->whereNull('email')
            ->update([
                'email' => DB::raw("CONCAT('guru-', id, '@guru-bk.local')"),
                'email_verified_at' => now(),
            ]);
    }
};

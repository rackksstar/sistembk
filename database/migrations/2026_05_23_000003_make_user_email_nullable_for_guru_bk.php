<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('email')->nullable()->change();
            $table->timestamp('email_verified_at')->nullable()->change();
        });

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
            ->whereNull('email')
            ->update([
                'email' => DB::raw("CONCAT('user-', id, '@guru-bk.local')"),
                'email_verified_at' => now(),
            ]);

        Schema::table('users', function (Blueprint $table) {
            $table->string('email')->nullable(false)->change();
        });
    }
};

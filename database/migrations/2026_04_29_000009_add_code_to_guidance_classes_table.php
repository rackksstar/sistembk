<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('guidance_classes', function (Blueprint $table) {
            $table->string('code')->nullable()->unique()->after('name');
        });

        DB::table('guidance_classes')->orderBy('id')->get()->each(function ($class): void {
            DB::table('guidance_classes')
                ->where('id', $class->id)
                ->update(['code' => 'BK-'.Str::upper(Str::random(6))]);
        });
    }

    public function down(): void
    {
        Schema::table('guidance_classes', function (Blueprint $table) {
            $table->dropColumn('code');
        });
    }
};

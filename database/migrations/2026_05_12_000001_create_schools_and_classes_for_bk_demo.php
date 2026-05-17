<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('schools')) {
            Schema::create('schools', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('npsn')->nullable()->unique();
                $table->text('address')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('classes')) {
            Schema::create('classes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('school_id')->constrained()->cascadeOnDelete();
                $table->string('name');
                $table->string('level')->nullable();
                $table->timestamps();
                $table->unique(['school_id', 'name']);
            });
        }

        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'school_id')) {
                $table->foreignId('school_id')->nullable()->after('school')->constrained('schools')->nullOnDelete();
            }

            if (! Schema::hasColumn('users', 'class_id')) {
                $table->foreignId('class_id')->nullable()->after('school_id')->constrained('classes')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'class_id')) {
                $table->dropConstrainedForeignId('class_id');
            }

            if (Schema::hasColumn('users', 'school_id')) {
                $table->dropConstrainedForeignId('school_id');
            }
        });

        Schema::dropIfExists('classes');
        Schema::dropIfExists('schools');
    }
};

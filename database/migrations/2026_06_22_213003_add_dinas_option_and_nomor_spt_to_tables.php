<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('izin', function (Blueprint $table) {
            $table->string('nomor_spt')->nullable()->after('lampiran');
        });

        // Modify enum columns using DB::statement for compatibility
        DB::statement("ALTER TABLE izin MODIFY COLUMN jenis ENUM('izin', 'sakit', 'dinas') NOT NULL DEFAULT 'izin'");
        DB::statement("ALTER TABLE absensi MODIFY COLUMN status ENUM('hadir', 'terlambat', 'izin', 'sakit', 'alpha', 'dinas') NOT NULL DEFAULT 'alpha'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('izin', function (Blueprint $table) {
            $table->dropColumn('nomor_spt');
        });

        DB::statement("ALTER TABLE izin MODIFY COLUMN jenis ENUM('izin', 'sakit') NOT NULL DEFAULT 'izin'");
        DB::statement("ALTER TABLE absensi MODIFY COLUMN status ENUM('hadir', 'terlambat', 'izin', 'sakit', 'alpha') NOT NULL DEFAULT 'alpha'");
    }
};

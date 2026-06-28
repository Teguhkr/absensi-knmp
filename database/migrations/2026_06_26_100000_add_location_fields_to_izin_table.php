<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('izin', function (Blueprint $table) {
            // Koordinat lokasi penugasan (diisi saat pengajuan)
            $table->decimal('latitude', 10, 7)->nullable()->after('nomor_spt');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');

            // Request perubahan lokasi selama penugasan berlangsung
            $table->decimal('req_latitude', 10, 7)->nullable()->after('longitude');
            $table->decimal('req_longitude', 10, 7)->nullable()->after('req_latitude');
            $table->enum('req_lokasi_status', ['pending', 'approved', 'rejected'])->nullable()->after('req_longitude');
            $table->text('req_lokasi_alasan')->nullable()->after('req_lokasi_status');
            $table->text('req_lokasi_catatan')->nullable()->after('req_lokasi_alasan');
        });
    }

    public function down(): void
    {
        Schema::table('izin', function (Blueprint $table) {
            $table->dropColumn([
                'latitude', 'longitude',
                'req_latitude', 'req_longitude',
                'req_lokasi_status', 'req_lokasi_alasan', 'req_lokasi_catatan',
            ]);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Mengubah kolom dokumentasi dan keterangan_dokumentasi menjadi JSON
     * agar dapat menyimpan lebih dari 1 dokumentasi per laporan.
     */
    public function up(): void
    {
        // Langkah 1: Ubah tipe kolom dokumentasi ke text dulu (agar bisa menampung JSON panjang)
        // dan tambahkan kolom sementara untuk menyimpan data lama
        Schema::table('laporan_harians', function (Blueprint $table) {
            $table->text('dokumentasi')->nullable()->change();
        });

        // Langkah 2: Migrasi data lama ke format JSON array baru
        $records = DB::table('laporan_harians')->get();

        foreach ($records as $record) {
            $oldDokumentasi = $record->dokumentasi;
            $oldKeterangan  = $record->keterangan_dokumentasi;

            // Jika sudah JSON array (sudah di-migrate sebelumnya), skip
            $decoded = json_decode($oldDokumentasi, true);
            if (is_array($decoded)) {
                continue;
            }

            // Bungkus data lama (string path) ke dalam array JSON
            $newDokumentasi = json_encode([
                [
                    'foto'       => $oldDokumentasi ?? '',
                    'keterangan' => $oldKeterangan  ?? '',
                ]
            ]);

            DB::table('laporan_harians')
                ->where('id', $record->id)
                ->update(['dokumentasi' => $newDokumentasi]);
        }

        // Langkah 3: Hapus kolom keterangan_dokumentasi (sudah masuk dalam JSON dokumentasi)
        Schema::table('laporan_harians', function (Blueprint $table) {
            $table->dropColumn('keterangan_dokumentasi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('laporan_harians', function (Blueprint $table) {
            $table->text('keterangan_dokumentasi')->nullable()->after('dokumentasi');
        });

        // Kembalikan data pertama ke kolom terpisah
        $records = DB::table('laporan_harians')->get();
        foreach ($records as $record) {
            $decoded = json_decode($record->dokumentasi, true);
            if (is_array($decoded) && count($decoded) > 0) {
                $first = $decoded[0];
                DB::table('laporan_harians')
                    ->where('id', $record->id)
                    ->update([
                        'dokumentasi'           => $first['foto']        ?? null,
                        'keterangan_dokumentasi' => $first['keterangan'] ?? null,
                    ]);
            }
        }

        // Ubah kembali ke string
        Schema::table('laporan_harians', function (Blueprint $table) {
            $table->string('dokumentasi')->nullable()->change();
        });
    }
};

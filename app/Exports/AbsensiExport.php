<?php

namespace App\Exports;

use App\Models\Absensi;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AbsensiExport implements FromQuery, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    use Exportable;

    public function query()
    {
        return Absensi::query()->with('user')->orderBy('tanggal', 'desc');
    }

    public function headings(): array
    {
        return [
            'ID',
            'NIK',
            'Nama Pegawai',
            'Departemen',
            'Tanggal',
            'Jam Masuk',
            'Jam Pulang',
            'Status',
            'Keterangan',
        ];
    }

    public function map($absensi): array
    {
        return [
            $absensi->id,
            $absensi->user->nik ?? '-',
            $absensi->user->name ?? '-',
            $absensi->user->departemen ?? '-',
            $absensi->tanggal->format('d/m/Y'),
            $absensi->jam_masuk ?? '-',
            $absensi->jam_pulang ?? '-',
            strtoupper($absensi->status),
            $absensi->keterangan ?? '-',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1    => ['font' => ['bold' => true]],
        ];
    }
}

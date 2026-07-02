<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Harian - {{ $user->name }} - {{ $monthName }} {{ $year }}</title>
    <style>
        @page {
            margin: 1.2cm 1.5cm;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            line-height: 1.3;
            color: #000;
        }
        .page-break {
            page-break-after: always;
        }
        .page-break:last-child {
            page-break-after: avoid;
        }
        .section-title {
            font-size: 11px;
            font-weight: bold;
            margin-top: 10px;
            margin-bottom: 4px;
            text-transform: uppercase;
        }

        /* Table umum */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
            page-break-inside: auto;
        }
        tr {
            page-break-inside: avoid;
            page-break-after: auto;
        }
        th, td {
            border: 1px solid #000;
            padding: 5px 7px;
            vertical-align: middle;
            font-size: 10.5px;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
            text-align: center;
        }
        .text-center { text-align: center; }
        .text-justify { text-align: justify; }
        .bold { font-weight: bold; }

        /* Header khusus */
        .header-table td, .header-table th {
            padding: 6px 8px;
        }

        /* Lebar kolom */
        .no-col   { width: 28px; text-align: center; }
        .status-col { width: 55px; text-align: center; }
    </style>
</head>
<body>

@foreach($reports as $report)
<div class="page-break">

    {{-- ===== HEADER TABLE ===== --}}
    <table class="header-table" style="border: 1.5px solid #000; margin-bottom: 12px;">

        {{-- Baris 1: LAPORAN HARIAN (kiri colspan 2) | Logo (kanan rowspan 2) --}}
        <tr>
            <td colspan="2"
                style="text-align:center; font-weight:bold; font-size:13px;
                       border-right: 1.5px solid #000; border-bottom: 1px solid #000;
                       padding: 6px 8px;">
                LAPORAN HARIAN
            </td>
            <td rowspan="2"
                style="text-align:center; vertical-align:middle;
                       width:35%; border-left: 1.5px solid #000; border-bottom: 1px solid #000;
                       padding: 6px;">
                @php
                    $logoPath = public_path('image.png');
                    $logoBase64 = '';
                    if (file_exists($logoPath)) {
                        try {
                            $logoData = file_get_contents($logoPath);
                            $logoBase64 = 'data:image/' . pathinfo($logoPath, PATHINFO_EXTENSION) . ';base64,' . base64_encode($logoData);
                        } catch (\Exception $e) {
                            // ignore
                        }
                    }
                @endphp
                @if($logoBase64)
                    <img src="{{ $logoBase64 }}" style="max-height:90px; max-width:90px; display:block; margin:0 auto;">
                @endif
            </td>
        </tr>

        {{-- Baris 2: Subjudul (kiri colspan 2) | (lanjutan logo kanan) --}}
        <tr>
            <td colspan="2"
                style="text-align:center; font-size:10px;
                       border-right: 1.5px solid #000; border-bottom: 1px solid #000;
                       padding: 5px 8px; line-height: 1.5;">
                {{ strtoupper($user->jabatan ?? 'ASISTEN TENAGA AHLI') }}<br>
                PROGRAM KAMPUNG NELAYAN MERAH PUTIH<br>
                TAHUN ANGGARAN {{ $year }}
            </td>
        </tr>

        {{-- Baris 3: Hari | nilai (kiri 2 col) | SEKRETARIAT (kanan rowspan 2) --}}
        <tr>
            <td style="font-weight:bold; width:12%;
                       border-right: 1px solid #000; border-bottom: 1px solid #000;">
                Hari
            </td>
            <td style="width:53%;
                       border-right: 1.5px solid #000; border-bottom: 1px solid #000;">
                {{ $report->getHariIndonesian() }}
            </td>
            <td rowspan="2"
                style="text-align:center; font-weight:bold; vertical-align:middle;
                       width:35%; font-size:10.5px; line-height: 1.5;
                       border-left: none; border-bottom: 1px solid #000;">
                SEKRETARIAT DIREKTORAT JENDERAL<br>PERIKANAN TANGKAP
            </td>
        </tr>

        {{-- Baris 4: Tanggal | nilai (kiri 2 col) | (lanjutan SEKRETARIAT) --}}
        <tr>
            <td style="font-weight:bold;
                       border-right: 1px solid #000; border-bottom: 1.5px solid #000;">
                Tanggal
            </td>
            <td style="border-right: 1.5px solid #000; border-bottom: 1.5px solid #000;">
                {{ $report->getTanggalIndonesian() }}
            </td>
        </tr>

        {{-- Baris 5: DILAPORKAN OLEH (full width) --}}
        <tr>
            <td colspan="3"
                style="text-align:center; font-weight:bold; font-size:11px;
                       border-top: 1.5px solid #000; border-bottom: 1px solid #000;
                       padding: 5px 8px;">
                DILAPORKAN OLEH:
            </td>
        </tr>

        {{-- Baris 6: NAMA --}}
        <tr>
            <td style="text-align:center; font-weight:bold;
                       border-right: 1px solid #000; border-bottom: 1px solid #000;">
                NAMA
            </td>
            <td colspan="2"
                style="text-align:center;
                       border-bottom: 1px solid #000;">
                {{ $user->name }}
            </td>
        </tr>

        {{-- Baris 7: JABATAN --}}
        <tr>
            <td style="text-align:center; font-weight:bold;
                       border-right: 1px solid #000; border-bottom: none;">
                JABATAN
            </td>
            <td colspan="2"
                style="text-align:center; border-bottom: none;">
                {{ $user->jabatan ?? 'Asisten Tenaga Ahli' }}
            </td>
        </tr>

    </table>
    {{-- ===== END HEADER ===== --}}

    {{-- ===== OPERASIONAL ===== --}}
    <div class="section-title">OPERASIONAL</div>
    @php
        $operasionalItems = $report->operasional ?? [];
        if (!is_array($operasionalItems)) {
            $operasionalItems = [];
        }
        // Tambahkan tepat 1 item kosong di akhir
        $operasionalItems[] = ['kegiatan' => '', 'status' => null];
    @endphp
    <table>
        <thead>
            <tr>
                <th class="no-col">No</th>
                <th>Kegiatan</th>
                <th class="status-col">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($operasionalItems as $index => $item)
            <tr>
                <td class="text-center" style="height:18px;">{{ $index + 1 }}</td>
                <td>{{ $item['kegiatan'] ?? '' }}</td>
                <td class="text-center bold"
                    style="font-family: DejaVu Sans, sans-serif; font-size: 11px;">
                    @if(isset($item['status']) && ($item['status'] === 'Selesai' || $item['status'] === true || $item['status'] == 1))
                        <span style="font-family: DejaVu Sans, sans-serif; font-size: 13px;">&#x221A;</span>
                    @elseif(isset($item['status']) && $item['status'] === 'In Progress')
                        <span style="font-size: 9px; font-weight: normal;">In Progress</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- ===== LOKASI KNMP ===== --}}
    <div class="section-title">LOKASI KNMP <span style="font-size:9px; font-style:italic; font-weight:normal;">(Dikantor dikosongkan)</span></div>
    @php
        $lokasiItems = $report->lokasi_knmp ?? [];
        if (!is_array($lokasiItems)) {
            $lokasiItems = [];
        }
        // Tambahkan tepat 1 item kosong di akhir
        $lokasiItems[] = ['peristiwa' => '', 'status' => '', 'keterangan' => '', 'tindak_lanjut' => ''];
    @endphp
    <table>
        <thead>
            <tr>
                <th class="no-col">No</th>
                <th style="width:25%;">Peristiwa</th>
                <th class="status-col">Status</th>
                <th style="width:28%;">Keterangan</th>
                <th style="width:28%;">Tindak Lanjut</th>
            </tr>
        </thead>
        <tbody>
            @foreach($lokasiItems as $index => $item)
            <tr>
                <td class="text-center" style="height:18px;">{{ $index + 1 }}</td>
                <td>{{ $item['peristiwa']    ?? '' }}</td>
                <td class="text-center" style="font-size:9px;">{{ $item['status']     ?? '' }}</td>
                <td>{{ $item['keterangan']   ?? '' }}</td>
                <td>{{ $item['tindak_lanjut']?? '' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div style="font-size:8px; font-style:italic; margin-top:-8px; margin-bottom:12px; color:#555;">
        Status: Clear / Hold / On-Progress
    </div>

    {{-- ===== DOKUMENTASI ===== --}}
    <div class="section-title">DOKUMENTASI</div>
    @php
        // Support format lama (string) dan format baru (array JSON)
        $dokumentasiItems = $report->dokumentasi ?? [];
        if (is_string($dokumentasiItems)) {
            // Format lama: wrap ke array agar tetap kompatibel
            $dokumentasiItems = [[
                'fotos'       => [$dokumentasiItems],
                'keterangan'  => $report->keterangan_dokumentasi ?? '',
            ]];
        }
        if (empty($dokumentasiItems)) {
            $dokumentasiItems = [['fotos' => [], 'keterangan' => '']];
        }
    @endphp
    <table>
        <thead>
            <tr>
                <th class="no-col">No.</th>
                <th style="width:40%;">Dokumentasi</th>
                <th style="width:55%;">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($dokumentasiItems as $dokIndex => $dokItem)
            <tr>
                <td class="text-center" style="vertical-align:middle;">{{ $dokIndex + 1 }}</td>
                <td class="text-center" style="padding:8px; vertical-align:middle;">
                    @php
                        // Ambil daftar foto (bisa dari key 'fotos' yang berupa array, atau fallback ke 'foto' tunggal)
                        $fotos = $dokItem['fotos'] ?? [];
                        if (is_string($fotos)) {
                            $fotos = [$fotos];
                        }
                        if (empty($fotos) && !empty($dokItem['foto'])) {
                            $fotos = [$dokItem['foto']];
                        }
                        
                        $renderedImages = [];
                        foreach ($fotos as $fotoPath) {
                            if ($fotoPath) {
                                $imgPath = storage_path('app/public/' . $fotoPath);
                                if (file_exists($imgPath)) {
                                    try {
                                        $imgData = file_get_contents($imgPath);
                                        $base64 = 'data:image/' . pathinfo($imgPath, PATHINFO_EXTENSION) . ';base64,' . base64_encode($imgData);
                                        $renderedImages[] = [
                                            'src' => $base64,
                                            'error' => false
                                        ];
                                    } catch (\Exception $e) {
                                        $renderedImages[] = [
                                            'src' => null,
                                            'error' => true,
                                            'path' => $fotoPath
                                        ];
                                    }
                                } else {
                                    $renderedImages[] = [
                                        'src' => null,
                                        'error' => true,
                                        'path' => $fotoPath
                                    ];
                                }
                            }
                        }
                    @endphp

                    @if(!empty($renderedImages))
                        <div style="text-align: center;">
                            @foreach($renderedImages as $image)
                                @if($image['src'])
                                    <img src="{{ $image['src'] }}"
                                         style="max-width: 46%; max-height: 110px; display: inline-block; margin: 3px; vertical-align: middle; border: 0.5px solid #ccc; padding: 2px;">
                                @else
                                    <div style="color:red; font-size:8px; margin: 4px; display: block; clear: both;">File tidak ditemukan/tidak dapat dibaca ({{ basename($image['path']) }})</div>
                                @endif
                            @endforeach
                        </div>
                    @else
                        <span style="color:gray; font-size:8px;">Tidak ada foto dokumentasi</span>
                    @endif
                </td>
                <td class="text-justify" style="line-height:1.4; padding:8px; font-size:9.5px;">
                    {!! nl2br(e($dokItem['keterangan'] ?? '')) !!}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

</div>
@endforeach

</body>
</html>

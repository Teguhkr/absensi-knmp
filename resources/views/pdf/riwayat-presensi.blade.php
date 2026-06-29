<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Riwayat Presensi - {{ $user->name }} - {{ $monthName }} {{ $year }}</title>
    <style>
        @page {
            margin: 1.5cm 1.8cm;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            line-height: 1.4;
            color: #000;
        }

        /* ===== HEADER ===== */
        .doc-header {
            margin-bottom: 14px;
        }
        .doc-title {
            font-size: 13px;
            font-weight: bold;
            text-align: center;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 2px;
        }
        .doc-subtitle {
            font-size: 10px;
            text-align: center;
            margin-bottom: 10px;
            color: #333;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
        }
        .info-table td {
            padding: 3px 0;
            font-size: 11px;
            vertical-align: top;
        }
        .info-label {
            width: 26%;
            font-weight: bold;
        }
        .info-sep {
            width: 2%;
        }
        .info-val {
            width: 72%;
        }

        /* ===== MAIN TABLE ===== */
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
        }
        table.data-table th, table.data-table td {
            border: 1px solid #000;
            padding: 5px 7px;
            vertical-align: middle;
            font-size: 10.5px;
        }
        table.data-table th {
            background-color: #e8e8e8;
            font-weight: bold;
            text-align: center;
        }
        .text-center { text-align: center; }
        .no-col  { width: 26px; text-align: center; }
        .day-col { width: 38%; }
        .time-col { width: 14%; text-align: center; }
        .dur-col  { width: 12%; text-align: center; }
        .stat-col { width: 14%; text-align: center; }
        .ket-col  { width: 14%; text-align: center; }

        /* ===== STATUS BADGE ===== */
        .badge {
            display: inline-block;
            padding: 1px 6px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: bold;
        }
        .badge-hadir     { background: #d4edda; color: #155724; }
        .badge-terlambat { background: #fff3cd; color: #856404; }
        .badge-cuti      { background: #d1ecf1; color: #0c5460; }
        .badge-sakit     { background: #fff3cd; color: #856404; }
        .badge-dinas     { background: #d4edda; color: #155724; }
        .badge-alpha     { background: #f8d7da; color: #721c24; }
        .badge-empty     { color: #aaa; font-size: 9px; }

        /* ===== REKAP ===== */
        .rekap-title {
            font-size: 11px;
            font-weight: bold;
            margin-bottom: 6px;
            text-transform: uppercase;
        }
        table.rekap-table {
            width: auto;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table.rekap-table th, table.rekap-table td {
            border: 1px solid #000;
            padding: 4px 10px;
            font-size: 10.5px;
            text-align: center;
        }
        table.rekap-table th {
            background: #e8e8e8;
            font-weight: bold;
        }

        /* ===== TTANDATANGAN ===== */
        .ttd-wrapper {
            margin-top: 20px;
            width: 100%;
        }
        .ttd-box {
            float: right;
            text-align: center;
            width: 200px;
        }
        .ttd-place {
            font-size: 10.5px;
            margin-bottom: 50px;
        }
        .ttd-name {
            font-weight: bold;
            font-size: 11px;
            border-top: 1px solid #000;
            padding-top: 4px;
        }
        .ttd-jabatan {
            font-size: 10px;
            color: #333;
        }
        .clearfix:after {
            content: "";
            display: table;
            clear: both;
        }

        /* ===== LOGO ===== */
        .logo-area {
            float: right;
            text-align: center;
        }
    </style>
</head>
<body>

    {{-- ===== HEADER ===== --}}
    <table style="width:100%;border-collapse:collapse;margin-bottom:12px;">
        <tr>
            <td style="vertical-align:middle;width:75%;">
                <div style="font-size:14px;font-weight:bold;text-transform:uppercase;letter-spacing:1px;">
                    PRESENSI HARIAN
                </div>
                <div style="font-size:11px;font-weight:bold;margin-top:3px;">
                    NAMA : {{ strtoupper($user->name) }}
                </div>
                <div style="font-size:10px;color:#333;margin-top:2px;">
                    Periode : {{ $monthName }} {{ $year }}
                    @if($user->departemen)
                        &nbsp;|&nbsp; Penempatan : {{ $user->departemen }}
                    @endif
                    @if($user->jabatan)
                        &nbsp;|&nbsp; Jabatan : {{ $user->jabatan }}
                    @endif
                </div>
            </td>
            <td style="vertical-align:middle;text-align:right;width:25%;">
                @php
                    $logoPath = public_path('image.png');
                    $logoBase64 = '';
                    if (file_exists($logoPath)) {
                        try {
                            $logoData = file_get_contents($logoPath);
                            $logoBase64 = 'data:image/' . pathinfo($logoPath, PATHINFO_EXTENSION) . ';base64,' . base64_encode($logoData);
                        } catch (\Exception $e) {
                            // Silently ignore if read fails
                        }
                    }
                @endphp
                @if($logoBase64)
                    <img src="{{ $logoBase64 }}" style="max-height:70px;max-width:80px;object-fit:contain;">
                @endif
            </td>
        </tr>
    </table>

    <hr style="border:0;border-top:1.5px solid #000;margin-bottom:14px;">

    {{-- ===== TABEL PRESENSI ===== --}}
    <table class="data-table">
        <thead>
            <tr>
                <th class="no-col">No.</th>
                <th class="day-col">Hari / Tanggal</th>
                <th class="time-col">Jam Kedatangan<br><span style="font-size:8.5px;font-weight:normal;">(WIB)</span></th>
                <th class="time-col">Jam Pulang<br><span style="font-size:8.5px;font-weight:normal;">(WIB)</span></th>
                <th class="dur-col">Durasi Kerja</th>
                <th class="stat-col">Status</th>
                <th class="ket-col">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @php
                $daysInMonth = (int) $endDate->format('j');
                $no = 1;
                $indoDays = [
                    'Sunday' => 'Minggu',
                    'Monday' => 'Senin',
                    'Tuesday' => 'Selasa',
                    'Wednesday' => 'Rabu',
                    'Thursday' => 'Kamis',
                    'Friday' => 'Jumat',
                    'Saturday' => 'Sabtu'
                ];
                $indoMonths = [
                    1 => 'Januari',
                    2 => 'Februari',
                    3 => 'Maret',
                    4 => 'April',
                    5 => 'Mei',
                    6 => 'Juni',
                    7 => 'Juli',
                    8 => 'Agustus',
                    9 => 'September',
                    10 => 'Oktober',
                    11 => 'November',
                    12 => 'Desember'
                ];
            @endphp
            @for($day = 1; $day <= $daysInMonth; $day++)
                @php
                    $dateObj = \Carbon\Carbon::createFromDate($year, $month, $day);
                    $dateStr = $dateObj->format('Y-m-d');
                    $absensi = $absensiByDate->get($dateStr);
                    
                    $englishDay = $dateObj->format('l');
                    $hariNama = $indoDays[$englishDay] ?? $englishDay;
                    
                    $dayNum = $dateObj->format('j');
                    $monthNum = (int)$dateObj->format('n');
                    $yearNum = $dateObj->format('Y');
                    $tglFormat = "{$dayNum} {$indoMonths[$monthNum]} {$yearNum}";

                    // Hitung durasi
                    $durasi = '-';
                    if ($absensi && $absensi->jam_masuk && $absensi->jam_pulang) {
                        $masuk  = \Carbon\Carbon::createFromTimeString($absensi->jam_masuk);
                        $pulang = \Carbon\Carbon::createFromTimeString($absensi->jam_pulang);
                        $menit  = $masuk->diffInMinutes($pulang);
                        $durasi = floor($menit / 60) . 'j ' . ($menit % 60) . 'm';
                    }

                    // Label status
                    $statusLabel = '';
                    $statusClass = '';
                    if ($absensi) {
                        $statusLabel = match($absensi->status) {
                            'hadir'     => 'Hadir',
                            'terlambat' => 'Terlambat',
                            'izin'      => 'Cuti',
                            'sakit'     => 'Sakit',
                            'dinas'     => 'Penugasan',
                            'alpha'     => 'Alpha',
                            default     => ucfirst($absensi->status),
                        };
                        $statusClass = match($absensi->status) {
                            'hadir'     => 'badge-hadir',
                            'terlambat' => 'badge-terlambat',
                            'izin'      => 'badge-cuti',
                            'sakit'     => 'badge-sakit',
                            'dinas'     => 'badge-dinas',
                            'alpha'     => 'badge-alpha',
                            default     => '',
                        };
                    }
                @endphp
                <tr style="{{ !$absensi ? 'color:#888;' : '' }}">
                    <td class="text-center" style="height:19px;">{{ $day }}.</td>
                    <td>{{ $hariNama }}, {{ $tglFormat }}</td>
                    <td class="text-center">
                        @if($absensi && $absensi->jam_masuk)
                            {{ \Carbon\Carbon::createFromTimeString($absensi->jam_masuk)->format('H.i') }}
                        @endif
                    </td>
                    <td class="text-center">
                        @if($absensi && $absensi->jam_pulang)
                            {{ \Carbon\Carbon::createFromTimeString($absensi->jam_pulang)->format('H.i') }}
                        @endif
                    </td>
                    <td class="text-center" style="font-size:9.5px;">
                        @if($absensi && $absensi->jam_masuk && $absensi->jam_pulang)
                            {{ $durasi }}
                        @endif
                    </td>
                    <td class="text-center">
                        @if($statusLabel)
                            <span class="badge {{ $statusClass }}">{{ $statusLabel }}</span>
                        @endif
                    </td>
                    <td style="font-size:9.5px;color:#555;">
                        @if($absensi && $absensi->keterangan)
                            {{ \Illuminate\Support\Str::limit($absensi->keterangan, 40) }}
                        @endif
                    </td>
                </tr>
            @endfor
        </tbody>
    </table>

    {{-- ===== REKAPITULASI ===== --}}
    <div class="rekap-title">Rekapitulasi Presensi</div>
    <table class="rekap-table">
        <thead>
            <tr>
                <th>Hadir</th>
                <th>Terlambat</th>
                <th>Cuti</th>
                <th>Sakit</th>
                <th>Penugasan</th>
                <th>Alpha</th>
                <th>Total Hari Kerja</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $rekap['hadir'] }} hari</td>
                <td>{{ $rekap['terlambat'] }} hari</td>
                <td>{{ $rekap['izin'] }} hari</td>
                <td>{{ $rekap['sakit'] }} hari</td>
                <td>{{ $rekap['dinas'] }} hari</td>
                <td>{{ $rekap['alpha'] }} hari</td>
                <td><strong>{{ array_sum($rekap) }} hari</strong></td>
            </tr>
        </tbody>
    </table>

    {{-- ===== TANDA TANGAN ===== --}}
    <div class="clearfix">
        <div class="ttd-box">
            <div class="ttd-place" style="margin-bottom: 0;">
                @php
                    $lastDate = \Carbon\Carbon::createFromDate($year, $month)->endOfMonth();
                    $lastDayNum = $lastDate->format('j');
                    $lastMonthNum = (int)$lastDate->format('n');
                    $lastYearNum = $lastDate->format('Y');
                    $lastTglFormat = "{$lastDayNum} {$indoMonths[$lastMonthNum]} {$lastYearNum}";
                @endphp
                {{-- {{ $instansi }}, {{ $lastTglFormat }} --}}
            </div>
            <div style="font-size: 10.5px; margin-bottom: 50px; margin-top: 4px;">
                Mengetahui,
            </div>
            <div class="ttd-name">Achmad Rizcky Alfadion</div>
            <div class="ttd-jabatan">(Mentor)</div>
        </div>
    </div>

</body>
</html>

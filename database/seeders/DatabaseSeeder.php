<?php

namespace Database\Seeders;

use App\Models\PengaturanSistem;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Admin user
        $admin = User::updateOrCreate(
            ['email' => 'teguhk356@gmail.com'],
            [
                'name'      => 'Administrator',
                'password'  => bcrypt('password'),
                'role'      => 'admin',
                'nip'       => '000000001',
                'jabatan'   => 'Administrator Sistem',
                'departemen' => 'IT',
                'is_active' => true,
                'qr_token'  => Str::uuid()->toString(),
            ]
        );

        // Contoh pegawai
        $pegawai = [
            [
                'name'       => 'Budi Santoso',
                'email'      => 'budi@knmp.go.id',
                'nip'        => '198501012010011001',
                'jabatan'    => 'Analis Kebijakan',
                'departemen' => 'Bidang Perikanan',
            ],
            [
                'name'       => 'Siti Rahayu',
                'email'      => 'siti@knmp.go.id',
                'nip'        => '199003152012012002',
                'jabatan'    => 'Staf Administrasi',
                'departemen' => 'Bidang Umum',
            ],
            [
                'name'       => 'Agus Hermawan',
                'email'      => 'agus@knmp.go.id',
                'nip'        => '198712052011011003',
                'jabatan'    => 'Kepala Seksi',
                'departemen' => 'Bidang Perikanan',
            ],
        ];

        foreach ($pegawai as $p) {
            User::updateOrCreate(
                ['email' => $p['email']],
                array_merge($p, [
                    'password'   => bcrypt('password'),
                    'role'       => 'pegawai',
                    'is_active'  => true,
                    'qr_token'   => Str::uuid()->toString(),
                ])
            );
        }

        // Pengaturan sistem default
        $settings = [
            ['key' => 'jam_masuk',         'value' => '08:00', 'label' => 'Jam Masuk Kerja',          'tipe' => 'time'],
            ['key' => 'jam_pulang',        'value' => '16:00', 'label' => 'Jam Pulang Kerja',         'tipe' => 'time'],
            ['key' => 'toleransi_menit',   'value' => '15',    'label' => 'Toleransi Keterlambatan (menit)', 'tipe' => 'number'],
            ['key' => 'kantor_latitude',   'value' => '0',     'label' => 'Latitude Kantor',           'tipe' => 'text'],
            ['key' => 'kantor_longitude',  'value' => '0',     'label' => 'Longitude Kantor',          'tipe' => 'text'],
            ['key' => 'radius_absensi',    'value' => '500',   'label' => 'Radius Absensi (meter)',    'tipe' => 'number'],
            ['key' => 'nama_instansi',     'value' => 'KNMP',  'label' => 'Nama Instansi',             'tipe' => 'text'],
            ['key' => 'validasi_gps',      'value' => '1',     'label' => 'Aktifkan Validasi GPS',     'tipe' => 'boolean'],
        ];

        foreach ($settings as $s) {
            PengaturanSistem::updateOrCreate(['key' => $s['key']], $s);
        }
    }
}

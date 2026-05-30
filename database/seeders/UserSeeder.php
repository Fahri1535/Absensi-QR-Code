<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Karyawan;
use App\Models\JadwalKerja;
use App\Models\QrCode;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Jadwal Kerja
        JadwalKerja::firstOrCreate([], [
            'jam_masuk'       => '08:00',
            'jam_pulang'      => '17:00',
            'toleransi_menit' => 5,
            'hari_kerja'      => 'Senin - Jumat',
        ]);

        // QR Codes
        QrCode::getOrCreate('masuk');
        QrCode::getOrCreate('pulang');

        // Operator
        User::updateOrCreate(
            ['username' => 'operator'],
            [
                'password' => Hash::make('operator123'),
                'role' => 'operator',
            ]
        );

        // HRD
        $hrd = User::updateOrCreate(
            ['username' => 'hrd'],
            [
                'password' => Hash::make('hrd123'),
                'role' => 'hrd',
            ]
        );

        Karyawan::updateOrCreate(
            ['user_id' => $hrd->id],
            [
                'nama_lengkap' => 'Admin HRD',
                'jabatan' => 'HRD Manager',
                'status' => 'aktif',
                'kode_karyawan' => 'KRY-HRD00000001',
            ]
        );

        // Karyawan
        $users = [
            "Heru Susanto",
            "Eko Purnomo",
            "Anik Marlina",
            "Hary Imansyah",
            "Putri Milenia",
            "Randy Azhari",
            "Syiefa Alaida",
            "Ersa",
            "Nisa",
            "M. Fachrizal",
            "Mariam Muslimah",
            "Wahyudi",
            "Devigo Arthurito"
        ];

        foreach ($users as $name) {

            // Khusus untuk M. Fachrizal, gunakan username "fachrizal"
            if ($name === "M. Fachrizal") {
                $username = "fachrizal";
            } else {
                $username = strtolower(explode(' ', $name)[0]);
            }

            $user = User::updateOrCreate(
                ['username' => $username],
                [
                    'password' => Hash::make($username . '123'),
                    'role' => 'karyawan',
                ]
            );

            Karyawan::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'nama_lengkap' => $name,
                    'jabatan' => 'Staff',
                    'status' => 'aktif',
                    'kode_karyawan' => 'KRY-' . Str::upper(Str::random(12)),
                ]
            );
        }
    }
}

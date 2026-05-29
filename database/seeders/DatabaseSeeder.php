<?php

namespace Database\Seeders;

use App\Models\{JadwalKerja, Karyawan, QrCode, User};
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── Jadwal default
        JadwalKerja::firstOrCreate([], [
            'jam_masuk'       => '08:00',
            'jam_pulang'      => '17:00',
            'toleransi_menit' => 5,
            'hari_kerja'      => 'Senin - Jumat',
        ]);

        // ── QR Codes
        QrCode::getOrCreate('masuk');
        QrCode::getOrCreate('pulang');

        // ── Operator
        $operator = User::firstOrCreate(['username' => 'operator'], [
            'password' => Hash::make('operator123'),
            'role'     => 'operator',
        ]);

        // ── HRD
        $hrd = User::firstOrCreate(['username' => 'hrd'], [
            'password' => Hash::make('hrd123'),
            'role'     => 'hrd',
        ]);

        Karyawan::firstOrCreate(['user_id' => $hrd->id], [
            'nama_lengkap'  => 'Admin HRD',
            'jabatan'       => 'HRD Manager',
            'status'        => 'aktif',
            'kode_karyawan' => 'KRY-HRD00000001',
        ]);

        // ── Demo karyawan
        $demoUsers = [
            ['username' => 'budi',    'nama_lengkap' => 'Budi Santoso',    'jabatan' => 'Staff Teknik'],
            ['username' => 'siti',    'nama_lengkap' => 'Siti Rahayu',     'jabatan' => 'Staff Admin'],
            ['username' => 'ahmad',   'nama_lengkap' => 'Ahmad Fauzi',     'jabatan' => 'Supervisor'],
        ];

        foreach ($demoUsers as $data) {
            $user = User::firstOrCreate(['username' => $data['username']], [
                'password' => Hash::make('karyawan123'),
                'role'     => 'karyawan',
            ]);

            Karyawan::firstOrCreate(['user_id' => $user->id], [
                'nama_lengkap'  => $data['nama_lengkap'],
                'jabatan'       => $data['jabatan'],
                'status'        => 'aktif',
                'kode_karyawan' => 'KRY-' . Str::upper(Str::random(12)),
            ]);
        }

        $this->command->info('Seeder selesai!');
        $this->command->table(
            ['Role', 'Username', 'Password'],
            [
                ['Operator', 'operator', 'operator123'],
                ['HRD',      'hrd',      'hrd123'],
                ['Karyawan', 'budi',     'karyawan123'],
                ['Karyawan', 'siti',     'karyawan123'],
                ['Karyawan', 'ahmad',    'karyawan123'],
            ]
        );
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Karyawan;

class UserSeeder extends Seeder
{
    public function run(): void
    {
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

            $firstName = strtolower(explode(' ', $name)[0]);

            $user = User::updateOrCreate(
                ['username' => $firstName],
                [
                    'password' => Hash::make($firstName . '123'),
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

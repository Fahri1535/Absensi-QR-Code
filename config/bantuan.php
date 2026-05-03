<?php

return [

    'jam_operasional' => env('BANTUAN_JAM_OPERASIONAL', 'Senin–Jumat, 08.00–17.00 WIB'),

    'sla' => env('BANTUAN_SLA', 'Respons diusahakan paling lambat dalam 1×24 jam kerja.'),

    'kontak' => [
        'operator' => [
            'nama'     => env('ADMIN_OPERASI_NAMA', 'Tim Operator'),
            'deskripsi' => 'Data karyawan, QR presensi, jadwal & lokasi kantor',
            'telepon'   => env('ADMIN_OPERASI_TEL'),
            'whatsapp'  => env('ADMIN_OPERASI_WHATSAPP'),
            'email'     => env('ADMIN_OPERASI_EMAIL'),
        ],
        'hrd' => [
            'nama'      => env('ADMIN_HRD_NAMA', 'Tim HRD'),
            'deskripsi' => 'Persetujuan izin/cuti dan kebijakan SDM',
            'telepon'   => env('ADMIN_HRD_TEL'),
            'whatsapp'  => env('ADMIN_HRD_WHATSAPP'),
            'email'     => env('ADMIN_HRD_EMAIL'),
        ],
    ],

];

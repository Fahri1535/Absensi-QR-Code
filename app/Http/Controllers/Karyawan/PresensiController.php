<?php

namespace App\Http\Controllers\Karyawan;

use App\Http\Controllers\Controller;
use App\Models\{JadwalKerja, Notifikasi, Presensi, QrCode};
use Carbon\Carbon;
use Illuminate\Http\{JsonResponse, Request};

class PresensiController extends Controller
{
    /* ─── Halaman scan ───────────────────────────────────────── */

    public function index()
    {
        $karyawan        = auth()->user()->karyawan;
        // FIXED: ::getSetting() bukan ::get()
        $jadwal          = JadwalKerja::getSetting();
        $presensiHariIni = Presensi::where('karyawan_id', $karyawan->id)
            ->whereDate('tanggal', today())
            ->first();

        return view('karyawan.presensi', compact('karyawan', 'jadwal', 'presensiHariIni'));
    }

    /* ─── POST scan QR ───────────────────────────────────────── */

    public function scan(Request $request): JsonResponse
    {
        $request->validate(['qr_data' => 'required|string']);

        $karyawan = auth()->user()->karyawan;
        // FIXED: ::getSetting() bukan ::get()
        $jadwal   = JadwalKerja::getSetting();
        $now      = Carbon::now();
        $qrData   = $request->qr_data;

        // Cari QR di database
        $qr = QrCode::where('kode_qr', $qrData)->where('is_active', true)->first();

        if (! $qr) {
            return response()->json(['success' => false, 'message' => 'QR Code tidak valid atau tidak aktif.'], 422);
        }

        // Presensi hari ini
        $presensi = Presensi::firstOrNew([
            'karyawan_id' => $karyawan->id,
            'tanggal'     => today()->toDateString(),
        ]);

        /* ── QR Masuk ── */
        if ($qr->tipe === 'masuk') {
            if ($presensi->jam_datang) {
                return response()->json(['success' => false, 'message' => 'Anda sudah presensi masuk hari ini.'], 422);
            }

            $jamMasuk    = Carbon::parse($jadwal->jam_masuk);
            $windowBuka  = $jamMasuk->copy()->subMinutes(15);
            $windowTutup = $jamMasuk->copy()->addMinutes(60 + $jadwal->toleransi_menit);

            if ($now->lt($windowBuka) || $now->gt($windowTutup)) {
                return response()->json([
                    'success' => false,
                    'message' => "Window presensi masuk: {$windowBuka->format('H:i')} – {$windowTutup->format('H:i')}",
                ], 422);
            }

            // FIXED: cek toleransi dengan copy() agar $jamMasuk tidak termutasi
            $statusMasuk = $now->gt($jamMasuk->copy()->addMinutes($jadwal->toleransi_menit))
                ? 'terlambat' : 'tepat_waktu';

            $presensi->fill([
                'jam_datang'   => $now->toTimeString(),
                'status_masuk' => $statusMasuk,
            ])->save();

            Notifikasi::create([
                'user_id' => auth()->id(),
                'judul'   => 'Presensi Masuk Berhasil',
                'pesan'   => "Presensi masuk tercatat pukul {$now->format('H:i')} · " . ucfirst(str_replace('_', ' ', $statusMasuk)),
                'ikon'    => 'fa-clock',
                'warna'   => $statusMasuk === 'tepat_waktu' ? 'green' : 'amber',
                'link'    => route('karyawan.riwayat'),
            ]);

            return response()->json([
                'success'      => true,
                'type'         => 'masuk',
                'jam'          => $now->format('H:i'),
                'status'       => $statusMasuk,
                'status_label' => $statusMasuk === 'tepat_waktu' ? 'Tepat Waktu' : 'Terlambat',
            ]);
        }

        /* ── QR Pulang ── */
        if ($qr->tipe === 'pulang') {
            if (! $presensi->jam_datang) {
                return response()->json(['success' => false, 'message' => 'Anda belum melakukan presensi masuk hari ini.'], 422);
            }
            if ($presensi->jam_pulang) {
                return response()->json(['success' => false, 'message' => 'Anda sudah presensi pulang hari ini.'], 422);
            }

            $jamPulang   = Carbon::parse($jadwal->jam_pulang);
            $windowBuka  = $jamPulang->copy()->subMinutes(30);
            $windowTutup = $jamPulang->copy()->addHour();

            if ($now->lt($windowBuka) || $now->gt($windowTutup)) {
                return response()->json([
                    'success' => false,
                    'message' => "Window presensi pulang: {$windowBuka->format('H:i')} – {$windowTutup->format('H:i')}",
                ], 422);
            }

            $statusPulang = $now->lt($jamPulang) ? 'lebih_awal' : 'normal';

            $presensi->fill([
                'jam_pulang'    => $now->toTimeString(),
                'status_pulang' => $statusPulang,
            ])->save();

            Notifikasi::create([
                'user_id' => auth()->id(),
                'judul'   => 'Presensi Pulang Berhasil',
                'pesan'   => "Presensi pulang tercatat pukul {$now->format('H:i')}",
                'ikon'    => 'fa-house',
                'warna'   => 'green',
                'link'    => route('karyawan.riwayat'),
            ]);

            return response()->json([
                'success'      => true,
                'type'         => 'pulang',
                'jam'          => $now->format('H:i'),
                'status'       => $statusPulang,
                'status_label' => $statusPulang === 'normal' ? 'Normal' : 'Lebih Awal',
            ]);
        }

        return response()->json(['success' => false, 'message' => 'Tipe QR tidak dikenali.'], 422);
    }
}
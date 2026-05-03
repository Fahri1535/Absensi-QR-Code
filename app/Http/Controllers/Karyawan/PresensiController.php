<?php

namespace App\Http\Controllers\Karyawan;

use App\Http\Controllers\Controller;
use App\Models\{JadwalKerja, Notifikasi, Presensi, QrCode};
use Carbon\Carbon;
use Illuminate\Http\{JsonResponse, Request};

class PresensiController extends Controller
{
    public function index()
    {
        $karyawan        = auth()->user()->karyawan;
        $jadwal          = JadwalKerja::getSetting();
        $presensiHariIni = Presensi::where('karyawan_id', $karyawan->id)
            ->whereDate('tanggal', today())
            ->first();

        $geoRequired = $jadwal->kantor_latitude !== null
            && $jadwal->kantor_longitude !== null
            && ! empty($jadwal->radius_meter);

        $pendingQrToken = null;
        $t = request('t');
        if ($t && QrCode::where('kode_qr', $t)->where('is_active', true)->exists()) {
            $pendingQrToken = $t;
        }

        return view('karyawan.presensi', compact(
            'karyawan',
            'jadwal',
            'presensiHariIni',
            'geoRequired',
            'pendingQrToken'
        ));
    }

    public function scan(Request $request): JsonResponse
    {
        $request->validate([
            'qr_data'   => 'required|string',
            'latitude'  => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        $qrData = self::normalizeQrPayload($request->input('qr_data'));

        $karyawan = auth()->user()->karyawan;
        $jadwal   = JadwalKerja::getSetting();
        $now      = Carbon::now();

        if ($err = $this->validateLokasiKantor($request, $jadwal)) {
            return response()->json(['success' => false, 'message' => $err], 422);
        }

        $qr = QrCode::where('kode_qr', $qrData)->where('is_active', true)->first();

        if (! $qr) {
            return response()->json(['success' => false, 'message' => 'QR Code tidak valid atau tidak aktif.'], 422);
        }

        $presensi = Presensi::firstOrNew([
            'karyawan_id' => $karyawan->id,
            'tanggal'     => today()->toDateString(),
        ]);

        if ($qr->tipe === 'masuk') {
            if ($presensi->jam_datang) {
                return response()->json(['success' => false, 'message' => 'Anda sudah presensi masuk hari ini.'], 422);
            }

            $jamMasuk    = Carbon::parse($jadwal->jam_masuk);
            $windowBuka  = $jamMasuk->copy()->subMinutes(15);
            $windowTutup = $jamMasuk->copy()->addMinutes($jadwal->toleransi_menit);

            if ($now->lt($windowBuka) || $now->gt($windowTutup)) {
                return response()->json([
                    'success' => false,
                    'message' => "Window presensi masuk: {$windowBuka->format('H:i')} – {$windowTutup->format('H:i')} (Batas toleransi)",
                ], 422);
            }

            $statusMasuk = $now->gt($jamMasuk->copy()->addMinutes($jadwal->toleransi_menit))
                ? 'terlambat' : 'tepat_waktu';

            $presensi->fill([
                'jam_datang'   => $now->toTimeString(),
                'status_masuk' => $statusMasuk,
            ])->save();

            Notifikasi::create([
                'user_id' => auth()->user()->getKey(),
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

        if ($qr->tipe === 'pulang') {
            if (! $presensi->jam_datang) {
                return response()->json(['success' => false, 'message' => 'Anda belum melakukan presensi masuk hari ini.'], 422);
            }
            if ($presensi->jam_pulang) {
                return response()->json(['success' => false, 'message' => 'Anda sudah presensi pulang hari ini.'], 422);
            }

            $jamPulang   = Carbon::parse($jadwal->jam_pulang);
            $windowBuka  = $jamPulang->copy()->subMinutes(30);
            $windowTutup = $jamPulang;

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
                'user_id' => auth()->user()->getKey(),
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

    /** Ambil token `t` dari URL QR atau kembalikan string mentah (kompatibel QR lama). */
    public static function normalizeQrPayload(string $raw): string
    {
        $raw = trim($raw);
        if ($raw === '') {
            return $raw;
        }

        if (str_contains($raw, 'http://') || str_contains($raw, 'https://')) {
            $parts = parse_url($raw);
            if (! empty($parts['query'])) {
                parse_str($parts['query'], $q);
                if (! empty($q['t'])) {
                    return $q['t'];
                }
            }
        }

        return $raw;
    }

    public static function haversineMeters(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earth = 6371000.0;
        $φ1 = deg2rad($lat1);
        $φ2 = deg2rad($lat2);
        $Δφ = deg2rad($lat2 - $lat1);
        $Δλ = deg2rad($lon2 - $lon1);
        $a = sin($Δφ / 2) ** 2 + cos($φ1) * cos($φ2) * sin($Δλ / 2) ** 2;

        return 2 * $earth * atan2(sqrt($a), sqrt(1 - $a));
    }

    protected function validateLokasiKantor(Request $request, JadwalKerja $jadwal): ?string
    {
        if ($jadwal->kantor_latitude === null || $jadwal->kantor_longitude === null || empty($jadwal->radius_meter)) {
            return null;
        }

        $lat = $request->input('latitude');
        $lng = $request->input('longitude');
        if ($lat === null || $lng === null || ! is_numeric($lat) || ! is_numeric($lng)) {
            return 'Aktifkan izin lokasi perangkat untuk presensi di area kantor.';
        }

        $meters = self::haversineMeters(
            (float) $lat,
            (float) $lng,
            (float) $jadwal->kantor_latitude,
            (float) $jadwal->kantor_longitude
        );

        if ($meters > (int) $jadwal->radius_meter) {
            return 'Anda di luar radius kantor yang diizinkan (' . (int) $jadwal->radius_meter
                . ' m). Jarak perkiraan: ' . (int) round($meters) . ' m.';
        }

        return null;
    }
}

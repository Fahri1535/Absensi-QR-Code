<?php

namespace App\Http\Controllers\Karyawan;

use App\Http\Controllers\Controller;
use App\Models\{Izin, JadwalKerja, Notifikasi, Presensi, QrCode};
use Carbon\Carbon;
use Illuminate\Http\{JsonResponse, Request};

class PresensiController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $karyawan = $user->karyawan;

        if (!$karyawan) {
            // Jika admin/hrd belum punya record karyawan, buatkan default agar tidak error
            $karyawan = \App\Models\Karyawan::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'nama_lengkap' => ucfirst($user->role) . ' Admin',
                    'status' => 'aktif',
                    'kode_karyawan' => 'ADM-' . \Illuminate\Support\Str::upper(\Illuminate\Support\Str::random(12)),
                ]
            );
        }

        $jadwal          = $karyawan->schedule;
        $presensiHariIni = Presensi::where('karyawan_id', $karyawan->id)
            ->whereDate('tanggal', today())
            ->first();

        // Cek apakah karyawan sedang izin/cuti/sakit yang disetujui untuk hari ini
        $sedangIzin = Izin::where('karyawan_id', $karyawan->id)
            ->where('status', 'disetujui')
            ->whereDate('tanggal_mulai', '<=', today())
            ->whereDate('tanggal_selesai', '>=', today())
            ->first();

        // Cek apakah karyawan punya pengajuan izin yang masih menunggu persetujuan
        $izinPending = Izin::where('karyawan_id', $karyawan->id)
            ->where('status', 'pending')
            ->first();

        // Cek apakah karyawan tercatat alpa hari ini (tidak hadir tanpa izin
        // disetujui di hari kerja). Konsisten dengan cara sistem menghitung "alpa"
        // di Karyawan\IzinController.
        $sedangAlpaHariIni = $this->cekAlpaHariIni($karyawan->id);

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
            'sedangIzin',
            'izinPending',
            'sedangAlpaHariIni',
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
        $jadwal   = $karyawan->schedule;
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

            // Tidak boleh presensi jika sedang izin/cuti/sakit yang disetujui untuk hari ini
            $sedangIzin = Izin::where('karyawan_id', $karyawan->id)
                ->where('status', 'disetujui')
                ->whereDate('tanggal_mulai', '<=', $now->toDateString())
                ->whereDate('tanggal_selesai', '>=', $now->toDateString())
                ->first();

            if ($sedangIzin) {
                $jenisIzin = ucfirst(str_replace('_', ' ', $sedangIzin->jenis_izin));
                return response()->json([
                    'success' => false,
                    'message' => "Anda tercatat {$jenisIzin} hari ini sehingga tidak dapat melakukan presensi.",
                ], 422);
            }

            $windows = $jadwal->presensiWindows($now);
            $windowBuka  = $windows['masuk_buka'];
            $windowTutup = $windows['masuk_tutup'];
            $jamMasuk    = $windows['jam_masuk'];

            if ($now->lt($windowBuka) || $now->gt($windowTutup)) {
                return response()->json([
                    'success' => false,
                    'message' => $this->buildWindowMessage(
                        'masuk',
                        $jamMasuk,
                        $windowBuka,
                        $windowTutup,
                        $now
                    ),
                ], 422);
            }

            $statusMasuk = $now->gt($jamMasuk->copy()->addMinutes($jadwal->toleransi_menit))
                ? 'terlambat' : 'tepat_waktu';

            $fillData = ['jam_datang' => $now->toTimeString()];
            
            // Check if status_masuk column exists before adding it
            if (\Illuminate\Support\Facades\Schema::hasColumn('presensi', 'status_masuk')) {
                $fillData['status_masuk'] = $statusMasuk;
            }

            $presensi->fill($fillData)->save();

            Notifikasi::create([
                'user_id' => auth()->id(),
                'judul'   => 'Presensi Masuk Berhasil',
                'pesan'   => "Presensi masuk tercatat pukul {$now->format('H:i')} · " . ucfirst(str_replace('_', ' ', $statusMasuk)),
                'ikon'    => 'fa-clock',
                'warna'   => $statusMasuk === 'tepat_waktu' ? 'green' : 'amber',
                'link'    => route(auth()->user()->role . '.riwayat'),
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

            $windows = $jadwal->presensiWindows($now);
            $windowBuka  = $windows['pulang_buka'];
            $windowTutup = $windows['pulang_tutup'];
            $jamPulang   = $windows['jam_pulang'];

            if ($now->lt($windowBuka) || $now->gt($windowTutup)) {
                return response()->json([
                    'success' => false,
                    'message' => $this->buildWindowMessage(
                        'pulang',
                        $jamPulang,
                        $windowBuka,
                        $windowTutup,
                        $now
                    ),
                ], 422);
            }

            $statusPulang = $now->lt($jamPulang) ? 'pulang_awal' : 'normal';

            $fillData = ['jam_pulang' => $now->toTimeString()];
            
            // Check if status_pulang column exists before adding it
            if (\Illuminate\Support\Facades\Schema::hasColumn('presensi', 'status_pulang')) {
                $fillData['status_pulang'] = $statusPulang;
            }

            $presensi->fill($fillData)->save();

            Notifikasi::create([
                'user_id' => auth()->id(),
                'judul'   => 'Presensi Pulang Berhasil',
                'pesan'   => "Presensi pulang tercatat pukul {$now->format('H:i')}",
                'ikon'    => 'fa-house',
                'warna'   => 'green',
                'link'    => route(auth()->user()->role . '.riwayat'),
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

    /**
     * Susun pesan error saat scan dilakukan di luar window presensi.
     *
     * Pesan dibuat context-aware (terlalu awal vs sudah lewat batas) dan
     * selalu menyebutkan jam masuk/jam pulang + jam buka-tutup scan, agar
     * karyawan tahu kapan harus mencoba lagi — konsisten dengan tampilan
     * di halaman Kelola QR Code & Lokasi/Waktu Kerja.
     */
    protected function buildWindowMessage(
        string $tipe,
        Carbon $jamAcuan,
        Carbon $windowBuka,
        Carbon $windowTutup,
        Carbon $now
    ): string {
        $isMasuk   = $tipe === 'masuk';
        $labelJam  = $isMasuk ? 'Jam masuk' : 'Jam pulang';
        $labelAksi = $isMasuk ? 'presensi masuk' : 'presensi pulang';

        $jamAcuanFmt   = $jamAcuan->format('H:i');
        $windowBukaFmt = $windowBuka->format('H:i');
        $windowTutupFmt= $windowTutup->format('H:i');

        if ($now->lt($windowBuka)) {
            // Terlalu awal — window belum dibuka
            $menit = $now->diffInMinutes($windowBuka, false); // positif = masih tunggu
            $tunggu = $menit > 0
                ? " (baru bisa ~{$menit} menit lagi)"
                : '';
            return "Belum waktunya {$labelAksi}. "
                . "{$labelJam}: {$jamAcuanFmt}. "
                . "Scan dibuka pukul {$windowBukaFmt}{$tunggu}.";
        }

        // Sudah lewat window tutup
        $menit = $now->diffInMinutes($windowTutup, false); // negatif = sudah lewat
        $terlambat = $menit < 0
            ? " (telah berakhir ~" . abs($menit) . " menit lalu)"
            : '';
        return "Waktu {$labelAksi} telah berakhir. "
            . "{$labelJam}: {$jamAcuanFmt}. "
            . "Scan ditutup pukul {$windowTutupFmt}{$terlambat}.";
    }

    /**
     * Cek apakah karyawan alpa pada hari ini.
     *
     * Alpa hanya muncul untuk tanggal KEMARIN atau sebelumnya. Untuk HARI INI,
     * alpha TIDAK PERNAH final — selalu kembalikan false, agar operator bisa
     * mengubah pengaturan jam kerja (mis. memperpanjang durasi_scan) kapan
     * saja tanpa membuat karyawan terkunci sebagai alpha.
     *
     * Konsisten dengan cara Operator\PresensiController menampilkan data:
     * hari ini → "Belum Presensi", bukan "Alpa".
     * Kemarin/lampau → "Alpa" jika window sudah lewat & tidak ada presensi/izin.
     */
    protected function cekAlpaHariIni(int $karyawanId): bool
    {
        $hariIni = today();
        $jadwal  = JadwalKerja::getSetting();

        // Hari ini: TIDAK PERNAH alpha — operator bisa kapan saja ubah jam kerja
        if ($hariIni->isToday()) {
            return false;
        }

        // Selama jam masuk tanggal hari ini belum lewat, belum waktunya
        // menandai siapa pun sebagai alpha.
        if (! $jadwal->alphaFinalUntukTanggal($hariIni)) {
            return false;
        }

        // Sudah presensi (absen masuk) hari ini -> tidak alpa
        $sudahPresensi = Presensi::where('karyawan_id', $karyawanId)
            ->whereDate('tanggal', $hariIni)
            ->whereNotNull('jam_datang')
            ->exists();
        if ($sudahPresensi) {
            return false;
        }

        // Punya izin yang disetujui mencakup hari ini -> tidak alpa
        $adaIzinDisetujui = Izin::where('karyawan_id', $karyawanId)
            ->where('status', 'disetujui')
            ->where('tanggal_mulai', '<=', $hariIni)
            ->where('tanggal_selesai', '>=', $hariIni)
            ->exists();
        if ($adaIzinDisetujui) {
            return false;
        }

        return true;
    }
}
